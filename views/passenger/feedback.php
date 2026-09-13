<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card card-narrow">

    <h3 class="card-title">Get in Touch</h3>
    <p class="muted-text">Have a question, complaint, or suggestion? Send us a message below.</p>

    <form method="post" action="index.php?page=passenger&action=sendfeedback" novalidate>
        <?php csrf_field(); ?>

        <?php if (is_logged_in()): ?>
            <div class="form-group">
                <label>Name</label>
                <input type="text" value="<?= esc(current_user_name()) ?>" readonly>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="feedbackMessage">Message</label>
            <textarea name="message" id="feedbackMessage" placeholder="Write your message here" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Send Message</button>

    </form>

</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>
