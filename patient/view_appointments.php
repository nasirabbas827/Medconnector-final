<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['patient_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit;
}

$patient_id = $_SESSION['patient_id'];

/* =======================
   HANDLE CANCELLATION
======================= */
if (isset($_GET['cancel_id'])) {
    $cancel_id = intval($_GET['cancel_id']);
    $update = $conn->prepare("
        UPDATE appointments 
        SET status='cancelled' 
        WHERE appointment_id=? 
        AND patient_id=? 
        AND status='pending'
    ");
    $update->bind_param("ii", $cancel_id, $patient_id);
    $update->execute();
}

/* =======================
   FETCH APPOINTMENTS
======================= */
$appointments = $conn->query("
    SELECT 
        a.appointment_id,
        a.appointment_date,
        a.start_time,
        a.end_time,
        a.status,
        a.reason,
        d.full_name,
        d.specialization,
        d.profile_image
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.patient_id = $patient_id
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
        .doctor-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
        }
        .reason-box {
            max-width: 250px;
            white-space: normal;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-4">
    <h2>My Appointments</h2>

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Doctor</th>
                    <th>Specialization</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th width="120">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($appointments->num_rows > 0): ?>
                <?php while ($row = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img src="../admin/<?= htmlspecialchars($row['profile_image']) ?>" class="doctor-img">
                            <?= htmlspecialchars($row['full_name']) ?>
                        </td>
                        <td><?= htmlspecialchars($row['specialization']) ?></td>
                        <td><?= $row['appointment_date'] ?></td>
                        <td><?= $row['start_time'] ?> - <?= $row['end_time'] ?></td>
                        <td class="reason-box">
                            <?= $row['reason'] ? htmlspecialchars($row['reason']) : '<span class="text-muted">N/A</span>' ?>
                        </td>
                        <td>
                            <span class="badge badge-<?=
                                $row['status'] === 'pending' ? 'warning' :
                                ($row['status'] === 'confirmed' ? 'success' :
                                ($row['status'] === 'cancelled' ? 'danger' : 'secondary'))
                            ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <a href="view_appointments.php?cancel_id=<?= $row['appointment_id'] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Cancel this appointment?')">
                                    Cancel
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No appointments found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
