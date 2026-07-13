# Mükellef Takip — Kurulum Notları

Kişisel kullanım için hazırlanmış, şifreyle korunan basit bir mükellef takip paneli.
PHP + SQLite ile çalışır, veritabanı tek bir dosyadır (`data/takip.sqlite`), ekstra bir
MySQL kurulumu gerekmez. Natro gibi standart paylaşımlı hosting'lerde çoğunlukla
PHP + SQLite3 zaten hazır gelir.

## 1) Yüklemeden önce yapılacaklar

`config.php` dosyasını aç ve şu satırı **kendine özel rastgele bir metinle** değiştir:

```php
define('SIR_ANAHTAR', 'bunu-degistir-cok-gizli-bir-metin-2026');
```

Bu anahtar, entegratör şifrelerinin veritabanında şifrelenmesinde kullanılıyor.
Siteyi yayına almadan önce mutlaka değiştir, sonra bir daha değiştirme (değiştirirsen
o ana kadar kaydedilen şifreler çözülemez hale gelir).

## 2) Hosting'e yükleme

- Bu klasördeki her şeyi (dosyalar dahil) FTP veya cPanel Dosya Yöneticisi ile
  hosting'inde istediğin bir klasöre yükle. Ana siteyle (turansmmm.com.tr) karışmaması
  için ayrı bir alt klasör ya da alt alan adı (örn. `takip.turansmmm.com.tr` veya
  `turansmmm.com.tr/takip`) kullanman önerilir.
- `data/` klasörünün yazılabilir olması lazım (çoğu hosting'de varsayılan izinler
  yeterlidir; sorun olursa hosting panelinden `data` klasörüne 755 veya 775 izni ver).
- Bu bir statik site değil, **PHP çalıştıran bir sunucu** gerektirir — GitHub Pages'te
  çalışmaz, Natro'daki hosting paketinde çalışır.

## 3) İlk kurulum

Siteye ilk girişte otomatik olarak `kurulum.php` sayfasına yönlendirilirsin.
Burada:
- Karşılama ismini gir (örn. "Yusuf TURAN")
- Giriş şifreni belirle

Bundan sonra her girişte sadece bu şifre sorulur.

## 4) Kullanım

- **Genel Bakış**: seçili ayda hangi mükellefte kaç iş tamamlanmış, özet halde.
- **Mükellefler**: tüm mükellef listesi, arama, yeni ekleme.
- Bir mükellefe tıklayınca üç sekme çıkar:
  - **Aylık Takip**: ay/yıl seçip o aya ait iş listesini işaretleyebilir, her satıra
    not düşebilir, istediğin kadar özel görev ekleyip silebilirsin.
  - **Temel Bilgiler**: unvan, vergi no, vergi dairesi, telefon, adres, ücret vb.
  - **Giriş Bilgileri**: e-fatura entegratörü, kullanıcı adı ve şifre (şifre
    veritabanında şifreli tutulur, ekranda "GÖSTER" ile açılır).
- **Ayarlar**: karşılama ismini ve şifreni değiştirebilir, her yeni ay açıldığında
  otomatik eklenecek varsayılan görev şablonlarını (KDV, Muhtasar, Geçici Vergi,
  SGK gibi) düzenleyebilirsin.

## 5) Özelleştirme

Kod sade tutuldu, dosya isimleri Türkçe ve okunaklı — beğenmediğin her şeyi elle
değiştirebilirsin:

- Renkler / yazı tipleri → `assets/style.css` (dosyanın en üstünde `:root` içinde
  renk değişkenleri var).
- Sayfa metinleri → ilgili `.php` dosyasının içinde düz Türkçe metin olarak duruyor.
- Varsayılan görev şablonları → Ayarlar sayfasından, kod değiştirmeden eklenip
  silinebiliyor.
- Mükellef formundaki alanlar → `mukellef_form.php` ve `includes/db.php`'deki
  `mukellefler` tablosuna alan eklemek yeterli.

## 6) Güvenlik notları

- Site tek şifreyle korunur, oturum PHP session ile tutulur.
- `data/` ve `includes/` klasörlerine tarayıcıdan doğrudan erişim `.htaccess` ile
  engellendi (Apache/cPanel hosting'lerde otomatik çalışır).
- Entegratör şifreleri düz metin değil, AES-256 ile şifrelenmiş olarak saklanır.
- Yine de bu bir "kurumsal güvenlik" seviyesinde değil, kişisel kullanım için
  yeterli bir koruma seviyesidir — hassas gördüğün başka noktalar olursa haber ver,
  birlikte sıkılaştırırız.
