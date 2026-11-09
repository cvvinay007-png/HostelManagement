

<?php
include '../config/db.php';

$sql = "SELECT * FROM student ORDER BY Student_ID DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$totalStudents = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Management | Hostel Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6fb;
            color: #1e293b;
        }

        /* Sidebar */

        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #172554;
            padding: 25px 15px;
            z-index: 100;
        }

        .brand {
            color: white;
            font-size: 23px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 38px;
        }

        .brand i {
            color: #60a5fa;
            margin-right: 8px;
        }

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
            transition: 0.2s;
            font-size: 14px;
        }

        .sidebar a i {
            width: 25px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2563eb;
            color: white;
        }

        /* Main content */

        .main-content {
            margin-left: 250px;
            min-height: 100vh;
        }

        /* Topbar */

        .topbar {
            height: 75px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 35px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }

        .topbar h5 {
            margin: 0;
            font-weight: 600;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

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

        /* Page */

        .page-content {
            padding: 35px;
        }

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
            margin-bottom: 5px;
        }

        .page-heading p {
            color: #64748b;
            margin: 0;
            font-size: 14px;
        }

        .btn-primary {
            background: #2563eb;
            border: none;
            padding: 11px 18px;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        /* Statistics */

        .stats-card {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.04);
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
            justify-content: center;
            align-items: center;
            font-size: 21px;
        }

        .stats-card h3 {
            font-size: 25px;
            font-weight: 700;
            margin: 0;
        }

        .stats-card p {
            margin: 3px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        /* Table container */

        .table-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.04);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 22px;
        }

        .table-header h5 {
            font-weight: 700;
            margin: 0;
        }

        .search-box {
            position: relative;
            width: 270px;
        }

        .search-box i {
            position: absolute;
            left: 13px;
            top: 13px;
            color: #94a3b8;
        }

        .search-box input {
            width: 100%;
            padding: 10px 12px 10px 38px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            outline: none;
        }

        .search-box input:focus {
            border-color: #2563eb;
        }

        .table {
            white-space: nowrap;
            vertical-align: middle;
            font-size: 13px;
        }

        .table thead th {
            background: #172554;
            color: white;
            padding: 15px;
            font-weight: 500;
            border: none;
        }

        .table tbody td {
            padding: 14px 15px;
            color: #475569;
        }

        .table tbody tr:hover {
            background: #eff6ff;
        }

        .student-name {
            color: #172554;
            font-weight: 600;
        }

        .student-id {
            background: #eff6ff;
            color: #2563eb;
            padding: 5px 9px;
            border-radius: 6px;
            font-weight: 600;
        }

        .empty-message {
            padding: 30px !important;
            text-align: center;
            color: #64748b;
        }

        .footer {
            padding: 20px 0;
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
        }

        /* Mobile responsiveness */

        @media(max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 20px 8px;
            }

            .brand {
                font-size: 0;
            }

            .brand i {
                font-size: 22px;
                margin: 0;
            }

            .menu-label {
                display: none;
            }

            .sidebar a {
                text-align: center;
                font-size: 0;
                padding: 14px 5px;
            }

            .sidebar a i {
                font-size: 17px;
            }

            .main-content {
                margin-left: 70px;
            }

            .page-content {
                padding: 20px 12px;
            }

            .topbar {
                padding: 0 15px;
            }

            .search-box {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<!-- Sidebar -->

<aside class="sidebar">

    <div class="brand">
        <i class="fa-solid fa-building-user"></i>
        Hostel Admin
    </div>

    <p class="menu-label">MAIN MENU</p>

    <a href="../index.php">
        <i class="fa-solid fa-chart-pie"></i>
        Dashboard
    </a>

    <a href="view.php" class="active">
        <i class="fa-solid fa-users"></i>
        Students
    </a>

    <a href="../hostels/view.php">
        <i class="fa-solid fa-building"></i>
        Hostels
    </a>

    <a href="../rooms/view.php">
        <i class="fa-solid fa-door-open"></i>
        Rooms
    </a>

    <a href="../allocations/view.php">
        <i class="fa-solid fa-bed"></i>
        Allocations
    </a>

    <a href="../payments/view.php">
        <i class="fa-solid fa-wallet"></i>
        Payments
    </a>

</aside>


<!-- Main content -->

<main class="main-content">

    <!-- Topbar -->

    <nav class="topbar">

        <h5>Student Management</h5>

        <div class="admin-profile">
            <div class="avatar">
                <i class="fa-solid fa-user"></i>
            </div>

            <span class="fw-semibold">Administrator</span>
        </div>

    </nav>


    <section class="page-content">

        <!-- Page heading -->

        <div class="page-heading">

            <div>
                <h2>Students</h2>
                <p>Manage and monitor all registered students.</p>
            </div>

            <a href="add.php" class="btn btn-primary">
                <i class="fa-solid fa-plus me-2"></i>
                Add Student
            </a>

        </div>


        <!-- Statistics card -->

        <div class="row">

            <div class="col-md-5 col-lg-4">

                <div class="stats-card">

                    <div class="stats-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>

                    <div>
                        <h3><?php echo $totalStudents; ?></h3>
                        <p>Total Registered Students</p>
                    </div>

                </div>

            </div>

        </div>


        <!-- Student table -->

        <div class="table-card">

            <div class="table-header">

                <h5>
                    <i class="fa-solid fa-list me-2 text-primary"></i>
                    Student Records
                </h5>

                <div class="search-box">
                    <i class="fa-solid fa-search"></i>

                    <input
                        type="text"
                        id="studentSearch"
                        placeholder="Search students..."
                        onkeyup="searchStudents()"
                    >
                </div>

            </div>


            <div class="table-responsive">

                <table class="table table-hover" id="studentTable">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student Name</th>
                            <th>Gender</th>
                            <th>Date of Birth</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Address</th>
			    <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($totalStudents > 0): ?>

                        <?php while ($row = mysqli_fetch_assoc($result)): ?>

                            <tr>

                                <td>
                                    <span class="student-id">
                                        #<?php echo htmlspecialchars($row['Student_ID']); ?>
                                    </span>
                                </td>

                                <td class="student-name">
                                    <?php echo htmlspecialchars($row['Name']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['Gender'] ?? ''); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['DOB'] ?? ''); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['Phone'] ?? ''); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['Email'] ?? ''); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['Course'] ?? ''); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['Year'] ?? ''); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['Address'] ?? ''); ?>
                                </td>
				<td>
    <a href="edit.php?id=<?php echo (int)$row['Student_ID']; ?>"
       class="btn btn-sm btn-warning">
        <i class="fa-solid fa-pen"></i> Edit
    </a>

    <form action="delete.php" method="POST"
          class="d-inline"
          onsubmit="return confirm('Are you sure you want to delete this student?');">

        <input type="hidden" name="id"
               value="<?php echo (int)$row['Student_ID']; ?>">

        <button type="submit" class="btn btn-sm btn-danger">
            <i class="fa-solid fa-trash"></i> Delete
        </button>

    </form>
</td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="10" class="empty-message">
                                <i class="fa-solid fa-users fa-2x mb-3 d-block"></i>
                                No student records found.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

                <p id="noSearchResults" class="text-center text-muted mt-3"
                   style="display:none;">
                    No matching students found.
                </p>

            </div>

        </div>

        <div class="footer">
            Hostel Management System &copy; 2026
        </div>

    </section>

</main>


<script>
function searchStudents() {

    let input = document.getElementById("studentSearch")
        .value.toLowerCase();

    let table = document.getElementById("studentTable");
    let rows = table.getElementsByTagName("tbody")[0]
        .getElementsByTagName("tr");

    let visibleCount = 0;

    for (let i = 0; i < rows.length; i++) {

        let text = rows[i].innerText.toLowerCase();

        if (text.includes(input)) {
            rows[i].style.display = "";
            visibleCount++;
        } else {
            rows[i].style.display = "none";
        }

    }

    document.getElementById("noSearchResults").style.display =
        visibleCount === 0 ? "block" : "none";
}
</script>

</body>
</html>
