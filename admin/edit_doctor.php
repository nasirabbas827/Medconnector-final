<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit;
}

$id = intval($_GET['id']);

// Fetch doctor info
$q = $conn->prepare("SELECT * FROM doctors WHERE doctor_id=?");
$q->bind_param("i", $id);
$q->execute();
$res = $q->get_result();

if ($res->num_rows !== 1) {
    echo "Doctor not found.";
    exit;
}

$doc = $res->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']); // plain text as requested
    $specialization = trim($_POST['specialization']);
    $qualification = trim($_POST['qualification']);
    $experience_years = intval($_POST['experience_years']);
    $consultation_fee = floatval($_POST['consultation_fee']);
    $bio = trim($_POST['bio']);
    $status = $_POST['status'];
    $rating = floatval($_POST['rating']);
    $location = trim($_POST['location']);

    $image_path = $doc['profile_image'];

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['name']) {
        $target_dir = "uploads/";
        $new_path = $target_dir . time() . "_" . basename($_FILES["profile_image"]["name"]);
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $new_path)) {
            if (file_exists($image_path)) unlink($image_path);
            $image_path = $new_path;
        }
    }

    $u = $conn->prepare("UPDATE doctors SET full_name=?, email=?, phone=?, password=?, specialization=?, qualification=?, experience_years=?, consultation_fee=?, profile_image=?, bio=?, status=?, rating=?, location=? WHERE doctor_id=?");
    $u->bind_param("ssssssssssssss", $full_name, $email, $phone, $password, $specialization, $qualification, $experience_years, $consultation_fee, $image_path, $bio, $status, $rating, $location, $id);

    if ($u->execute()) {
        echo "<script>alert('Doctor updated successfully.'); window.location='view_doctors.php';</script>";
    } else {
        $error = 'Update failed: ' . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Doctor</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <div class="card mx-auto" style="max-width:700px;">
        <div class="card-body">
            <h3>Edit Doctor</h3>

            <?php if (isset($error)) echo "<p class='text-danger'>$error</p>"; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($doc['full_name']) ?>" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($doc['email']) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($doc['phone']) ?>">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Password</label>
                        <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($doc['password']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Specialization</label>
                        <input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($doc['specialization']) ?>">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Qualification</label>
                        <input type="text" name="qualification" class="form-control" value="<?= htmlspecialchars($doc['qualification']) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Experience Years</label>
                        <input type="number" name="experience_years" class="form-control" value="<?= htmlspecialchars($doc['experience_years']) ?>">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Consultation Fee</label>
                        <input type="number" step="0.01" class="form-control" name="consultation_fee" value="<?= htmlspecialchars($doc['consultation_fee']) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Rating</label>
                        <input type="number" step="0.1" min="0" max="5" name="rating" class="form-control" value="<?= htmlspecialchars($doc['rating']) ?>">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Location</label>
                        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($doc['location']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Current Image</label><br>
                    <?php if (!empty($doc['profile_image']) && file_exists($doc['profile_image'])): ?>
                        <img src="<?= $doc['profile_image'] ?>" width="80"><br><br>
                    <?php endif; ?>
                    <input type="file" name="profile_image" class="form-control-file">
                </div>

                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($doc['bio']) ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active" <?= $doc['status']=="active"?"selected":"" ?>>Active</option>
                            <option value="inactive" <?= $doc['status']=="inactive"?"selected":"" ?>>Inactive</option>
                            <option value="on_leave" <?= $doc['status']=="on_leave"?"selected":"" ?>>On Leave</option>
                        </select>
                    </div>
                </div>

                <button class="btn btn-primary">Update Doctor</button>
                <a href="view_doctors.php" class="btn btn-secondary">Cancel</a>

            </form>

        </div>
    </div>
</div>
</body>
</html>
