<?php require __DIR__ . '/../partials/header.php'; ?>

<?php
// find the biggest revenue to scale the bars (avoid divide-by-zero)
$maxRevenue = 0;
foreach ($revenueByRoute as $row) {
    if ((float) $row['revenue'] > $maxRevenue) {
        $maxRevenue = (float) $row['revenue'];
    }
}
?>

<!-- SUMMARY CARDS -->
<div class="summary-grid">
    <div class="summary-card">
        <p>Total Revenue</p>
        <h2 class="summary-total"><?= CURRENCY . number_format((float) $summary['total_revenue'], 2) ?></h2>
    </div>
    <div class="summary-card">
        <p>Paid Bookings</p>
        <h2 class="summary-confirmed"><?= (int) $summary['paid_count'] ?></h2>
    </div>
    <div class="summary-card">
        <p>Pending Amount</p>
        <h2 class="summary-pending"><?= CURRENCY . number_format((float) $summary['pending_amount'], 2) ?></h2>
    </div>
    <div class="summary-card">
        <p>Total Bookings</p>
        <h2><?= (int) $summary['total_bookings'] ?></h2>
    </div>
</div>


<!-- BAR CHART (pure CSS) -->
<section class="card">
    <h3 class="card-title">Revenue by Route</h3>

    <?php if (empty($revenueByRoute)): ?>

        <div class="empty-box"><p>No paid bookings yet — no revenue to show.</p></div>

    <?php else: ?>

        <div class="bar-chart">
            <?php foreach ($revenueByRoute as $row): ?>
                <?php
                    $rev = (float) $row['revenue'];
                    $pct = $maxRevenue > 0 ? round(($rev / $maxRevenue) * 100) : 0;
                ?>
                <div class="bar-row">
                    <div class="bar-label"><?= esc($row['origin']) ?> &rarr; <?= esc($row['destination']) ?></div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: <?= $pct ?>%;"></div>
                    </div>
                    <div class="bar-value"><?= CURRENCY . number_format($rev, 0) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</section>


<!-- TABLE -->
<?php if (!empty($revenueByRoute)): ?>
<section class="card">
    <h3 class="card-title">Breakdown</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Route</th>
                <th>Paid Bookings</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($revenueByRoute as $row): ?>
                <tr>
                    <td><?= esc($row['origin']) ?> &rarr; <?= esc($row['destination']) ?></td>
                    <td><?= (int) $row['bookings'] ?></td>
                    <td><?= CURRENCY . number_format((float) $row['revenue'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>