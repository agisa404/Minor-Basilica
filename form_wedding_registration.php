<?php
require_once __DIR__ . '/request_helpers.php';
require_once __DIR__ . '/layout.php';
$user = require_login();
$logoPath = '612401184_4348220988792023_5812589285034246497_n.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'groom_name' => trim($_POST['groom_name'] ?? ''),
        'groom_address' => trim($_POST['groom_address'] ?? ''),
        'groom_age' => trim($_POST['groom_age'] ?? ''),
        'bride_name' => trim($_POST['bride_name'] ?? ''),
        'bride_address' => trim($_POST['bride_address'] ?? ''),
        'bride_age' => trim($_POST['bride_age'] ?? ''),
        'groom_contact' => trim($_POST['groom_contact'] ?? ''),
        'bride_contact' => trim($_POST['bride_contact'] ?? ''),
        'reservation_fee' => trim($_POST['reservation_fee'] ?? ''),
        'receipt_no' => trim($_POST['receipt_no'] ?? ''),
        'reservation_date' => trim($_POST['reservation_date'] ?? ''),
        'requirements' => $_POST['requirements'] ?? [],
        'other_requirements' => trim($_POST['other_requirements'] ?? ''),
        'notes' => trim($_POST['notes'] ?? ''),
        'book_no' => trim($_POST['book_no'] ?? ''),
        'page_no' => trim($_POST['page_no'] ?? ''),
        'line_no' => trim($_POST['line_no'] ?? ''),
        'amount' => trim($_POST['amount'] ?? ''),
        'minister_of_marriage' => trim($_POST['minister_of_marriage'] ?? ''),
        'received_by' => trim($_POST['received_by'] ?? '')
    ];

    $requestId = create_service_request(
        (int)$user['id'],
        'Wedding Registration',
        'Registration Form for Weddings',
        $data,
        $_POST['wedding_date'] ?? null,
        $_POST['wedding_time'] ?? null
    );

    notify_user((int)$user['id'], 'Wedding registration submitted. Reference #' . $requestId . '.');
    set_flash('success', 'Wedding registration submitted.');
    header('Location: request_success.php?id=' . $requestId);
    exit();
}

