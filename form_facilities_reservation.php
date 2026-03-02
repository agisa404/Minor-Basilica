<?php
require_once __DIR__ . '/request_helpers.php';
require_once __DIR__ . '/layout.php';
$user = require_login();
$logoPath = '612401184_4348220988792023_5812589285034246497_n.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'requesting_group' => trim($_POST['requesting_group'] ?? ''),
        'name_of_head' => trim($_POST['name_of_head'] ?? ''),
        'authorized_representative' => trim($_POST['authorized_representative'] ?? ''),
        'contact_nos' => trim($_POST['contact_nos'] ?? ''),
        'purpose_activity' => trim($_POST['purpose_activity'] ?? ''),
        'participants' => trim($_POST['participants'] ?? ''),
        'facilities' => $_POST['facilities'] ?? [],
        'charges' => trim($_POST['charges'] ?? ''),
        'holy_cave_purposes' => $_POST['holy_cave_purposes'] ?? [],
        'equipment_needed' => trim($_POST['equipment_needed'] ?? ''),
        'total_charges' => trim($_POST['total_charges'] ?? ''),
        'terms_accepted' => isset($_POST['terms_accepted']) ? 'yes' : 'no',
        'signed_date' => trim($_POST['signed_date'] ?? ''),
        'notes' => trim($_POST['notes'] ?? ''),
        'book_no' => trim($_POST['book_no'] ?? ''),
        'page_no' => trim($_POST['page_no'] ?? ''),
        'line_no' => trim($_POST['line_no'] ?? ''),
        'amount' => trim($_POST['amount'] ?? ''),
        'official_receipt_no' => trim($_POST['official_receipt_no'] ?? ''),
        'date_of_payment' => trim($_POST['date_of_payment'] ?? ''),
        'endorsed_by' => trim($_POST['endorsed_by'] ?? ''),
        'approved_by' => trim($_POST['approved_by'] ?? ''),
    ];

    $requestId = create_service_request(
        (int)$user['id'],
        'Facilities Reservation',
        'Facilities Reservation Form',
        $data,
        trim($_POST['date'] ?? ''),
        trim($_POST['time'] ?? '')
    );

    notify_user((int)$user['id'], 'Facilities reservation submitted. Reference #' . $requestId . '.');
    set_flash('success', 'Facilities reservation submitted.');
    header('Location: request_success.php?id=' . $requestId);
    exit();
}

