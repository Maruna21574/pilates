<?php
defined('ADMIN') or exit;

/** Prečíta jeden riadok formulára cvičenia. */
function read_class_row(array $r, array $prev): array {
    $px = in_int($r['px'] ?? 50, 0, 100, 50); $py = in_int($r['py'] ?? 50, 0, 100, 50);
    $img = in_media($r['image'] ?? '');
    $video = in_line($r['video'] ?? '', 80);
    $videoOk = $video !== '' && video_exists($video);
    $color = (string)($r['color'] ?? '');
    return [
        'id' => $prev['id'] ?? '',
        'name' => in_line($r['name'] ?? '', 60), 'short' => $prev['short'] ?? '', 'blurb' => in_line($r['blurb'] ?? '', 90),
        'tag' => in_line($r['tag'] ?? '', 60), 'lead' => in_str($r['lead'] ?? '', 260), 'text' => in_str($r['text'] ?? '', 900),
        'equipment' => in_line($r['equipment'] ?? '', 160), 'capacity' => in_int($r['capacity'] ?? 1, 1, 60, 1), 'duration' => in_int($r['duration'] ?? 55, 5, 240, 55),
        'color' => preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#4a7c77',
        'for' => in_lines($r['for'] ?? '', 8, 140), 'images' => $img !== '' ? [$img] : [], 'video' => $videoOk ? $video : null,
        'pos' => ($px === 50 && $py === 50) ? '' : "{$px}% {$py}%", 'price' => in_line($r['price'] ?? '', 40),
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['reset'])) { reset_content('classes', 'Cvičenia sú vrátené na pôvodné.'); redirect(admin_url('classes')); }
    $rows = is_array($_POST['c'] ?? null) ? array_values($_POST['c']) : [];
    $list = []; $used = []; $errors = [];
    foreach ($rows as $r) {
        if (!is_array($r)) continue;
        $id = preg_match('/^[a-z0-9-]{1,40}$/', (string)($r['id'] ?? '')) ? (string)$r['id'] : '';
        $row = read_class_row($r, ['id' => $id, 'short' => $classes[$id]['short'] ?? '']);
        if ($row['name'] === '') { $errors[] = 'Každé cvičenie musí mať názov.'; continue; }
        if ($id === '') { $base = slugify($row['name']); $id = $base; $n = 2; while (isset($classes[$id]) || isset($used[$id])) $id = $base . '-' . $n++; }
        $row['id'] = $id; $used[$id] = true; $list[] = $row;
    }
    if (!$list) $errors[] = 'Musí zostať aspoň jedno cvičenie.';
    if ($errors) { foreach (array_unique($errors) as $m) flash($m, 'err'); redirect(admin_url('classes')); }
    // lekcie v rozvrhu, ktorých cvičenie bolo odstránené, sa zahodia
    $ov = content_load();
    $removed = array_diff(array_keys($classes), array_column($list, 'id'));
    if ($removed && isset($ov['schedule'])) {
        $ov['schedule'] = array_values(array_filter($ov['schedule'], fn($s) => !in_array($s['class'] ?? '', $removed, true)));
    }
    $ov['classes'] = $list;
    $r = content_save($ov);
    $r === true ? flash('Cvičenia sú uložené.' . ($removed ? ' Lekcie odstránených cvičení boli zmazané z rozvrhu.' : '')) : flash((string)$r, 'err');
    redirect(admin_url('classes'));
}

$videoOpts = [];
foreach ($videos as $v) $videoOpts[$v['file']] = $v['title'] !== '' ? $v['title'] . ' (' . $v['file'] . ')' : $v['file'];

