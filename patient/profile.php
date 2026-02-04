<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['patient_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit;
}

$patient_id = $_SESSION['patient_id'];
$res = $conn->query("SELECT * FROM patients WHERE id = $patient_id");
if($res->num_rows !== 1){
    echo "Patient not found.";
    exit;
}
$patient = $res->fetch_assoc();
$msg = "";

// Handle profile update
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $blood_group = $_POST['blood_group'];
    $allergies = $_POST['allergies'];
    $emergency_contact_name = $_POST['emergency_contact_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $guardian_name = $_POST['guardian_name'];
    $guardian_phone = $_POST['guardian_phone'];

    $update = $conn->prepare("
        UPDATE patients SET 
            name=?, email=?, phone=?, password=?, blood_group=?, allergies=?,
            emergency_contact_name=?, date_of_birth=?, gender=?, address=?,
            guardian_name=?, guardian_phone=?
        WHERE id=?
    ");
    $update->bind_param(
        "ssssssssssssi",
        $name, $email, $phone, $password, $blood_group, $allergies,
        $emergency_contact_name, $date_of_birth, $gender, $address,
        $guardian_name, $guardian_phone, $patient_id
    );

    if($update->execute()){
        $msg = "<div class='alert alert-success'>Profile updated successfully.</div>";
        // Refresh patient data
        $res = $conn->query("SELECT * FROM patients WHERE id = $patient_id");
        $patient = $res->fetch_assoc();
    } else {
        $msg = "<div class='alert alert-danger'>Error updating profile.</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Profile</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-4">
        <div class="card mx-auto" style="max-width: 700px;">
        <div class="card-body">
    <h2>My Profile</h2>
    <?php if($msg) echo $msg; ?>

    <form method="POST">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>MR Number</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($patient['mr_number']) ?>" readonly>
            </div>
            <div class="form-group col-md-6">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($patient['name']) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($patient['email']) ?>">
            </div>
            <div class="form-group col-md-6">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($patient['phone']) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Password</label>
                <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($patient['password']) ?>" required>
            </div>
            <div class="form-group col-md-6">
                <label>Blood Group</label>
                <input type="text" name="blood_group" class="form-control" value="<?= htmlspecialchars($patient['blood_group']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Allergies</label>
            <textarea name="allergies" class="form-control"><?= htmlspecialchars($patient['allergies']) ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Emergency Contact Name</label>
                <input type="text" name="emergency_contact_name" class="form-control" value="<?= htmlspecialchars($patient['emergency_contact_name']) ?>">
            </div>
            <div class="form-group col-md-6">
                <label>Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($patient['date_of_birth']) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Gender</label>
                <select name="gender" class="form-control">
                    <option value="male" <?= $patient['gender']=="male"?"selected":"" ?>>Male</option>
                    <option value="female" <?= $patient['gender']=="female"?"selected":"" ?>>Female</option>
                    <option value="other" <?= $patient['gender']=="other"?"selected":"" ?>>Other</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label>Address</label>
                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($patient['address']) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Guardian Name</label>
                <input type="text" name="guardian_name" class="form-control" value="<?= htmlspecialchars($patient['guardian_name']) ?>">
            </div>
            <div class="form-group col-md-6">
                <label>Guardian Phone</label>
                <input type="text" name="guardian_phone" class="form-control" value="<?= htmlspecialchars($patient['guardian_phone']) ?>">
            </div>
        </div>

        <button class="btn btn-primary">Update Profile</button>
    </form>
</div>
</div>
</div>
</body>
</html>
