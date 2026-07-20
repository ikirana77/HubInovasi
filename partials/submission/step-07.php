<?php
$mediaCards = [
    SUBMISSION_PROBLEM_VISUAL_TYPE => ['icon'=>'⚠️','title'=>tr('Visual Masalah','Problem Visuals'),'help'=>tr('1–3 gambar seperti proses lama, borang manual, rekod Excel atau situasi sebenar masalah. Digalakkan.','1–3 images such as the old process, manual forms, Excel records or the real problem situation. Recommended.'),'limit'=>3],
    SUBMISSION_SOLUTION_VISUAL_TYPE => ['icon'=>'💡','title'=>tr('Visual Penyelesaian','Solution Visuals'),'help'=>tr('1–3 screenshot aplikasi, dashboard, mockup, prototaip atau diagram sistem.','1–3 application screenshots, dashboards, mockups, prototypes or system diagrams.'),'limit'=>3],
    SUBMISSION_POSTER_TYPE => ['icon'=>'🖼️','title'=>tr('Poster Projek','Project Poster'),'help'=>tr('Satu poster pilihan, maksimum 5MB. Poster akan dipaparkan sebagai kad khas.','One optional poster, maximum 5MB. It will appear as a dedicated card.'),'limit'=>1],
    SUBMISSION_GALLERY_TYPE => ['icon'=>'📷','title'=>tr('Galeri Projek','Project Gallery'),'help'=>tr('Sehingga 10 gambar. Pilih satu sebagai Gambar Utama dan tambah kapsyen jika perlu.','Up to 10 images. Select one Cover Image and add captions where useful.'),'limit'=>10],
];
?>
<fieldset class="form-section submission-step" data-submission-step="7" hidden>
    <legend><span>07</span> <?= e(tr('Media, Galeri & Perjalanan', 'Media, Gallery & Journey')) ?></legend>
    <p class="submission-step__intro"><?= e(tr('Pilih visual yang benar-benar membantu orang memahami masalah, penyelesaian dan perjalanan projek.', 'Choose visuals that genuinely help people understand the problem, solution and project journey.')) ?></p>

    <?php foreach ($mediaCards as $mediaType => $card): $prefix = submission_media_source_prefix($mediaType); $rows = $mediaDetails[$mediaType] ?? []; ?>
        <section class="media-builder-card media-builder-card--<?= e(str_replace('_', '-', $mediaType)) ?>" data-media-collection="<?= e($mediaType) ?>" data-media-limit="<?= (int) $card['limit'] ?>">
            <header><span aria-hidden="true"><?= $card['icon'] ?></span><div><h2><?= e($card['title']) ?></h2><p><?= e($card['help']) ?></p></div></header>
            <label class="gallery-dropzone"><strong><?= e(tr('Pilih gambar', 'Choose images')) ?></strong><span><?= e(tr('JPG, PNG atau WebP · maksimum 5MB setiap fail', 'JPG, PNG or WebP · maximum 5MB per file')) ?></span><input type="file" name="<?= e($prefix) ?>_images[]" accept="image/jpeg,image/png,image/webp" <?= $card['limit'] > 1 ? 'multiple' : '' ?> data-media-input></label>
            <div class="gallery-editor" data-media-list>
                <?php foreach ($rows as $image): $reference = 'id:' . $image['id']; $publicPath = submission_gallery_public_path($image['file_path'] ?? null); ?>
                    <article class="gallery-editor-card" draggable="true" data-media-row data-media-reference="<?= e($reference) ?>">
                        <input type="hidden" name="<?= e($prefix) ?>_order[]" value="<?= e($reference) ?>">
                        <div class="gallery-editor-card__image" data-media-preview><?php if ($publicPath): ?><img src="<?= e($publicPath) ?>" alt=""><?php endif; ?></div>
                        <div class="gallery-editor-card__body"><span class="gallery-drag-handle" title="<?= e(tr('Seret untuk susun', 'Drag to reorder')) ?>">↕</span><?php if ($mediaType === SUBMISSION_GALLERY_TYPE): ?><label class="form-check form-check--compact"><input type="radio" name="gallery_cover" value="<?= e($reference) ?>" <?= !empty($image['is_cover']) ? 'checked' : '' ?>><span><?= e(tr('Gambar Utama', 'Cover Image')) ?></span></label><?php endif; ?><label class="gallery-replace"><span><?= e(tr('Ganti', 'Replace')) ?></span><input type="file" name="<?= e($prefix) ?>_replacements[<?= e($prefix) ?>-<?= (int) $image['id'] ?>]" accept="image/jpeg,image/png,image/webp" data-media-replacement></label><button type="button" class="text-button text-button--danger" data-remove-media><?= e(tr('Buang', 'Remove')) ?></button></div>
                        <label class="form-field gallery-caption"><span><?= e(tr('Kapsyen ringkas (pilihan)', 'Short caption (optional)')) ?></span><input type="text" name="<?= e($prefix) ?>_captions[<?= e($reference) ?>]" value="<?= e($image['caption'] ?? '') ?>" maxlength="1000" placeholder="<?= e(tr('Terangkan perkara penting dalam gambar', 'Explain what matters in this image')) ?>"></label>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="gallery-empty" data-media-empty <?= $rows ? 'hidden' : '' ?>><?= e(tr('Belum ada gambar.', 'No images yet.')) ?></p>
        </section>
    <?php endforeach; ?>

    <section class="media-builder-card media-builder-card--video" aria-labelledby="video-title">
        <header><span aria-hidden="true">🎥</span><div><h2 id="video-title"><?= e(tr('Video Demo', 'Demo Video')) ?></h2><p><?= e(tr('Tampal satu pautan YouTube, Vimeo, Google Drive atau Microsoft Stream. Video tidak dimuat naik ke server.', 'Paste one YouTube, Vimeo, Google Drive or Microsoft Stream link. The video is not uploaded to the server.')) ?></p></div></header>
        <label class="form-field"><span><?= e(tr('URL video (pilihan)', 'Video URL (optional)')) ?></span><input type="url" name="video_demo_url" value="<?= e($mediaDetails['video_url'] ?? '') ?>" maxlength="1000" placeholder="https://www.youtube.com/watch?v=..." data-video-url></label>
    </section>

    <section class="media-builder-card media-builder-card--journey" aria-labelledby="journey-title">
        <header><span aria-hidden="true">🚀</span><div><h2 id="journey-title"><?= e(tr('Perjalanan Projek', 'Project Journey')) ?></h2><p><?= e(tr('Tambah detik penting projek dalam bentuk rekod ringkas.', 'Add important moments in the project as short records.')) ?></p></div></header>
        <div class="journey-editor" data-journey-list>
            <?php foreach ($mediaDetails['milestones'] as $index => $milestone): $key = $milestone['row_key'] ?? ('journey-' . $index); ?>
                <article class="journey-editor-card" draggable="true" data-journey-row><input type="hidden" name="journey_records[<?= e($key) ?>][id]" value="<?= (int) ($milestone['id'] ?? 0) ?>"><div class="journey-editor-card__toolbar"><span class="gallery-drag-handle">↕</span><button type="button" class="text-button text-button--danger" data-remove-journey><?= e(tr('Buang', 'Remove')) ?></button></div><label class="form-field"><span><?= e(tr('Tajuk', 'Title')) ?></span><input type="text" name="journey_records[<?= e($key) ?>][title]" value="<?= e($milestone['title'] ?? '') ?>" maxlength="255" placeholder="<?= e(tr('Contoh: Prototaip pertama', 'Example: First prototype')) ?>"></label><label class="form-field"><span><?= e(tr('Penerangan ringkas', 'Short description')) ?></span><textarea name="journey_records[<?= e($key) ?>][description]" rows="2" maxlength="2000"><?= e($milestone['description'] ?? '') ?></textarea></label></article>
            <?php endforeach; ?>
        </div>
        <p class="journey-empty" data-journey-empty <?= $mediaDetails['milestones'] ? 'hidden' : '' ?>><?= e(tr('Belum ada rekod perjalanan.', 'No journey records yet.')) ?></p>
        <button type="button" class="button button--secondary" data-add-journey><?= e(tr('Tambah Perjalanan', 'Add Journey')) ?></button>
        <template data-journey-template><article class="journey-editor-card" draggable="true" data-journey-row><input type="hidden" name="journey_records[__KEY__][id]" value="0"><div class="journey-editor-card__toolbar"><span class="gallery-drag-handle">↕</span><button type="button" class="text-button text-button--danger" data-remove-journey><?= e(tr('Buang', 'Remove')) ?></button></div><label class="form-field"><span><?= e(tr('Tajuk', 'Title')) ?></span><input type="text" name="journey_records[__KEY__][title]" maxlength="255" placeholder="<?= e(tr('Contoh: Idea awal', 'Example: Initial idea')) ?>"></label><label class="form-field"><span><?= e(tr('Penerangan ringkas', 'Short description')) ?></span><textarea name="journey_records[__KEY__][description]" rows="2" maxlength="2000" placeholder="<?= e(tr('Idea projek dibincangkan bersama mentor.', 'The project idea was discussed with a mentor.')) ?>"></textarea></label></article></template>
    </section>
</fieldset>
