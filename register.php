<?php
session_start();
require_once('config.php');

$mr_number = $name = $email = $phone = $password = $blood_group = $allergies = $emergency_contact_name = "";
$date_of_birth = $gender = $address = $guardian_name = $guardian_phone = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (empty(trim($_POST['mr_number']))) $errors['mr_number'] = "MR Number is required";
    else $mr_number = trim($_POST['mr_number']);

    if (empty(trim($_POST['name']))) $errors['name'] = "Name is required";
    else $name = trim($_POST['name']);

    if (empty(trim($_POST['phone']))) $errors['phone'] = "Phone is required";
    else $phone = trim($_POST['phone']);

    if (empty(trim($_POST['password']))) $errors['password'] = "Password is required";
    else $password = trim($_POST['password']);

    if (empty($_POST['gender'])) $errors['gender'] = "Please select gender";
    else $gender = $_POST['gender'];

    $email = trim($_POST['email']);
    $blood_group = trim($_POST['blood_group']);
    $allergies = trim($_POST['allergies']);
    $emergency_contact_name = trim($_POST['emergency_contact_name']);
    $date_of_birth = $_POST['date_of_birth'];
    $address = trim($_POST['address']);
    $guardian_name = trim($_POST['guardian_name']);
    $guardian_phone = trim($_POST['guardian_phone']);

    $stmt = $conn->prepare("SELECT * FROM patients WHERE mr_number=? OR email=? OR phone=? LIMIT 1");
    $stmt->bind_param("sss", $mr_number, $email, $phone);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) $errors['duplicate'] = "MR Number, Email, or Phone already exists";
    $stmt->close();

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO patients 
        (mr_number, name, email, phone, password, blood_group, allergies, emergency_contact_name, date_of_birth, gender, address, guardian_name, guardian_phone) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssss",
            $mr_number, $name, $email, $phone, $password, $blood_group, $allergies, $emergency_contact_name, $date_of_birth, $gender, $address, $guardian_name, $guardian_phone
        );

        if ($stmt->execute()) {
            $success = "Registration successful. You can now <a href='login.php'>login</a>.";
        } else {
            $errors['db'] = "Database error: " . $conn->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Patient Registration</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <h3 class="text-center mb-3">Patient Registration</h3>

            <?php if (!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
            <?php if (!empty($errors)) foreach ($errors as $err) echo "<div class='alert alert-danger'>$err</div>"; ?>

            <form method="post">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>MR Number</label>
                        <input type="text" name="mr_number" class="form-control" value="<?= htmlspecialchars($mr_number) ?>" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Password</label>
                        <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($password) ?>" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select</option>
                            <option value="male" <?= $gender=="male"?"selected":"" ?>>Male</option>
                            <option value="female" <?= $gender=="female"?"selected":"" ?>>Female</option>
                            <option value="other" <?= $gender=="other"?"selected":"" ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($date_of_birth) ?>">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Blood Group</label>
                        <input type="text" name="blood_group" class="form-control" value="<?= htmlspecialchars($blood_group) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" class="form-control" value="<?= htmlspecialchars($emergency_contact_name) ?>">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Guardian Name</label>
                        <input type="text" name="guardian_name" class="form-control" value="<?= htmlspecialchars($guardian_name) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Guardian Phone</label>
                        <input type="text" name="guardian_phone" class="form-control" value="<?= htmlspecialchars($guardian_phone) ?>">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Address</label>
                        <textarea name="address" class="form-control"><?= htmlspecialchars($address) ?></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label>Allergies</label>
                    <textarea name="allergies" class="form-control"><?= htmlspecialchars($allergies) ?></textarea>
                </div>

                <button class="btn btn-primary btn-block">Register</button>
            </form>

        </div>
    </div>
</div>
</body>
</html>
