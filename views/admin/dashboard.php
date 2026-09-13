<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">
    <h3 class="card-title">Welcome, Admin 👋</h3>
    <p class="muted-text">This is your admin panel. Use the menu above to manage buses, routes, trips, promos and
        revenue.</p>
</section>

<section class="card">
    <h3 class="card-title">All Users</h3>
    <p class="card-note"><?= (int) $totalUsers ?> user(s) total</p>

    <?php if (empty($users)): ?>

        <div class="empty-box">
            <p>No users found.</p>
        </div>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Change Role</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= esc($u['name']) ?></td>
                        <td><?= esc($u['email']) ?></td>
                        <td><?= esc($u['phone']) ?></td>
                        <td><span class="pill pill-success"><?= esc(ucfirst($u['role'])) ?></span></td>
                        <td><?= $u['created_at'] ? esc(substr($u['created_at'], 0, 10)) : '<span class="muted-text">—</span>' ?>
                        </td>
                        <td>
                            <?php if ((int) $u['user_id'] === current_user_id()): ?>
                                <span class="muted-text">This is you</span>
                            <?php else: ?>
                                <form method="POST" action="index.php?page=admin&action=updateuserrole"
                                    onsubmit="return confirm('Change this user\'s role?');"
                                    style="display:flex; gap:8px; align-items:center;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="user_id" value="<?= (int) $u['user_id'] ?>">
                                    <input type="hidden" name="p" value="<?= (int) $page ?>">
                                    <select name="role">
                                        <?php foreach (['passenger', 'driver', 'admin', 'manager'] as $roleOption): ?>
                                            <option value="<?= esc($roleOption) ?>" <?= $u['role'] === $roleOption ? 'selected' : '' ?>>
                                                <?= esc(ucfirst($roleOption)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-small btn-primary">Update</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div class="form-row-inline" style="margin-top: 20px;">
                <span class="muted-text">Page <?= (int) $page ?> of <?= (int) $totalPages ?></span>
                <div style="display:flex; gap:10px;">
                    <?php if ($page > 1): ?>
                        <a href="index.php?page=admin&action=dashboard&p=<?= $page - 1 ?>" class="btn btn-small btn-ghost">&larr;
                            Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="index.php?page=admin&action=dashboard&p=<?= $page + 1 ?>" class="btn btn-small btn-ghost">Next
                            &rarr;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>