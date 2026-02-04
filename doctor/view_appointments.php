<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['doctor_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

// Handle update
if(isset($_POST['update'])){
    $appointment_id = intval($_POST['appointment_id']);
    $appointment_date = $_POST['appointment_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $status = $_POST['status'];

    $update = $conn->prepare("UPDATE appointments SET appointment_date=?, start_time=?, end_time=?, status=?, updated_at=NOW() WHERE appointment_id=? AND doctor_id=?");
    $update->bind_param("ssssss", $appointment_date, $start_time, $end_time, $status, $appointment_id, $doctor_id);
    if($update->execute()){
        $msg = "<div class='alert alert-success'>Appointment updated successfully.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Failed to update appointment.</div>";
    }
}

// Fetch all appointments for this doctor
$query = "SELECT a.*, p.name AS patient_name 
          FROM appointments a
          JOIN patients p ON a.patient_id = p.id
          WHERE a.doctor_id = $doctor_id
          ORDER BY a.appointment_date DESC, a.start_time DESC";
$res = $conn->query($query);

// Fetch doctor info
$doc_res = $conn->query("SELECT * FROM doctors WHERE doctor_id = $doctor_id");
$doctor = $doc_res->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard - Appointments</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-4">
    
    <h4>My Appointments</h4>
    <?php if(isset($msg)) echo $msg; ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($res->num_rows > 0){
                    while($row = $res->fetch_assoc()){
                        echo "<tr>
                            <td>{$row['appointment_id']}</td>
                            <td>{$row['patient_name']}</td>
                            <td>{$row['appointment_date']}</td>
                            <td>{$row['start_time']}</td>
                            <td>{$row['end_time']}</td>
                            <td>{$row['status']}</td>
                            <td>
                                <button class='btn btn-sm btn-primary' data-toggle='modal' data-target='#editModal{$row['appointment_id']}'>Edit</button>
                            </td>
                        </tr>";

                        // Modal for editing
                        echo "
                        <div class='modal fade' id='editModal{$row['appointment_id']}' tabindex='-1' role='dialog'>
                          <div class='modal-dialog' role='document'>
                            <div class='modal-content'>
                              <form method='POST'>
                                <div class='modal-header'>
                                  <h5 class='modal-title'>Edit Appointment #{$row['appointment_id']}</h5>
                                  <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                </div>
                                <div class='modal-body'>
                                    <input type='hidden' name='appointment_id' value='{$row['appointment_id']}'>
                                    <div class='form-group'>
                                        <label>Appointment Date</label>
                                        <input type='date' name='appointment_date' class='form-control' value='{$row['appointment_date']}' required>
                                    </div>
                                    <div class='form-group'>
                                        <label>Start Time</label>
                                        <input type='time' name='start_time' class='form-control' value='{$row['start_time']}' required>
                                    </div>
                                    <div class='form-group'>
                                        <label>End Time</label>
                                        <input type='time' name='end_time' class='form-control' value='{$row['end_time']}' required>
                                    </div>
                                    <div class='form-group'>
                                        <label>Status</label>
                                        <select name='status' class='form-control'>
                                            <option value='pending' ".($row['status']=='pending'?'selected':'').">Pending</option>
                                            <option value='confirmed' ".($row['status']=='confirmed'?'selected':'').">Confirmed</option>
                                            <option value='cancelled' ".($row['status']=='cancelled'?'selected':'').">Cancelled</option>
                                            <option value='completed' ".($row['status']=='completed'?'selected':'').">Completed</option>
                                        </select>
                                    </div>
                                </div>
                                <div class='modal-footer'>
                                    <button type='submit' name='update' class='btn btn-success'>Save Changes</button>
                                    <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No appointments found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
