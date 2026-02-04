<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['patient_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit;
}

$patient_id = $_SESSION['patient_id'];

// Fetch patient details
$res = $conn->query("SELECT * FROM patients WHERE id = $patient_id");
$patient = $res->fetch_assoc();

// Fetch counts for stat cards
$apt_count_res = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE patient_id = $patient_id");
$total_apts = $apt_count_res->fetch_assoc()['total'];

$upcoming_res = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE patient_id = $patient_id AND appointment_date >= CURDATE() AND status != 'cancelled'");
$upcoming_apts = $upcoming_res->fetch_assoc()['total'];

// Handle doctor search
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$query = "SELECT * FROM doctors WHERE status='active'";

if (!empty($search)) {
    $query .= " AND (full_name LIKE '%$search%' OR specialization LIKE '%$search%' OR location LIKE '%$search%')";
}

$query .= " ORDER BY full_name ASC";
$doctors_res = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Dashboard | <?= htmlspecialchars($patient['name']) ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { background-color: #f8f9fa; }
        .stat-card { border: none; border-radius: 12px; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
        .bg-gradient-blue { background: linear-gradient(45deg, #4e73df, #224abe); color: white; }
        .bg-gradient-green { background: linear-gradient(45deg, #1cc88a, #13855c); color: white; }
        .bg-gradient-red { background: linear-gradient(45deg, #e74a3b, #be2617); color: white; }
        
        .doctor-card { border: none; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .card-img-top { height: 180px; object-fit: cover; }
        .search-container { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .sidebar-heading { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; color: #888; }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-weight-bold">Hello, <?= htmlspecialchars($patient['name']) ?>!</h2>
            <p class="text-muted"><i class="fas fa-id-card mr-2"></i>MR Number: <span class="badge badge-light border"><?= htmlspecialchars($patient['mr_number']) ?></span></p>
        </div>
        <a href="view_appointments.php" class="btn btn-outline-primary btn-rounded shadow-sm">
            <i class="fas fa-calendar-alt mr-2"></i>My Appointments
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card bg-gradient-blue shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small opacity-75">Total Visits</div>
                            <div class="h3 font-weight-bold mb-0"><?= $total_apts ?></div>
                        </div>
                        <i class="fas fa-history fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-gradient-green shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small opacity-75">Upcoming Bookings</div>
                            <div class="h3 font-weight-bold mb-0"><?= $upcoming_apts ?></div>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-gradient-red shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small opacity-75">Blood Group</div>
                            <div class="h3 font-weight-bold mb-0"><?= htmlspecialchars($patient['blood_group'] ?: 'N/A') ?></div>
                        </div>
                        <i class="fas fa-heartbeat fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>



            <div class="search-container mb-4">
                <h4 class="mb-3">Find a Specialist</h4>
                <form method="GET" class="input-group">
                    <input type="text" name="search" placeholder="Search by name, specialty, or location..." class="form-control form-control-lg border-right-0" value="<?= htmlspecialchars($search) ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary px-4"><i class="fas fa-search"></i></button>
                    </div>
                    <?php if(!empty($search)): ?>
                        <a href="patient_dashboard.php" class="btn btn-link text-muted ml-2">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="row">
                <?php if($doctors_res->num_rows > 0): ?>
                    <?php while($doc = $doctors_res->fetch_assoc()): ?>
                        <div class="col-md-6 col-xl-4 mb-4">
                            <div class="card doctor-card h-100 shadow-sm">
                                <div class="position-relative">
                                    <img src="../admin/<?= $doc['profile_image'] ?: '../uploads/default_doctor.png' ?>" class="card-img-top" alt="Doctor Image">
                                    <span class="badge badge-success position-absolute" style="top:10px; right:10px;">Active</span>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title font-weight-bold text-dark mb-1"><?= htmlspecialchars($doc['full_name']) ?></h5>
                                    <p class="text-primary small mb-2"><?= htmlspecialchars($doc['specialization']) ?></p>
                                    
                                    <div class="small text-muted mb-2">
                                        <i class="fas fa-map-marker-alt mr-1 text-danger"></i> <?= htmlspecialchars($doc['location']) ?>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="text-warning small">
                                            <?php for($i=1; $i<=5; $i++) echo ($i <= $doc['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                                            <span class="text-muted ml-1">(<?= $doc['rating'] ?>)</span>
                                        </div>
                                        <div class="font-weight-bold text-success">RS <?= number_format($doc['consultation_fee'], 0) ?></div>
                                    </div>

                                    <div class="row no-gutters">
                                        <div class="col-6 pr-1">
                                            <a href="doctor_profile.php?id=<?= $doc['doctor_id'] ?>" class="btn btn-outline-info btn-sm btn-block">Profile</a>
                                        </div>
                                        <div class="col-6 pl-1">
                                            <a href="create_appointment.php?doctor_id=<?= $doc['doctor_id'] ?>" class="btn btn-dark btn-sm btn-block">Book Now</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <img src="../assets/no-results.png" style="width: 80px; opacity: 0.3;" class="mb-3">
                        <p class="text-muted">No doctors match your search criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>