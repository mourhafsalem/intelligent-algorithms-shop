<?php
declare(strict_types=1);

function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function url(string $path): string {
    return '/shop/' . ltrim($path, '/');
}

function flash(?string $msg = null): ?string {
    if ($msg !== null) { $_SESSION['_flash'] = $msg; return null; }
    $m = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return $m;
}

function cart(): array {
    return $_SESSION['cart'] ?? [];
}

function cartAdd(int $productId, int $qty = 1): void {
    $c = cart();
    $c[$productId] = ($c[$productId] ?? 0) + $qty;
    $_SESSION['cart'] = $c;
}

function cartRemove(int $productId): void {
    $c = cart();
    unset($c[$productId]);
    $_SESSION['cart'] = $c;
}

function cartTotal(): float {
    $total = 0.0;
    $byId = DataStore::productsById();
    foreach (cart() as $pid => $qty) {
        if (isset($byId[$pid])) $total += (float)$byId[$pid]['price'] * $qty;
    }
    return $total;
}

function header_html(string $title): void {
    $user = Auth::user();
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title><?= e($title) ?> - <?= SITE_NAME ?></title>
      <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    </head>
    <body>
    <nav class="nav">
      <div class="nav-inner">
        <a class="brand" href="<?= url('index.php') ?>">⚡ <?= SITE_NAME ?></a>
        <div class="nav-links">
          <a href="<?= url('products.php') ?>">المنتجات</a>
          <?php if ($user): ?>
            <a href="<?= url('recommendations.php') ?>">توصياتي ✨</a>
            <a href="<?= url('cart.php') ?>">السلة (<?= count(cart()) ?>)</a>
            <a href="<?= url('user/profile.php') ?>">حسابي</a>
            <?php if (Auth::isAdmin()): ?>
              <a href="<?= url('admin/index.php') ?>" class="admin-link">لوحة الأدمن</a>
            <?php endif; ?>
            <a href="<?= url('logout.php') ?>">خروج</a>
          <?php else: ?>
            <a href="<?= url('login.php') ?>">دخول</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>
    <main class="container">
    <?php
    if ($f = flash()) echo '<div class="flash">' . e($f) . '</div>';
}

function footer_html(): void {
    ?>
    </main>
    <footer class="footer">
      <p>mourhaf_196606 ... dania_175474 ... marita_212711 ... yusra_157068 ... banan_154555 ... reem_151560</p>
    </footer>
    </body></html>
    <?php
}
