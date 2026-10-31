<?php
require_once __DIR__ . '/data.php';
$pageTitle = $pageTitle ?? 'Pilates Žilina';
$pageDesc  = $pageDesc  ?? 'Organic Pilates Studio v Žiline – reformer, barre, pilates na podložkách, cvičenie pre tehotné a seniorov. Rezervujte si miesto online.';
$fullTitle = $pageTitle . ' | ' . $site['name'];
$ld = [
    '@context' => 'https://schema.org',
    '@type' => 'HealthClub',
    'name' => $site['name'],
    'description' => $pageDesc,
    'telephone' => $site['phone'],
    'email' => $site['email'],
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'Obežná 2/8563', 'postalCode' => '010 08', 'addressLocality' => 'Žilina', 'addressCountry' => 'SK'],
    'sameAs' => [$site['instagram'], $site['facebook']],
];
?><!doctype html>
<html lang="sk">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
  <title><?= e($fullTitle) ?></title>
  <?php if (!empty($baseHref)): ?><base href="<?= e($baseHref) ?>"><?php endif; ?>
  <meta name="description" content="<?= e($pageDesc) ?>">
  <meta name="theme-color" content="#3f6f6a">
  <?php
    $thisPage  = basename($_SERVER['SCRIPT_NAME']);
    $canonical = $site['url'] . ($thisPage === 'index.php' ? '/' : '/' . $thisPage);
    $ogImage   = $site['url'] . '/assets/img/og-image.jpg';
  ?>
  <link rel="canonical" href="<?= e($canonical) ?>">
  <?php if (!empty($noindex)): ?><meta name="robots" content="noindex"><?php endif; ?>
  <meta property="og:site_name" content="<?= e($site['name']) ?>">
  <meta property="og:locale" content="sk_SK">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= e($canonical) ?>">
  <meta property="og:title" content="<?= e($fullTitle) ?>">
  <meta property="og:description" content="<?= e($pageDesc) ?>">
  <meta property="og:image" content="<?= e($ogImage) ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="<?= e($site['name'] . ' – ' . $site['tagline']) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($fullTitle) ?>">
  <meta name="twitter:description" content="<?= e($pageDesc) ?>">
  <meta name="twitter:image" content="<?= e($ogImage) ?>">
  <link rel="icon" href="favicon.ico" sizes="48x48">
  <link rel="icon" href="assets/img/icon-32.png" type="image/png" sizes="32x32">
  <link rel="icon" href="assets/img/icon-192.png" type="image/png" sizes="192x192">
  <link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">
  <link rel="manifest" href="site.webmanifest">
  <link rel="preload" href="assets/fonts/jost-400-latin.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="assets/css/fonts.css">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= (int)@filemtime(__DIR__ . "/../assets/css/style.css") ?>">
  <script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
</head>
<body class="<?= e(trim(($bodyClass ?? '') . (!empty($announcement) ? ' has-bar' : ''))) ?>">
<a class="skip" href="#main">Preskočiť na obsah</a>

<?php if (!empty($announcement)): ?>
<div class="announce" data-announce role="region" aria-label="Oznam">
  <p>
    <?= e($announcement['text']) ?>
    <?php if (!empty($announcement['link'])): ?><a href="<?= e($announcement['link']) ?>"><?= e($announcement['link_text'] ?? 'Viac') ?> <span aria-hidden="true">→</span></a><?php endif; ?>
  </p>
  <button type="button" class="announce-close" aria-label="Zavrieť oznam">×</button>
</div>
<?php endif; ?>

