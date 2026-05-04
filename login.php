<?php
require_once __DIR__ . '/config/config.php';

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['user_id'] ?? '');
    $p = $_POST['password'] ?? '';
    if (Auth::login($u, $p)) {
        header('Location: ' . url('index.php'));
        exit;
    }
    $err = 'بيانات الدخول غير صحيحة';
}
header_html('دخول');
?>
<div class="form-card">
  <h2>تسجيل الدخول</h2>
  <?php if ($err): ?><div class="flash" style="border-color:var(--danger);"><?= e($err) ?></div><?php endif; ?>
  <form method="post">
    <label>رقم المستخدم (User ID)</label>
    <input type="text" name="user_id" placeholder="مثال: 5" required autofocus>
    <label>كلمة المرور</label>
    <input type="password" name="password" required>
    <button class="btn" type="submit">دخول</button>
  </form>
  <div style="margin-top:20px;padding:14px;background:var(--surface-2);border-radius:10px;font-size:13px;color:var(--text-dim);">
    <strong>للتجربة:</strong><br>
    • مستخدم عادي: ID = أي رقم من 2-1000، كلمة المرور: <code>password</code><br>
    • أدمن: ID = 1، كلمة المرور: <code>admin123</code>
  </div>
</div>
<?php footer_html(); ?>
