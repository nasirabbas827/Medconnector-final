<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['doctor_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

// Get doctor info
$stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$stmt->close();

$today = date('Y-m-d');

// NEW: Get the single latest appointment for the top notification
$latest_appt_sql = "
    SELECT a.appointment_id, a.start_time, p.name AS patient_name 
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    WHERE a.doctor_id = ? 
    ORDER BY a.created_at DESC LIMIT 1";
$l_stmt = $conn->prepare($latest_appt_sql);
$l_stmt->bind_param("i", $doctor_id);
$l_stmt->execute();
$latest_notification = $l_stmt->get_result()->fetch_assoc();
$l_stmt->close();

// 1. Get pending appointments (notifications)
$pending_sql = "
    SELECT a.appointment_id, a.appointment_date, a.start_time, a.reason,
            p.mr_number, p.name AS patient_name, p.phone
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    WHERE a.doctor_id = ? 
      AND a.status = 'pending'
    ORDER BY a.created_at DESC
    LIMIT 8";
$pending_stmt = $conn->prepare($pending_sql);
$pending_stmt->bind_param("i", $doctor_id);
$pending_stmt->execute();
$pending_appointments = $pending_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$pending_stmt->close();

// 2. Today's confirmed & pending appointments
$today_sql = "
    SELECT a.appointment_id, a.appointment_date, a.start_time, a.end_time, a.status, a.reason,
            p.mr_number, p.name AS patient_name, p.phone, p.gender, p.date_of_birth
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    WHERE a.doctor_id = ? 
      AND a.appointment_date = ?
      AND a.status IN ('pending', 'confirmed')
    ORDER BY a.start_time ASC";
$today_stmt = $conn->prepare($today_sql);
$today_stmt->bind_param("is", $doctor_id, $today);
$today_stmt->execute();
$today_appointments = $today_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$today_stmt->close();

// 3. Fetch ALL appointments for the Calendar
$cal_sql = "SELECT a.appointment_date, a.start_time, a.end_time, a.status, p.name 
            FROM appointments a 
            JOIN patients p ON a.patient_id = p.id 
            WHERE a.doctor_id = ?";
$cal_stmt = $conn->prepare($cal_sql);
$cal_stmt->bind_param("i", $doctor_id);
$cal_stmt->execute();
$cal_res = $cal_stmt->get_result();
$events = [];
while($row = $cal_res->fetch_assoc()) {
    $events[] = [
        'title' => $row['name'],
        'start' => $row['appointment_date'] . 'T' . $row['start_time'],
        'end' => $row['appointment_date'] . 'T' . $row['end_time'],
        'extendedProps' => ['status' => $row['status']]
    ];
}
$cal_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard | <?= htmlspecialchars($doctor['full_name']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
    <style>
        :root { --primary: #4e73df; --success: #1cc88a; --info: #36b9cc; --warning: #f6c23e; --danger: #e74a3b; }
        body { background-color: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .card { border: none; border-radius: 10px; transition: all 0.3s ease; margin-bottom: 1.5rem; }
        .card-header { border-top-left-radius: 10px !important; border-top-right-radius: 10px !important; font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
        
        /* Sidebar Polish */
        .doctor-profile-img { width: 80px; height: 80px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 2rem; color: var(--primary); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        
        /* List Items */
        .list-group-item { border-left: none; border-right: none; border-color: #f1f1f1; padding: 15px; }
        .list-group-item:hover { background-color: #fcfcfc; }
        
        /* Calendar Styling */
        #calendar { background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .fc-toolbar-title { font-size: 1.25rem !important; font-weight: bold; color: #333; }
        
        /* Custom Event Status Colors */
        .fc-event-pending { background-color: var(--warning) !important; border-color: var(--warning) !important; color: #000 !important; }
        .fc-event-confirmed { background-color: var(--success) !important; border-color: var(--success) !important; color: #fff !important; }
        .fc-event-cancelled { background-color: var(--danger) !important; border-color: var(--danger) !important; color: #fff !important; }
        
        .notification-badge { position: absolute; top: -8px; right: -8px; border-radius: 50%; padding: 4px 8px; }

        /* Latest Appointment Alert Styling */
        .alert-latest {
            background: #fff;
            border-left: 5px solid var(--primary);
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            border-radius: 8px;
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container-fluid mt-4">
    <?php if ($latest_notification): ?>
    <div class="row mb-3" id="latest-alert-row">
        <div class="col-12">
            <div class="alert alert-latest alert-dismissible fade show d-flex align-items-center" role="alert">
                <div class="bg-primary text-white rounded-circle p-2 mr-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Latest Booking</span>
                    <strong><?= htmlspecialchars($latest_notification['patient_name']) ?></strong> scheduled for <?= date('h:i A', strtotime($latest_notification['start_time'])) ?>.
                </div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm text-center p-3">
                <div class="doctor-profile-img">
                    <i class="fas fa-user-md"></i>
                </div>
                <h5 class="font-weight-bold mb-0">Dr. <?= htmlspecialchars($doctor['full_name']) ?></h5>
                <p class="text-primary small mb-3"><?= htmlspecialchars($doctor['specialization'] ?: 'General Physician') ?></p>
                
                <div class="text-left px-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Experience</span>
                        <span class="font-weight-bold small"><?= $doctor['experience_years'] ?> Yrs</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Status</span>
                        <span class="badge badge-pill badge-<?= $doctor['status'] === 'active' ? 'success' : 'warning' ?>">
                            <?= ucfirst($doctor['status']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white text-warning border-bottom">
                    <i class="fas fa-bell mr-2"></i> New Requests
                    <?php if (count($pending_appointments) > 0): ?>
                        <span class="badge badge-danger notification-badge"><?= count($pending_appointments) ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($pending_appointments)): ?>
                        <div class="text-center py-4 text-muted small">No new pending requests</div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($pending_appointments as $appt): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong class="text-dark d-block"><?= htmlspecialchars($appt['patient_name']) ?></strong>
                                            <small class="text-muted"><i class="fas fa-clock mr-1"></i> <?= date('h:i A', strtotime($appt['start_time'])) ?></small>
                                        </div>
                                        <div class="btn-group">
                                            <a href="appointment_action.php?id=<?= $appt['appointment_id'] ?>&action=confirm" class="btn btn-xs btn-outline-success border-0"><i class="fas fa-check"></i></a>
                                            <a href="appointment_action.php?id=<?= $appt['appointment_id'] ?>&action=cancel" class="btn btn-xs btn-outline-danger border-0"><i class="fas fa-times"></i></a>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-clipboard-list mr-2"></i> Today's Schedule
                </div>
                <div class="card-body p-0">
                    <?php if (empty($today_appointments)): ?>
                        <div class="text-center py-4 text-muted small">No appointments for today</div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($today_appointments as $appt): ?>
                                <li class="list-group-item border-left-success">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="font-weight-bold"><?= htmlspecialchars($appt['patient_name']) ?></span>
                                            <small class="d-block text-muted"><?= $appt['mr_number'] ?> | <?= ucfirst($appt['gender']) ?></small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-light border"><?= date('h:i A', strtotime($appt['start_time'])) ?></span>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-calendar-alt mr-2"></i> Appointment Calendar</span>
                    <button onclick="location.reload()" class="btn btn-sm btn-outline-light border-0"><i class="fas fa-sync"></i></button>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        themeSystem: 'bootstrap',
        events: <?= json_encode($events) ?>,
        eventDisplay: 'block',
        height: 'auto',
        eventClassNames: function(arg) {
            if (arg.event.extendedProps.status === 'pending') return ['fc-event-pending'];
            if (arg.event.extendedProps.status === 'confirmed') return ['fc-event-confirmed'];
            if (arg.event.extendedProps.status === 'cancelled') return ['fc-event-cancelled'];
            return [];
        },
        eventClick: function(info) {
            alert('Patient: ' + info.event.title + '\nStatus: ' + info.event.extendedProps.status);
        }
    });
    calendar.render();
});
</script>
</body>
</html>