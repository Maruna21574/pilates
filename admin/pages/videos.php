<?php
defined('ADMIN') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['reset'])) { reset_content('videos', 'Videá sú vrátené na pôvodné.'); redirect(admin_url('videos')); }
    $rows = is_array($_POST['v'] ?? null) ? array_values($_POST['v']) : [];
    $list = []; $seen = [];
    foreach ($rows as $r) {
        if (!is_array($r)) continue;
        $f = in_line($r['file'] ?? '', 120);
        if (!video_exists($f) || isset($seen[$f])) continue;
        $seen[$f] = true;
        $list[] = ['file' => $f, 'title' => in_line($r['title'] ?? '', 60), 'home' => !empty($r['home']), 'gallery' => !empty($r['gallery'])];
    }
    save_content('videos', $list, $list ? 'Videá sú uložené.' : 'Zoznam videí je prázdny – na webe sa žiadne video nezobrazí.');
    redirect(admin_url('videos'));
}

function video_tile(string $n, string $file, string $title, string $poster, bool $home, bool $gal): void { ?>
  <div class="tile vtile">
    <img src="<?= e($poster) ?>" alt="" loading="lazy">
    <input type="hidden" name="v[<?= e($n) ?>][file]" value="<?= e($file) ?>">
    <input type="text" name="v[<?= e($n) ?>][title]" value="<?= e($title) ?>" maxlength="60" placeholder="Popis videa (nepovinné)" aria-label="Popis videa">
    <label class="chk"><input type="checkbox" name="v[<?= e($n) ?>][home]" value="1"<?= $home ? ' checked' : '' ?>><span>Úvodná stránka</span></label>
    <label class="chk"><input type="checkbox" name="v[<?= e($n) ?>][gallery]" value="1"<?= $gal ? ' checked' : '' ?>><span>Galéria</span></label>
    <div class="tile-bar">
      <button type="button" class="iconbtn" data-up aria-label="Skôr">←</button>
      <button type="button" class="iconbtn" data-down aria-label="Neskôr">→</button>
      <span style="flex:1"></span>
      <button type="button" class="iconbtn del" data-del aria-label="Odstrániť zo zoznamu">×</button>
    </div>
    <small><?= e($file) ?></small>
  </div>
<?php }

$limit = upload_limit_bytes();
admin_head('Videá', 'videos', 'Videá na úvodnej stránke („Čo vás čaká na prvej hodine“) a v galérii. Poradie nastavíte šípkami.');
?>
<form method="post" class="track">
  <?= csrf_field() ?>
  <section class="card" data-vbulk data-max="<?= (int)$limit ?>">
    <div class="tiles">
      <?php foreach ($videos as $i => $v) video_tile((string)$i, $v['file'], $v['title'], '../' . media(video_poster_path($v['file'])), $v['home'], $v['gallery']); ?>
    </div>
    <template><?php video_tile('__N__', '__FILE__', '', '__POSTER__', false, true); ?></template>
    <label class="dz" style="margin-top:1rem">+ Nahrať nové video (MP4)<input type="file" accept="video/mp4" multiple hidden></label>
    <p class="vbulk-msg note" style="margin-top:.8rem" aria-live="polite">Najväčšie video, ktoré server prijme: <strong><?= e(format_bytes($limit)) ?></strong>. Odporúčame zvislé video do 720 px a približne 5 MB, formát MP4 (H.264). Pred uložením zmien sa nové video na webe nezobrazí.</p>
  </section>
  <p class="note" style="margin-bottom:1rem">Odstránenie zo zoznamu video nezmaže zo servera – zmazať ho môžete v časti <a href="<?= e(admin_url('media')) ?>">Médiá</a>. Pri cvičení sa video vyberá v časti Cvičenia.</p>
  <?php f_actions('Vrátiť zoznam videí na pôvodný?'); ?>
</form>
<?php admin_foot();
