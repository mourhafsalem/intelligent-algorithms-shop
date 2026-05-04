<?php
require_once __DIR__ . '/../config/config.php';
Auth::requireLogin();
$user = Auth::user();

$ratings = DataStore::ratingsByUser((int)$user['user_id']);
$behavior = DataStore::behaviorByUser((int)$user['user_id']);

header_html('حسابي');
?>
<h2 class="section-title">👤 ملفي الشخصي</h2>
<div class="stats">
  <div class="stat"><div class="num">#<?= (int)$user['user_id'] ?></div><div class="label">معرّفك</div></div>
  <div class="stat"><div class="num"><?= (int)$user['age'] ?></div><div class="label">العمر</div></div>
  <div class="stat"><div class="num"><?= e($user['country']) ?></div><div class="label">البلد</div></div>
  <div class="stat"><div class="num"><?= count($ratings) ?></div><div class="label">تقييماتك</div></div>
</div>

<h3 style="margin:24px 0 12px;">📊 تقييماتك السابقة</h3>
<table class="table">
  <thead><tr><th>المنتج</th><th>التقييم</th></tr></thead>
  <tbody>
  <?php foreach (array_slice($ratings, 0, 20) as $r): ?>
    <tr><td>منتج #<?= (int)$r['product_id'] ?></td><td>⭐ <?= (int)$r['rating'] ?>/5</td></tr>
  <?php endforeach; ?>
  </tbody>
</table>

<h3 style="margin:24px 0 12px;">👁️ سلوكك</h3>
<table class="table">
  <thead><tr><th>المنتج</th><th>مشاهدة</th><th>نقرة</th><th>شراء</th></tr></thead>
  <tbody>
  <?php foreach (array_slice($behavior, 0, 20) as $b): ?>
    <tr>
      <td>منتج #<?= (int)$b['product_id'] ?></td>
      <td><?= $b['viewed'] ? '✅' : '—' ?></td>
      <td><?= $b['clicked'] ? '✅' : '—' ?></td>
      <td><?= $b['purchased'] ? '✅' : '—' ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php footer_html(); ?>
