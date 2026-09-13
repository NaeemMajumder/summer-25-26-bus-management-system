<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">
    <h3 class="card-title"><?= $editing ? 'Edit Service Record' : 'Add Service Record' ?></h3>

    <form method="POST"
          action="index.php?page=manager&action=<?= $editing ? 'updateservice&id=' . (int) $editing['service_id'] : 'addservice' ?>">

        <?php csrf_field(); ?>

        <div class="form-row">
            <div class="form-group">
                <label for="bus_id">Bus</label>
                <?php $currentBus = $editing['bus_id'] ?? old('bus_id'); ?>
                <select id="bus_id" name="bus_id" required>
                    <option value="">— Select a bus —</option>
                    <?php foreach ($busList as $bus): ?>
                        <option value="<?= (int) $bus['bus_id'] ?>" <?= (string) $currentBus === (string) $bus['bus_id'] ? 'selected' : '' ?>>
                            <?= esc($bus['name']) ?> (<?= esc($bus['bus_number']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="service_date">Service Date</label>
                <input type="date" id="service_date" name="service_date" required
                       value="<?= esc($editing['service_date'] ?? old('service_date')) ?>">
            </div>

            <div class="form-group">
                <label for="cost">Cost (<?= esc(CURRENCY) ?>)</label>
                <input type="number" id="cost" name="cost" min="0" step="0.01" required
                       value="<?= esc($editing['cost'] ?? old('cost', '0')) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="work_done">Work Done</label>
            <textarea id="work_done" name="work_done" required><?= esc($editing['work_done'] ?? old('work_done')) ?></textarea>
        </div>

        <?php if (!$editing): ?>
            <!-- Parts used are locked in at creation time — not editable afterwards -->
            <div class="form-group">
                <label>Parts Used (optional, up to 3)</label>
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <div class="form-row" style="margin-bottom: 10px;">
                        <div class="form-group">
                            <select name="part_id[]">
                                <option value="">— No part —</option>
                                <?php foreach ($partList as $part): ?>
                                    <option value="<?= (int) $part['part_id'] ?>">
                                        <?= esc($part['part_name']) ?> (<?= esc($part['stock_quantity']) ?> in stock)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="number" name="part_qty[]" min="0" placeholder="Quantity used">
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <?= $editing ? 'Update' : 'Add Service Record' ?>
            </button>
            <?php if ($editing): ?>
                <a href="index.php?page=manager&action=services" class="btn btn-ghost">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card">
    <h3 class="card-title">Service History</h3>

    <form method="GET" action="index.php" class="search-form" style="margin-bottom: 20px;">
        <input type="hidden" name="page" value="manager">
        <input type="hidden" name="action" value="services">
        <div class="form-group">
            <input type="text" name="q" placeholder="Search by work done or bus number..." value="<?= esc($_GET['q'] ?? '') ?>">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-small">Search</button>
        </div>
    </form>

    <?php if (empty($serviceList)): ?>

        <div class="empty-box"><p>No service records yet.</p></div>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Bus</th>
                    <th>Date</th>
                    <th>Work Done</th>
                    <th>Parts Used</th>
                    <th>Cost</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($serviceList as $s): ?>
                    <tr>
                        <td><?= esc($s['bus_name']) ?> (<?= esc($s['bus_number']) ?>)</td>
                        <td><?= esc($s['service_date']) ?></td>
                        <td><?= esc($s['work_done']) ?></td>
                        <td>
                            <?php if (empty($s['parts_used'])): ?>
                                <span class="muted-text">—</span>
                            <?php else: ?>
                                <?php
                                $parts = array_map(function ($p) {
                                    return esc($p['part_name']) . ' x' . esc($p['quantity_used']);
                                }, $s['parts_used']);
                                echo implode(', ', $parts);
                                ?>
                            <?php endif; ?>
                        </td>
                        <td><?= esc(format_currency($s['cost'])) ?></td>
                        <td>
                            <a href="index.php?page=manager&action=services&edit=<?= (int) $s['service_id'] ?>" class="btn btn-small">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>