<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['patient_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit;
}

$patient_id = $_SESSION['patient_id'];

// Handle cancellation
if(isset($_GET['cancel_id'])){
    $cancel_id = intval($_GET['cancel_id']);
    $update = $conn->prepare("UPDATE appointments SET status='cancelled' WHERE appointment_id=? AND patient_id=? AND status='pending'");
    $update->bind_param("ii", $cancel_id, $patient_id);
    $update->execute();
}

// Fetch appointments
$appointments = $conn->query("
    SELECT a.*, d.full_name, d.specialization, d.profile_image 
    FROM appointments a
    JOIN doctors d ON a.doctor_id=d.doctor_id
    WHERE a.patient_id=$patient_id
    ORDER BY a.appointment_date DESC, a.start_time DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Appointments</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .doctor-img { width: 60px; height: 60px; object-fit: cover; border-radius: 50%; }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-4">
    <h2>My Appointments</h2>
    <table class="table table-bordered table-hover mt-3">
        <thead>
            <tr>
                <th>Doctor</th>
                <th>Specialization</th>
                <th>Date</th>
                <th>Start - End</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if($appointments->num_rows > 0){
                while($row = $appointments->fetch_assoc()){
                    echo "<tr>
                        <td><img src='../admin/{$row['profile_image']}' class='doctor-img'> {$row['full_name']}</td>
                        <td>{$row['specialization']}</td>
                        <td>{$row['appointment_date']}</td>
                        <td>{$row['start_time']} - {$row['end_time']}</td>
                        <td>{$row['status']}</td>
                        <td>";
                    if($row['status'] === 'pending'){
                        echo "<a href='view_appointments.php?cancel_id={$row['appointment_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Cancel this appointment?\")'>Cancel</a>";
                    } else {
                        echo "-";
                    }
                    echo "</td></tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>No appointments found.</td></tr>";
            } ?>
        </tbody>
    </table>
</div>
</body>
</html>