render_header('Wedding Registration Form', 'services');
?>
<div class="request-paper-wrap">
    <form method="POST" class="request-paper">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
            <div class="d-flex align-items-center gap-2">
                <img src="<?php echo e($logoPath); ?>" alt="Basilica Logo" style="width:42px;height:42px;border-radius:50%;border:1px solid #a18d6d;">
                <div style="font-weight:700;">Basílica Menor de<br>San Pedro Bautista</div>
            </div>
            <div class="paper-title" style="margin:0;font-size:1.35rem;">REGISTRATION FORM FOR WEDDINGS</div>
        </div>

        <p class="paper-subhead" style="font-size:0.85rem;">
            Instructions: Answer all the blanks accordingly using ALL CAPS. Please write legibly.
            Copy necessary details as it is written in the Certificate of Live Birth of those to be married.
        </p>

        <p class="paper-section">Name of Groom:</p>
        <label class="paper-inline-field"><input class="paper-line-input" type="text" name="groom_name" required></label>
        <div class="row g-2 mb-2">
            <div class="col-md-6"><label class="paper-inline-field">Address: <input class="paper-line-input" type="text" name="groom_address"></label></div>
            <div class="col-md-3"><label class="paper-inline-field">Current Age: <input class="paper-line-input" type="text" name="groom_age"></label></div>
            <div class="col-md-3"><label class="paper-inline-field">Contact Nos.: <input class="paper-line-input" type="text" name="groom_contact"></label></div>
        </div>

        <p class="paper-section">Name of Bride:</p>
        <label class="paper-inline-field"><input class="paper-line-input" type="text" name="bride_name" required></label>
        <div class="row g-2 mb-2">
            <div class="col-md-6"><label class="paper-inline-field">Address: <input class="paper-line-input" type="text" name="bride_address"></label></div>
            <div class="col-md-3"><label class="paper-inline-field">Current Age: <input class="paper-line-input" type="text" name="bride_age"></label></div>
            <div class="col-md-3"><label class="paper-inline-field">Contact Nos.: <input class="paper-line-input" type="text" name="bride_contact"></label></div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-6"><label class="paper-inline-field">Date of Wedding: <input class="paper-line-input paper-date" type="date" name="wedding_date" required></label></div>
            <div class="col-md-6"><label class="paper-inline-field">Time of Wedding: <input class="paper-line-input paper-date" type="time" name="wedding_time" required></label></div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-4"><label class="paper-inline-field">Reservation Fee: <input class="paper-line-input" type="number" step="0.01" name="reservation_fee"></label></div>
            <div class="col-md-4"><label class="paper-inline-field">Official Receipt No.: <input class="paper-line-input" type="text" name="receipt_no"></label></div>
            <div class="col-md-4"><label class="paper-inline-field">Date of Reservation: <input class="paper-line-input paper-date" type="date" name="reservation_date"></label></div>
        </div>

        <hr style="border-color:#756a58;">

        <div class="row g-3">
            <div class="col-md-6">
                <p class="paper-section mb-1">Checklist of Requirements:</p>
                <div class="paper-checklist">
                    <label><input type="checkbox" name="requirements[]" value="Certificate of Live Birth"> Certificate of Live Birth (from the Philippine Statistics Authority)</label>
                    <label><input type="checkbox" name="requirements[]" value="Baptismal Certificate"> Baptismal Certificate</label>
                    <label><input type="checkbox" name="requirements[]" value="Confirmation Certificate"> Confirmation Certificate</label>
                    <label><input type="checkbox" name="requirements[]" value="CENOMAR"> Certificate of No Marriage (CENOMAR)</label>
                    <label><input type="checkbox" name="requirements[]" value="Marriage Banns"> Marriage Banns</label>
                    <label><input type="checkbox" name="requirements[]" value="Marriage License or Affidavit of Cohabitation"> Marriage License or Affidavit of Cohabitation</label>
                    <label><input type="checkbox" name="requirements[]" value="Canonical Interview"> Canonical Interview</label>
                    <label><input type="checkbox" name="requirements[]" value="Pre-Cana Seminar"> Pre-Cana Seminar</label>
                    <label><input type="checkbox" name="requirements[]" value="Marriage Counseling"> Marriage Counseling</label>
                    <label><input type="checkbox" name="requirements[]" value="Confession"> Confession</label>
                </div>
                <label class="paper-inline-field">Others: <input class="paper-line-input" type="text" name="other_requirements"></label>
            </div>
            <div class="col-md-4">
                <p class="paper-section mb-1">Notes:</p>
                <textarea name="notes" rows="8" class="paper-line-input" style="width:100%;border:1px solid #837a6b !important;border-radius:0.2rem !important;padding:0.45rem !important;"></textarea>
            </div>
            <div class="col-md-2">
                <label class="paper-inline-field">Book No. <input class="paper-line-input" type="text" name="book_no"></label>
                <label class="paper-inline-field">Page No. <input class="paper-line-input" type="text" name="page_no"></label>
                <label class="paper-inline-field">Line No. <input class="paper-line-input" type="text" name="line_no"></label>
                <label class="paper-inline-field">Amount <input class="paper-line-input" type="text" name="amount"></label>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-6">
                <label class="paper-inline-field">Minister of Marriage: <input class="paper-line-input" type="text" name="minister_of_marriage"></label>
            </div>
            <div class="col-md-6">
                <label class="paper-inline-field">Received by: <input class="paper-line-input" type="text" name="received_by"></label>
            </div>
        </div>

        <div class="paper-submit">
            <button class="btn btn-warning" type="submit">Submit Form</button>
        </div>
    </form>
</div>
<?php render_footer(); ?>
