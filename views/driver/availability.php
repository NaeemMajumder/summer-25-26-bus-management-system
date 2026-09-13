<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">
    <h3 class="card-title"><?= $editing ? 'Edit Availability' : 'Add Availability' ?></h3>

    <form method="POST"
        action="index.php?page=driver&action=<?= $editing ? 'updateavailability&id=' . (int) $editing['availability_id'] : 'addavailability' ?>"
        class="form-grid">

        <?php csrf_field(); ?>

        <div class="form-group">
            <label for="date">Date</label>
            <input type="date" id="date" name="date" required value="<?= esc($editing['date'] ?? old('date')) ?>">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <?php
                $current = $editing['status'] ?? old('status');
                $options = ['available' => 'Available', 'off_day' => 'Off Day', 'on_duty' => 'On Duty'];
                foreach ($options as $value => $label):
                    ?>
                    <option value="<?= esc($value) ?>" <?= $current === $value ? 'selected' : '' ?>>
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group form-group-wide">
            <label for="note">Note (optional)</label>
            <input type="text" id="note" name="note" maxlength="150"
                value="<?= esc($editing['note'] ?? old('note')) ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <?= $editing ? 'Update' : 'Add' ?>
            </button>
            <?php if ($editing): ?>
                <a href="index.php?page=driver&action=availability" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card">
    <h3 class="card-title">My Schedule</h3>

    <?php if (empty($availabilityList)): ?>

        <div class="empty-box">
            <p>এখনো কোনো availability entry দেওয়া হয়নি।</p>
        </div>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Note</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($availabilityList as $a): ?>
                    <tr>
                        <td><?= esc($a['date']) ?></td>
                        <td>
                            <?php
                            $map = ['available' => 'success', 'off_day' => 'danger', 'on_duty' => 'warning'];
                            $cls = $map[$a['status']] ?? 'success';
                            ?>
                            <span class="pill pill-<?= $cls ?>">
                                <?= esc(ucwords(str_replace('_', ' ', $a['status']))) ?>
                            </span>
                        </td>
                        <td><?= esc($a['note']) ?></td>
                        <td>
                            <a href="index.php?page=driver&action=availability&edit=<?= (int) $a['availability_id'] ?>"
                                class="btn btn-small">Edit</a>

                            <form method="POST"
                                action="index.php?page=driver&action=deleteavailability&id=<?= (int) $a['availability_id'] ?>"
                                onsubmit="return confirm('এই entry-টা মুছে ফেলতে চাও?');" style="display:inline;">
                                <?php csrf_field(); ?>
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>