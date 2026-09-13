<?php require __DIR__ . '/../partials/header.php'; ?>

<!-- ADD / EDIT FORM -->
<section class="card card-narrow">

    <h3 class="card-title"><?= $editPromo ? 'Edit Promo Code' : 'Add New Promo Code' ?></h3>

    <form method="post" action="index.php?page=admin&action=<?= $editPromo ? 'updatepromo' : 'addpromo' ?>" novalidate>
        <?php csrf_field(); ?>

        <?php if ($editPromo): ?>
            <input type="hidden" name="promo_id" value="<?= (int) $editPromo['promo_id'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="code">Promo Code</label>
            <input id="code" name="code" type="text" value="<?= esc($editPromo['code'] ?? '') ?>"
                placeholder="e.g. EID2026">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="discount_type">Discount Type</label>
                <select id="discount_type" name="discount_type">
                    <?php $dt = $editPromo['discount_type'] ?? ''; ?>
                    <option value="percent" <?= $dt === 'percent' ? 'selected' : '' ?>>Percent (%)</option>
                    <option value="flat" <?= $dt === 'flat' ? 'selected' : '' ?>>Flat (৳)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="discount_value">Value</label>
                <input id="discount_value" name="discount_value" type="number" step="0.01" min="0"
                    value="<?= esc($editPromo['discount_value'] ?? '') ?>" placeholder="e.g. 10">
            </div>
        </div>

        <div class="form-group">
            <label for="expiry_date">Expiry Date <span class="muted-text">(optional)</span></label>
            <input id="expiry_date" name="expiry_date" type="date" value="<?= esc($editPromo['expiry_date'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="checkbox-inline">
                <input type="checkbox" name="is_active" value="1" <?= (!$editPromo || $editPromo['is_active']) ? 'checked' : '' ?>>
                Active
            </label>
        </div>

        <button type="submit" class="btn btn-primary"><?= $editPromo ? 'Update Promo' : 'Add Promo' ?></button>

        <?php if ($editPromo): ?>
            <a href="index.php?page=admin&action=promos" class="btn btn-ghost">Cancel</a>
        <?php endif; ?>

    </form>
</section>


<!-- SEARCH + TABLE -->
<section class="card">

    <div class="form-row-inline">
        <h3 class="card-title" style="margin:0;">All Promo Codes</h3>
        <form method="get" action="index.php" class="search-form-compact">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="action" value="promos">
            <input type="text" name="q" value="<?= esc($_GET['q'] ?? '') ?>" placeholder="Search code or type..."
                style="width:260px;">
        </form>
    </div>

    <?php if (empty($promos)): ?>

        <div class="empty-box">
            <p>No promo codes found.</p>
        </div>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promos as $p): ?>
                    <tr>
                        <td><strong><?= esc($p['code']) ?></strong></td>
                        <td><?= esc(ucfirst($p['discount_type'])) ?></td>
                        <td>
                            <?= $p['discount_type'] === 'percent'
                                ? esc($p['discount_value']) . '%'
                                : CURRENCY . esc($p['discount_value']) ?>
                        </td>
                        <td><?= $p['expiry_date'] ? esc($p['expiry_date']) : '<span class="muted-text">No expiry</span>' ?></td>
                        <td>
                            <span class="pill pill-<?= $p['is_active'] ? 'success' : 'danger' ?>">
                                <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="index.php?page=admin&action=promos&edit=<?= (int) $p['promo_id'] ?>"
                                    class="btn btn-ghost btn-small">Edit</a>

                                <form method="post" action="index.php?page=admin&action=deletepromo"
                                    onsubmit="return confirm('Delete this promo code?');" style="display:inline;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="promo_id" value="<?= (int) $p['promo_id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>