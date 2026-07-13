<?php
require_once __DIR__ . '/config.php';
if (!kurulum_tamamlandi_mi()) { redirect('kurulum.php'); }
giris_zorunlu();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM mukellefler WHERE id = ?');
$stmt->execute([$id]);
$m = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$m) { redirect('mukellefler.php'); }

$sekme = $_GET['sekme'] ?? 'takip';
$yil = (int)($_GET['yil'] ?? date('Y'));
$ay  = (int)($_GET['ay'] ?? date('n'));

$gorevler = ay_gorevlerini_getir($id, $yil, $ay);

$notStmt = db()->prepare('SELECT not_metni FROM aylik_notlar WHERE mukellef_id = ? AND yil = ? AND ay = ?');
$notStmt->execute([$id, $yil, $ay]);
$ayNotu = $notStmt->fetchColumn() ?: '';

$sifre_duz = giris_bilgisi_coz($m['giris_sifre_sifreli']);

$sayfa_basligi = $m['unvan'];
$aktif_sayfa = 'mukellefler';
include __DIR__ . '/includes/header.php';
?>

<div class="topbar">
    <div>
        <h1><?= h($m['unvan']) ?></h1>
        <div class="alt"><?= tip_etiketi($m['tip']) ?><?= $m['vergi_no'] ? ' · VN ' . h($m['vergi_no']) : '' ?> · <span class="etiket <?= $m['durum'] === 'aktif' ? 'etiket-aktif' : 'etiket-pasif' ?>"><?= $m['durum'] === 'aktif' ? 'Aktif' : 'Pasif' ?></span></div>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="mukellef_form.php?id=<?= $id ?>" class="btn">Düzenle</a>
        <form method="post" action="mukellef_sil.php" onsubmit="return confirm('&quot;<?= h($m['unvan']) ?>&quot; silinsin mi? Bu işlem geri alınamaz.');">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="id" value="<?= $id ?>">
            <button type="submit" class="btn btn-danger">Sil</button>
        </form>
    </div>
</div>

<div class="sekmeler">
    <a class="sekme <?= $sekme === 'takip' ? 'aktif' : '' ?>" href="?id=<?= $id ?>&sekme=takip&yil=<?= $yil ?>&ay=<?= $ay ?>">Aylık Takip</a>
    <a class="sekme <?= $sekme === 'bilgiler' ? 'aktif' : '' ?>" href="?id=<?= $id ?>&sekme=bilgiler">Temel Bilgiler</a>
    <a class="sekme <?= $sekme === 'giris' ? 'aktif' : '' ?>" href="?id=<?= $id ?>&sekme=giris">Giriş Bilgileri</a>
</div>

<div class="sekme-icerik">
<?php if ($sekme === 'bilgiler'): ?>

    <div class="card" style="padding:22px 26px; max-width:640px;">
        <div class="bilgi-satiri"><span class="etk">Unvan</span><span><?= h($m['unvan']) ?></span></div>
        <div class="bilgi-satiri"><span class="etk">Tip</span><span><?= tip_etiketi($m['tip']) ?></span></div>
        <div class="bilgi-satiri"><span class="etk">Vergi No</span><span><?= h($m['vergi_no']) ?: '—' ?></span></div>
        <div class="bilgi-satiri"><span class="etk">Vergi Dairesi</span><span><?= h($m['vergi_dairesi']) ?: '—' ?></span></div>
        <div class="bilgi-satiri"><span class="etk">Telefon</span><span><?= h($m['telefon']) ?: '—' ?></span></div>
        <div class="bilgi-satiri"><span class="etk">E-posta</span><span><?= h($m['eposta']) ?: '—' ?></span></div>
        <div class="bilgi-satiri"><span class="etk">Adres</span><span><?= h($m['adres']) ?: '—' ?></span></div>
        <div class="bilgi-satiri"><span class="etk">Aylık Ücret</span><span><?= h($m['ucret']) ?: '—' ?></span></div>
        <div class="bilgi-satiri"><span class="etk">Genel Not</span><span><?= nl2br(h($m['genel_not'])) ?: '—' ?></span></div>
    </div>

<?php elseif ($sekme === 'giris'): ?>

    <div class="card" style="padding:22px 26px; max-width:640px;">
        <div class="bilgi-satiri"><span class="etk">Entegratör</span><span><?= h($m['entegrator']) ?: '—' ?></span></div>
        <div class="bilgi-satiri"><span class="etk">Kullanıcı Adı</span><span><?= h($m['giris_kullanici']) ?: '—' ?></span></div>
        <div class="bilgi-satiri">
            <span class="etk">Şifre</span>
            <span class="gizli-alan">
                <span id="sifre-metni" data-sifre="<?= h($sifre_duz) ?>">••••••••</span>
                <span class="goster-btn" onclick="sifreGosterGizle()">GÖSTER</span>
            </span>
        </div>
    </div>
    <p class="uyari-metin">Şifreler veritabanında şifrelenmiş olarak saklanır, sadece bu ekranda çözülerek gösterilir.</p>
    <script>
    function sifreGosterGizle() {
        const el = document.getElementById('sifre-metni');
        const btn = event.target;
        if (btn.textContent === 'GÖSTER') {
            el.textContent = el.dataset.sifre || '(girilmemiş)';
            btn.textContent = 'GİZLE';
        } else {
            el.textContent = '••••••••';
            btn.textContent = 'GÖSTER';
        }
    }
    </script>

