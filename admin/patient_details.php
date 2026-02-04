<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if(!isset($_GET['id'])){
    echo "Invalid request.";
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM patients WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows !== 1){
    echo "Patient not found.";
    exit;
}

$patient = $res->fetch_assoc();

// Handle status update
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])){
    $new_status = $_POST['status'];
    $update = $conn->prepare("UPDATE patients SET status=? WHERE id=?");
    $update->bind_param("si", $new_status, $id);
    if($update->execute()){
        $patient['status'] = $new_status;
        $msg = "<div class='alert alert-success'>Status updated successfully.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error updating status.</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <h3>Patient Details</h3>
    <a href="view_patients.php" class="btn btn-secondary mb-3">Back to Patients</a>

    <?php if(isset($msg)) echo $msg; ?>

    <div class="card">
        <div class="card-body">
            <h5>Personal Information</h5>
            <p><strong>MR Number:</strong> <?= $patient['mr_number'] ?></p>
            <p><strong>Name:</strong> <?= $patient['name'] ?></p>
            <p><strong>Email:</strong> <?= $patient['email'] ?></p>
            <p><strong>Phone:</strong> <?= $patient['phone'] ?></p>
            <p><strong>Gender:</strong> <?= $patient['gender'] ?></p>
            <p><strong>Date of Birth:</strong> <?= $patient['date_of_birth'] ?></p>
            <p><strong>Blood Group:</strong> <?= $patient['blood_group'] ?></p>
            <p><strong>Allergies:</strong> <?= $patient['allergies'] ?></p>

            <hr>
            <h5>Emergency Contact</h5>
            <p><strong>Name:</strong> <?= $patient['emergency_contact_name'] ?></p>
            <p><strong>Guardian Name:</strong> <?= $patient['guardian_name'] ?></p>
            <p><strong>Guardian Phone:</strong> <?= $patient['guardian_phone'] ?></p>
            <p><strong>Address:</strong> <?= $patient['address'] ?></p>

            <hr>
            <h5>Registration & Status</h5>
            <p><strong>Registration Date:</strong> <?= $patient['registration_date'] ?></p>
            
            <form method="POST" class="form-inline">
                <label class="mr-2"><strong>Status:</strong></label>
                <select name="status" class="form-control mr-2">
                    <option value="active" <?= $patient['status']=="active"?"selected":"" ?>>Active</option>
                    <option value="inactive" <?= $patient['status']=="inactive"?"selected":"" ?>>Inactive</option>
                </select>
                <button class="btn btn-primary">Update Status</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
