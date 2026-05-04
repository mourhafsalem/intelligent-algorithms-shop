<?php
require_once __DIR__ . '/config/config.php';
header_html('الرئيسية');

$products = DataStore::products();
$users = DataStore::users();
$ratings = DataStore::ratings();

// أفضل المنتجات (الأعلى تقييماً)
$avgRatings = [];
$counts = [];
foreach ($ratings as $r) {
    $pid = (int)$r['product_id'];
    $avgRatings[$pid] = ($avgRatings[$pid] ?? 0) + (float)$r['rating'];
    $counts[$pid] = ($counts[$pid] ?? 0) + 1;
}
foreach ($avgRatings as $pid => $s) $avgRatings[$pid] = $s / $counts[$pid];
arsort($avgRatings);
$topIds = array_slice(array_keys($avgRatings), 0, 8);
$byId = DataStore::productsById();
?>
<section class="hero">
  <h1>BIA 601</h1>
  <p>mourhaf_196606 ... dania_175474 ... marita_212711 ... yusra_157068 ... banan_154555 ... reem_151560</p>
  <a href="<?= url('products.php') ?>" class="btn">تصفح المنتجات</a>
  <?php if (!Auth::check()): ?>
    <a href="<?= url('login.php') ?>" class="btn btn-ghost" style="margin-right:10px;">سجّل دخولك للتوصيات</a>
  <?php else: ?>
    <a href="<?= url('recommendations.php') ?>" class="btn btn-ghost" style="margin-right:10px;">توصياتي ✨</a>
  <?php endif; ?>
</section>

<div class="stats">
  <div class="stat"><div class="num"><?= count($products) ?></div><div class="label">منتج</div></div>
  <div class="stat"><div class="num"><?= count($users) ?></div><div class="label">مستخدم</div></div>
  <div class="stat"><div class="num"><?= count($ratings) ?></div><div class="label">تقييم</div></div>
  <div class="stat"><div class="num"><?= count(DataStore::categories()) ?></div><div class="label">فئة</div></div>
</div>

<h2 class="section-title">🔥 الأعلى تقييماً</h2>
<div class="grid">
  <?php foreach ($topIds as $pid): $p = $byId[$pid] ?? null; if (!$p) continue; ?>
    <div class="card">
      <span class="badge"><?= e($p['category']) ?></span>
      <h3>منتج #<?= (int)$p['product_id'] ?></h3>
      <div class="price">$<?= number_format((float)$p['price'], 2) ?></div>
      <p style="color:var(--text-dim);font-size:13px;">⭐ <?= number_format($avgRatings[$pid], 2) ?> / 5</p>
    </div>
  <?php endforeach; ?>
</div>
<?php footer_html(); ?>
