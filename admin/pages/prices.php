<?php
defined('ADMIN') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $p = is_array($_POST['price'] ?? null) ? $_POST['price'] : [];
    $ov = content_load();
    $list = classes_as_list($classes);
    foreach ($list as &$row) $row['price'] = in_line($p[$row['id']] ?? '', 40);
    unset($row);
    $ov['classes'] = $list;
    $ind = $ov['individual'] ?? [];
    $ind += ['name' => $individual['name'], 'tag' => $individual['tag'], 'lead' => $individual['lead'], 'text' => $individual['text'], 'for' => $individual['for'], 'images' => $individual['images'], 'pos' => $individual['pos'] ?? ''];
    $ind['price'] = in_line($p['_individual'] ?? '', 40);
    $ov['individual'] = $ind;
    $ov['settings'] = ($ov['settings'] ?? []) + [];
    $ov['settings']['pass_price'] = in_line($_POST['pass_price'] ?? '', 40);
    $r = content_save($ov);
    $r === true ? flash('Ceny sú uložené a zobrazia sa v cenníku.') : flash((string)$r, 'err');
    redirect(admin_url('prices'));
}

admin_head('Ceny', 'prices', 'Zmeňte ceny na jednom mieste. Zobrazia sa v cenníku. Píšte ich tak, ako sa majú zobraziť (napr. „12 €“).');
?>
<form method="post" class="track">
  <?= csrf_field() ?>
  <section class="card">
    <h2>Služby</h2>
    <table class="tbl">
      <thead><tr><th>Služba</th><th>Cena</th></tr></thead>
      <tbody>
        <?php foreach ($classes as $id => $c): ?>
          <tr>
            <td><?php if (!empty($c['images'][0])): ?><img class="thumb" src="../<?= e(media($c['images'][0])) ?>" alt=""><?php endif; ?><strong><?= e($c['name']) ?></strong> <span class="tag"><?= (int)$c['capacity'] === 1 ? '1 osoba' : 'max. ' . (int)$c['capacity'] . ' osôb' ?></span></td>
            <td style="width:220px"><input type="text" name="price[<?= e($id) ?>]" value="<?= e((string)($c['price'] ?? '')) ?>" maxlength="40" placeholder="napr. 12 €" aria-label="Cena – <?= e($c['name']) ?>"></td>
          </tr>
        <?php endforeach; ?>
        <tr>
          <td><?php if (!empty($individual['images'][0])): ?><img class="thumb" src="../<?= e(media($individual['images'][0])) ?>" alt=""><?php endif; ?><strong><?= e($individual['name']) ?></strong> <span class="tag"><?= e($individual['tag']) ?></span></td>
          <td><input type="text" name="price[_individual]" value="<?= e((string)($individual['price'] ?? '')) ?>" maxlength="40" placeholder="napr. 35 €" aria-label="Cena – individuálne lekcie"></td>
        </tr>
      </tbody>
    </table>
  </section>
  <section class="card">
    <h2>Permanentky a balíky</h2>
    <div class="grid">
      <?php f_text('pass_price', 'Cena balíka', (string)($settings['pass_price'] ?? ''), ['max' => 40, 'placeholder' => 'napr. 45 €', 'hint' => 'Zobrazí sa v cenníku pri „Permanentky a balíky“. Text tejto časti upravíte v Texty stránok.']); ?>
    </div>
  </section>
  <?php f_actions(); ?>
</form>
<?php admin_foot();
