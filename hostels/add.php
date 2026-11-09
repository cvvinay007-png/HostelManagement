
<?php
include '../config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['hostel_name']);
    $location = trim($_POST['location']);
    $gender = $_POST['gender'];
    $totalRooms = (int) $_POST['total_rooms'];

    if ($name === "" || $location === "" || $totalRooms < 1) {
        $error = "Please enter valid hostel details.";
    } else {

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO hostel
            (Hostel_Name, Location, Gender, Total_Rooms)
            VALUES (?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $name,
            $location,
            $gender,
            $totalRooms
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: view.php?added=1");
            exit();
        } else {
            $error = "Unable to add hostel: " .
                     mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Hostel | Hostel Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        body {
            background: #f4f6fb;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #1e293b;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            background: #172554;
            padding: 25px 15px;
        }

        .brand {
            color: white;
            text-align: center;
            font-size: 23px;
            font-weight: bold;
            margin-bottom: 38px;
        }

        .sidebar a {
            display: block;
            color: #cbd5e1;
            text-decoration: none;
            padding: 13px 15px;
            margin-bottom: 7px;
            border-radius: 8px;
        }

        .sidebar a i { width: 25px; }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2563eb;
            color: white;
        }

        .main-content { margin-left: 250px; }

        .topbar {
            background: white;
            padding: 23px 35px;
            box-shadow: 0 2px 10px rgba(0,0,0,.04);
        }

        .page-content { padding: 35px; }

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,.04);
            max-width: 850px;
        }

        .form-label {
            font-weight: 600;
            color: #334155;
        }

        .form-control,
        .form-select {
            padding: 11px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }

        .btn-primary {
            background: #2563eb;
            border: none;
            padding: 11px 22px;
        }

        @media(max-width:768px) {
            .sidebar {
                width: 70px;
                padding: 20px 8px;
            }

            .brand { font-size: 0; }

            .brand i { font-size: 22px; }

            .sidebar a {
                font-size: 0;
                text-align: center;
            }

            .sidebar a i { font-size: 17px; }

            .main-content { margin-left: 70px; }
            .page-content { padding: 15px; }
        }
    </style>
</head>

<body>

<aside class="sidebar">

    <div class="brand">
        <i class="fa-solid fa-building-user"></i>
        Hostel Admin
    </div>

    <a href="../index.php">
        <i class="fa-solid fa-chart-pie"></i> Dashboard
    </a>

    <a href="../students/view.php">
        <i class="fa-solid fa-users"></i> Students
    </a>

    <a href="view.php" class="active">
        <i class="fa-solid fa-building"></i> Hostels
    </a>

    <a href="../rooms/view.php">
        <i class="fa-solid fa-door-open"></i> Rooms
    </a>

    <a href="../allocations/view.php">
        <i class="fa-solid fa-bed"></i> Allocations
    </a>

    <a href="../payments/view.php">
        <i class="fa-solid fa-wallet"></i> Payments
    </a>

</aside>

<main class="main-content">

    <div class="topbar">
        <h5 class="mb-0">Add New Hostel</h5>
    </div>

    <section class="page-content">

        <h2 class="fw-bold">Register Hostel</h2>

        <p class="text-muted mb-4">
            Enter the hostel information below.
        </p>

        <div class="form-card">

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="row g-4">

                    <div class="col-md-6">
                        <label class="form-label">Hostel Name *</label>

                        <input
                            type="text"
                            name="hostel_name"
                            class="form-control"
                            placeholder="e.g. Boys Hostel A"
                            maxlength="100"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Location *</label>

                        <input
                            type="text"
                            name="location"
                            class="form-control"
                            placeholder="Enter hostel location"
                            maxlength="150"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Hostel Gender *</label>

                        <select name="gender" class="form-select" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Boys Hostel</option>
                            <option value="Female">Girls Hostel</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Total Rooms *</label>

                        <input
                            type="number"
                            name="total_rooms"
                            class="form-control"
                            placeholder="Enter total rooms"
                            min="1"
                            required
                        >
                    </div>

                </div>

                <hr class="my-4">

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save me-2"></i>
                    Save Hostel
                </button>

                <a href="view.php" class="btn btn-light ms-2">
                    Cancel
                </a>

            </form>

        </div>

    </section>

</main>

</body>
</html>
