<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href=" patient_dashboard.php">
        MMGC – Patient Panel
    </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse"
            data-target="#navbarNav" aria-controls="navbarNav"
            aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">

            <?php if (isset($_SESSION['patient_id']) && $_SESSION['role'] === 'patient'): ?>

                <li class="nav-item">
                    <a class="nav-link" href=" patient_dashboard.php">Dashboard</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href=" view_appointments.php">Appointments</a>
                </li>

                    <li class="nav-item">
                    <a class="nav-link" href=" profile.php">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href=" logout.php">Logout</a>
                </li>

            <?php else: ?>

                <li class="nav-item">
                    <a class="nav-link" href="/admin/admin_login.php">Admin Login</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/login.php">Doctor Login</a>
                </li>

            <?php endif; ?>

        </ul>
    </div>
</nav>
