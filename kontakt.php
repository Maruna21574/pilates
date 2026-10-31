<?php
$pageTitle = 'Kontakt';
$pageDesc  = 'Kontakt na Organic Pilates Studio v Žiline: adresa Obežná 2/8563, telefón, e-mail a sociálne siete.';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/partials.php';
page_hero('Kontakt', 'Napíšte nám, <span class="script" style="font-size:1.15em">radi poradíme</span>', T('contact_lead'), true);
?>

<section class="section">
  <div class="wrap contact-grid">
    <div class="contact-list reveal reveal-left">
      <a href="tel:<?= e($site['phone_href']) ?>"><span class="ico"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 4h4l2 5-2.500 1.500a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg></span><span><small>Telefón (<?= e($site['phone_person']) ?>)</small><?= e($site['phone']) ?></span></a>
      <a href="mailto:<?= e($site['email']) ?>"><span class="ico"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span><span><small>E-mail</small><?= e($site['email']) ?></span></a>
      <a href="<?= e($site['maps']) ?>" target="_blank" rel="noopener"><span class="ico"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s7-6.1 7-11.5a7 7 0 0 0-14 0C5 14.900 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg></span><span><small>Adresa – otvoriť v mapách</small><?= e($site['address']) ?></span></a>
      <a href="<?= e($site['instagram']) ?>" target="_blank" rel="noopener"><span class="ico"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/></svg></span><span><small>Instagram</small>@organicpilateszilina</span></a>
      <a href="<?= e($site['facebook']) ?>" target="_blank" rel="noopener"><span class="ico"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7.5h2.5l.4-3h-2.900V8.700c0-.9.3-1.500 1.500-1.500h1.500V4.500c-.3 0-1.200-.1-2.200-.1-2.200 0-3.700 1.300-3.700 3.800v2.300H8v3h2.600V21z"/></svg></span><span><small>Facebook</small>Organic Pilates Studio</span></a>
    </div>
    <form class="panel reveal reveal-right" id="contactForm">
      <h3>Napíšte nám</h3>
      <div style="display:grid;gap:1rem">
        <div class="field"><label for="c-name">Meno</label><input id="c-name" name="name" required autocomplete="name"></div>
        <div class="field"><label for="c-email">E-mail</label><input id="c-email" name="email" type="email" required autocomplete="email"></div>
        <div class="field"><label for="c-phone">Telefón <span class="muted">(nepovinné)</span></label><input id="c-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" maxlength="30"></div>
        <div class="field"><label for="c-msg">Správa</label><textarea id="c-msg" name="message" required></textarea></div>
        <label class="check">
          <input type="checkbox" name="gdpr" value="1" required>
          <span>Súhlasím so spracovaním osobných údajov na účel vybavenia mojej správy. Podrobnosti nájdete v <a href="ochrana-udajov.php" target="_blank" rel="noopener">zásadách ochrany osobných údajov</a>.</span>
        </label>
        <div class="hp" aria-hidden="true"><label>Nevyplňujte <input name="website" tabindex="-1" autocomplete="off"></label></div>
        <button class="btn btn-primary" type="submit">Odoslať správu</button>
        <div class="form-msg" hidden></div>
      </div>
    </form>
  </div>
</section>

<?php reserve_banner(); ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
