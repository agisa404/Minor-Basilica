<?php
require_once __DIR__ . '/layout.php';
$priest = require_priest_only();

$announcementCount = 0;
$aRes = $conn->query('SELECT COUNT(*) AS total FROM announcements WHERE is_published = 1');
if ($aRes) {
    $row = $aRes->fetch_assoc();
    $announcementCount = (int)($row['total'] ?? 0);
}

$upcomingEvents = [];
$evRes = $conn->query('SELECT title, event_date, event_time, location FROM event_schedules WHERE event_date >= CURDATE() ORDER BY event_date ASC, event_time ASC LIMIT 8');
if ($evRes) {
    $upcomingEvents = $evRes->fetch_all(MYSQLI_ASSOC);
}

render_header('Priest Dashboard', 'priest');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Priest Dashboard</h2>
    <div class="text-secondary">Logged in as <?php echo e($priest['full_name'] ?: $priest['email']); ?></div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-outline-light" href="announcements.php">View Announcements</a>
    <a class="btn btn-outline-light" href="events.php">View Schedules</a>
    <a class="btn btn-outline-light" href="services.php">Ministries</a>
    <a class="btn btn-outline-light" href="account_management.php">Manage Account</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card bg-dark border-warning-subtle h-100">
            <div class="card-body">
                <h6 class="text-warning mb-2">Published Announcements</h6>
                <div class="display-6"><?php echo $announcementCount; ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card bg-dark border-warning-subtle h-100">
            <div class="card-body">
                <h6 class="text-warning mb-3">Upcoming Event Schedules</h6>
                <?php if (!$upcomingEvents): ?>
                    <p class="text-secondary mb-0">No upcoming schedules yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingEvents as $event): ?>
                                    <tr>
                                        <td><?php echo e($event['title']); ?></td>
                                        <td><?php echo e($event['event_date']); ?></td>
                                        <td><?php echo e(date('h:i A', strtotime($event['event_time']))); ?></td>
                                        <td><?php echo e($event['location'] ?: '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php render_footer(); ?>
