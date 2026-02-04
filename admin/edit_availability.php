<?php
session_start();
include('config.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

if(!isset($_GET['id'])){
    echo "Invalid request.";
    exit;
}

$id = intval($_GET['id']);

// Fetch current availability
$stmt = $conn->prepare("SELECT * FROM doctor_availability WHERE availability_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows !== 1){
    echo "Availability not found.";
    exit;
}
$availability = $res->fetch_assoc();

// Fetch doctors
$doctors_res = $conn->query("SELECT doctor_id, full_name FROM doctors WHERE status='active'");

// Update
if($_SERVER["REQUEST_METHOD"] === "POST"){
    $doctor_id = $_POST['doctor_id'];
    $day_of_week = $_POST['day_of_week'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $u = $conn->prepare("UPDATE doctor_availability SET doctor_id=?, day_of_week=?, start_time=?, end_time=? WHERE availability_id=?");
    $u->bind_param("isssi", $doctor_id, $day_of_week, $start_time, $end_time, $id);

    if($u->execute()){
        echo "<script>alert('Availability updated successfully.'); window.location='view_availability.php';</script>";
    } else {
        $error = "Update failed.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Availability</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <div class="card mx-auto" style="max-width:600px;">
        <div class="card-body">
            <h3>Edit Doctor Availability</h3>
            <?php if(isset($error)) echo "<p class='text-danger'>$error</p>"; ?>

            <form method="post">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Doctor</label>
                        <select name="doctor_id" class="form-control" required>
                            <?php while($d = $doctors_res->fetch_assoc()) { ?>
                                <option value="<?= $d['doctor_id'] ?>" <?= $availability['doctor_id']==$d['doctor_id']?"selected":"" ?>><?= htmlspecialchars($d['full_name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Day</label>
                        <select name="day_of_week" class="form-control" required>
                            <?php foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day){ ?>
                                <option value="<?= $day ?>" <?= $availability['day_of_week']==$day?"selected":"" ?>><?= $day ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Start Time</label>
                        <input type="time" name="start_time" class="form-control" value="<?= $availability['start_time'] ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>End Time</label>
                        <input type="time" name="end_time" class="form-control" value="<?= $availability['end_time'] ?>" required>
                    </div>
                </div>

                <button class="btn btn-primary">Update Availability</button>
                <a href="view_availability.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
