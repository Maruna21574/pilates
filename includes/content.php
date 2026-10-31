<?php
/**
 * Úložisko obsahu upravovaného v admin paneli.
 *
 * Pôvodné (predvolené) hodnoty sú v includes/data.php. Admin ukladá zmeny do data/content.json
 * a pri každom načítaní stránky sa prekryjú cez predvolené hodnoty. Kľúč, ktorý v súbore chýba,
 * ostáva na predvolenej hodnote, takže „Obnoviť pôvodné“ znamená len zmazať príslušný kľúč.
 *
 * Priečinok data/ je zablokovaný cez .htaccess (nie je dostupný z prehliadača).
 */

const CONTENT_KEEP_BACKUPS = 40;

function content_dir(): string  { return dirname(__DIR__) . '/data'; }
function content_file(): string { return content_dir() . '/content.json'; }

/** Načíta uložený obsah; pri chýbajúcom alebo poškodenom súbore vráti prázdne pole (web ostane na predvolených hodnotách). */
function content_load(): array {
    $f = content_file();
    if (!is_file($f)) return [];
    $raw = @file_get_contents($f);
    $data = $raw === false ? null : json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/** Atomický zápis s vytvorením zálohy predchádzajúcej verzie. Vráti true alebo text chyby. */
function content_save(array $data): bool|string {
    $dir = content_dir();
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) return 'Priečinok data/ neexistuje a nepodarilo sa ho vytvoriť. Skontrolujte oprávnenia.';
    if (!is_writable($dir)) return 'Do priečinka data/ sa nedá zapisovať. Nastavte mu oprávnenia na zápis (napr. 755 alebo 775).';

    $lock = @fopen($dir . '/.lock', 'c');
    if ($lock) flock($lock, LOCK_EX);
    try {
        $file = content_file();
        if (is_file($file)) {
            $bdir = $dir . '/backups';
            if (!is_dir($bdir)) @mkdir($bdir, 0755, true);
            if (is_dir($bdir) && is_writable($bdir)) {
                @copy($file, $bdir . '/content-' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(2)), 0, 4) . '.json');
                $all = glob($bdir . '/content-*.json') ?: [];
                sort($all);
                foreach (array_slice($all, 0, max(0, count($all) - CONTENT_KEEP_BACKUPS)) as $old) @unlink($old);
            }
        }
        $data['_updated'] = date('c');
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($json === false) return 'Obsah sa nepodarilo zakódovať.';
        $tmp = $file . '.tmp' . bin2hex(random_bytes(3));
        if (@file_put_contents($tmp, $json, LOCK_EX) === false) return 'Zápis do súboru zlyhal.';
        if (!@rename($tmp, $file)) { @unlink($tmp); return 'Súbor sa nepodarilo uložiť.'; }
        return true;
    } finally {
        if ($lock) { flock($lock, LOCK_UN); fclose($lock); }
    }
}

function content_set(string $key, mixed $value): bool|string {
    $c = content_load(); $c[$key] = $value; return content_save($c);
}
function content_unset(string $key): bool|string {
    $c = content_load(); unset($c[$key]); return content_save($c);
}

/** @return array<int,array{name:string,time:int,size:int}> najnovšie prvé */
function content_backups(): array {
    $out = [];
    foreach (glob(content_dir() . '/backups/content-*.json') ?: [] as $f) {
        $out[] = ['name' => basename($f), 'time' => (int)filemtime($f), 'size' => (int)filesize($f)];
    }
    usort($out, fn($a, $b) => strcmp($b['name'], $a['name']));
    return $out;
}

function content_restore(string $name): bool|string {
    if (!preg_match('/^content-[0-9]{8}-[0-9]{6}-[0-9a-f]{4}\.json$/', $name)) return 'Neplatný názov zálohy.';
    $f = content_dir() . '/backups/' . $name;
    if (!is_file($f)) return 'Záloha sa nenašla.';
    $data = json_decode((string)file_get_contents($f), true);
    if (!is_array($data)) return 'Záloha je poškodená.';
    unset($data['_updated']);
    return content_save($data);
}

/* ---------- prekrytie predvolených hodnôt ---------- */

