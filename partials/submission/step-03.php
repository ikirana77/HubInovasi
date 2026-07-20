<fieldset class="form-section submission-step" data-submission-step="3" hidden>
    <legend><span>03</span> <?= e(tr('Butiran Produk Mengikut Kategori', 'Category-specific Product Details')) ?></legend>
    <div class="adaptive-category-notice" role="note">
        <strong><?= e(tr('Medan adaptif CP10B aktif', 'CP10B adaptive fields enabled')) ?></strong>
        <p><?= e(tr('Soalan di bawah berubah mengikut bidang penyelesaian Step 1 dan disimpan bersama draf anda.', 'The questions below change according to the Step 1 solution area and are saved with your draft.')) ?></p>
    </div>

    <div class="adaptive-category-empty" data-adaptive-category-empty <?= $selectedArea !== '' ? 'hidden' : '' ?>>
        <p><?= e(tr('Pilih bidang penyelesaian utama dalam Step 1 untuk memaparkan butiran produk yang berkaitan.', 'Choose a primary solution area in Step 1 to display the relevant product details.')) ?></p>
        <button class="button button--secondary" type="button" data-go-to-step="1"><?= e(tr('Kembali ke Identiti Projek', 'Return to Project Identity')) ?></button>
    </div>

    <?php foreach ($categoryFieldDefinitions as $categorySlug => $fields): ?>
        <?php $categoryIsActive = $selectedArea === $categorySlug; ?>
        <section class="adaptive-category-panel" data-adaptive-category="<?= e($categorySlug) ?>" <?= $categoryIsActive ? '' : 'hidden' ?>>
            <header>
                <p class="eyebrow"><?= e(tr('Soalan untuk bidang', 'Questions for area')) ?></p>
                <h2><?= e($areaOptions[$categorySlug] ?? $categorySlug) ?></h2>
            </header>
            <?php foreach ($fields as $field): ?>
                <?php $fieldValue = $categoryIsActive ? ($categoryDetails[$field['key']] ?? '') : ''; ?>
                <label class="form-field">
                    <span><?= e($field['label']) ?><?php if (!empty($field['required'])): ?> <em>*</em><?php endif; ?></span>
                    <?php if ($field['type'] === 'textarea'): ?>
                        <textarea name="category_details[<?= e($field['key']) ?>]" rows="4" maxlength="<?= (int) $field['maxlength'] ?>" data-adaptive-input data-required="<?= !empty($field['required']) ? '1' : '0' ?>" <?= $categoryIsActive ? (!empty($field['required']) ? 'required' : '') : 'disabled' ?>><?= e($fieldValue) ?></textarea>
                    <?php else: ?>
                        <input type="<?= e($field['type']) ?>" name="category_details[<?= e($field['key']) ?>]" value="<?= e($fieldValue) ?>" maxlength="<?= (int) $field['maxlength'] ?>" data-adaptive-input data-required="<?= !empty($field['required']) ? '1' : '0' ?>" <?= $categoryIsActive ? (!empty($field['required']) ? 'required' : '') : 'disabled' ?>>
                    <?php endif; ?>
                    <small><?= e($field['help']) ?></small>
                </label>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
</fieldset>
