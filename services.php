<?php
require_once __DIR__ . '/layout.php';
$user = current_user();
render_header('Services', 'services');
?>
<h2 class="mb-3">Services</h2>
<p class="text-secondary mb-4">Submit your request forms online. After submission, a printable page is provided.</p>
<div class="d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-outline-light" href="filled_forms.php">View Filled Forms</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-4"><a class="card h-100 text-decoration-none bg-dark border-warning-subtle" href="form_mass_intentions.php"><div class="card-body"><h5 class="text-warning">Mass Intentions Form</h5><p>Request mass intentions and preferred schedule.</p></div></a></div>
    <div class="col-md-6 col-xl-4"><a class="card h-100 text-decoration-none bg-dark border-warning-subtle" href="form_baptism_registration.php"><div class="card-body"><h5 class="text-warning">Baptism Registration</h5><p>Registration form for baptism with sponsors and requirements checklist.</p></div></a></div>
    <div class="col-md-6 col-xl-4"><a class="card h-100 text-decoration-none bg-dark border-warning-subtle" href="form_baptismal_request.php"><div class="card-body"><h5 class="text-warning">Request Form for Baptismal</h5><p>Request form for baptismal, confirmation, marriage and other certification.</p></div></a></div>
    <div class="col-md-6 col-xl-4"><a class="card h-100 text-decoration-none bg-dark border-warning-subtle" href="form_confirmation_registration.php"><div class="card-body"><h5 class="text-warning">Confirmation Registration</h5><p>Online registration for confirmation.</p></div></a></div>
    <div class="col-md-6 col-xl-4"><a class="card h-100 text-decoration-none bg-dark border-warning-subtle" href="form_holy_communion.php"><div class="card-body"><h5 class="text-warning">Holy Communion Registration</h5><p>Registration form for Holy Communion with requirements checklist.</p></div></a></div>
    <div class="col-md-6 col-xl-4"><a class="card h-100 text-decoration-none bg-dark border-warning-subtle" href="form_wedding_registration.php"><div class="card-body"><h5 class="text-warning">Wedding Registration</h5><p>Wedding booking and requirements checklist.</p></div></a></div>
    <div class="col-md-6 col-xl-4"><a class="card h-100 text-decoration-none bg-dark border-warning-subtle" href="form_facilities_reservation.php"><div class="card-body"><h5 class="text-warning">Facilities Reservation Form</h5><p>Reserve parish facilities, indicate charges, and agree to usage conditions.</p></div></a></div>
    <div class="col-md-6 col-xl-4"><a class="card h-100 text-decoration-none bg-dark border-warning-subtle" href="form_outside_basilica_request.php"><div class="card-body"><h5 class="text-warning">Outside Basilica Request</h5><p>Sacraments and liturgical activities outside the basilica.</p></div></a></div>
</div>

<?php if (!$user): ?>
    <div class="alert alert-warning">You can view forms, but you must <a href="account_management.php" class="alert-link">login</a> to submit requests.</div>
<?php endif; ?>
<?php render_footer(); ?>
