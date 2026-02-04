<?php
session_start();
require_once('config.php');

$email_or_mr = $password = $role = "";
$email_err = $password_err = $role_err = $login_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (empty(trim($_POST['email_or_mr']))) {
        $email_err = "Email or MR Number is required";
    } else {
        $email_or_mr = trim($_POST['email_or_mr']);
    }

    if (empty(trim($_POST['password']))) {
        $password_err = "Password is required";
    } else {
        $password = trim($_POST['password']);
    }

    if (empty($_POST['role'])) {
        $role_err = "Please select role";
    } else {
        $role = $_POST['role'];
    }

    if (empty($email_err) && empty($password_err) && !empty($role)) {

        if ($role === 'doctor') {
            $stmt = $conn->prepare(
                "SELECT doctor_id, full_name, email, password, status 
                 FROM doctors WHERE email = ? LIMIT 1"
            );
            $stmt->bind_param("s", $email_or_mr);
            $redirect = "doctor/doctor_dashboard.php";

        } elseif ($role === 'patient') {
            $stmt = $conn->prepare(
                "SELECT id, name, mr_number, email, password, status 
                 FROM patients WHERE email=? OR mr_number=? LIMIT 1"
            );
            $stmt->bind_param("ss", $email_or_mr, $email_or_mr);
            $redirect = "patient/patient_dashboard.php";
        }

        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $row = $res->fetch_assoc();

            if ($row['status'] !== 'active') {
                $login_msg = "<div class='alert alert-danger'>Account inactive</div>";
            } elseif ($password === $row['password']) {

                if ($role === 'doctor') {
                    $_SESSION['doctor_id'] = $row['doctor_id'];
                    $_SESSION['role'] = 'doctor';
                    header("Location: $redirect");
                    exit();
                }

                if ($role === 'patient') {
                    $_SESSION['patient_id'] = $row['id'];
                    $_SESSION['role'] = 'patient';
                    header("Location: $redirect");
                    exit();
                }

            } else {
                $password_err = "Invalid password";
            }

        } else {
            $email_err = ucfirst($role)." not found";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto shadow-sm" style="max-width: 450px;">
        <div class="card-body">
            <h3 class="text-center mb-3">Login</h3>
            <?= $login_msg ?>

            <form method="post">
                <div class="form-group">
                    <label>Email or MR Number</label>
                    <input type="text" name="email_or_mr" class="form-control" value="<?= htmlspecialchars($email_or_mr) ?>">
                    <small class="text-danger"><?= $email_err ?></small>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control">
                    <small class="text-danger"><?= $password_err ?></small>
                </div>

                <div class="form-group">
                    <label>Login As</label>
                    <select name="role" class="form-control">
                        <option value="">Select</option>
                        <option value="doctor" <?= $role=='doctor'?"selected":"" ?>>Doctor</option>
                        <option value="patient" <?= $role=='patient'?"selected":"" ?>>Patient</option>
                    </select>
                    <small class="text-danger"><?= $role_err ?></small>
                </div>

                <button class="btn btn-primary btn-block">Login</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