<header class="site-header" data-header>
  <div class="wrap header-in">
    <a class="brand" href="index.php" aria-label="<?= e($site['name']) ?> – úvod">
      <img class="logo-light" src="assets/img/logo-mark-white.png" alt="" width="108" height="56">
      <img class="logo-dark" src="assets/img/logo-mark-teal.png" alt="" width="108" height="56">
      <span class="brand-text">Pilates Studio<small>Žilina</small></span>
    </a>
    <?php
      $chev = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
      $thumb = fn(string $n) => 'assets/media/thumbs/' . $n . '.jpg';   // pevné náhľady (Videá, Fotky)
      $ctThumb = fn(array $c) => !empty($c['images'][0]) ? media_thumb($c['images'][0]) : (!empty($c['video']) ? media_thumb(video_poster_path($c['video'])) : $thumb('fotky'));
    ?>
    <nav class="nav" id="nav" aria-label="Hlavné menu">
      <a href="index.php"<?= is_current('index.php') ?>>Úvod</a>

      <div class="nav-item has-drop mega-wide">
        <a class="nav-link" href="cvicenia.php"<?= is_current('cvicenia.php') ?>>Cvičenia</a>
        <button type="button" class="drop-toggle" aria-expanded="false" aria-label="Rozbaliť ponuku cvičení"><?= $chev ?></button>
        <div class="mega" role="group" aria-label="Cvičenia">
          <div class="mega-grid">
            <?php foreach ($classes as $id => $c): ?>
              <a class="mega-tile" href="cvicenia.php#<?= e($id) ?>">
                <span class="mt-img"><img src="<?= e($ctThumb($c)) ?>" alt="" width="400" height="320" loading="lazy"></span>
                <span class="mt-name"><?= e($c['name']) ?></span>
                <span class="mt-tag"><?= e($c['tag']) ?></span>
              </a>
            <?php endforeach; ?>
            <a class="mega-tile" href="cvicenia.php#individualne">
              <span class="mt-img"><img src="<?= e($ctThumb($individual)) ?>" alt="" width="400" height="320" loading="lazy"></span>
              <span class="mt-name">Individuálne lekcie</span>
              <span class="mt-tag"><?= e($individual['tag']) ?></span>
            </a>
            <?php if ($videos_gallery): ?>
            <a class="mega-tile is-video" href="galeria.php#videa">
              <span class="mt-img"><img src="<?= e($thumb('videa')) ?>" alt="" width="400" height="320" loading="lazy"><i class="mt-play" aria-hidden="true"></i></span>
              <span class="mt-name">Videá z lekcií</span>
              <span class="mt-tag">Pozrite, ako cvičíme</span>
            </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <a href="cennik.php"<?= is_current('cennik.php') ?>>Cenník</a>
      <a href="o-nas.php"<?= is_current('o-nas.php') ?>>O nás</a>

      <div class="nav-item has-drop">
        <a class="nav-link" href="galeria.php"<?= is_current('galeria.php') ?>>Galéria</a>
        <button type="button" class="drop-toggle" aria-expanded="false" aria-label="Rozbaliť ponuku galérie"><?= $chev ?></button>
        <div class="mega mega-small" role="group" aria-label="Galéria">
          <div class="mega-grid">
            <a class="mega-tile" href="galeria.php">
              <span class="mt-img"><img src="<?= e($thumb('fotky')) ?>" alt="" width="400" height="320" loading="lazy"></span>
              <span class="mt-name">Fotky</span>
              <span class="mt-tag">Štúdio a lekcie</span>
            </a>
            <?php if ($videos_gallery): ?>
            <a class="mega-tile is-video" href="galeria.php#videa">
              <span class="mt-img"><img src="<?= e($thumb('videa')) ?>" alt="" width="400" height="320" loading="lazy"><i class="mt-play" aria-hidden="true"></i></span>
              <span class="mt-name">Videá</span>
              <span class="mt-tag">Pohyb v praxi</span>
            </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <a href="kontakt.php"<?= is_current('kontakt.php') ?>>Kontakt</a>
      <a class="btn btn-sm nav-cta" href="rezervacia.php">Rezervovať</a>
      <div class="nav-foot">
        <a href="tel:<?= e($site['phone_href']) ?>"><?= e($site['phone']) ?></a>
        <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a>
        <div class="nf-soc">
          <a href="<?= e($site['instagram']) ?>" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/></svg></a>
          <a href="<?= e($site['facebook']) ?>" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7.5h2.5l.4-3h-2.900V8.700c0-.9.3-1.500 1.500-1.500h1.500V4.500c-.3 0-1.200-.1-2.200-.1-2.200 0-3.700 1.300-3.700 3.800v2.300H8v3h2.600V21z"/></svg></a>
        </div>
      </div>
    </nav>
    <button class="burger" aria-label="Menu" aria-expanded="false" aria-controls="nav" data-burger><span></span><span></span></button>
  </div>
</header>

<main id="main">
