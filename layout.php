<?php
require_once __DIR__ . '/core.php';

function render_header(string $title, string $active = ''): void
{
    $user = current_user();
    $isLogged = $user !== null;
    $currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $publicPages = ['index.php', 'account_management.php', 'login.php', 'register.php', 'logout.php', 'register.html'];
    if (!$isLogged && !in_array($currentPage, $publicPages, true)) {
        set_flash('danger', 'Please login first.');
        header('Location: index.php');
        exit();
    }
    $isAdminStaff = is_admin_or_staff($user);
    $role = $user['role'] ?? '';
    $isAdmin = $role === 'admin';
    $isPriest = $role === 'priest';
    $isHome = $active === 'home';
    $useGlobalSidebar = false;
    $logoPath = '612401184_4348220988792023_5812589285034246497_n.jpg';
    $sideLinks = [
        ['key' => 'home', 'label' => 'Dashboard', 'href' => 'index.php', 'icon' => 'bi-house-door'],
        ['key' => 'announcements', 'label' => 'Announcements', 'href' => 'announcements.php', 'icon' => 'bi-megaphone'],
        ['key' => 'services', 'label' => 'Services', 'href' => 'services.php', 'icon' => 'bi-journal-text'],
        ['key' => 'reservations', 'label' => 'Reservations', 'href' => 'service_reservations.php', 'icon' => 'bi-calendar-check'],
        ['key' => 'documents', 'label' => 'Documents', 'href' => 'document_requests.php', 'icon' => 'bi-file-earmark-text'],
        ['key' => 'events', 'label' => 'Schedules', 'href' => 'events.php', 'icon' => 'bi-calendar-event'],
        ['key' => 'attendance', 'label' => 'Attendance', 'href' => 'attendance.php', 'icon' => 'bi-qr-code-scan'],
        ['key' => 'account', 'label' => 'Account', 'href' => 'account_management.php', 'icon' => 'bi-person'],
        ['key' => 'settings', 'label' => 'Settings', 'href' => 'settings.php', 'icon' => 'bi-gear'],
    ];

    $today = app_now()->setTime(0, 0);
    $year = (int)$today->format('Y');
    $lentStart = new DateTimeImmutable($year . '-02-18');
    $lentEnd = new DateTimeImmutable($year . '-04-02');
    $easterStart = new DateTimeImmutable($year . '-04-03');
    $easterEnd = new DateTimeImmutable($year . '-05-31');
    $adventStart = new DateTimeImmutable($year . '-12-01');
    $adventEnd = new DateTimeImmutable($year . '-12-24');
    $christmasStart = new DateTimeImmutable($year . '-12-01');

    $liturgicalSeason = 'ordinary';
    if ($today >= $lentStart && $today <= $lentEnd) {
        $liturgicalSeason = 'lent';
    } elseif ($today >= $easterStart && $today <= $easterEnd) {
        $liturgicalSeason = 'easter';
    } elseif ($today >= $adventStart && $today <= $adventEnd) {
        $liturgicalSeason = 'christmas';
    } elseif ($today >= $christmasStart || $today <= new DateTimeImmutable($year . '-01-12')) {
        $liturgicalSeason = 'christmas';
    }
    $seasonClass = 'liturgical-' . $liturgicalSeason;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo e($title); ?></title>
        <script>
            (function () {
                var mode = localStorage.getItem('themeMode') || 'system';
                var resolved = mode === 'system'
                    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                    : mode;
                document.documentElement.setAttribute('data-theme', resolved);
                document.documentElement.setAttribute('data-bs-theme', resolved);
            })();
        </script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="app.css?v=<?php echo filemtime(__DIR__ . '/app.css'); ?>">
    </head>
    <body class="<?php echo trim(($isHome ? 'page-home ' : '') . $seasonClass); ?>">
        <?php if ($useGlobalSidebar): ?>
        <button class="btn btn-outline-light app-mobile-menu d-lg-none m-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar">
            <i class="bi bi-list"></i> Menu
        </button>
        <div class="offcanvas offcanvas-start offcanvas-lg app-sidebar-wrap" tabindex="-1" id="appSidebar" aria-labelledby="appSidebarLabel">
            <div class="offcanvas-header d-lg-none">
                <h5 class="offcanvas-title" id="appSidebarLabel">Minor Basilica</h5>
                <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0">
                <aside class="app-sidebar">
                    <div class="app-sidebar-header">
                        <img class="app-sidebar-logo" src="<?php echo e($logoPath); ?>" alt="Basilica Logo">
                        <div class="app-sidebar-title">Minor Basilica</div>
                        <div class="app-sidebar-sub">Portal Navigation</div>
                    </div>
                    <nav class="app-side-nav app-side-main">
                        <?php foreach ($sideLinks as $link): ?>
                            <a class="<?php echo $active === $link['key'] ? 'active' : ''; ?>" href="<?php echo e($link['href']); ?>">
                                <i class="bi <?php echo e($link['icon']); ?>"></i><span><?php echo e($link['label']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                    <div class="app-side-spacer"></div>
                    <nav class="app-side-nav app-side-bottom">
                        <?php if ($isAdminStaff): ?>
                            <a class="<?php echo $active === 'admin' ? 'active' : ''; ?>" href="admin_dashboard.php"><i class="bi bi-speedometer2"></i><span>Admin Dashboard</span></a>
                        <?php endif; ?>
                        <a href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
                    </nav>
                    <?php if ($isLogged): ?>
                        <div class="app-side-meta">
                            <small><?php echo e($user['full_name'] ?: $user['email']); ?></small>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
        <?php endif; ?>
        <main class="app-main py-4">
            <div class="container-fluid app-main-container">
                <?php
                $showTopSearch = $isLogged && $currentPage !== 'index.php';
                if ($showTopSearch):
                ?>
                <div class="app-topbar d-flex justify-content-end align-items-center gap-2 mb-3">
                    <form class="app-search" role="search" method="GET" action="#">
                        <i class="bi bi-search"></i>
                        <input type="search" name="q" placeholder="Search..." aria-label="Search">
                    </form>
                </div>
                <?php endif; ?>
                <?php $hasModulePanel = $isLogged; ?>
                <?php $GLOBALS['__layout_has_module_panel'] = $hasModulePanel; ?>
                <?php if ($hasModulePanel): ?>
                    <div class="module-layout">
                        <aside class="module-left">
                            <div class="module-side rounded-4 p-3 h-100 d-flex flex-column">
                                <div class="module-side-title mb-3">Modules</div>
                                <nav class="d-grid gap-2 module-links">
                                    <?php foreach ($sideLinks as $link): ?>
                                        <a href="<?php echo e($link['href']); ?>" class="<?php echo $active === $link['key'] ? 'active' : ''; ?>">
                                            <i class="bi <?php echo e($link['icon']); ?>"></i>
                                            <span><?php echo e($link['label']); ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                    <?php if ($isAdmin): ?>
                                        <a href="admin_dashboard.php" class="<?php echo $active === 'admin' ? 'active' : ''; ?>">
                                            <i class="bi bi-speedometer2"></i>
                                            <span>Admin Dashboard</span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($isPriest): ?>
                                        <a href="priest_dashboard.php" class="<?php echo $active === 'priest' ? 'active' : ''; ?>">
                                            <i class="bi bi-person-badge"></i>
                                            <span>Priest Dashboard</span>
                                        </a>
                                    <?php endif; ?>
                                </nav>
                                <nav class="d-grid gap-2 module-links module-links-bottom mt-auto pt-3">
                                    <a href="logout.php">
                                        <i class="bi bi-box-arrow-right"></i>
                                        <span>Logout</span>
                                    </a>
                                </nav>
                            </div>
                        </aside>
                        <section class="module-main">
                <?php endif; ?>
                <?php $flash = get_flash(); ?>
                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo e($flash['type']); ?> mb-4"><?php echo e($flash['message']); ?></div>
                <?php endif; ?>
    <?php
}

function render_footer(): void
{
    $hasModulePanel = !empty($GLOBALS['__layout_has_module_panel']);
    ?>
            <?php if ($hasModulePanel): ?>
                        </section>
                    </div>
            <?php endif; ?>
            </div>
        </main>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            (function () {
                var current = window.location.pathname.split('/').pop() || 'index.php';
                var links = document.querySelectorAll('.app-side-nav a');
                links.forEach(function (link) {
                    var href = link.getAttribute('href') || '';
                    if (href === current) {
                        link.classList.add('active');
                        link.setAttribute('aria-current', 'page');
                    }
                });
            })();

            (function () {
                var select = document.getElementById('themeModeSelect');
                if (!select) return;

                function resolveTheme(mode) {
                    if (mode === 'system') {
                        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    }
                    return mode;
                }

                function applyTheme(mode) {
                    var resolved = resolveTheme(mode);
                    document.documentElement.setAttribute('data-theme', resolved);
                    document.documentElement.setAttribute('data-bs-theme', resolved);
                }

                var savedMode = localStorage.getItem('themeMode') || 'system';
                select.value = savedMode;
                applyTheme(savedMode);

                select.addEventListener('change', function () {
                    localStorage.setItem('themeMode', select.value);
                    applyTheme(select.value);
                });

                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
                    if ((localStorage.getItem('themeMode') || 'system') === 'system') {
                        applyTheme('system');
                    }
                });
            })();
        </script>
    </body>
    </html>
    <?php
}
?>
