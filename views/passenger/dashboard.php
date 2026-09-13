<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="hero">
    <div>
        <h1>Your journey starts here</h1>
        <p>Book bus tickets quickly with <?= esc(APP_NAME) ?>.</p>
    </div>
</section>


<section class="card">

    <h3 class="card-title">Search Buses</h3>

    <form class="search-form" action="index.php" method="get">

        <input type="hidden" name="page" value="passenger">
        <input type="hidden" name="action" value="search">

        <div class="form-group">
            <label for="homeFrom">From</label>
            <select name="from" id="homeFrom" required>
                <option value="">Select starting point</option>
                <?php foreach ($origins as $origin): ?>
                    <option value="<?= esc($origin) ?>"><?= esc($origin) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="homeTo">To</label>
            <select name="to" id="homeTo" required>
                <option value="">Select destination</option>
                <?php foreach ($destinations as $destination): ?>
                    <option value="<?= esc($destination) ?>"><?= esc($destination) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="homeDate">Journey Date</label>
            <input type="date" name="date" id="homeDate" min="<?= date('Y-m-d') ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Search Bus</button>

    </form>

</section>


<?php if (is_logged_in() && $summary): ?>

    <section class="summary-grid">

        <div class="summary-card">
            <p>Confirmed Bookings</p>
            <h2><?= esc($summary['confirmed_bookings'] ?? 0) ?></h2>
        </div>

        <div class="summary-card">
            <p>Pending Payments</p>
            <h2 class="summary-pending"><?= esc($summary['pending_payments'] ?? 0) ?></h2>
        </div>

        <div class="summary-card">
            <p>Cancelled Bookings</p>
            <h2 class="summary-cancelled"><?= esc($summary['cancelled_bookings'] ?? 0) ?></h2>
        </div>

        <div class="summary-card">
            <p>Total Trips Booked</p>
            <h2 class="summary-total"><?= esc($summary['total_trips'] ?? 0) ?></h2>
        </div>

    </section>

<?php endif; ?>


<section class="card">

    <h3 class="card-title">Popular Routes</h3>

    <?php if (empty($popularRoutes)): ?>

        <p class="muted-text">No scheduled trips yet — check back soon.</p>

    <?php else: ?>

        <?php foreach ($popularRoutes as $route): ?>

            <div class="bus-card">
                <div>
                    <h4><?= esc($route['origin']) ?> &rarr; <?= esc($route['destination']) ?></h4>
                    <p>Fares from <?= esc(format_currency($route['min_fare'])) ?></p>
                </div>

                <a href="index.php?page=passenger&action=search&from=<?= urlencode($route['origin']) ?>&to=<?= urlencode($route['destination']) ?>" class="btn btn-primary">
                    View Trips
                </a>
            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</section>


<section class="card">

    <h3 class="card-title">Top Rated Buses</h3>

    <?php if (empty($topRatedBuses)): ?>

        <p class="muted-text">No ratings yet — be the first to leave a review after your trip.</p>

    <?php else: ?>

        <div class="rating-grid">

            <?php foreach ($topRatedBuses as $bus): ?>

                <div class="rating-card">
                    <h4><?= esc($bus['bus_name']) ?></h4>
                    <p class="muted-text"><?= esc($bus['bus_type']) ?> &middot; <?= esc($bus['bus_number']) ?></p>
                    <?= star_rating_html($bus['avg_rating'], $bus['review_count']) ?>
                </div>

            <?php endforeach; ?>

        </div>

        <p class="form-footnote"><a href="index.php?page=passenger&action=busratings">See all bus ratings &rarr;</a></p>

    <?php endif; ?>

</section>


<?php if (is_logged_in()): ?>

    <section class="card">

        <h3 class="card-title">Recent Tickets</h3>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Route</th>
                    <th>Bus</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>

                <?php if (empty($recentTickets)): ?>

                    <tr>
                        <td colspan="3">No recent tickets found. <a href="index.php?page=passenger&action=search">Search a bus</a> to get started.</td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($recentTickets as $ticket): ?>

                        <tr>
                            <td><?= esc($ticket['origin']) ?> &rarr; <?= esc($ticket['destination']) ?></td>
                            <td><?= esc($ticket['bus_name']) ?></td>
                            <td>
                                <span class="pill <?= $ticket['booking_status'] === 'confirmed' ? 'pill-success' : 'pill-danger' ?>">
                                    <?= esc(booking_status_label($ticket['booking_status'])) ?>
                                </span>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>
        </table>

    </section>

<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
