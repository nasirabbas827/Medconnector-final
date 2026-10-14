# Med Connect Scheduler - Healthcare Redefined

Medconnector-final is a doctor appointment scheduling application.

![PHP](https://img.shields.io/badge/PHP-2563EB?style=flat-square) ![MySQL](https://img.shields.io/badge/MySQL-0F172A?style=flat-square)

## Overview

The data model covers `admins`, `appointments`, `doctors`, `doctor_availability`, `patients`.

## Application areas

| Area | Pages in the repository |
| --- | --- |
| Admin | [`admin/add_availability.php`](admin/add_availability.php), [`admin/add_doctor.php`](admin/add_doctor.php), [`admin/admin_home.php`](admin/admin_home.php), [`admin/admin_login.php`](admin/admin_login.php), [`admin/admin_navbar.php`](admin/admin_navbar.php) |
| Doctor | [`doctor/doctor_availability.php`](doctor/doctor_availability.php), [`doctor/doctor_dashboard.php`](doctor/doctor_dashboard.php), [`doctor/view_appointments.php`](doctor/view_appointments.php) |
| Root Pages | [`login.php`](login.php), [`register.php`](register.php) |
| Patient | [`patient/create_appointment.php`](patient/create_appointment.php), [`patient/doctor_profile.php`](patient/doctor_profile.php), [`patient/patient_dashboard.php`](patient/patient_dashboard.php), [`patient/profile.php`](patient/profile.php), [`patient/view_appointments.php`](patient/view_appointments.php) |

## Getting started

### 1. Get the project

```bash
git clone https://github.com/nasirabbas827/Medconnector-final.git
cd Medconnector-final
```

### 2. Configure PHP and the database

1. Use a local PHP server with MySQL or MariaDB, such as XAMPP.
2. Import [`Database/medconnect_db.sql`](Database/medconnect_db.sql) into your local database server.
3. Set your local database connection details in [`admin/config.php`](admin/config.php), [`config.php`](config.php), [`doctor/config.php`](doctor/config.php), [`patient/config.php`](patient/config.php).
4. Keep the existing asset and upload directory paths intact.

### 3. Start the application

With the database running, start a local development server from the repository root:

```bash
php -S 127.0.0.1:8000
```

Open http://127.0.0.1:8000/index.php. If using Apache/XAMPP instead, place the project in its document root and open the matching localhost folder.

## Repository guide

| Path | Purpose |
| --- | --- |
| [`Database`](Database) | Database scripts |
| [`config.php`](config.php) | Application/database configuration |
| [`css`](css) | Stylesheets |
| [`images`](images) | Image assets |
| [`index.php`](index.php) | Web entry page |

## Development notes

These setup instructions are based on the repository source. The application has not been executed as part of this documentation update; verify dependencies and configuration in your local environment.
