<?php
require_once __DIR__ . '/config/config.php';
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove'])) cartRemove((int)$_POST['remove']);
    if (isset($_POST['clear'])) $_SESSION['cart'] = [];
    if (isset($_POST['checkout'])) {
        $_SESSION['cart'] = [];
        flash('تم تنفيذ الطلب بنجاح!');
    }
    header('Location: ' . url('cart.php'));
    exit;
}

$byId = DataStore::productsById();
header_html('السلة');
?>
<h2 class="section-title">🛒 سلة التسوق</h2>
<?php if (!cart()): ?>
  <p style="color:var(--text-dim);">السلة فارغة. <a href="<?= url('products.php') ?>" style="color:var(--primary);">تصفح المنتجات</a></p>
<?php else: ?>
  <table class="table">
    <thead><tr><th>المنتج</th><th>الفئة</th><th>السعر</th><th>الكمية</th><th>المجموع</th><th></th></tr></thead>
    <tbody>
    <?php foreach (cart() as $pid => $qty): $p = $byId[$pid] ?? null; if (!$p) continue; ?>
      <tr>
        <td>منتج #<?= $pid ?></td>
        <td><?= e($p['category']) ?></td>
        <td>$<?= number_format((float)$p['price'], 2) ?></td>
        <td><?= $qty ?></td>
        <td>$<?= number_format((float)$p['price'] * $qty, 2) ?></td>
        <td>
          <form method="post" style="display:inline;">
            <button class="btn btn-danger" name="remove" value="<?= $pid ?>">حذف</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div style="margin-top:24px;display:flex;justify-content:space-between;align-items:center;">
    <strong style="font-size:24px;color:var(--accent);">المجموع: $<?= number_format(cartTotal(), 2) ?></strong>
    <form method="post">
      <button class="btn btn-ghost" name="clear" value="1">تفريغ</button>
      <button class="btn" name="checkout" value="1">إتمام الطلب ✓</button>
    </form>
  </div>
<?php endif; ?>
<?php footer_html(); ?>
