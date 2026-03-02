<?php
require_once __DIR__ . '/layout.php';
purge_expired_announcements();

$user = current_user();
$isAdmin = is_admin_or_staff($user);
$logoPath = '612401184_4348220988792023_5812589285034246497_n.jpg';

if (!$user) {
    $publicStmt = $conn->prepare('SELECT a.title, a.content, a.created_at FROM announcements a WHERE a.is_published = 1 AND (a.expires_at IS NULL OR a.expires_at > ?) ORDER BY a.created_at DESC LIMIT 3');
    $nowPublic = app_now()->format('Y-m-d H:i:s');
    $publicStmt->bind_param('s', $nowPublic);
    $publicStmt->execute();
    $publicAnnouncements = $publicStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $publicStmt->close();

    render_header('Minor Basilica', 'home');
    ?>
    <div class="dashboard-home">
        <div class="dash-main rounded-4 p-4 p-md-5">
            <div class="home-maroon-strip rounded-4 mb-4 px-3 px-md-4 py-2">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <img class="home-strip-logo" src="<?php echo e($logoPath); ?>" alt="Basilica Logo">
                        <span class="home-strip-title">Minor Basilica</span>
                    </div>
                    <div class="home-strip-social d-flex align-items-center gap-2">
                        <a href="https://www.facebook.com/spbbasilica" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
            </div>

            <h1 class="h2 mb-2">Welcome to Minor Basilica Portal</h1>
            <p class="text-light-subtle mb-4">Visitors can view public updates. Please login to access the full system modules.</p>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="about_us.php" class="top-link"><i class="bi bi-info-circle"></i><span>About Us</span></a>
                <a href="mission_vision.php" class="top-link"><i class="bi bi-bullseye"></i><span>Mission &amp; Vision</span></a>
                <a href="ministries.php" class="top-link"><i class="bi bi-building"></i><span>Ministries</span></a>
                <a href="help.php" class="top-link"><i class="bi bi-question-circle"></i><span>Help</span></a>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-4">
                <a class="btn btn-warning" href="account_management.php">Login</a>
                <a class="btn btn-outline-light" href="account_management.php">Register</a>
            </div>

            <div class="dash-announcements rounded-4 p-3 p-md-4">
                <h2 class="h4 mb-3">Public Announcements</h2>
                <?php if (!$publicAnnouncements): ?>
                    <div class="alert alert-info mb-0">No announcements available right now.</div>
                <?php else: ?>
                    <div class="d-grid gap-3">
                        <?php foreach ($publicAnnouncements as $item): ?>
                            <article class="announce-item rounded-3 p-3">
                                <h3 class="h6 mb-2"><?php echo e($item['title']); ?></h3>
                                <p class="mb-2"><?php echo nl2br(e($item['content'])); ?></p>
                                <small><?php echo e($item['created_at']); ?></small>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    render_footer();
    exit();
}

$nowForAnnouncements = app_now()->format('Y-m-d H:i:s');
$announcementSql = $isAdmin
    ? 'SELECT a.*, u.full_name FROM announcements a LEFT JOIN users u ON u.id = a.created_by WHERE (a.expires_at IS NULL OR a.expires_at > ?) ORDER BY a.created_at DESC LIMIT 5'
    : 'SELECT a.*, u.full_name FROM announcements a LEFT JOIN users u ON u.id = a.created_by WHERE a.is_published = 1 AND (a.expires_at IS NULL OR a.expires_at > ?) ORDER BY a.created_at DESC LIMIT 5';

$stmt = $conn->prepare($announcementSql);
$stmt->bind_param('s', $nowForAnnouncements);
$stmt->execute();
$announcements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$actionCards = [
    [
        'href' => 'services.php',
        'title' => 'Service Forms',
        'description' => 'Submit mass intentions, baptisms, weddings, and related requests.',
    ],
    [
        'href' => 'attendance.php',
        'title' => 'QR Attendance',
        'description' => 'Track participant check-ins and view attendance logs by event.',
    ],
];

