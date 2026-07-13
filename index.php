<?php
require_once __DIR__ . '/config.php';
if (!kurulum_tamamlandi_mi()) { redirect('kurulum.php'); }
giris_zorunlu();

$yil = (int)($_GET['yil'] ?? date('Y'));
$ay  = (int)($_GET['ay'] ?? date('n'));

$mukellefler = db()->query("SELECT * FROM mukellefler WHERE durum = 'aktif' ORDER BY sira, unvan")->fetchAll(PDO::FETCH_ASSOC);

$satirlar = [];
$toplamGorev = 0;
$toplamTamam = 0;
foreach ($mukellefler as $m) {
    $gorevler = ay_gorevlerini_getir((int)$m['id'], $yil, $ay);
    $tamam = count(array_filter($gorevler, fn($g) => (int)$g['tamamlandi'] === 1));
    $toplam = count($gorevler);
    $toplamGorev += $toplam;
    $toplamTamam += $tamam;
    $satirlar[] = ['m' => $m, 'tamam' => $tamam, 'toplam' => $toplam];
}

// Eksiği en çok olana göre sırala (dikkat gerektirenler üstte)
usort($satirlar, function ($a, $b) {
    $aEksik = $a['toplam'] - $a['tamam'];
    $bEksik = $b['toplam'] - $b['tamam'];
    return $bEksik <=> $aEksik;
});

$sayfa_basligi = 'Genel Bakış';
$aktif_sayfa = 'anasayfa';
include __DIR__ . '/includes/header.php';
?>

<div class="topbar">
    <div>
        <h1><?= AY_ISIMLERI[$ay] ?> <?= $yil ?> — Genel Bakış</h1>
        <div class="alt"><?= count($mukellefler) ?> aktif mükellef · bu ay <?= $toplamTamam ?>/<?= $toplamGorev ?> iş tamamlandı</div>
    </div>
    <a href="mukellef_form.php" class="btn btn-gold">+ Yeni Mükellef</a>
</div>

<div class="ay-secici">
    <?php
    $oncekiYil = $ay == 1 ? $yil - 1 : $yil;
    $oncekiAy  = $ay == 1 ? 12 : $ay - 1;
    $sonrakiYil = $ay == 12 ? $yil + 1 : $yil;
    $sonrakiAy  = $ay == 12 ? 1 : $ay + 1;
    ?>
    <div class="yil-nav">
        <a href="?yil=<?= $oncekiYil ?>&ay=<?= $oncekiAy ?>">‹</a>
        <span><?= $yil ?></span>
        <a href="?yil=<?= $sonrakiYil ?>&ay=<?= $sonrakiAy ?>">›</a>
    </div>
    <?php foreach (AY_ISIMLERI as $no => $adi): ?>
        <a class="ay-pill <?= $no === $ay ? 'aktif' : '' ?>" href="?yil=<?= $yil ?>&ay=<?= $no ?>"><?= $adi ?></a>
    <?php endforeach; ?>
</div>

<?php if (empty($mukellefler)): ?>
    <div class="card bos-durum">
        <div class="ikon">📋</div>
        <p>Henüz mükellef eklenmemiş.</p>
        <a href="mukellef_form.php" class="btn btn-gold">İlk mükellefi ekle</a>
    </div>
<?php else: ?>
    <div class="card mukellef-listesi" style="padding: 6px 0;">
        <?php foreach ($satirlar as $i => $s): $m = $s['m']; $eksik = $s['toplam'] - $s['tamam']; ?>
            <a class="mukellef-row" href="mukellef_detay.php?id=<?= $m['id'] ?>&yil=<?= $yil ?>&ay=<?= $ay ?>" style="<?= $i > 0 ? 'border-top:1px solid var(--paper-line)' : '' ?>">
                <span class="no"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                <span class="isim"><?= h($m['unvan']) ?><small><?= tip_etiketi($m['tip']) ?><?= $m['vergi_no'] ? ' · VN ' . h($m['vergi_no']) : '' ?></small></span>
                <span class="durum-ozet">
                    <span class="nokta <?= $eksik === 0 && $s['toplam'] > 0 ? 'nokta-tam' : 'nokta-eksik' ?>"></span>
                    <?= $s['tamam'] ?>/<?= $s['toplam'] ?> tamam
                </span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
