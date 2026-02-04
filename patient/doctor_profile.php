<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['patient_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit;
}

if(!isset($_GET['id'])){
    echo "Invalid request.";
    exit;
}

$doctor_id = intval($_GET['id']);
$res = $conn->query("SELECT * FROM doctors WHERE doctor_id=$doctor_id AND status='active'");
if($res->num_rows !== 1){
    echo "Doctor not found.";
    exit;
}
$doc = $res->fetch_assoc();

// Fetch availability
$avail_res = $conn->query("SELECT * FROM doctor_availability WHERE doctor_id=$doctor_id ORDER BY FIELD(day_of_week,'Mon','Tue','Wed','Thu','Fri','Sat','Sun')");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dr. <?= htmlspecialchars($doc['full_name']) ?> | Profile</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { background-color: #f4f7f6; }
        .profile-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            height: 180px;
            margin-bottom: 80px;
            border-radius: 0 0 50px 50px;
        }
        .doctor-img-container {
            margin-top: -100px;
        }
        .doctor-img {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 50%;
            border: 6px solid #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .info-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            background: #fff;
        }
        .badge-custom {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        .availability-table td { padding: 12px; vertical-align: middle; }
        .sidebar-sticky { position: sticky; top: 20px; }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="profile-header"></div>

<div class="container mb-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="doctor-img-container text-center text-lg-left mb-4">
                <img src="../admin/<?= $doc['profile_image'] ?: '../uploads/default_doctor.png' ?>" class="doctor-img" alt="Doctor Image">
                <div class="mt-3">
                    <h2 class="font-weight-bold mb-1">Dr. <?= htmlspecialchars($doc['full_name']) ?></h2>
                    <p class="text-primary lead font-weight-bold"><?= htmlspecialchars($doc['specialization']) ?></p>
                    <div class="d-flex justify-content-center justify-content-lg-start mb-3">
                        <span class="badge badge-warning text-dark mr-2 badge-custom">
                            <i class="fas fa-star mr-1"></i> <?= htmlspecialchars($doc['rating']) ?> / 5.0
                        </span>
                        <span class="badge badge-light border badge-custom">
                            <i class="fas fa-briefcase mr-1 text-muted"></i> <?= htmlspecialchars($doc['experience_years']) ?> Years Exp.
                        </span>
                    </div>
                </div>
            </div>

            <div class="card info-card mb-4">
                <div class="card-body p-4">
                    <h5 class="font-weight-bold border-bottom pb-2 mb-3">About Doctor</h5>
                    <p class="text-muted" style="line-height: 1.8;">
                        <?= nl2br(htmlspecialchars($doc['bio'] ?: 'No bio available for this doctor.')) ?>
                    </p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted small text-uppercase font-weight-bold">Education & Qualification</h6>
                            <p class="mb-0"><i class="fas fa-user-graduate text-info mr-2"></i><?= htmlspecialchars($doc['qualification']) ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted small text-uppercase font-weight-bold">Clinic Location</h6>
                            <p class="mb-0"><i class="fas fa-map-marker-alt text-danger mr-2"></i><?= htmlspecialchars($doc['location']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card info-card">
                <div class="card-body p-4">
                    <h5 class="font-weight-bold border-bottom pb-2 mb-3">Working Hours</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless availability-table mb-0">
                            <thead class="text-muted small text-uppercase">
                                <tr>
                                    <th>Day</th>
                                    <th>Shift Hours</th>
                                    <th class="text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if($avail_res->num_rows > 0){
                                    while($av = $avail_res->fetch_assoc()){
                                        echo "<tr>
                                                <td class='font-weight-bold'>{$av['day_of_week']}</td>
                                                <td class='text-muted'><i class='far fa-clock mr-2 text-primary'></i> " . date('h:i A', strtotime($av['start_time'])) . " - " . date('h:i A', strtotime($av['end_time'])) . "</td>
                                                <td class='text-right'><span class='badge badge-pill badge-success-light text-success' style='background: #e8f5e9;'>Available</span></td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3' class='text-center text-muted py-4'>No availability hours defined.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sidebar-sticky mt-lg-n5">
                <div class="card info-card shadow">
                    <div class="card-body text-center p-4">
                        <div class="mb-3">
                            <span class="text-muted small d-block mb-1">Consultation Fee</span>
                            <h3 class="text-success font-weight-bold">RS <?= number_format($doc['consultation_fee'], 2) ?></h3>
                        </div>
                        <hr>
                        <div class="text-left mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle text-primary mr-2"></i>
                                <span class="small">Verified Professional</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle text-primary mr-2"></i>
                                <span class="small">Instant Booking Confirmation</span>
                            </div>
                        </div>
                        <a href="create_appointment.php?doctor_id=<?= $doc['doctor_id'] ?>" class="btn btn-success btn-lg btn-block shadow-sm py-3 font-weight-bold">
                            Book Appointment Now
                        </a>
                        <p class="text-muted small mt-3">No payment required until visit</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>