<?php
require_once __DIR__ . '/layout.php';
$user = current_user();
$isAdmin = is_admin_or_staff($user);

$stmt = $conn->prepare('SELECT e.*,
    (SELECT COUNT(*) FROM attendance_logs a WHERE a.schedule_id = e.id) AS attendance_count
    FROM event_schedules e
    ORDER BY e.event_date ASC, e.event_time ASC');
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

render_header('QR Attendance Monitoring', 'attendance');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Church Event Attendance Monitoring</h2>
    <?php if ($isAdmin): ?>
        <a class="btn btn-warning" href="event_schedule_admin.php">Create Schedule</a>
    <?php endif; ?>
</div>
<p class="text-secondary mb-4">Scan the event QR to check in participants. Attendance is logged per event schedule.</p>

<div class="row g-3">
    <?php if (!$events): ?>
        <div class="col-12"><div class="alert alert-info">No scheduled events found.</div></div>
    <?php else: ?>
        <?php
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        ?>
        <?php foreach ($events as $e): ?>
            <?php $checkInUrl = $scheme . '://' . $host . dirname($_SERVER['SCRIPT_NAME']) . '/attendance_scan.php?token=' . urlencode($e['qr_token']); ?>
            <div class="col-lg-6">
                <div class="card bg-dark border-warning-subtle h-100">
                    <div class="card-body">
                        <h5 class="text-warning mb-2"><?php echo e($e['title']); ?></h5>
                        <p class="mb-1"><strong>Date:</strong> <?php echo e($e['event_date']); ?> <?php echo e(date('h:i A', strtotime($e['event_time']))); ?></p>
                        <p class="mb-1"><strong>Location:</strong> <?php echo e($e['location'] ?: 'TBA'); ?></p>
                        <p class="mb-3"><strong>Attendance:</strong> <span class="badge text-bg-info"><?php echo (int)$e['attendance_count']; ?></span></p>

                        <div class="mb-2">
                            <img
                                src="https://quickchart.io/qr?text=<?php echo urlencode($checkInUrl); ?>&size=190"
                                alt="Attendance QR"
                                width="190"
                                height="190"
                                class="rounded border border-warning-subtle bg-white p-2"
                            >
                        </div>
                        <small class="d-block mb-2">Scan QR or open:</small>
                        <a class="btn btn-sm btn-outline-light" href="attendance_scan.php?token=<?php echo e($e['qr_token']); ?>">Open Check-in</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php render_footer(); ?>
