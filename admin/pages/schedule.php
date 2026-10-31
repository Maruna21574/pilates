<?php
defined('ADMIN') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['reset'])) { reset_content('schedule', 'Rozvrh je vrátený na ukážkový.'); redirect(admin_url('schedule')); }
    $rows = is_array($_POST['s'] ?? null) ? array_values($_POST['s']) : [];
    $list = []; $seen = []; $bad = 0;
    foreach ($rows as $r) {
        if (!is_array($r)) continue;
        $cls = (string)($r['class'] ?? ''); $day = (int)($r['day'] ?? 0); $time = (string)($r['time'] ?? '');
        if (!isset($classes[$cls]) || $day < 1 || $day > 7 || !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $time)) { if ($time !== '' || $cls !== '') $bad++; continue; }
        $k = "$cls|$day|$time"; if (isset($seen[$k])) continue; $seen[$k] = true;
        $list[] = ['class' => $cls, 'day' => $day, 'time' => $time];
    }
    usort($list, fn($a, $b) => [$a['day'], $a['time']] <=> [$b['day'], $b['time']]);
    if ($bad) flash("$bad riadkov s neplatným časom alebo cvičením bolo preskočených.", 'warn');
    save_content('schedule', $list, 'Rozvrh je uložený. Rezervácia a termíny na webe sa podľa neho prepočítali.');
    redirect(admin_url('schedule'));
}

$classOpts = array_map(fn($c) => $c['name'], $classes);
function sched_item(string $n, array $s, array $classOpts): void { ?>
  <article class="item" data-del-msg="Odstrániť túto lekciu z rozvrhu?">
    <div class="item-h"><span class="num">1</span><strong><?= e(days_sk()[(int)($s['day'] ?? 1)] ?? '') ?></strong>
      <button type="button" class="iconbtn del" data-del aria-label="Odstrániť" title="Odstrániť">×</button></div>
    <div class="grid three">
      <?php f_select("s[$n][class]", 'Cvičenie', $classOpts, (string)($s['class'] ?? '')); ?>
      <?php f_select("s[$n][day]", 'Deň', days_sk(), (string)($s['day'] ?? 1)); ?>
      <?php f_text("s[$n][time]", 'Čas začiatku', (string)($s['time'] ?? '18:00'), ['type' => 'time', 'required' => true]); ?>
    </div>
  </article>
<?php }

$sorted = $schedule;
usort($sorted, fn($a, $b) => [$a['day'], $a['time']] <=> [$b['day'], $b['time']]);
admin_head('Rozvrh', 'schedule', 'Týždenný rozvrh lekcií. Opakuje sa každý týždeň a podľa neho sa vytvárajú termíny v rezervácii.');
$isDemo = !isset(content_load()['schedule']);
?>
<form method="post" class="track">
  <?= csrf_field() ?>
  <?php if ($isDemo): ?><div class="flash warn">Zobrazený rozvrh je ukážkový. Upravte ho podľa skutočných lekcií a uložte.</div><?php endif; ?>
  <div class="note" style="margin-bottom:1rem">Kapacitu lekcie určuje cvičenie (v časti Cvičenia). Po uložení sa riadky zoradia podľa dňa a času.</div>
  <div data-repeater>
    <div class="items">
      <?php foreach ($sorted as $i => $s) sched_item((string)$i, $s, $classOpts); ?>
    </div>
    <template><?php sched_item('__N__', ['class' => array_key_first($classes), 'day' => 1, 'time' => '18:00'], $classOpts); ?></template>
    <button type="button" class="btn add-row" data-add>+ Pridať lekciu</button>
  </div>
  <?php f_actions('Vrátiť rozvrh na ukážkový?'); ?>
</form>
<?php admin_foot();
