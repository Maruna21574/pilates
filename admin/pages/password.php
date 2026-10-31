<?php
defined('ADMIN') or exit;

$creds = creds_load();
$forced = !empty($creds['must_change']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $done = false;
    $cur = (string)($_POST['cur'] ?? ''); $new = (string)($_POST['new'] ?? ''); $new2 = (string)($_POST['new2'] ?? '');
    $user = in_line($_POST['user'] ?? '', 40);
    if (!password_verify($cur, (string)$creds['hash'])) {
        usleep(600000); flash('Súčasné heslo nie je správne.', 'err');
    } elseif (mb_strlen($new) < 10) {
        flash('Nové heslo musí mať aspoň 10 znakov.', 'err');
    } elseif ($new !== $new2) {
        flash('Nové heslá sa nezhodujú.', 'err');
    } elseif ($new === $cur) {
        flash('Nové heslo musí byť iné ako súčasné.', 'err');
    } elseif ($user !== '' && !preg_match('/^[A-Za-z0-9._-]{3,40}$/', $user)) {
        flash('Meno môže obsahovať len písmená bez diakritiky, čísla a znaky . _ - (3 až 40 znakov).', 'err');
    } else {
        $creds['hash'] = password_hash($new, PASSWORD_DEFAULT);
        $creds['must_change'] = false;
        if ($user !== '') $creds['user'] = $user;
        $creds['updated'] = date('c');
        if (creds_save($creds)) {
            session_regenerate_id(true);
            $done = true;
            flash('Heslo bolo zmenené.');
        } else flash('Heslo sa nepodarilo uložiť (skontrolujte oprávnenia priečinka data/).', 'err');
    }
    redirect(admin_url($done ? 'home' : 'password'));
}

admin_head('Heslo', 'password', $forced ? 'Pred ďalšou prácou si nastavte vlastné heslo.' : 'Zmena hesla a prihlasovacieho mena.');
?>
<form method="post" class="card" autocomplete="off">
  <?= csrf_field() ?>
  <?php if ($forced): ?><div class="flash warn">Používate heslo, ktoré bolo vygenerované pri inštalácii. Z bezpečnostných dôvodov ho prosím zmeňte.</div><?php endif; ?>
  <div class="grid">
    <div class="fld full"><label><span>Súčasné heslo</span><input type="password" name="cur" autocomplete="current-password" required></label></div>
    <div class="fld"><label><span>Nové heslo (min. 10 znakov)</span><input type="password" name="new" minlength="10" autocomplete="new-password" required></label><small>Použite dlhšiu vetu alebo kombináciu slov, čísel a znakov.</small></div>
    <div class="fld"><label><span>Nové heslo znova</span><input type="password" name="new2" minlength="10" autocomplete="new-password" required></label></div>
    <div class="fld"><label><span>Prihlasovacie meno (nepovinné)</span><input type="text" name="user" value="<?= e((string)($creds['user'] ?? 'admin')) ?>" maxlength="40" autocomplete="username"></label><small>Ak ho nezmeníte, ostane súčasné.</small></div>
  </div>
  <div class="actions"><button class="btn primary" type="submit">Zmeniť heslo</button></div>
  <p class="note">Ak heslo zabudnete: cez FTP zmažte súbor <code>data/admin.json.php</code> a otvorte <code>admin/setup.php</code>. Budete potrebovať inštalačný kód (prvých 12 znakov hodnoty <code>$voucher_secret</code> v súbore includes/data.php).</p>
</form>
<?php admin_foot();
