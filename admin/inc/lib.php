<?php
declare(strict_types=1);
/**
 * Spoločná knižnica admin panela: relácie, prihlásenie, CSRF, hlášky, rozloženie a pomocné funkcie formulárov.
 */
defined('ADMIN') or exit;

require_once dirname(__DIR__, 2) . '/includes/data.php';

const ADMIN_SESSION = 'opadmin';
const ADMIN_IDLE_SECONDS = 7200;        // automatické odhlásenie po 2 h nečinnosti
const LOGIN_MAX_FAILS = 5;              // počet chybných pokusov z jednej adresy
const LOGIN_WINDOW = 900;               // za 15 minút

/* ---------- relácia a hlavičky ---------- */

function admin_headers(): void {
    header('X-Robots-Tag: noindex, nofollow, noarchive');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:; media-src 'self' blob:; style-src 'self' 'unsafe-inline'; script-src 'self'; frame-ancestors 'none'; form-action 'self'; base-uri 'self'");
}

function admin_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name(ADMIN_SESSION);
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
    if (isset($_SESSION['last']) && time() - (int)$_SESSION['last'] > ADMIN_IDLE_SECONDS) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    if (!empty($_SESSION['auth'])) $_SESSION['last'] = time();
}

function admin_url(string $page = '', array $q = []): string {
    $q = ($page !== '' && $page !== 'home' ? ['p' => $page] : []) + $q;
    return 'index.php' . ($q ? '?' . http_build_query($q) : '');
}
function redirect(string $to): never { header('Location: ' . $to); exit; }

/* ---------- prihlasovacie údaje ---------- */

/** Súbor s hashom hesla má príponu .php a začína príkazom exit – aj keby server ignoroval .htaccess, jeho obsah sa nedá stiahnuť. */
const CREDS_GUARD = "<?php http_response_code(404); exit; ?>
";
function creds_file(): string { return content_dir() . '/admin.json.php'; }
function creds_load(): ?array {
    $raw = is_file(creds_file()) ? @file_get_contents(creds_file()) : false;
    if ($raw !== false && str_starts_with($raw, CREDS_GUARD)) $raw = substr($raw, strlen(CREDS_GUARD));
    $d = $raw === false ? null : json_decode($raw, true);
    return is_array($d) && !empty($d['hash']) ? $d : null;
}
function creds_save(array $d): bool {
    $tmp = creds_file() . '.tmp' . bin2hex(random_bytes(3));
    if (@file_put_contents($tmp, CREDS_GUARD . json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) === false) return false;
    return @rename($tmp, creds_file());
}

function is_logged_in(): bool { return !empty($_SESSION['auth']); }
function require_login(): void {
    if (!is_logged_in()) redirect('login.php');
}

/* obmedzenie pokusov o prihlásenie (podľa IP adresy) */
function attempts_file(): string { return content_dir() . '/login-attempts.json'; }
function attempts_load(): array {
    $d = is_file(attempts_file()) ? json_decode((string)@file_get_contents(attempts_file()), true) : [];
    return is_array($d) ? $d : [];
}
function client_key(): string { return hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '?') . '|opadmin'); }
function recent_fails(): array {
    $all = attempts_load(); $now = time();
    return array_values(array_filter($all[client_key()] ?? [], fn($t) => $now - (int)$t < LOGIN_WINDOW));
}
function login_locked(): int {   // zostávajúce sekundy zámku, 0 = nie je zamknuté
    $f = recent_fails();
    if (count($f) < LOGIN_MAX_FAILS) return 0;
    return max(0, LOGIN_WINDOW - (time() - (int)min($f)));
}
function record_fail(): void {
    $all = attempts_load(); $now = time(); $k = client_key();
    foreach ($all as $key => $list) { $all[$key] = array_values(array_filter((array)$list, fn($t) => $now - (int)$t < LOGIN_WINDOW)); if (!$all[$key]) unset($all[$key]); }
    $all[$k][] = $now;
    @file_put_contents(attempts_file(), json_encode($all), LOCK_EX);
}
function clear_fails(): void {
    $all = attempts_load(); unset($all[client_key()]);
    @file_put_contents(attempts_file(), json_encode($all), LOCK_EX);
}

