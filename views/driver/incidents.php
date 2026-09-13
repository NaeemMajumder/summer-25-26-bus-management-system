<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">
    <h3 class="card-title"><?= $editing ? 'Edit Report' : 'Report Incident / Damage' ?></h3>

    <form method="POST"
        action="index.php?page=driver&action=<?= $editing ? 'updateincident&id=' . (int) $editing['incident_id'] : 'addincident' ?>">

        <?php csrf_field(); ?>

        <div class="form-row">
            <div class="form-group">
                <label for="trip_id">Related Trip (optional)</label>
                <?php $currentTrip = $editing['trip_id'] ?? old('trip_id'); ?>
                <select id="trip_id" name="trip_id">
                    <option value="">— General / Not trip-specific —</option>
                    <?php foreach ($myTrips as $trip): ?>
                        <option value="<?= (int) $trip['trip_id'] ?>" <?= (string) $currentTrip === (string) $trip['trip_id'] ? 'selected' : '' ?>>
                            <?= esc($trip['trip_date']) ?> — <?= esc($trip['origin']) ?> &rarr;
                            <?= esc($trip['destination']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="type">Type</label>
                <?php $currentType = $editing['type'] ?? old('type'); ?>
                <select id="type" name="type" required>
                    <option value="incident" <?= $currentType === 'incident' ? 'selected' : '' ?>>Incident</option>
                    <option value="damage" <?= $currentType === 'damage' ? 'selected' : '' ?>>Damage</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"
                required><?= esc($editing['description'] ?? old('description')) ?></textarea>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <?= $editing ? 'Update' : 'Submit Report' ?>
            </button>
            <?php if ($editing): ?>
                <a href="index.php?page=driver&action=incidents" class="btn btn-ghost">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card">
    <h3 class="card-title">My Reports</h3>

    <?php if (empty($incidentList)): ?>

        <div class="empty-box">
            <p>No reports submitted yet.</p>
        </div>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Trip</th>
                    <th>Bus</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incidentList as $i): ?>
                    <tr>
                        <td><?= esc(substr($i['created_at'], 0, 10)) ?></td>
                        <td>
                            <?php if ($i['trip_id']): ?>
                                <?= esc($i['origin']) ?> &rarr; <?= esc($i['destination']) ?>
                            <?php else: ?>
                                <span class="muted-text">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $i['bus_number'] ? esc($i['bus_number']) : '<span class="muted-text">—</span>' ?></td>
                        <td><?= esc(ucfirst($i['type'])) ?></td>
                        <td><?= esc($i['description']) ?></td>
                        <td>
                            <?php
                            $map = ['open' => 'danger', 'reviewing' => 'warning', 'resolved' => 'success'];
                            $cls = $map[$i['status']] ?? 'success';
                            ?>
                            <span class="pill pill-<?= $cls ?>"><?= esc(ucfirst($i['status'])) ?></span>
                        </td>
                        <td class="table-actions">
                            <a href="index.php?page=driver&action=incidents&edit=<?= (int) $i['incident_id'] ?>"
                                class="btn btn-small">Edit</a>

                            <form method="POST"
                                action="index.php?page=driver&action=deleteincident&id=<?= (int) $i['incident_id'] ?>"
                                onsubmit="return confirm('Delete this report?');" style="display:inline;">
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