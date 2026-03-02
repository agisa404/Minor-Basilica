<?php
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/core.php';

$schedules = [];

$stmt = $conn->prepare("SELECT s.id, s.event_title, s.event_date, s.event_time, r.form_type, r.status, 'reservation' AS source
    FROM schedules s
    JOIN service_requests r ON r.id = s.request_id
    ORDER BY s.event_date ASC, s.event_time ASC");
$stmt->execute();
$reservationSchedules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("SELECT e.id, e.title AS event_title, e.event_date, e.event_time, 'Admin Event' AS form_type, 'scheduled' AS status, 'admin' AS source
    FROM event_schedules e
    ORDER BY e.event_date ASC, e.event_time ASC");
$stmt->execute();
$adminSchedules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$schedules = array_merge($reservationSchedules, $adminSchedules);
usort($schedules, static function ($a, $b) {
    return strcmp(($a['event_date'] . ' ' . $a['event_time']), ($b['event_date'] . ' ' . $b['event_time']));
});

render_header('Events and Schedules', 'events');
?>
<h2 class="mb-3">Approved Event Schedules</h2>
<p class="text-secondary">Schedules are generated after Admin/Staff confirmation.</p>

<div class="table-responsive">
    <table class="table table-dark table-striped table-hover align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Event</th>
                <th>Date</th>
                <th>Time</th>
                <th>Form Type</th>
                <th>Source</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$schedules): ?>
                <tr><td colspan="7" class="text-center text-secondary">No schedules yet.</td></tr>
            <?php else: ?>
                <?php foreach ($schedules as $i => $s): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo e($s['event_title']); ?></td>
                        <td><?php echo e($s['event_date']); ?></td>
                        <td><?php echo e(date('h:i A', strtotime($s['event_time']))); ?></td>
                        <td><?php echo e($s['form_type']); ?></td>
                        <td><span class="badge text-bg-<?php echo $s['source'] === 'admin' ? 'info' : 'secondary'; ?>"><?php echo e($s['source']); ?></span></td>
                        <td><span class="badge text-bg-success"><?php echo e($s['status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php render_footer(); ?>
