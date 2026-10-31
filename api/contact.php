<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/data.php';
header('Content-Type: application/json; charset=utf-8');

function out(bool $ok, string $message, int $code = 200): never {
    http_response_code($code);
    echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Neplatná požiadavka.', 405);
if (!empty($_POST['website'])) out(true, 'Ďakujeme, správa bola odoslaná.'); // honeypot – bot

$name    = trim((string)($_POST['name'] ?? ''));
$email   = trim((string)($_POST['email'] ?? ''));
$phone   = trim((string)($_POST['phone'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

if (($_POST['gdpr'] ?? '') !== '1') {
    out(false, 'Na odoslanie správy je potrebný súhlas so spracovaním osobných údajov.', 422);
}
if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    out(false, 'Vyplňte prosím meno, platný e-mail a správu.', 422);
}
if ($phone !== '' && !preg_match('/^[0-9+()\/\-\s]{6,30}$/', $phone)) {
    out(false, 'Telefónne číslo má neplatný formát.', 422);
}
if (mb_strlen($name) > 120 || mb_strlen($message) > 5000) out(false, 'Správa je príliš dlhá.', 422);
// zabráni vloženiu hlavičiek do e-mailu
if (preg_match('/[\r\n]/', $name . $email . $phone)) out(false, 'Neplatné údaje.', 422);

$subject = '=?UTF-8?B?' . base64_encode('Správa z webu – ' . $name) . '?=';
$body    = "Meno: $name\nE-mail: $email\nTelefón: " . ($phone !== '' ? $phone : '–') . "\nSúhlas so spracovaním údajov: áno (" . date('j. n. Y H:i') . ")\n\n$message\n";
$headers = [
    'From' => $site['email'],
    'Reply-To' => $email,
    'Content-Type' => 'text/plain; charset=UTF-8',
];
$ok = @mail($site['email'], $subject, $body, $headers);

$ok ? out(true, 'Ďakujeme, správa bola odoslaná. Ozveme sa vám.')
    : out(false, 'Správu sa nepodarilo odoslať. Skúste to prosím neskôr alebo nám zavolajte.', 500);
