<?php
/** Karta cvičenia (3D tilt). Bez fotky sa použije gradient s motívom z loga. */
function class_card(string $id, array $c, float $delay = 0): void { ?>
  <a class="class-card tilt reveal" style="--d:<?= $delay ?>s" href="cvicenia.php#<?= e($id) ?>">
    <?php if (!empty($c['images'])): ?>
      <div class="bg"><img src="<?= e(media($c['images'][0])) ?>" alt="<?= e($c['name']) ?> v štúdiu Organic Pilates" loading="lazy"<?= !empty($c['pos']) ? ' style="object-position:' . e($c['pos']) . '"' : '' ?>></div>
    <?php else: ?>
      <div class="art" style="--c1: <?= e($c['color']) ?>; --c2: #2b504b">
        <svg viewBox="0 0 100 100" fill="none" stroke="#fff" stroke-width=".8" aria-hidden="true"><circle cx="50" cy="50" r="46"/><circle cx="50" cy="50" r="38"/><circle cx="50" cy="50" r="28"/><circle cx="50" cy="50" r="16"/></svg>
      </div>
    <?php endif; ?>
    <span class="glare"></span>
    <div class="body">
      <span class="chip"><?= e($c['tag']) ?></span>
      <h3 style="margin-top:.7rem"><?= e($c['name']) ?></h3>
      <p><?= e($c['lead']) ?></p>
    </div>
  </a>
<?php }

/** Výrazný banner „Rezervovať lekciu" – používa sa na viacerých stránkach pred pätičkou.
 *  Najbližšie voľné termíny doplní main.js do [data-next]. */
function reserve_banner(string $title = 'Zacvičte si s nami <span class="script">už tento týždeň</span>'): void {
    global $site; ?>
  <section class="banner" aria-labelledby="banner-title">
    <div class="wrap">
      <div class="banner-card reveal">
        <div class="banner-text">
          <span class="banner-kicker">Rezervácia</span>
          <h2 id="banner-title"><?= $title ?></h2>
          <p><?= e(T('banner_text')) ?></p>
          <div class="btn-row">
            <a class="btn" href="rezervacia.php">Rezervovať lekciu</a>
            <a class="btn btn-ghost" href="tel:<?= e($site['phone_href']) ?>">Zavolať <?= e($site['phone']) ?></a>
          </div>
        </div>
        <div class="next-box glass">
          <h3 class="next-title">Najbližšie voľné termíny</h3>
          <div data-next><p style="margin:0">Načítavam termíny…</p></div>
        </div>
      </div>
    </div>
  </section>
<?php }

/** Vzhľad darčekovej poukážky (živý náhľad aj tlač). Polia s data-v-* dopĺňa JS v náhľade. */
function voucher_card(array $v): void {
    global $site;
    $to = trim((string)($v['recipient'] ?? '')); $ded = trim((string)($v['dedication'] ?? '')); ?>
  <div class="voucher" data-voucher>
    <div class="v-ring" aria-hidden="true"></div>
    <div class="v-head">
      <img src="assets/img/logo-white.png" alt="<?= e($site['name']) ?>" width="150">
      <span class="v-kind">Darčeková poukážka</span>
    </div>
    <div class="v-main">
      <span class="v-script">pre vás</span>
      <div class="v-value" data-v-value><?= e($v['value'] ?? '') ?></div>
      <div class="v-to" data-v-to <?= $to === '' ? 'hidden' : '' ?>>Pre: <b data-v-recipient><?= e($to) ?></b></div>
      <p class="v-ded" data-v-ded <?= $ded === '' ? 'hidden' : '' ?>>„<span data-v-dedication><?= e($ded) ?></span>“</p>
    </div>
    <div class="v-foot">
      <div><small>Kód poukážky</small><b data-v-code><?= e($v['code'] ?? '') ?></b></div>
      <div><small>Platnosť</small><b data-v-valid><?= e($v['valid'] ?? '') ?></b></div>
      <div class="v-contact"><small>Rezervácia lekcie</small><b><?= e($site['phone']) ?></b><span><?= e($site['email']) ?></span></div>
    </div>
  </div>
<?php }

