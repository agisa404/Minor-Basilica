<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
$isAdmin = is_admin_or_staff($user);

if ($isAdmin) {
    $stmt = $conn->prepare('SELECT r.*, u.full_name, u.email
        FROM service_requests r
        JOIN users u ON u.id = r.user_id
        ORDER BY r.created_at DESC');
} else {
    $uid = (int)$user['id'];
    $stmt = $conn->prepare('SELECT r.*, u.full_name, u.email
        FROM service_requests r
        JOIN users u ON u.id = r.user_id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC');
    $stmt->bind_param('i', $uid);
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

render_header('Service Reservations', 'reservations');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Service Reservation</h2>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-light" href="filled_forms.php">View Filled Forms</a>
        <a class="btn btn-warning" href="services.php">Create Reservation</a>
    </div>
</div>
<p class="text-secondary mb-4">Track reservation requests for church services and ceremony schedules.</p>

<div class="card bg-dark border-warning-subtle">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if ($isAdmin): ?><th>Requester</th><?php endif; ?>
                        <th>Service</th>
                        <th>Requested Date/Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="<?php echo $isAdmin ? 6 : 5; ?>" class="text-center">No service reservations found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td>#<?php echo (int)$r['id']; ?></td>
                                <?php if ($isAdmin): ?>
                                    <td><?php echo e(($r['full_name'] ?: '-') . ' / ' . $r['email']); ?></td>
                                <?php endif; ?>
                                <td>
                                    <strong><?php echo e($r['title']); ?></strong><br>
                                    <small class="text-secondary"><?php echo e($r['form_type']); ?></small>
                                </td>
                                <td>
                                    <?php echo e($r['requested_date'] ?: 'N/A'); ?><br>
                                    <small class="text-secondary"><?php echo e($r['requested_time'] ? date('h:i A', strtotime($r['requested_time'])) : 'N/A'); ?></small>
                                </td>
                                <td>
                                    <?php
                                    $badge = match ($r['status']) {
                                        'confirmed' => 'success',
                                        'rejected' => 'danger',
                                        'conflict' => 'warning',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge text-bg-<?php echo $badge; ?>"><?php echo e($r['status']); ?></span>
                                </td>
                                <td><a class="btn btn-sm btn-outline-info" href="filled_form_view.php?id=<?php echo (int)$r['id']; ?>">Full View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php render_footer(); ?>
