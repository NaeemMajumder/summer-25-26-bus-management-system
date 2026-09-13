<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">

    <h3 class="card-title">Modify Booking — <?= esc(ticket_code($booking['booking_id'])) ?></h3>
    <p class="muted-text"><?= esc($booking['origin']) ?> &rarr; <?= esc($booking['destination']) ?> &middot; <?= esc(date('d M Y', strtotime($booking['trip_date']))) ?></p>

    <form method="post" action="index.php?page=passenger&action=modify&id=<?= (int) $booking['booking_id'] ?>" id="modifyForm" novalidate>
        <?php csrf_field(); ?>

        <div class="form-row">

            <div class="form-group">
                <label for="modBoardingStop">Boarding Point</label>
                <select name="boarding_stop_id" id="modBoardingStop" required>
                    <?php foreach ($stops as $stop): ?>
                        <option value="<?= (int) $stop['stop_id'] ?>" <?= (int) $stop['stop_id'] === (int) $booking['boarding_stop_id'] ? 'selected' : '' ?>>
                            <?= esc($stop['stop_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="modDroppingStop">Dropping Point</label>
                <select name="dropping_stop_id" id="modDroppingStop" required>
                    <?php foreach ($stops as $stop): ?>
                        <option value="<?= (int) $stop['stop_id'] ?>" <?= (int) $stop['stop_id'] === (int) $booking['dropping_stop_id'] ? 'selected' : '' ?>>
                            <?= esc($stop['stop_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <div class="seat-box">
            <label>Number of Seats (max <?= (int) SEATS_PER_BOOKING ?>)</label>

            <div class="seat-control">
                <button type="button" id="minusSeat">-</button>
                <input type="number" name="seats" id="seatCount" value="<?= (int) $booking['seats_booked'] ?>" min="1" max="<?= (int) SEATS_PER_BOOKING ?>" readonly>
                <button type="button" id="plusSeat">+</button>
            </div>
        </div>

        <div class="checkbox-area">
            <label class="checkbox-inline">
                <input type="checkbox" name="wheelchair" value="1" <?= $booking['wheelchair'] ? 'checked' : '' ?>>
                &#9855; Wheelchair support needed
            </label>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Save Changes</button>

    </form>

    <p class="form-footnote">
        <a href="index.php?page=passenger&action=viewticket&id=<?= (int) $booking['booking_id'] ?>">&larr; Back to ticket</a>
    </p>

</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>
