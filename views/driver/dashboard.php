<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">
    <h3 class="card-title">Assigned Trips</h3>

    <?php if (empty($trips)): ?>

        <div class="empty-box">
            <p>No trips assigned to you yet. The admin will assign trips to you.</p>
        </div>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Route</th>
                    <th>Bus</th>
                    <th>Date</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                    <th>Fare</th>
                    <th>Seats Left</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trips as $t): ?>
                    <tr>
                        <td><?= esc($t['origin']) ?> &rarr; <?= esc($t['destination']) ?></td>
                        <td><?= esc($t['bus_name']) ?> (<?= esc($t['bus_number']) ?>)</td>
                        <td><?= esc($t['trip_date']) ?></td>
                        <td><?= esc(substr($t['departure_time'], 0, 5)) ?></td>
                        <td><?= esc(substr($t['arrival_time'], 0, 5)) ?></td>
                        <td><?= esc(format_currency($t['fare'])) ?></td>
                        <td><?= esc($t['available_seats']) ?></td>
                        <td>
                            <?php
                            $map = ['scheduled' => 'success', 'running' => 'warning', 'completed' => 'success', 'cancelled' => 'danger'];
                            $cls = $map[$t['status']] ?? 'success';
                            ?>
                            <span class="pill pill-<?= $cls ?>"><?= esc(ucfirst($t['status'])) ?></span>
                        </td>
                        <td>
                            <?php if ($t['status'] === 'scheduled'): ?>
                                <form method="POST" action="index.php?page=driver&action=startlog" style="display:inline;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="trip_id" value="<?= (int) $t['trip_id'] ?>">
                                    <button type="submit" class="btn btn-small btn-primary">Start Trip</button>
                                </form>
                            <?php elseif ($t['status'] === 'running'): ?>
                                <a href="index.php?page=driver&action=logs" class="btn btn-small">Go to Logs</a>
                            <?php else: ?>
                                <span class="muted-text">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>