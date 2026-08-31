<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * POSTS_PER_PAGE;

$total = (int)db()
    ->query("
        SELECT COUNT(*)
        FROM posts
        WHERE status = 'published'
          AND published_at <= NOW()
    ")
    ->fetchColumn();

$totalPages = max(
    1,
    (int)ceil($total / POSTS_PER_PAGE)
);

$stmt = db()->prepare("
    SELECT
        p.*,
        c.name AS category_name,
        u.display_name AS author_name
    FROM posts p
    LEFT JOIN categories c
        ON c.id = p.category_id
    LEFT JOIN users u
        ON u.id = p.author_id
    WHERE p.status = 'published'
      AND p.published_at <= NOW()
    ORDER BY
        p.is_featured DESC,
        p.published_at DESC
    LIMIT :lim OFFSET :off
");

$stmt->bindValue(':lim', POSTS_PER_PAGE, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();

$posts = $stmt->fetchAll();

$lead = $posts[0] ?? null;
$side = array_slice($posts, 1, 3);
$rest = array_slice($posts, 4);

$pageTitle = APP_NAME . ' — Главная';

include __DIR__ . '/includes/header.php';
?>

<?php if (!$posts): ?>

    <section class="article-page">
        <span class="section-label">Редакция готовится</span>

        <h1>Первый выпуск уже близко</h1>

        <p class="dek">
            В базе пока нет опубликованных материалов.
            Зайдите в редакционную панель и создайте первую новость.
        </p>
    </section>

<?php else: ?>

    <section class="front-grid">

        <?php if ($lead): ?>
            <article class="lead-story">

                <span class="section-label">
                    <?= e($lead['category_name'] ?? 'Главное') ?>
                </span>

                <h1>
                    <a href="<?= e(
                        url(
                            'article.php?slug=' .
                            urlencode($lead['slug'])
                        )
                    ) ?>">
                        <?= e($lead['title']) ?>
                    </a>
                </h1>

                <p class="dek">
                    <?= e(
                        $lead['excerpt']
                            ?: excerpt($lead['content'], 240)
                    ) ?>
                </p>

                <div class="meta">
                    <span>
                        <?= e(
                            $lead['author_name']
                            ?? 'Редакция'
                        ) ?>
                    </span>

                    <span>
                        <?= e(
                            format_date(
                                $lead['published_at']
                            )
                        ) ?>
                    </span>
                </div>

                <?php if (!empty($lead['image_url'])): ?>
                    <img
                        class="story-image"
                        src="<?= e($lead['image_url']) ?>"
                        alt="<?= e($lead['title']) ?>"
                    >
                <?php else: ?>
                    <div class="placeholder-art">
                        Extra!<br>
                        Читайте всё
                    </div>
                <?php endif; ?>

            </article>
        <?php endif; ?>

        <aside>
            <?php foreach ($side as $post): ?>

                <article class="news-card">

                    <span class="section-label">
                        <?= e(
                            $post['category_name']
                            ?? 'Новости'
                        ) ?>
                    </span>

                    <h3>
                        <a href="<?= e(
                            url(
                                'article.php?slug=' .
                                urlencode($post['slug'])
                            )
                        ) ?>">
                            <?= e($post['title']) ?>
                        </a>
                    </h3>

                    <p>
                        <?= e(
                            $post['excerpt']
                                ?: excerpt(
                                    $post['content'],
                                    120
                                )
                        ) ?>
                    </p>

                    <div class="meta">
                        <span>
                            <?= e(
                                format_date(
                                    $post['published_at']
                                )
                            ) ?>
                        </span>
                    </div>

                </article>

            <?php endforeach; ?>
        </aside>

    </section>

    <?php if ($rest): ?>

        <hr class="paper-rule">

        <section class="story-grid">

            <?php foreach ($rest as $post): ?>

                <article class="news-card">

                    <span class="section-label">
                        <?= e(
                            $post['category_name']
                            ?? 'Новости'
                        ) ?>
                    </span>

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
                            $post['excerpt']
                                ?: excerpt(
                                    $post['content']
                                )
                        ) ?>
                    </p>

                    <div class="meta">

                        <span>
                            <?= e(
                                $post['author_name']
                                ?? 'Редакция'
                            ) ?>
                        </span>

                        <span>
                            <?= e(
                                format_date(
                                    $post['published_at']
                                )
                            ) ?>
                        </span>

                    </div>

                </article>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>

    <?php if ($totalPages > 1): ?>

        <nav
            class="pagination"
            aria-label="Навигация по страницам"
        >

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                <?php if ($i === $page): ?>

                    <span class="active">
                        <?= $i ?>
                    </span>

                <?php else: ?>

                    <a href="<?= e(
                        url(
                            'index.php?page=' . $i
                        )
                    ) ?>">
                        <?= $i ?>
                    </a>

                <?php endif; ?>

            <?php endfor; ?>

        </nav>

    <?php endif; ?>

<?php endif; ?>

<?php
include __DIR__ . '/includes/footer.php';
?>
