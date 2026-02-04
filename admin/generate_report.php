<?php
include('config.php');
session_start();

// Security Check
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle CSV Export Request
if (isset($_POST['export_csv'])) {
    $from = $_POST['date_from'];
    $to = $_POST['date_to'];
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Medical_Report_'.$from.'_to_'.$to.'.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Patient Name', 'Doctor Name', 'Date', 'Time', 'Fee', 'Status'));
    
    $query = "SELECT a.appointment_id, p.name as p_name, d.full_name as d_name, a.appointment_date, a.start_time, d.consultation_fee, a.status 
              FROM appointments a 
              JOIN patients p ON a.patient_id = p.id 
              JOIN doctors d ON a.doctor_id = d.doctor_id 
              WHERE a.appointment_date BETWEEN '$from' AND '$to'";
    
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Default View Logic
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // Default to start of month
$date_to = $_GET['date_to'] ?? date('Y-m-d');

$report_query = "SELECT a.*, p.name as patient_name, d.full_name as doctor_name, d.consultation_fee 
                FROM appointments a 
                JOIN patients p ON a.patient_id = p.id 
                JOIN doctors d ON a.doctor_id = d.doctor_id 
                WHERE a.appointment_date BETWEEN '$date_from' AND '$date_to'
                ORDER BY a.appointment_date DESC";
$report_res = $conn->query($report_query);

// Summary Stats
$summary = $conn->query("SELECT COUNT(*) as total_appts, SUM(d.consultation_fee) as total_rev 
                        FROM appointments a JOIN doctors d ON a.doctor_id = d.doctor_id 
                        WHERE a.appointment_date BETWEEN '$date_from' AND '$date_to' AND a.status='completed'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial & Appointment Report | Med Connect</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
            body { background: white; }
        }
        .report-header { border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px; }
        .stats-highlight { background: #f8f9fa; border-radius: 10px; padding: 20px; border-left: 5px solid #007bff; }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="card shadow-sm mb-4 no-print border-0">
        <div class="card-body">
            <h5 class="font-weight-bold mb-4">Generate Custom Report</h5>
            <form method="GET" class="form-row align-items-end">
                <div class="col-md-4">
                    <label class="small font-weight-bold">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                </div>
                <div class="col-md-4">
                    <label class="small font-weight-bold">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-block">Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0 p-4">
        <div class="report-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="font-weight-bold text-primary mb-0">Med Connect Hospital</h2>
                <p class="text-muted mb-0">Performance & Revenue Report</p>
            </div>
            <div class="text-right no-print">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="date_from" value="<?= $date_from ?>">
                    <input type="hidden" name="date_to" value="<?= $date_to ?>">
                    <button type="submit" name="export_csv" class="btn btn-outline-success mr-2">
                        <i class="fas fa-file-csv mr-1"></i> Export CSV
                    </button>
                </form>
                <button onclick="window.print()" class="btn btn-dark">
                    <i class="fas fa-print mr-1"></i> Print Report
                </button>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stats-highlight">
                    <small class="text-muted text-uppercase">Reporting Period</small>
                    <h5 class="mb-0"><?= date('M d, Y', strtotime($date_from)) ?> - <?= date('M d, Y', strtotime($date_to)) ?></h5>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="stats-highlight" style="border-left-color: #28a745;">
                    <small class="text-muted text-uppercase">Total Completed</small>
                    <h5 class="mb-0 font-weight-bold text-success"><?= $summary['total_appts'] ?: 0 ?></h5>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="stats-highlight" style="border-left-color: #ffc107;">
                    <small class="text-muted text-uppercase">Total Revenue</small>
                    <h5 class="mb-0 font-weight-bold text-dark">Rs <?= number_format($summary['total_rev'] ?: 0) ?></h5>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Status</th>
                        <th class="text-right">Consultation Fee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($report_res->num_rows > 0): ?>
                        <?php while($row = $report_res->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($row['appointment_date'])) ?></td>
                                <td class="font-weight-bold"><?= htmlspecialchars($row['patient_name']) ?></td>
                                <td>Dr. <?= htmlspecialchars($row['doctor_name']) ?></td>
                                <td>
                                    <span class="badge badge-pill badge-<?= ($row['status'] == 'completed') ? 'success' : (($row['status'] == 'cancelled') ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td class="text-right font-weight-bold">Rs <?= number_format($row['consultation_fee']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4">No records found for the selected dates.</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-light font-weight-bold">
                    <tr>
                        <td colspan="4" class="text-right">Total Revenue (Completed Only):</td>
                        <td class="text-right text-success">Rs <?= number_format($summary['total_rev'] ?: 0) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="mt-5 text-center small text-muted">
            Report generated on <?= date('Y-m-d H:i:s') ?> by Admin System.
        </div>
    </div>
</div>

</body>
</html>