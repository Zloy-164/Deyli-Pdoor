<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

if (is_admin()) {
    redirect('admin/index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $login = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $expectedLogin = getenv('ADMIN_LOGIN') ?: 'admin';

    $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
    $stmt->execute(['admin@example.com']);
    $user = $stmt->fetch();

    if (
        $login === $expectedLogin &&
        $user &&
        password_verify($password, $user['password_hash'])
    ) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['display_name'] = $user['display_name'];

        redirect('admin/index.php');
    }

    $error = 'Неверный логин или пароль.';
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход — <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/fonts.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<div class="admin-shell">
    <section class="login-card">
        <span class="section-label">Только для редакции</span>
        <h1 class="admin-title">Вход</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="field">
                <label for="login">Логин</label>
                <input id="login" name="email" type="text" required autocomplete="username">
            </div>

            <div class="field">
                <label for="password">Пароль</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
            </div>

            <button class="btn" type="submit">Войти в редакцию</button>
        </form>
    </section>
</div>
</body>
</html>