/** Slider s recenziami klientov (dáta: $reviews v data.php; prázdne pole sekciu skryje). */
function reviews_section(): void {
    global $reviews, $google_reviews_url;
    if (empty($reviews)) return;
    $gLogo = '<svg class="rev-g" viewBox="0 0 48 48" width="22" height="22" role="img" aria-label="Google"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>';
    $star = '<svg viewBox="0 0 24 24" width="17" height="17" fill="currentColor" aria-hidden="true"><path d="m12 2.8 2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.600 6.200 20.700l1.100-6.500L2.600 9.600l6.500-.9z"/></svg>';
    ?>
  <section class="section reviews" aria-labelledby="rev-title">
    <div class="wrap">
      <div class="reviews-head">
        <div class="section-head reveal"><span class="eyebrow">Recenzie</span><h2 id="rev-title"><?= e(T('reviews_title')) ?></h2></div>
        <div class="rev-ctrl">
          <?php if (!empty($google_reviews_url)): ?><a class="btn btn-ghost-dark btn-sm" href="<?= e($google_reviews_url) ?>" target="_blank" rel="noopener">Všetky recenzie</a><?php endif; ?>
          <button type="button" class="rev-btn" data-rev-prev aria-label="Predchádzajúce recenzie"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg></button>
          <button type="button" class="rev-btn" data-rev-next aria-label="Ďalšie recenzie"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg></button>
        </div>
      </div>
      <div class="rev-track reveal" data-rev tabindex="0" role="region" aria-roledescription="slider" aria-label="Recenzie klientov">
        <?php foreach ($reviews as $r): $rate = max(1, min(5, (int)($r['rating'] ?? 5))); ?>
          <figure class="rev-card">
            <div class="rev-top">
              <div class="rev-stars" role="img" aria-label="Hodnotenie <?= $rate ?> z 5"><?php for ($i = 0; $i < $rate; $i++) echo $star; ?></div>
              <?php if (($r['source'] ?? '') === 'google') echo $gLogo; ?>
            </div>
            <blockquote class="rev-text"><?= e($r['text']) ?></blockquote>
            <figcaption class="rev-who">
              <span class="rev-av" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($r['name'], 0, 1))) ?></span>
              <span><b><?= e($r['name']) ?></b><small><?= e($r['date'] ?? '') ?></small></span>
            </figcaption>
          </figure>
        <?php endforeach; ?>
      </div>
      <div class="rev-dots" data-rev-dots aria-hidden="true"></div>
    </div>
  </section>
<?php }

/** Kruhový avatar člena tímu: portrét (ak je) alebo iniciály. */
function team_avatar(array $m, bool $large = false): void { ?>
  <span class="avatar<?= $large ? ' lg' : '' ?>" aria-hidden="true">
    <?php if (!empty($m['photo'])): ?><img src="<?= e(media($m['photo'])) ?>" alt="" loading="lazy"><?php else: ?><?= e($m['initials'] ?? '') ?><?php endif; ?>
  </span>
<?php }

/** Sekcia s interaktívnym 3D modelom reformeru (assets/js/reformer3d.js). Diely sú v $reformer_parts (data.php). */
function reformer_3d(): void {
    global $reformer_parts;
    if (empty($reformer_parts)) return; ?>
  <section class="section dark r3d" id="reformer3d" aria-labelledby="r3d-title">
    <div class="blobs" aria-hidden="true"><i></i></div>
    <div class="wrap">
      <div class="section-head reveal">
        <span class="eyebrow">3D model</span>
        <h2 id="r3d-title"><?= e(T('r3d_title')) ?></h2>
        <p><?= e(T('r3d_text')) ?></p>
      </div>
      <div class="r3d-grid">
        <div class="r3d-stage reveal" data-reformer3d>
          <canvas class="r3d-canvas" tabindex="0" role="img" aria-label="Interaktívny 3D model reformeru. Šípkami model otáčate, klávesmi plus a mínus ho približujete."></canvas>
          <div class="r3d-pins" aria-hidden="true"></div>
          <img class="r3d-fallback" src="<?= e(media('reformer-02.jpg')) ?>" alt="Reformer v štúdiu Organic Pilates" loading="lazy">
          <div class="r3d-loading" role="status">Načítavam 3D model…</div>
          <p class="r3d-hint" aria-hidden="true">Potiahnite pre otočenie</p>
          <div class="r3d-card" hidden role="status" aria-live="polite">
            <button type="button" class="r3d-card-x" aria-label="Zavrieť popis">×</button>
            <b class="r3d-card-t"></b>
            <p class="r3d-card-p"></p>
          </div>
          <div class="r3d-tools">
            <button type="button" class="r3d-play" data-r3d-play aria-pressed="false"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg><span>Ukázať pohyb</span></button>
            <button type="button" class="r3d-ico" data-r3d-zoom="out" aria-label="Oddialiť"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/></svg></button>
            <button type="button" class="r3d-ico" data-r3d-zoom="in" aria-label="Priblížiť"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></button>
            <button type="button" class="r3d-ico" data-r3d-reset aria-label="Pôvodný pohľad"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12a8 8 0 1 0 2.6-5.9M4 4v4h4"/></svg></button>
          </div>
        </div>
        <ol class="r3d-list">
          <?php foreach ($reformer_parts as $i => $p): ?>
            <li class="r3d-item reveal" style="--d:<?= $i * .05 ?>s" data-part="<?= e($p['id']) ?>">
              <button type="button" class="r3d-head" aria-expanded="false"><span class="r3d-num"><?= $i + 1 ?></span><span class="r3d-name"><?= e($p['title']) ?></span><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></button>
              <p class="r3d-text"><?= e($p['text']) ?></p>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    </div>
  </section>
<?php }

