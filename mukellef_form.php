<?php
require_once __DIR__ . '/config.php';
if (!kurulum_tamamlandi_mi()) { redirect('kurulum.php'); }
giris_zorunlu();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$m = [
    'unvan' => '', 'tip' => 'sahis', 'vergi_no' => '', 'vergi_dairesi' => '',
    'telefon' => '', 'eposta' => '', 'adres' => '', 'entegrator' => '',
    'giris_kullanici' => '', 'ucret' => '', 'durum' => 'aktif', 'genel_not' => '',
];
$sifre_duz = '';

if ($id) {
    $stmt = db()->prepare('SELECT * FROM mukellefler WHERE id = ?');
    $stmt->execute([$id]);
    $bulunan = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$bulunan) { redirect('mukellefler.php'); }
    $m = $bulunan;
    $sifre_duz = giris_bilgisi_coz($bulunan['giris_sifre_sifreli']);
}

$hata = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_dogrula();
    $unvan = trim($_POST['unvan'] ?? '');
    if ($unvan === '') {
        $hata = 'Unvan/isim alanı zorunlu.';
    } else {
        $veri = [
            'unvan' => $unvan,
            'tip' => $_POST['tip'] ?? 'sahis',
            'vergi_no' => trim($_POST['vergi_no'] ?? ''),
            'vergi_dairesi' => trim($_POST['vergi_dairesi'] ?? ''),
            'telefon' => trim($_POST['telefon'] ?? ''),
            'eposta' => trim($_POST['eposta'] ?? ''),
            'adres' => trim($_POST['adres'] ?? ''),
            'entegrator' => trim($_POST['entegrator'] ?? ''),
            'giris_kullanici' => trim($_POST['giris_kullanici'] ?? ''),
            'giris_sifre_sifreli' => giris_bilgisi_sifrele(trim($_POST['giris_sifre'] ?? '')),
            'ucret' => trim($_POST['ucret'] ?? ''),
            'durum' => $_POST['durum'] ?? 'aktif',
            'genel_not' => trim($_POST['genel_not'] ?? ''),
        ];

        if ($id) {
            $sql = 'UPDATE mukellefler SET ' . implode(', ', array_map(fn($k) => "$k = :$k", array_keys($veri))) . ' WHERE id = :id';
            $veri['id'] = $id;
            db()->prepare($sql)->execute($veri);
            redirect('mukellef_detay.php?id=' . $id);
        } else {
            $kolonlar = array_keys($veri);
            $sql = 'INSERT INTO mukellefler (' . implode(',', $kolonlar) . ') VALUES (:' . implode(',:', $kolonlar) . ')';
            db()->prepare($sql)->execute($veri);
            redirect('mukellef_detay.php?id=' . db()->lastInsertId());
        }
    }
}

$sayfa_basligi = $id ? 'Mükellef Düzenle' : 'Yeni Mükellef';
$aktif_sayfa = 'mukellefler';
include __DIR__ . '/includes/header.php';
?>

<div class="topbar">
    <h1><?= $id ? h($m['unvan']) . ' — Düzenle' : 'Yeni Mükellef' ?></h1>
</div>

<?php if ($hata): ?><div class="flash flash-hata"><?= h($hata) ?></div><?php endif; ?>

<form method="post" class="card" style="padding:26px 28px; max-width:760px;">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

    <div class="form-grid">
        <div class="full">
            <label>Unvan / İsim</label>
            <input type="text" name="unvan" value="<?= h($m['unvan']) ?>" required autofocus>
        </div>

        <div>
            <label>Mükellef Tipi</label>
            <select name="tip">
                <?php foreach (['sahis' => 'Şahıs', 'limited' => 'Limited Şirket', 'anonim' => 'Anonim Şirket', 'basit_usul' => 'Basit Usul'] as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $m['tip'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Durum</label>
            <select name="durum">
                <option value="aktif" <?= $m['durum'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="pasif" <?= $m['durum'] === 'pasif' ? 'selected' : '' ?>>Pasif</option>
            </select>
        </div>

        <div>
            <label>Vergi No</label>
            <input type="text" name="vergi_no" value="<?= h($m['vergi_no']) ?>">
        </div>
        <div>
            <label>Vergi Dairesi</label>
            <input type="text" name="vergi_dairesi" value="<?= h($m['vergi_dairesi']) ?>">
        </div>

        <div>
            <label>Telefon</label>
            <input type="text" name="telefon" value="<?= h($m['telefon']) ?>">
        </div>
        <div>
            <label>E-posta</label>
            <input type="email" name="eposta" value="<?= h($m['eposta']) ?>">
        </div>

        <div class="full">
            <label>Adres</label>
            <input type="text" name="adres" value="<?= h($m['adres']) ?>">
        </div>

        <div>
            <label>Aylık Ücret</label>
            <input type="text" name="ucret" placeholder="Örn. 3.000 TL" value="<?= h($m['ucret']) ?>">
        </div>
        <div>
            <label>E-Fatura Entegratörü</label>
            <input type="text" name="entegrator" placeholder="Örn. Foriba, Uyumsoft..." value="<?= h($m['entegrator']) ?>">
        </div>

        <div>
            <label>Entegratör Kullanıcı Adı</label>
            <input type="text" name="giris_kullanici" value="<?= h($m['giris_kullanici']) ?>">
        </div>
        <div>
            <label>Entegratör Şifresi</label>
            <input type="text" name="giris_sifre" value="<?= h($sifre_duz) ?>" placeholder="Şifreli saklanır">
        </div>

        <div class="full">
            <label>Genel Not</label>
            <textarea name="genel_not"><?= h($m['genel_not']) ?></textarea>
        </div>
    </div>

    <div style="margin-top:22px; display:flex; gap:10px;">
        <button type="submit" class="btn btn-gold"><?= $id ? 'Kaydet' : 'Mükellefi Ekle' ?></button>
        <a href="<?= $id ? 'mukellef_detay.php?id=' . $id : 'mukellefler.php' ?>" class="btn btn-ghost">Vazgeç</a>
    </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
