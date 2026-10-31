<?php
defined('ADMIN') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['reset'])) { reset_content('reviews'); redirect(admin_url('reviews')); }
    $rows = is_array($_POST['r'] ?? null) ? array_values($_POST['r']) : [];
    $list = [];
    foreach ($rows as $r) {
        if (!is_array($r)) continue;
        $text = in_str($r['text'] ?? '', 600);
        if ($text === '') continue;
        $list[] = ['name' => in_line($r['name'] ?? '', 60) ?: 'Klientka', 'date' => in_line($r['date'] ?? '', 30), 'rating' => in_int($r['rating'] ?? 5, 1, 5, 5),
                   'source' => (($r['source'] ?? '') === 'google') ? 'google' : '', 'text' => $text];
    }
    $url = in_url($_POST['google_url'] ?? '');
    if (in_line($_POST['google_url'] ?? '') !== '' && $url === '') flash('Odkaz na Google recenzie musí začínať http:// alebo https://. Odkaz nebol uložený.', 'warn');
    $ov = content_load();
    $ov['reviews'] = $list;
    $ov['settings'] = ($ov['settings'] ?? []);
    $ov['settings']['google_reviews_url'] = $url ?: null;
    $res = content_save($ov);
    $res === true ? flash($list ? 'Recenzie sú uložené.' : 'Recenzie sú prázdne – sekcia sa na webe nezobrazí.') : flash((string)$res, 'err');
    redirect(admin_url('reviews'));
}

function review_item(string $n, array $r): void {
    $name = trim((string)($r['name'] ?? '')); ?>
  <article class="item" data-del-msg="Odstrániť túto recenziu?">
    <div class="item-h"><span class="num">1</span><strong data-empty="Nová recenzia"><?= e($name ?: 'Nová recenzia') ?></strong>
      <button type="button" class="iconbtn" data-up aria-label="Vyššie">↑</button>
      <button type="button" class="iconbtn" data-down aria-label="Nižšie">↓</button>
      <button type="button" class="iconbtn del" data-del aria-label="Odstrániť">×</button></div>
    <div class="grid three">
      <?php f_text("r[$n][name]", 'Meno', $name, ['max' => 60, 'attrs' => 'data-title']); ?>
      <?php f_text("r[$n][date]", 'Dátum (text)', (string)($r['date'] ?? ''), ['max' => 30, 'placeholder' => 'napr. október 2026']); ?>
      <?php f_select("r[$n][rating]", 'Hodnotenie', [5 => '5 hviezdičiek', 4 => '4 hviezdičky', 3 => '3 hviezdičky', 2 => '2 hviezdičky', 1 => '1 hviezdička'], (string)($r['rating'] ?? 5)); ?>
      <?php f_area("r[$n][text]", 'Text recenzie', (string)($r['text'] ?? ''), ['max' => 600, 'rows' => 3, 'class' => 'full']); ?>
      <?php f_select("r[$n][source]", 'Zdroj', ['' => 'Bez loga', 'google' => 'Google (zobrazí logo Google)'], (string)($r['source'] ?? ''), ['hint' => 'Logo Google dajte len pri recenziách, ktoré naozaj pochádzajú z Google.', 'class' => 'full']); ?>
    </div>
  </article>
<?php }

admin_head('Recenzie', 'reviews', 'Slider s recenziami na úvodnej stránke.');
?>
<form method="post" class="track">
  <?= csrf_field() ?>
  <div class="flash warn">Zverejňujte len <strong>skutočné</strong> recenzie a len so súhlasom autorov. Vymyslené alebo upravené hodnotenia sú v EÚ zakázané. Sekcia na webe zmizne, keď zoznam vyprázdnite.</div>
  <section class="card">
    <h2>Odkaz na všetky recenzie</h2>
    <div class="grid">
      <?php f_text('google_url', 'Odkaz na recenzie štúdia na Google (nepovinné)', (string)($settings['google_reviews_url'] ?? ''), ['type' => 'url', 'max' => 300, 'class' => 'full', 'hint' => 'Ak ho vyplníte, pri slideri sa zobrazí tlačidlo „Všetky recenzie“.']); ?>
    </div>
  </section>
  <div data-repeater>
    <div class="items">
      <?php foreach ($reviews as $i => $r) review_item((string)$i, $r); ?>
    </div>
    <template><?php review_item('__N__', ['name' => '', 'date' => '', 'rating' => 5, 'text' => '', 'source' => 'google']); ?></template>
    <button type="button" class="btn add-row" data-add>+ Pridať recenziu</button>
  </div>
  <?php f_actions('Vrátiť recenzie na pôvodné (zástupné)?'); ?>
</form>
<?php admin_foot();
