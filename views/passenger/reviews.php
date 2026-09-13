<?php require __DIR__ . '/../partials/header.php'; ?>


<section class="card">

    <h3>Give Review</h3>

    <p>Booking ID: <?= $_GET['id'] ?? '' ?></p>


    <form method="post">

        <label>Rating</label>

        <select name="rating">
            <option value="5">5 Star</option>
            <option value="4">4 Star</option>
            <option value="3">3 Star</option>
            <option value="2">2 Star</option>
            <option value="1">1 Star</option>
        </select>


        <br><br>


        <label>Comment</label>

        <textarea name="comment"></textarea>


        <br><br>


        <button class="btn btn-primary">
            Submit Review
        </button>


    </form>


</section>


<?php require __DIR__ . '/../partials/footer.php'; ?>


<section class="card">

    <h3 class="card-title">Trips Awaiting Your Review</h3>

    <?php if (empty($eligibleBookings)): ?>

        <p class="muted-text">No completed trips are waiting for a review right now.</p>

    <?php else: ?>

        <?php foreach ($eligibleBookings as $trip): ?>

            <div class="review-form-card">

                <div class="review-trip-meta">
                    <strong><?= esc($trip['bus_name']) ?></strong>
                    <span class="muted-text"><?= esc($trip['origin']) ?> &rarr; <?= esc($trip['destination']) ?> &middot;
                        <?= esc(date('d M Y', strtotime($trip['trip_date']))) ?></span>
                </div>

                <form method="post" action="index.php?page=passenger&action=addreview" class="review-inline-form" novalidate>
                    <?php csrf_field(); ?>
                    <input type="hidden" name="booking_id" value="<?= (int) $trip['booking_id'] ?>">
                    <input type="hidden" name="bus_id" value="<?= (int) $trip['bus_id'] ?>">

                    <select name="rating" required>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= $i === 5 ? 'selected' : '' ?>><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                        <?php endfor; ?>
                    </select>

                    <input type="text" name="comment" placeholder="Share your experience" required>

                    <button type="submit" class="btn btn-primary btn-small">Submit</button>
                </form>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</section>


<section class="card">

    <h3 class="card-title">My Reviews</h3>

    <?php if (empty($reviews)): ?>

        <p class="muted-text">You haven't written any reviews yet.</p>

    <?php else: ?>

        <?php foreach ($reviews as $review): ?>

            <div class="review-card">

                <h4><?= esc($review['bus_name'] ?? 'Bus') ?></h4>

                <?php if (!empty($review['origin'])): ?>
                    <p class="muted-text"><?= esc($review['origin']) ?> &rarr; <?= esc($review['destination']) ?></p>
                <?php endif; ?>

                <p><?= star_rating_html($review['rating']) ?></p>
                <p><?= esc($review['comment']) ?></p>

                <div class="table-actions">
                    <a href="index.php?page=passenger&action=myreviews&edit=<?= (int) $review['review_id'] ?>"
                        class="btn btn-small">Edit</a>

                    <form method="post" action="index.php?page=passenger&action=deletereview"
                        onsubmit="return confirm('Delete this review?');" style="display:inline;">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= (int) $review['review_id'] ?>">
                        <button type="submit" class="btn btn-small btn-danger">Delete</button>
                    </form>
                </div>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>