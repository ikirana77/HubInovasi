<fieldset class="form-section submission-step" data-submission-step="1">
    <legend><span>01</span> <?= e(tr('Identiti Projek', 'Project Identity')) ?></legend>
    <p class="submission-step__intro"><?= e(tr('Mulakan dengan maklumat asas yang membantu HubInovasi mengenal pasti projek dan pemiliknya.', 'Start with the core information HubInovasi uses to identify the project and its owner.')) ?></p>
    <div class="form-row">
        <label class="form-field"><span><?= e(tr('Nama penuh', 'Full name')) ?> <em>*</em></span><input type="text" name="name" value="<?= e($defaultName) ?>" autocomplete="name" required <?= $user ? 'readonly' : '' ?>></label>
        <label class="form-field"><span>Email <em>*</em></span><input type="email" name="email" value="<?= e($defaultEmail) ?>" autocomplete="email" required <?= $user ? 'readonly' : '' ?>></label>
    </div>
    <div class="form-row">
        <label class="form-field"><span><?= e(tr('Nama projek', 'Project name')) ?> <em>*</em></span><input type="text" name="project_name" value="<?= submission_value($submission, 'project_name') ?>" required maxlength="80"></label>
        <label class="form-field"><span><?= e(tr('Institusi / kolej', 'Institution / college')) ?></span><input type="text" name="institution" value="<?= e($defaultInstitution) ?>"></label>
    </div>
    <div class="form-row">
        <label class="form-field"><span><?= e(tr('Bidang penyelesaian utama', 'Primary solution area')) ?> <em>*</em></span><select name="category" required><option value=""><?= e(tr('Pilih bidang', 'Select an area')) ?></option><?php foreach ($areaOptions as $value => $label): ?><option value="<?= e($value) ?>" <?= $selectedArea === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
        <label class="form-field"><span><?= e(tr('Tahap pembangunan', 'Development stage')) ?> <em>*</em></span><select name="development_status" required><option value=""><?= e(tr('Pilih tahap', 'Select a stage')) ?></option><?php foreach ($developmentOptions as $developmentStatus): ?><option value="<?= e($developmentStatus) ?>" <?= ($submission['project_development_status'] ?? '') === $developmentStatus ? 'selected' : '' ?>><?= e($developmentStatus) ?></option><?php endforeach; ?></select></label>
    </div>
    <div class="form-row">
        <label class="form-field"><span><?= e(tr('Jenis inovasi', 'Innovation type')) ?> <em>*</em></span><select name="innovation_type" required><option value=""><?= e(tr('Pilih jenis inovasi', 'Select an innovation type')) ?></option><?php foreach ($innovationOptions as $value => $label): ?><option value="<?= e($value) ?>" <?= ($submission['innovation_type'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
    </div>
    <fieldset class="programme-selector">
        <legend><?= e(tr('Program projek', 'Project programmes')) ?> <em>*</em></legend>
        <label class="form-field"><span><?= e(tr('Program peneraju', 'Lead programme')) ?> <em>*</em></span><select name="lead_programme" required><option value=""><?= e(tr('Pilih program peneraju', 'Select the lead programme')) ?></option><?php foreach ($programmeOptions as $code => $label): ?><option value="<?= e($code) ?>" <?= $selectedLeadProgramme === $code ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
        <p><?= e(tr('Jika projek dibangunkan secara kolaboratif, pilih program penyumbang tambahan.', 'For a collaborative project, select any additional contributing programmes.')) ?></p>
        <div><?php foreach ($programmeOptions as $code => $label): ?><label><input type="checkbox" name="programmes[]" value="<?= e($code) ?>" <?= in_array($code, $selectedContributingProgrammes, true) ? 'checked' : '' ?>><span><strong><?= e($code) ?></strong><?= e(preg_replace('/^[A-Z]{3} — /', '', $label)) ?></span></label><?php endforeach; ?></div>
    </fieldset>
</fieldset>
