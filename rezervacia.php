<?php
$pageTitle = 'Rezervácia lekcií';
$pageDesc  = 'Rezervujte si miesto na lekcii pilatesu v Žiline. Vyberte cvičenie, deň a čas a pozrite si počet voľných miest.';
$bodyClass = 'page-booking';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/partials.php';
page_hero('Rezervácia', 'Vyberte si <span class="script" style="font-size:1.15em">svoj termín</span>', T('book_lead'), true);
?>

<section class="section" style="padding-top:3rem">
  <div class="wrap">
    <div class="booking" id="booking">
      <aside class="panel">
        <h3>Lekcia</h3>
        <div class="filters" id="filters" role="group" aria-label="Filter cvičení"></div>
        <p style="font-size:.9rem;color:var(--muted);margin:1.2rem 0 0">Individuálne lekcie a cvičenie podľa dohody: <a href="kontakt.php">kontaktujte nás</a>.</p>
      </aside>
      <div class="panel">
        <h3>Deň a čas</h3>
        <div class="cal-bar">
          <button type="button" class="cal-nav" data-month-prev aria-label="Predchádzajúci mesiac"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg></button>
          <div class="cal-title" id="calTitle" aria-live="polite"></div>
          <button type="button" class="cal-nav" data-month-next aria-label="Ďalší mesiac"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg></button>
          <button type="button" class="cal-today" id="calToday">Dnes</button>
        </div>
        <div class="days" id="days" role="group" aria-label="Výber dňa"></div>
        <div class="sessions" id="sessions" aria-live="polite"></div>
      </div>
    </div>
  </div>
</section>

<dialog class="modal" id="bookModal" aria-labelledby="bm-title">
  <div class="modal-head">
    <button class="close" type="button" aria-label="Zavrieť">×</button>
    <h3 id="bm-title">Rezervácia lekcie</h3>
    <p id="bm-sub">Skontrolujte údaje a vyplňte kontakt</p>
  </div>
  <form>
    <div class="summary" aria-label="Zhrnutie rezervácie">
      <div class="sum-title"><i id="bm-dot" aria-hidden="true"></i><span id="bm-s-class"></span><span class="sum-wait" id="bm-s-wait" hidden>Čakacia listina</span></div>
      <div class="sum-meta">
        <span><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15" rx="3"/><path d="M3.5 10h17M8 3v4M16 3v4"/></svg><b id="bm-s-date"></b></span>
        <span><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg><b id="bm-s-time"></b></span>
      </div>
    </div>
    <div class="field"><label for="bm-name">Meno a priezvisko</label><input id="bm-name" name="name" required autocomplete="name"></div>
    <div class="field"><label for="bm-email">E-mail</label><input id="bm-email" name="email" type="email" required autocomplete="email"></div>
    <div class="field"><label for="bm-phone">Telefón</label><input id="bm-phone" name="phone" type="tel" required autocomplete="tel"></div>
    <label class="check">
      <input type="checkbox" name="gdpr" value="1" required>
      <span>Súhlasím so spracovaním osobných údajov na účel vybavenia rezervácie. Podrobnosti nájdete v <a href="ochrana-udajov.php" target="_blank" rel="noopener">zásadách ochrany osobných údajov</a>.</span>
    </label>
    <div class="hp" aria-hidden="true"><label>Nevyplňujte <input name="website" tabindex="-1" autocomplete="off"></label></div>
    <div class="form-msg err" id="bm-err" hidden></div>
    <button class="btn btn-primary" type="submit">Potvrdiť rezerváciu</button>
  </form>
  <div class="form-msg" id="bm-msg" hidden></div>
</dialog>

<?php require __DIR__ . '/includes/footer.php'; ?>
