<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">

    <div class="form-row-inline">
        <h3 class="card-title" style="margin:0;">All Feedback</h3>
        <form method="get" action="index.php" class="search-form-compact">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="action" value="feedback">
            <input type="text" name="q" value="<?= esc($_GET['q'] ?? '') ?>"
                   placeholder="Search message or user..." style="width:260px;">
        </form>
    </div>

    <?php if (empty($feedback)): ?>

        <div class="empty-box"><p>No feedback yet.</p></div>

    <?php else: ?>

        <?php foreach ($feedback as $f): ?>
            <div class="review-card">
                <p style="margin-bottom:8px;"><?= esc($f['message']) ?></p>
                <p class="muted-text">
                    <?php if ($f['user_name']): ?>
                        &mdash; <?= esc($f['user_name']) ?>
                        <span class="pill"><?= esc(ucfirst($f['user_role'] ?? 'user')) ?></span>
                    <?php else: ?>
                        &mdash; <em>Guest</em>
                    <?php endif; ?>
                    &middot; <?= esc($f['created_at']) ?>
                </p>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>