$shortcuts = [
    ['label' => 'Manage Announcements', 'href' => 'announcements.php'],
    ['label' => 'Schedule Events', 'href' => 'event_schedule_admin.php'],
    ['label' => 'Document Requests', 'href' => 'document_requests.php'],
];
$churchAddress = '69 San Pedro Bautista St., San Francisco del Monte, Quezon City, Philippines, 1104';
$churchMapUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($churchAddress);

$appNow = app_now();
$today = $appNow->setTime(0, 0);
$monthParam = trim((string)($_GET['cal'] ?? ''));
if (!preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
    $monthParam = $appNow->format('Y-m');
}
$calendarStart = DateTimeImmutable::createFromFormat('Y-m-d', $monthParam . '-01');
if (!$calendarStart) {
    $calendarStart = new DateTimeImmutable($appNow->format('Y-m-01'));
}
$calendarPrev = $calendarStart->modify('-1 month')->format('Y-m');
$calendarNext = $calendarStart->modify('+1 month')->format('Y-m');

$monthStartDate = $calendarStart->format('Y-m-01');
$monthEndDate = $calendarStart->format('Y-m-t');
$daysInMonth = (int)$calendarStart->format('t');
$startWeekday = (int)$calendarStart->format('w');

$calendarEventCounts = [];
$calendarMonthEvents = [];

$eventStmt = $conn->prepare('SELECT title AS item_title, event_date AS item_date, event_time AS item_time, "Event" AS item_type FROM event_schedules WHERE event_date BETWEEN ? AND ?');
$eventStmt->bind_param('ss', $monthStartDate, $monthEndDate);
$eventStmt->execute();
$eventRows = $eventStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$eventStmt->close();

$scheduleStmt = $conn->prepare('SELECT event_title AS item_title, event_date AS item_date, event_time AS item_time, "Schedule" AS item_type FROM schedules WHERE event_date BETWEEN ? AND ?');
$scheduleStmt->bind_param('ss', $monthStartDate, $monthEndDate);
$scheduleStmt->execute();
$scheduleRows = $scheduleStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$scheduleStmt->close();

$calendarMonthEvents = array_merge($eventRows, $scheduleRows);
usort($calendarMonthEvents, static function (array $a, array $b): int {
    $left = ($a['item_date'] ?? '') . ' ' . ($a['item_time'] ?? '');
    $right = ($b['item_date'] ?? '') . ' ' . ($b['item_time'] ?? '');
    return strcmp($left, $right);
});

foreach ($calendarMonthEvents as $itemRow) {
    $dayNumber = (int)date('j', strtotime((string)$itemRow['item_date']));
    $calendarEventCounts[$dayNumber] = ($calendarEventCounts[$dayNumber] ?? 0) + 1;
}

$calendarCells = array_fill(0, $startWeekday, null);
for ($day = 1; $day <= $daysInMonth; $day++) {
    $calendarCells[] = $day;
}
while (count($calendarCells) % 7 !== 0) {
    $calendarCells[] = null;
}

