<?php
require_once __DIR__ . '/config/config.php';

$q = $_GET['q'] ?? null;
$cat = $_GET['cat'] ?? null;
$min = isset($_GET['min']) && $_GET['min'] !== '' ? (int)$_GET['min'] : null;
$max = isset($_GET['max']) && $_GET['max'] !== '' ? (int)$_GET['max'] : null;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 24;

$results = DataStore::filterProducts($q, $cat, $min, $max);
$total = count($results);
$results = array_slice($results, ($page - 1) * $perPage, $perPage);
$cats = DataStore::categories();

header_html('المنتجات');
?>
<h2 class="section-title">🛍️ المنتجات (<?= $total ?>)</h2>

<form class="filters" method="get">
  <input type="text" name="q" value="<?= e($q) ?>" placeholder="🔍 ابحث...">
  <select name="cat">
    <option value="">كل الفئات</option>
    <?php foreach ($cats as $c): ?>
      <option value="<?= e($c) ?>" <?= $cat === $c ? 'selected' : '' ?>><?= e($c) ?></option>
    <?php endforeach; ?>
  </select>
  <input type="number" name="min" value="<?= $min !== null ? $min : '' ?>" placeholder="السعر من">
  <input type="number" name="max" value="<?= $max !== null ? $max : '' ?>" placeholder="إلى">
  <button class="btn" type="submit">تصفية</button>
</form>

<div class="grid">
  <?php foreach ($results as $p): ?>
    <div class="card">
      <span class="badge"><?= e($p['category']) ?></span>
      <h3>منتج #<?= (int)$p['product_id'] ?></h3>
      <div class="price">$<?= number_format((float)$p['price'], 2) ?></div>
      <a href="<?= url('product.php?id=' . (int)$p['product_id']) ?>" class="btn" style="width:100%;text-align:center;">عرض</a>
    </div>
  <?php endforeach; ?>
</div>

<?php
$pages = (int)ceil($total / $perPage);
if ($pages > 1):
?>
  <div style="text-align:center;margin-top:30px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
    <?php for ($i = max(1, $page - 3); $i <= min($pages, $page + 3); $i++):
      $qs = http_build_query(array_merge($_GET, ['page' => $i])); ?>
      <a href="?<?= $qs ?>" class="btn <?= $i === $page ? '' : 'btn-ghost' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
<?php endif; ?>

<?php footer_html(); ?>
