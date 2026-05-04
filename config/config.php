<?php
// =====================================================
// إعدادات المشروع - بدون قاعدة بيانات
// البيانات تُقرأ مباشرة من ملفات CSV / XLSX
// =====================================================

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// عرض الأخطاء (ضعها false في الإنتاج)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// المسارات
define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . '/data');
define('CACHE_PATH', BASE_PATH . '/cache');

// أسماء الملفات (يمكن تغييرها لـ .xlsx بعد التحويل)
define('FILE_USERS',     DATA_PATH . '/users.csv');
define('FILE_PRODUCTS',  DATA_PATH . '/products.csv');
define('FILE_RATINGS',   DATA_PATH . '/ratings.csv');
define('FILE_BEHAVIOR',  DATA_PATH . '/behavior.csv');

// إعدادات الكاش (ثواني) - يحدد كل كم ثانية يعاد تحميل CSV
define('CACHE_TTL', 3600); // ساعة واحدة

// إعدادات الخوارزمية الجينية الافتراضية
define('GA_POPULATION',  60);
define('GA_GENERATIONS', 40);
define('GA_MUTATION',    0.10);
define('GA_K',           8); // عدد التوصيات

define('SITE_NAME', 'INTELLIGENT ALGORITHMS');

require_once __DIR__ . '/../includes/DataStore.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