/* ---------- CSRF ---------- */

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="_t" value="' . e(csrf_token()) . '">'; }
function csrf_valid(): bool {
    $sent = (string)($_POST['_t'] ?? $_SERVER['HTTP_X_CSRF'] ?? '');
    return $sent !== '' && hash_equals(csrf_token(), $sent);
}
function csrf_check(): void {
    if (!csrf_valid()) { http_response_code(400); exit('Platnosť formulára vypršala. Vráťte sa späť, obnovte stránku a skúste to znova.'); }
}

/* ---------- hlášky ---------- */

function flash(string $msg, string $type = 'ok'): void { $_SESSION['flash'][] = [$type, $msg]; }
function flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

/* ---------- čistenie vstupov ---------- */

function in_str(mixed $v, int $max = 200): string {
    $s = is_string($v) ? $v : '';
    $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', str_replace("\r\n", "\n", $s)) ?? '';
    return mb_substr(trim($s), 0, $max);
}
function in_line(mixed $v, int $max = 200): string { return trim(preg_replace('/\s+/u', ' ', in_str($v, $max)) ?? ''); }
function in_lines(mixed $v, int $maxItems = 12, int $maxLen = 200): array {
    $out = [];
    foreach (explode("\n", in_str($v, 4000)) as $l) {
        $l = in_line($l, $maxLen);
        if ($l !== '') $out[] = $l;
        if (count($out) >= $maxItems) break;
    }
    return $out;
}
function in_url(mixed $v, bool $allowRelative = false): string {
    $s = in_line($v, 300);
    if ($s === '') return '';
    if (preg_match('#^https?://[^\s<>"\']+$#i', $s)) return $s;
    if ($allowRelative && preg_match('#^[A-Za-z0-9._/\-]+(\?[A-Za-z0-9=&._%\-]*)?(\#[A-Za-z0-9_\-]+)?$#', $s)) return $s;
    return '';
}
function in_media(mixed $v): string {
    $s = in_line($v, 120);
    return ($s !== '' && preg_match('#^(uploads/)?[A-Za-z0-9._-]+\.(jpe?g|png|webp)$#i', $s) && is_file(dirname(__DIR__, 2) . '/assets/media/' . $s)) ? $s : '';
}
function in_int(mixed $v, int $min, int $max, int $default): int {
    return (is_numeric($v)) ? max($min, min($max, (int)$v)) : $default;
}
function slugify(string $s): string {
    $s = mb_strtolower($s);
    $s = strtr($s, ['á'=>'a','ä'=>'a','č'=>'c','ď'=>'d','é'=>'e','ě'=>'e','í'=>'i','ĺ'=>'l','ľ'=>'l','ň'=>'n','ó'=>'o','ô'=>'o','ŕ'=>'r','š'=>'s','ť'=>'t','ú'=>'u','ý'=>'y','ž'=>'z']);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
    return trim(substr(trim($s, '-'), 0, 30), '-') ?: 'cvicenie';
}

/* ---------- rozloženie ---------- */

