<?php
$pageTitle = 'Ochrana osobných údajov';
$pageDesc  = 'Zásady ochrany osobných údajov Organic Pilates Studio Žilina: aké údaje spracúvame, na aký účel a aké máte práva.';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/partials.php';
page_hero('Súkromie', 'Ochrana <span class="script" style="font-size:1.15em">osobných údajov</span>', 'Ako nakladáme s vašimi údajmi, na aký účel a aké máte práva.');
$todo = fn(string $t) => '<span class="placeholder">[' . e($t) . ']</span>';
?>

<section class="section">
  <div class="wrap">
    <article class="prose">
      <p class="lead">Tieto zásady vysvetľujú, ako spracúvame osobné údaje návštevníkov webu a klientov štúdia v súlade s Nariadením (EÚ) 2016/679 (GDPR) a zákonom č. 18/2018 Z. z. o ochrane osobných údajov.</p>

      <h2>1. Prevádzkovateľ</h2>
      <p>
        <strong><?= e($site['name']) ?></strong>, <?= e($site['address']) ?><br>
        <?= $todo('obchodné meno a IČO doplniť') ?><br>
        E-mail: <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a><br>
        Telefón: <a href="tel:<?= e($site['phone_href']) ?>"><?= e($site['phone']) ?></a>
      </p>

      <h2>2. Aké údaje spracúvame a prečo</h2>
      <ul>
        <li><strong>Kontaktný formulár</strong> – meno, e-mail, telefón (ak ho uvediete) a text správy. Spracúvame ich na vybavenie vašej požiadavky. Právny základ: váš súhlas (čl. 6 ods. 1 písm. a) GDPR).</li>
        <li><strong>Rezervácia lekcií</strong> – meno, e-mail, telefón a vybraná lekcia. Spracúvame ich na evidenciu a organizáciu lekcií. Právny základ: opatrenia pred uzavretím zmluvy a plnenie zmluvy (čl. 6 ods. 1 písm. b) GDPR).</li>
        <li><strong>Prevádzka webu</strong> – technické údaje, ktoré vzniknú pri návšteve stránky (napríklad IP adresa a čas prístupu v záznamoch servera). Spracúvame ich na zabezpečenie a prevádzku webu. Právny základ: oprávnený záujem (čl. 6 ods. 1 písm. f) GDPR).</li>
      </ul>

      <h2>3. Ako dlho údaje uchovávame</h2>
      <p>Údaje zo správ uchovávame po dobu potrebnú na vybavenie požiadavky, najdlhšie <?= $todo('doplniť dobu') ?>. Údaje o rezerváciách uchovávame <?= $todo('doplniť dobu') ?>. Technické záznamy servera uchováva poskytovateľ hostingu podľa svojich pravidiel.</p>

      <h2>4. Komu údaje sprístupňujeme</h2>
      <p>Osobné údaje nepredávame. Môžu ich spracúvať len poskytovatelia služieb, ktorí nám zabezpečujú prevádzku webu a e-mailovej komunikácie (hosting a e-mail), a to len v rozsahu nevyhnutnom na poskytovanie týchto služieb. <?= $todo('doplniť zoznam sprostredkovateľov') ?></p>

      <h2>5. Cookies a písma</h2>
      <p>Web v súčasnosti nepoužíva analytické ani marketingové cookies a neodosiela údaje návštevníkov tretím stranám. Písma sú uložené priamo na tomto webe. Ak sa v budúcnosti pridajú nástroje, ktoré cookies používajú (napríklad analytika alebo vložené mapy), tieto zásady aktualizujeme a požiadame vás o súhlas.</p>

      <h2>6. Vaše práva</h2>
      <p>Ako dotknutá osoba máte právo:</p>
      <ul>
        <li>na prístup k svojim osobným údajom,</li>
        <li>na opravu nesprávnych alebo neúplných údajov,</li>
        <li>na vymazanie údajov, ak na to nastanú dôvody podľa GDPR,</li>
        <li>na obmedzenie spracúvania,</li>
        <li>na prenosnosť údajov,</li>
        <li>namietať proti spracúvaniu založenému na oprávnenom záujme,</li>
        <li>kedykoľvek odvolať udelený súhlas (odvolanie nemá vplyv na zákonnosť spracúvania pred jeho odvolaním).</li>
      </ul>
      <p>Svoje práva si môžete uplatniť e-mailom na <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a>.</p>

      <h2>7. Právo podať sťažnosť</h2>
      <p>Ak sa domnievate, že pri spracúvaní došlo k porušeniu pravidiel ochrany osobných údajov, máte právo podať sťažnosť dozornému orgánu: Úrad na ochranu osobných údajov Slovenskej republiky, Hraničná 12, 820 07 Bratislava 27, <a href="https://dataprotection.gov.sk" target="_blank" rel="noopener">dataprotection.gov.sk</a>.</p>

      <h2>8. Zmeny zásad</h2>
      <p>Tieto zásady môžeme upraviť. Aktuálna verzia je vždy zverejnená na tejto stránke.<br><span class="muted">Posledná aktualizácia: <?= $todo('doplniť dátum') ?></span></p>
    </article>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
