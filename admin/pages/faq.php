<?php
defined('ADMIN') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['reset'])) { reset_content('faq'); redirect(admin_url('faq')); }
    $rows = is_array($_POST['q'] ?? null) ? array_values($_POST['q']) : [];
    $list = [];
    foreach ($rows as $r) {
        if (!is_array($r)) continue;
        $q = in_line($r['q'] ?? '', 160); $a = in_str($r['a'] ?? '', 800);
        if ($q !== '' && $a !== '') $list[] = [$q, $a];
    }
    save_content('faq', $list, $list ? 'Otázky sú uložené.' : 'Otázky sú prázdne – sekcia sa na webe nezobrazí.');
    redirect(admin_url('faq'));
}

function faq_item(string $n, array $f): void {
    $q = trim((string)($f[0] ?? '')); ?>
  <article class="item" data-del-msg="Odstrániť túto otázku?">
    <div class="item-h"><span class="num">1</span><strong data-empty="Nová otázka"><?= e($q ?: 'Nová otázka') ?></strong>
      <button type="button" class="iconbtn" data-up aria-label="Vyššie">↑</button>
      <button type="button" class="iconbtn" data-down aria-label="Nižšie">↓</button>
      <button type="button" class="iconbtn del" data-del aria-label="Odstrániť">×</button></div>
    <div class="grid">
      <?php f_text("q[$n][q]", 'Otázka', $q, ['max' => 160, 'class' => 'full', 'attrs' => 'data-title']); ?>
      <?php f_area("q[$n][a]", 'Odpoveď', (string)($f[1] ?? ''), ['max' => 800, 'rows' => 3, 'class' => 'full']); ?>
    </div>
  </article>
<?php }

admin_head('Časté otázky', 'faq', 'Zobrazujú sa na úvodnej stránke a v cenníku.');
?>
<form method="post" class="track">
  <?= csrf_field() ?>
  <div data-repeater>
    <div class="items">
      <?php foreach ($faq as $i => $f) faq_item((string)$i, $f); ?>
    </div>
    <template><?php faq_item('__N__', ['', '']); ?></template>
    <button type="button" class="btn add-row" data-add>+ Pridať otázku</button>
  </div>
  <?php f_actions('Vrátiť otázky na pôvodné?'); ?>
</form>
<?php admin_foot();
