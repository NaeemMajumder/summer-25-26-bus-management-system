<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">

    <h3 class="card-title">Booking Summary</h3>

    <div class="trip-summary-box">

        <div>
            <h2><?= esc($trip['bus_name']) ?> <span class="badge badge-<?= $trip['bus_type'] === 'AC' ? 'ac' : 'nonac' ?>"><?= esc($trip['bus_type']) ?></span></h2>
            <p><?= esc($trip['origin']) ?> &rarr; <?= esc($trip['destination']) ?></p>
            <p>&#128197; <?= esc(date('d M Y', strtotime($trip['trip_date']))) ?> &nbsp; &#8987; <?= esc(date('h:i A', strtotime($trip['departure_time']))) ?></p>
            <p>Boarding: <strong><?= esc($boardingStop['stop_name']) ?></strong> &middot; Dropping: <strong><?= esc($droppingStop['stop_name']) ?></strong></p>
            <p>Seats: <strong><?= (int) $seats ?></strong>
                <?php if ($wheelchair): ?> &middot; &#9855; Wheelchair requested<?php endif; ?>
            </p>
        </div>

    </div>

</section>


<section class="card">

    <h3 class="card-title">Fare Breakdown</h3>

    <div class="fare-box">
        <div class="fare-row">
            <span>Subtotal (<?= (int) $seats ?> &times; <?= esc(format_currency($trip['fare'])) ?>)</span>
            <span><?= esc(format_currency($totals['subtotal'])) ?></span>
        </div>

        <?php if ($totals['discount'] > 0): ?>
            <div class="fare-row">
                <span>Promo discount (<?= esc($promoCode) ?>)</span>
                <span>-<?= esc(format_currency($totals['discount'])) ?></span>
            </div>
        <?php endif; ?>

        <div class="fare-row fare-total">
            <span>Total</span>
            <span><?= esc(format_currency($totals['total'])) ?></span>
        </div>
    </div>

</section>


<section class="card">

    <h3 class="card-title">Payment Method</h3>

    <form method="post" action="index.php?page=passenger&action=book" novalidate>
        <?php csrf_field(); ?>

        <input type="hidden" name="trip_id" value="<?= (int) $trip['trip_id'] ?>">
        <input type="hidden" name="seats" value="<?= (int) $seats ?>">
        <input type="hidden" name="boarding_stop_id" value="<?= (int) $boardingStop['stop_id'] ?>">
        <input type="hidden" name="dropping_stop_id" value="<?= (int) $droppingStop['stop_id'] ?>">
        <?php if ($wheelchair): ?><input type="hidden" name="wheelchair" value="1"><?php endif; ?>
        <?php if (!is_blank($promoCode)): ?><input type="hidden" name="promo_code" value="<?= esc($promoCode) ?>"><?php endif; ?>

        <div class="payment-box">

            <label class="payment-option">
                <input type="radio" name="payment_method" value="COD" checked>
                <span>
                    <strong>Cash on Boarding</strong>
                    <small>Pay the conductor when you board the bus</small>
                </span>
            </label>

            <label class="payment-option">
                <input type="radio" name="payment_method" value="counter">
                <span>
                    <strong>Pay at Counter</strong>
                    <small>Pay at the bus company's counter before departure</small>
                </span>
            </label>

        </div>

        <div class="fare-box">
            <div class="fare-row fare-total">
                <span>Total to Pay</span>
                <span><?= esc(format_currency($totals['total'])) ?></span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Confirm Booking</button>

    </form>

</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>
