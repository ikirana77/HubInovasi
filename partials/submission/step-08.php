<fieldset class="form-section submission-step" data-submission-step="8" hidden>
    <legend><span>08</span> <?= e(tr('Semakan & Hantar', 'Review & Submit')) ?></legend>
    <p class="submission-step__intro"><?= e(tr('Semak ringkasan medan aktif sebelum menghantar projek kepada admin.', 'Review the active-field summary before sending the project to an administrator.')) ?></p>
    <dl class="submission-review">
        <div><dt><?= e(tr('Projek', 'Project')) ?></dt><dd data-review-field="project_name"><?= submission_value($submission, 'project_name', '—') ?></dd></div>
        <div><dt><?= e(tr('Bidang', 'Area')) ?></dt><dd data-review-field="category"><?= e($selectedArea ?: '—') ?></dd></div>
        <div><dt><?= e(tr('Tagline', 'Tagline')) ?></dt><dd data-review-field="tagline"><?= submission_value($submission, 'tagline', '—') ?></dd></div>
        <div><dt><?= e(tr('Masalah', 'Problem')) ?></dt><dd data-review-field="problem"><?= submission_value($submission, 'problem', '—') ?></dd></div>
        <div><dt><?= e(tr('Penyelesaian', 'Solution')) ?></dt><dd data-review-field="solution"><?= submission_value($submission, 'solution', '—') ?></dd></div>
        <div><dt><?= e(tr('Impak', 'Impact')) ?></dt><dd data-review-field="impact"><?= submission_value($submission, 'impact', '—') ?></dd></div>
    </dl>
    <div class="submission-review-adaptive">
        <h2><?= e(tr('Butiran produk mengikut kategori', 'Category-specific product details')) ?></h2>
        <?php foreach ($categoryFieldDefinitions as $categorySlug => $fields): ?>
            <dl class="submission-review submission-review--category" data-review-category="<?= e($categorySlug) ?>" <?= $selectedArea === $categorySlug ? '' : 'hidden' ?>>
                <?php foreach ($fields as $field): ?>
                    <div><dt><?= e($field['label']) ?></dt><dd data-review-category-key="<?= e($field['key']) ?>"><?= e($selectedArea === $categorySlug ? ($categoryDetails[$field['key']] ?? '—') : '—') ?></dd></div>
                <?php endforeach; ?>
            </dl>
        <?php endforeach; ?>
    </div>
    <label class="form-check"><input type="checkbox" name="consent" required><span><?= e(tr('Saya bersetuju maklumat ini disimpan sebagai submission dan disemak oleh admin HubInovasi.', 'I agree that this information may be stored as a submission and reviewed by the HubInovasi administrator.')) ?> <em>*</em></span></label>
    <button class="button button--primary submission-submit" type="submit" name="intent" value="submit_review"><?= e(tr('Hantar untuk Semakan', 'Submit for Review')) ?></button>
</fieldset>
