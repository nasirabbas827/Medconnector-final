<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Check if doctor ID is provided
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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <h3>Doctor Details</h3>
    <a href="view_doctors.php" class="btn btn-secondary mb-3">Back to Doctors List</a>

    <div class="card mx-auto" style="max-width:700px;">
        <div class="card-body">
            <div class="text-center mb-3">
                <?php if (!empty($doc['profile_image']) && file_exists($doc['profile_image'])): ?>
                    <img src="<?= $doc['profile_image'] ?>" width="120" class="rounded-circle">
                <?php else: ?>
                    <div style="width:120px; height:120px; line-height:120px; background:#ddd; border-radius:50%; margin:auto;">No Image</div>
                <?php endif; ?>
            </div>

            <table class="table table-bordered">
                <tr>
                    <th>ID</th>
                    <td><?= $doc['doctor_id'] ?></td>
                </tr>
                <tr>
                    <th>Full Name</th>
                    <td><?= htmlspecialchars($doc['full_name']) ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= htmlspecialchars($doc['email']) ?></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td><?= htmlspecialchars($doc['phone']) ?></td>
                </tr>
                <tr>
                    <th>Password</th>
                    <td><?= htmlspecialchars($doc['password']) ?></td>
                </tr>
                <tr>
                    <th>Specialization</th>
                    <td><?= htmlspecialchars($doc['specialization']) ?></td>
                </tr>
                <tr>
                    <th>Qualification</th>
                    <td><?= htmlspecialchars($doc['qualification']) ?></td>
                </tr>
                <tr>
                    <th>Experience Years</th>
                    <td><?= $doc['experience_years'] ?></td>
                </tr>
                <tr>
                    <th>Consultation Fee</th>
                    <td><?= $doc['consultation_fee'] ?></td>
                </tr>
                <tr>
                    <th>Rating</th>
                    <td><?= $doc['rating'] ?>/5</td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td><?= htmlspecialchars($doc['location']) ?></td>
                </tr>
                <tr>
                    <th>Bio</th>
                    <td><?= nl2br(htmlspecialchars($doc['bio'])) ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><?= ucfirst($doc['status']) ?></td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td><?= $doc['created_at'] ?></td>
                </tr>
                <tr>
                    <th>Last Updated</th>
                    <td><?= $doc['updated_at'] ?></td>
                </tr>
            </table>

            <a href="edit_doctor.php?id=<?= $doc['doctor_id'] ?>" class="btn btn-warning">Edit Doctor</a>
            <a href="view_doctors.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>
</body>
</html>
