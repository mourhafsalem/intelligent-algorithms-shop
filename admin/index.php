<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireAdmin();

$users = DataStore::users();
$products = DataStore::products();
$ratings = DataStore::ratings();
$behavior = DataStore::behavior();

// أكثر الفئات شعبية
$catCount = [];
foreach ($products as $p) $catCount[$p['category']] = ($catCount[$p['category']] ?? 0) + 1;
arsort($catCount);

header_html('لوحة الأدمن');
?>
<h2 class="section-title">⚙️ لوحة الأدمن</h2>
<div class="stats">
  <div class="stat"><div class="num"><?= count($users) ?></div><div class="label">مستخدم</div></div>
  <div class="stat"><div class="num"><?= count($products) ?></div><div class="label">منتج</div></div>
  <div class="stat"><div class="num"><?= count($ratings) ?></div><div class="label">تقييم</div></div>
  <div class="stat"><div class="num"><?= count($behavior) ?></div><div class="label">سجل سلوك</div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">
  <div class="card">
    <h3 style="margin-bottom:14px;">🧪 اختبار الخوارزمية الجينية</h3>
    <p style="color:var(--text-dim);font-size:14px;margin-bottom:16px;">جرّب الخوارزمية بمعاملات مختلفة وراقب التقارب.</p>
    <a href="<?= url('admin/ga_test.php') ?>" class="btn">افتح الاختبار</a>
  </div>
  <div class="card">
    <h3 style="margin-bottom:14px;">💾 إدارة الكاش</h3>
    <p style="color:var(--text-dim);font-size:14px;margin-bottom:16px;">حذف الكاش</p>
    <a href="<?= url('admin/clear_cache.php') ?>" class="btn btn-ghost">مسح الكاش</a>
  </div>
</div>

<h3 style="margin:30px 0 14px;">📊 توزيع المنتجات حسب الفئة</h3>
<table class="table">
  <thead><tr><th>الفئة</th><th>عدد المنتجات</th></tr></thead>
  <tbody>
  <?php foreach ($catCount as $c => $n): ?>
    <tr><td><?= e($c) ?></td><td><?= $n ?></td></tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php footer_html(); ?>
