<?php
$pageTitle = 'O nás – tím a štúdio';
$pageDesc  = 'Spoznajte tím Organic Pilates Studio v Žiline a priestor, v ktorom cvičíme: reformery, Ladder Barrel, barre a pomôcky.';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/partials.php';
page_hero('O nás', 'Štúdio <span class="script" style="font-size:1.15em">zdravého pohybu</span>', T('about_lead'), true);
?>

<section class="section">
  <div class="wrap split">
    <div class="reveal reveal-left">
      <div class="team-photo"><img src="<?= e(media('team-01.jpg')) ?>" alt="Tím inštruktoriek Organic Pilates Studio" loading="lazy"></div>
    </div>
    <div class="reveal reveal-right">
      <span class="eyebrow">Tím</span>
      <h2><?= e(T('about_team_title')) ?></h2>
      <p><?= e(T('about_team_text')) ?></p>
      <ul class="plain team-list">
        <?php foreach ($team as $i => $m): ?>
          <li>
            <button type="button" class="team-btn" data-open-dialog="#team-<?= $i ?>" aria-haspopup="dialog">
              <?php team_avatar($m); ?>
              <span class="team-name"><b><?= e($m['name']) ?></b><small><?= e($m['role']) ?></small></span>
              <span class="team-more">Viac<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg></span>
            </button>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<section class="section soft">
  <div class="wrap split rev">
    <div class="split-media reveal reveal-right">
      <div class="frame parallax" data-speed=".04"><img src="<?= e(media('studio-01.jpg')) ?>" alt="Štúdio Organic Pilates – reformery, Cadillac a zrkadlá" loading="lazy"></div>
    </div>
    <div class="reveal reveal-left">
      <span class="eyebrow">Priestor</span>
      <h2><?= e(T('about_studio_title')) ?></h2>
      <p><?= e(T('about_studio_text1')) ?></p>
      <p><?= e(T('about_studio_text2')) ?></p>
      <div class="btn-row"><a class="btn btn-primary" href="rezervacia.php">Rezervovať</a><a class="btn btn-ghost-dark" href="galeria.php">Galéria</a></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal"><span class="eyebrow">Naše zásady</span><h2><?= e(T('about_p_title')) ?></h2></div>
    <div class="steps steps-light">
      <div class="step reveal"><h3><?= e(T('about_p1_title')) ?></h3><p><?= e(T('about_p1_text')) ?></p></div>
      <div class="step reveal" style="--d:.1s"><h3><?= e(T('about_p2_title')) ?></h3><p><?= e(T('about_p2_text')) ?></p></div>
      <div class="step reveal" style="--d:.2s"><h3><?= e(T('about_p3_title')) ?></h3><p><?= e(T('about_p3_text')) ?></p></div>
    </div>
  </div>
</section>

<?php foreach ($team as $i => $m): ?>
<dialog class="modal team-modal" id="team-<?= $i ?>" aria-labelledby="team-<?= $i ?>-title">
  <div class="modal-head team-head">
    <button class="close" type="button" aria-label="Zavrieť">×</button>
    <?php team_avatar($m, true); ?>
    <div><h3 id="team-<?= $i ?>-title"><?= e($m['name']) ?></h3><p><?= e($m['role']) ?></p></div>
  </div>
  <div class="modal-body">
    <p><?= e($m['bio']) ?></p>
    <?php if (!empty($m['focus'])): ?>
      <h4>Na hodine nájdete</h4>
      <ul class="list-check"><?php foreach ($m['focus'] as $f): ?><li><?= e($f) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
    <a class="btn btn-primary btn-sm" href="rezervacia.php">Rezervovať lekciu</a>
  </div>
</dialog>
<?php endforeach; ?>

<?php reserve_banner('Prídite si to <span class="script">vyskúšať</span>'); ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
