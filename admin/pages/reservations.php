<?php
defined('ADMIN') or exit;
require_once __DIR__ . '/../../includes/booking.php';

$pdo = db();
$labels = ['paid' => 'Zaplatené online', 'on_site' => 'Platba na mieste', 'waitlist' => 'Čakacia listina',
    'pending' => 'Čaká na platbu', 'cancelled' => 'Zrušené', 'expired' => 'Platba nedokončená', 'refunded' => 'Vrátené'];

if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (isset($_POST['cancel'])) {
        $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ? AND status IN ('on_site','waitlist','paid','pending')")->execute([$id]);
        flash('Rezervácia je zrušená a miesto je znova voľné. Ak bola zaplatená online, platbu vráťte v Stripe (Payments → Refund).');
    } elseif (isset($_POST['confirm'])) {
        // z čakacej listiny na potvrdenú rezerváciu s platbou na mieste
        $pdo->prepare("UPDATE reservations SET status = 'on_site', pay_method = 'on_site' WHERE id = ? AND status = 'waitlist'")->execute([$id]);
        flash('Rezervácia z čakacej listiny je potvrdená (platba na mieste). Klientke dajte vedieť e-mailom alebo telefonicky.');
    }
    redirect(admin_url('reservations', ['show' => $_GET['show'] ?? '']));
}

$show = ($_GET['show'] ?? '') === 'past' ? 'past' : 'upcoming';
admin_head('Rezervácie', 'reservations', 'Rezervácie lekcií a ich platby. Obsadenosť v kalendári na webe sa počíta z tohto zoznamu.');

if (!$pdo): ?>
<section class="card">
  <h2>Databáza nie je pripojená</h2>
  <p class="lead">Rezervácie sa teraz len posielajú e-mailom. Pre ukladanie a online platby vytvorte súbor <code>data/config.php</code> podľa <code>data/config.sample.php</code>.</p>
</section>
<?php admin_foot(); return; endif;

$rows = $pdo->query($show === 'past'
    ? "SELECT * FROM reservations WHERE session_date < CURDATE() ORDER BY session_date DESC, session_time DESC LIMIT 200"
    : "SELECT * FROM reservations WHERE session_date >= CURDATE() AND NOT (status = 'expired') ORDER BY session_date, session_time, id LIMIT 500")->fetchAll();
?>
<section class="card">
  <p>
    <a class="btn<?= $show === 'upcoming' ? '' : ' ghost' ?>" href="<?= e(admin_url('reservations')) ?>">Nadchádzajúce</a>
    <a class="btn<?= $show === 'past' ? '' : ' ghost' ?>" href="<?= e(admin_url('reservations', ['show' => 'past'])) ?>">Minulé</a>
  </p>
  <?php if (!$rows): ?>
    <p>Zatiaľ tu nie sú žiadne rezervácie.</p>
  <?php else: ?>
  <table class="tbl">
    <thead><tr><th>Termín</th><th>Lekcia</th><th>Klientka</th><th>Stav</th><th>Suma</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r):
        $c = $classes[$r['class_id']]['name'] ?? $r['class_id'];
        $state = $r['status'];
        $dim = in_array($state, ['cancelled', 'refunded', 'expired'], true); ?>
      <tr<?= $dim ? ' style="opacity:.55"' : '' ?>>
        <td><?= e(date('j. n. Y', strtotime($r['session_date']))) ?> <b><?= e($r['session_time']) ?></b></td>
        <td><?= e($c) ?></td>
        <td><?= e($r['name']) ?><br><small><a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a> · <?= e($r['phone']) ?></small></td>
        <td><?= e($labels[$state] ?? $state) ?></td>
        <td><?= e(reservation_money($r['amount_cents'] !== null ? (int)$r['amount_cents'] : null)) ?: '–' ?></td>
        <td>
          <?php if (in_array($state, ['on_site', 'waitlist', 'paid', 'pending'], true)): ?>
          <form method="post" action="<?= e(admin_url('reservations', ['show' => $show])) ?>" style="display:inline">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <?php if ($state === 'waitlist'): ?><button class="btn" name="confirm" value="1">Potvrdiť</button><?php endif; ?>
            <button class="btn ghost" name="cancel" value="1" onclick="return confirm('Zrušiť túto rezerváciu?')">Zrušiť</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</section>
<?php admin_foot();
