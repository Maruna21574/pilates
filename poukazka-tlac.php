<?php
/**
 * Tlač darčekovej poukážky. Odkaz dostane štúdio v e-maile s objednávkou; je podpísaný (HMAC),
 * takže údaje na poukážke sa nedajú upraviť v adrese.
 */
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/partials.php';

function b64u_decode(string $s): string|false { return base64_decode(strtr($s, '-_', '+/')); }

$payload = (string)($_GET['d'] ?? '');
$sig     = (string)($_GET['s'] ?? '');
$data    = null;
if ($payload !== '' && $sig !== '' && hash_equals(hash_hmac('sha256', $payload, $voucher_secret), $sig)) {
    $json = b64u_decode($payload);
    $data = $json !== false ? json_decode($json, true) : null;
}

header('X-Robots-Tag: noindex, nofollow');
if (!is_array($data) || empty($data['v']) || empty($data['c'])) {
    http_response_code(403);
    $title = 'Neplatný odkaz';
} else {
    $title = 'Poukážka ' . $data['c'];
}
?><!doctype html>
<html lang="sk">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($title) ?> | <?= e($site['name']) ?></title>
  <link rel="icon" href="favicon.ico" sizes="48x48">
  <link rel="stylesheet" href="assets/css/fonts.css">
  <link rel="stylesheet" href="assets/css/style.css?v=1">
</head>
<body class="print-page">
<main class="print-wrap">
<?php if (!is_array($data) || empty($data['v']) || empty($data['c'])): ?>
  <div class="print-bar"><div><b>Tento odkaz je neplatný</b><span>Poukážku otvorte z e-mailu s objednávkou. Odkaz nesmie byť upravený ani skrátený.</span></div></div>
<?php else: ?>
  <div class="print-bar">
    <div><b>Poukážka <?= e($data['c']) ?></b><span>Pred tlačou v prehliadači zapnite „Tlačiť pozadie / Background graphics“, aby sa vytlačili farby.</span></div>
    <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">Vytlačiť poukážku</button>
  </div>
  <?php voucher_card([
      'value'      => $data['v'],
      'recipient'  => $data['r'] ?? '',
      'dedication' => $data['m'] ?? '',
      'code'       => $data['c'],
      'valid'      => ($data['x'] ?? '') !== '' ? 'do ' . $data['x'] : '',
  ]); ?>
  <p class="print-meta">Vystavené: <?= e($data['i'] ?? '') ?></p>
<?php endif; ?>
</main>
</body>
</html>
