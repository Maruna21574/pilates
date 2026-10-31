<?php
defined('ADMIN') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['reset'])) { reset_content('team'); redirect(admin_url('team')); }
    $rows = is_array($_POST['m'] ?? null) ? array_values($_POST['m']) : [];
    $list = [];
    foreach ($rows as $r) {
        if (!is_array($r)) continue;
        $name = in_line($r['name'] ?? '', 60);
        if ($name === '') continue;
        $list[] = ['name' => $name, 'role' => in_line($r['role'] ?? '', 60), 'photo' => in_media($r['photo'] ?? '') ?: null,
                   'bio' => in_str($r['bio'] ?? '', 700), 'focus' => in_lines($r['focus'] ?? '', 6, 120)];
    }
    save_content('team', $list, $list ? 'Tím je uložený.' : 'Tím je prázdny – na webe sa nezobrazí žiadna osoba.');
    redirect(admin_url('team'));
}

function team_item(string $n, array $m): void {
    $name = trim((string)($m['name'] ?? '')); ?>
  <article class="item" data-del-msg="Odstrániť osobu „<?= e($name ?: 'nová osoba') ?>“?">
    <div class="item-h"><span class="num">1</span><strong data-empty="Nová osoba"><?= e($name ?: 'Nová osoba') ?></strong>
      <button type="button" class="iconbtn" data-up aria-label="Vyššie">↑</button>
      <button type="button" class="iconbtn" data-down aria-label="Nižšie">↓</button>
      <button type="button" class="iconbtn del" data-del aria-label="Odstrániť">×</button></div>
    <div class="grid">
      <?php f_text("m[$n][name]", 'Meno a priezvisko', $name, ['max' => 60, 'required' => true, 'attrs' => 'data-title']); ?>
      <?php f_text("m[$n][role]", 'Rola', (string)($m['role'] ?? ''), ['max' => 60, 'placeholder' => 'Inštruktorka']); ?>
      <?php f_area("m[$n][bio]", 'Predstavenie (zobrazí sa po kliknutí na meno)', (string)($m['bio'] ?? ''), ['max' => 700, 'rows' => 4, 'class' => 'full']); ?>
      <?php f_area("m[$n][focus]", 'Na hodine nájdete (každý bod na nový riadok)', implode("\n", $m['focus'] ?? []), ['max' => 800, 'rows' => 3]); ?>
      <?php f_image("m[$n][photo]", 'Fotka (nepovinné)', (string)($m['photo'] ?? ''), ['hint' => '']); ?>
    </div>
  </article>
<?php }

admin_head('Tím', 'team', 'Osoby na stránke O nás. Po kliknutí na meno sa otvorí okno s predstavením.');
?>
<form method="post" class="track">
  <?= csrf_field() ?>
  <div class="note" style="margin-bottom:1rem">Bez fotky sa pri mene zobrazia iniciály. Ak pridáte portrét, zobrazí sa v kruhu.</div>
  <div data-repeater>
    <div class="items">
      <?php foreach ($team as $i => $m) team_item((string)$i, $m); ?>
    </div>
    <template><?php team_item('__N__', ['name' => '', 'role' => 'Inštruktorka', 'bio' => '', 'focus' => []]); ?></template>
    <button type="button" class="btn add-row" data-add>+ Pridať osobu</button>
  </div>
  <?php f_actions('Vrátiť tím na pôvodný?'); ?>
</form>
<?php admin_foot();
