<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../algorithms/genetic.php';
Auth::requireAdmin();

$result = null; $duration = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid  = max(1, (int)($_POST['user_id'] ?? 1));
    $K    = max(2, min(20, (int)($_POST['k'] ?? 8)));
    $pop  = max(10, min(200, (int)($_POST['pop'] ?? 60)));
    $gen  = max(5,  min(200, (int)($_POST['gen'] ?? 40)));
    $mut  = max(0.0, min(1.0, (float)($_POST['mut'] ?? 0.1)));
    $start = microtime(true);
    $ga = new GeneticRecommender($uid, $K, $pop, $gen, $mut);
    $result = $ga->run();
    $duration = microtime(true) - $start;
}

header_html('اختبار GA');
?>
<h2 class="section-title">🧪 وحدة اختبار الخوارزمية الجينية</h2>
<form method="post" class="filters" style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;">
  <div><label style="font-size:12px;color:var(--text-dim);">User ID</label><input type="number" name="user_id" value="<?= e($_POST['user_id'] ?? '5') ?>"></div>
  <div><label style="font-size:12px;color:var(--text-dim);">K</label><input type="number" name="k" value="<?= e($_POST['k'] ?? '8') ?>"></div>
  <div><label style="font-size:12px;color:var(--text-dim);">السكان</label><input type="number" name="pop" value="<?= e($_POST['pop'] ?? '60') ?>"></div>
  <div><label style="font-size:12px;color:var(--text-dim);">الأجيال</label><input type="number" name="gen" value="<?= e($_POST['gen'] ?? '40') ?>"></div>
  <div><label style="font-size:12px;color:var(--text-dim);">الطفرة</label><input type="number" step="0.01" name="mut" value="<?= e($_POST['mut'] ?? '0.1') ?>"></div>
  <button class="btn" type="submit" style="grid-column:span 5;">شغّل الخوارزمية ⚡</button>
</form>

<?php if ($result): ?>
  <div class="stats">
    <div class="stat"><div class="num"><?= number_format($result['fitness'], 3) ?></div><div class="label">أفضل لياقة</div></div>
    <div class="stat"><div class="num"><?= number_format($duration, 3) ?>s</div><div class="label">زمن التنفيذ</div></div>
    <div class="stat"><div class="num"><?= count($result['recommendations']) ?></div><div class="label">توصية</div></div>
  </div>

  <div class="chart-container">
    <h3 style="margin-bottom:14px;">📈 منحنى التقارب</h3>
    <div style="display:flex;align-items:flex-end;gap:2px;height:200px;padding:10px;background:var(--bg);border-radius:8px;">
      <?php
      $max = max($result['convergence']);
      $min = min($result['convergence']);
      $range = max(0.01, $max - $min);
      foreach ($result['convergence'] as $i => $f):
          $h = (int)((($f - $min) / $range) * 180) + 4;
      ?>
        <div class="bar" style="height:<?= $h ?>px;flex:1;" title="جيل <?= $i+1 ?>: <?= number_format($f, 3) ?>"></div>
      <?php endforeach; ?>
    </div>
  </div>

  <h3 style="margin:20px 0 14px;">المنتجات الموصى بها</h3>
  <?php $byId = DataStore::productsById(); ?>
  <div class="grid">
    <?php foreach ($result['recommendations'] as $pid): $p = $byId[$pid] ?? null; if (!$p) continue; ?>
      <div class="card">
        <span class="badge"><?= e($p['category']) ?></span>
        <h3>منتج #<?= $pid ?></h3>
        <div class="price">$<?= number_format((float)$p['price'], 2) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php footer_html(); ?>
