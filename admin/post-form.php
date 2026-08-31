<?php
declare(strict_types=1);

$pageTitle = 'Редактор публикации';

require_once __DIR__ . '/_header.php';

$id = (int)($_GET['id'] ?? 0);

$p = [
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content' => '<p></p>',
    'image_url' => '',
    'category_id' => '',
    'status' => 'draft',
    'is_featured' => 0,
    'published_at' => date('Y-m-d\TH:i'),
];

if ($id) {
    $stmt = db()->prepare(
        'SELECT * FROM posts WHERE id = ? LIMIT 1'
    );

    $stmt->execute([$id]);

    $existingPost = $stmt->fetch();

    if (!$existingPost) {
        http_response_code(404);
        exit('Публикация не найдена.');
    }

    $p = $existingPost;

    $p['published_at'] = $p['published_at']
        ? date(
            'Y-m-d\TH:i',
            strtotime($p['published_at'])
        )
        : date('Y-m-d\TH:i');
}

$cats = categories();
?>

<form
    method="post"
    action="<?= e(url('admin/save-post.php')) ?>"
    class="form-grid"
    enctype="multipart/form-data"
>
    <input
        type="hidden"
        name="csrf_token"
        value="<?= e(csrf_token()) ?>"
    >

    <input
        type="hidden"
        name="id"
        value="<?= $id ?>"
    >

    <div class="field">
        <label for="title">
            Заголовок
        </label>

        <input
            id="title"
            name="title"
            value="<?= e($p['title']) ?>"
            required
            maxlength="255"
        >
    </div>

    <div class="inline-fields">

        <div class="field">
            <label for="slug">
                URL-slug
            </label>

            <input
                id="slug"
                name="slug"
                value="<?= e($p['slug']) ?>"
            >
        </div>

        <div class="field">
            <label for="category_id">
                Рубрика
            </label>

            <select
                id="category_id"
                name="category_id"
            >
                <option value="">
                    Без рубрики
                </option>

                <?php foreach ($cats as $c): ?>
                    <option
                        value="<?= (int)$c['id'] ?>"
                        <?=
                        (string)$p['category_id']
                        ===
                        (string)$c['id']
                            ? 'selected'
                            : ''
                        ?>
                    >
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

    </div>

    <div class="field">
        <label for="excerpt">
            Короткий анонс
        </label>

        <textarea
            id="excerpt"
            name="excerpt"
            style="min-height:100px"
        ><?= e($p['excerpt']) ?></textarea>
    </div>

    <div class="field">
        <label for="image_file">
            Фото новости
        </label>

        <input
            id="image_file"
            name="image_file"
            type="file"
            accept="image/jpeg,image/png,image/webp,image/gif"
        >

        <?php if (!empty($p['image_url'])): ?>
            <p>
                Текущее фото:
            </p>

            <img
                src="<?= e($p['image_url']) ?>"
                alt="<?= e($p['title']) ?>"
                style="
                    max-width:300px;
                    height:auto;
                    border:2px solid #111;
                "
            >
        <?php endif; ?>
    </div>

    <div class="field">
        <label for="content">
            Текст статьи (базовый HTML)
        </label>

        <textarea
            id="content"
            name="content"
            required
        ><?= e($p['content']) ?></textarea>
    </div>

    <div class="inline-fields">

        <div class="field">
            <label for="status">
                Статус
            </label>

            <select
                id="status"
                name="status"
            >
                <option
                    value="draft"
                    <?= $p['status'] === 'draft'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Черновик
                </option>

                <option
                    value="published"
                    <?= $p['status'] === 'published'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Опубликовано
                </option>
            </select>
        </div>

        <div class="field">
            <label for="published_at">
                Дата публикации
            </label>

            <input
                id="published_at"
                name="published_at"
                type="datetime-local"
                value="<?= e($p['published_at']) ?>"
            >
        </div>

    </div>

    <label>
        <input
            type="checkbox"
            name="is_featured"
            value="1"
            <?= !empty($p['is_featured'])
                ? 'checked'
                : ''
            ?>
        >

        Сделать главным материалом
    </label>

    <div class="actions">

        <button
            class="btn"
            type="submit"
        >
            Сохранить
        </button>

        <a
            class="btn btn-secondary"
            href="<?= e(url('admin/index.php')) ?>"
        >
            Отмена
        </a>

    </div>

</form>

<?php
require_once __DIR__ . '/_footer.php';
?>