const ADMIN_NAV = [
    ['Prehľad', [['home', 'Prehľad', 'M3 11l9-8 9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z']]],
    ['Rezervácie', [['reservations', 'Rezervácie a platby', 'M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1zM4 10h16M9 15l2 2 4-4']]],
    ['Obsah webu', [
        ['classes',    'Cvičenia',             'M4 7h16M4 12h16M4 17h10'],
        ['individual', 'Individuálne lekcie',  'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM4 21a8 8 0 0 1 16 0'],
        ['prices',     'Ceny',                 'M4 7a2 2 0 0 1 2-2h8l6 6v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zM9 15h6M12 9v6'],
        ['schedule',   'Rozvrh',               'M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1zM4 10h16M9 3v4M15 3v4'],
        ['team',       'Tím',                  'M9 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM3 20a6 6 0 0 1 12 0M16 11a3 3 0 1 0 0-6M17 14a6 6 0 0 1 4 6'],
        ['reviews',    'Recenzie',             'M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.500l6.100-.9z'],
        ['faq',        'Časté otázky',         'M12 17v.01M12 13.500a2.500 2.500 0 1 0-2.400-3.200M4 12a8 8 0 1 0 16 0 8 8 0 0 0-16 0z'],
        ['texts',      'Texty stránok',        'M5 5h14M5 9h14M5 13h9M5 17h6'],
        ['slider',     'Úvodný slider',        'M3 6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM3 16l5-5 4 4 3-3 6 6'],
        ['gallery',    'Galéria',              'M4 5h7v7H4zM13 5h7v4h-7zM13 11h7v8h-7zM4 14h7v5H4z'],
        ['videos',     'Videá',                'M3 6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM10 9v6l5-3z'],
        ['media',      'Médiá',                'M12 4v12m0 0l-4-4m4 4l4-4M4 20h16'],
    ]],
    ['Nastavenia', [
        ['announce', 'Oznam hore',         'M4 10v4h3l5 4V6L7 10zM16 9a4 4 0 0 1 0 6'],
        ['site',     'Kontakt a údaje',    'M5 4h4l2 5-2.500 1.500a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z'],
        ['settings', 'Poukážky a rezervácie', 'M3 8h18v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1zM3 8l2-4h14l2 4M12 8v11'],
        ['password', 'Heslo',              'M6 11V8a6 6 0 0 1 12 0v3M5 11h14v9H5z'],
        ['backups',  'Zálohy',             'M4 7c0-1.700 3.600-3 8-3s8 1.300 8 3-3.600 3-8 3-8-1.300-8-3zM4 7v5c0 1.700 3.600 3 8 3s8-1.300 8-3V7M4 12v5c0 1.700 3.600 3 8 3s8-1.300 8-3v-5'],
    ]],
];

function admin_head(string $title, string $active = 'home', string $sub = ''): void {
    global $site;
    $flashes = flashes();
    ?><!doctype html>
<html lang="sk">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($title) ?> | Admin – <?= e($site['name']) ?></title>
  <link rel="icon" href="../favicon.ico" sizes="48x48">
  <link rel="stylesheet" href="../assets/css/fonts.css">
  <link rel="stylesheet" href="admin.css?v=1">
</head>
<body class="admin">
<div class="shell">
  <aside class="side" id="side">
    <a class="side-brand" href="index.php"><img src="../assets/img/logo-white.png" alt="<?= e($site['name']) ?>" width="150"><span>Správa webu</span></a>
    <nav aria-label="Admin menu">
      <?php foreach (ADMIN_NAV as [$group, $items]): ?>
        <?php if ($group !== 'Prehľad'): ?><h3><?= e($group) ?></h3><?php endif; ?>
        <?php foreach ($items as [$id, $label, $path]): ?>
          <a href="<?= e(admin_url($id)) ?>"<?= $id === $active ? ' aria-current="page"' : '' ?>><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="<?= e($path) ?>"/></svg><?= e($label) ?></a>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </nav>
    <div class="side-foot">
      <a href="../index.php" target="_blank" rel="noopener">Zobraziť web ↗</a>
      <form method="post" action="logout.php"><?= csrf_field() ?><button type="submit">Odhlásiť sa</button></form>
    </div>
  </aside>
  <div class="main">
    <header class="top">
      <button type="button" class="menu-btn" id="menuBtn" aria-label="Menu" aria-expanded="false" aria-controls="side"><span></span><span></span></button>
      <div><h1><?= e($title) ?></h1><?php if ($sub): ?><p><?= e($sub) ?></p><?php endif; ?></div>
    </header>
    <main class="content">
      <?php foreach ($flashes as [$type, $msg]): ?><div class="flash <?= e($type) ?>" role="status"><?= e($msg) ?></div><?php endforeach; ?>
<?php }

