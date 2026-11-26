<?php
require_once __DIR__ . '/../includes/functions.php';
if (!empty($_SESSION['admin_id'])) {
    if (time() - ($_SESSION['admin_seen'] ?? 0) <= 7200) { redirect(url('admin/index.php')); }
    unset($_SESSION['admin_id']);
}
$error = null;
$kunci = APP_ROOT . '/assets/uploads/.login-' . hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'x'); // folder ini tertutup untuk publik

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    // Batasi percobaan per alamat IP: 5 kali gagal = jeda 15 menit.
    $data = is_file($kunci) ? json_decode((string) file_get_contents($kunci), true) : null;
    if (!$data || time() - $data['t'] > 900) { $data = ['n' => 0, 't' => time()]; }
    if ($data['n'] >= 5) {
        $error = 'Terlalu banyak percobaan. Coba lagi sekitar 15 menit.';
    } else {
        $user = query('SELECT * FROM admin WHERE username=?', [input('username', 50)])->fetch();
        $pass = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        if ($user && password_verify($pass, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_seen'] = time();
            unset($_SESSION['csrf']);
            if (is_file($kunci)) { unlink($kunci); }
            redirect(url('admin/index.php'));
        }
        $data['n']++;
        file_put_contents($kunci, json_encode($data), LOCK_EX);
        sleep(1);
        $error = 'Username atau password salah.';
    }
}
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow">
<title>Login Admin · Sahaja</title><link rel="icon" href="<?= asset('assets/images/logo/favicon.png') ?>"><link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>"></head>
<body class="login-body">
<main class="panel login-box">
  <img src="<?= asset('assets/images/logo/bunga.png') ?>" alt="">
  <h1 class="center">Admin</h1>
  <?php if ($error): ?><div class="alert alert-err" role="alert"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="form-grid">
    <?= csrf_field() ?>
    <label>Username<input name="username" required autocomplete="username" autofocus></label>
    <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
    <button class="btn">Masuk</button>
  </form>
</main>
</body></html>
