<?php
http_response_code(404);
// Stránka sa môže zobraziť na ľubovoľnej (aj hlbokej) adrese, preto relatívne odkazy ukotvíme na koreň webu.
$baseHref = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
$noindex  = true;
$pageTitle = 'Stránka sa nenašla';
$pageDesc  = 'Hľadaná stránka neexistuje alebo bola presunutá.';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/partials.php';
page_hero('Chyba 404', 'Táto stránka sa <span class="script" style="font-size:1.15em">stratila</span>', 'Hľadaná stránka neexistuje alebo bola presunutá. Vráťte sa na úvod alebo si vyberte, kam chcete ísť.', true);
?>

<section class="section">
  <div class="wrap">
    <div class="lost">
      <a class="lost-card" href="index.php"><b>Úvod</b><span>Späť na hlavnú stránku</span></a>
      <a class="lost-card" href="cvicenia.php"><b>Cvičenia</b><span>Čo je čo a pre koho</span></a>
      <a class="lost-card" href="rezervacia.php"><b>Rezervácia</b><span>Vyberte si termín lekcie</span></a>
      <a class="lost-card" href="kontakt.php"><b>Kontakt</b><span>Radi vám poradíme</span></a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
