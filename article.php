<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');

$stmt = db()->prepare("
    SELECT
        p.*,
        c.name AS category_name,
        c.slug AS category_slug,
        u.display_name AS author_name
    FROM posts p
    LEFT JOIN categories c
        ON c.id = p.category_id
    LEFT JOIN users u
        ON u.id = p.author_id
    WHERE p.slug = ?
      AND p.status = 'published'
    LIMIT 1
");

$stmt->execute([$slug]);

$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);

    $pageTitle = 'Материал не найден';

    include __DIR__ . '/includes/header.php';
    ?>

    <section class="article-page">
        <span class="section-label">404</span>

        <h1>Материал не найден</h1>

        <p class="dek">
            Такой публикации нет или она не опубликована.
        </p>

        <p>
            <a href="<?= e(url('index.php')) ?>">
                ← Вернуться на главную
            </a>
        </p>
    </section>

    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $post['title'] . ' — ' . APP_NAME;

$pageDescription = !empty($post['excerpt'])
    ? $post['excerpt']
    : excerpt($post['content']);

include __DIR__ . '/includes/header.php';
?>

<article class="article-page">

    <span class="section-label">
        <?= e($post['category_name'] ?? 'Материал') ?>
    </span>

    <h1>
        <?= e($post['title']) ?>
    </h1>

    <?php if (!empty($post['excerpt'])): ?>

        <p class="dek">
            <?= e($post['excerpt']) ?>
        </p>

    <?php endif; ?>

    <div class="meta">

        <span>
            Автор:
            <?= e($post['author_name'] ?? 'Редакция') ?>
        </span>

        <?php if (!empty($post['published_at'])): ?>

            <span>
                <?= e(
                    format_date(
                        $post['published_at']
                    )
                ) ?>
            </span>

        <?php endif; ?>

    </div>

    <?php if (!empty($post['image_url'])): ?>

        <img
            class="story-image"
            src="<?= e($post['image_url']) ?>"
            alt="<?= e($post['title']) ?>"
        >

    <?php endif; ?>

    <div class="article-body">
        <?= $post['content'] ?>
    </div>

</article>

<?php
include __DIR__ . '/includes/footer.php';
?>
