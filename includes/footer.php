</main>

<footer class="site-footer">
  <div class="footer-glow" aria-hidden="true"></div>
  <div class="wrap footer-grid">
    <div class="footer-brand">
      <img src="assets/img/logo-white.png" alt="<?= e($site['name']) ?> – <?= e($site['tagline']) ?>" width="220" loading="lazy">
    </div>
    <div>
      <h3>Kontakt</h3>
      <ul class="plain">
        <li><?= e($site['address']) ?></li>
        <li><a href="tel:<?= e($site['phone_href']) ?>"><?= e($site['phone']) ?></a> <span class="muted">(<?= e($site['phone_person']) ?>)</span></li>
        <li><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></li>
      </ul>
    </div>
    <div>
      <h3>Menu</h3>
      <ul class="plain">
        <li><a href="rezervacia.php">Rezervácia</a></li>
        <li><a href="cvicenia.php">Cvičenia</a></li>
        <li><a href="cennik.php">Cenník</a></li>
        <li><a href="poukazky.php">Poukážky</a></li>
        <li><a href="o-nas.php">O nás</a></li>
        <li><a href="galeria.php">Galéria</a></li>
      </ul>
    </div>
    <div>
      <h3>Sledujte nás</h3>
      <div class="socials">
        <a href="<?= e($site['instagram']) ?>" target="_blank" rel="noopener" aria-label="Instagram">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/></svg>
        </a>
        <a href="<?= e($site['facebook']) ?>" target="_blank" rel="noopener" aria-label="Facebook">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7.5h2.5l.4-3h-2.900V8.700c0-.9.3-1.500 1.500-1.500h1.500V4.500c-.3 0-1.200-.1-2.200-.1-2.200 0-3.700 1.300-3.700 3.800v2.300H8v3h2.600V21z"/></svg>
        </a>
        <a href="<?= e($site['maps']) ?>" target="_blank" rel="noopener" aria-label="Mapa">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 21s7-6.1 7-11.5a7 7 0 0 0-14 0C5 14.900 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
        </a>
      </div>
    </div>
  </div>
  <div class="wrap footer-bottom">
    <span>© <?= date('Y') ?> <?= e($site['name']) ?>, <?= e($site['city']) ?></span>
    <a href="ochrana-udajov.php">Ochrana osobných údajov</a>
  </div>
</footer>

<a class="sticky-cta btn" href="rezervacia.php">Rezervovať miesto</a>

<script>window.OP = <?= json_encode([
    'classes' => array_map(fn($c, $k) => ['id' => $k, 'name' => $c['name'], 'capacity' => $c['capacity'], 'duration' => $c['duration'], 'color' => $c['color'], 'blurb' => $c['blurb'] ?? ''], $classes, array_keys($classes)),
    'schedule' => $schedule,
    'bookMonths' => $settings['book_months'],
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="assets/js/main.js?v=<?= (int)@filemtime(__DIR__ . "/../assets/js/main.js") ?>" defer></script>
</body>
</html>
