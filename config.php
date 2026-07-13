<?php
// Bu değeri sunucuya yüklerken kendine özel, rastgele bir metinle değiştir.
// Giriş bilgileri şifreleme anahtarı olarak kullanılıyor.
define('SIR_ANAHTAR', '4288123');

define('SITE_ADI', 'Mükellef Takip');

date_default_timezone_set('Europe/Istanbul');
session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
