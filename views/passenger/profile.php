<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card card-narrow">

    <h3 class="card-title">Profile Information</h3>

    <form method="post" action="index.php?page=passenger&action=updateprofile" novalidate>
        <?php csrf_field(); ?>

        <div class="form-group">
            <label for="profName">Name</label>
            <input type="text" id="profName" name="name" value="<?= esc($user['name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="profEmail">Email</label>
            <input type="email" id="profEmail" value="<?= esc($user['email'] ?? '') ?>" readonly>
        </div>

        <div class="form-group">
            <label for="profPhone">Phone</label>
            <input type="text" id="profPhone" name="phone" value="<?= esc($user['phone'] ?? '') ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>

    </form>

</section>


<section class="card card-narrow">

    <h3 class="card-title">Change Password</h3>

    <form method="post" action="index.php?page=passenger&action=changepassword" novalidate>
        <?php csrf_field(); ?>

        <div class="form-group">
            <label for="currentPassword">Current Password</label>
            <input type="password" id="currentPassword" name="current_password" required>
        </div>

        <div class="form-group">
            <label for="newPassword">New Password</label>
            <input type="password" id="newPassword" name="new_password" placeholder="At least 6 characters" required>
        </div>

        <div class="form-group">
            <label for="confirmNewPassword">Confirm New Password</label>
            <input type="password" id="confirmNewPassword" name="confirm_password" required>
        </div>

        <button type="submit" class="btn btn-primary">Change Password</button>

    </form>

</section>


<section class="card card-narrow card-danger-zone">

    <h3 class="card-title">Delete Account</h3>
    <p class="muted-text">This permanently deletes your account, bookings and reviews. This cannot be undone.</p>

    <form method="post" action="index.php?page=passenger&action=deleteaccount" onsubmit="return confirm('This will permanently delete your account. Continue?');" novalidate>
        <?php csrf_field(); ?>

        <div class="form-group">
            <label for="deletePassword">Confirm Password</label>
            <input type="password" id="deletePassword" name="password" required>
        </div>

        <button type="submit" class="btn btn-danger">Delete My Account</button>

    </form>

</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>
