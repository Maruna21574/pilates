<?php
$pageTitle = 'Pilates Žilina – reformer, barre, podložky';
$pageDesc  = 'Organic Pilates Studio v Žiline: reformer, barre, pilates na podložkách, Ladder Barrel, cvičenie pre tehotné a seniorov. Malé skupiny, rezervácia online.';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/partials.php';
?>

<section class="hero">
  <div class="blobs" aria-hidden="true"><i></i><i></i><i></i></div><div class="grain" aria-hidden="true"></div>
  <div class="wrap hero-grid">
    <div>
      <h1 class="reveal" style="--d:.08s"><span class="script">organic</span><span class="thin">pilates studio</span></h1>
      <p class="lead reveal" style="--d:.16s"><?= e(T('home_hero_lead')) ?></p>
      <div class="btn-row reveal" style="--d:.24s">
        <a class="btn" href="rezervacia.php">Rezervovať miesto</a>
        <a class="btn btn-ghost" href="cvicenia.php">Pozrieť cvičenia</a>
      </div>
      <div class="next-box glass reveal" style="--d:.32s">
        <h2 class="next-title">Najbližšie voľné termíny</h2>
        <div data-next><p style="margin:0">Načítavam termíny…</p></div>
      </div>
    </div>
    <?php $slides = array_map(fn($s) => [$s['file'], $s['alt']], $hero_slides); ?>
    <div class="scene reveal" style="--d:.2s" role="region" aria-roledescription="carousel" aria-label="Fotky zo štúdia">
      <div class="scene-inner">
        <div class="ring" aria-hidden="true"></div>
        <div class="card3d arch slider" data-slider>
          <?php foreach ($slides as $i => $s): ?>
            <figure class="slide<?= $i === 0 ? ' is-active' : '' ?>" <?= $i ? 'aria-hidden="true"' : '' ?>>
              <img src="<?= e(media($s[0])) ?>" alt="<?= e($s[1]) ?>"<?= $i === 0 ? ' fetchpriority="high"' : ' loading="lazy"' ?>>
            </figure>
          <?php endforeach; ?>
          <div class="slider-ui">
            <div class="s-dots" role="tablist" aria-label="Výber fotky">
              <?php foreach ($slides as $i => $s): ?><button type="button" role="tab" aria-label="Fotka <?= $i + 1 ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"><i></i></button><?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="marquee" aria-hidden="true">
  <div class="marquee-track">
    <?php for ($i = 0; $i < 2; $i++): foreach ($classes as $c): ?><span><?= e($c['name']) ?></span><?php endforeach; endfor; ?>
  </div>
</div>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Cvičenia</span>
      <h2><?= e(T('home_classes_title')) ?></h2>
      <p><?= e(T('home_classes_lead')) ?></p>
    </div>
    <div class="cards">
      <?php $i = 0; foreach ($classes as $id => $c) class_card($id, $c, ($i++ % 3) * .1); ?>
    </div>
  </div>
</section>

<section class="section soft">
  <div class="wrap split flip-m">
    <div class="split-media reveal reveal-left">
      <div class="frame parallax" data-speed=".04"><img src="<?= e(media($individual['images'][0] ?? 'studio-02.jpg')) ?>" alt="Individuálna lekcia pilatesu s inštruktorkou" loading="lazy"></div>
    </div>
    <div class="reveal reveal-right">
      <span class="eyebrow"><?= e($individual['tag']) ?></span>
      <h2><?= e($individual['name']) ?></h2>
      <p class="lead" style="font-weight:300;font-size:1.15rem"><?= e($individual['lead']) ?></p>
      <ul class="list-check"><?php foreach ($individual['for'] as $f): ?><li><?= e($f) ?></li><?php endforeach; ?></ul>
      <div class="btn-row">
        <a class="btn btn-primary" href="kontakt.php">Dohodnúť lekciu</a>
        <a class="btn btn-ghost-dark" href="cvicenia.php#individualne">Viac</a>
      </div>
    </div>
  </div>
</section>

<section class="section dark airy">
  <div class="blobs" aria-hidden="true"><i></i><i></i></div>
  <canvas class="gl" data-gl="dark" aria-hidden="true"></canvas>
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow">Ako to funguje</span>
      <h2><?= e(T('home_steps_title')) ?></h2>
    </div>
    <div class="steps">
      <div class="step reveal"><h3><?= e(T('home_step1_title')) ?></h3><p><?= e(T('home_step1_text')) ?></p></div>
      <div class="step reveal" style="--d:.1s"><h3><?= e(T('home_step2_title')) ?></h3><p><?= e(T('home_step2_text')) ?></p></div>
      <div class="step reveal" style="--d:.2s"><h3><?= e(T('home_step3_title')) ?></h3><p><?= e(T('home_step3_text')) ?></p></div>
    </div>

    <?php if ($videos_home): ?>
    <div class="section-head reveal airy-gap">
      <span class="eyebrow">Prvá hodina</span>
      <h2><?= e(T('home_video_title')) ?></h2>
    </div>
    <div class="video-row">
      <?php foreach ($videos_home as $v)
          video_card($v['file'], $v['title'], '', 'data-lb-video="' . e(media($v['file'] . '.mp4')) . '" tabindex="0" role="button" aria-label="Otvoriť video' . ($v['title'] !== '' ? ': ' . e($v['title']) : '') . '"'); ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="wrap split rev flip-m">
    <div class="split-media reveal reveal-right">
      <div class="frame parallax" data-speed=".04"><img src="<?= e(media('studio-02.jpg')) ?>" alt="Štúdio Organic Pilates v Žiline – reformery a pomôcky" loading="lazy"></div>
    </div>
    <div class="reveal reveal-left">
      <span class="eyebrow">Štúdio</span>
      <h2><?= e(T('home_studio_title')) ?></h2>
      <p><?= e(T('home_studio_text')) ?></p>
      <p><?= e($site['address']) ?></p>
      <div class="btn-row">
        <a class="btn btn-primary" href="o-nas.php">Spoznajte nás</a>
        <a class="btn btn-ghost-dark" href="galeria.php">Galéria</a>
      </div>
    </div>
  </div>
</section>

<?php reviews_section(); ?>

<section class="section soft">
  <div class="wrap">
    <div class="section-head center reveal"><span class="eyebrow">Otázky</span><h2>Často kladené otázky</h2></div>
    <div class="faq">
      <?php foreach ($faq as $q): ?>
        <details class="reveal"><summary><?= e($q[0]) ?></summary><p><?= e($q[1]) ?></p></details>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php reserve_banner(); ?>

<?php if ($videos_home) media_lightbox(); ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
