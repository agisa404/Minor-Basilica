<?php
require_once __DIR__ . '/layout.php';
$admin = require_admin_or_staff();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventDate = trim($_POST['event_date'] ?? '');
        $eventTime = trim($_POST['event_time'] ?? '');
        $location = trim($_POST['location'] ?? '');

        if ($title === '' || $eventDate === '' || $eventTime === '') {
            set_flash('danger', 'Title, date, and time are required.');
            header('Location: event_schedule_admin.php');
            exit();
        }

        $token = generate_qr_token(48);
        $uid = (int)$admin['id'];
        $stmt = $conn->prepare('INSERT INTO event_schedules (title, description, event_date, event_time, location, qr_token, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssssi', $title, $description, $eventDate, $eventTime, $location, $token, $uid);
        $stmt->execute();
        $stmt->close();

        set_flash('success', 'Event schedule created with QR attendance token.');
        header('Location: event_schedule_admin.php');
        exit();
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM event_schedules WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Event schedule deleted.');
        header('Location: event_schedule_admin.php');
        exit();
    }
}

$stmt = $conn->prepare('SELECT e.*, u.full_name,
    (SELECT COUNT(*) FROM attendance_logs a WHERE a.schedule_id = e.id) AS attendance_count
    FROM event_schedules e
    LEFT JOIN users u ON u.id = e.created_by
    ORDER BY e.event_date DESC, e.event_time DESC');
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

render_header('Event Schedule Admin', 'schedule_admin');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Event Schedule</h2>
    <a class="btn btn-outline-light" href="attendance.php">Open Attendance Module</a>
</div>

<div class="card bg-dark border-warning-subtle mb-4">
    <div class="card-body">
        <h5 class="text-warning">Create Event Schedule</h5>
        <form method="POST" class="row g-3">
            <input type="hidden" name="action" value="create">
            <div class="col-md-6">
                <label class="form-label">Event Title</label>
                <input class="form-control" type="text" name="title" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input class="form-control" type="date" name="event_date" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Time</label>
                <input class="form-control" type="time" name="event_time" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Location</label>
                <input class="form-control" type="text" name="location" placeholder="Minor Basilica Main Hall">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3"></textarea>
            </div>
            <div class="col-12">
                <button class="btn btn-warning" type="submit">Create Schedule</button>
            </div>
        </form>
    </div>
</div>

<div class="card bg-dark border-warning-subtle">
    <div class="card-body">
        <h5 class="text-warning mb-3">Created Schedules</h5>
        <div class="table-responsive">
            <table class="table table-dark table-striped align-middle">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date/Time</th>
                        <th>Location</th>
                        <th>Attendance</th>
                        <th>QR Link</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$events): ?>
                        <tr><td colspan="6" class="text-center">No event schedules yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($events as $e): ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($e['title']); ?></strong><br>
                                    <small class="text-secondary"><?php echo e($e['description'] ?: '-'); ?></small>
                                </td>
                                <td><?php echo e($e['event_date']); ?> <?php echo e(date('h:i A', strtotime($e['event_time']))); ?></td>
                                <td><?php echo e($e['location'] ?: '-'); ?></td>
                                <td><span class="badge text-bg-info"><?php echo (int)$e['attendance_count']; ?></span></td>
                                <td><a href="attendance_scan.php?token=<?php echo e($e['qr_token']); ?>" class="btn btn-sm btn-outline-light">Check-in URL</a></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$e['id']; ?>">
                                        <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php render_footer(); ?>
