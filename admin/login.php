Перейти к содержимому
Открыть боковую панель

Вы сказали:



Вы сказали:
Сайт не открывается 




ChatGPT сказал:
Обработка заняла 23s
Да, вижу — сайт всё ещё не отвечает. Сейчас наиболее вероятная причина уже порт, а не PHP-код.

Railway сам рекомендует приложению слушать 0.0.0.0 и порт из переменной PORT, а публичный домен должен быть направлен на тот же порт. Ошибка Application failed to respond часто возникает именно из-за несовпадения target port и порта приложения. 

Railway Docs
+1
Сделай так:

Deyli-Pdoor → Переменные → добавь:


PORT=8080
Примени изменения и сделай новый deploy.

Deyli-Pdoor → Настройки → Сеть → у домена deyli-pdoor.up.railway.app измени целевой порт с 80 на 8080.

Твой текущий Dockerfile уже использует ${PORT}, поэтому после этого PHP-сервер автоматически должен слушать:


0.0.0.0:8080
В логе правильного запуска должна появиться строка:


Database ready.
Starting PHP server on 0.0.0.0:8080
Если после этого сайт не откроется, пришли новый лог — но порт 8080 сейчас стоит исправить первым.






Вы сказали:
3й пункт бля это где гит или что 




ChatGPT сказал:
Это в Railway, не в GitHub.

Путь такой:

Railway → Deyli-Pdoor → Настройки → Сеть / Networking → Public Networking

Там у домена deyli-pdoor.up.railway.app будет целевой порт. Поменяй его с 80 на 8080.

То есть:

GitHub сейчас не трогаешь;

в Railway в Переменные добавляешь PORT=8080;

там же в Настройки → Сеть меняешь порт домена на 8080.

Если хочешь, скинь скрин вкладки Настройки → Сеть, я покажу куда именно жать.






Вы сказали:
Зашёл я в админку и вижу 
Фатальная ошибка: неперехваченное исключение PDOException: SQLSTATE[42000]: Синтаксическая ошибка или нарушение доступа: 1064 У вас ошибка в синтаксисе SQL; проверьте руководство, соответствующее вашей версии сервера MySQL, на предмет правильного синтаксиса для использования рядом с '\"admin \" LIMIT 1' в строке 1 в / var / www / html / admin / login.php: 2 Трассировка стека: # 0 /var/www/html/admin/login.php (2): PDO-> подготовить ('ВЫБРАТЬ * ИЗ u ...') #1 {main} добавлено /var/www/html/admin/login.php в строке 2 

Развернуть




ChatGPT сказал:
Да, ошибка именно в admin/login.php: там сломан SQL из-за \"admin\".

Я сделал исправленный файл:

Скачать исправленный login.php

Что делать:

GitHub → папка admin → файл login.php.

Открой редактирование.

Удали всё содержимое.

Вставь содержимое моего нового login.php.

Нажми Commit changes.

Railway сам сделает новый deploy.

После этого заходи:

/admin/login.php

Логин: admin
Пароль: admin

MySQL, Dockerfile, Volume и остальные файлы сейчас не трогай. 

login.php
Код





ChatGPT может допускать ошибки. Рекомендуем проверять важную информацию.



Средний


Выбрать файлыФайл не выбранВыбрать файлыФайл не выбран
Библиотека
/
login.php


12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35
36
37
38
39
40
41
42
43
44
45
46
47
48
49
50
51
52
53
54
55
56
57
58
59
60
61
62
63
64
65
66
67
68
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
