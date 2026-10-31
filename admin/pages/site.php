<?php
defined('ADMIN') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['reset'])) { reset_content('site'); redirect(admin_url('site')); }
    $email = in_line($_POST['email'] ?? '', 120);
    $phone = in_line($_POST['phone'] ?? '', 30);
    $errors = [];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'E-mail nie je platný.';
    if (!preg_match('/^[0-9+()\/\-\s]{6,30}$/', $phone)) $errors[] = 'Telefónne číslo má neplatný formát (povolené sú čísla, medzery a znaky + - ( ) /).';
    $fb = in_url($_POST['facebook'] ?? ''); $ig = in_url($_POST['instagram'] ?? '');
    if (in_line($_POST['facebook'] ?? '') !== '' && $fb === '') $errors[] = 'Odkaz na Facebook musí začínať http:// alebo https://.';
    if (in_line($_POST['instagram'] ?? '') !== '' && $ig === '') $errors[] = 'Odkaz na Instagram musí začínať http:// alebo https://.';
    if (in_line($_POST['name'] ?? '') === '') $errors[] = 'Názov štúdia nesmie byť prázdny.';
    if ($errors) { foreach ($errors as $m) flash($m, 'err'); $_SESSION['old'] = $_POST; redirect(admin_url('site')); }
    save_content('site', [
        'name' => in_line($_POST['name'], 80), 'tagline' => in_line($_POST['tagline'] ?? '', 80), 'city' => in_line($_POST['city'] ?? '', 60),
        'address' => in_line($_POST['address'] ?? '', 160), 'phone' => $phone, 'phone_person' => in_line($_POST['phone_person'] ?? '', 80),
        'email' => $email, 'instagram' => $ig ?: $site['instagram'], 'facebook' => $fb ?: $site['facebook'],
    ]);
    redirect(admin_url('site'));
}

$v = $_SESSION['old'] ?? $site; unset($_SESSION['old']);
admin_head('Kontakt a údaje', 'site', 'Zobrazia sa v pätičke, na stránke Kontakt, v e-mailoch a vo vyhľadávačoch.');
?>
<form method="post" class="track">
  <?= csrf_field() ?>
  <section class="card">
    <h2>Štúdio</h2>
    <div class="grid">
      <?php f_text('name', 'Názov štúdia', (string)$v['name'], ['required' => true, 'max' => 80]); ?>
      <?php f_text('tagline', 'Slogan', (string)$v['tagline'], ['max' => 80]); ?>
      <?php f_text('address', 'Adresa', (string)$v['address'], ['max' => 160, 'hint' => 'Podľa nej sa vytvorí odkaz na mapu.']); ?>
      <?php f_text('city', 'Mesto', (string)$v['city'], ['max' => 60]); ?>
    </div>
  </section>
  <section class="card">
    <h2>Kontakt</h2>
    <div class="grid">
      <?php f_text('phone', 'Telefón', (string)$v['phone'], ['type' => 'tel', 'required' => true, 'max' => 30, 'hint' => 'Napr. +421 911 174 567']); ?>
      <?php f_text('phone_person', 'Kontaktná osoba pri telefóne', (string)$v['phone_person'], ['max' => 80]); ?>
      <?php f_text('email', 'E-mail', (string)$v['email'], ['type' => 'email', 'required' => true, 'max' => 120, 'hint' => 'Sem chodia správy z formulárov, rezervácie aj objednávky poukážok.', 'class' => 'full']); ?>
    </div>
  </section>
  <section class="card">
    <h2>Sociálne siete</h2>
    <div class="grid">
      <?php f_text('instagram', 'Instagram (odkaz)', (string)$v['instagram'], ['type' => 'url', 'max' => 300]); ?>
      <?php f_text('facebook', 'Facebook (odkaz)', (string)$v['facebook'], ['type' => 'url', 'max' => 300]); ?>
    </div>
  </section>
  <?php f_actions('Vrátiť kontaktné údaje na pôvodné hodnoty?'); ?>
</form>
<?php admin_foot();
