<?php
declare(strict_types=1);
/**
 * Nahrávanie videa (AJAX). Prijíma MP4 + úvodný obrázok (poster), ktorý vytvorí prehliadač z prvého záberu.
 * Video sa overí podľa obsahu (musí to byť MP4), poster sa prekóduje cez GD na JPEG. Súbory sa uložia do
 * assets/media/uploads/<názov>.mp4 a <názov>-poster.jpg. Video sa na serveri neprekódováva.
 */
define('ADMIN', true);
require __DIR__ . '/inc/lib.php';
admin_headers();
header('Content-Type: application/json; charset=utf-8');
admin_session();

function vo(bool $ok, array $d = [], int $code = 200): never {
    http_response_code($code);
    echo json_encode(['ok' => $ok] + $d, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!is_logged_in()) vo(false, ['message' => 'Nie ste prihlásený.'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') vo(false, ['message' => 'Neplatná požiadavka.'], 405);
if (!csrf_valid()) vo(false, ['message' => 'Platnosť stránky vypršala. Obnovte ju a skúste znova.'], 400);

// príliš veľký POST PHP zahodí ešte pred spracovaním – $_FILES aj $_POST sú potom prázdne
if (empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    vo(false, ['message' => 'Súbor je väčší než limit servera (' . format_bytes(upload_limit_bytes()) . '). Zmenšite video a skúste znova.'], 413);
}

$v = $_FILES['video'] ?? null;
$p = $_FILES['poster'] ?? null;
if (!$v || ($v['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    $err = (int)($v['error'] ?? UPLOAD_ERR_NO_FILE);
    vo(false, ['message' => in_array($err, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
        ? 'Video je väčšie než limit servera (' . format_bytes(upload_limit_bytes()) . '). Zmenšite ho a skúste znova.'
        : 'Video sa nepodarilo nahrať.'], 400);
}
if (!$p || ($p['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) vo(false, ['message' => 'Chýba úvodný obrázok videa. Skúste video nahrať znova.'], 400);
if (!is_uploaded_file($v['tmp_name']) || !is_uploaded_file($p['tmp_name'])) vo(false, ['message' => 'Neplatný súbor.'], 400);
if ($v['size'] > upload_limit_bytes()) vo(false, ['message' => 'Video je väčšie než limit servera (' . format_bytes(upload_limit_bytes()) . ').'], 413);

// video musí byť MP4: podpis „ftyp“ na začiatku a zodpovedajúci typ obsahu
$fh = @fopen($v['tmp_name'], 'rb');
$head = $fh ? (string)fread($fh, 16) : '';
if ($fh) fclose($fh);
$mime = function_exists('finfo_open') ? (string)finfo_file(finfo_open(FILEINFO_MIME_TYPE), $v['tmp_name']) : 'video/mp4';
if (strlen($head) < 12 || substr($head, 4, 4) !== 'ftyp' || !in_array($mime, ['video/mp4', 'application/mp4', 'application/octet-stream'], true)) {
    vo(false, ['message' => 'Podporovaný je len formát MP4 (H.264). Iný formát najprv prevedťte na MP4.'], 400);
}
if (substr($head, 8, 4) === 'qt  ') vo(false, ['message' => 'Súbory MOV nie sú podporované. Uložte video ako MP4 (H.264).'], 400);

// úvodný obrázok -> JPEG
if (!function_exists('imagecreatetruecolor')) vo(false, ['message' => 'Server nepodporuje spracovanie obrázkov (chýba GD).'], 500);
$info = @getimagesize($p['tmp_name']);
if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true) || $p['size'] > 6 * 1024 * 1024) vo(false, ['message' => 'Úvodný obrázok videa je neplatný.'], 400);
$im = match ($info[2]) {
    IMAGETYPE_JPEG => @imagecreatefromjpeg($p['tmp_name']),
    IMAGETYPE_PNG  => @imagecreatefrompng($p['tmp_name']),
    default        => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($p['tmp_name']) : false,
};
if (!$im) vo(false, ['message' => 'Úvodný obrázok sa nepodarilo načítať.'], 400);
[$w, $h] = [imagesx($im), imagesy($im)];
$scale = min(1.0, 720 / max(1, $w));
$nw = max(1, (int)round($w * $scale)); $nh = max(1, (int)round($h * $scale));
$out = imagecreatetruecolor($nw, $nh);
imagefill($out, 0, 0, imagecolorallocate($out, 0, 0, 0));
imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);

$dir = dirname(__DIR__) . '/assets/media/uploads';
if (!is_dir($dir) && !@mkdir($dir, 0755, true)) vo(false, ['message' => 'Priečinok na nahrávanie sa nepodarilo vytvoriť.'], 500);
if (!is_writable($dir)) vo(false, ['message' => 'Do priečinka assets/media/uploads sa nedá zapisovať (nastavte oprávnenia).'], 500);

$base = 'v' . date('Ymd') . '-' . bin2hex(random_bytes(5));
if (!@imagejpeg($out, "$dir/$base-poster.jpg", 82)) vo(false, ['message' => 'Úvodný obrázok sa nepodarilo uložiť.'], 500);
if (!@move_uploaded_file($v['tmp_name'], "$dir/$base.mp4")) { @unlink("$dir/$base-poster.jpg"); vo(false, ['message' => 'Video sa nepodarilo uložiť.'], 500); }

$title = in_line(preg_replace('/\.[A-Za-z0-9]{2,4}$/', '', (string)($v['name'] ?? '')), 60);
vo(true, ['file' => 'uploads/' . $base, 'poster' => '../assets/media/uploads/' . $base . '-poster.jpg', 'title' => $title, 'size' => (int)$v['size']]);
