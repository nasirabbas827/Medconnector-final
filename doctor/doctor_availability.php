<?php
session_start();
require_once('../config.php');

if (!isset($_SESSION['doctor_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

/* =======================
   ADD AVAILABILITY
======================= */
if (isset($_POST['add_availability'])) {
    $day = $_POST['day_of_week'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    $stmt = $conn->prepare("
        INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("isss", $doctor_id, $day, $start, $end);

    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>Availability added successfully.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Failed to add availability.</div>";
    }
}

/* =======================
   UPDATE AVAILABILITY
======================= */
if (isset($_POST['update_availability'])) {
    $availability_id = intval($_POST['availability_id']);
    $day = $_POST['day_of_week'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    $stmt = $conn->prepare("
        UPDATE doctor_availability
        SET day_of_week=?, start_time=?, end_time=?
        WHERE availability_id=? AND doctor_id=?
    ");
    $stmt->bind_param("sssii", $day, $start, $end, $availability_id, $doctor_id);

    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>Availability updated successfully.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Failed to update availability.</div>";
    }
}

/* =======================
   DELETE AVAILABILITY
======================= */
if (isset($_GET['delete'])) {
    $availability_id = intval($_GET['delete']);

    $stmt = $conn->prepare("
        DELETE FROM doctor_availability
        WHERE availability_id=? AND doctor_id=?
    ");
    $stmt->bind_param("ii", $availability_id, $doctor_id);
    $stmt->execute();

    header("Location: doctor_availability.php");
    exit;
}

/* =======================
   FETCH AVAILABILITY
======================= */
$res = $conn->query("
    SELECT * FROM doctor_availability
    WHERE doctor_id = $doctor_id
    ORDER BY FIELD(day_of_week,'Mon','Tue','Wed','Thu','Fri','Sat','Sun'), start_time
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Availability</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>My Availability</h4>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addModal">
            Add Availability
        </button>
    </div>

    <?php if (isset($msg)) echo $msg; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Day</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($res->num_rows > 0): ?>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['day_of_week']) ?></td>
                        <td><?= $row['start_time'] ?></td>
                        <td><?= $row['end_time'] ?></td>
                        <td>
                            <button class="btn btn-sm btn-info"
                                data-toggle="modal"
                                data-target="#editModal<?= $row['availability_id'] ?>">
                                Edit
                            </button>
                            <a href="?delete=<?= $row['availability_id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this availability?')">
                                Delete
                            </a>
                        </td>
                    </tr>

                    <!-- EDIT MODAL -->
                    <div class="modal fade" id="editModal<?= $row['availability_id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Availability</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="availability_id" value="<?= $row['availability_id'] ?>">

                                        <div class="form-group">
                                            <label>Day</label>
                                            <select name="day_of_week" class="form-control" required>
                                                <?php
                                                $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                                                foreach ($days as $d) {
                                                    $sel = ($row['day_of_week'] === $d) ? 'selected' : '';
                                                    echo "<option value='$d' $sel>$d</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Start Time</label>
                                            <input type="time" name="start_time" class="form-control"
                                                   value="<?= $row['start_time'] ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label>End Time</label>
                                            <input type="time" name="end_time" class="form-control"
                                                   value="<?= $row['end_time'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="update_availability" class="btn btn-success">
                                            Save Changes
                                        </button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No availability added yet.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD MODAL -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add Availability</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <label>Day</label>
                        <select name="day_of_week" class="form-control" required>
                            <option value="">Select Day</option>
                            <option>Mon</option>
                            <option>Tue</option>
                            <option>Wed</option>
                            <option>Thu</option>
                            <option>Fri</option>
                            <option>Sat</option>
                            <option>Sun</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_availability" class="btn btn-primary">
                        Add Availability
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
