<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['patient_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit;
}

$patient_id = $_SESSION['patient_id'];

if (!isset($_GET['doctor_id'])) {
    echo "Invalid request.";
    exit;
}

$doctor_id = intval($_GET['doctor_id']);

// Fetch doctor
$doc_res = $conn->prepare("SELECT * FROM doctors WHERE doctor_id=? AND status='active'");
$doc_res->bind_param("i", $doctor_id);
$doc_res->execute();
$doc = $doc_res->get_result()->fetch_assoc();

if (!$doc) {
    echo "Doctor not found.";
    exit;
}

$msg = "";

/* ==========================
   HANDLE BOOKING
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $appointment_date = $_POST['appointment_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $reason = trim($_POST['reason']);

    // Basic validation
    if ($end_time <= $start_time) {
        $msg = "<div class='alert alert-danger'>End time must be after start time.</div>";
    } elseif (strlen($reason) < 5) {
        $msg = "<div class='alert alert-danger'>Please provide a valid reason (min 5 characters).</div>";
    } else {

        // Check overlapping appointments
        $check = $conn->prepare("
            SELECT appointment_id FROM appointments 
            WHERE doctor_id=? 
            AND appointment_date=? 
            AND status IN ('pending','confirmed')
            AND (
                (start_time < ? AND end_time > ?)
            )
        ");
        $check->bind_param(
            "isss",
            $doctor_id,
            $appointment_date,
            $end_time,
            $start_time
        );
        $check->execute();
        $check_res = $check->get_result();

        if ($check_res->num_rows > 0) {
            $msg = "<div class='alert alert-danger'>This time slot is already booked. Please choose another slot.</div>";
        } else {

            // INSERT with reason (FIXED)
            $insert = $conn->prepare("
                INSERT INTO appointments 
                (patient_id, doctor_id, appointment_date, start_time, end_time, reason, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            $insert->bind_param(
                "iissss",
                $patient_id,
                $doctor_id,
                $appointment_date,
                $start_time,
                $end_time,
                $reason
            );

            if ($insert->execute()) {
                $msg = "<div class='alert alert-success'>Appointment booked successfully.</div>";
            } else {
                $msg = "<div class='alert alert-danger'>Error booking appointment.</div>";
            }
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
    <div class="card mx-auto" style="max-width: 550px;">
        <div class="card-body">

            <h4 class="mb-3">
                Book Appointment with Dr. <?= htmlspecialchars($doc['full_name']) ?>
            </h4>

            <?php if ($msg) echo $msg; ?>

            <form method="POST">

                <div class="form-group">
                    <label>Appointment Date</label>
                    <input type="date" name="appointment_date" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Start Time</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>End Time</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Reason for Visit</label>
                    <textarea
                        name="reason"
                        class="form-control"
                        rows="3"
                        placeholder="Describe your problem..."
                        required></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Book Appointment
                </button>

            </form>

        </div>
    </div>
</div>

</body>
</html>
