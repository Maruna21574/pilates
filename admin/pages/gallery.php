<?php
defined('ADMIN') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['reset'])) { reset_content('gallery', 'Galéria je vrátená na pôvodnú.'); redirect(admin_url('gallery')); }
    $rows = is_array($_POST['g'] ?? null) ? array_values($_POST['g']) : [];
    $list = [];
    foreach ($rows as $r) {
        if (!is_array($r)) continue;
        $f = in_media($r['file'] ?? '');
        $cat = (string)($r['cat'] ?? '');
        if ($f !== '') $list[] = ['file' => $f, 'cat' => isset($gallery_cats[$cat]) ? $cat : array_key_first($gallery_cats)];
    }
    save_content('gallery', $list, $list ? 'Galéria je uložená.' : 'Galéria je prázdna – na webe sa nezobrazia žiadne fotky.');
    redirect(admin_url('gallery'));
}

function gal_tile(string $n, string $file, string $cat, string $url, array $cats): void { ?>
  <div class="tile">
    <img src="<?= e($url) ?>" alt="" loading="lazy">
    <input type="hidden" name="g[<?= e($n) ?>][file]" value="<?= e($file) ?>">
    <div class="tile-bar">
      <select name="g[<?= e($n) ?>][cat]" aria-label="Kategória"><?php foreach ($cats as $k => $l): ?><option value="<?= e($k) ?>"<?= $k === $cat ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
    </div>
    <div class="tile-bar">
      <button type="button" class="iconbtn" data-up aria-label="Skôr">←</button>
      <button type="button" class="iconbtn" data-down aria-label="Neskôr">→</button>
      <span style="flex:1"></span>
      <button type="button" class="iconbtn del" data-del aria-label="Odstrániť z galérie">×</button>
    </div>
    <small><?= e($file) ?></small>
  </div>
<?php }

admin_head('Galéria', 'gallery', 'Fotky na stránke Galéria. Kategória určuje filter nad fotkami.');
?>
<form method="post" class="track">
  <?= csrf_field() ?>
  <section class="card" data-bulk>
    <div class="tiles">
      <?php foreach ($gallery as $i => $g) gal_tile((string)$i, $g['file'], $g['cat'], '../' . media($g['file']), $gallery_cats); ?>
    </div>
    <template><?php gal_tile('__N__', '__FILE__', array_key_first($gallery_cats), '__URL__', $gallery_cats); ?></template>
    <label class="dz" style="margin-top:1rem">+ Nahrať nové fotky<input type="file" accept="image/jpeg,image/png,image/webp" multiple hidden></label>
    <p class="bulk-msg note" style="margin-top:.8rem" aria-live="polite">Fotky sa zmenšia a optimalizujú pre web. Poradie nastavíte šípkami, odstránenie z galérie súbor nezmaže.</p>
  </section>
  <?php f_actions('Vrátiť galériu na pôvodnú?'); ?>
</form>
<?php admin_foot();
