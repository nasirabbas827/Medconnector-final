<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['patient_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit;
}

$patient_id = $_SESSION['patient_id'];

if(!isset($_GET['doctor_id'])){
    echo "Invalid request.";
    exit;
}

$doctor_id = intval($_GET['doctor_id']);
$doc_res = $conn->query("SELECT * FROM doctors WHERE doctor_id=$doctor_id AND status='active'");
if($doc_res->num_rows !== 1){
    echo "Doctor not found.";
    exit;
}
$doc = $doc_res->fetch_assoc();

$msg = "";

// Handle appointment submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $appointment_date = $_POST['appointment_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $reason = $_POST['reason'];

    // Check for overlapping appointments
    $check = $conn->prepare("
        SELECT * FROM appointments 
        WHERE doctor_id=? AND appointment_date=? 
        AND ((start_time<=? AND end_time>?) OR (start_time<? AND end_time>=?))
        AND status IN ('pending','confirmed')
    ");
    $check->bind_param("isssss", $doctor_id, $appointment_date, $start_time, $start_time, $end_time, $end_time);
    $check->execute();
    $check_res = $check->get_result();

    if($check_res->num_rows > 0){
        $msg = "<div class='alert alert-danger'>This time slot is already booked. Please choose another slot.</div>";
    } else {
        $insert = $conn->prepare("
            INSERT INTO appointments (patient_id, doctor_id, appointment_date, start_time, end_time, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $insert->bind_param("issss", $patient_id, $doctor_id, $appointment_date, $start_time, $end_time);

        if($insert->execute()){
            $msg = "<div class='alert alert-success'>Appointment booked successfully.</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Error booking appointment.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-4">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2>Book Appointment with Dr. <?= htmlspecialchars($doc['full_name']) ?></h2>
    <?php if($msg) echo $msg; ?>

    <form method="POST">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Appointment Date</label>
                <input type="date" name="appointment_date" class="form-control" required>
            </div>
            <div class="form-group col-md-4">
                <label>Start Time</label>
                <input type="time" name="start_time" class="form-control" required>
            </div>
            <div class="form-group col-md-4">
                <label>End Time</label>
                <input type="time" name="end_time" class="form-control" required>
            </div>
        </div>
        <div class="form-group">
            <label>Reason for Visit</label>
            <textarea name="reason" class="form-control" required></textarea>
        </div>
        <button class="btn btn-primary">Book Appointment</button>
    </form>
</div>
</div>
</div>
</body>
</html>
