<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireAdmin();
$n = DataStore::clearCache();
flash("تم حذف $n ملف من الكاش. القراءة القادمة ستكون من CSV مباشرة.");
header('Location: ' . url('admin/index.php'));
