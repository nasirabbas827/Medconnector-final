<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']); // plain text as requested
    $specialization = trim($_POST['specialization']);
    $qualification = trim($_POST['qualification']);
    $experience_years = $_POST['experience_years'];
    $consultation_fee = $_POST['consultation_fee'];
    $bio = trim($_POST['bio']);
    $status = $_POST['status'];
    $rating = $_POST['rating'];
    $location = trim($_POST['location']);

    $image_path = "";
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $image_name = time() . "_" . basename($_FILES['profile_image']['name']);
        $target_dir = "uploads/";
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    $query = "INSERT INTO doctors (full_name, email, phone, password, specialization, qualification, experience_years, consultation_fee, profile_image, bio, status, rating, location)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssssssss", $full_name, $email, $phone, $password, $specialization, $qualification, $experience_years, $consultation_fee, $image_path, $bio, $status, $rating, $location);

    if ($stmt->execute()) {
        $success = "Doctor added successfully.";
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Doctor</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <div class="card mx-auto" style="max-width:700px;">
        <div class="card-body">
            <h3>Add Doctor</h3>

            <?php if (isset($success)) echo "<p class='text-success'>$success</p>"; ?>
            <?php if (isset($error)) echo "<p class='text-danger'>$error</p>"; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Full Name</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>

        <div class="form-group col-md-6">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control">
        </div>

        <div class="form-group col-md-6">
            <label>Password</label>
            <input type="text" name="password" required class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Specialization</label>
            <input type="text" name="specialization" class="form-control">
        </div>

        <div class="form-group col-md-6">
            <label>Qualification</label>
            <input type="text" name="qualification" class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Experience Years</label>
            <input type="number" name="experience_years" class="form-control">
        </div>

        <div class="form-group col-md-6">
            <label>Consultation Fee</label>
            <input type="number" step="0.01" name="consultation_fee" class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Rating</label>
            <input type="number" step="0.1" min="0" max="5" name="rating" class="form-control" value="0">
        </div>

        <div class="form-group col-md-6">
            <label>Location</label>
            <input type="text" name="location" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label>Profile Image</label>
        <input type="file" name="profile_image" class="form-control-file">
    </div>

    <div class="form-group">
        <label>Bio</label>
        <textarea name="bio" class="form-control" rows="3"></textarea>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="on_leave">On Leave</option>
            </select>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Add Doctor</button>
    <a href="view_doctors.php" class="btn btn-dark">View Doctors</a>
</form>

        </div>
    </div>
</div>
</body>
</html>
