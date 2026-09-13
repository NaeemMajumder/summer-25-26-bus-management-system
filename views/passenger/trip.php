<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">

    <h3 class="card-title">Trip Details</h3>

    <div class="trip-summary-box">
        <div>
            <h2>
                <?= esc($trip['bus_name']) ?>
                <span class="badge badge-<?= $trip['bus_type'] === 'AC' ? 'ac' : 'nonac' ?>"><?= esc($trip['bus_type']) ?></span>
            </h2>
            <p class="muted-text">&#128652; Bus No: <?= esc($trip['bus_number']) ?></p>
            <p><?= esc($trip['origin']) ?> &rarr; <?= esc($trip['destination']) ?> &middot; <?= esc($trip['duration']) ?></p>
            <p>&#128197; <?= esc(date('d M Y', strtotime($trip['trip_date']))) ?>
                &nbsp; &#8987; <?= esc(date('h:i A', strtotime($trip['departure_time']))) ?>
                &rarr; <?= esc(date('h:i A', strtotime($trip['arrival_time']))) ?>
            </p>
            <p><?= star_rating_html($trip['avg_rating'], $trip['review_count']) ?></p>
        </div>

        <div class="trip-fare-callout">
            <p>Fare per seat</p>
            <h2><?= esc(format_currency($trip['fare'])) ?></h2>
            <p class="muted-text"><?= (int) $trip['available_seats'] ?> seat(s) left</p>
        </div>
    </div>

</section>


<?php if (!is_logged_in()): ?>

    <section class="card">
        <h3 class="card-title">Ready to book?</h3>
        <p>Please log in to reserve a seat on this trip.</p>
        <a href="index.php?page=login" class="btn btn-primary">Login to Book</a>
        <a href="index.php?page=register" class="btn btn-ghost">Create an account</a>
    </section>

<?php elseif ($trip['status'] !== 'scheduled' || (int) $trip['available_seats'] < 1): ?>

    <section class="card">
        <h3 class="card-title">This trip is unavailable</h3>
        <p>Sorry, this trip is no longer open for booking. Please search for another trip.</p>
        <a href="index.php?page=passenger&action=search" class="btn btn-primary">Back to Search</a>
    </section>

<?php else: ?>

    <section class="card">

        <h3 class="card-title">Passenger Details</h3>

        <form method="post" action="index.php?page=passenger&action=confirm" id="bookingForm" novalidate>
            <?php csrf_field(); ?>

            <input type="hidden" name="trip_id" value="<?= (int) $trip['trip_id'] ?>">

            <div class="form-row">

                <div class="form-group">
                    <label for="boardingStop">Boarding Point</label>
                    <select name="boarding_stop_id" id="boardingStop" required>
                        <option value="">Select boarding point</option>
                        <?php foreach ($stops as $stop): ?>
                            <option value="<?= (int) $stop['stop_id'] ?>"><?= esc($stop['stop_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="droppingStop">Dropping Point</label>
                    <select name="dropping_stop_id" id="droppingStop" required>
                        <option value="">Select dropping point</option>
                        <?php foreach ($stops as $stop): ?>
                            <option value="<?= (int) $stop['stop_id'] ?>"><?= esc($stop['stop_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="seat-box">
                <label>Number of Seats (max <?= (int) SEATS_PER_BOOKING ?>)</label>

                <div class="seat-control">
                    <button type="button" id="minusSeat">-</button>
                    <input type="number" name="seats" id="seatCount" value="1" min="1" max="<?= min((int) SEATS_PER_BOOKING, (int) $trip['available_seats']) ?>" readonly>
                    <button type="button" id="plusSeat">+</button>
                </div>
            </div>

            <div class="checkbox-area">
                <label class="checkbox-inline">
                    <input type="checkbox" name="wheelchair" id="wheelchairCheck" value="1">
                    &#9855; Wheelchair support needed
                </label>
            </div>

            <div class="form-group">
                <label for="promoCode">Promo Code (optional)</label>
                <input type="text" name="promo_code" id="promoCode" placeholder="e.g. EID2026">
                <p class="promo-note" id="promoNote"></p>
            </div>

            <div class="fare-box">
                <div class="fare-row">
                    <span>Subtotal</span>
                    <span id="fareSubtotal"><?= esc(format_currency($trip['fare'])) ?></span>
                </div>
                <div class="fare-row" id="discountRow" style="display:none;">
                    <span>Discount</span>
                    <span id="fareDiscount">-<?= esc(format_currency(0)) ?></span>
                </div>
                <div class="fare-row fare-total">
                    <span>Total</span>
                    <span id="fareTotal"><?= esc(format_currency($trip['fare'])) ?></span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Proceed to Confirm &amp; Pay</button>

        </form>

    </section>

    <script>
        window.TRIP_FARE = <?= (float) $trip['fare'] ?>;
        window.TRIP_ID = <?= (int) $trip['trip_id'] ?>;
        window.SEATS_PER_BOOKING = <?= (int) SEATS_PER_BOOKING ?>;
        window.MAX_AVAILABLE = <?= (int) $trip['available_seats'] ?>;
        window.CURRENCY_SYMBOL = "<?= addslashes(CURRENCY) ?>";
    </script>

<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
