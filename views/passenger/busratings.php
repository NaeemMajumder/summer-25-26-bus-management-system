<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">

    <h3 class="card-title">All Bus Ratings</h3>

    <?php if (empty($ratings)): ?>

        <p class="muted-text">No buses found.</p>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Bus</th>
                    <th>Number</th>
                    <th>Type</th>
                    <th>Rating</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($ratings as $bus): ?>

                    <tr>
                        <td><?= esc($bus['bus_name']) ?></td>
                        <td><?= esc($bus['bus_number']) ?></td>
                        <td><span class="badge badge-<?= $bus['bus_type'] === 'AC' ? 'ac' : 'nonac' ?>"><?= esc($bus['bus_type']) ?></span></td>
                        <td><?= star_rating_html($bus['avg_rating'], $bus['review_count']) ?></td>
                    </tr>

                <?php endforeach; ?>

            </tbody>
        </table>

    <?php endif; ?>

</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>
