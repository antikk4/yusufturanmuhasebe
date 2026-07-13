<?php
require_once __DIR__ . '/config.php';

if (kurulum_tamamlandi_mi()) {
    redirect('login.php');
}

$hata = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sifre  = $_POST['sifre'] ?? '';
    $sifre2 = $_POST['sifre_tekrar'] ?? '';
    $isim   = trim($_POST['isim'] ?? '');

    if (strlen($sifre) < 4) {
        $hata = 'Şifre en az 4 karakter olmalı.';
    } elseif ($sifre !== $sifre2) {
        $hata = 'Şifreler eşleşmiyor.';
    } elseif ($isim === '') {
        $hata = 'Karşılama için bir isim gir.';
    } else {
        ayar_kaydet('sifre_hash', password_hash($sifre, PASSWORD_DEFAULT));
        ayar_kaydet('karsilama_isim', $isim);
        redirect('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>İlk Kurulum · <?= h(SITE_ADI) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="login-sayfa">
    <div class="login-kutu">
        <h1>İlk Kurulum</h1>
        <p>Siteyi kullanmaya başlamadan önce bir giriş şifresi ve karşılama ismi belirle.</p>
        <?php if ($hata): ?><div class="flash flash-hata"><?= h($hata) ?></div><?php endif; ?>
        <form method="post">
            <label>Karşılama ismi</label>
            <input type="text" name="isim" placeholder="Örn. Yusuf TURAN" value="<?= h($_POST['isim'] ?? '') ?>" required>

            <label>Giriş şifresi</label>
            <input type="password" name="sifre" required>

            <label>Şifre (tekrar)</label>
            <input type="password" name="sifre_tekrar" required>

            <button class="btn btn-gold" type="submit">Kurulumu Tamamla</button>
        </form>
    </div>
</div>
</body>
</html>
