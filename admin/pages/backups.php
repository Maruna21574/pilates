<?php
defined('ADMIN') or exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['restore'])) {
        $r = content_restore((string)$_POST['restore']);
        $r === true ? flash('Záloha bola obnovená. Predchádzajúci stav sa uložil ako nová záloha.') : flash((string)$r, 'err');
    } elseif (isset($_POST['reset_all'])) {
        if (!is_file(content_file())) { flash('Nie sú žiadne vlastné zmeny, ktoré by sa dali zrušiť.', 'warn'); }
        else {
            $r = content_save([]);                // záloha aktuálneho stavu + prázdny obsah = všetko pôvodné
            $r === true ? flash('Všetok obsah je vrátený na pôvodný. Predchádzajúci stav je v zálohách.') : flash((string)$r, 'err');
        }
    }
    redirect(admin_url('backups'));
}

if (isset($_GET['download'])) {
    $name = (string)$_GET['download'];
    $path = $name === 'current' ? content_file() : (preg_match('/^content-[0-9]{8}-[0-9]{6}-[0-9a-f]{4}\.json$/', $name) ? content_dir() . '/backups/' . $name : '');
    if ($path !== '' && is_file($path)) {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . ($name === 'current' ? 'obsah-' . date('Ymd-His') . '.json' : $name) . '"');
        readfile($path); exit;
    }
    flash('Súbor sa nenašiel.', 'err'); redirect(admin_url('backups'));
}

$list = content_backups();
admin_head('Zálohy', 'backups', 'Pred každým uložením sa automaticky vytvorí záloha. Uchováva sa posledných ' . CONTENT_KEEP_BACKUPS . '.');
?>
<section class="card">
  <h2>Aktuálny obsah</h2>
  <p class="lead">Stiahnite si kópiu všetkých vašich úprav (texty, ceny, rozvrh…) pre istotu.</p>
  <a class="btn" href="<?= e(admin_url('backups', ['download' => 'current'])) ?>">Stiahnuť aktuálny obsah</a>
</section>

<section class="card">
  <h2>Predchádzajúce verzie</h2>
  <?php if (!$list): ?>
    <p>Zatiaľ žiadne zálohy. Vytvoria sa pri prvom uložení zmeny.</p>
  <?php else: ?>
    <table class="tbl">
      <thead><tr><th>Dátum a čas</th><th>Veľkosť</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($list as $b): ?>
          <tr>
            <td><?= e(fmt_dt($b['time'])) ?></td>
            <td><?= (int)round($b['size'] / 1024, 0) ?> kB</td>
            <td style="text-align:right">
              <a class="btn sm ghost" href="<?= e(admin_url('backups', ['download' => $b['name']])) ?>">Stiahnuť</a>
              <form method="post" style="display:inline"><?= csrf_field() ?><button class="btn sm" type="submit" name="restore" value="<?= e($b['name']) ?>" data-confirm="Obnoviť stav z <?= e(fmt_dt($b['time'])) ?>? Aktuálny stav sa predtým zálohuje.">Obnoviť</button></form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<section class="card">
  <h2>Vrátiť všetko na pôvodné</h2>
  <p class="lead">Zruší všetky úpravy a web sa vráti do stavu pri odovzdaní. Aktuálny stav sa predtým uloží do záloh.</p>
  <form method="post"><?= csrf_field() ?><button class="btn danger" type="submit" name="reset_all" value="1" data-confirm="Naozaj vrátiť VŠETOK obsah na pôvodný? Predchádzajúci stav bude v zálohách.">Vrátiť všetko na pôvodné</button></form>
</section>
<?php admin_foot();
