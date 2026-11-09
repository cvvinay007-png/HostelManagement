
<?php
include 'config/db.php';

// Dashboard statistics
$studentQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM student");
$totalStudents = mysqli_fetch_assoc($studentQuery)['total'];

$hostelQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM hostel");
$totalHostels = mysqli_fetch_assoc($hostelQuery)['total'];

$roomQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM room");
$totalRooms = mysqli_fetch_assoc($roomQuery)['total'];

$allocationQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM allocation WHERE Status = 'Active'"
);
$totalAllocations = mysqli_fetch_assoc($allocationQuery)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hostel Management System</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="sidebar">
    <h3>Hostel Admin</h3>

    <a href="index.php" class="active">Dashboard</a>
    <a href="students/view.php">Students</a>
    <a href="hostels/view.php">Hostels</a>
    <a href="rooms/view.php">Rooms</a>
    <a href="allocations/view.php">Allocations</a>
    <a href="payments/view.php">Payments</a>
</div>

<div class="main-content">

    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid">
            <h4>Dashboard</h4>
            <span>Admin Panel</span>
        </div>
    </nav>

    <div class="container-fluid">

        <h2>Welcome to Hostel Management System</h2>
        <p class="text-muted">Here's your hostel overview.</p>

        <div class="row g-4 mt-2">

            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <h5>Total Students</h5>
                    <h2><?php echo $totalStudents; ?></h2>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <h5>Total Hostels</h5>
                    <h2><?php echo $totalHostels; ?></h2>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <h5>Total Rooms</h5>
                    <h2><?php echo $totalRooms; ?></h2>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <h5>Active Allocations</h5>
                    <h2><?php echo $totalAllocations; ?></h2>
                </div>
            </div>

        </div>

    </div>

</div>

</body>
</html>
