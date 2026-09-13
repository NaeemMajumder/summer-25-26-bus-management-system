<?php
?>

</main>

<footer class="footer">

    <h3><?= esc(APP_NAME) ?></h3>
    <p>Online bus ticket booking and travel management system.</p>

    <div class="footer-links">
        <a href="index.php?page=passenger">Home</a>
        <a href="index.php?page=passenger&action=busratings">Bus Ratings</a>
        <a href="index.php?page=passenger&action=feedback">Contact</a>
    </div>

    <p>&copy; <?= date('Y') ?> <?= esc(APP_NAME) ?> — All rights reserved.</p>

</footer>

<script src="assets/js/app.js?v=4"></script>

</body>

</html>