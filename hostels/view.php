
<?php
include '../config/db.php';

$sql = "SELECT * FROM hostel ORDER BY Hostel_ID DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$totalHostels = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hostel Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
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
            font-size: 23px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 38px;
        }

        .brand i { color: #60a5fa; }

        .menu-label {
            color: #94a3b8;
            font-size: 11px;
            letter-spacing: 1.5px;
            margin: 20px 12px 12px;
        }

        .sidebar a {
            display: block;
            color: #cbd5e1;
            text-decoration: none;
            padding: 13px 15px;
            margin-bottom: 7px;
            border-radius: 8px;
            font-size: 14px;
        }

        .sidebar a i { width: 25px; }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2563eb;
            color: white;
        }

        .main-content {
            margin-left: 250px;
            min-height: 100vh;
        }

        .topbar {
            height: 75px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 35px;
            box-shadow: 0 2px 10px rgba(0,0,0,.04);
        }

        .topbar h5 { margin: 0; font-weight: 600; }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #dbeafe;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page-content { padding: 35px; }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .page-heading h2 {
            font-size: 25px;
            font-weight: 700;
        }

        .page-heading p {
            color: #64748b;
            margin: 0;
        }

        .btn-primary {
            background: #2563eb;
            border: none;
            padding: 11px 18px;
            border-radius: 8px;
        }

        .stats-card, .table-card {
            background: white;
            border-radius: 12px;
            padding: 23px;
            box-shadow: 0 3px 12px rgba(0,0,0,.04);
        }

        .stats-card {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 25px;
        }

        .stats-icon {
            width: 52px;
            height: 52px;
            background: #dbeafe;
            color: #2563eb;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
        }

        .stats-card h3 {
            margin: 0;
            font-size: 25px;
            font-weight: 700;
        }

        .stats-card p {
            margin: 3px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        .table-card h5 { font-weight: 700; }

        .table {
            vertical-align: middle;
            white-space: nowrap;
            font-size: 14px;
        }

        .table thead th {
            background: #172554;
            color: white;
            padding: 15px;
            border: none;
        }

        .table tbody td { padding: 15px; }

        .hostel-name {
            color: #172554;
            font-weight: 600;
        }

        .hostel-id {
            background: #eff6ff;
            color: #2563eb;
            padding: 5px 9px;
            border-radius: 6px;
        }

        .gender-badge {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 6px 10px;
            border-radius: 20px;
        }

        @media(max-width:768px) {
            .sidebar {
                width: 70px;
                padding: 20px 8px;
            }

            .brand { font-size: 0; }
            .brand i { font-size: 22px; }

            .menu-label { display: none; }

            .sidebar a {
                font-size: 0;
                text-align: center;
                padding: 14px 5px;
            }

            .sidebar a i { font-size: 17px; }

            .main-content { margin-left: 70px; }
            .page-content { padding: 20px 12px; }
            .topbar { padding: 0 15px; }
        }
    </style>
</head>

<body>

<aside class="sidebar">

    <div class="brand">
        <i class="fa-solid fa-building-user"></i>
        Hostel Admin
    </div>

    <p class="menu-label">MAIN MENU</p>

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

    <nav class="topbar">
        <h5>Hostel Management</h5>

        <div class="d-flex align-items-center gap-2">
            <div class="avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <span class="fw-semibold">Administrator</span>
        </div>
    </nav>

    <section class="page-content">

        <div class="page-heading">
            <div>
                <h2>Hostels</h2>
                <p>Manage all registered hostels.</p>
            </div>

            <a href="add.php" class="btn btn-primary">
                <i class="fa-solid fa-plus me-2"></i>
                Add Hostel
            </a>
        </div>

        <div class="row">
            <div class="col-md-5 col-lg-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fa-solid fa-building"></i>
                    </div>

                    <div>
                        <h3><?php echo $totalHostels; ?></h3>
                        <p>Total Registered Hostels</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-card">

            <h5 class="mb-4">
                <i class="fa-solid fa-list me-2 text-primary"></i>
                Hostel Records
            </h5>

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>
                        <tr>
                            <th>Hostel ID</th>
                            <th>Hostel Name</th>
                            <th>Location</th>
                            <th>Gender</th>
                            <th>Total Rooms</th>
			    <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($totalHostels > 0): ?>

                        <?php while ($row = mysqli_fetch_assoc($result)): ?>

                            <tr>
                                <td>
                                    <span class="hostel-id">
                                        #<?php echo (int)$row['Hostel_ID']; ?>
                                    </span>
                                </td>

                                <td class="hostel-name">
                                    <?php echo htmlspecialchars($row['Hostel_Name']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['Location'] ?? ''); ?>
                                </td>

                                <td>
                                    <span class="gender-badge">
                                        <?php echo htmlspecialchars($row['Gender'] ?? ''); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php echo (int)$row['Total_Rooms']; ?>
                                </td>
				<td>
    <a href="edit.php?id=<?php echo (int)$row['Hostel_ID']; ?>"
       class="btn btn-sm btn-warning">
        <i class="fa-solid fa-pen"></i> Edit
    </a>

    <form action="delete.php" method="POST"
          class="d-inline"
          onsubmit="return confirm('Are you sure you want to delete this hostel?');">

        <input type="hidden" name="id"
               value="<?php echo (int)$row['Hostel_ID']; ?>">

        <button type="submit" class="btn btn-sm btn-danger">
            <i class="fa-solid fa-trash"></i> Delete
        </button>

    </form>
</td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="text-center py-4">
                                No hostels registered yet.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>

            </div>
        </div>

    </section>

</main>

</body>
</html>
