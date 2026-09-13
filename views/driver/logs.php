<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">
    <h3 class="card-title">Trip Logs</h3>

    <?php if (empty($logList)): ?>

        <div class="empty-box">
            <p>No trips started yet — click "Start Trip" on the Dashboard to create an entry here.</p>
        </div>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Route</th>
                    <th>Trip Date</th>
                    <th>Started At</th>
                    <th>Ended At</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logList as $l): ?>
                    <tr>
                        <td><?= esc($l['origin']) ?> &rarr; <?= esc($l['destination']) ?></td>
                        <td><?= esc($l['trip_date']) ?></td>
                        <td><?= $l['start_time'] ? esc(substr($l['start_time'], 11, 5)) : '<span class="muted-text">—</span>' ?>
                        </td>
                        <td><?= $l['end_time'] ? esc(substr($l['end_time'], 11, 5)) : '<span class="muted-text">—</span>' ?>
                        </td>
                        <td>
                            <?php $cls = $l['status'] === 'completed' ? 'success' : 'warning'; ?>
                            <span class="pill pill-<?= $cls ?>"><?= esc(ucfirst($l['status'])) ?></span>
                        </td>
                        <td class="table-actions">
                            <?php if ($l['status'] === 'started'): ?>
                                <form method="POST" action="index.php?page=driver&action=updatelog&id=<?= (int) $l['log_id'] ?>"
                                    style="display:inline;">
                                    <?php csrf_field(); ?>
                                    <button type="submit" class="btn btn-small btn-primary">Complete Trip</button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" action="index.php?page=driver&action=deletelog&id=<?= (int) $l['log_id'] ?>"
                                onsubmit="return confirm('Delete this log entry?');" style="display:inline;">
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