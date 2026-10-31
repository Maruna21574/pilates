<?php
declare(strict_types=1);
/**
 * Rezervácie: databáza (MySQL cez PDO), Stripe Checkout a e-maily.
 * Nastavenie je v data/config.php (vzor: data/config.sample.php). Bez neho web funguje po starom (len e-mail).
 */

function booking_config(): array {
    static $cfg = null;
    if ($cfg === null) {
        $f = __DIR__ . '/../data/config.php';
        $cfg = is_file($f) ? (array)(include $f) : [];
    }
    return $cfg;
}

/** PDO spojenie alebo null, ak databáza nie je nastavená / dostupná. Tabuľky sa vytvoria pri prvom použití. */
function db(): ?PDO {
    static $pdo = false;
    if ($pdo !== false) return $pdo;
    $c = booking_config()['db'] ?? null;
    if (!$c || empty($c['name'])) return $pdo = null;
    try {
        $pdo = new PDO("mysql:host={$c['host']};dbname={$c['name']};charset=utf8mb4", $c['user'], $c['pass'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec("SET time_zone = '" . (new DateTime())->format('P') . "'");
        try { $pdo->query('SELECT 1 FROM reservations LIMIT 0'); } catch (PDOException) { db_install($pdo); }
    } catch (Throwable $e) {
        error_log('DB: ' . $e->getMessage());
        $pdo = null;
    }
    return $pdo;
}

function db_install(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS reservations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        public_id CHAR(32) NOT NULL UNIQUE,
        class_id VARCHAR(40) NOT NULL,
        session_date DATE NOT NULL,
        session_time CHAR(5) NOT NULL,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        status ENUM('pending','paid','on_site','waitlist','cancelled','expired','refunded') NOT NULL,
        pay_method ENUM('online','on_site') NULL,
        amount_cents INT UNSIGNED NULL,
        stripe_session_id VARCHAR(120) NULL UNIQUE,
        stripe_payment_intent VARCHAR(120) NULL,
        expires_at DATETIME NULL,
        created_at DATETIME NOT NULL,
        paid_at DATETIME NULL,
        KEY slot (class_id, session_date, session_time, status),
        KEY by_date (session_date, session_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/* ---------- ceny a kapacita ---------- */

/** „12 €“ / „12,50 €“ → centy; null, ak cena nie je vyplnená alebo sa nedá prečítať. */
function price_to_cents(?string $price): ?int {
    if ($price === null || !preg_match('/^\s*(\d{1,4})(?:[.,](\d{1,2}))?\s*(?:€|eur)?\s*$/iu', $price, $m)) return null;
    $c = (int)$m[1] * 100 + (isset($m[2]) ? (int)str_pad($m[2], 2, '0') : 0);
    return $c > 0 ? $c : null;
}

/** Počet obsadených miest: zaplatené, platba na mieste a nezaplatené s ešte platným podržaním. */
function seats_taken(PDO $pdo, string $class, string $date, string $time): int {
    $s = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE class_id = ? AND session_date = ? AND session_time = ?
        AND (status IN ('paid','on_site') OR (status = 'pending' AND expires_at > NOW()))");
    $s->execute([$class, $date, $time]);
    return (int)$s->fetchColumn();
}

/** Obsadenosť všetkých termínov od dnes: ['2026-11-03|09:00|reformer' => 2, …] (pre kalendár na webe). */
function seats_overview(): array {
    $pdo = db();
    if (!$pdo) return [];
    try {
        $r = $pdo->query("SELECT class_id, session_date, session_time, COUNT(*) n FROM reservations
            WHERE session_date >= CURDATE() AND (status IN ('paid','on_site') OR (status = 'pending' AND expires_at > NOW()))
            GROUP BY class_id, session_date, session_time")->fetchAll();
    } catch (Throwable) { return []; }
    $out = [];
    foreach ($r as $x) $out["{$x['session_date']}|{$x['session_time']}|{$x['class_id']}"] = (int)$x['n'];
    return $out;
}

/* ---------- Stripe ---------- */

/** Online platba je zapnutá, keď sú v configu oba kľúče (secret aj publishable) a beží databáza. */
function stripe_enabled(): bool {
    $s = booking_config()['stripe'] ?? [];
    return !empty($s['secret_key']) && !empty($s['publishable_key']) && db() !== null;
}

/** Volanie Stripe API (form-encoded, bez knižnice). Pri chybe vyhodí výnimku. */
function stripe_call(string $method, string $path, array $params = []): array {
    $key = (string)(booking_config()['stripe']['secret_key'] ?? '');
    $ch = curl_init('https://api.stripe.com/v1/' . $path);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_USERPWD => $key . ':',
        CURLOPT_HTTPHEADER => ['Stripe-Version: 2025-04-30.basil'],   // pevná verzia API, aby sa správanie nezmenilo samo
        CURLOPT_POSTFIELDS => $method === 'POST' ? http_build_query($params) : null,
    ]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err = curl_error($ch);
    $j = is_string($body) ? json_decode($body, true) : null;
    if (!is_array($j) || $code >= 400) throw new RuntimeException('Stripe: ' . ($j['error']['message'] ?? $err ?: "HTTP $code"));
    return $j;
}

/** Overí podpis webhooku (hlavička Stripe-Signature) – tolerancia 5 minút. */
function stripe_verify_signature(string $payload, string $header, string $secret): bool {
    $t = null; $sigs = [];
    foreach (explode(',', $header) as $part) {
        [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
        if ($k === 't') $t = $v; elseif ($k === 'v1') $sigs[] = $v;
    }
    if ($t === null || !ctype_digit($t) || abs(time() - (int)$t) > 300 || $secret === '') return false;
    $expected = hash_hmac('sha256', $t . '.' . $payload, $secret);
    foreach ($sigs as $s) if (hash_equals($expected, $s)) return true;
    return false;
}

function base_url(): string {
    $c = (string)(booking_config()['base_url'] ?? '');
    if ($c !== '') return rtrim($c, '/');
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $dir = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/api/x'))), '/');
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $dir;
}

/**
 * Označí rezerváciu ako zaplatenú (volá webhook aj návrat zákazníka – bezpečné volať viackrát).
 * E-maily sa pošlú iba pri prvom prechode do stavu „zaplatené“.
 */
function reservation_mark_paid(string $stripeSessionId, ?string $paymentIntent): void {
    $pdo = db();
    if (!$pdo) return;
    $u = $pdo->prepare("UPDATE reservations SET status = 'paid', paid_at = NOW(), stripe_payment_intent = ?
        WHERE stripe_session_id = ? AND status IN ('pending','expired')");
    $u->execute([$paymentIntent, $stripeSessionId]);
    if ($u->rowCount() !== 1) return;
    $s = $pdo->prepare('SELECT * FROM reservations WHERE stripe_session_id = ?');
    $s->execute([$stripeSessionId]);
    if ($row = $s->fetch()) reservation_notify($row, true);
}

/** Skontroluje platbu priamo u Stripe (použije sa pri návrate zákazníka, ak webhook ešte nedorazil). */
function reservation_sync_from_stripe(array $row): void {
    if ($row['status'] !== 'pending' || !$row['stripe_session_id'] || !stripe_enabled()) return;
    try {
        $s = stripe_call('GET', 'checkout/sessions/' . rawurlencode($row['stripe_session_id']));
        if (($s['payment_status'] ?? '') === 'paid') reservation_mark_paid($row['stripe_session_id'], $s['payment_intent'] ?? null);
    } catch (Throwable $e) { error_log($e->getMessage()); }
}

/* ---------- e-maily ---------- */

function mail_enc(string $t): string { return '=?UTF-8?B?' . base64_encode($t) . '?='; }

/** Popis termínu pre e-maily: „Reformer, pondelok 3. 11. 2026 o 09:00“. */
function reservation_when(array $row): array {
    global $classes;
    $days = ['pondelok', 'utorok', 'streda', 'štvrtok', 'piatok', 'sobota', 'nedeľa'];
    $d = new DateTimeImmutable($row['session_date']);
    $cls = $classes[$row['class_id']] ?? ['name' => $row['class_id'], 'duration' => 55];
    $when = $days[(int)$d->format('N') - 1] . ' ' . $d->format('j. n. Y') . ' o ' . $row['session_time'];
    return [$cls, $when, $cls['name'] . ', ' . $when];
}

function reservation_money(?int $cents): string {
    return $cents === null ? '' : rtrim(rtrim(number_format($cents / 100, 2, ',', ''), '0'), ',') . ' €';
}

/** Pošle štúdiu aj klientke správu podľa stavu rezervácie. $paidNow = platba práve prišla. */
function reservation_notify(array $row, bool $paidNow = false): bool {
    global $site;
    [$cls, $when, $what] = reservation_when($row);
    $status = $row['status'];
    $overbooked = false;
    if ($paidNow && ($pdo = db())) {
        $overbooked = seats_taken($pdo, $row['class_id'], $row['session_date'], $row['session_time']) > (int)($cls['capacity'] ?? 0);
    }
    $kind = ['paid' => 'Rezervácia (zaplatená online)', 'on_site' => 'Rezervácia (platba na mieste)', 'waitlist' => 'Čakacia listina'][$status] ?? 'Rezervácia';
    $studio = "$kind\n\n"
        . "Služba: {$cls['name']}\nTermín: $when ({$cls['duration']} min)\n"
        . ($status === 'paid' ? 'Zaplatené online: ' . reservation_money((int)$row['amount_cents']) . "\n" : '')
        . ($status === 'on_site' ? 'Platba: na mieste' . ($row['amount_cents'] ? ' (' . reservation_money((int)$row['amount_cents']) . ')' : '') . "\n" : '')
        . "\nMeno: {$row['name']}\nE-mail: {$row['email']}\nTelefón: {$row['phone']}\n"
        . "Súhlas so spracovaním údajov: áno ({$row['created_at']})\n";
    if ($overbooked) $studio .= "\nPOZOR: lekcia je po tejto platbe preplnená (platba prišla po vypršaní podržaného miesta). Dohodnite s klientkou iný termín alebo jej platbu vráťte v Stripe.\n";
    $ok = @mail($site['email'], mail_enc("$kind – $what – {$row['name']}"), $studio, [
        'From' => $site['email'], 'Reply-To' => $row['email'], 'Content-Type' => 'text/plain; charset=UTF-8',
    ]);

    $intro = match ($status) {
        'paid'     => "vašu rezerváciu a platbu (" . reservation_money((int)$row['amount_cents']) . ") sme prijali. Miesto na lekcii je potvrdené:",
        'on_site'  => "vaše miesto na lekcii je rezervované. Lekciu zaplatíte na mieste v štúdiu:",
        'waitlist' => "lekcia je momentálne plná, zapísali sme vás na čakaciu listinu. Ak sa miesto uvoľní, ozveme sa vám:",
        default    => "prijali sme vašu rezerváciu:",
    };
    $client = "Dobrý deň,\n\n$intro\n\n  $what\n\n"
        . "Ak potrebujete niečo zmeniť alebo rezerváciu zrušiť, napíšte nám na {$site['email']} alebo zavolajte na {$site['phone']}.\n\n"
        . "{$site['name']}\n{$site['address']}\n";
    @mail($row['email'], mail_enc("Rezervácia – {$site['name']}"), $client, [
        'From' => $site['email'], 'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
    return $ok;
}
