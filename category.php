<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');

$stmt = db()->prepare("
    SELECT
        id,
        name,
        slug
    FROM categories
    WHERE slug = ?
    LIMIT 1
");

$stmt->execute([$slug]);

$category = $stmt->fetch();

if (!$category) {
    http_response_code(404);

    $pageTitle = 'Рубрика не найдена';

    include __DIR__ . '/includes/header.php';
    ?>

    <section class="article-page">
        <span class="section-label">404</span>

        <h1>Рубрика не найдена</h1>

        <p class="dek">
            Такой рубрики не существует.
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

$stmt = db()->prepare("
    SELECT
        p.*,
        u.display_name AS author_name
    FROM posts p
    LEFT JOIN users u
        ON u.id = p.author_id
    WHERE p.category_id = ?
      AND p.status = 'published'
    ORDER BY p.published_at DESC
");

$stmt->execute([
    (int)$category['id']
]);

$posts = $stmt->fetchAll();

$pageTitle = $category['name'] . ' — ' . APP_NAME;

include __DIR__ . '/includes/header.php';
?>

<h1 class="category-heading">
    <?= e($category['name']) ?>
</h1>

<?php if ($posts): ?>

    <section class="story-grid">

        <?php foreach ($posts as $post): ?>

            <article class="news-card">

                <?php if (!empty($post['image_url'])): ?>

                    <img
                        class="story-image"
                        src="<?= e($post['image_url']) ?>"
                        alt="<?= e($post['title']) ?>"
                    >

                <?php endif; ?>

                <h2>
                    <a href="<?= e(
                        url(
                            'article.php?slug=' .
                            urlencode($post['slug'])
                        )
                    ) ?>">
                        <?= e($post['title']) ?>
                    </a>
                </h2>

                <p>
                    <?= e(
                        !empty($post['excerpt'])
                            ? $post['excerpt']
                            : excerpt($post['content'])
                    ) ?>
                </p>

                <div class="meta">

                    <span>
                        <?= e(
                            $post['author_name']
                            ?? 'Редакция'
                        ) ?>
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

            </article>

        <?php endforeach; ?>

    </section>

<?php else: ?>

    <section class="article-page">
        <p class="dek">
            В этой рубрике пока нет опубликованных материалов.
        </p>
    </section>

<?php endif; ?>

<?php
include __DIR__ . '/includes/footer.php';
?>