render_header('Minor Basilica Information Management System', 'home');
?>
<div class="dashboard-home">
    <div class="row g-3">
        <section class="col-xl-9">
            <div class="dash-main rounded-4 p-3 p-md-4">
                <div class="home-maroon-strip rounded-4 mb-4 px-3 px-md-4 py-2">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <img class="home-strip-logo" src="<?php echo e($logoPath); ?>" alt="Basilica Logo">
                            <span class="home-strip-title">Minor Basilica</span>
                        </div>
                        <div class="home-strip-social d-flex align-items-center gap-2">
                            <a href="https://www.facebook.com/spbbasilica" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                            <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                            <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                            <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                            <a href="#" aria-label="Tiktok"><i class="bi bi-tiktok"></i></a>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div>
                        <h1 class="h2 mb-1">Minor Basilica Information Management System</h1>
                        <p class="mb-0 text-light-subtle">Dashboard, announcements, requests, events, and attendance in one place.</p>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-lg-4">
                        <div class="dash-metric rounded-4 p-3 h-100">
                            <div class="metric-icon"><i class="bi bi-megaphone"></i></div>
                            <div class="metric-label">Latest Announcements</div>
                            <div class="metric-value"><?php echo (int)count($announcements); ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="dash-metric rounded-4 p-3 h-100">
                            <div class="metric-icon"><i class="bi bi-grid"></i></div>
                            <div class="metric-label">Active Modules</div>
                            <div class="metric-value">8</div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-4">
                        <div class="dash-metric rounded-4 p-3 h-100">
                            <div class="metric-icon"><i class="bi bi-calendar-event"></i></div>
                            <div class="metric-label">Current Month</div>
                            <div class="metric-value"><?php echo e($appNow->format('F')); ?></div>
                        </div>
                    </div>
                </div>

                <div class="dash-welcome rounded-4 p-3 p-md-4 mb-4">
                    <h2 class="h4 mb-3">Welcome</h2>
                    <p class="mb-0">
                        Manage church operations from one dashboard: post notices, receive online requests,
                        prepare schedules, and monitor QR attendance activity.
                    </p>
                </div>

                <div class="dash-announcements rounded-4 p-3 p-md-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h4 mb-0">Announcements</h2>
                        <a class="btn btn-sm btn-outline-light" href="announcements.php">View All</a>
                    </div>

                    <?php if (!$announcements): ?>
                        <div class="alert alert-info mb-0">No announcements available yet.</div>
                    <?php else: ?>
                        <div class="d-grid gap-3">
                            <?php foreach ($announcements as $item): ?>
                                <article class="announce-item rounded-3 p-3">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <h3 class="h6 mb-2"><?php echo e($item['title']); ?></h3>
                                            <p class="mb-2"><?php echo nl2br(e($item['content'])); ?></p>
                                            <small>
                                                Posted by <?php echo e($item['full_name'] ?: 'System'); ?> on <?php echo e($item['created_at']); ?>
                                            </small>
                                        </div>
                                        <?php if ($isAdmin): ?>
                                            <span class="badge text-bg-<?php echo $item['is_published'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $item['is_published'] ? 'Published' : 'Draft'; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row g-3">
                    <?php foreach ($actionCards as $card): ?>
                        <div class="col-md-6">
                            <a class="dash-action rounded-4 p-3 d-block text-decoration-none" href="<?php echo e($card['href']); ?>">
                                <h3 class="h5 mb-2"><?php echo e($card['title']); ?></h3>
                                <p class="mb-0"><?php echo e($card['description']); ?></p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="dash-announcements rounded-4 p-3 p-md-4 mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h4 mb-0">Reports and Analytics</h2>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a class="dash-action rounded-4 p-3 d-block text-decoration-none h-100" href="attendance.php">
                                <h3 class="h6 mb-2">Attendance Reports</h3>
                                <p class="mb-0">View attendance logs and participation summaries.</p>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a class="dash-action rounded-4 p-3 d-block text-decoration-none h-100" href="document_requests.php">
                                <h3 class="h6 mb-2">Request Analytics</h3>
                                <p class="mb-0">Track document and service request volumes.</p>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <?php if (($user['role'] ?? '') === 'admin'): ?>
                                <a class="dash-action rounded-4 p-3 d-block text-decoration-none h-100" href="admin_dashboard.php">
                                    <h3 class="h6 mb-2">Administrative Insights</h3>
                                    <p class="mb-0">Review key operational data and system activity.</p>
                                </a>
                            <?php elseif (($user['role'] ?? '') === 'priest'): ?>
                                <a class="dash-action rounded-4 p-3 d-block text-decoration-none h-100" href="priest_dashboard.php">
                                    <h3 class="h6 mb-2">Priest Dashboard</h3>
                                    <p class="mb-0">Open priest tools and view pastoral schedules.</p>
                                </a>
                            <?php else: ?>
                                <a class="dash-action rounded-4 p-3 d-block text-decoration-none h-100" href="services.php">
                                    <h3 class="h6 mb-2">Ministry Insights</h3>
                                    <p class="mb-0">Check ministry forms, requests, and service activity.</p>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <aside class="col-xl-3">
            <div class="dash-right rounded-4 p-3 p-md-4">
                <h2 class="h4 mb-3">Quick Panel</h2>
                <div class="quick-card rounded-4 p-3 mb-3">
                    <h3 class="h6 mb-3">Quick Info</h3>
                    <div class="quick-info-list">
                        <div class="quick-info-item">
                            <div class="quick-info-label">Sunday Masses</div>
                            <div class="quick-info-value">6:00 AM &bull; 8:00 AM &bull; 10:00 AM</div>
                            <div class="quick-info-value">3:00 PM &bull; 4:30 PM &bull; 6:00 PM</div>
                        </div>
                        <div class="quick-info-item">
                            <div class="quick-info-label">Location</div>
                            <div class="quick-info-value"><?php echo e($churchAddress); ?></div>
                            <a class="quick-map-link" href="<?php echo e($churchMapUrl); ?>" target="_blank" rel="noopener noreferrer">Open in Google Maps</a>
                            <iframe
                                class="quick-map-embed"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                src="https://maps.google.com/maps?q=<?php echo rawurlencode($churchAddress); ?>&t=&z=15&ie=UTF8&iwloc=&output=embed"
                                title="Minor Basilica map location"></iframe>
                        </div>
                        <div class="quick-info-item">
                            <div class="quick-info-label">Contact</div>
                            <div class="quick-info-value">271482136</div>
                        </div>
                    </div>
                </div>
                <div class="quick-card rounded-4 p-3 mb-3">
                    <h3 class="h6 mb-3">Calendar</h3>
                    <div class="quick-calendar">
                        <div class="quick-calendar-nav">
                            <a class="quick-cal-btn" href="?cal=<?php echo e($calendarPrev); ?>" aria-label="Previous month">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                            <div class="quick-calendar-month"><?php echo e($calendarStart->format('F Y')); ?></div>
                            <a class="quick-cal-btn" href="?cal=<?php echo e($calendarNext); ?>" aria-label="Next month">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                        <div class="quick-calendar-grid quick-calendar-weekdays">
                            <?php foreach (['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $weekday): ?>
                                <div><?php echo e($weekday); ?></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="quick-calendar-grid quick-calendar-days">
                            <?php foreach ($calendarCells as $cellDay): ?>
                                <?php if ($cellDay === null): ?>
                                    <div class="quick-day quick-day-empty"></div>
                                <?php else: ?>
                                    <?php
                                    $cellDate = $calendarStart->format('Y-m-') . str_pad((string)$cellDay, 2, '0', STR_PAD_LEFT);
                                    $isToday = $cellDate === $today->format('Y-m-d');
                                    $eventCount = (int)($calendarEventCounts[$cellDay] ?? 0);
                                    ?>
                                    <div class="quick-day <?php echo $isToday ? 'is-today' : ''; ?> <?php echo $eventCount > 0 ? 'has-event' : ''; ?>">
                                        <span class="quick-day-num"><?php echo $cellDay; ?></span>
                                        <?php if ($eventCount > 0): ?>
                                            <span class="quick-day-dot" title="<?php echo e($eventCount . ' event(s)'); ?>"></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="quick-calendar-events mt-3">
                            <?php if (!$calendarMonthEvents): ?>
                                <p class="mb-0 small">No events this month.</p>
                            <?php else: ?>
                                <div class="small fw-semibold mb-2">Events and schedules this month</div>
                                <div class="quick-event-list">
                                    <?php foreach (array_slice($calendarMonthEvents, 0, 6) as $event): ?>
                                        <div class="quick-event-item">
                                            <span class="quick-event-date"><?php echo e(date('M d', strtotime((string)$event['item_date']))); ?></span>
                                            <span class="quick-event-title"><?php echo e($event['item_title']); ?> <small>(<?php echo e($event['item_type']); ?>)</small></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="quick-card rounded-4 p-3 mb-3">
                    <h3 class="h6 mb-3">Shortcuts</h3>
                    <div class="d-grid gap-2">
                        <?php foreach ($shortcuts as $shortcut): ?>
                            <a href="<?php echo e($shortcut['href']); ?>" class="btn btn-sm btn-outline-info">
                                <?php echo e($shortcut['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if ($user): ?>
                    <div class="quick-card rounded-4 p-3">
                        <h3 class="h6 mb-2">Session</h3>
                        <p class="mb-1"><?php echo e($user['full_name'] ?: $user['email']); ?></p>
                        <small class="text-uppercase"><?php echo e($user['role']); ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>
<?php render_footer(); ?>

