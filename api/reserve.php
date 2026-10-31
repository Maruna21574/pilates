<?php
declare(strict_types=1);
/**
 * Žiadosť o rezerváciu lekcie: pošle e-mail štúdiu a potvrdenie o prijatí žiadosti klientke.
 * Termín potvrdzuje štúdio (kapacity sa zatiaľ nesledujú v databáze).
 */
require_once __DIR__ . '/../includes/data.php';
header('Content-Type: application/json; charset=utf-8');

function out(bool $ok, string $message, int $code = 200): never {
    http_response_code($code);
    echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
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
if (mb_strlen($name) > 120) out(false, 'Meno je príliš dlhé.', 422);
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

$days = ['pondelok', 'utorok', 'streda', 'štvrtok', 'piatok', 'sobota', 'nedeľa'];
$cls  = $classes[$class];
$when = $days[$weekday - 1] . ' ' . $d->format('j. n. Y') . ' o ' . $time;
$what = $cls['name'] . ', ' . $when;

$enc = fn(string $t) => '=?UTF-8?B?' . base64_encode($t) . '?=';

// 1) štúdiu
$studioBody = "Nová žiadosť o rezerváciu\n\n"
    . "Služba: {$cls['name']}\nTermín: $when ({$cls['duration']} min)\n\n"
    . "Meno: $name\nE-mail: $email\nTelefón: $phone\n"
    . "Súhlas so spracovaním údajov: áno (" . date('j. n. Y H:i') . ")\n";
$okStudio = @mail($site['email'], $enc("Rezervácia – $what – $name"), $studioBody, [
    'From' => $site['email'], 'Reply-To' => $email, 'Content-Type' => 'text/plain; charset=UTF-8',
]);

// 2) klientke (potvrdenie o prijatí žiadosti)
$clientBody = "Dobrý deň,\n\nďakujeme za záujem o lekciu v {$site['name']}. Prijali sme vašu žiadosť o rezerváciu:\n\n"
    . "  $what\n\n"
    . "Termín vám štúdio potvrdí e-mailom alebo telefonicky. Ak potrebujete niečo zmeniť, napíšte nám na {$site['email']} alebo zavolajte na {$site['phone']}.\n\n"
    . "{$site['name']}\n{$site['address']}\n";
@mail($email, $enc("Žiadosť o rezerváciu – {$site['name']}"), $clientBody, [
    'From' => $site['email'], 'Content-Type' => 'text/plain; charset=UTF-8',
]);

$okStudio
    ? out(true, 'Žiadosť o rezerváciu sme prijali.')
    : out(false, 'Rezerváciu sa nepodarilo odoslať. Skúste to prosím neskôr alebo nám zavolajte.', 500);
