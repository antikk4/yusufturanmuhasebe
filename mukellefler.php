<?php
require_once __DIR__ . '/config.php';
if (!kurulum_tamamlandi_mi()) { redirect('kurulum.php'); }
giris_zorunlu();

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = db()->prepare("SELECT * FROM mukellefler WHERE unvan LIKE ? OR vergi_no LIKE ? ORDER BY sira, unvan");
    $like = '%' . $q . '%';
    $stmt->execute([$like, $like]);
} else {
    $stmt = db()->query("SELECT * FROM mukellefler ORDER BY sira, unvan");
}
$mukellefler = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sayfa_basligi = 'Mükellefler';
$aktif_sayfa = 'mukellefler';
include __DIR__ . '/includes/header.php';
?>

<div class="topbar">
    <div>
        <h1>Mükellefler</h1>
        <div class="alt"><?= count($mukellefler) ?> kayıt</div>
    </div>
    <a href="mukellef_form.php" class="btn btn-gold">+ Yeni Mükellef</a>
</div>

<form method="get" style="margin-bottom:16px; max-width:340px;">
    <input type="text" name="q" placeholder="Unvan veya vergi no ara..." value="<?= h($q) ?>">
</form>

<?php if (empty($mukellefler)): ?>
    <div class="card bos-durum">
        <div class="ikon">🔍</div>
        <p><?= $q !== '' ? 'Aramanla eşleşen mükellef bulunamadı.' : 'Henüz mükellef eklenmemiş.' ?></p>
    </div>
<?php else: ?>
    <div class="card mukellef-listesi" style="padding:6px 0;">
        <?php foreach ($mukellefler as $i => $m): ?>
            <a class="mukellef-row" href="mukellef_detay.php?id=<?= $m['id'] ?>" style="<?= $i > 0 ? 'border-top:1px solid var(--paper-line)' : '' ?>">
                <span class="no"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                <span class="isim"><?= h($m['unvan']) ?><small><?= tip_etiketi($m['tip']) ?><?= $m['vergi_no'] ? ' · VN ' . h($m['vergi_no']) : '' ?></small></span>
                <span class="etiket etiket-tip"><?= tip_etiketi($m['tip']) ?></span>
                <span class="etiket <?= $m['durum'] === 'aktif' ? 'etiket-aktif' : 'etiket-pasif' ?>"><?= $m['durum'] === 'aktif' ? 'Aktif' : 'Pasif' ?></span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
