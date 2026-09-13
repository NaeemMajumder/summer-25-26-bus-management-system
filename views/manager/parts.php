<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">
    <h3 class="card-title"><?= $editing ? 'Edit Part' : 'Add Spare Part' ?></h3>

    <form method="POST"
        action="index.php?page=manager&action=<?= $editing ? 'updatepart&id=' . (int) $editing['part_id'] : 'addpart' ?>">

        <?php csrf_field(); ?>

        <div class="form-row">
            <div class="form-group">
                <label for="part_name">Part Name</label>
                <input type="text" id="part_name" name="part_name" required
                    value="<?= esc($editing['part_name'] ?? old('part_name')) ?>">
            </div>

            <div class="form-group">
                <label for="stock_quantity">Stock Quantity</label>
                <input type="number" id="stock_quantity" name="stock_quantity" min="0" required
                    value="<?= esc($editing['stock_quantity'] ?? old('stock_quantity', '0')) ?>">
            </div>

            <div class="form-group">
                <label for="unit_price">Unit Price (<?= esc(CURRENCY) ?>)</label>
                <input type="number" id="unit_price" name="unit_price" min="0" step="0.01" required
                    value="<?= esc($editing['unit_price'] ?? old('unit_price', '0')) ?>">
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <?= $editing ? 'Update' : 'Add Part' ?>
            </button>
            <?php if ($editing): ?>
                <a href="index.php?page=manager&action=parts" class="btn btn-ghost">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card">
    <h3 class="card-title">All Spare Parts</h3>

    <form method="GET" action="index.php" class="search-form" style="margin-bottom: 20px;">
        <input type="hidden" name="page" value="manager">
        <input type="hidden" name="action" value="parts">
        <div class="form-group">
            <input type="text" name="q" placeholder="Search by part name..." value="<?= esc($_GET['q'] ?? '') ?>">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-small">Search</button>
        </div>
    </form>

    <?php if (empty($partList)): ?>

        <div class="empty-box">
            <p>No spare parts found.</p>
        </div>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Part Name</th>
                    <th>Stock</th>
                    <th>Unit Price</th>
                    <th>Last Updated</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($partList as $p): ?>
                    <tr>
                        <td><?= esc($p['part_name']) ?></td>
                        <td>
                            <?= esc($p['stock_quantity']) ?>
                            <?php if ((int) $p['stock_quantity'] <= PARTS_LOW_STOCK): ?>
                                <span class="pill pill-danger">Low</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc(format_currency($p['unit_price'])) ?></td>
                        <td><?= $p['updated_at'] ? esc(substr($p['updated_at'], 0, 16)) : '<span class="muted-text">—</span>' ?>
                        </td>
                        <td class="table-actions">
                            <a href="index.php?page=manager&action=parts&edit=<?= (int) $p['part_id'] ?>"
                                class="btn btn-small">Edit</a>

                            <form method="POST" action="index.php?page=manager&action=deletepart&id=<?= (int) $p['part_id'] ?>"
                                onsubmit="return confirm('Delete this part?');" style="display:inline;">
                                <?php csrf_field(); ?>
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>