<?php
defined('ADMIN') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['reset'])) { reset_content('price_tables', 'Tabuľky cenníka sú vrátené na pôvodné.'); redirect(admin_url('prices')); }
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
    // tabuľky cenníka: stĺpce a riadky sa píšu oddelené znakom |, jeden riadok = jeden riadok tabuľky; bez názvu sa tabuľka vynechá
    $tables = [];
    foreach (array_slice(is_array($_POST['t'] ?? null) ? $_POST['t'] : [], 0, 12) as $t) {
        if (!is_array($t)) continue;
        $title = in_line($t['title'] ?? '', 120);
        $cut = fn(string $l): array => array_map(fn($c) => in_line($c, 120), explode('|', $l));
        $head = $cut(in_line($t['head'] ?? '', 400));
        if ($title === '' || count($head) > 6) continue;
        $rows = [];
        foreach (array_slice(preg_split('/\r\n|\r|\n/', in_str($t['rows'] ?? '', 6000)) ?: [], 0, 40) as $line) {
            if (trim($line) !== '') $rows[] = array_slice(array_pad($cut($line), count($head), ''), 0, count($head));
        }
        $tables[] = ['title' => $title, 'head' => $head, 'rows' => $rows];
    }
    $ov['price_tables'] = $tables;
    $r = content_save($ov);
    $r === true ? flash('Ceny sú uložené a zobrazia sa v cenníku.') : flash((string)$r, 'err');
    redirect(admin_url('prices'));
}

admin_head('Ceny', 'prices', 'Zmeňte ceny na jednom mieste. Zobrazia sa v cenníku. Píšte ich tak, ako sa majú zobraziť (napr. „12 €“).');
$tablesUi = $price_tables; $tablesUi[] = ['title' => '', 'head' => ['Vstup / platnosť', 'Cena', 'Cena s permanentkou'], 'rows' => []];   // prázdna tabuľka na pridanie novej
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
    <h2>Cena jednorazového vstupu</h2>
    <p class="lead">Cena hore pri službách (napr. „11 €“) je zároveň suma, ktorú zákazníčka zaplatí pri online platbe za jednu lekciu. Pri službe bez ceny sa online platba nenaponúka.</p>
  </section>
  <section class="card">
    <h2>Permanentky a balíky (tabuľky v cenníku)</h2>
    <p class="lead">Každá tabuľka má názov, hlavičku a riadky. Stĺpce oddeľte znakom <b>|</b>, každý riadok napíšte na nový riadok. Tabuľku zmažete tak, že jej názov vymažete. Úplne dole je prázdna tabuľka na pridanie novej. Nadpis, platnosť a poznámku pod tabuľkami upravíte v Texty stránok.</p>
    <?php foreach ($tablesUi as $i => $t): ?>
      <div class="card" style="margin-top:1rem">
        <?php f_text("t[$i][title]", $t['title'] === '' ? 'Nová tabuľka: názov' : 'Názov tabuľky', $t['title'], ['max' => 120]); ?>
        <?php f_text("t[$i][head]", 'Hlavička (stĺpce oddelené |)', implode(' | ', $t['head']), ['max' => 400]); ?>
        <?php f_area("t[$i][rows]", 'Riadky (každý riadok = jeden riadok tabuľky, stĺpce oddelené |)', implode("
", array_map(fn($r) => implode(' | ', $r), $t['rows'])), ['rows' => max(4, count($t['rows']) + 1), 'max' => 6000]); ?>
      </div>
    <?php endforeach; ?>
  </section>
  <?php f_actions('Vrátiť tabuľky cenníka na pôvodné?'); ?>
</form>
<?php admin_foot();
