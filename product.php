<?php
require_once __DIR__ . '/config/config.php';

$id = (int)($_GET['id'] ?? 0);
$byId = DataStore::productsById();
$p = $byId[$id] ?? null;
if (!$p) { http_response_code(404); exit('المنتج غير موجود'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::check()) {
    cartAdd($id, max(1, (int)($_POST['qty'] ?? 1)));
    flash('تمت الإضافة للسلة');
    header('Location: ' . url('product.php?id=' . $id));
    exit;
}

// تقييمات هذا المنتج
$ratings = array_filter(DataStore::ratings(), fn($r) => (int)$r['product_id'] === $id);
$avg = $ratings ? array_sum(array_column($ratings, 'rating')) / count($ratings) : 0;

header_html('منتج #' . $id);
?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;background:var(--surface);padding:30px;border-radius:20px;border:1px solid var(--border);">
  <div style="background:var(--gradient);border-radius:14px;display:flex;align-items:center;justify-content:center;min-height:300px;font-size:60px;">📦</div>
  <div>
    <span class="badge"><?= e($p['category']) ?></span>
    <h1 style="margin:10px 0;">منتج #<?= $id ?></h1>
    <div class="price" style="font-size:32px;color:var(--accent);font-weight:800;">$<?= number_format((float)$p['price'], 2) ?></div>
    <p style="color:var(--text-dim);margin:14px 0;">⭐ <?= number_format($avg, 2) ?> من <?= count($ratings) ?> تقييم</p>
    <p style="margin-bottom:20px;">منتج عالي الجودة من فئة <?= e($p['category']) ?>. اختيار رائع للمستخدمين الباحثين عن الأفضل.</p>
    <?php if (Auth::check()): ?>
      <form method="post" style="display:flex;gap:10px;">
        <input type="number" name="qty" value="1" min="1" max="10" style="width:80px;padding:12px;background:var(--bg);color:var(--text);border:1px solid var(--border);border-radius:8px;">
        <button class="btn" type="submit">أضف للسلة 🛒</button>
      </form>
    <?php else: ?>
      <a href="<?= url('login.php') ?>" class="btn">سجّل دخولك للشراء</a>
    <?php endif; ?>
  </div>
</div>
<?php footer_html(); ?>
