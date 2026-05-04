<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/algorithms/genetic.php';
Auth::requireLogin();

$user = Auth::user();
$K = (int)($_GET['k'] ?? GA_K);

$start = microtime(true);
$ga = new GeneticRecommender(
    (int)$user['user_id'],
    $K,
    GA_POPULATION,
    GA_GENERATIONS,
    GA_MUTATION
);
$result = $ga->run();
$duration = microtime(true) - $start;

$byId = DataStore::productsById();
header_html('توصياتي');
?>
<h2 class="section-title">✨ توصيات مخصصة لك</h2>
<p style="color:var(--text-dim);margin-bottom:24px;">
  تم توليد <?= $K ?> توصية بواسطة الخوارزمية الجينية في <?= number_format($duration, 3) ?> ثانية
  • أفضل لياقة: <strong style="color:var(--accent);"><?= number_format($result['fitness'], 3) ?></strong>
  • <?= GA_GENERATIONS ?> جيل، <?= GA_POPULATION ?> فرد
</p>

<div class="grid">
  <?php foreach ($result['recommendations'] as $pid): $p = $byId[$pid] ?? null; if (!$p) continue; ?>
    <div class="card recommendation-card">
      <span class="badge" style="margin-top:24px;display:block;width:fit-content;"><?= e($p['category']) ?></span>
      <h3>منتج #<?= $pid ?></h3>
      <div class="price">$<?= number_format((float)$p['price'], 2) ?></div>
      <a href="<?= url('product.php?id=' . $pid) ?>" class="btn" style="width:100%;text-align:center;">عرض</a>
    </div>
  <?php endforeach; ?>
</div>

<div class="chart-container">
  <h3 style="margin-bottom:14px;">📈 تقارب اللياقة عبر الأجيال</h3>
  <div style="display:flex;align-items:flex-end;gap:1px;height:140px;padding:10px;background:var(--bg);border-radius:8px;">
    <?php
    $max = max($result['convergence']);
    $min = min($result['convergence']);
    $range = max(0.01, $max - $min);
    foreach ($result['convergence'] as $f):
        $h = (int)((($f - $min) / $range) * 120) + 4;
    ?>
      <div class="bar" style="height:<?= $h ?>px;" title="<?= number_format($f, 3) ?>"></div>
    <?php endforeach; ?>
  </div>
  <p style="color:var(--text-dim);font-size:12px;margin-top:8px;">المحور الأفقي: رقم الجيل • العمودي: أعلى لياقة</p>
</div>

<?php footer_html(); ?>
