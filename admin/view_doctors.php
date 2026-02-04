<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    $img_res = $conn->query("SELECT profile_image FROM doctors WHERE doctor_id = $id");
    $img_row = $img_res->fetch_assoc();

    if ($img_row && file_exists($img_row['profile_image'])) {
        unlink($img_row['profile_image']);
    }

    $del = $conn->prepare("DELETE FROM doctors WHERE doctor_id=?");
    $del->bind_param("i", $id);

    if ($del->execute()) {
        echo "<script>alert('Doctor deleted.'); window.location='view_doctors.php';</script>";
    } else {
        echo "<script>alert('Error deleting doctor.');</script>";
    }
}

// Handle search
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$query = "SELECT * FROM doctors";

if (!empty($search)) {
    $query .= " WHERE doctor_id LIKE '%$search%' OR full_name LIKE '%$search%' OR specialization LIKE '%$search%' OR email LIKE '%$search%'";
}

$query .= " ORDER BY doctor_id DESC";

$res = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Doctors</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <a href="add_doctor.php" class="btn btn-dark float-right">Add Doctor</a>
    <h3>All Doctors</h3>

    <form method="GET" class="form-inline mb-3">
        <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>" class="form-control mr-2">
        <button class="btn btn-primary">Search</button>
        <a href="view_doctors.php" class="btn btn-secondary ml-2">Reset</a>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Specialization</th>
                    <th>Image</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php
                if ($res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['doctor_id']}</td>
                            <td>{$row['full_name']}</td>
                            <td>{$row['email']}</td>
                            <td>{$row['specialization']}</td>
                            <td>";
                        if (!empty($row['profile_image']) && file_exists($row['profile_image'])) {
                            echo "<img src='{$row['profile_image']}' width='60'>";
                        } else {
                            echo "No Image";
                        }
                        echo "</td>
                            <td>{$row['status']}</td>
                            <td>
                                <a href='view_doctor_details.php?id={$row['doctor_id']}' class='btn btn-dark btn-sm'>View Details</a>
                                <a href='edit_doctor.php?id={$row['doctor_id']}' class='btn btn-warning btn-sm'>Edit</a>
                                <a href='view_doctors.php?delete_id={$row['doctor_id']}' onclick=\"return confirm('Delete this doctor?');\" class='btn btn-danger btn-sm'>Delete</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No doctors found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
