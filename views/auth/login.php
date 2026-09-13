<?php

$pageTitle = 'Login';
$pageHeading = 'Login to ' . esc(APP_NAME);
$pageSub = 'Access your passenger account';

require __DIR__ . '/../partials/header.php';
?>

<section class="card card-narrow">

    <h3 class="card-title">Login</h3>
    <form method="post" action="index.php?page=login" id="loginForm" novalidate>
        <?php csrf_field(); ?>

        <div class="form-group">
            <label for="loginEmail">Email</label>
            <input type="email" id="loginEmail" name="email" value="<?= esc($_COOKIE['remember_email'] ?? '') ?>"
                placeholder="Enter your email">
            <small class="field-error" id="err-login-email"></small>
        </div>

        <div class="form-group">
            <label for="loginPassword">Password</label>
            <div class="password-wrap">
                <input type="password" id="loginPassword" name="password" placeholder="Enter your password">
                <button type="button" class="toggle-eye" data-target="loginPassword" aria-label="Show password">
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
            <small class="field-error" id="err-login-password"></small>
        </div>

        <div class="form-row-inline">
            <label class="checkbox-inline">
                <input type="checkbox" name="remember" value="1">
                Remember me
            </label>
            <a href="index.php?page=forgot">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Login</button>

    </form>

    <p class="form-footnote">
        Don't have an account?
        <a href="index.php?page=register">Register</a>
    </p>

</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>