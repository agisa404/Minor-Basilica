<?php
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/request_exact_renderer.php';
$user = require_login();

$requestId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($requestId <= 0) {
    set_flash('danger', 'Invalid request reference.');
    header('Location: services.php');
    exit();
}

$stmt = $conn->prepare('SELECT r.*, u.full_name, u.email FROM service_requests r JOIN users u ON u.id = r.user_id WHERE r.id = ? LIMIT 1');
$stmt->bind_param('i', $requestId);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$request) {
    set_flash('danger', 'Request not found.');
    header('Location: services.php');
    exit();
}

if ((int)$request['user_id'] !== (int)$user['id'] && !is_admin_or_staff($user)) {
    set_flash('danger', 'You do not have permission to view that request.');
    header('Location: services.php');
    exit();
}

$details = json_decode((string)$request['details'], true);
if (!is_array($details)) {
    $details = ['details' => (string)$request['details']];
}
$queueTicket = 'Q-' . date('Ymd', strtotime((string)$request['created_at'])) . '-' . str_pad((string)$request['id'], 6, '0', STR_PAD_LEFT);

render_header('Request Submitted', 'services');
?>
<?php render_exact_request_form($request, $details); ?>
<div class="alert alert-info mt-3 mb-0">
    <strong>Queue Ticket Number:</strong> <?php echo e($queueTicket); ?>
</div>
<div class="d-flex flex-wrap gap-2 mt-3">
    <button class="btn btn-warning" type="button" onclick="printQueueTicket('<?php echo e($queueTicket); ?>', '<?php echo e($request['title']); ?>', '<?php echo (int)$request['id']; ?>')">Print Queue Ticket</button>
    <a class="btn btn-outline-info" href="request_pdf.php?id=<?php echo (int)$request['id']; ?>&view=1" target="_blank" rel="noopener noreferrer">View PDF</a>
    <a class="btn btn-outline-info" href="request_pdf.php?id=<?php echo (int)$request['id']; ?>">Download PDF</a>
    <a class="btn btn-outline-light" href="filled_form_view.php?id=<?php echo (int)$request['id']; ?>">Full View</a>
    <a class="btn btn-outline-light" href="services.php">Back to Services</a>
    <?php if (is_admin_or_staff($user)): ?>
        <a class="btn btn-outline-light" href="admin_dashboard.php">Open Admin Dashboard</a>
    <?php endif; ?>
</div>
<script>
function printQueueTicket(ticketNumber, formTitle, referenceId) {
    var popup = window.open('', '_blank', 'width=420,height=620');
    if (!popup) return;
    var now = new Date();
    var issuedAt = now.toLocaleString();
    popup.document.write('<html><head><title>Queue Ticket</title><style>body{font-family:Arial,sans-serif;padding:24px;} .box{border:2px dashed #222;padding:18px;border-radius:10px;} h2{margin:0 0 14px;} .n{font-size:28px;font-weight:700;margin:10px 0;} .meta{margin:6px 0;font-size:14px;}</style></head><body>');
    popup.document.write('<div class="box">');
    popup.document.write('<h2>Queue Ticket</h2>');
    popup.document.write('<div class="n">' + ticketNumber + '</div>');
    popup.document.write('<div class="meta"><strong>Reference:</strong> #' + referenceId + '</div>');
    popup.document.write('<div class="meta"><strong>Form:</strong> ' + formTitle + '</div>');
    popup.document.write('<div class="meta"><strong>Issued:</strong> ' + issuedAt + '</div>');
    popup.document.write('</div></body></html>');
    popup.document.close();
    popup.focus();
    popup.print();
}
</script>
<?php render_footer(); ?>
