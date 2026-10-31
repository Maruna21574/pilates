<?php
declare(strict_types=1);
define('ADMIN', true);
require __DIR__ . '/inc/lib.php';
admin_headers();
admin_session();
if (is_logged_in()) redirect('index.php');

$creds = creds_load();
$error = '';
$lock = login_locked();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $lock = login_locked();
    if ($lock > 0) {
        $error = 'Príliš veľa nesprávnych pokusov. Skúste to znova o ' . (int)ceil($lock / 60) . ' min.';
    } elseif (!$creds) {
        $error = 'Admin ešte nie je nastavený.';
    } else {
        $okUser = hash_equals((string)($creds['user'] ?? 'admin'), trim((string)($_POST['user'] ?? '')));
        $okPass = password_verify((string)($_POST['pass'] ?? ''), (string)$creds['hash']);
        if ($okUser && $okPass) {
            clear_fails();
            session_regenerate_id(true);
            $_SESSION = ['auth' => true, 'last' => time(), 'csrf' => bin2hex(random_bytes(24))];
            if (password_needs_rehash((string)$creds['hash'], PASSWORD_DEFAULT)) {
                $creds['hash'] = password_hash((string)$_POST['pass'], PASSWORD_DEFAULT);
                creds_save($creds);
            }
            redirect('index.php');
        }
        record_fail();
        usleep(700000);                       // spomalí automatické hádanie hesiel
        $left = LOGIN_MAX_FAILS - count(recent_fails());
        $error = 'Nesprávne meno alebo heslo.' . ($left > 0 && $left <= 2 ? " Zostáva pokusov: $left." : '');
        if ($left <= 0) $error = 'Príliš veľa nesprávnych pokusov. Skúste to znova o 15 min.';
    }
}
$flashes = flashes();
?><!doctype html>
<html lang="sk">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Prihlásenie | Admin – <?= e($site['name']) ?></title>
  <link rel="icon" href="../favicon.ico" sizes="48x48">
  <link rel="stylesheet" href="../assets/css/fonts.css">
  <link rel="stylesheet" href="admin.css?v=1">
</head>
<body class="admin login-page">
  <main class="login-card">
    <img src="../assets/img/logo-white.png" alt="<?= e($site['name']) ?>" width="170">
    <h1>Správa webu</h1>
    <?php foreach ($flashes as [$ft, $fm]): ?><div class="flash <?= e($ft) ?>" role="status"><?= e($fm) ?></div><?php endforeach; ?>
    <?php if (!$creds): ?>
      <p class="login-note">Prístup do správy ešte nie je nastavený. Otvorte <a href="setup.php">nastavenie</a>.</p>
    <?php else: ?>
      <?php if ($error): ?><div class="flash err" role="alert"><?= e($error) ?></div><?php endif; ?>
      <form method="post" autocomplete="on">
        <?= csrf_field() ?>
        <label><span>Meno</span><input name="user" value="<?= e((string)($_POST['user'] ?? '')) ?>" autocomplete="username" required autofocus></label>
        <label><span>Heslo</span><input type="password" name="pass" autocomplete="current-password" required></label>
        <button class="btn primary" type="submit"<?= $lock > 0 ? ' disabled' : '' ?>>Prihlásiť sa</button>
      </form>
    <?php endif; ?>
    <a class="login-back" href="../index.php">← Späť na web</a>
  </main>
</body>
</html>
