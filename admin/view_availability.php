<?php
session_start();
include('config.php');

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

// Handle delete
if(isset($_GET['delete_id'])){
    $id = intval($_GET['delete_id']);
    $del = $conn->prepare("DELETE FROM doctor_availability WHERE availability_id=?");
    $del->bind_param("i", $id);
    $del->execute();
    echo "<script>alert('Availability deleted.'); window.location='view_availability.php';</script>";
}

// Fetch availability
$query = "SELECT da.*, d.full_name FROM doctor_availability da 
          JOIN doctors d ON da.doctor_id=d.doctor_id
          ORDER BY da.doctor_id, FIELD(day_of_week,'Mon','Tue','Wed','Thu','Fri','Sat','Sun')";
$res = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Availability</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <a href="add_availability.php" class="btn btn-dark float-right">Add Availability</a>
    <h3>Doctor Availability</h3>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Doctor</th>
                    <th>Day</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if($res->num_rows > 0){
                while($row = $res->fetch_assoc()){
                    echo "<tr>
                        <td>{$row['availability_id']}</td>
                        <td>{$row['full_name']}</td>
                        <td>{$row['day_of_week']}</td>
                        <td>{$row['start_time']}</td>
                        <td>{$row['end_time']}</td>
                        <td>
                            <a href='edit_availability.php?id={$row['availability_id']}' class='btn btn-warning btn-sm'>Edit</a>
                            <a href='view_availability.php?delete_id={$row['availability_id']}' onclick=\"return confirm('Delete this availability?');\" class='btn btn-danger btn-sm'>Delete</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>No availability found.</td></tr>";
            } ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
