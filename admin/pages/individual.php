<?php
defined('ADMIN') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['reset'])) { reset_content('individual'); redirect(admin_url('individual')); }
    $px = in_int($_POST['px'] ?? 50, 0, 100, 50); $py = in_int($_POST['py'] ?? 50, 0, 100, 50);
    $img = in_media($_POST['image'] ?? '');
    if (in_line($_POST['name'] ?? '') === '') { flash('Názov nesmie byť prázdny.', 'err'); redirect(admin_url('individual')); }
    save_content('individual', [
        'name' => in_line($_POST['name'], 60), 'tag' => in_line($_POST['tag'] ?? '', 60), 'lead' => in_str($_POST['lead'] ?? '', 260),
        'text' => in_str($_POST['text'] ?? '', 900), 'for' => in_lines($_POST['for'] ?? '', 8, 140), 'images' => $img !== '' ? [$img] : [],
        'pos' => ($px === 50 && $py === 50) ? '' : "{$px}% {$py}%", 'price' => in_line($_POST['price'] ?? '', 40),
    ]);
    redirect(admin_url('individual'));
}

$i = $individual; $pos = ['50', '50'];
if (!empty($i['pos']) && preg_match('/^(\d{1,3})%\s+(\d{1,3})%$/', $i['pos'], $m)) $pos = [$m[1], $m[2]];
admin_head('Individuálne lekcie', 'individual', 'Lekcie pre jednu alebo dve osoby – objednávajú sa dohodou, nie podľa rozvrhu.');
?>
<form method="post" class="track">
  <?= csrf_field() ?>
  <section class="card">
    <div class="grid">
      <?php f_text('name', 'Názov', (string)$i['name'], ['max' => 60, 'required' => true]); ?>
      <?php f_text('tag', 'Štítok', (string)$i['tag'], ['max' => 60, 'hint' => 'Napr. „1 alebo 2 osoby“']); ?>
      <?php f_area('lead', 'Úvodná veta', (string)$i['lead'], ['max' => 260, 'rows' => 2, 'class' => 'full']); ?>
      <?php f_area('text', 'Popis', (string)$i['text'], ['max' => 900, 'rows' => 5, 'class' => 'full']); ?>
      <?php f_area('for', 'Pre koho je určené (každý bod na nový riadok)', implode("\n", $i['for'] ?? []), ['max' => 1200, 'rows' => 4]); ?>
      <?php f_text('price', 'Cena', (string)($i['price'] ?? ''), ['max' => 40, 'placeholder' => 'napr. 35 €']); ?>
      <?php f_image('image', 'Fotka', (string)($i['images'][0] ?? ''), ['class' => 'full']); ?>
      <div class="fld"><span class="lbl">Výrez fotky</span>
        <div class="row2"><div class="suffix" data-s="% zľava"><input type="number" name="px" value="<?= e($pos[0]) ?>" min="0" max="100" aria-label="Vodorovne"></div><div class="suffix" data-s="% zhora"><input type="number" name="py" value="<?= e($pos[1]) ?>" min="0" max="100" aria-label="Zvisle"></div></div></div>
    </div>
  </section>
  <?php f_actions('Vrátiť individuálne lekcie na pôvodné?'); ?>
</form>
<?php admin_foot();
