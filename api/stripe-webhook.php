<?php
declare(strict_types=1);
/** Webhook od Stripe: potvrdenie platby, vypršanie platby, vrátenie peňazí. Podpis sa vždy overuje. */
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/booking.php';

$payload = (string)file_get_contents('php://input');
$secret  = (string)(booking_config()['stripe']['webhook_secret'] ?? '');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !stripe_verify_signature($payload, $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '', $secret)) {
    http_response_code(400); exit('bad signature');
}
$event = json_decode($payload, true);
$pdo = db();
if (!is_array($event) || !$pdo) { http_response_code(500); exit('error'); }

$obj = $event['data']['object'] ?? [];
try {
    switch ($event['type'] ?? '') {
        case 'checkout.session.completed':
        case 'checkout.session.async_payment_succeeded':
            if (($obj['payment_status'] ?? '') === 'paid') reservation_mark_paid((string)$obj['id'], $obj['payment_intent'] ?? null);
            break;
        case 'checkout.session.expired':
            $pdo->prepare("UPDATE reservations SET status = 'expired' WHERE stripe_session_id = ? AND status = 'pending'")->execute([(string)$obj['id']]);
            break;
        case 'charge.refunded':
            if (!empty($obj['refunded']) && !empty($obj['payment_intent'])) {
                $pdo->prepare("UPDATE reservations SET status = 'refunded' WHERE stripe_payment_intent = ? AND status = 'paid'")->execute([(string)$obj['payment_intent']]);
            }
            break;
    }
} catch (Throwable $e) {
    error_log('Stripe webhook: ' . $e->getMessage());
    http_response_code(500); exit('error');   // Stripe to skúsi znova
}
echo 'ok';
