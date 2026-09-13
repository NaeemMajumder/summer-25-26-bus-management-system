<?php

$pageTitle   = 'Register';
$pageHeading = 'Create Account';
$pageSub     = 'Join ' . esc(APP_NAME);

// Which role is selected right now (kept after a failed submit)
$selRole = old('role') ?: 'passenger';

require __DIR__ . '/../partials/header.php';
?>

<section class="card card-narrow">

    <h3 class="card-title">Create Account</h3>

    <form method="post" action="index.php?page=register" id="registerForm" novalidate>
        <?php csrf_field(); ?>

        <!-- ROLE TOGGLE -->
        <div class="form-group">
            <label>Register as</label>
            <div class="role-toggle" id="roleToggle">
                <button type="button" class="role-option <?= $selRole === 'passenger' ? 'active' : '' ?>" data-role="passenger">Passenger</button>
                <button type="button" class="role-option <?= $selRole === 'driver'    ? 'active' : '' ?>" data-role="driver">Driver</button>
                <button type="button" class="role-option <?= $selRole === 'manager'   ? 'active' : '' ?>" data-role="manager">Manager</button>
            </div>
            <input type="hidden" name="role" id="roleInput" value="<?= esc($selRole) ?>">
        </div>

        <!-- COMMON FIELDS -->
        <div class="form-group">
            <label for="regName">Name</label>
            <input id="regName" name="name" type="text" value="<?= esc(old('name')) ?>">
            <small class="field-error" id="err-name"></small>
        </div>

        <div class="form-group">
            <label for="regEmail">Email</label>
            <input id="regEmail" name="email" type="email" value="<?= esc(old('email')) ?>">
            <small class="field-error" id="err-email"></small>
        </div>

        <div class="form-group">
            <label for="regPhone">Phone</label>
            <input id="regPhone" name="phone" type="text" placeholder="11-digit phone number" value="<?= esc(old('phone')) ?>">
            <small class="field-error" id="err-phone"></small>
        </div>

        <!-- ROLE-SPECIFIC FIELDS -->
        <div class="role-fields <?= $selRole === 'passenger' ? 'active' : '' ?>" data-role-fields="passenger">
            <div class="form-group">
                <label for="regNid">NID Number</label>
                <input id="regNid" name="nid_number" type="text" placeholder="National ID number" value="<?= esc(old('nid_number')) ?>">
                <small class="field-error" id="err-nid_number"></small>
            </div>
        </div>

        <div class="role-fields <?= $selRole === 'driver' ? 'active' : '' ?>" data-role-fields="driver">
            <div class="form-group">
                <label for="regLicense">Driving License Number</label>
                <input id="regLicense" name="license_number" type="text" placeholder="License number" value="<?= esc(old('license_number')) ?>">
                <small class="field-error" id="err-license_number"></small>
            </div>
        </div>

        <div class="role-fields <?= $selRole === 'manager' ? 'active' : '' ?>" data-role-fields="manager">
            <div class="form-group">
                <label for="regExp">Years of Experience</label>
                <input id="regExp" name="experience_years" type="number" min="0" placeholder="e.g. 5" value="<?= esc(old('experience_years')) ?>">
                <small class="field-error" id="err-experience_years"></small>
            </div>
            <div class="form-group">
                <label for="regPrevCompany">Previous Company <span class="muted-text">(optional)</span></label>
                <input id="regPrevCompany" name="previous_company" type="text" placeholder="If any" value="<?= esc(old('previous_company')) ?>">
                <small class="field-error" id="err-previous_company"></small>
            </div>
        </div>

        <!-- PASSWORD + EYE -->
        <div class="form-group">
            <label for="regPassword">Password</label>
            <div class="password-wrap">
                <input id="regPassword" name="password" type="password" placeholder="At least 6 characters">
                <button type="button" class="toggle-eye" data-target="regPassword" aria-label="Show password">
                    <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
            <small class="field-error" id="err-password"></small>
        </div>

        <div class="form-group">
            <label for="regConfirmPassword">Confirm Password</label>
            <div class="password-wrap">
                <input id="regConfirmPassword" name="confirm_password" type="password">
                <button type="button" class="toggle-eye" data-target="regConfirmPassword" aria-label="Show password">
                    <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
            <small class="field-error" id="err-confirm_password"></small>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Register</button>

    </form>

    <p class="form-footnote">
        Already have an account?
        <a href="index.php?page=login">Login</a>
    </p>

</section>

<?php
clear_old();
require __DIR__ . '/../partials/footer.php';