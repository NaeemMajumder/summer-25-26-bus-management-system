<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card ticket-card" id="printableTicket">

    <div class="ticket-head">
        <div>
            <h2><?= esc(APP_NAME) ?> E-Ticket</h2>
            <p class="muted-text">Booking Reference</p>
        </div>
        <div class="ticket-code-box">
            <?= esc(ticket_code($booking['booking_id'])) ?>
        </div>
    </div>

    <div class="ticket-body">

        <div class="ticket-row">
            <span>Bus</span>
            <strong><?= esc($booking['bus_name']) ?> (<?= esc($booking['bus_number']) ?>) &middot; <?= esc($booking['bus_type']) ?></strong>
        </div>

        <div class="ticket-row">
            <span>Route</span>
            <strong><?= esc($booking['origin']) ?> &rarr; <?= esc($booking['destination']) ?></strong>
        </div>

        <div class="ticket-row">
            <span>Date &amp; Time</span>
            <strong><?= esc(date('d M Y', strtotime($booking['trip_date']))) ?> &middot; <?= esc(date('h:i A', strtotime($booking['departure_time']))) ?></strong>
        </div>

        <div class="ticket-row">
            <span>Boarding &rarr; Dropping</span>
            <strong><?= esc($booking['boarding_name'] ?? '-') ?> &rarr; <?= esc($booking['dropping_name'] ?? '-') ?></strong>
        </div>

        <div class="ticket-row">
            <span>Seats</span>
            <strong><?= (int) $booking['seats_booked'] ?></strong>
        </div>

        <?php if ($booking['wheelchair']): ?>
            <div class="ticket-row">
                <span>Special Request</span>
                <strong>&#9855; Wheelchair support</strong>
            </div>
        <?php endif; ?>

        <?php if (!empty($booking['promo_code'])): ?>
            <div class="ticket-row">
                <span>Promo Applied</span>
                <strong><?= esc($booking['promo_code']) ?></strong>
            </div>
        <?php endif; ?>

        <div class="ticket-row">
            <span>Total Fare</span>
            <strong><?= esc(format_currency($booking['total_amount'])) ?></strong>
        </div>

        <div class="ticket-row">
            <span>Payment</span>
            <strong>
                <?= esc(payment_method_label($booking['payment_method'])) ?> &middot;
                <span class="pill <?= $booking['payment_status'] === 'paid' ? 'pill-success' : 'pill-warning' ?>">
                    <?= esc(payment_status_label($booking['payment_status'])) ?>
                </span>
            </strong>
        </div>

        <div class="ticket-row">
            <span>Status</span>
            <strong>
                <span class="pill <?= $booking['booking_status'] === 'confirmed' ? 'pill-success' : 'pill-danger' ?>">
                    <?= esc(booking_status_label($booking['booking_status'])) ?>
                </span>
            </strong>
        </div>

    </div>

</section>


<section class="card no-print">

    <div class="ticket-actions">

        <button type="button" class="btn btn-ghost" onclick="window.print()">&#128424; Print / Download</button>

        <?php if ($booking['booking_status'] === 'confirmed' && $booking['trip_status'] === 'scheduled'): ?>

            <a href="index.php?page=passenger&action=modify&id=<?= (int) $booking['booking_id'] ?>" class="btn btn-ghost">Modify Booking</a>

            <form method="post" action="index.php?page=passenger&action=cancel" onsubmit="return confirm('Cancel this ticket? This cannot be undone.');" style="display:inline;">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" value="<?= (int) $booking['booking_id'] ?>">
                <button type="submit" class="btn btn-danger">Cancel Booking</button>
            </form>

        <?php elseif ($booking['booking_status'] === 'confirmed'): ?>

            <span class="muted-text">This trip has already departed, so the booking can no longer be modified or cancelled.</span>

        <?php endif; ?>

        <?php if ($booking['booking_status'] === 'confirmed' && $booking['payment_status'] === 'pending' && $booking['payment_method'] === 'COD'): ?>

            <form method="post" action="index.php?page=passenger&action=pay" style="display:inline;">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" value="<?= (int) $booking['booking_id'] ?>">
                <input type="hidden" name="payment_method" value="COD">
                <input type="hidden" name="mark_paid" value="1">
                <button type="submit" class="btn btn-primary">I've Paid (Cash Collected)</button>
            </form>

        <?php elseif ($booking['booking_status'] === 'confirmed' && $booking['payment_status'] === 'pending' && $booking['payment_method'] === 'counter'): ?>

            <span class="muted-text">Pay at the counter — our admin will confirm your payment.</span>

        <?php endif; ?>

        <a href="index.php?page=passenger&action=mytickets" class="btn btn-ghost">Back to My Tickets</a>

    </div>

</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>
