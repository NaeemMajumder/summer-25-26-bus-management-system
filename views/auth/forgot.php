<?php

$token = $_GET['token'] ?? '';

$pageTitle = 'Forgot Password';
$pageHeading = $token ? 'Set a New Password' : 'Forgot Password';
$pageSub = $token ? 'Choose a new password for your account' : 'Enter your email to get a reset link';

require __DIR__ . '/../partials/header.php';
?>

<section class="card card-narrow">

    <?php if ($token): ?>

        <h3 class="card-title">Set a New Password</h3>

        <form method="post" action="index.php?page=forgot" novalidate>
            <?php csrf_field(); ?>
            <input type="hidden" name="token" value="<?= esc($token) ?>">

            <div class="form-group">
                <label for="newPassword">New Password</label>
                <div class="password-wrap">
                    <input id="newPassword" name="password" type="password" placeholder="At least 6 characters">
                    <button type="button" class="toggle-eye" data-target="newPassword" aria-label="Show password">
                        <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                            <line x1="1" y1="1" x2="23" y2="23" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirmNewPassword">Confirm New Password</label>
                <div class="password-wrap">
                    <input id="confirmNewPassword" name="confirm_password" type="password">
                    <button type="button" class="toggle-eye" data-target="confirmNewPassword" aria-label="Show password">
                        <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                            <line x1="1" y1="1" x2="23" y2="23" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Update Password</button>

        </form>

    <?php else: ?>

        <h3 class="card-title">Forgot Password</h3>
        <p class="card-note">Enter the email on your account and we'll help you reset your password.</p>

        <form method="post" action="index.php?page=forgot" novalidate>
            <?php csrf_field(); ?>

            <div class="form-group">
                <label for="forgotEmail">Email</label>
                <input id="forgotEmail" name="email" type="email" placeholder="you@example.com" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>

        </form>

    <?php endif; ?>

    <p class="form-footnote">
        <a href="index.php?page=login">Back to login</a>
    </p>

</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>