<?php
declare(strict_types=1);
/**
 * Nahrávanie obrázkov (AJAX). Súbor sa overí podľa skutočného obsahu, prekóduje do JPEG
 * (odstráni sa EXIF/skryté dáta a nebezpečný obsah), zmenší a uloží do assets/media/uploads/.
 */
define('ADMIN', true);
require __DIR__ . '/inc/lib.php';
admin_headers();
header('Content-Type: application/json; charset=utf-8');
admin_session();

function up_out(bool $ok, array $d = [], int $code = 200): never {
    http_response_code($code);
    echo json_encode(['ok' => $ok] + $d, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!is_logged_in()) up_out(false, ['message' => 'Nie ste prihlásený.'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') up_out(false, ['message' => 'Neplatná požiadavka.'], 405);
if (!csrf_valid()) up_out(false, ['message' => 'Platnosť stránky vypršala. Obnovte ju a skúste znova.'], 400);

$f = $_FILES['file'] ?? null;
if (!$f || !is_array($f) || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    $err = (int)($f['error'] ?? UPLOAD_ERR_NO_FILE);
    $msg = in_array($err, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
        ? 'Súbor je príliš veľký (limit servera je ' . ini_get('upload_max_filesize') . ').'
        : 'Súbor sa nepodarilo nahrať.';
    up_out(false, ['message' => $msg], 400);
}
if (!is_uploaded_file($f['tmp_name'])) up_out(false, ['message' => 'Neplatný súbor.'], 400);
if ($f['size'] > 12 * 1024 * 1024) up_out(false, ['message' => 'Obrázok môže mať najviac 12 MB.'], 400);
if (!function_exists('imagecreatetruecolor')) up_out(false, ['message' => 'Server nepodporuje spracovanie obrázkov (chýba GD).'], 500);

$info = @getimagesize($f['tmp_name']);
$type = $info[2] ?? 0;
$mime = function_exists('finfo_open') ? (string)finfo_file(finfo_open(FILEINFO_MIME_TYPE), $f['tmp_name']) : '';
if (!$info || !in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true) || ($mime !== '' && !in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true))) {
    up_out(false, ['message' => 'Podporované sú len obrázky JPG, PNG a WebP.'], 400);
}
if ($info[0] * $info[1] > 40_000_000) up_out(false, ['message' => 'Obrázok má príliš veľké rozlíšenie.'], 400);

$im = match ($type) {
    IMAGETYPE_JPEG => @imagecreatefromjpeg($f['tmp_name']),
    IMAGETYPE_PNG  => @imagecreatefrompng($f['tmp_name']),
    default        => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($f['tmp_name']) : false,
};
if (!$im) up_out(false, ['message' => 'Obrázok sa nepodarilo načítať.'], 400);

// natočenie podľa EXIF (fotky z mobilu)
if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
    $exif = @exif_read_data($f['tmp_name']);
    $o = (int)($exif['Orientation'] ?? 1);
    $rot = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
    if ($rot) { $r = imagerotate($im, $rot, 0); if ($r) $im = $r; }
}
[$w, $h] = [imagesx($im), imagesy($im)];
$max = 1800; $scale = min(1.0, $max / max($w, $h));
$nw = max(1, (int)round($w * $scale)); $nh = max(1, (int)round($h * $scale));
$out = imagecreatetruecolor($nw, $nh);
imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));        // priehľadnosť PNG -> biele pozadie
imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);

$dir = dirname(__DIR__) . '/assets/media/uploads';
if (!is_dir($dir) && !@mkdir($dir, 0755, true)) up_out(false, ['message' => 'Priečinok na nahrávanie sa nepodarilo vytvoriť.'], 500);
if (!is_writable($dir)) up_out(false, ['message' => 'Do priečinka assets/media/uploads sa nedá zapisovať (nastavte oprávnenia).'], 500);
$name = date('Ymd') . '-' . bin2hex(random_bytes(5)) . '.jpg';
if (!@imagejpeg($out, $dir . '/' . $name, 85)) up_out(false, ['message' => 'Obrázok sa nepodarilo uložiť.'], 500);

up_out(true, ['file' => 'uploads/' . $name, 'url' => '../assets/media/uploads/' . $name, 'w' => $nw, 'h' => $nh]);
