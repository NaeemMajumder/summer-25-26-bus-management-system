<?php require __DIR__ . '/../partials/header.php'; ?>

<section class="card">
    <h3 class="card-title">Report a Maintenance Issue</h3>

    <form method="POST" action="index.php?page=manager&action=addrequest">
        <?php csrf_field(); ?>

        <div class="form-group">
            <label for="bus_id">Bus</label>
            <select id="bus_id" name="bus_id" required>
                <option value="">— Select a bus —</option>
                <?php foreach ($busList as $bus): ?>
                    <option value="<?= (int) $bus['bus_id'] ?>" <?= (string) old('bus_id') === (string) $bus['bus_id'] ? 'selected' : '' ?>>
                        <?= esc($bus['name']) ?> (<?= esc($bus['bus_number']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="issue">Issue Description</label>
            <textarea id="issue" name="issue" required><?= esc(old('issue')) ?></textarea>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </div>
    </form>
</section>

<section class="card">
    <h3 class="card-title">My Maintenance Requests</h3>

    <form method="GET" action="index.php" class="search-form" style="margin-bottom: 20px;">
        <input type="hidden" name="page" value="manager">
        <input type="hidden" name="action" value="requests">
        <div class="form-group">
            <input type="text" name="q" placeholder="Search by issue or bus number..."
                value="<?= esc($_GET['q'] ?? '') ?>">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-small">Search</button>
        </div>
    </form>

    <?php if (empty($requestList)): ?>

        <div class="empty-box">
            <p>No maintenance requests yet.</p>
        </div>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Bus</th>
                    <th>Issue</th>
                    <th>Status</th>
                    <th>Reported</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requestList as $r): ?>
                    <tr>
                        <td><?= esc($r['bus_name']) ?> (<?= esc($r['bus_number']) ?>)</td>
                        <td><?= esc($r['issue']) ?></td>
                        <td>
                            <?php
                            $map = ['pending' => 'warning', 'in_progress' => 'warning', 'done' => 'success', 'cancelled' => 'danger'];
                            $cls = $map[$r['status']] ?? 'success';
                            ?>
                            <span class="pill pill-<?= $cls ?>"><?= esc(ucwords(str_replace('_', ' ', $r['status']))) ?></span>
                        </td>
                        <td><?= esc(substr($r['created_at'], 0, 16)) ?></td>
                        <td class="table-actions">
                            <?php if ($r['status'] === 'pending'): ?>
                                <form method="POST"
                                    action="index.php?page=manager&action=updaterequest&id=<?= (int) $r['request_id'] ?>"
                                    style="display:inline;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="status" value="in_progress">
                                    <button type="submit" class="btn btn-small btn-primary">Start</button>
                                </form>
                            <?php elseif ($r['status'] === 'in_progress'): ?>
                                <form method="POST"
                                    action="index.php?page=manager&action=updaterequest&id=<?= (int) $r['request_id'] ?>"
                                    style="display:inline;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="status" value="done">
                                    <button type="submit" class="btn btn-small btn-primary">Mark Done</button>
                                </form>
                            <?php endif; ?>

                            <?php if (in_array($r['status'], ['pending', 'in_progress'], true)): ?>
                                <form method="POST"
                                    action="index.php?page=manager&action=cancelrequest&id=<?= (int) $r['request_id'] ?>"
                                    onsubmit="return confirm('Cancel this request?');" style="display:inline;">
                                    <?php csrf_field(); ?>
                                    <button type="submit" class="btn btn-small btn-danger">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>