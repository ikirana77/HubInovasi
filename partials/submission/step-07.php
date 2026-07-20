<fieldset class="form-section submission-step" data-submission-step="7" hidden>
    <legend><span>07</span> <?= e(tr('Media, Galeri & Perjalanan', 'Media, Gallery & Journey')) ?></legend>
    <div class="cp10b-notice" role="note"><strong><?= e(tr('Akan dilengkapkan pada CP10F', 'To be completed in CP10F')) ?></strong><p><?= e(tr('Galeri, pautan dan milestone berulang belum diaktifkan untuk simpanan.', 'Gallery items, links and repeatable milestones are not yet enabled for persistence.')) ?></p></div>
    <label class="form-field"><span><?= e(tr('Media projek', 'Project media')) ?></span><input type="file" accept="image/*,video/*" multiple disabled></label>
    <label class="form-field"><span><?= e(tr('Pautan demo / video', 'Demo / video link')) ?></span><input type="url" disabled placeholder="https://"></label>
    <label class="form-field"><span><?= e(tr('Milestone perjalanan', 'Journey milestone')) ?></span><textarea rows="3" disabled></textarea></label>
</fieldset>
