<?php require __DIR__ . '/../partials/header.php'; ?>

<?php
// Turn existing stops into textarea text (one per line) when editing
$stopsText = '';
if (!empty($editStops)) {
    $stopsText = implode("\n", array_map(fn($s) => $s['stop_name'], $editStops));
}
?>

<!-- ADD / EDIT FORM -->
<section class="card card-narrow">

    <h3 class="card-title"><?= $editRoute ? 'Edit Route' : 'Add New Route' ?></h3>

    <form method="post"
          action="index.php?page=admin&action=<?= $editRoute ? 'updateroute' : 'addroute' ?>"
          novalidate>
        <?php csrf_field(); ?>

        <?php if ($editRoute): ?>
            <input type="hidden" name="route_id" value="<?= (int) $editRoute['route_id'] ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label for="origin">From (Origin)</label>
                <input id="origin" name="origin" type="text"
                       value="<?= esc($editRoute['origin'] ?? '') ?>" placeholder="e.g. Dhaka">
            </div>
            <div class="form-group">
                <label for="destination">To (Destination)</label>
                <input id="destination" name="destination" type="text"
                       value="<?= esc($editRoute['destination'] ?? '') ?>" placeholder="e.g. Sylhet">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="distance_km">Distance (km)</label>
                <input id="distance_km" name="distance_km" type="number" step="0.01" min="0"
                       value="<?= esc($editRoute['distance_km'] ?? '') ?>" placeholder="e.g. 240">
            </div>
            <div class="form-group">
                <label for="duration">Duration</label>
                <input id="duration" name="duration" type="text"
                       value="<?= esc($editRoute['duration'] ?? '') ?>" placeholder="e.g. 6 Hours">
            </div>
        </div>

        <div class="form-group">
            <label for="stops">Stops / Counters <span class="muted-text">(one per line, in order)</span></label>
            <textarea id="stops" name="stops" placeholder="Dhaka Counter&#10;Bhairab Counter&#10;Sylhet Counter"><?= esc($stopsText) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary"><?= $editRoute ? 'Update Route' : 'Add Route' ?></button>

        <?php if ($editRoute): ?>
            <a href="index.php?page=admin&action=routes" class="btn btn-ghost">Cancel</a>
        <?php endif; ?>

    </form>
</section>


<!-- SEARCH + TABLE -->
<section class="card">

    <div class="form-row-inline">
        <h3 class="card-title" style="margin:0;">All Routes</h3>
        <form method="get" action="index.php" class="search-form-compact">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="action" value="routes">
            <input type="text" name="q" value="<?= esc($_GET['q'] ?? '') ?>"
                   placeholder="Search origin or destination..." style="width:260px;">
        </form>
    </div>

    <?php if (empty($routes)): ?>

        <div class="empty-box"><p>No routes found.</p></div>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Route</th>
                    <th>Distance</th>
                    <th>Duration</th>
                    <th>Stops</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routes as $r): ?>
                    <?php $rStops = get_route_stops($conn, $r['route_id']); ?>
                    <tr>
                        <td><?= esc($r['origin']) ?> &rarr; <?= esc($r['destination']) ?></td>
                        <td><?= esc($r['distance_km']) ?> km</td>
                        <td><?= esc($r['duration']) ?></td>
                        <td>
                            <?php if (empty($rStops)): ?>
                                <span class="muted-text">No stops</span>
                            <?php else: ?>
                                <?= esc(implode(' · ', array_map(fn($s) => $s['stop_name'], $rStops))) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="index.php?page=admin&action=routes&edit=<?= (int) $r['route_id'] ?>"
                                   class="btn btn-ghost btn-small">Edit</a>

                                <form method="post" action="index.php?page=admin&action=deleteroute"
                                      onsubmit="return confirm('Delete this route and its stops?');" style="display:inline;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="route_id" value="<?= (int) $r['route_id'] ?>">
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