<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">Edit: <?= e($person['name']) ?></h1>
        <a href="<?= url('admin/outreach/people') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Back</a>
    </div>

    <div class="admin-card" style="max-width:780px;">
        <div class="admin-card-body">
            <form method="POST" action="<?= url('admin/outreach/people/' . $person['id'] . '/edit') ?>">
                <?= csrf_field() ?>

                <h3 style="color:#5C3A21;border-bottom:1px solid #e5e0d5;padding-bottom:.5rem;margin:0 0 1.2rem;">Personal Details</h3>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label required">Full Name</label>
                        <input type="text" name="name" class="form-input" value="<?= e(old('name', $person['name'])) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Title / Role</label>
                        <input type="text" name="title" class="form-input" value="<?= e(old('title', $person['title'] ?? '')) ?>">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label required">Category</label>
                        <select name="category" class="form-select" required>
                            <?php foreach ($categories as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= old('category', $person['category']) === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Organization / Institution</label>
                        <input type="text" name="organization" class="form-input" value="<?= e(old('organization', $person['organization'] ?? '')) ?>">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" value="<?= e(old('email', $person['email'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-input" value="<?= e(old('phone', $person['phone'] ?? '')) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Website / Social Media</label>
                    <input type="url" name="website" class="form-input" value="<?= e(old('website', $person['website'] ?? '')) ?>">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-input" value="<?= e(old('country', $person['country'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">State / Region</label>
                        <input type="text" name="state_region" class="form-input" value="<?= e(old('state_region', $person['state_region'] ?? '')) ?>">
                    </div>
                </div>

                <h3 style="color:#5C3A21;border-bottom:1px solid #e5e0d5;padding-bottom:.5rem;margin:1.5rem 0 1.2rem;">Contact & Engagement</h3>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Contact Status</label>
                        <select name="contact_status" class="form-select">
                            <?php foreach ($contactStatuses as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= old('contact_status', $person['contact_status']) === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Consent / Opt-in</label>
                        <select name="consent_status" class="form-select">
                            <?php foreach ($consentStatuses as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= old('consent_status', $person['consent_status']) === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Response Status</label>
                        <select name="response_status" class="form-select">
                            <?php foreach ($responseStatuses as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= old('response_status', $person['response_status']) === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Last Contact Date</label>
                        <input type="date" name="last_contact_date" class="form-input" value="<?= e(old('last_contact_date', $person['last_contact_date'] ?? '')) ?>">
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:.3rem;">
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                            <input type="checkbox" name="is_unsubscribed" value="1" <?= (old('is_unsubscribed') || $person['is_unsubscribed']) ? 'checked' : '' ?>>
                            <span style="font-size:.88rem;color:#5a4a3a;">Mark as Unsubscribed</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-textarea" rows="3"><?= e(old('notes', $person['notes'] ?? '')) ?></textarea>
                </div>

                <h3 style="color:#5C3A21;border-bottom:1px solid #e5e0d5;padding-bottom:.5rem;margin:1.5rem 0 1.2rem;">Source & Compliance</h3>

                <div class="form-group">
                    <label class="form-label required">Source Notes</label>
                    <textarea name="source_notes" class="form-textarea" rows="3" required placeholder="Describe where this person's information was found…"><?= e(old('source_notes', $person['source_notes'] ?? '')) ?></textarea>
                    <small style="color:#8a7a6a;font-size:.8rem;">Required for compliance. Record where you found this person's contact information.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Source URL <span style="color:#8a7a6a;font-weight:400;">(optional)</span></label>
                    <input type="url" name="source_url" class="form-input" value="<?= e(old('source_url', $person['source_url'] ?? '')) ?>" placeholder="https://…">
                </div>

                <div class="form-group">
                    <label class="form-label">Legal Basis for Contact</label>
                    <select name="gdpr_basis" class="form-select">
                        <?php foreach ($gdprBases as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= old('gdpr_basis', $person['gdpr_basis'] ?? 'not_set') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:#8a7a6a;font-size:.8rem;">The legal basis under GDPR/NDPR for holding this person's data.</small>
                </div>

                <?php if ($person['consent_date']): ?>
                <div style="background:#f0faf4;border:1px solid #c3e6cb;border-radius:6px;padding:.75rem 1rem;font-size:.83rem;color:#2d6a4f;margin-bottom:.5rem;">
                    &#10003; Consent recorded on <strong><?= date('M j, Y', strtotime($person['consent_date'])) ?></strong>
                </div>
                <?php endif; ?>

                <!-- Tracking info (read-only) -->
                <div style="background:#f7f4ee;border-radius:6px;padding:1rem;font-size:.83rem;color:#7a6a5a;margin-top:1rem;">
                    &#8505; Emails sent: <strong><?= (int)$person['emails_sent'] ?></strong>
                    &nbsp;&middot;&nbsp; Added: <strong><?= date('M j, Y', strtotime($person['created_at'])) ?></strong>
                    <?php if ($person['last_contact_date']): ?>
                        &nbsp;&middot;&nbsp; Last contact: <strong><?= date('M j, Y', strtotime($person['last_contact_date'])) ?></strong>
                    <?php endif; ?>
                </div>

                <div style="display:flex;gap:.7rem;margin-top:1.2rem;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="<?= url('admin/outreach/people') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
</div>
