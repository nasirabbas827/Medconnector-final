<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['doctor_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

/* =======================
   HANDLE UPDATE
======================= */
if (isset($_POST['update'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $appointment_date = $_POST['appointment_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $status = $_POST['status'];

    $update = $conn->prepare("
        UPDATE appointments 
        SET appointment_date=?, start_time=?, end_time=?, status=?, updated_at=NOW() 
        WHERE appointment_id=? AND doctor_id=?
    ");
    $update->bind_param(
        "ssssii",
        $appointment_date,
        $start_time,
        $end_time,
        $status,
        $appointment_id,
        $doctor_id
    );

    if ($update->execute()) {
        $msg = "<div class='alert alert-success'>Appointment updated successfully.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Failed to update appointment.</div>";
    }
}

/* =======================
   FETCH APPOINTMENTS
======================= */
$query = "
    SELECT 
        a.appointment_id,
        a.appointment_date,
        a.start_time,
        a.end_time,
        a.status,
        a.reason,
        p.name AS patient_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    WHERE a.doctor_id = $doctor_id
    ORDER BY a.appointment_date DESC, a.start_time DESC
";
$res = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard - Appointments</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .reason-cell {
            max-width: 220px;
            white-space: normal;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-4">
    <h4>My Appointments</h4>
    <?php if (isset($msg)) echo $msg; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($res->num_rows > 0): ?>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['appointment_id'] ?></td>
                        <td><?= htmlspecialchars($row['patient_name']) ?></td>
                        <td><?= $row['appointment_date'] ?></td>
                        <td><?= $row['start_time'] ?> - <?= $row['end_time'] ?></td>
                        <td class="reason-cell">
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
                            <button
                                class="btn btn-sm btn-primary"
                                data-toggle="modal"
                                data-target="#editModal<?= $row['appointment_id'] ?>">
                                Edit
                            </button>
                        </td>
                    </tr>

                    <!-- EDIT MODAL -->
                    <div class="modal fade" id="editModal<?= $row['appointment_id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Edit Appointment #<?= $row['appointment_id'] ?>
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>

                                    <div class="modal-body">
                                        <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">

                                        <div class="form-group">
                                            <label>Appointment Date</label>
                                            <input type="date" name="appointment_date" class="form-control"
                                                   value="<?= $row['appointment_date'] ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Start Time</label>
                                            <input type="time" name="start_time" class="form-control"
                                                   value="<?= $row['start_time'] ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label>End Time</label>
                                            <input type="time" name="end_time" class="form-control"
                                                   value="<?= $row['end_time'] ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Reason (Patient Provided)</label>
                                            <textarea class="form-control" rows="3" readonly><?= 
                                                $row['reason'] ? htmlspecialchars($row['reason']) : 'N/A'
                                            ?></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="status" class="form-control">
                                                <option value="pending" <?= $row['status']=='pending'?'selected':'' ?>>Pending</option>
                                                <option value="confirmed" <?= $row['status']=='confirmed'?'selected':'' ?>>Confirmed</option>
                                                <option value="cancelled" <?= $row['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                                                <option value="completed" <?= $row['status']=='completed'?'selected':'' ?>>Completed</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="submit" name="update" class="btn btn-success">
                                            Save Changes
                                        </button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                            Close
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

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

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
