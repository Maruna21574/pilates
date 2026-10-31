<?php
defined('ADMIN') or exit;

$ov = content_load();
$noPrice = array_keys(array_filter($classes, fn($c) => trim((string)($c['price'] ?? '')) === ''));
if (trim((string)($individual['price'] ?? '')) === '') $noPrice[] = 'individualne';
$placeholderReviews = count(array_filter($reviews, fn($r) => $r['name'] === 'Meno Priezvisko'));
$placeholderTeam = count(array_filter($team, fn($m) => str_contains($m['name'], 'doplníme')));

$checks = [];
$dataOk = is_dir(content_dir()) && is_writable(content_dir());
$checks[] = [$dataOk ? 'ok' : 'err', $dataOk ? 'Ukladanie zmien funguje.' : 'Do priečinka data/ sa nedá zapisovať – zmeny sa neuložia. Nastavte mu oprávnenia na zápis.', null];
$upDir = dirname(__DIR__, 2) . '/assets/media/uploads';
$upOk = is_dir($upDir) && is_writable($upDir);
$checks[] = [$upOk ? 'ok' : 'warn', $upOk ? 'Nahrávanie obrázkov funguje.' : 'Do priečinka assets/media/uploads sa nedá zapisovať – nahrávanie obrázkov nebude fungovať.', null];
$checks[] = [!$noPrice ? 'ok' : 'warn', !$noPrice ? 'Všetky služby majú cenu.' : count($noPrice) . ' z ' . (count($classes) + 1) . ' služieb nemá vyplnenú cenu (v cenníku sa zobrazí „00 €“).', admin_url('prices')];
$checks[] = [!isset($ov['schedule']) ? 'warn' : 'ok', !isset($ov['schedule']) ? 'Rozvrh je zatiaľ ukážkový. Zadajte skutočný rozvrh lekcií.' : 'Rozvrh je nastavený.', admin_url('schedule')];
$checks[] = [$placeholderReviews === 0 ? 'ok' : 'warn', $placeholderReviews === 0 ? 'Recenzie sú vyplnené.' : $placeholderReviews . ' recenzií je zástupných. Vložte skutočné (so súhlasom autorov) alebo ich odstráňte.', admin_url('reviews')];
$checks[] = [$placeholderTeam === 0 ? 'ok' : 'warn', $placeholderTeam === 0 ? 'Tím je vyplnený.' : 'V tíme je zástupná osoba. Doplňte meno alebo ju odstráňte.', admin_url('team')];
$checks[] = ['warn', 'Stránka Ochrana osobných údajov obsahuje polia označené [doplniť] (obchodné meno, IČO, doby uchovávania). Upravte súbor ochrana-udajov.php.', null];
if ($announcement) $checks[] = ['ok', 'Oznam hore je zapnutý: „' . mb_substr($announcement['text'], 0, 80) . '“', admin_url('announce')];

admin_head('Prehľad', 'home', 'Vitajte v správe webu. Tu upravíte texty, ceny, rozvrh, fotky aj ďalšie veci.');
?>
<div class="stats">
  <div class="stat"><b><?= count($classes) ?></b><span>cvičení</span></div>
  <div class="stat"><b><?= count($schedule) ?></b><span>lekcií týždenne</span></div>
  <div class="stat"><b><?= count($reviews) ?></b><span>recenzií</span></div>
  <div class="stat"><b><?= count($gallery) ?></b><span>fotiek v galérii</span></div>
</div>

<section class="card">
  <h2>Čo ešte treba doplniť</h2>
  <ul class="checks">
    <?php foreach ($checks as [$type, $text, $link]): ?>
      <li class="<?= e($type) ?>"><span class="dot"></span><div><?= e($text) ?></div><?php if ($link): ?><a href="<?= e($link) ?>">Upraviť →</a><?php endif; ?></li>
    <?php endforeach; ?>
  </ul>
</section>

<section class="card">
  <h2>Rýchle odkazy</h2>
  <div class="quick">
    <a href="<?= e(admin_url('prices')) ?>"><b>Ceny</b><span>Zmeňte ceny služieb a permanentky</span></a>
    <a href="<?= e(admin_url('schedule')) ?>"><b>Rozvrh</b><span>Dni a časy lekcií</span></a>
    <a href="<?= e(admin_url('classes')) ?>"><b>Cvičenia</b><span>Texty, fotky, kapacity</span></a>
    <a href="<?= e(admin_url('announce')) ?>"><b>Oznam hore</b><span>Sviatky, zmena rozvrhu</span></a>
    <a href="<?= e(admin_url('texts')) ?>"><b>Texty stránok</b><span>Nadpisy a odseky</span></a>
    <a href="<?= e(admin_url('gallery')) ?>"><b>Galéria</b><span>Pridať alebo odstrániť fotky</span></a>
  </div>
</section>

<section class="card">
  <h2>Ako to funguje</h2>
  <p>Každá zmena sa po kliknutí na <strong>Uložiť zmeny</strong> okamžite zobrazí na webe. Pred každým uložením sa automaticky vytvorí záloha, takže sa dá vrátiť k predchádzajúcej verzii (<a href="<?= e(admin_url('backups')) ?>">Zálohy</a>). Tlačidlo <strong>Obnoviť pôvodné</strong> vráti danú časť do pôvodného stavu.</p>
  <?php if (!empty($ov['_updated'])): ?><p class="note">Posledná zmena: <?= e(date('j. n. Y H:i', (int)strtotime((string)$ov['_updated']))) ?></p><?php endif; ?>
</section>
<?php admin_foot();
