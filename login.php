<?php
require_once __DIR__ . '/config.php';

if (!kurulum_tamamlandi_mi()) {
    redirect('kurulum.php');
}
if (giris_yapilmis_mi()) {
    redirect('index.php');
}

$hata = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sifre = $_POST['sifre'] ?? '';
    $hash  = ayar_getir('sifre_hash');

    if ($sifre !== '' && password_verify($sifre, $hash)) {
        $_SESSION['giris_ok'] = true;
        redirect('index.php');
    } else {
        $hata = 'Şifre hatalı.';
    }
}

$isim = ayar_getir('karsilama_isim', '');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Giriş · <?= h(SITE_ADI) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="login-sayfa">
    <div class="login-kutu">
        <h1>Hoş geldin<?= $isim ? ', ' . h($isim) : '' ?></h1>
        <p><?= h(SITE_ADI) ?> paneline giriş yap.</p>
        <?php if ($hata): ?><div class="flash flash-hata"><?= h($hata) ?></div><?php endif; ?>
        <form method="post">
            <label>Şifre</label>
            <input type="password" name="sifre" autofocus required>
            <button class="btn btn-gold" type="submit">Giriş Yap</button>
        </form>
    </div>
</div>
</body>
</html>
