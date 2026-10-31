<?php
declare(strict_types=1);
define('ADMIN', true);
require __DIR__ . '/inc/lib.php';
admin_headers();
admin_session();
require_login();

$pages = ['home', 'site', 'announce', 'texts', 'classes', 'individual', 'prices', 'schedule', 'team', 'reviews', 'faq', 'slider', 'gallery', 'videos', 'media', 'settings', 'password', 'backups'];
$p = (string)($_GET['p'] ?? 'home');
if (!in_array($p, $pages, true)) $p = 'home';

// po prvom prihlásení s vygenerovaným heslom treba heslo zmeniť
$creds = creds_load();
if (!empty($creds['must_change']) && $p !== 'password') {
    flash('Najprv si prosím nastavte vlastné heslo.', 'warn');
    redirect(admin_url('password'));
}
require __DIR__ . '/pages/' . $p . '.php';
