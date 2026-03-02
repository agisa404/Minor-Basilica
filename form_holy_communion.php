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
        'date_of_baptism' => trim($_POST['date_of_baptism'] ?? ''),
        'church_parish_of_baptism' => trim($_POST['church_parish_of_baptism'] ?? ''),
        'father_name' => trim($_POST['father_name'] ?? ''),
        'mother_maiden_name' => trim($_POST['mother_maiden_name'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'parish' => trim($_POST['parish'] ?? ''),
        'contact_nos' => trim($_POST['contact_nos'] ?? ''),
        'requirements' => $_POST['requirements'] ?? [],
        'others' => trim($_POST['others'] ?? ''),
        'notes' => trim($_POST['notes'] ?? ''),
        'book_no' => trim($_POST['book_no'] ?? ''),
        'page_no' => trim($_POST['page_no'] ?? ''),
        'line_no' => trim($_POST['line_no'] ?? ''),
        'signature_of_catechist' => trim($_POST['signature_of_catechist'] ?? ''),
    ];

    $holyDate = trim($_POST['date_of_holy_communion'] ?? '');
    $holyTime = trim($_POST['time_of_holy_communion'] ?? '');

    $requestId = create_service_request(
        (int)$user['id'],
        'Holy Communion Registration',
        'Registration Form for Holy Communion',
        $data,
        $holyDate !== '' ? $holyDate : null,
        $holyTime !== '' ? $holyTime : null
    );

    notify_user((int)$user['id'], 'Holy Communion registration submitted. Reference #' . $requestId . '.');
    set_flash('success', 'Holy Communion registration submitted.');
    header('Location: request_success.php?id=' . $requestId);
    exit();
}

render_header('Holy Communion Registration Form', 'services');
?>
<div class="request-paper-wrap">
    <form method="POST" class="request-paper">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
            <div class="d-flex align-items-center gap-2">
                <img src="<?php echo e($logoPath); ?>" alt="Basilica Logo" style="width:42px;height:42px;border-radius:50%;border:1px solid #a18d6d;">
                <div style="font-weight:700;">Basílica Menor de<br>San Pedro Bautista</div>
            </div>
            <div style="text-align:right;">
                <div class="paper-title" style="margin:0;font-size:1.15rem;">REGISTRATION FORM FOR HOLY COMMUNION</div>
                <label class="paper-inline-field" style="justify-content:flex-end;">Date of Holy Communion: <input class="paper-line-input paper-date" type="date" name="date_of_holy_communion"></label>
                <label class="paper-inline-field" style="justify-content:flex-end;">Time: <input class="paper-line-input paper-date" type="time" name="time_of_holy_communion"></label>
            </div>
        </div>

        <p class="paper-section">Name of Child:</p>
        <label class="paper-inline-field"><input class="paper-line-input" type="text" name="name_of_child" required></label>

        <div class="row g-2 mb-2">
            <div class="col-md-4"><label class="paper-inline-field">Date of Birth: <input class="paper-line-input paper-date" type="date" name="date_of_birth"></label></div>
            <div class="col-md-4"><label class="paper-inline-field">Place of Birth: <input class="paper-line-input" type="text" name="place_of_birth"></label></div>
            <div class="col-md-2"><label class="paper-inline-field">Gender: <input class="paper-line-input" type="text" name="gender"></label></div>
            <div class="col-md-2"><label class="paper-inline-field">Current Age: <input class="paper-line-input" type="text" name="current_age"></label></div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-6"><label class="paper-inline-field">Date of Baptism: <input class="paper-line-input paper-date" type="date" name="date_of_baptism"></label></div>
            <div class="col-md-6"><label class="paper-inline-field">Church/Parish of Baptism: <input class="paper-line-input" type="text" name="church_parish_of_baptism"></label></div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-6"><label class="paper-inline-field">Father's Name: <input class="paper-line-input" type="text" name="father_name"></label></div>
            <div class="col-md-6"><label class="paper-inline-field">Mother's Maiden Name: <input class="paper-line-input" type="text" name="mother_maiden_name"></label></div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-6"><label class="paper-inline-field">Address: <input class="paper-line-input" type="text" name="address"></label></div>
            <div class="col-md-3"><label class="paper-inline-field">Parish: <input class="paper-line-input" type="text" name="parish"></label></div>
            <div class="col-md-3"><label class="paper-inline-field">Contact Nos.: <input class="paper-line-input" type="text" name="contact_nos"></label></div>
        </div>

        <hr style="border-color:#756a58;">

        <div class="row g-3">
            <div class="col-md-5">
                <p class="paper-section mb-1">Checklist of Requirements:</p>
                <div class="paper-checklist">
                    <label><input type="checkbox" name="requirements[]" value="Baptismal Certificate"> Baptismal Cert. original with annotation for 1st communion purposes</label>
                    <label><input type="checkbox" name="requirements[]" value="Parents Seminar"> Parents seminar (no parents seminar no 1st communion)</label>
                    <label><input type="checkbox" name="requirements[]" value="Practices"> Practices</label>
                    <label><input type="checkbox" name="requirements[]" value="Confession"> Confession</label>
                    <label><input type="checkbox" name="requirements[]" value="Others"> Others</label>
                </div>
                <label class="paper-inline-field">Others: <input class="paper-line-input" type="text" name="others"></label>
            </div>
            <div class="col-md-4">
                <p class="paper-section mb-1">Notes:</p>
                <textarea name="notes" rows="6" class="paper-line-input" style="width:100%;border:1px solid #837a6b !important;border-radius:0.2rem !important;padding:0.45rem !important;"></textarea>
            </div>
            <div class="col-md-3">
                <label class="paper-inline-field">Book No. <input class="paper-line-input" type="text" name="book_no"></label>
                <label class="paper-inline-field">Page No. <input class="paper-line-input" type="text" name="page_no"></label>
                <label class="paper-inline-field">Line No. <input class="paper-line-input" type="text" name="line_no"></label>
            </div>
        </div>

        <label class="paper-inline-field mt-3">Signature of Catechist: <input class="paper-line-input" type="text" name="signature_of_catechist"></label>

        <div class="paper-submit">
            <button class="btn btn-warning" type="submit">Submit Form</button>
        </div>
    </form>
</div>
<?php render_footer(); ?>