function admin_foot(): void { ?>
    </main>
  </div>
</div>
<script src="admin.js?v=1" defer data-csrf="<?= e(csrf_token()) ?>"></script>
</body>
</html>
<?php }

/* ---------- prvky formulárov ---------- */

function f_text(string $name, string $label, string $value = '', array $o = []): void {
    $hint = $o['hint'] ?? ''; $max = (int)($o['max'] ?? 200); $type = $o['type'] ?? 'text';
    ?><div class="fld<?= !empty($o['class']) ? ' ' . e($o['class']) : '' ?>"><label><span><?= e($label) ?></span>
      <input type="<?= e($type) ?>" name="<?= e($name) ?>" value="<?= e($value) ?>" maxlength="<?= $max ?>"<?= !empty($o['placeholder']) ? ' placeholder="' . e($o['placeholder']) . '"' : '' ?><?= !empty($o['required']) ? ' required' : '' ?><?= isset($o['min']) ? ' min="' . (int)$o['min'] . '"' : '' ?><?= isset($o['max_n']) ? ' max="' . (int)$o['max_n'] . '"' : '' ?><?= !empty($o['attrs']) ? ' ' . $o['attrs'] : '' ?>></label>
      <?php if ($hint): ?><small><?= e($hint) ?></small><?php endif; ?></div>
<?php }

function f_area(string $name, string $label, string $value = '', array $o = []): void {
    $hint = $o['hint'] ?? ''; $max = (int)($o['max'] ?? 1500); $rows = (int)($o['rows'] ?? 3);
    ?><div class="fld<?= !empty($o['class']) ? ' ' . e($o['class']) : '' ?>"><label><span><?= e($label) ?></span>
      <textarea name="<?= e($name) ?>" rows="<?= $rows ?>" maxlength="<?= $max ?>"><?= e($value) ?></textarea></label>
      <?php if ($hint): ?><small><?= e($hint) ?></small><?php endif; ?></div>
<?php }

/** @param array<string,string> $options hodnota => popis */
function f_select(string $name, string $label, array $options, string $value = '', array $o = []): void {
    ?><div class="fld<?= !empty($o['class']) ? ' ' . e($o['class']) : '' ?>"><label><span><?= e($label) ?></span>
      <select name="<?= e($name) ?>"><?php foreach ($options as $v => $l): ?><option value="<?= e((string)$v) ?>"<?= (string)$v === $value ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
      <?php if (!empty($o['hint'])): ?><small><?= e($o['hint']) ?></small><?php endif; ?></div>
<?php }

function f_check(string $name, string $label, bool $checked, string $hint = ''): void {
    ?><div class="fld"><label class="chk"><input type="checkbox" name="<?= e($name) ?>" value="1"<?= $checked ? ' checked' : '' ?>><span><?= e($label) ?></span></label><?php if ($hint): ?><small><?= e($hint) ?></small><?php endif; ?></div>
<?php }

/** Výber obrázka z knižnice médií + nahratie nového. */
function f_image(string $name, string $label, string $value = '', array $o = []): void {
    $files = media_files('jpg'); $optional = $o['optional'] ?? true;
    if ($value !== '' && !isset($files[$value]) && is_file(dirname(__DIR__, 2) . '/assets/media/' . $value)) $files[$value] = 'Použité';
    $groups = [];
    foreach ($files as $f => $g) $groups[$g][] = $f;
    ?><div class="fld picker<?= !empty($o['class']) ? ' ' . e($o['class']) : '' ?>" data-picker>
      <span class="lbl"><?= e($label) ?></span>
      <div class="pk">
        <img class="pk-prev" src="<?= $value !== '' ? '../assets/media/' . e($value) : 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' ?>" alt="" width="96" height="72">
        <div class="pk-ctl">
          <select name="<?= e($name) ?>">
            <?php if ($optional): ?><option value="">— žiadny obrázok —</option><?php endif; ?>
            <?php foreach ($groups as $g => $list): ?><optgroup label="<?= e($g) ?>"><?php foreach ($list as $f): ?><option value="<?= e($f) ?>"<?= $f === $value ? ' selected' : '' ?>><?= e($f) ?></option><?php endforeach; ?></optgroup><?php endforeach; ?>
          </select>
          <label class="btn-up">Nahrať nový obrázok<input type="file" accept="image/jpeg,image/png,image/webp" hidden></label>
          <small class="pk-msg" aria-live="polite"></small>
        </div>
      </div>
    </div>
<?php }