function class_item(string $n, array $c, array $videoOpts): void {
    $pos = ['50', '50'];
    if (!empty($c['pos']) && preg_match('/^(\d{1,3})%\s+(\d{1,3})%$/', $c['pos'], $m)) $pos = [$m[1], $m[2]];
    $name = trim((string)($c['name'] ?? ''));
    ?><article class="item" data-del-msg="Odstrániť cvičenie „<?= e($name ?: 'nové cvičenie') ?>“? Zmizne z webu aj z rozvrhu.">
      <input type="hidden" name="c[<?= e($n) ?>][id]" value="<?= e((string)($c['id'] ?? '')) ?>">
      <div class="item-h">
        <span class="num">1</span><strong data-empty="Nové cvičenie"><?= e($name ?: 'Nové cvičenie') ?></strong>
        <button type="button" class="iconbtn" data-up aria-label="Posunúť vyššie" title="Vyššie">↑</button>
        <button type="button" class="iconbtn" data-down aria-label="Posunúť nižšie" title="Nižšie">↓</button>
        <button type="button" class="iconbtn del" data-del aria-label="Odstrániť" title="Odstrániť">×</button>
      </div>
      <div class="grid">
        <?php f_text("c[$n][name]", 'Názov', $name, ['max' => 60, 'required' => true, 'attrs' => 'data-title']); ?>
        <?php f_text("c[$n][tag]", 'Štítok (krátko, pri nadpise)', (string)($c['tag'] ?? ''), ['max' => 60, 'hint' => 'Napr. „Malá skupina · max. 4“']); ?>
        <?php f_text("c[$n][blurb]", 'Krátky popis do rezervácie', (string)($c['blurb'] ?? ''), ['max' => 90, 'class' => 'full', 'hint' => 'Zobrazí sa pod názvom pri výbere cvičenia v rezervácii.']); ?>
        <?php f_area("c[$n][lead]", 'Úvodná veta', (string)($c['lead'] ?? ''), ['max' => 260, 'rows' => 2, 'class' => 'full']); ?>
        <?php f_area("c[$n][text]", 'Popis cvičenia', (string)($c['text'] ?? ''), ['max' => 900, 'rows' => 5, 'class' => 'full']); ?>
        <?php f_area("c[$n][for]", 'Pre koho je určené (každý bod na nový riadok)', implode("\n", $c['for'] ?? []), ['max' => 1200, 'rows' => 4]); ?>
        <?php f_text("c[$n][equipment]", 'Pomôcky', (string)($c['equipment'] ?? ''), ['max' => 160]); ?>
        <?php f_text("c[$n][capacity]", 'Najviac osôb na lekcii', (string)($c['capacity'] ?? 8), ['type' => 'number', 'min' => 1, 'max_n' => 60, 'hint' => 'Pri 1 sa lekcia berie ako individuálna.']); ?>
        <?php f_text("c[$n][duration]", 'Dĺžka lekcie (minúty)', (string)($c['duration'] ?? 55), ['type' => 'number', 'min' => 5, 'max_n' => 240]); ?>
        <?php f_text("c[$n][price]", 'Cena', (string)($c['price'] ?? ''), ['max' => 40, 'placeholder' => 'napr. 12 €', 'hint' => 'Zobrazí sa v cenníku. Ceny sa dajú upraviť aj hromadne v časti Ceny.']); ?>
        <div class="fld"><label><span>Farba v rezervácii</span><input type="color" name="c[<?= e($n) ?>][color]" value="<?= e((string)($c['color'] ?? '#4a7c77')) ?>"></label></div>
        <?php f_image("c[$n][image]", 'Hlavná fotka', (string)($c['images'][0] ?? ''), ['class' => 'full']); ?>
        <div class="fld"><span class="lbl">Výrez fotky (kde je hlavný záber)</span>
          <div class="row2"><div class="suffix" data-s="% zľava"><input type="number" name="c[<?= e($n) ?>][px]" value="<?= e($pos[0]) ?>" min="0" max="100" aria-label="Vodorovne"></div><div class="suffix" data-s="% zhora"><input type="number" name="c[<?= e($n) ?>][py]" value="<?= e($pos[1]) ?>" min="0" max="100" aria-label="Zvisle"></div></div>
          <small>50 / 50 = stred. Mení sa, keď je na kartách osoba orezaná.</small></div>
        <?php f_select("c[$n][video]", 'Video na stránke cvičení', ['' => '— žiadne —'] + $videoOpts, (string)($c['video'] ?? ''), ['hint' => 'Ak je video, zobrazí sa namiesto fotky.']); ?>
      </div>
    </article>
<?php }

$new = ['id' => '', 'name' => '', 'tag' => '', 'blurb' => '', 'lead' => '', 'text' => '', 'for' => [], 'equipment' => '', 'capacity' => 8, 'duration' => 55, 'price' => '', 'color' => '#5a8a84', 'images' => [], 'video' => null, 'pos' => ''];

admin_head('Cvičenia', 'classes', 'Typy lekcií. Poradie, texty, fotky aj kapacity – nové cvičenie sa hneď objaví v menu, rezervácii aj cenníku.');
?>
<form method="post" class="track">
  <?= csrf_field() ?>
  <div class="note" style="margin-bottom:1rem">Poradie tu určuje poradie na webe. Cvičenie, ktoré odstránite, zmizne aj z rozvrhu. Cvičenia sa dajú zoradiť šípkami.</div>
  <div data-repeater>
    <div class="items">
      <?php foreach ($classes as $id => $c) class_item((string)(abs(crc32($id)) % 100000), ['id' => $id] + $c, $videoOpts); ?>
    </div>
    <template><?php class_item('__N__', $new, $videoOpts); ?></template>
    <button type="button" class="btn add-row" data-add>+ Pridať cvičenie</button>
  </div>
  <?php f_actions('Vrátiť cvičenia na pôvodné (zmaže všetky vaše úpravy cvičení)?'); ?>
</form>
<?php admin_foot();
