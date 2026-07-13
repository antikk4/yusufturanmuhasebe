<?php
require_once __DIR__ . '/config.php';
if (!kurulum_tamamlandi_mi()) { redirect('kurulum.php'); }
giris_zorunlu();

$basarili = '';
$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_dogrula();
    $islem = $_POST['islem'] ?? '';

    if ($islem === 'genel') {
        ayar_kaydet('karsilama_isim', trim($_POST['isim'] ?? ''));
        $basarili = 'Kaydedildi.';
    }

    if ($islem === 'sifre') {
        $mevcut = $_POST['mevcut_sifre'] ?? '';
        $yeni = $_POST['yeni_sifre'] ?? '';
        $yeni2 = $_POST['yeni_sifre_tekrar'] ?? '';
        if (!password_verify($mevcut, ayar_getir('sifre_hash'))) {
            $hata = 'Mevcut şifre yanlış.';
        } elseif (strlen($yeni) < 4) {
            $hata = 'Yeni şifre en az 4 karakter olmalı.';
        } elseif ($yeni !== $yeni2) {
            $hata = 'Yeni şifreler eşleşmiyor.';
        } else {
            ayar_kaydet('sifre_hash', password_hash($yeni, PASSWORD_DEFAULT));
            $basarili = 'Şifre güncellendi.';
        }
    }

    if ($islem === 'sablon_ekle') {
        $ad = trim($_POST['yeni_sablon'] ?? '');
        if ($ad !== '') {
            $sira = (int) db()->query('SELECT COALESCE(MAX(sira), -1) + 1 FROM gorev_sablonlari')->fetchColumn();
            db()->prepare('INSERT INTO gorev_sablonlari (ad, sira) VALUES (?, ?)')->execute([$ad, $sira]);
            $basarili = 'Şablon eklendi.';
        }
    }

    if ($islem === 'sablon_sil') {
        db()->prepare('DELETE FROM gorev_sablonlari WHERE id = ?')->execute([(int)($_POST['sablon_id'] ?? 0)]);
        $basarili = 'Şablon silindi.';
    }
}

$isim = ayar_getir('karsilama_isim', '');
$sablonlar = db()->query('SELECT * FROM gorev_sablonlari ORDER BY sira, id')->fetchAll(PDO::FETCH_ASSOC);

$sayfa_basligi = 'Ayarlar';
$aktif_sayfa = 'ayarlar';
include __DIR__ . '/includes/header.php';
?>

<div class="topbar"><h1>Ayarlar</h1></div>

<?php if ($basarili): ?><div class="flash flash-basari"><?= h($basarili) ?></div><?php endif; ?>
<?php if ($hata): ?><div class="flash flash-hata"><?= h($hata) ?></div><?php endif; ?>

<h3>Genel</h3>
<form method="post" class="card" style="padding:22px 26px; max-width:480px; margin-bottom:26px;">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="islem" value="genel">
    <label>Karşılama ismi</label>
    <input type="text" name="isim" value="<?= h($isim) ?>">
    <div style="margin-top:16px;"><button class="btn btn-gold" type="submit">Kaydet</button></div>
</form>

<h3>Şifre Değiştir</h3>
<form method="post" class="card" style="padding:22px 26px; max-width:480px; margin-bottom:26px;">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="islem" value="sifre">
    <label>Mevcut Şifre</label>
    <input type="password" name="mevcut_sifre" required>
    <label>Yeni Şifre</label>
    <input type="password" name="yeni_sifre" required>
    <label>Yeni Şifre (tekrar)</label>
    <input type="password" name="yeni_sifre_tekrar" required>
    <div style="margin-top:16px;"><button class="btn btn-gold" type="submit">Şifreyi Güncelle</button></div>
</form>

<h3>Aylık Görev Şablonları</h3>
<p class="uyari-metin" style="margin-bottom:14px;">Yeni bir ayı ilk açtığında her mükellef için otomatik eklenecek varsayılan görevler. Mevcut aylardaki görevleri etkilemez, sadece yeni açılan aylar için geçerlidir.</p>
<div class="card" style="padding:6px 18px; max-width:480px; margin-bottom:16px;">
    <?php foreach ($sablonlar as $i => $s): ?>
        <div class="gorev-satir">
            <div class="gorev-adi"><?= h($s['ad']) ?></div>
            <form method="post" onsubmit="return confirm('Bu şablon silinsin mi?')">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="islem" value="sablon_sil">
                <input type="hidden" name="sablon_id" value="<?= $s['id'] ?>">
                <button type="submit" class="goster-btn" style="color:var(--red); background:none; border:none; cursor:pointer;">SİL</button>
            </form>
        </div>
    <?php endforeach; ?>
    <?php if (empty($sablonlar)): ?><div class="bos-durum" style="padding:20px;">Şablon yok.</div><?php endif; ?>
</div>
<form method="post" style="display:flex; gap:8px; max-width:480px;">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="islem" value="sablon_ekle">
    <input type="text" name="yeni_sablon" placeholder="Yeni şablon görev adı...">
    <button type="submit" class="btn btn-sm btn-ink" style="flex-shrink:0;">Ekle</button>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