<?php else: /* takip */ ?>

    <div class="ay-secici" style="margin-top:0;">
        <?php
        $oncekiYil = $ay == 1 ? $yil - 1 : $yil;
        $oncekiAy  = $ay == 1 ? 12 : $ay - 1;
        $sonrakiYil = $ay == 12 ? $yil + 1 : $yil;
        $sonrakiAy  = $ay == 12 ? 1 : $ay + 1;
        ?>
        <div class="yil-nav">
            <a href="?id=<?= $id ?>&yil=<?= $oncekiYil ?>&ay=<?= $oncekiAy ?>">‹</a>
            <span><?= $yil ?></span>
            <a href="?id=<?= $id ?>&yil=<?= $sonrakiYil ?>&ay=<?= $sonrakiAy ?>">›</a>
        </div>
        <?php foreach (AY_ISIMLERI as $no => $adi): ?>
            <a class="ay-pill <?= $no === $ay ? 'aktif' : '' ?>" href="?id=<?= $id ?>&yil=<?= $yil ?>&ay=<?= $no ?>"><?= $adi ?></a>
        <?php endforeach; ?>
    </div>

    <div class="card gorev-tablosu" style="padding:8px 18px; margin-top:16px;" id="gorev-liste" data-mukellef="<?= $id ?>" data-yil="<?= $yil ?>" data-ay="<?= $ay ?>">
        <?php foreach ($gorevler as $g): ?>
            <div class="gorev-satir" data-id="<?= $g['id'] ?>">
                <div class="check <?= $g['tamamlandi'] ? 'tamam' : '' ?>" onclick="gorevToggle(this)">✓</div>
                <div class="gorev-adi <?= $g['tamamlandi'] ? 'tamam-metin' : '' ?>"><?= h($g['gorev_adi']) ?></div>
                <input class="gorev-not-input" placeholder="not ekle..." value="<?= h($g['not_metni']) ?>" onchange="gorevNotKaydet(this)">
                <div class="gorev-tarih"><?= $g['tamamlanma_tarihi'] ? h(substr($g['tamamlanma_tarihi'], 0, 10)) : '' ?></div>
                <span class="goster-btn" style="color:var(--red); flex-shrink:0;" onclick="gorevSil(this)">SİL</span>
            </div>
        <?php endforeach; ?>
        <?php if (empty($gorevler)): ?>
            <div class="bos-durum" style="padding:30px;">Bu ay için görev yok. Aşağıdan ekleyebilirsin.</div>
        <?php endif; ?>
    </div>

    <form style="display:flex; gap:8px; margin-top:14px; max-width:460px;" onsubmit="return gorevEkle(event)">
        <input type="text" id="yeni-gorev-adi" placeholder="Yeni görev / iş ekle...">
        <button type="submit" class="btn btn-sm btn-ink" style="flex-shrink:0;">Ekle</button>
    </form>

    <label style="margin-top:26px;">Bu Aya Ait Genel Not</label>
    <textarea id="ay-notu" style="max-width:640px;" onchange="ayNotuKaydet(this.value)" placeholder="Bu ayla ilgili serbest not..."><?= h($ayNotu) ?></textarea>

    <script>
    const CSRF = "<?= csrf_token() ?>";
    const MUKELLEF_ID = <?= $id ?>;
    const YIL = <?= $yil ?>;
    const AY = <?= $ay ?>;

    async function api(url, data) {
        const body = new URLSearchParams({...data, csrf: CSRF});
        const res = await fetch(url, { method: 'POST', body });
        return res.json();
    }

    function gorevToggle(el) {
        const satir = el.closest('.gorev-satir');
        const id = satir.dataset.id;
        const yeniDurum = !el.classList.contains('tamam');
        el.classList.toggle('tamam');
        satir.querySelector('.gorev-adi').classList.toggle('tamam-metin');
        api('api/gorev_toggle.php', { id, tamamlandi: yeniDurum ? 1 : 0 }).then(r => {
            if (r.tarih !== undefined) {
                satir.querySelector('.gorev-tarih').textContent = r.tarih || '';
            }
        });
    }

    function gorevNotKaydet(el) {
        const satir = el.closest('.gorev-satir');
        api('api/gorev_not.php', { id: satir.dataset.id, not_metni: el.value });
    }

    function gorevSil(el) {
        if (!confirm('Bu görev silinsin mi?')) return;
        const satir = el.closest('.gorev-satir');
        api('api/gorev_sil.php', { id: satir.dataset.id }).then(() => satir.remove());
    }

    function gorevEkle(e) {
        e.preventDefault();
        const input = document.getElementById('yeni-gorev-adi');
        const ad = input.value.trim();
        if (!ad) return false;
        api('api/gorev_ekle.php', { mukellef_id: MUKELLEF_ID, yil: YIL, ay: AY, gorev_adi: ad }).then(r => {
            if (r.id) {
                const liste = document.getElementById('gorev-liste');
                const bos = liste.querySelector('.bos-durum');
                if (bos) bos.remove();
                const div = document.createElement('div');
                div.className = 'gorev-satir';
                div.dataset.id = r.id;
                div.innerHTML = `
                    <div class="check" onclick="gorevToggle(this)">✓</div>
                    <div class="gorev-adi">${ad.replace(/</g,'&lt;')}</div>
                    <input class="gorev-not-input" placeholder="not ekle..." onchange="gorevNotKaydet(this)">
                    <div class="gorev-tarih"></div>
                    <span class="goster-btn" style="color:var(--red); flex-shrink:0;" onclick="gorevSil(this)">SİL</span>
                `;
                liste.appendChild(div);
                input.value = '';
            }
        });
        return false;
    }

    function ayNotuKaydet(deger) {
        api('api/ay_notu.php', { mukellef_id: MUKELLEF_ID, yil: YIL, ay: AY, not_metni: deger });
    }
    </script>

<?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
