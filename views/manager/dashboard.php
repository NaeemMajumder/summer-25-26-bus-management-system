<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="summary-grid">
    <div class="summary-card">
        <p>Buses Needing Service</p>
        <h2 class="summary-cancelled"><?= count($busesNeedingService) ?></h2>
    </div>
    <div class="summary-card">
        <p>Open Requests</p>
        <h2 class="summary-pending"><?= count($openRequests) ?></h2>
    </div>
    <div class="summary-card">
        <p>Low Stock Parts</p>
        <h2 class="summary-cancelled"><?= count($lowStockParts) ?></h2>
    </div>
    <div class="summary-card">
        <p>Total Maintenance Cost</p>
        <h2 class="summary-total"><?= esc(format_currency($costReport['total_cost'])) ?></h2>
    </div>
</section>

<section class="card">
    <h3 class="card-title">Service-Due Alert</h3>

    <?php if (empty($busesNeedingService)): ?>
        <div class="empty-box"><p>No buses currently need servicing.</p></div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bus</th>
                    <th>Trips Since Service</th>
                    <th>Service Limit</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($busesNeedingService as $b): ?>
                    <tr>
                        <td><?= esc($b['name']) ?> (<?= esc($b['bus_number']) ?>)</td>
                        <td><?= esc($b['trips_since_service']) ?></td>
                        <td><?= esc($b['service_trip_limit']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section class="card">
    <h3 class="card-title">My Open Maintenance Requests</h3>

    <?php if (empty($openRequests)): ?>
        <div class="empty-box"><p>No open requests right now.</p></div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bus</th>
                    <th>Issue</th>
                    <th>Status</th>
                    <th>Reported</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($openRequests as $r): ?>
                    <tr>
                        <td><?= esc($r['bus_name']) ?> (<?= esc($r['bus_number']) ?>)</td>
                        <td><?= esc($r['issue']) ?></td>
                        <td><span class="pill pill-warning"><?= esc(ucwords(str_replace('_', ' ', $r['status']))) ?></span></td>
                        <td><?= esc(substr($r['created_at'], 0, 16)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="form-footnote"><a href="index.php?page=manager&action=requests">View all requests &rarr;</a></p>
    <?php endif; ?>
</section>

<section class="card">
    <h3 class="card-title">Low Spare-Part Stock Alert</h3>

    <?php if (empty($lowStockParts)): ?>
        <div class="empty-box"><p>All parts are sufficiently stocked.</p></div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Part</th>
                    <th>Stock</th>
                    <th>Unit Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lowStockParts as $p): ?>
                    <tr>
                        <td><?= esc($p['part_name']) ?></td>
                        <td><?= esc($p['stock_quantity']) ?> <span class="pill pill-danger">Low</span></td>
                        <td><?= esc(format_currency($p['unit_price'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="form-footnote"><a href="index.php?page=manager&action=parts">View all parts &rarr;</a></p>
    <?php endif; ?>
</section>

<section class="card">
    <h3 class="card-title">Maintenance Cost Report</h3>

    <div class="fare-box">
        <div class="fare-row"><span>Labor / Service Cost</span><span><?= esc(format_currency($costReport['labor_cost'])) ?></span></div>
        <div class="fare-row"><span>Parts Cost</span><span><?= esc(format_currency($costReport['parts_cost'])) ?></span></div>
        <div class="fare-row fare-total"><span>Total</span><span><?= esc(format_currency($costReport['total_cost'])) ?></span></div>
    </div>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>