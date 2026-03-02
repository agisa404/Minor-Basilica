<?php
require_once __DIR__ . '/request_helpers.php';
require_once __DIR__ . '/layout.php';
$user = require_login();
$logoPath = '612401184_4348220988792023_5812589285034246497_n.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namesText = trim($_POST['names_text'] ?? '');
    $names = $namesText === '' ? [] : preg_split('/\r\n|\r|\n/', $namesText);
    $names = array_values(array_filter(array_map('trim', $names), static fn($item) => $item !== ''));

    $data = [
        'intentions' => $_POST['intention'] ?? [],
        'names' => $names,
        'donor' => trim($_POST['donor'] ?? ''),
        'donation' => trim($_POST['donation'] ?? ''),
        'contact_no' => trim($_POST['contact_no'] ?? ''),
        'notes' => trim($_POST['notes'] ?? ''),
        'official_receipt_no' => trim($_POST['official_receipt_no'] ?? ''),
        'received_by' => trim($_POST['received_by'] ?? ''),
    ];

    $requestId = create_service_request(
        (int)$user['id'],
        'Mass Intentions',
        'Mass Intentions Form',
        $data,
        $_POST['mass_date'] ?? null,
        $_POST['mass_time'] ?? null
    );

    notify_user((int)$user['id'], 'Mass intentions request submitted. Reference #' . $requestId . '.');
    set_flash('success', 'Mass intentions request submitted.');
    header('Location: request_success.php?id=' . $requestId);
    exit();
}

render_header('Mass Intentions Form', 'services');
?>
<div class="request-paper-wrap">
    <form method="POST" class="request-paper">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
            <div class="d-flex align-items-center gap-2">
                <img src="<?php echo e($logoPath); ?>" alt="Basilica Logo" style="width:42px;height:42px;border-radius:50%;border:1px solid #a18d6d;">
                <div style="font-weight:700;">Basílica Menor de<br>San Pedro Bautista</div>
            </div>
            <div class="paper-title" style="margin:0;font-size:1.3rem;">MASS INTENTIONS FORM</div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-6"><label class="paper-inline-field">Date of Mass: <input class="paper-line-input paper-date" type="date" name="mass_date" required></label></div>
            <div class="col-md-6"><label class="paper-inline-field">Time of Mass: <input class="paper-line-input paper-date" type="time" name="mass_time" required></label></div>
        </div>

        <p class="paper-section mb-1">Mass Intention Type:</p>
        <div class="paper-checklist mb-2">
            <label><input type="checkbox" name="intention[]" value="Thanksgiving"> Thanksgiving</label>
            <label><input type="checkbox" name="intention[]" value="Birthday"> Birthday</label>
            <label><input type="checkbox" name="intention[]" value="Special Intentions"> Special Intentions</label>
            <label><input type="checkbox" name="intention[]" value="Healing"> Healing</label>
            <label><input type="checkbox" name="intention[]" value="Souls"> Souls</label>
            <label><input type="checkbox" name="intention[]" value="Death Anniversary"> Death Anniversary</label>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-6"><label class="paper-inline-field">Donor / Requestor: <input class="paper-line-input" type="text" name="donor" required></label></div>
            <div class="col-md-6"><label class="paper-inline-field">Contact No.: <input class="paper-line-input" type="text" name="contact_no"></label></div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-6"><label class="paper-inline-field">Amount of Donation: <input class="paper-line-input" type="number" step="0.01" min="0" name="donation"></label></div>
            <div class="col-md-6"><label class="paper-inline-field">Official Receipt No.: <input class="paper-line-input" type="text" name="official_receipt_no"></label></div>
        </div>

        <p class="paper-section mb-1">Name(s) to include in Mass Intention (one per line):</p>
        <textarea class="paper-line-input" name="names_text" rows="7" style="width:100%;border:1px solid #837a6b !important;border-radius:0.2rem !important;padding:0.45rem !important;" placeholder="Name 1&#10;Name 2"></textarea>

        <label class="paper-inline-field mt-2">Notes: <input class="paper-line-input" type="text" name="notes"></label>
        <label class="paper-inline-field">Received by: <input class="paper-line-input" type="text" name="received_by"></label>

        <div class="paper-submit">
            <button class="btn btn-warning" type="submit">Submit Form</button>
        </div>
    </form>
</div>
<?php render_footer(); ?>
