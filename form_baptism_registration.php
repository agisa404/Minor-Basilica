<?php
require_once __DIR__ . '/request_helpers.php';
require_once __DIR__ . '/layout.php';
$user = require_login();
$logoPath = '612401184_4348220988792023_5812589285034246497_n.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name_of_child' => trim($_POST['name_of_child'] ?? ''),
        'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
        'place_of_birth' => trim($_POST['place_of_birth'] ?? ''),
        'gender' => trim($_POST['gender'] ?? ''),
        'current_age' => trim($_POST['current_age'] ?? ''),
        'father_name' => trim($_POST['father_name'] ?? ''),
        'mother_maiden_name' => trim($_POST['mother_maiden_name'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'contact_nos' => trim($_POST['contact_nos'] ?? ''),
        'full_name_of_sponsors' => trim($_POST['full_name_of_sponsors'] ?? ''),
        'sponsors_address' => trim($_POST['sponsors_address'] ?? ''),
        'additional_sponsors' => trim($_POST['additional_sponsors'] ?? ''),
        'additional_sponsors_address' => trim($_POST['additional_sponsors_address'] ?? ''),
        'requirements' => $_POST['requirements'] ?? [],
        'notes' => trim($_POST['notes'] ?? ''),
        'date_registered' => trim($_POST['date_registered'] ?? ''),
        'book_no' => trim($_POST['book_no'] ?? ''),
        'page_no' => trim($_POST['page_no'] ?? ''),
        'line_no' => trim($_POST['line_no'] ?? ''),
        'amount' => trim($_POST['amount'] ?? ''),
        'received_by' => trim($_POST['received_by'] ?? ''),
        'minister_of_baptism' => trim($_POST['minister_of_baptism'] ?? ''),
    ];

    $requestId = create_service_request(
        (int)$user['id'],
        'Baptism Registration',
        'Registration Form for Baptism',
        $data,
        trim($_POST['date_of_baptism'] ?? ''),
        trim($_POST['time_of_baptism'] ?? '')
    );

    notify_user((int)$user['id'], 'Baptism registration submitted. Reference #' . $requestId . '.');
    set_flash('success', 'Baptism registration submitted.');
    header('Location: request_success.php?id=' . $requestId);
    exit();
}

render_header('Baptism Registration Form', 'services');
?>
<div class="request-paper-wrap">
    <form method="POST" class="request-paper">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
            <div class="d-flex align-items-center gap-2">
                <img src="<?php echo e($logoPath); ?>" alt="Basilica Logo" style="width:42px;height:42px;border-radius:50%;border:1px solid #a18d6d;">
                <div style="font-weight:700;">Basílica Menor de<br>San Pedro Bautista</div>
            </div>
            <div style="text-align:right;">
                <div class="paper-title" style="margin:0;font-size:1.15rem;">REGISTRATION FORM FOR BAPTISM</div>
                <label class="paper-inline-field" style="justify-content:flex-end;">Date of Baptism: <input class="paper-line-input paper-date" type="date" name="date_of_baptism"></label>
                <label class="paper-inline-field" style="justify-content:flex-end;">Time: <input class="paper-line-input paper-date" type="time" name="time_of_baptism"></label>
            </div>
        </div>

        <p class="paper-subhead" style="font-size:0.85rem;">
            Instructions: Answer all the blanks accordingly using ALL CAPS. Please write legibly.
            Copy necessary details as it is written in the Certificate of Live Birth of the one to be baptized.
        </p>

        <p class="paper-section">Name of Child:</p>
        <label class="paper-inline-field"><input class="paper-line-input" type="text" name="name_of_child" required></label>

        <div class="row g-2 mb-2">
            <div class="col-md-4"><label class="paper-inline-field">Date of Birth: <input class="paper-line-input paper-date" type="date" name="date_of_birth"></label></div>
            <div class="col-md-4"><label class="paper-inline-field">Place of Birth: <input class="paper-line-input" type="text" name="place_of_birth"></label></div>
            <div class="col-md-2"><label class="paper-inline-field">Gender: <input class="paper-line-input" type="text" name="gender"></label></div>
            <div class="col-md-2"><label class="paper-inline-field">Current Age: <input class="paper-line-input" type="text" name="current_age"></label></div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-6"><label class="paper-inline-field">Father's Name: <input class="paper-line-input" type="text" name="father_name"></label></div>
            <div class="col-md-6"><label class="paper-inline-field">Mother's Maiden Name: <input class="paper-line-input" type="text" name="mother_maiden_name"></label></div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-8"><label class="paper-inline-field">Address: <input class="paper-line-input" type="text" name="address"></label></div>
            <div class="col-md-4"><label class="paper-inline-field">Contact Nos.: <input class="paper-line-input" type="text" name="contact_nos"></label></div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-6"><label class="paper-inline-field">Full Name of Sponsors: <input class="paper-line-input" type="text" name="full_name_of_sponsors"></label></div>
            <div class="col-md-6"><label class="paper-inline-field">Address: <input class="paper-line-input" type="text" name="sponsors_address"></label></div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-6"><label class="paper-inline-field">Additional Sponsors: <input class="paper-line-input" type="text" name="additional_sponsors"></label></div>
            <div class="col-md-6"><label class="paper-inline-field">Address: <input class="paper-line-input" type="text" name="additional_sponsors_address"></label></div>
        </div>

        <hr style="border-color:#756a58;">

        <div class="row g-3">
            <div class="col-md-5">
                <p class="paper-section mb-1">Checklist of Requirements:</p>
                <div class="paper-checklist">
                    <label><input type="checkbox" name="requirements[]" value="Certificate of Live Birth"> Certificate of Live Birth (from PSA/local Civil Registrar, with Registry Number)</label>
                    <label><input type="checkbox" name="requirements[]" value="Marriage Certificate of Parents"> Marriage Certificate of Parents (if married)</label>
                    <label><input type="checkbox" name="requirements[]" value="Permit for Baptism"> Permit for Baptism (for those living outside parish jurisdiction)</label>
                    <label><input type="checkbox" name="requirements[]" value="Certificate of No Record"> Certificate of No Record (from nearby parishes)</label>
                </div>
            </div>
            <div class="col-md-4">
                <p class="paper-section mb-1">Notes:</p>
                <textarea name="notes" rows="7" class="paper-line-input" style="width:100%;border:1px solid #837a6b !important;border-radius:0.2rem !important;padding:0.45rem !important;"></textarea>
            </div>
            <div class="col-md-3">
                <label class="paper-inline-field">Date Registered: <input class="paper-line-input paper-date" type="date" name="date_registered"></label>
                <label class="paper-inline-field">Book No. <input class="paper-line-input" type="text" name="book_no"></label>
                <label class="paper-inline-field">Page No. <input class="paper-line-input" type="text" name="page_no"></label>
                <label class="paper-inline-field">Line No. <input class="paper-line-input" type="text" name="line_no"></label>
                <label class="paper-inline-field">Amount <input class="paper-line-input" type="text" name="amount"></label>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-md-6">
                <label class="paper-inline-field">Received by: <input class="paper-line-input" type="text" name="received_by"></label>
            </div>
            <div class="col-md-6">
                <label class="paper-inline-field">Minister of Baptism: <input class="paper-line-input" type="text" name="minister_of_baptism"></label>
            </div>
        </div>

        <div class="paper-submit">
            <button class="btn btn-warning" type="submit">Submit Form</button>
        </div>
    </form>
</div>
<?php render_footer(); ?>
