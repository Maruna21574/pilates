<?php
declare(strict_types=1);
/** Objednávka darčekovej poukážky: e-mail štúdiu + potvrdenie o prijatí objednávky kupujúcemu. */
require_once __DIR__ . '/../includes/data.php';
header('Content-Type: application/json; charset=utf-8');

function out(bool $ok, string $message, array $extra = [], int $code = 200): never {
    http_response_code($code);
    echo json_encode(['ok' => $ok, 'message' => $message] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Neplatná požiadavka.', [], 405);
if (!empty($_POST['website'])) out(true, 'Ďakujeme.'); // honeypot – bot

$type       = (string)($_POST['type'] ?? '');
$amount     = (string)($_POST['amount'] ?? '');
$count      = (string)($_POST['count'] ?? '1');
$recipient  = trim((string)($_POST['recipient'] ?? ''));
$dedication = trim((string)($_POST['dedication'] ?? ''));
$delivery   = ($_POST['delivery'] ?? '') === 'osobne' ? 'osobne v štúdiu' : 'elektronicky (e-mailom)';
$name       = trim((string)($_POST['name'] ?? ''));
$email      = trim((string)($_POST['email'] ?? ''));
$phone      = trim((string)($_POST['phone'] ?? ''));

if (($_POST['gdpr'] ?? '') !== '1') out(false, 'Na odoslanie objednávky je potrebný súhlas so spracovaním osobných údajov.', [], 422);
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) out(false, 'Vyplňte prosím meno a platný e-mail.', [], 422);
if (!preg_match('/^[0-9+()\/\-\s]{6,30}$/', $phone)) out(false, 'Vyplňte prosím platné telefónne číslo.', [], 422);
if (mb_strlen($name) > 120 || mb_strlen($recipient) > 120 || mb_strlen($dedication) > 400) out(false, 'Niektorý z údajov je príliš dlhý.', [], 422);
if (preg_match('/[\r\n]/', $name . $email . $phone . $recipient)) out(false, 'Neplatné údaje.', [], 422);

if ($type === 'sum') {
    if (!ctype_digit($amount) || (int)$amount < 5 || (int)$amount > 1000) out(false, 'Zadajte sumu poukážky od 5 do 1000 €.', [], 422);
    $what = 'Poukážka na sumu ' . (int)$amount . ' €';
} else {
    if (!ctype_digit($count) || (int)$count < 1 || (int)$count > 20) out(false, 'Počet lekcií musí byť od 1 do 20.', [], 422);
    $label = $type === 'individualne' ? 'Individuálne lekcie' : ($classes[$type]['name'] ?? null);
    if ($label === null) out(false, 'Vybraný druh poukážky sa nepodarilo overiť.', [], 422);
    $what = 'Poukážka na ' . (int)$count . '× ' . $label;
}

$enc = fn(string $t) => '=?UTF-8?B?' . base64_encode($t) . '?=';

// jedinečný kód (bez zameniteľných znakov) a podpísaný odkaz na tlač hotovej poukážky
$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$part = fn(int $n) => implode('', array_map(fn() => $alphabet[random_int(0, strlen($alphabet) - 1)], range(1, $n)));
$code = 'OP-' . $part(4) . '-' . $part(4);
$value = $type === 'sum' ? (int)$amount . ' €' : (int)$count . '× ' . ($type === 'individualne' ? 'Individuálne lekcie' : $classes[$type]['name']);
$months = (int)($voucher_validity_months ?? 12);
$valid  = (new DateTimeImmutable('now'))->modify("+$months months")->format('j. n. Y');
$payload = rtrim(strtr(base64_encode(json_encode([
    'v' => $value, 'r' => $recipient, 'm' => $dedication, 'c' => $code,
    'i' => date('j. n. Y'), 'x' => $valid,
], JSON_UNESCAPED_UNICODE)), '+/', '-_'), '=');
$printUrl = rtrim($site['url'], '/') . '/poukazka-tlac.php?d=' . $payload . '&s=' . hash_hmac('sha256', $payload, $voucher_secret);

$studioBody = "Nová objednávka darčekovej poukážky\n\n"
    . "Kód poukážky: $code\nPlatnosť do: $valid\n"
    . "Poukážka: $what\nPre koho: " . ($recipient !== '' ? $recipient : '–') . "\n"
    . "Venovanie: " . ($dedication !== '' ? $dedication : '–') . "\nSpôsob odovzdania: $delivery\n\n"
    . "Kupujúci: $name\nE-mail: $email\nTelefón: $phone\n"
    . "Súhlas so spracovaním údajov: áno (" . date('j. n. Y H:i') . ")\n\n"
    . "Hotová poukážka na vytlačenie (po prijatí platby):\n$printUrl\n";
$okStudio = @mail($site['email'], $enc("Poukážka – $what – $name"), $studioBody, [
    'From' => $site['email'], 'Reply-To' => $email, 'Content-Type' => 'text/plain; charset=UTF-8',
]);

$clientBody = "Dobrý deň,\n\nďakujeme za objednávku darčekovej poukážky v {$site['name']}:\n\n  $what\n  Kód poukážky: $code\n\n"
    . "Čoskoro sa vám ozveme, dohodneme platbu aj odovzdanie poukážky. Ak niečo potrebujete zmeniť, napíšte nám na {$site['email']} alebo zavolajte na {$site['phone']}.\n\n"
    . "{$site['name']}\n{$site['address']}\n";
@mail($email, $enc("Objednávka poukážky – {$site['name']}"), $clientBody, [
    'From' => $site['email'], 'Content-Type' => 'text/plain; charset=UTF-8',
]);

$okStudio
    ? out(true, 'Objednávku sme prijali.', ['summary' => $what])
    : out(false, 'Objednávku sa nepodarilo odoslať. Skúste to prosím neskôr alebo nám zavolajte.', [], 500);
