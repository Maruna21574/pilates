<?php
defined('ADMIN') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['reset'])) { reset_content('hero_slides'); redirect(admin_url('slider')); }
    $rows = is_array($_POST['s'] ?? null) ? array_values($_POST['s']) : [];
    $list = [];
    foreach ($rows as $r) {
        if (!is_array($r)) continue;
        $f = in_media($r['file'] ?? '');
        if ($f !== '') $list[] = ['file' => $f, 'alt' => in_line($r['alt'] ?? '', 140)];
    }
    if (!$list) { flash('Slider musí mať aspoň jednu fotku.', 'err'); redirect(admin_url('slider')); }
    save_content('hero_slides', $list);
    redirect(admin_url('slider'));
}

function slide_tile(string $n, string $file, string $alt, string $url): void { ?>
  <div class="tile">
    <img src="<?= e($url) ?>" alt="">
    <input type="hidden" name="s[<?= e($n) ?>][file]" value="<?= e($file) ?>">
    <input type="text" name="s[<?= e($n) ?>][alt]" value="<?= e($alt) ?>" maxlength="140" placeholder="Popis fotky (pre nevidiacich a Google)" aria-label="Popis fotky">
    <div class="tile-bar">
      <button type="button" class="iconbtn" data-up aria-label="Skôr">←</button>
      <button type="button" class="iconbtn" data-down aria-label="Neskôr">→</button>
      <span style="flex:1"></span>
      <button type="button" class="iconbtn del" data-del aria-label="Odstrániť">×</button>
    </div>
    <small><?= e($file) ?></small>
  </div>
<?php }

admin_head('Úvodný slider', 'slider', 'Fotky, ktoré sa striedajú v oblúku na začiatku úvodnej stránky.');
?>
<form method="post" class="track">
  <?= csrf_field() ?>
  <section class="card" data-bulk>
    <div class="tiles">
      <?php foreach ($hero_slides as $i => $s) slide_tile((string)$i, $s['file'], $s['alt'], '../' . media($s['file'])); ?>
    </div>
    <template><?php slide_tile('__N__', '__FILE__', '', '__URL__'); ?></template>
    <label class="dz" style="margin-top:1rem">+ Nahrať nové fotky<input type="file" accept="image/jpeg,image/png,image/webp" multiple hidden></label>
    <p class="bulk-msg note" style="margin-top:.8rem" aria-live="polite">Odporúčame fotky na výšku. Poradie nastavíte šípkami.</p>
  </section>
  <?php f_actions('Vrátiť slider na pôvodný?'); ?>
</form>
<?php admin_foot();