function f_actions(string $reset = ''): void { ?>
  <div class="actions">
    <button class="btn primary" type="submit" name="save" value="1">Uložiť zmeny</button>
    <?php if ($reset): ?><button class="btn ghost danger" type="submit" name="reset" value="1" data-confirm="<?= e($reset) ?>">Obnoviť pôvodné</button><?php endif; ?>
  </div>
<?php }

/** Uloží do content.json a nastaví hlášku. @return bool uspelo */
function save_content(string $key, mixed $value, string $okMsg = 'Zmeny sú uložené a zobrazia sa na webe.'): bool {
    $r = content_set($key, $value);
    if ($r === true) { flash($okMsg); return true; }
    flash((string)$r, 'err'); return false;
}
function reset_content(string $key, string $msg = 'Obnovené pôvodné hodnoty.'): void {
    $r = content_unset($key);
    $r === true ? flash($msg) : flash((string)$r, 'err');
}

/** Zoznam tried v tvare vhodnom na uloženie (id + polia). */
function classes_as_list(array $classes): array {
    $out = [];
    foreach ($classes as $id => $c) {
        $out[] = ['id' => $id, 'name' => $c['name'], 'short' => $c['short'] ?? '', 'blurb' => $c['blurb'] ?? '', 'tag' => $c['tag'] ?? '', 'lead' => $c['lead'] ?? '', 'text' => $c['text'] ?? '',
                  'equipment' => $c['equipment'] ?? '', 'capacity' => (int)$c['capacity'], 'duration' => (int)$c['duration'], 'color' => $c['color'] ?? '#4a7c77',
                  'for' => $c['for'] ?? [], 'images' => $c['images'] ?? [], 'video' => $c['video'] ?? null, 'pos' => $c['pos'] ?? '', 'price' => $c['price'] ?? ''];
    }
    return $out;
}

function days_sk(): array { return [1 => 'Pondelok', 2 => 'Utorok', 3 => 'Streda', 4 => 'Štvrtok', 5 => 'Piatok', 6 => 'Sobota', 7 => 'Nedeľa']; }

function fmt_dt(int $ts): string { return date('j. n. Y H:i', $ts); }

/* ---------- limity nahrávania ---------- */

function ini_bytes(string $v): int {
    $v = trim($v); if ($v === '') return 0;
    $n = (float)$v; $u = strtolower(substr($v, -1));
    return (int)($n * match ($u) { 'g' => 1 << 30, 'm' => 1 << 20, 'k' => 1 << 10, default => 1 });
}
/** Najväčšie povolené nahrávané video v bajtoch (podľa limitov PHP; malá rezerva na úvodný obrázok a hlavičky). */
function upload_limit_bytes(): int {
    $a = ini_bytes((string)ini_get('upload_max_filesize')) ?: (2 << 20);
    $b = ini_bytes((string)ini_get('post_max_size'));            // 0 = bez obmedzenia
    $lim = $b > 0 ? min($a, $b) : $a;
    return max(0, $lim - 400 * 1024);
}
function format_bytes(int $b): string { return $b >= 1 << 20 ? round($b / (1 << 20), 1) . ' MB' : round($b / 1024) . ' kB'; }