function c_str(mixed $v, string $fallback = ''): string { return is_string($v) || is_numeric($v) ? trim((string)$v) : $fallback; }
function c_lines(mixed $v): array {
    if (!is_array($v)) return [];
    return array_values(array_filter(array_map(fn($x) => c_str($x), $v), fn($x) => $x !== ''));
}

/**
 * @param array $d predvolené hodnoty (site, classes, individual, team, schedule, faq, reviews, announcement, settings, hero_slides, gallery, texts)
 * @param array $o hodnoty z content.json
 * @return array rovnaké kľúče ako $d, s uplatnenými zmenami
 */
function content_apply(array $d, array $o): array {
    $r = $d;

    if (isset($o['site']) && is_array($o['site'])) {
        foreach (['name', 'tagline', 'city', 'address', 'phone', 'phone_person', 'email', 'instagram', 'facebook'] as $k) {
            if (isset($o['site'][$k]) && is_string($o['site'][$k]) && trim($o['site'][$k]) !== '') $r['site'][$k] = trim($o['site'][$k]);
        }
    }
    $r['site']['phone_href'] = preg_replace('/[^0-9+]/', '', $r['site']['phone']);
    $r['site']['maps'] = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($r['site']['address']);

    if (array_key_exists('announcement', $o)) {
        $a = $o['announcement'];
        $r['announcement'] = (is_array($a) && !empty($a['enabled']) && c_str($a['text'] ?? '') !== '')
            ? ['text' => c_str($a['text']), 'link' => c_str($a['link'] ?? ''), 'link_text' => c_str($a['link_text'] ?? '') ?: 'Viac']
            : null;
    }

    if (isset($o['classes']) && is_array($o['classes'])) {
        $list = [];
        foreach ($o['classes'] as $c) {
            if (!is_array($c) || empty($c['id']) || !preg_match('/^[a-z0-9-]{1,40}$/', (string)$c['id'])) continue;
            $id = (string)$c['id'];
            $base = $d['classes'][$id] ?? ['short' => '', 'icon' => 'mat', 'video' => null, 'pos' => '', 'price' => '', 'blurb' => '', 'equipment' => '', 'for' => [], 'images' => []];
            $row = $base;
            foreach (['name', 'short', 'blurb', 'tag', 'lead', 'text', 'equipment', 'pos', 'price'] as $k) if (array_key_exists($k, $c)) $row[$k] = c_str($c[$k]);
            $row['capacity'] = max(1, (int)($c['capacity'] ?? $base['capacity'] ?? 1));
            $row['duration'] = max(5, (int)($c['duration'] ?? $base['duration'] ?? 55));
            $row['color']    = preg_match('/^#[0-9a-fA-F]{6}$/', (string)($c['color'] ?? '')) ? (string)$c['color'] : ($base['color'] ?? '#4a7c77');
            $row['for']      = array_key_exists('for', $c) ? c_lines($c['for']) : ($base['for'] ?? []);
            $row['images']   = array_key_exists('images', $c) ? c_lines($c['images']) : ($base['images'] ?? []);
            $row['video']    = array_key_exists('video', $c) ? (c_str($c['video']) ?: null) : ($base['video'] ?? null);
            if (c_str($row['name'] ?? '') === '') continue;
            $list[$id] = $row;
        }
        if ($list) $r['classes'] = $list;
    }

    if (isset($o['individual']) && is_array($o['individual'])) {
        $i = $o['individual']; $b = $d['individual'];
        foreach (['name', 'tag', 'lead', 'text', 'pos', 'price'] as $k) if (array_key_exists($k, $i)) $r['individual'][$k] = c_str($i[$k]);
        if (array_key_exists('for', $i))    $r['individual']['for'] = c_lines($i['for']);
        if (array_key_exists('images', $i)) $r['individual']['images'] = c_lines($i['images']);
        if (c_str($r['individual']['name'] ?? '') === '') $r['individual']['name'] = $b['name'];
    }

    if (isset($o['team']) && is_array($o['team'])) {
        $list = [];
        foreach ($o['team'] as $m) {
            if (!is_array($m) || c_str($m['name'] ?? '') === '') continue;
            $name = c_str($m['name']);
            $ini = '';
            foreach (preg_split('/\s+/u', $name) ?: [] as $w) { if ($w !== '' && mb_strlen($ini) < 2) $ini .= mb_strtoupper(mb_substr($w, 0, 1)); }
            $list[] = ['name' => $name, 'role' => c_str($m['role'] ?? ''), 'initials' => $ini, 'photo' => c_str($m['photo'] ?? '') ?: null,
                       'bio' => c_str($m['bio'] ?? ''), 'focus' => c_lines($m['focus'] ?? [])];
        }
        $r['team'] = $list;
    }

    if (isset($o['schedule']) && is_array($o['schedule'])) {
        $list = [];
        foreach ($o['schedule'] as $s) {
            if (!is_array($s)) continue;
            $cls = c_str($s['class'] ?? ''); $day = (int)($s['day'] ?? 0); $time = c_str($s['time'] ?? '');
            if (!isset($r['classes'][$cls]) || $day < 1 || $day > 7 || !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $time)) continue;
            $list[] = ['class' => $cls, 'day' => $day, 'time' => $time];
        }
        $r['schedule'] = $list;
    } else {
        $r['schedule'] = array_values(array_filter($r['schedule'], fn($s) => isset($r['classes'][$s['class']])));
    }

    if (isset($o['faq']) && is_array($o['faq'])) {
        $list = [];
        foreach ($o['faq'] as $q) {
            if (is_array($q) && c_str($q[0] ?? '') !== '' && c_str($q[1] ?? '') !== '') $list[] = [c_str($q[0]), c_str($q[1])];
        }
        $r['faq'] = $list;
    }

    if (isset($o['reviews']) && is_array($o['reviews'])) {
        $list = [];
        foreach ($o['reviews'] as $v) {
            if (!is_array($v) || c_str($v['text'] ?? '') === '') continue;
            $list[] = ['name' => c_str($v['name'] ?? '') ?: 'Klientka', 'date' => c_str($v['date'] ?? ''), 'rating' => max(1, min(5, (int)($v['rating'] ?? 5))),
                       'source' => (($v['source'] ?? '') === 'google') ? 'google' : '', 'text' => c_str($v['text'])];
        }
        $r['reviews'] = $list;
    }

    if (isset($o['hero_slides']) && is_array($o['hero_slides'])) {
        $list = [];
        foreach ($o['hero_slides'] as $s) {
            if (is_array($s) && c_str($s['file'] ?? '') !== '') $list[] = ['file' => c_str($s['file']), 'alt' => c_str($s['alt'] ?? '')];
        }
        if ($list) $r['hero_slides'] = $list;
    }

    if (isset($o['gallery']) && is_array($o['gallery'])) {
        $list = [];
        foreach ($o['gallery'] as $g) {
            if (is_array($g) && c_str($g['file'] ?? '') !== '') $list[] = ['file' => c_str($g['file']), 'cat' => c_str($g['cat'] ?? '')];
        }
        $r['gallery'] = $list;
    }

    if (isset($o['videos']) && is_array($o['videos'])) {
        $list = [];
        foreach ($o['videos'] as $v) {
            if (!is_array($v)) continue;
            $f = c_str($v['file'] ?? '');
            if (!video_exists($f)) continue;
            $list[] = ['file' => $f, 'title' => c_str($v['title'] ?? ''), 'home' => !empty($v['home']), 'gallery' => !empty($v['gallery'])];
        }
        $r['videos'] = $list;
    }

    if (isset($o['texts']) && is_array($o['texts'])) {
        foreach ($o['texts'] as $k => $v) if (is_string($k) && is_string($v) && isset($d['texts'][$k]) && trim($v) !== '') $r['texts'][$k] = trim($v);
    }

    if (isset($o['settings']) && is_array($o['settings'])) {
        $s = $o['settings'];
        if (isset($s['voucher_validity_months'])) $r['settings']['voucher_validity_months'] = max(1, min(60, (int)$s['voucher_validity_months']));
        if (isset($s['book_months']))             $r['settings']['book_months'] = max(1, min(24, (int)$s['book_months']));
        if (array_key_exists('google_reviews_url', $s)) $r['settings']['google_reviews_url'] = c_str($s['google_reviews_url']) ?: null;
        if (array_key_exists('pass_price', $s))   $r['settings']['pass_price'] = c_str($s['pass_price']);
    }
    return $r;
}

