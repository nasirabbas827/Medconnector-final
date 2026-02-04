<?php
include('config.php');
session_start();

// Security Check
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// --- DATA FETCHING ---
$total_patients = $conn->query("SELECT COUNT(*) as count FROM patients")->fetch_assoc()['count'];
$total_doctors = $conn->query("SELECT COUNT(*) as count FROM doctors")->fetch_assoc()['count'];
$total_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(consultation_fee) as total FROM appointments a JOIN doctors d ON a.doctor_id = d.doctor_id WHERE a.status='completed'")->fetch_assoc()['total'] ?? 0;

$recent_appointments = $conn->query("SELECT a.*, p.name as patient_name, d.full_name as doctor_name 
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    JOIN doctors d ON a.doctor_id = d.doctor_id 
    WHERE a.appointment_date = CURDATE() ORDER BY a.start_time ASC");

$events_res = $conn->query("SELECT a.appointment_id, p.name as title, a.appointment_date as start, a.status 
                            FROM appointments a JOIN patients p ON a.patient_id = p.id");
$events = [];
while($row = $events_res->fetch_assoc()){
    $row['color'] = ($row['status'] == 'confirmed') ? '#28a745' : (($row['status'] == 'pending') ? '#ffc107' : '#dc3545');
    $events[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Med Connect</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="../css/style.css">

    <style>
        
        .dashboard-wrapper { padding-top: 30px; padding-bottom: 50px; }

        /* Modern Stat Cards */
        .stat-card {
            border: none;
            border-radius: 15px;
            color: white;
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .bg-patients { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-doctors { background: linear-gradient(135deg, #2af598 0%, #009efd 100%); }
        .bg-appts { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); }
        .bg-revenue { background: linear-gradient(135deg, #ff0844 0%, #ffb199 100%); }

        .stat-card i {
            position: absolute;
            right: 15px;
            bottom: 10px;
            font-size: 3rem;
            opacity: 0.2;
        }

        /* Calendar Styling */
        #calendar {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: none;
        }
        .fc-header-toolbar { margin-bottom: 1.5rem !important; }

        /* Notification Feed */
        .feed-card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .notification-item {
            border-left: 4px solid #4e73df;
            margin-bottom: 12px;
            background: #fff;
            border-radius: 8px;
            transition: 0.2s;
            border-top: 1px solid #eee;
            border-right: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        .notification-item:hover { background: #fcfcfc; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        
        .badge-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .badge-confirmed { background: #e8f5e9; color: #2e7d32; }
        .badge-pending { background: #fff8e1; color: #f57f17; }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container dashboard-wrapper">
    <div class="row align-items-center mb-4">
        <div class="col-md-6 text-center text-md-left">
            <h2 class="font-weight-bold text-dark">Admin Command Center</h2>
            <p class="text-muted">Welcome back! Here is what's happening today.</p>
        </div>
        <div class="col-md-6 text-center text-md-right">
            <span class="badge badge-white border px-3 py-2 shadow-sm">
                <i class="far fa-calendar-alt mr-2 text-primary"></i> <?php echo date('l, jS F Y'); ?>
            </span>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-patients p-4 h-100">
                <small class="text-uppercase font-weight-bold">Total Patients</small>
                <h2 class="mb-0 mt-2 font-weight-bold"><?= number_format($total_patients) ?></h2>
                <i class="fas fa-user-injured"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-doctors p-4 h-100">
                <small class="text-uppercase font-weight-bold">Active Doctors</small>
                <h2 class="mb-0 mt-2 font-weight-bold"><?= $total_doctors ?></h2>
                <i class="fas fa-user-md"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-appts p-4 h-100">
                <small class="text-uppercase font-weight-bold">Total Appointments</small>
                <h2 class="mb-0 mt-2 font-weight-bold"><?= $total_appointments ?></h2>
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-revenue p-4 h-100">
                <small class="text-uppercase font-weight-bold">Total Revenue</small>
                <h2 class="mb-0 mt-2 font-weight-bold">Rs <?= number_format($total_revenue) ?></h2>
                <i class="fas fa-wallet"></i>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-calendar-alt mr-2 text-primary"></i>Schedule Overview</h5>
                </div>
                <div class="card-body">
                    <div id='calendar'></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card feed-card mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-broadcast-tower mr-2 text-danger"></i>Today's Feed</h5>
                    <span class="badge badge-pill badge-danger"><?= $recent_appointments->num_rows ?></span>
                </div>
                <div class="card-body px-3 py-3">
                    <?php if($recent_appointments->num_rows > 0): ?>
                        <?php while($app = $recent_appointments->fetch_assoc()): ?>
                            <div class="notification-item p-3 shadow-sm">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-0 font-weight-bold text-dark"><?= htmlspecialchars($app['patient_name']) ?></h6>
                                        <small class="text-muted">Seeing: Dr. <?= htmlspecialchars($app['doctor_name']) ?></small>
                                    </div>
                                    <small class="font-weight-bold text-primary"><?= date('h:i A', strtotime($app['start_time'])) ?></small>
                                </div>
                                <div class="mt-2">
                                    <?php $status_class = ($app['status'] == 'confirmed') ? 'badge-confirmed' : 'badge-pending'; ?>
                                    <span class="badge <?= $status_class ?> px-2 py-1">
                                        <span class="badge-dot" style="background-color: currentColor;"></span>
                                        <?= ucfirst($app['status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-coffee fa-3x mb-3 opacity-25"></i>
                            <p>No appointments scheduled for today.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm bg-dark text-white p-2">
                <div class="card-body">
                    <h6 class="text-uppercase small font-weight-bold mb-3">System Actions</h6>
                    <a href="add_doctor.php" class="btn btn-primary btn-block mb-2 shadow-sm">
                        <i class="fas fa-plus-circle mr-2"></i> Register Doctor
                    </a>
                    <a href="generate_report.php" class="btn btn-outline-light btn-block">
                        <i class="fas fa-file-download mr-2"></i> Export Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        themeSystem: 'Bootstrap',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        events: <?php echo json_encode($events); ?>,
        eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
        eventClick: function(info) {
            alert('Appointment: ' + info.event.title + '\nStatus: ' + info.event.extendedProps.status);
        }
    });
    calendar.render();
});
</script>

</body>
</html>