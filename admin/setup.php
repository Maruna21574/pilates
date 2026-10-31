<?php
declare(strict_types=1);
/**
 * Prvé nastavenie prístupu (alebo obnova zabudnutého hesla).
 * Funguje LEN keď neexistuje súbor data/admin.json.php. Vyžaduje inštalačný kód = prvých 12 znakov
 * hodnoty $voucher_secret z includes/data.php (dostupné len cez FTP). Pri zabudnutom hesle zmažte data/admin.json.php.
 */
define('ADMIN', true);
require __DIR__ . '/inc/lib.php';
admin_headers();
admin_session();
if (creds_load()) redirect('login.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $lock = login_locked();
    $code = trim((string)($_POST['code'] ?? ''));
    $pass = (string)($_POST['pass'] ?? '');
    if ($lock > 0) {
        $error = 'Príliš veľa pokusov. Skúste neskôr.';
    } elseif (!hash_equals(substr($voucher_secret, 0, 12), $code)) {
        record_fail(); usleep(700000);
        $error = 'Inštalačný kód nie je správny.';
    } elseif (mb_strlen($pass) < 10) {
        $error = 'Heslo musí mať aspoň 10 znakov.';
    } elseif ($pass !== (string)($_POST['pass2'] ?? '')) {
        $error = 'Heslá sa nezhodujú.';
    } else {
        if (!is_dir(content_dir())) @mkdir(content_dir(), 0755, true);
        $ok = creds_save(['user' => 'admin', 'hash' => password_hash($pass, PASSWORD_DEFAULT), 'must_change' => false, 'updated' => date('c')]);
        if ($ok) { flash('Prístup je nastavený. Prihláste sa menom „admin“.'); redirect('login.php'); }
        $error = 'Súbor data/admin.json.php sa nepodarilo vytvoriť. Skontrolujte oprávnenia priečinka data/.';
    }
}
?><!doctype html>
<html lang="sk">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Nastavenie prístupu | Admin</title>
  <link rel="stylesheet" href="../assets/css/fonts.css">
  <link rel="stylesheet" href="admin.css?v=1">
</head>
<body class="admin login-page">
  <main class="login-card">
    <img src="../assets/img/logo-white.png" alt="" width="170">
    <h1>Nastavenie prístupu</h1>
    <?php if ($error): ?><div class="flash err" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <label><span>Inštalačný kód</span><input name="code" autocomplete="off" required><small>Prvých 12 znakov hodnoty <code>$voucher_secret</code> v súbore includes/data.php.</small></label>
      <label><span>Nové heslo (min. 10 znakov)</span><input type="password" name="pass" autocomplete="new-password" minlength="10" required></label>
      <label><span>Heslo znova</span><input type="password" name="pass2" autocomplete="new-password" minlength="10" required></label>
      <button class="btn primary" type="submit">Nastaviť</button>
    </form>
  </main>
</body>
</html>
