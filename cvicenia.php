<?php
$pageTitle = 'Lekcie – reformer, barre, podložky, Ladder Barrel';
$pageDesc  = 'Prehľad cvičení v Organic Pilates Studio Žilina: čo je reformer, barre, Ladder Barrel, pilates na podložkách, Tehuľky pre tehotné a Vital Pilates pre seniorov.';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/partials.php';
page_hero('Lekcie', 'Čo je čo a <span class="script" style="font-size:1.15em">pre koho</span>', T('classes_lead'), true);

/** Jedno médium ku cvičeniu: video (ak ho má), inak jedna fotka. Kliknutím sa otvorí v modálnom okne. */
function class_media(array $c, string $alt): void {
    if (!empty($c['video'])) { ?>
      <div>
        <?php video_card($c['video'], '', 'as-frame', 'data-modal-video="' . e(media($c['video'] . '.mp4')) . '" tabindex="0" role="button" aria-label="Otvoriť video ' . e($c['name']) . '"'); ?>
      </div>
    <?php } elseif (!empty($c['images'][0])) {
        $src = media($c['images'][0]); ?>
      <div>
        <div class="frame zoomable" data-modal-img="<?= e($src) ?>" tabindex="0" role="button" aria-label="Zväčšiť fotku: <?= e($alt) ?>">
          <img src="<?= e($src) ?>" alt="<?= e($alt) ?>" loading="lazy"<?= !empty($c['pos']) ? ' style="object-position:' . e($c['pos']) . '"' : '' ?>>
          <?php zoom_hint(); ?>
        </div>
      </div>
    <?php }
}
?>

<div class="class-filter">
  <nav class="wrap" aria-label="Skok na lekciu">
    <?php foreach ($classes as $id => $c): ?><a class="chip-link" href="#<?= e($id) ?>"><?= e($c['name']) ?></a><?php endforeach; ?>
    <a class="chip-link" href="#individualne">Individuálne</a>
  </nav>
</div>

<?php $n = 0; foreach ($classes as $id => $c): $rev = $n++ % 2 === 1; ?>
<section class="section <?= $rev ? 'soft' : '' ?>">
  <div class="wrap split flip-m <?= $rev ? 'rev' : '' ?>">
    <div class="split-media reveal <?= $rev ? 'reveal-right' : 'reveal-left' ?>">
      <?php class_media($c, $c['name'] . ' – Organic Pilates Studio Žilina'); ?>
    </div>
    <div class="reveal <?= $rev ? 'reveal-left' : 'reveal-right' ?>" id="<?= e($id) ?>">
      <span class="eyebrow"><?= e($c['tag']) ?></span>
      <h2><?= e($c['name']) ?></h2>
      <p style="font-size:1.15rem;font-weight:300"><?= e($c['lead']) ?></p>
      <p><?= e($c['text']) ?></p>
      <div class="facts">
        <span class="chip"><?= (int)$c['capacity'] === 1 ? 'individuálne, 1 osoba' : 'max. ' . (int)$c['capacity'] . ' osôb' ?></span>
        <span class="chip"><?= (int)$c['duration'] ?> min</span>
        <span class="chip"><?= e($c['equipment']) ?></span>
      </div>
      <h3 style="font-size:1rem;letter-spacing:.14em;text-transform:uppercase;font-weight:500;color:var(--t600);margin-top:1.6rem">Pre koho je určené</h3>
      <ul class="list-check" style="margin-top:.8rem"><?php foreach ($c['for'] as $f): ?><li><?= e($f) ?></li><?php endforeach; ?></ul>
      <a class="btn btn-primary" href="rezervacia.php?class=<?= e($id) ?>">Rezervovať <?= e(mb_strtolower($c['name'])) ?></a>
    </div>
  </div>
</section>
<?php if ($id === 'reformer') reformer_3d(); ?>
<?php endforeach; ?>

<section class="section no-deco divided">
  <div class="wrap split flip-m">
    <div class="split-media reveal reveal-left">
      <?php class_media(['images' => $individual['images'], 'name' => $individual['name']], 'Individuálna lekcia pilatesu'); ?>
    </div>
    <div class="reveal reveal-right" id="individualne">
      <span class="eyebrow"><?= e($individual['tag']) ?></span>
      <h2><?= e($individual['name']) ?></h2>
      <p style="font-size:1.15rem;font-weight:300"><?= e($individual['lead']) ?></p>
      <p><?= e($individual['text']) ?></p>
      <ul class="list-check"><?php foreach ($individual['for'] as $f): ?><li><?= e($f) ?></li><?php endforeach; ?></ul>
      <a class="btn btn-primary" href="kontakt.php">Dohodnúť individuálnu lekciu</a>
    </div>
  </div>
</section>

<?php reserve_banner('Našli ste svoju lekciu? <span class="script">Rezervujte si ju</span>'); ?>

<dialog class="media-modal" id="mediaModal" aria-label="Zväčšené médium">
  <button class="mm-close" type="button" aria-label="Zavrieť">×</button>
  <div class="mm-body"></div>
</dialog>

<?php require __DIR__ . '/includes/footer.php'; ?>
