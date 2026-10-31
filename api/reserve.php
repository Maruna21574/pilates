<?php
declare(strict_types=1);
/**
 * Rezervácia lekcie. S databázou: uloží rezerváciu, stráži kapacitu a pri online platbe vráti odkaz na Stripe Checkout.
 * Bez databázy (data/config.php chýba) funguje po starom: len e-mail štúdiu a potvrdenie o prijatí žiadosti.
 */
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/booking.php';
header('Content-Type: application/json; charset=utf-8');

function out(bool $ok, string $message, int $code = 200, array $extra = []): never {
    http_response_code($code);
    echo json_encode(['ok' => $ok, 'message' => $message] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Neplatná požiadavka.', 405);
if (!empty($_POST['website'])) out(true, 'Ďakujeme.'); // honeypot – bot

$name  = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$class = (string)($_POST['class'] ?? '');
$date  = (string)($_POST['date'] ?? '');
$time  = (string)($_POST['time'] ?? '');

if (($_POST['gdpr'] ?? '') !== '1') out(false, 'Na odoslanie rezervácie je potrebný súhlas so spracovaním osobných údajov.', 422);
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) out(false, 'Vyplňte prosím meno a platný e-mail.', 422);
if (!preg_match('/^[0-9+()\/\-\s]{6,30}$/', $phone)) out(false, 'Vyplňte prosím platné telefónne číslo.', 422);
if (mb_strlen($name) > 120 || mb_strlen($email) > 190) out(false, 'Niektorý z údajov je príliš dlhý.', 422);
if (preg_match('/[\r\n]/', $name . $email . $phone)) out(false, 'Neplatné údaje.', 422);

// termín musí existovať v rozvrhu a nesmie byť v minulosti
$d = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
if (!isset($classes[$class]) || !$d || $d->format('Y-m-d') !== $date || !preg_match('/^\d{2}:\d{2}$/', $time)) {
    out(false, 'Vybraný termín sa nepodarilo overiť. Skúste to prosím znova.', 422);
}
$weekday = (int)$d->format('N');
$exists = false;
foreach ($schedule as $s) {
    if ($s['class'] === $class && $s['day'] === $weekday && $s['time'] === $time) { $exists = true; break; }
}
$start = DateTimeImmutable::createFromFormat('Y-m-d H:i', "$date $time");
if (!$exists || !$start || $start < new DateTimeImmutable('now')) {
    out(false, 'Tento termín už nie je k dispozícii. Vyberte prosím iný.', 422);
}

$pdo = db();
if ($pdo === null) {
    // záložný režim bez databázy: žiadosť o rezerváciu e-mailom
    $days = ['pondelok', 'utorok', 'streda', 'štvrtok', 'piatok', 'sobota', 'nedeľa'];
    $cls  = $classes[$class];
    $when = $days[$weekday - 1] . ' ' . $d->format('j. n. Y') . ' o ' . $time;
    $what = $cls['name'] . ', ' . $when;
    $studioBody = "Nová žiadosť o rezerváciu\n\n"
        . "Služba: {$cls['name']}\nTermín: $when ({$cls['duration']} min)\n\n"
        . "Meno: $name\nE-mail: $email\nTelefón: $phone\n"
        . "Súhlas so spracovaním údajov: áno (" . date('j. n. Y H:i') . ")\n";
    $okStudio = @mail($site['email'], mail_enc("Rezervácia – $what – $name"), $studioBody, [
        'From' => $site['email'], 'Reply-To' => $email, 'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
    $clientBody = "Dobrý deň,\n\nďakujeme za záujem o lekciu v {$site['name']}. Prijali sme vašu žiadosť o rezerváciu:\n\n"
        . "  $what\n\n"
        . "Termín vám štúdio potvrdí e-mailom alebo telefonicky. Ak potrebujete niečo zmeniť, napíšte nám na {$site['email']} alebo zavolajte na {$site['phone']}.\n\n"
        . "{$site['name']}\n{$site['address']}\n";
    @mail($email, mail_enc("Žiadosť o rezerváciu – {$site['name']}"), $clientBody, [
        'From' => $site['email'], 'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
    $okStudio
        ? out(true, 'Žiadosť o rezerváciu sme prijali.', 200, ['status' => 'request'])
        : out(false, 'Rezerváciu sa nepodarilo odoslať. Skúste to prosím neskôr alebo nám zavolajte.', 500);
}

// ---------- s databázou ----------
$pay   = ($_POST['pay'] ?? '') === 'online' ? 'online' : 'on_site';
$cents = price_to_cents($classes[$class]['price'] ?? null);   // cena vždy zo servera, nikdy z formulára
$cap   = (int)$classes[$class]['capacity'];
$hold  = max(30, min(1440, (int)(booking_config()['hold_minutes'] ?? 30)));
if ($pay === 'online' && (!stripe_enabled() || $cents === null)) out(false, 'Online platba teraz nie je dostupná. Vyberte prosím platbu na mieste.', 422);

// jeden termín = jeden zámok, aby sa posledné miesto nepredalo dvom ľuďom naraz (zámok sa uvoľní aj pri exit)
$lock = 'slot:' . md5("$class|$date|$time");
if ((int)$pdo->query('SELECT GET_LOCK(' . $pdo->quote($lock) . ', 5)')->fetchColumn() !== 1) {
    out(false, 'Server je práve vyťažený. Skúste to prosím o chvíľu.', 503);
}
// rovnaká osoba nemôže mať ten istý termín dvakrát
$dup = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE class_id = ? AND session_date = ? AND session_time = ? AND email = ?
    AND (status IN ('paid','on_site','waitlist') OR (status = 'pending' AND expires_at > NOW()))");
$dup->execute([$class, $date, $time, $email]);
if ((int)$dup->fetchColumn() > 0) out(false, 'Na tento termín už rezerváciu s týmto e-mailom máte.', 409);

$full     = seats_taken($pdo, $class, $date, $time) >= $cap;
$status   = $full ? 'waitlist' : ($pay === 'online' ? 'pending' : 'on_site');
$publicId = bin2hex(random_bytes(16));
$pdo->prepare('INSERT INTO reservations (public_id, class_id, session_date, session_time, name, email, phone, status, pay_method, amount_cents, expires_at, created_at)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())')
    ->execute([$publicId, $class, $date, $time, $name, $email, $phone, $status, $full ? null : $pay, $cents,
        $status === 'pending' ? date('Y-m-d H:i:s', time() + $hold * 60) : null]);
$id = (int)$pdo->lastInsertId();
$pdo->query('SELECT RELEASE_LOCK(' . $pdo->quote($lock) . ')');

$row = $pdo->query("SELECT * FROM reservations WHERE id = $id")->fetch();

if ($status !== 'pending') {
    reservation_notify($row);   // ak e-mail zlyhá, rezervácia je aj tak uložená a vidno ju v admine
    out(true, $full ? 'Lekcia je plná, zapísali sme vás na čakaciu listinu.' : 'Rezerváciu sme prijali.', 200, ['status' => $status]);
}

// online platba: Stripe Checkout
[$cls, $when] = reservation_when($row);
try {
    $sess = stripe_call('POST', 'checkout/sessions', [
        'mode' => 'payment',
        'client_reference_id' => $publicId,
        'customer_email' => $email,
        'locale' => 'sk',
        'expires_at' => time() + $hold * 60,
        'line_items' => [[
            'quantity' => 1,
            'price_data' => ['currency' => 'eur', 'unit_amount' => $cents, 'product_data' => ['name' => $cls['name'], 'description' => $when]],
        ]],
        'metadata' => ['reservation' => $publicId],
        'ui_mode' => 'embedded',                 // platobný formulár sa zobrazí priamo v rezervačnom okne
        'redirect_on_completion' => 'if_required',
        'return_url'  => base_url() . '/rezervacia.php?paid=' . $publicId,   // len pre metódy, ktoré vyžadujú presmerovanie
    ]);
    $pdo->prepare('UPDATE reservations SET stripe_session_id = ? WHERE id = ?')->execute([$sess['id'], $id]);
    out(true, 'Prejdite k platbe.', 200, ['status' => 'pending', 'clientSecret' => $sess['client_secret'], 'reservation' => $publicId]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    $pdo->prepare("UPDATE reservations SET status = 'expired' WHERE id = ?")->execute([$id]);
    out(false, 'Platobnú bránu sa nepodarilo spustiť. Skúste to prosím neskôr alebo vyberte platbu na mieste.', 502);
}
