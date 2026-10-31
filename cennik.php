<?php
$pageTitle = 'Cenník';
$pageDesc  = 'Cenník lekcií v Organic Pilates Studio Žilina: reformer, barre, podložky, Ladder Barrel, Tehuľky, Vital Pilates a individuálne lekcie.';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/partials.php';
page_hero('Cenník', 'Jasné ceny, <span class="script" style="font-size:1.15em">bez prekvapení</span>', T('price_lead'), true);

/** Cena z data.php (kľúč 'price'); kým nie je vyplnená, ukáže sa zástupná hodnota. */
function price_html(array $item): string {
    return !empty($item['price']) ? e($item['price']) : '<span class="placeholder">00 €</span>';
}
function svc_thumb(array $item): string {
    if (!empty($item['images'][0])) return media($item['images'][0]);
    if (!empty($item['video'])) return media(video_poster_path($item['video']));
    return media('studio-02.jpg');
}

/** Jedna služba v zozname. */
function service_card(string $id, array $item, string $href, string $btn): void { ?>
  <article class="svc reveal">
    <img class="svc-thumb" src="<?= e(svc_thumb($item)) ?>" alt="" loading="lazy"<?= !empty($item['pos']) ? ' style="object-position:' . e($item['pos']) . '"' : '' ?>>
    <div class="svc-body">
      <h3><?= e($item['name']) ?></h3>
      <p class="svc-price">Cena: <b><?= price_html($item) ?></b></p>
      <p class="svc-lead"><?= e($item['lead']) ?></p>
      <details class="svc-more">
        <summary><span class="more">Zobraziť viac</span><span class="less">Zobraziť menej</span></summary>
        <p><?= e($item['text']) ?></p>
        <ul class="plain svc-facts">
          <?php if (!empty($item['capacity'])): ?>
            <li><?= (int)$item['capacity'] === 1 ? 'Individuálne, 1 osoba' : 'Max. ' . (int)$item['capacity'] . ' osôb' ?></li>
            <li><?= (int)$item['duration'] ?> min</li>
          <?php else: ?>
            <li><?= e($item['tag']) ?></li>
          <?php endif; ?>
        </ul>
        <a class="btn btn-primary btn-sm" href="<?= e($href) ?>"><?= e($btn) ?></a>
      </details>
    </div>
  </article>
<?php }
?>

<section class="section" style="padding-top:3rem">
  <div class="wrap">
    <div class="svc-head reveal">
      <h2>Vyberte si službu</h2>
      <a class="btn btn-ghost-dark btn-sm" href="rezervacia.php">Rezervácia lekcií</a>
    </div>

    <div class="svc-grid">
      <?php foreach ($classes as $id => $c) service_card($id, $c, 'rezervacia.php?class=' . $id, 'Rezervovať'); ?>
      <?php service_card('individualne', $individual, 'kontakt.php', 'Dohodnúť lekciu'); ?>
    </div>

    <?php if ($price_tables): ?>
    <div class="ptables">
      <div class="svc-head reveal">
        <div>
          <h2><?= e(T('pass_title')) ?></h2>
          <p class="ptables-lead"><?= e(T('pass_text')) ?><?php if (T('price_valid') !== ''): ?> <b><?= e(T('price_valid')) ?></b><?php endif; ?></p>
        </div>
      </div>
      <div class="ptables-grid">
        <?php foreach ($price_tables as $t): ?>
        <div class="ptable reveal">
          <h3><?= e($t['title']) ?></h3>
          <div class="ptable-scroll"><table>
            <thead><tr><?php foreach ($t['head'] as $h): ?><th><?= e($h) ?></th><?php endforeach; ?></tr></thead>
            <tbody>
              <?php foreach ($t['rows'] as $row): ?>
              <tr><?php foreach ($row as $i => $cell): ?><<?= $i === 0 ? 'th scope="row"' : 'td' ?>><?= e($cell) ?></<?= $i === 0 ? 'th' : 'td' ?>><?php endforeach; ?></tr>
              <?php endforeach; ?>
            </tbody>
          </table></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (T('price_note') !== ''): ?><p class="ptables-note reveal"><?= e(T('price_note')) ?></p><?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="pass pass-gift reveal">
      <div>
        <h3>Darčekové poukážky</h3>
        <p>Darujte lekcie pilatesu na sumu alebo na konkrétne cvičenie.</p>
      </div>
      <a class="btn btn-primary btn-sm" href="poukazky.php">Objednať poukážku</a>
    </div>
  </div>
</section>

<section class="section soft compact">
  <div class="wrap">
    <div class="section-head center reveal"><span class="eyebrow">Otázky</span><h2>Platby a rezervácie</h2></div>
    <div class="faq">
      <?php foreach ($faq as $q): ?><details class="reveal"><summary><?= e($q[0]) ?></summary><p><?= e($q[1]) ?></p></details><?php endforeach; ?>
    </div>
  </div>
</section>

<?php reserve_banner(); ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
