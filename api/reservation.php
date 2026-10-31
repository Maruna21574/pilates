<?php
declare(strict_types=1);
/**
 * Stav a zrušenie nezaplatenej online rezervácie (volá rezervačné okno na webe).
 * GET ?id=<public_id>            → overí platbu u Stripe a vráti stav
 * POST id=<public_id>&cancel=1   → uvoľní podržané miesto, ak zákazník platbu zavrel
 */
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/booking.php';
header('Content-Type: application/json; charset=utf-8');

$id = (string)($_GET['id'] ?? $_POST['id'] ?? '');
$pdo = db();
if (!$pdo || !preg_match('/^[0-9a-f]{32}$/', $id)) { http_response_code(400); echo '{"ok":false}'; exit; }
$st = $pdo->prepare('SELECT * FROM reservations WHERE public_id = ?');
$st->execute([$id]);
$row = $st->fetch();
if (!$row) { http_response_code(404); echo '{"ok":false}'; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cancel']) && $row['status'] === 'pending') {
    $pdo->prepare("UPDATE reservations SET status = 'expired' WHERE id = ? AND status = 'pending'")->execute([$row['id']]);
    try { if ($row['stripe_session_id']) stripe_call('POST', 'checkout/sessions/' . rawurlencode($row['stripe_session_id']) . '/expire'); }
    catch (Throwable) { /* už zaplatené alebo vypršané */ }
    $st->execute([$id]); $row = $st->fetch();
} elseif ($row['status'] === 'pending') {
    reservation_sync_from_stripe($row);
    $st->execute([$id]); $row = $st->fetch();
}
echo json_encode(['ok' => true, 'status' => $row['status']]);