/* ---------- videá ---------- */

/** Video je odkaz bez prípony: 'reformer-v1' alebo 'uploads/abc'. Musí existovať súbor .mp4 aj úvodný obrázok (poster). */
function video_ref_ok(string $ref): bool { return (bool)preg_match('#^(uploads/)?[A-Za-z0-9._-]+$#', $ref) && !str_contains($ref, '..'); }
function video_poster_path(string $ref): string { return str_starts_with($ref, 'uploads/') ? $ref . '-poster.jpg' : $ref . '.jpg'; }
function video_exists(string $ref): bool {
    if (!video_ref_ok($ref)) return false;
    $root = dirname(__DIR__) . '/assets/media/';
    return is_file($root . $ref . '.mp4') && is_file($root . video_poster_path($ref));
}

/* ---------- náhľady obrázkov ---------- */

/**
 * Malý náhľad (400×320, orezaný na výplň) pre menu. Vytvorí sa pri prvom použití a uloží do assets/media/thumbs/.
 * Ak sa vytvoriť nedá (chýba GD alebo oprávnenia), vráti pôvodný obrázok.
 */
function media_thumb(string $file): string {
    $root = dirname(__DIR__) . '/assets/media/';
    $src  = $root . $file;
    if (!is_file($src)) return 'assets/media/' . $file;
    $name  = preg_replace('/[^A-Za-z0-9._-]/', '_', str_replace('/', '__', $file));
    $thumb = $root . 'thumbs/' . $name;
    $url   = 'assets/media/thumbs/' . $name;
    if (is_file($thumb) && filemtime($thumb) >= filemtime($src)) return $url;
    if (!function_exists('imagecreatetruecolor') || !is_dir($root . 'thumbs') || !is_writable($root . 'thumbs')) return 'assets/media/' . $file;
    $info = @getimagesize($src);
    $im = null;
    if ($info) {
        $im = match ($info[2]) { IMAGETYPE_JPEG => @imagecreatefromjpeg($src), IMAGETYPE_PNG => @imagecreatefrompng($src),
                                 IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($src) : null, default => null };
    }
    if (!$im) return 'assets/media/' . $file;
    [$w, $h] = [imagesx($im), imagesy($im)];
    $tw = 400; $th = 320; $ar = $tw / $th;
    if ($w / $h > $ar) { $ch = $h; $cw = (int)round($ch * $ar); } else { $cw = $w; $ch = (int)round($cw / $ar); }
    $cx = (int)(($w - $cw) / 2); $cy = (int)(($h - $ch) * 0.3);
    $out = imagecreatetruecolor($tw, $th);
    imagecopyresampled($out, $im, 0, 0, $cx, $cy, $tw, $th, $cw, $ch);
    $ok = @imagejpeg($out, $thumb, 80);
    imagedestroy($im); imagedestroy($out);
    return $ok ? $url : 'assets/media/' . $file;
}

/** Zoznam dostupných obrázkov/videí v assets/media (pre výber v admine). @return array<string,string> súbor => skupina */
function media_files(string $ext = 'jpg'): array {
    $root = dirname(__DIR__) . '/assets/media/';
    $out = [];
    $exts = $ext === 'mp4' ? ['mp4'] : ['jpg', 'jpeg', 'png', 'webp'];
    foreach (glob($root . '*') ?: [] as $f) {
        if (!is_file($f) || !in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $exts, true)) continue;
        if ($ext !== 'mp4' && preg_match('/-v[0-9]+\.jpg$/', $f)) continue;      // náhľady videí nie sú samostatné fotky
        $out[basename($f)] = 'Pôvodné';
    }
    foreach (glob($root . 'uploads/*') ?: [] as $f) {
        if (is_file($f) && in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $exts, true) && !str_ends_with($f, '-poster.jpg')) $out['uploads/' . basename($f)] = 'Nahrané';
    }
    ksort($out);
    return $out;
}
