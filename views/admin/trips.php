<?php require __DIR__ . '/../partials/header.php'; ?>

<!-- ADD / EDIT FORM -->
<section class="card">

    <h3 class="card-title"><?= $editTrip ? 'Edit Trip' : 'Schedule New Trip' ?></h3>

    <?php if (empty($allDrivers)): ?>
        <div class="alert alert-info">No drivers registered yet. A driver must register before you can schedule a trip.
        </div>
    <?php endif; ?>

    <form method="post" action="index.php?page=admin&action=<?= $editTrip ? 'updatetrip' : 'addtrip' ?>" novalidate>
        <?php csrf_field(); ?>

        <?php if ($editTrip): ?>
            <input type="hidden" name="trip_id" value="<?= (int) $editTrip['trip_id'] ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label for="bus_id">Bus</label>
                <select id="bus_id" name="bus_id">
                    <option value="">-- Select Bus --</option>
                    <?php foreach ($allBuses as $b): ?>
                        <option value="<?= (int) $b['bus_id'] ?>" <?= ($editTrip && $editTrip['bus_id'] == $b['bus_id']) ? 'selected' : '' ?>>
                            <?= esc($b['name'] . ' (' . $b['bus_number'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="route_id">Route</label>
                <select id="route_id" name="route_id">
                    <option value="">-- Select Route --</option>
                    <?php foreach ($allRoutes as $r): ?>
                        <option value="<?= (int) $r['route_id'] ?>" <?= ($editTrip && $editTrip['route_id'] == $r['route_id']) ? 'selected' : '' ?>>
                            <?= esc($r['origin'] . ' → ' . $r['destination']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="driver_id">Driver</label>
                <select id="driver_id" name="driver_id">
                    <option value="">-- Select Driver --</option>
                    <?php foreach ($allDrivers as $d): ?>
                        <option value="<?= (int) $d['user_id'] ?>" <?= ($editTrip && $editTrip['driver_id'] == $d['user_id']) ? 'selected' : '' ?>>
                            <?= esc($d['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="trip_date">Date</label>
                <input id="trip_date" name="trip_date" type="date" value="<?= esc($editTrip['trip_date'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="departure_time">Departure</label>
                <input id="departure_time" name="departure_time" type="time"
                    value="<?= esc($editTrip['departure_time'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="arrival_time">Arrival</label>
                <input id="arrival_time" name="arrival_time" type="time"
                    value="<?= esc($editTrip['arrival_time'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="fare">Fare (<?= CURRENCY ?>)</label>
                <input id="fare" name="fare" type="number" step="0.01" min="0"
                    value="<?= esc($editTrip['fare'] ?? '') ?>">
            </div>

            <?php if ($editTrip): ?>
                <div class="form-group">
                    <label for="available_seats">Available Seats</label>
                    <input id="available_seats" name="available_seats" type="number" min="0"
                        value="<?= esc($editTrip['available_seats'] ?? '') ?>">
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <?php
                    $st = $editTrip['status'] ?? 'scheduled';
                    foreach (['scheduled', 'running', 'completed', 'cancelled'] as $opt): ?>
                        <option value="<?= $opt ?>" <?= $st === $opt ? 'selected' : '' ?>><?= ucfirst($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (!$editTrip): ?>
            <p class="muted-text" style="margin-bottom:14px;">Available seats will be set from the bus's total seats
                automatically.</p>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary"><?= $editTrip ? 'Update Trip' : 'Schedule Trip' ?></button>

        <?php if ($editTrip): ?>
            <a href="index.php?page=admin&action=trips" class="btn btn-ghost">Cancel</a>
        <?php endif; ?>

    </form>
</section>


<!-- SEARCH + TABLE -->
<section class="card">

    <div class="form-row-inline">
        <h3 class="card-title" style="margin:0;">All Trips</h3>
        <form method="get" action="index.php" class="search-form-compact">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="action" value="trips">
            <input type="text" name="q" value="<?= esc($_GET['q'] ?? '') ?>" placeholder="Search bus, route, driver..."
                style="width:260px;">
        </form>
    </div>

    <?php if (empty($trips)): ?>

        <div class="empty-box">
            <p>No trips found.</p>
        </div>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Route</th>
                    <th>Bus</th>
                    <th>Driver</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Fare</th>
                    <th>Seats</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trips as $t): ?>
                    <tr>
                        <td><?= esc($t['origin']) ?> &rarr; <?= esc($t['destination']) ?></td>
                        <td><?= esc($t['bus_name']) ?></td>
                        <td><?= esc($t['driver_name']) ?></td>
                        <td><?= esc($t['trip_date']) ?></td>
                        <td><?= esc(substr($t['departure_time'], 0, 5)) ?></td>
                        <td><?= CURRENCY . esc($t['fare']) ?></td>
                        <td><?= (int) $t['available_seats'] ?></td>
                        <td>
                            <?php
                            $map = ['scheduled' => 'success', 'running' => 'warning', 'completed' => 'success', 'cancelled' => 'danger'];
                            $cls = $map[$t['status']] ?? 'success';
                            ?>
                            <span class="pill pill-<?= $cls ?>"><?= esc(ucfirst($t['status'])) ?></span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="index.php?page=admin&action=trips&edit=<?= (int) $t['trip_id'] ?>"
                                    class="btn btn-ghost btn-small">Edit</a>

                                <form method="post" action="index.php?page=admin&action=deletetrip"
                                    onsubmit="return confirm('Delete this trip?');" style="display:inline;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="trip_id" value="<?= (int) $t['trip_id'] ?>">
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