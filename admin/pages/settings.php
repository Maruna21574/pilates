<?php
defined('ADMIN') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $ov = content_load();
    if (isset($_POST['reset'])) {
        unset($ov['settings']['voucher_validity_months'], $ov['settings']['book_months']);
        $r = content_save($ov); $r === true ? flash('Nastavenia sú vrátené na pôvodné.') : flash((string)$r, 'err');
        redirect(admin_url('settings'));
    }
    $ov['settings'] = $ov['settings'] ?? [];
    $ov['settings']['voucher_validity_months'] = in_int($_POST['validity'] ?? 12, 1, 60, 12);
    $ov['settings']['book_months'] = in_int($_POST['book_months'] ?? 12, 1, 24, 12);
    $r = content_save($ov); $r === true ? flash('Nastavenia sú uložené.') : flash((string)$r, 'err');
    redirect(admin_url('settings'));
}

admin_head('Poukážky a rezervácie', 'settings', 'Pravidlá, ktoré sa premietnu na webe, v e-mailoch a na tlačenej poukážke.');
?>
<form method="post" class="track">
  <?= csrf_field() ?>
  <section class="card">
    <h2>Darčekové poukážky</h2>
    <div class="grid">
      <?php f_text('validity', 'Platnosť poukážky (mesiace)', (string)$settings['voucher_validity_months'], ['type' => 'number', 'min' => 1, 'max_n' => 60, 'hint' => 'Vytlačí sa na poukážke a uvedie sa v objednávke.']); ?>
    </div>
  </section>
  <section class="card">
    <h2>Rezervácie</h2>
    <div class="grid">
      <?php f_text('book_months', 'Ako ďaleko dopredu sa dá rezervovať (mesiace)', (string)$settings['book_months'], ['type' => 'number', 'min' => 1, 'max_n' => 24, 'hint' => 'Návštevník vidí termíny od dnes po koniec tohto mesiaca (aktuálny mesiac sa počíta ako prvý).']); ?>
    </div>
    <p class="note" style="margin-top:1rem">Kapacitu lekcií nastavíte pri cvičení, dni a časy v rozvrhu. Žiadosti o rezerváciu a objednávky poukážok prichádzajú e-mailom na adresu z časti Kontakt a údaje.</p>
  </section>
  <?php f_actions('Vrátiť nastavenia na pôvodné?'); ?>
</form>
<?php admin_foot();