/** Prehliadač fotiek a videí vo veľkom okne (ovláda ho assets/js/main.js; položky majú atribút data-lb-video alebo sú fotky v galérii). */
function media_lightbox(): void { ?>
<div class="lightbox" role="dialog" aria-modal="true" aria-label="Prehliadač fotiek a videí">
  <button type="button" class="lb-close" aria-label="Zavrieť">×</button>
  <button type="button" class="lb-nav lb-prev" aria-label="Predchádzajúca položka"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg></button>
  <div class="lb-stage"><img alt=""><video controls playsinline loop hidden></video></div>
  <button type="button" class="lb-nav lb-next" aria-label="Ďalšia položka"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg></button>
  <div class="lb-count" aria-live="polite"></div>
</div>
<?php }

/** Ikonka „zväčšiť" v rohu média, ktoré sa otvára v modálnom okne. */
function zoom_hint(): void { ?>
  <span class="zoom-hint" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6M10 20H4v-6M20 4l-7 7M4 20l7-7"/></svg></span>
<?php }

function video_card(string $name, string $caption = '', string $extra = '', string $attrs = ''): void { ?>
  <figure class="video-card reveal <?= e($extra) ?>" style="margin:0" <?= $attrs ?>>
    <video muted loop playsinline preload="none" poster="<?= e(media(video_poster_path($name))) ?>">
      <source src="<?= e(media($name . '.mp4')) ?>" type="video/mp4">
    </video>
    <span class="play" aria-hidden="true"></span>
    <button type="button" class="sound" aria-pressed="false" aria-label="Zapnúť zvuk">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M4 9.500v5h3.500L12 18.500v-13L7.500 9.500z" fill="currentColor" stroke="none"/>
        <path class="waves" d="M15.500 9a4.500 4.500 0 0 1 0 6M18 6.500a8 8 0 0 1 0 11"/>
        <path class="slash" d="m15 9.500 5 5m0-5-5 5"/>
      </svg>
    </button>
    <?php if ($attrs) zoom_hint(); ?>
    <?php if ($caption): ?><figcaption><?= e($caption) ?></figcaption><?php endif; ?>
  </figure>
<?php }

function page_hero(string $eyebrow, string $title, string $lead = '', bool $gl = false): void { ?>
  <section class="page-hero">
    <div class="blobs" aria-hidden="true"><i></i><i></i><i></i></div><div class="grain" aria-hidden="true"></div>
    <?php if ($gl): ?><link rel="preload" href="assets/js/vendor/three.min.js" as="script"><canvas class="gl" data-gl="hero" aria-hidden="true"></canvas><?php endif; ?>
    <div class="wrap">
      <span class="eyebrow reveal"><?= e($eyebrow) ?></span>
      <h1 class="reveal" style="--d:.08s"><?= $title ?></h1>
      <?php if ($lead): ?><p class="lead reveal" style="--d:.16s"><?= e($lead) ?></p><?php endif; ?>
    </div>
  </section>
<?php }
