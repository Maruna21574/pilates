<?php
defined('ADMIN') or exit;

$root = dirname(__DIR__, 2) . '/assets/media/';

/** Súbory z uploads/, ktoré sa niekde na webe používajú. @return array<string,string[]> súbor => kde */
function media_usage(): array {
    global $classes, $individual, $team, $hero_slides, $gallery;
    $u = [];
    foreach ($classes as $c) foreach ($c['images'] ?? [] as $f) $u[$f][] = 'Cvičenie ' . $c['name'];
    foreach ($individual['images'] ?? [] as $f) $u[$f][] = 'Individuálne lekcie';
    foreach ($team as $m) if (!empty($m['photo'])) $u[$m['photo']][] = 'Tím: ' . $m['name'];
    foreach ($hero_slides as $s) $u[$s['file']][] = 'Úvodný slider';
    foreach ($gallery as $g) $u[$g['file']][] = 'Galéria';
    return $u;
}

/** Použitie nahraných videí (knižnica videí + videá pri cvičeniach). */
function video_usage(): array {
    global $videos, $classes;
    $u = [];
    foreach ($videos as $v) { $where = []; if ($v['home']) $where[] = 'Úvodná stránka'; if ($v['gallery']) $where[] = 'Galéria'; $u[$v['file']] = $where ?: ['Knižnica videí']; }
    foreach ($classes as $c) if (!empty($c['video'])) $u[$c['video']][] = 'Cvičenie ' . $c['name'];
    return $u;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['video'])) {
        $vf = (string)$_POST['video'];
        if (!preg_match('#^uploads/v[0-9]{8}-[0-9a-f]{10}$#', $vf) || !is_file($root . $vf . '.mp4')) flash('Video sa nenašlo.', 'err');
        elseif (isset(video_usage()[$vf])) flash('Video sa ešte používa: ' . implode(', ', array_unique(video_usage()[$vf])) . '. Najprv ho odstráňte.', 'err');
        else { @unlink($root . $vf . '.mp4'); @unlink($root . $vf . '-poster.jpg'); foreach (glob($root . 'thumbs/' . str_replace('/', '__', $vf) . '-poster.jpg') ?: [] as $t) @unlink($t); flash('Video bolo zmazané.'); }
        redirect(admin_url('media'));
    }
    $f = (string)($_POST['file'] ?? '');
    if (!preg_match('#^uploads/[A-Za-z0-9._-]+\.jpg$#', $f) || !is_file($root . $f)) { flash('Súbor sa nenašiel.', 'err'); }
    elseif (isset(media_usage()[$f])) { flash('Obrázok sa ešte používa, najprv ho odstráňte zo stránky: ' . implode(', ', media_usage()[$f]) . '.', 'err'); }
    else { @unlink($root . $f); foreach (glob($root . 'thumbs/' . str_replace('/', '__', $f)) ?: [] as $t) @unlink($t); flash('Obrázok bol zmazaný.'); }
    redirect(admin_url('media'));
}

$usage = media_usage();
$vusage = video_usage();
$vfiles = array_map(fn($f) => 'uploads/' . basename($f, '.mp4'), glob($root . 'uploads/*.mp4') ?: []);
usort($vfiles, fn($a, $b) => filemtime($root . $b . '.mp4') <=> filemtime($root . $a . '.mp4'));
$files = array_filter(array_keys(media_files('jpg')), fn($f) => str_starts_with($f, 'uploads/'));
usort($files, fn($a, $b) => filemtime($root . $b) <=> filemtime($root . $a));

admin_head('Médiá', 'media', 'Obrázky, ktoré ste nahrali. Nepoužívané môžete zmazať. Pôvodné fotky štúdia sa tu nezobrazujú.');
?>
<section class="card">
  <?php if (!$files): ?>
    <p>Zatiaľ ste nenahrali žiadne obrázky. Nahrať ich môžete pri cvičeniach, v tíme, v slideri alebo v galérii.</p>
  <?php else: ?>
    <div class="tiles">
      <?php foreach ($files as $f): ?>
        <div class="tile">
          <img src="../assets/media/<?= e($f) ?>" alt="" loading="lazy">
          <small><?= e(basename($f)) ?><br><?= e(date('j. n. Y', (int)filemtime($root . $f))) ?> · <?= (int)round(filesize($root . $f) / 1024) ?> kB</small>
          <?php if (isset($usage[$f])): ?>
            <span class="tag wood">Používa sa: <?= e(implode(', ', array_unique($usage[$f]))) ?></span>
          <?php else: ?>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="file" value="<?= e($f) ?>"><button class="btn sm danger" type="submit" data-confirm="Zmazať tento obrázok natrvalo?">Zmazať</button></form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php if ($vfiles): ?>
<section class="card">
  <h2>Nahrané videá</h2>
  <div class="tiles">
    <?php foreach ($vfiles as $vf): ?>
      <div class="tile">
        <img src="../assets/media/<?= e($vf) ?>-poster.jpg" alt="" loading="lazy">
        <small><?= e(basename($vf)) ?>.mp4<br><?= e(date('j. n. Y', (int)filemtime($root . $vf . '.mp4'))) ?> · <?= e(format_bytes((int)filesize($root . $vf . '.mp4'))) ?></small>
        <?php if (isset($vusage[$vf])): ?>
          <span class="tag wood">Používa sa: <?= e(implode(', ', array_unique($vusage[$vf]))) ?></span>
        <?php else: ?>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="video" value="<?= e($vf) ?>"><button class="btn sm danger" type="submit" data-confirm="Zmazať toto video natrvalo?">Zmazať</button></form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
<?php admin_foot();
