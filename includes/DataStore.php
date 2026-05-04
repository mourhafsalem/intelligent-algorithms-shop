<?php
declare(strict_types=1);

/**
 * DataStore
 * ----------
 * طبقة قراءة البيانات من ملفات CSV (أو XLSX بعد تحويلها).
 * تستخدم نظام Cache مزدوج:
 *   1. APCu / static array داخل نفس الـ request
 *   2. ملفات serialized داخل /cache (تُجدد عند تغيير mtime للملف الأصلي)
 *
 * لا حاجة لقاعدة بيانات إطلاقاً.
 */
class DataStore
{
    private static array $memory = [];

    /** يقرأ CSV ويحوله لمصفوفة من المصفوفات الترابطية */
    public static function loadCsv(string $path): array
    {
        if (isset(self::$memory[$path])) {
            return self::$memory[$path];
        }

        if (!file_exists($path)) {
            throw new RuntimeException("ملف البيانات غير موجود: $path");
        }

        // محاولة التحميل من cache على القرص
        $cacheFile = CACHE_PATH . '/' . md5($path) . '.cache';
        if (file_exists($cacheFile) && filemtime($cacheFile) >= filemtime($path)) {
            $data = unserialize((string)file_get_contents($cacheFile));
            if (is_array($data)) {
                return self::$memory[$path] = $data;
            }
        }

        // قراءة CSV
        $rows = [];
        if (($h = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($h);
            if ($headers === false) {
                fclose($h);
                return [];
            }
            $headers = array_map('trim', $headers);
            while (($row = fgetcsv($h)) !== false) {
                if (count($row) !== count($headers)) continue;
                $rows[] = array_combine($headers, $row);
            }
            fclose($h);
        }

        // تحويل الأرقام
        foreach ($rows as &$r) {
            foreach ($r as $k => $v) {
                if (is_numeric($v)) {
                    $r[$k] = strpos((string)$v, '.') !== false ? (float)$v : (int)$v;
                }
            }
        }
        unset($r);

        // حفظ الكاش
        if (!is_dir(CACHE_PATH)) @mkdir(CACHE_PATH, 0777, true);
        @file_put_contents($cacheFile, serialize($rows));

        return self::$memory[$path] = $rows;
    }

    // ========== واجهات قراءة عالية المستوى ==========

    public static function users(): array     { return self::loadCsv(FILE_USERS); }
    public static function products(): array  { return self::loadCsv(FILE_PRODUCTS); }
    public static function ratings(): array   { return self::loadCsv(FILE_RATINGS); }
    public static function behavior(): array  { return self::loadCsv(FILE_BEHAVIOR); }

    /** فهرس المنتجات بـ product_id => row */
    public static function productsById(): array
    {
        static $idx = null;
        if ($idx !== null) return $idx;
        $idx = [];
        foreach (self::products() as $p) {
            $idx[(int)$p['product_id']] = $p;
        }
        return $idx;
    }

    public static function userById(int $id): ?array
    {
        foreach (self::users() as $u) {
            if ((int)$u['user_id'] === $id) return $u;
        }
        return null;
    }

    public static function findUserByName(string $name): ?array
    {
        // لا يوجد حقل username حقيقي، نستخدم user_id كرقم
        if (!is_numeric($name)) return null;
        return self::userById((int)$name);
    }

    public static function ratingsByUser(int $userId): array
    {
        $out = [];
        foreach (self::ratings() as $r) {
            if ((int)$r['user_id'] === $userId) $out[] = $r;
        }
        return $out;
    }

    public static function behaviorByUser(int $userId): array
    {
        $out = [];
        foreach (self::behavior() as $b) {
            if ((int)$b['user_id'] === $userId) $out[] = $b;
        }
        return $out;
    }

    /** قائمة الفئات الفريدة */
    public static function categories(): array
    {
        $cats = [];
        foreach (self::products() as $p) {
            $cats[$p['category']] = true;
        }
        return array_keys($cats);
    }

    /** فلترة منتجات بالبحث + الفئة + نطاق السعر */
    public static function filterProducts(?string $q, ?string $cat, ?int $min, ?int $max): array
    {
        $out = [];
        foreach (self::products() as $p) {
            if ($cat && $p['category'] !== $cat) continue;
            if ($min !== null && (int)$p['price'] < $min) continue;
            if ($max !== null && (int)$p['price'] > $max) continue;
            if ($q) {
                $hay = strtolower($p['category'] . ' ' . $p['product_id']);
                if (strpos($hay, strtolower($q)) === false) continue;
            }
            $out[] = $p;
        }
        return $out;
    }

    /** مسح الكاش يدوياً */
    public static function clearCache(): int
    {
        $n = 0;
        foreach (glob(CACHE_PATH . '/*.cache') ?: [] as $f) {
            if (@unlink($f)) $n++;
        }
        self::$memory = [];
        return $n;
    }
}