render_header('Facilities Reservation Form', 'services');
?>
<div class="request-paper-wrap">
    <form method="POST" class="request-paper">
        <div class="paper-header">
            <div class="paper-brand">
                <img class="paper-brand-logo" src="<?php echo e($logoPath); ?>" alt="Basilica Logo">
                <div>Basilica Menor de<br>San Pedro Bautista</div>
            </div>
            <h3 class="paper-title" style="margin:0;font-size:1.25rem;">FACILITIES RESERVATION FORM</h3>
        </div>
        <p class="paper-subhead">Santuario de San Pedro Bautista Parish - 69 San Pedro Bautista St., San Francisco del Monte, Quezon City</p>

        <label class="paper-inline-field">Requesting Ministry/Sub-ministry/Organization/Group: <input class="paper-line-input" type="text" name="requesting_group"></label>

        <div class="row g-2 mb-2">
            <div class="col-md-4"><label class="paper-inline-field">Name of Animator/Coordinator/Head: <input class="paper-line-input" type="text" name="name_of_head"></label></div>
            <div class="col-md-4"><label class="paper-inline-field">Authorized Representative: <input class="paper-line-input" type="text" name="authorized_representative"></label></div>
            <div class="col-md-4"><label class="paper-inline-field">Contact Nos.: <input class="paper-line-input" type="text" name="contact_nos"></label></div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-4"><label class="paper-inline-field">Purpose/Activity: <input class="paper-line-input" type="text" name="purpose_activity"></label></div>
            <div class="col-md-2"><label class="paper-inline-field">Date: <input class="paper-line-input paper-date" type="date" name="date"></label></div>
            <div class="col-md-3"><label class="paper-inline-field">Time (start-end): <input class="paper-line-input" type="text" name="time"></label></div>
            <div class="col-md-3"><label class="paper-inline-field">No. of Participants: <input class="paper-line-input" type="text" name="participants"></label></div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <p class="paper-section mb-1">List of Facilities (Check the ones to be used)</p>
                <div class="paper-checklist">
                    <label><input type="checkbox" name="facilities[]" value="St. Francis of Assisi Hall (2nd Floor)"> St. Francis of Assisi Hall (2nd Floor)</label>
                    <label><input type="checkbox" name="facilities[]" value="St. Peter of Alcantara (Peach Room)"> St. Peter of Alcantara (Peach Room)</label>
                    <label><input type="checkbox" name="facilities[]" value="St. Margaret of Cortona (Green Room)"> St. Margaret of Cortona (Green Room)</label>
                    <label><input type="checkbox" name="facilities[]" value="St. Louis IX (Blue Room)"> St. Louis IX (Blue Room)</label>
                </div>

                <p class="paper-subhead mb-1">Parish Church Compound</p>
                <div class="paper-checklist">
                    <label><input type="checkbox" name="facilities[]" value="Main Church"> Main Church</label>
                    <label><input type="checkbox" name="holy_cave_purposes[]" value="Mass"> Mass</label>
                    <label><input type="checkbox" name="holy_cave_purposes[]" value="Talks/Recollections"> Talks/Recollections</label>
                    <label><input type="checkbox" name="facilities[]" value="Holy Cave"> Holy Cave</label>
                    <label><input type="checkbox" name="facilities[]" value="Portiuncula Formation and Renewal Hall"> Portiuncula Formation and Renewal Hall</label>
                    <label><input type="checkbox" name="facilities[]" value="Brother Sun Sister Moon Garden"> Brother Sun Sister Moon Garden</label>
                    <label><input type="checkbox" name="facilities[]" value="San Damiano Garden"> San Damiano Garden</label>
                    <label><input type="checkbox" name="facilities[]" value="Chamber Room"> Chamber Room</label>
                </div>

                <label class="paper-inline-field">Charges: <input class="paper-line-input" type="text" name="charges"></label>
                <label class="paper-inline-field">Equipment/Items Needed: <input class="paper-line-input" type="text" name="equipment_needed"></label>
                <label class="paper-inline-field">Total Charges: <input class="paper-line-input" type="text" name="total_charges"></label>
            </div>

            <div class="col-md-6">
                <p class="paper-section mb-1">Terms and Conditions</p>
                <ol style="margin:0;padding-left:1.15rem;color:#191919;">
                    <li>The time of usage of parish facilities should be up to 10:00 PM only.</li>
                    <li>Programs must end before the time limit to give enough time for packing and cleaning.</li>
                    <li>Reservations are honored with endorsed forms approved by Parish Priest.</li>
                    <li>Activities should support major parish initiatives.</li>
                    <li>Users must maintain cleanliness and orderliness.</li>
                    <li>All fees/donations must be given to the Parish Office before use.</li>
                    <li>Requesting parties are liable for any damage due to misuse.</li>
                </ol>
                <label class="paper-inline-field mt-2"><input type="checkbox" name="terms_accepted" value="1"> I agree to observe the terms and conditions stated above.</label>
                <label class="paper-inline-field">Signed this date: <input class="paper-line-input paper-date" type="date" name="signed_date"></label>

                <label class="paper-inline-field mt-2">Notes: <input class="paper-line-input" type="text" name="notes"></label>
                <label class="paper-inline-field">Book No. <input class="paper-line-input" type="text" name="book_no"></label>
                <label class="paper-inline-field">Page No. <input class="paper-line-input" type="text" name="page_no"></label>
                <label class="paper-inline-field">Line No. <input class="paper-line-input" type="text" name="line_no"></label>
                <label class="paper-inline-field">Amount <input class="paper-line-input" type="text" name="amount"></label>
            </div>
        </div>

        <hr style="border-color:#756a58;">
        <div class="row g-2">
            <div class="col-md-6"><label class="paper-inline-field">Official Receipt No. <input class="paper-line-input" type="text" name="official_receipt_no"></label></div>
            <div class="col-md-6"><label class="paper-inline-field">Date of Payment <input class="paper-line-input paper-date" type="date" name="date_of_payment"></label></div>
        </div>
        <div class="row g-2 mt-1">
            <div class="col-md-6"><label class="paper-inline-field">Endorsed by (Parish Staff): <input class="paper-line-input" type="text" name="endorsed_by"></label></div>
            <div class="col-md-6"><label class="paper-inline-field">Approved by (Parish Priest / Rector): <input class="paper-line-input" type="text" name="approved_by"></label></div>
        </div>

        <div class="paper-submit">
            <button class="btn btn-warning" type="submit">Submit Form</button>
        </div>
    </form>
</div>
<?php render_footer(); ?>
