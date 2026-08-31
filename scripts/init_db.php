<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function connect_server(): PDO {
    $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

$attempts = 30;
$pdo = null;

for ($i = 1; $i <= $attempts; $i++) {
    try {
        $pdo = connect_server();
        break;
    } catch (Throwable $e) {
        fwrite(STDERR, "Waiting for MySQL ($i/$attempts)...\n");
        sleep(2);
    }
}

if (!$pdo) {
    fwrite(STDERR, "MySQL is unavailable.\n");
    exit(1);
}

$dbName = str_replace('`', '``', DB_NAME);
$pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `$dbName`");

$schema = [
"CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(120) NOT NULL,
    role ENUM('admin','editor') NOT NULL DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB",
"CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB",
"CREATE TABLE IF NOT EXISTS posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT NULL,
    content MEDIUMTEXT NOT NULL,
    image_url VARCHAR(1000) NULL,
    category_id INT UNSIGNED NULL,
    author_id INT UNSIGNED NOT NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    published_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_posts_public (status, published_at),
    INDEX idx_posts_category (category_id),
    CONSTRAINT fk_posts_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_posts_author
        FOREIGN KEY (author_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB"
];

foreach ($schema as $sql) {
    $pdo->exec($sql);
}

$adminLogin = getenv('ADMIN_LOGIN') ?: 'admin';
$adminPassword = getenv('ADMIN_PASSWORD') ?: 'admin';
$adminEmail = 'admin@example.com';

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$adminEmail]);
$userId = $stmt->fetchColumn();

$hash = password_hash($adminPassword, PASSWORD_DEFAULT);

if (!$userId) {
    $stmt = $pdo->prepare("INSERT INTO users(email,password_hash,display_name,role) VALUES(?,?,?,'admin')");
    $stmt->execute([$adminEmail, $hash, 'Главный редактор']);
    $userId = (int)$pdo->lastInsertId();
} else {
    // Keep the requested default admin/admin unless env variables override it.
    $stmt = $pdo->prepare("UPDATE users SET password_hash=?, display_name='Главный редактор', role='admin' WHERE id=?");
    $stmt->execute([$hash, $userId]);
}

$categories = [
    ['Главное', 'glavnoe'],
    ['Город', 'gorod'],
    ['Культура', 'kultura'],
    ['Технологии', 'tehnologii'],
    ['Мнения', 'mneniya'],
];

$stmt = $pdo->prepare('INSERT IGNORE INTO categories(name,slug) VALUES(?,?)');
foreach ($categories as [$name, $slug]) {
    $stmt->execute([$name, $slug]);
}

$count = (int)$pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
if ($count === 0) {
    $catId = (int)$pdo->query("SELECT id FROM categories WHERE slug='glavnoe' LIMIT 1")->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO posts
        (title,slug,excerpt,content,category_id,author_id,status,is_featured,published_at)
        VALUES(?,?,?,?,?,?,'published',1,NOW())");
    $stmt->execute([
        'Новый выпуск: город просыпается',
        'novyy-vypusk-gorod-prosypaetsya',
        'Первый демонстрационный материал вашей цифровой газеты.',
        '<p>Это демонстрационная статья. Замените её настоящей новостью через панель редакции.</p><h2>Газетный ритм</h2><p>Макет построен на крупных заголовках, колонках, контрастных линейках и состаренной бумажной фактуре.</p>',
        $catId,
        $userId
    ]);
}

fwrite(STDOUT, "Database ready.\n");
