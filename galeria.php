<?php
$pageTitle = 'Galéria';
$pageDesc  = 'Fotografie a videá z Organic Pilates Studio v Žiline: reformer, barre, Ladder Barrel, podložky a štúdio.';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/partials.php';

// zoznam fotiek a kategórie sa upravujú v admin paneli (Galéria); predvolené sú v includes/data.php
$cats = $gallery_cats;
$usedCats = array_filter($cats, fn($k) => in_array($k, array_column($gallery, 'cat'), true), ARRAY_FILTER_USE_KEY);
page_hero('Galéria', 'Pohľad do <span class="script" style="font-size:1.15em">štúdia</span>', 'Fotky z lekcií a z priestoru, v ktorom cvičíme.', true);
?>

<section class="section gallery-sec">
  <div class="wrap">
    <div class="tabs" role="group" aria-label="Filter galérie">
      <button type="button" data-cat="all" aria-pressed="true">Všetko</button>
      <?php foreach ($usedCats as $k => $label): ?><button type="button" data-cat="<?= e($k) ?>" aria-pressed="false"><?= e($label) ?></button><?php endforeach; ?>
    </div>
    <div class="masonry">
      <?php foreach ($gallery as $i => $g): ?>
        <figure class="reveal" data-cat="<?= e($g['cat']) ?>" style="--d:<?= ($i % 3) * .08 ?>s"><img src="<?= e(media($g['file'])) ?>" alt="<?= e(($cats[$g['cat']] ?? 'Galéria') . ' – Organic Pilates Studio Žilina') ?>" loading="lazy"><figcaption><?= e($cats[$g['cat']] ?? '') ?></figcaption></figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($videos_gallery): ?>
<section class="section soft" id="videa">
  <div class="wrap">
    <div class="section-head reveal"><span class="eyebrow">Videá</span><h2>Pohyb v praxi</h2></div>
    <div class="video-row">
      <?php foreach ($videos_gallery as $v):
          video_card($v['file'], $v['title'], '', 'data-lb-video="' . e(media($v['file'] . '.mp4')) . '" tabindex="0" role="button" aria-label="Otvoriť video' . ($v['title'] !== '' ? ': ' . e($v['title']) : '') . '"');
      endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php reserve_banner('Páči sa vám u nás? <span class="script">Zacvičte si s nami</span>'); ?>

<?php media_lightbox(); ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
