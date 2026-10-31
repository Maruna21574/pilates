<?php
defined('ADMIN') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['reset'])) { reset_content('texts', 'Všetky texty sú vrátené na pôvodné.'); redirect(admin_url('texts')); }
    $in = is_array($_POST['t'] ?? null) ? $_POST['t'] : [];
    $out = [];
    foreach ($text_defs as $k => $def) {
        $val = in_str($in[$k] ?? '', $def['t'] === 'line' ? 200 : 700);
        if ($def['t'] === 'line') $val = in_line($val, 200);
        if ($val !== '' && $val !== $def['d']) $out[$k] = $val;     // prázdne alebo nezmenené = pôvodný text
    }
    save_content('texts', $out);
    redirect(admin_url('texts'));
}

$groups = [];
foreach ($text_defs as $k => $def) $groups[$def['g']][$k] = $def;

admin_head('Texty stránok', 'texts', 'Nadpisy a odseky na jednotlivých stránkach. Prázdne pole = pôvodný text.');
?>
<form method="post" class="track">
  <?= csrf_field() ?>
  <?php foreach ($groups as $g => $items): ?>
    <section class="card">
      <h2><?= e($g) ?></h2>
      <div class="grid">
        <?php foreach ($items as $k => $def):
            $isArea = $def['t'] === 'area';
            $o = ['class' => $isArea ? 'full' : '', 'max' => $isArea ? 700 : 200, 'rows' => 3];
            $isArea ? f_area("t[$k]", $def['l'], $texts[$k], $o) : f_text("t[$k]", $def['l'], $texts[$k], $o);
        endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
  <?php f_actions('Vrátiť VŠETKY texty na pôvodné?'); ?>
</form>
<?php admin_foot();
