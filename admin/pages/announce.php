<?php
defined('ADMIN') or exit;

$ov = content_load()['announcement'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $on = !empty($_POST['enabled']);
    $text = in_line($_POST['text'] ?? '', 200);
    $linkRaw = in_line($_POST['link'] ?? '', 300);
    $link = in_url($linkRaw, true);
    if ($on && $text === '') { flash('Zadajte text oznamu, alebo oznam vypnite.', 'err'); $_SESSION['old'] = $_POST; redirect(admin_url('announce')); }
    if ($linkRaw !== '' && $link === '') { flash('Odkaz musí byť adresa začínajúca http(s):// alebo názov stránky, napr. rezervacia.php.', 'err'); $_SESSION['old'] = $_POST; redirect(admin_url('announce')); }
    save_content('announcement', ['enabled' => $on, 'text' => $text, 'link' => $link, 'link_text' => in_line($_POST['link_text'] ?? '', 40)],
        $on ? 'Oznam je zapnutý a zobrazuje sa na všetkých stránkach.' : 'Oznam je vypnutý.');
    redirect(admin_url('announce'));
}

$old = $_SESSION['old'] ?? null; unset($_SESSION['old']);
$v = $old ?? ($ov ? ['enabled' => !empty($ov['enabled']), 'text' => $ov['text'] ?? '', 'link' => $ov['link'] ?? '', 'link_text' => $ov['link_text'] ?? ''] : ['enabled' => false, 'text' => '', 'link' => '', 'link_text' => '']);
admin_head('Oznam hore', 'announce', 'Tmavý pruh nad hlavičkou webu – napríklad na sviatky alebo zmenu rozvrhu.');
?>
<form method="post" class="track">
  <?= csrf_field() ?>
  <section class="card">
    <?php f_check('enabled', 'Zobraziť oznam na webe', !empty($v['enabled']), 'Návštevník ho môže zavrieť krížikom.'); ?>
    <div class="grid" style="margin-top:1rem">
      <?php f_text('text', 'Text oznamu', (string)$v['text'], ['max' => 200, 'class' => 'full', 'placeholder' => 'Napr. Počas sviatkov platí upravený rozvrh.']); ?>
      <?php f_text('link', 'Odkaz (nepovinné)', (string)$v['link'], ['max' => 300, 'placeholder' => 'rezervacia.php', 'hint' => 'Názov stránky (napr. rezervacia.php, cennik.php) alebo celá adresa.']); ?>
      <?php f_text('link_text', 'Text odkazu', (string)$v['link_text'], ['max' => 40, 'placeholder' => 'Pozrieť rozvrh']); ?>
    </div>
  </section>
  <?php f_actions(); ?>
</form>
<?php admin_foot();
