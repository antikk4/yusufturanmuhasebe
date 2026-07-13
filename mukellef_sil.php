<?php
require_once __DIR__ . '/config.php';
giris_zorunlu();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_dogrula();
    $id = (int)($_POST['id'] ?? 0);
    db()->prepare('DELETE FROM mukellefler WHERE id = ?')->execute([$id]);
    db()->prepare('DELETE FROM aylik_gorevler WHERE mukellef_id = ?')->execute([$id]);
}
redirect('mukellefler.php');
