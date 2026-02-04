<?php
session_start();
require_once('config.php');

// Fetch Real-time Stats for the Landing Page
$total_patients = $conn->query("SELECT COUNT(*) as count FROM patients")->fetch_assoc()['count'];
$total_doctors = $conn->query("SELECT COUNT(*) as count FROM doctors WHERE status='active'")->fetch_assoc()['count'];
$today_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = CURDATE()")->fetch_assoc()['count'];
// Assuming a default number for lab tests if table doesn't exist yet, or query your labs table
$total_lab_tests = 1250; 

// Fetch 3 Featured Doctors
$featured_doctors = $conn->query("SELECT * FROM doctors WHERE status='active' LIMIT 3");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Med Connect Scheduler - Healthcare Redefined</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/style.css">

    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #3b82f6;
            --dark-navy: #1e293b;
            --soft-bg: #f8fafc;
        }

        body {   background-color: var(--soft-bg); color: #334155; }

        /* Modern Hero Section */
        .hero-section {
            position: relative;
            padding: 160px 0 100px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.9) 0%, rgba(30, 41, 59, 0.95) 100%), 
                        url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            clip-path: ellipse(150% 100% at 50% 0%);
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -1px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 2rem;
        }

        /* Stats Cards */
        .stat-box {
            background: white;
            border: none;
            border-radius: 24px;
            padding: 40px 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            margin-top: -50px;
            z-index: 10;
            position: relative;
        }
        .stat-box:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .stat-box i { font-size: 2.5rem; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 15px; }

        /* Section Styling */
        .section-padding { padding: 100px 0; }
        .section-title { font-weight: 800; font-size: 2.5rem; margin-bottom: 50px; color: var(--dark-navy); }

        /* Doctor Cards */
        .doc-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .doc-img { height: 250px; width: 100%; object-fit: cover; }
        .doc-info { padding: 25px; }

        .btn-modern {
            padding: 12px 35px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary-modern { background: var(--primary-color); color: white; border: none; }
        .btn-primary-modern:hover { background: var(--dark-navy); transform: scale(1.05); }

        footer { background: var(--dark-navy); color: #94a3b8; padding: 80px 0 30px; }
        footer h5 { color: white; font-weight: 700; margin-bottom: 25px; }
        footer a { color: #94a3b8; transition: 0.3s; }
        footer a:hover { color: var(--secondary-color); text-decoration: none; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 hero-content">
                <span class="badge badge-pill badge-primary mb-3 px-3 py-2">Trusted Healthcare Partner</span>
                <h1 class="text-white">Effortless Access to <br><span class="text-info">Expert Medical Care</span></h1>
                <p class="lead text-white-50 mt-4 mb-5">Med Connect Scheduler bridges the gap between patients and doctors with real-time booking and personalized healthcare management.</p>
                
                <div class="d-flex flex-wrap">
                    <?php if (!isset($_SESSION['patient_id']) && !isset($_SESSION['doctor_id'])): ?>
                        <a href="login.php" class="btn btn-primary-modern btn-modern mr-3 mb-3">Get Started Now</a>
                        <a href="#services" class="btn btn-outline-light btn-modern mb-3">Our Services</a>
                    <?php else: ?>
                        <a href="patient/patient_dashboard.php" class="btn btn-primary-modern btn-modern mb-3">
                            <i class="fas fa-th-large mr-2"></i> Access Your Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="glass-card">
                    <h4 class="text-white mb-4">Quick Appointment</h4>
                    <p class="small text-white-50">Join <?php echo $total_patients; ?>+ patients receiving quality care every day.</p>
                    <hr class="bg-white opacity-25">
                    <div class="d-flex align-items-center mt-3">
                        <div class="rounded-circle bg-success mr-3" style="width:10px; height:10px;"></div>
                        <span class="small"><?php echo $total_doctors; ?> Doctors are currently online</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <div class="row">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-box text-center">
                <i class="fas fa-user-check"></i>
                <h2 class="font-weight-bold"><?php echo $total_patients; ?>+</h2>
                <p class="text-muted mb-0">Recovered Patients</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-box text-center">
                <i class="fas fa-microscope"></i>
                <h2 class="font-weight-bold"><?php echo $total_lab_tests; ?>+</h2>
                <p class="text-muted mb-0">Lab Tests done</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-box text-center">
                <i class="fas fa-user-md"></i>
                <h2 class="font-weight-bold"><?php echo $total_doctors; ?></h2>
                <p class="text-muted mb-0">Specialist Doctors</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-box text-center">
                <i class="fas fa-hospital-user"></i>
                <h2 class="font-weight-bold"><?php echo $today_appointments; ?></h2>
                <p class="text-muted mb-0">Daily Visits</p>
            </div>
        </div>
    </div>
</div>

<section id="services" class="section-padding">
    <div class="container text-center">
        <h6 class="text-primary font-weight-bold text-uppercase">What we offer</h6>
        <h2 class="section-title">Comprehensive Care Services</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card p-5 h-100 border-0 shadow-sm rounded-xl">
                    <i class="fas fa-laptop-medical text-primary fa-3x mb-4"></i>
                    <h5 class="font-weight-bold">Online Booking</h5>
                    <p class="text-muted">Book appointments 24/7 with real-time slot availability from your home.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card p-5 h-100 border-0 shadow-sm rounded-xl">
                    <i class="fas fa-notes-medical text-success fa-3x mb-4"></i>
                    <h5 class="font-weight-bold">Health Records</h5>
                    <p class="text-muted">Access your medical history, prescriptions, and lab results in one secure place.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card p-5 h-100 border-0 shadow-sm rounded-xl">
                    <i class="fas fa-ambulance text-danger fa-3x mb-4"></i>
                    <h5 class="font-weight-bold">Emergency Support</h5>
                    <p class="text-muted">Rapid response medical services and priority scheduling for urgent cases.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light">
    <div class="container text-center">
        <h2 class="section-title">Meet Our Specialists</h2>
        <div class="row">
            <?php while($doc = $featured_doctors->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="doc-card text-left">
                    <img src="admin/<?php echo $doc['profile_image'] ?: 'uploads/default_doctor.png'; ?>" class="doc-img">
                    <div class="doc-info">
                        <h5 class="font-weight-bold mb-1"><?php echo $doc['full_name']; ?></h5>
                        <p class="text-primary small font-weight-bold mb-3"><?php echo $doc['specialization']; ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-warning small"><i class="fas fa-star"></i> <?php echo $doc['rating']; ?> (Review)</span>
                            <a href="patient/doctor_profile.php?id=<?php echo $doc['doctor_id']; ?>" class="btn btn-sm btn-outline-primary">View Profile</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-5">
                <h5 class="text-info">Med Connect</h5>
                <p>Redefining healthcare through technology. We make medical scheduling simple, fast, and accessible for everyone.</p>
                <div class="mt-4">
                    <a href="#" class="mr-3"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="mr-3"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#"><i class="fab fa-linkedin fa-lg"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 mb-5">
                <h5>Platform</h5>
                <ul class="list-unstyled">
                    <li><a href="#">Doctors List</a></li>
                    <li><a href="#">Services</a></li>
                    <li><a href="#">How it works</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 mb-5">
                <h5>Support</h5>
                <ul class="list-unstyled">
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Use</a></li>
                </ul>
            </div>
            <div class="col-lg-3 mb-5">
                <h5>Newsletter</h5>
                <p class="small">Subscribe for health tips and updates.</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control bg-dark border-0 text-white" placeholder="Email">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button">Join</button>
                    </div>
                </div>
            </div>
        </div>
        <hr class="border-secondary">
        <div class="text-center small mt-4">
            &copy; 2026 Med Connect Scheduler. All Rights Reserved.
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>