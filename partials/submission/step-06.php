<fieldset class="form-section submission-step" data-submission-step="6" hidden>
    <legend><span>06</span> <?= e(tr('Mentor', 'Mentors')) ?></legend>
    <div class="cp10b-notice" role="note"><strong><?= e(tr('Akan dilengkapkan pada CP10B', 'To be completed in CP10B')) ?></strong><p><?= e(tr('Shell mentor ini belum menyimpan rekod atau fail.', 'This mentor shell does not yet save records or files.')) ?></p></div>
    <div class="person-shell">
        <div class="person-shell__photo" aria-hidden="true">+</div>
        <div>
            <div class="form-row"><label class="form-field"><span><?= e(tr('Nama mentor', 'Mentor name')) ?></span><input type="text" disabled></label><label class="form-field"><span><?= e(tr('Jawatan', 'Position')) ?></span><input type="text" disabled></label></div>
            <label class="form-field"><span><?= e(tr('Gambar profil', 'Profile photo')) ?></span><input type="file" accept="image/jpeg,image/png,image/webp" disabled><small><?= e(tr('JPG, PNG atau WebP. Upload akan diaktifkan pada CP10B.', 'JPG, PNG or WebP. Upload will be enabled in CP10B.')) ?></small></label>
        </div>
    </div>
    <button class="button button--secondary" type="button" disabled><?= e(tr('Tambah mentor', 'Add mentor')) ?></button>
</fieldset>
