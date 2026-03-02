<?php
require_once __DIR__ . '/request_helpers.php';
require_once __DIR__ . '/layout.php';
$user = require_login();
$logoPath = '612401184_4348220988792023_5812589285034246497_n.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'certificate_types' => $_POST['certificate_types'] ?? [],
        'others' => trim($_POST['others'] ?? ''),
        'name' => trim($_POST['name'] ?? ''),
        'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
        'date_of_baptism' => trim($_POST['date_of_baptism'] ?? ''),
        'date_of_confirmation' => trim($_POST['date_of_confirmation'] ?? ''),
        'date_of_marriage' => trim($_POST['date_of_marriage'] ?? ''),
        'father_name' => trim($_POST['father_name'] ?? ''),
        'mother_name' => trim($_POST['mother_name'] ?? ''),
        'purpose' => trim($_POST['purpose'] ?? ''),
        'requested_by' => trim($_POST['requested_by'] ?? ''),
        'purpose_for_marriage' => trim($_POST['purpose_for_marriage'] ?? ''),
        'name_of_bride_or_groom' => trim($_POST['name_of_bride_or_groom'] ?? ''),
        'date_of_wedding' => trim($_POST['date_of_wedding'] ?? ''),
        'church_of_wedding' => trim($_POST['church_of_wedding'] ?? '')
    ];

    $requestId = create_service_request(
        (int)$user['id'],
        'Baptismal Request',
        'Request Form for Baptismal, Confirmation, Marriage and Other Certification',
        $data
    );

    notify_user((int)$user['id'], 'Baptismal request submitted. Reference #' . $requestId . '.');
    set_flash('success', 'Baptismal request submitted.');
    header('Location: request_success.php?id=' . $requestId);
    exit();
}

render_header('Baptismal Request Form', 'services');
?>
<div class="request-paper-wrap">
    <form method="POST" class="request-paper">
        <div class="paper-header">
            <div class="paper-brand">
                <img class="paper-brand-logo" src="<?php echo e($logoPath); ?>" alt="Basilica Logo">
                <div>Basilica Menor de<br>San Pedro Bautista</div>
            </div>
            <h3 class="paper-title" style="margin:0;font-size:1.12rem;">REQUEST FORM FOR BAPTISMAL, CONFIRMATION, MARRIAGE AND OTHER CERTIFICATION</h3>
        </div>

        <p class="paper-section">Please check certificate to be requested:</p>
        <div class="paper-checklist">
            <label><input type="checkbox" name="certificate_types[]" value="Certificate of Baptism (Binyag)" checked> Certificate of Baptism (Binyag)</label>
            <label><input type="checkbox" name="certificate_types[]" value="Certificate of Confirmation (Kumpil)"> Certificate of Confirmation (Kumpil)</label>
            <label><input type="checkbox" name="certificate_types[]" value="Certificate of Marriage (Kasal)"> Certificate of Marriage (Kasal)</label>
            <label><input type="checkbox" name="certificate_types[]" value="Certification of No Record"> Certification of No Record</label>
            <label><input type="checkbox" name="certificate_types[]" value="Permission for Baptism"> Permission for Baptism</label>
            <label class="paper-inline-field">Others: <input class="paper-line-input" type="text" name="others"></label>
        </div>

        <p class="paper-section">Please provide the following details:</p>
        <label class="paper-inline-field">Name: <input class="paper-line-input" type="text" name="name" required> Date of Birth: <input class="paper-line-input paper-date" type="date" name="date_of_birth"></label>

        <p class="paper-subhead">Date of:</p>
        <label class="paper-inline-field paper-bullet">Baptism <input class="paper-line-input paper-date" type="date" name="date_of_baptism"></label>
        <label class="paper-inline-field paper-bullet">Confirmation <input class="paper-line-input paper-date" type="date" name="date_of_confirmation"></label>
        <label class="paper-inline-field paper-bullet">Marriage <input class="paper-line-input paper-date" type="date" name="date_of_marriage"></label>

        <label class="paper-inline-field">Father's Name: <input class="paper-line-input" type="text" name="father_name"></label>
        <label class="paper-inline-field">Mother's Name: <input class="paper-line-input" type="text" name="mother_name"></label>
        <label class="paper-inline-field">Purpose: <input class="paper-line-input" type="text" name="purpose"></label>
        <label class="paper-inline-field">Requested by: <input class="paper-line-input" type="text" name="requested_by"></label>
        <label class="paper-inline-field">Purpose: <input class="paper-line-input" type="text" name="purpose_for_marriage"></label>

        <p class="paper-subhead">For certifications for Marriage purposes:</p>
        <label class="paper-inline-field">Name of Bride or Groom: <input class="paper-line-input" type="text" name="name_of_bride_or_groom"></label>
        <label class="paper-inline-field">Date of Wedding: <input class="paper-line-input paper-date" type="date" name="date_of_wedding"></label>
        <label class="paper-inline-field">Church of Wedding: <input class="paper-line-input" type="text" name="church_of_wedding"></label>

        <div class="paper-submit">
            <button class="btn btn-warning" type="submit">Submit Form</button>
        </div>
    </form>
</div>
<?php render_footer(); ?>
