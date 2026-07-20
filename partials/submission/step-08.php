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
    <div class="submission-review-impact">
        <h2><?= e(tr('Impak, bukti dan pengiktirafan', 'Impact, evidence and recognition')) ?></h2>
        <section><h3><?= e(tr('Metrik', 'Metrics')) ?></h3><ul data-review-repeatable="metric"><?php foreach ($impactDetails['metrics'] as $metric): ?><li><?= e($metric['label'] . ': ' . (($metric['value'] ?: $metric['target']) ?? '') . ($metric['unit'] ? ' ' . $metric['unit'] : '')) ?></li><?php endforeach; ?></ul></section>
        <section><h3><?= e(tr('Bukti', 'Evidence')) ?></h3><ul data-review-repeatable="evidence"><?php foreach ($impactDetails['evidence'] as $item): ?><li><?= e($item['title'] . ($item['url'] ? ' — ' . $item['url'] : '')) ?></li><?php endforeach; ?></ul></section>
        <section><h3><?= e(tr('Anugerah & pengiktirafan', 'Awards & recognitions')) ?></h3><ul data-review-repeatable="recognition"><?php foreach ($impactDetails['recognitions'] as $recognition): ?><li><?= e($recognition['title'] . ($recognition['level'] ? ' — ' . $recognition['level'] : '')) ?></li><?php endforeach; ?></ul></section>
    </div>
    <div class="submission-review-people">
        <h2><?= e(tr('Peserta & mentor', 'Participants & mentors')) ?></h2>
        <section><h3><?= e(tr('Pelajar', 'Students')) ?></h3><ul data-review-people="student"><?php foreach ($participantDetails['students'] as $student): ?><li><?= !empty($student['is_team_leader']) ? '<strong>' . e(tr('Ketua: ', 'Leader: ') . $student['full_name']) . '</strong>' : e($student['full_name']) ?> — <?= e($student['class_name'] ?? '') ?> — <?= e($student['role_title'] ?? '') ?></li><?php endforeach; ?></ul></section>
        <section><h3><?= e(tr('Mentor', 'Mentors')) ?></h3><ul data-review-people="mentor"><?php foreach ($participantDetails['mentors'] as $mentor): ?><li><?= e($mentor['full_name']) ?> — <?= e($mentor['position_title'] ?? '') ?> — <?= e($mentor['role_title'] ?? '') ?></li><?php endforeach; ?></ul></section>
    </div>
    <div class="submission-review-mentoring"><h2><?= e(tr('Rekod Bimbingan Mentor', 'Mentor Guidance Records')) ?></h2><ul data-review-mentoring><?php foreach ($mentoringRecords as $record): ?><li><?= e(($record['mentor_name'] ?? tr('Mentor belum dipilih', 'Mentor not selected')) . ' — ' . ($record['guidance_focus'] ?? '') . (($record['guidance_outcome'] ?? '') !== '' ? ' → ' . $record['guidance_outcome'] : '')) ?></li><?php endforeach; ?></ul></div>
    <div class="submission-review-media"><h2><?= e(tr('Media, Galeri & Perjalanan', 'Media, Gallery & Journey')) ?></h2><section><h3><?= e(tr('Visual mengikut seksyen', 'Section visuals')) ?></h3><p><?= e(tr('Masalah', 'Problem')) ?>: <span data-review-media-count="problem_visual"><?= count($mediaDetails['problem_visual']) ?></span> · <?= e(tr('Penyelesaian', 'Solution')) ?>: <span data-review-media-count="solution_visual"><?= count($mediaDetails['solution_visual']) ?></span> · <?= e(tr('Poster', 'Poster')) ?>: <span data-review-media-count="poster"><?= count($mediaDetails['poster']) ?></span></p></section><section><h3><?= e(tr('Galeri Projek', 'Project Gallery')) ?></h3><p data-review-gallery-count><?= count($mediaDetails['gallery']) ?> / 10</p></section><section><h3><?= e(tr('Video Demo', 'Demo Video')) ?></h3><p data-review-video><?= e($mediaDetails['video_url'] ?: '—') ?></p></section><section><h3><?= e(tr('Perjalanan Projek', 'Project Journey')) ?></h3><ul data-review-journey><?php foreach ($mediaDetails['milestones'] as $milestone): ?><li><?= e(($milestone['title'] ?? '') . (($milestone['description'] ?? '') !== '' ? ' — ' . $milestone['description'] : '')) ?></li><?php endforeach; ?></ul></section></div>
    <label class="form-check"><input type="checkbox" name="consent" required><span><?= e(tr('Saya bersetuju maklumat ini disimpan sebagai submission dan disemak oleh admin HubInovasi.', 'I agree that this information may be stored as a submission and reviewed by the HubInovasi administrator.')) ?> <em>*</em></span></label>
    <button class="button button--primary submission-submit" type="submit" name="intent" value="submit_review"><?= e(tr('Hantar untuk Semakan', 'Submit for Review')) ?></button>
</fieldset>
