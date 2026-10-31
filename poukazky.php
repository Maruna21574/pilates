<?php
$pageTitle = 'Darčekové poukážky';
$pageDesc  = 'Darčekové poukážky do Organic Pilates Studio v Žiline: darujte lekcie pilatesu na reformeri, barre alebo podložkách. Objednávka cez formulár.';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/partials.php';
page_hero('Poukážky', 'Darujte <span class="script" style="font-size:1.15em">pohyb</span>', T('gift_lead'), true);
?>

<section class="section">
  <div class="wrap split voucher-split">
    <div class="reveal reveal-left voucher-info">
      <span class="eyebrow">Ako to funguje</span>
      <h2><?= e(T('gift_title')) ?></h2>
      <p><?= e(T('gift_text')) ?></p>
      <ol class="vsteps">
        <li><b>Vyplníte formulár</b><span>Vyberiete, čo chcete darovať, a doplníte kontakt.</span></li>
        <li><b>Ozveme sa vám</b><span>Dohodneme platbu aj spôsob odovzdania poukážky.</span></li>
        <li><b>Obdarovaná osoba cvičí</b><span>Poukážku si uplatní pri rezervácii lekcie.</span></li>
      </ol>
      <p class="muted" style="font-size:.92rem">Máte otázku? Zavolajte na <a href="tel:<?= e($site['phone_href']) ?>"><?= e($site['phone']) ?></a> alebo napíšte na <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a>.</p>
    </div>

    <div class="panel reveal reveal-right" id="voucherPanel">
      <form id="voucherForm">
        <h3>Objednávka poukážky</h3>
        <div class="vgrid">
          <div class="field">
            <label for="v-type">Čo chcete darovať</label>
            <select id="v-type" name="type" required>
              <option value="sum">Poukážka na sumu (€)</option>
              <?php foreach ($classes as $id => $c): ?><option value="<?= e($id) ?>">Lekcie – <?= e($c['name']) ?></option><?php endforeach; ?>
              <option value="individualne">Individuálne lekcie</option>
            </select>
          </div>
          <div class="field" id="v-amount-wrap">
            <label for="v-amount">Suma (€)</label>
            <input id="v-amount" name="amount" type="number" min="5" max="1000" step="1" inputmode="numeric" placeholder="napr. 50" required>
          </div>
          <div class="field" id="v-count-wrap" hidden>
            <label for="v-count">Počet lekcií</label>
            <input id="v-count" name="count" type="number" min="1" max="20" step="1" value="1" inputmode="numeric">
          </div>
          <div class="field">
            <label for="v-for">Pre koho je poukážka <span class="muted">(nepovinné)</span></label>
            <input id="v-for" name="recipient" maxlength="120" autocomplete="off">
          </div>
          <div class="field">
            <label for="v-delivery">Spôsob odovzdania</label>
            <select id="v-delivery" name="delivery">
              <option value="elektronicky">Elektronicky (e-mailom)</option>
              <option value="osobne">Osobne v štúdiu</option>
            </select>
          </div>
          <div class="field full">
            <label for="v-msg">Venovanie <span class="muted">(nepovinné)</span></label>
            <textarea id="v-msg" name="dedication" maxlength="400" style="min-height:90px"></textarea>
          </div>

          <h3 class="full" style="margin:.6rem 0 0">Vaše údaje</h3>
          <div class="field"><label for="v-name">Meno a priezvisko</label><input id="v-name" name="name" required autocomplete="name" maxlength="120"></div>
          <div class="field"><label for="v-phone">Telefón</label><input id="v-phone" name="phone" type="tel" required autocomplete="tel" inputmode="tel" maxlength="30"></div>
          <div class="field full"><label for="v-email">E-mail</label><input id="v-email" name="email" type="email" required autocomplete="email"></div>

          <label class="check full">
            <input type="checkbox" name="gdpr" value="1" required>
            <span>Súhlasím so spracovaním osobných údajov na účel vybavenia objednávky. Podrobnosti nájdete v <a href="ochrana-udajov.php" target="_blank" rel="noopener">zásadách ochrany osobných údajov</a>.</span>
          </label>
          <div class="hp" aria-hidden="true"><label>Nevyplňujte <input name="website" tabindex="-1" autocomplete="off"></label></div>
          <div class="form-msg err full" id="v-err" hidden></div>
          <button class="btn btn-primary full" type="submit">Odoslať objednávku</button>
        </div>
      </form>
      <div class="done" id="v-done" hidden>
        <span class="done-ico" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg></span>
        <h4>Objednávku sme prijali</h4>
        <p><strong id="v-summary"></strong></p>
        <p>Potvrdenie sme poslali na váš e-mail. Čoskoro sa vám ozveme.</p>
      </div>
    </div>
  </div>
</section>

<?php reserve_banner('Chcete sa najprv <span class="script">sami presvedčiť?</span>'); ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
