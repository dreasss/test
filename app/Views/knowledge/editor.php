<?php include __DIR__ . '/../partials/header.php'; ?>
<div class="page-header">
    <h2>Article Editor</h2>
</div>
<form method="post" action="/knowledge/save" class="form" onsubmit="syncEditor()">
    <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
    <input type="hidden" name="id" value="<?= $article['id'] ?? '' ?>">
    <label>Slug
        <input type="text" name="slug" value="<?= htmlspecialchars($article['slug'] ?? '') ?>">
    </label>
    <label>Title (RU)
        <input type="text" name="title_ru" value="<?= htmlspecialchars($article['title_ru'] ?? '') ?>">
    </label>
    <label>Title (EN)
        <input type="text" name="title_en" value="<?= htmlspecialchars($article['title_en'] ?? '') ?>">
    </label>
    <div class="wysiwyg">
        <div class="toolbar">
            <button type="button" data-cmd="bold">B</button>
            <button type="button" data-cmd="italic">I</button>
            <button type="button" data-cmd="insertUnorderedList">• List</button>
        </div>
        <label>Body (RU)
            <div class="editor" contenteditable="true" data-target="body_ru"><?= htmlspecialchars($article['body_ru'] ?? '') ?></div>
            <textarea name="body_ru" class="hidden" data-editor-target="body_ru"></textarea>
        </label>
        <label>Body (EN)
            <div class="editor" contenteditable="true" data-target="body_en"><?= htmlspecialchars($article['body_en'] ?? '') ?></div>
            <textarea name="body_en" class="hidden" data-editor-target="body_en"></textarea>
        </label>
    </div>
    <label>Tags
        <input type="text" name="tags" value="<?= htmlspecialchars($article['tags'] ?? '') ?>">
    </label>
    <label>Status
        <select name="status" class="select">
            <option value="draft" <?= ($article['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="published" <?= ($article['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
        </select>
    </label>
    <button class="btn primary" type="submit"><?= $this->localization->get('save') ?></button>
</form>
<?php include __DIR__ . '/../partials/footer.php'; ?>
