<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">

    <h3 class="card-title">Ticket History</h3>

    <form method="get" action="index.php" class="search-form search-form-compact">
        <input type="hidden" name="page" value="passenger">
        <input type="hidden" name="action" value="mytickets">

        <div class="form-group">
            <label for="ticketSearch">Search</label>
            <input type="text" id="ticketSearch" name="q" value="<?= esc($search) ?>" placeholder="Route or bus name">
        </div>

        <div class="form-group">
            <label for="statusFilter">Status</label>
            <select id="statusFilter" name="status">
                <option value="">All</option>
                <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="index.php?page=passenger&action=mytickets" class="btn btn-ghost">Reset</a>
    </form>

</section>


<section class="card">

    <?php if (empty($tickets)): ?>

        <div class="empty-box">
            <p>No tickets found.</p>
            <a href="index.php?page=passenger&action=search" class="btn btn-primary">Search Bus</a>
        </div>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Bus</th>
                    <th>Route</th>
                    <th>Date</th>
                    <th>Departure</th>
                    <th>Seats</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($tickets as $ticket): ?>

                    <tr>
                        <td><?= esc($ticket['bus_name']) ?></td>
                        <td><?= esc($ticket['origin']) ?> &rarr; <?= esc($ticket['destination']) ?></td>
                        <td><?= esc(date('d M Y', strtotime($ticket['trip_date']))) ?></td>
                        <td><?= esc(date('h:i A', strtotime($ticket['departure_time']))) ?></td>
                        <td><?= esc($ticket['seats_booked']) ?></td>
                        <td><?= esc(format_currency($ticket['total_amount'])) ?></td>
                        <td>
                            <span class="pill <?= $ticket['payment_status'] === 'paid' ? 'pill-success' : 'pill-warning' ?>">
                                <?= esc(payment_status_label($ticket['payment_status'])) ?>
                            </span>
                        </td>
                        <td>

                            <?php if ($ticket['trip_status'] === 'completed'): ?>

                                <span class="pill pill-success">
                                    Completed
                                </span>

                            <?php elseif ($ticket['booking_status'] === 'confirmed'): ?>

                                <span class="pill pill-success">
                                    Confirmed
                                </span>

                            <?php else: ?>

                                <span class="pill pill-danger">
                                    Cancelled
                                </span>

                            <?php endif; ?>

                        </td>
                        <td class="table-actions">
                            <?php if ($ticket['trip_status'] === 'completed'): ?>

                                <a href="index.php?page=passenger&action=reviews&id=<?= (int) $ticket['booking_id'] ?>"
                                    class="btn btn-small">

                                    Give Review

                                </a>

                            <?php endif; ?>

                            <a href="index.php?page=passenger&action=viewticket&id=<?= (int) $ticket['booking_id'] ?>"
                                class="btn btn-small">View</a>

                            <?php if ($ticket['booking_status'] === 'confirmed' && $ticket['trip_status'] === 'scheduled'): ?>

                                <a href="index.php?page=passenger&action=modify&id=<?= (int) $ticket['booking_id'] ?>"
                                    class="btn btn-small">Modify</a>

                                <form method="post" action="index.php?page=passenger&action=cancel"
                                    onsubmit="return confirm('Cancel this ticket?');" style="display:inline;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?= (int) $ticket['booking_id'] ?>">
                                    <button type="submit" class="btn btn-small btn-danger">Cancel</button>
                                </form>

                            <?php endif; ?>

                        </td>
                    </tr>

                <?php endforeach; ?>

            </tbody>
        </table>

    <?php endif; ?>

</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>