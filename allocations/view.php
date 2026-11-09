
<?php
include "../config/db.php";

// Handle student checkout
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["checkout"])) {

    $allocation_id = filter_input(
        INPUT_POST,
        "allocation_id",
        FILTER_VALIDATE_INT
    );

    if ($allocation_id) {

        $checkoutStmt = mysqli_prepare(
            $conn,
            "UPDATE allocation
             SET Status = 'Completed',
                 Checkout_Date = CURDATE()
             WHERE Allocation_ID = ?
             AND Status = 'Active'"
        );

        mysqli_stmt_bind_param(
            $checkoutStmt,
            "i",
            $allocation_id
        );

        if (mysqli_stmt_execute($checkoutStmt) &&
            mysqli_stmt_affected_rows($checkoutStmt) > 0) {

            mysqli_stmt_close($checkoutStmt);

            header("Location: view.php?checkout=1");
            exit();

        } else {
            mysqli_stmt_close($checkoutStmt);

            header("Location: view.php?checkout=0");
            exit();
        }
    }
}


// Fetch all allocations
$query = "
    SELECT
        a.Allocation_ID,
        a.Student_ID,
        s.Name AS Student_Name,
        s.Gender,
        s.Course,
        s.Phone,
        h.Hostel_Name,
        a.Hostel_ID,
        a.Room_No,
        a.Allocation_Date,
        a.Checkout_Date,
        a.Status
    FROM allocation a
    JOIN student s ON a.Student_ID = s.Student_ID
    JOIN hostel h ON a.Hostel_ID = h.Hostel_ID
    ORDER BY a.Allocation_ID DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database error: " . mysqli_error($conn));
}


// Dashboard statistics
$totalQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM allocation"
);

$totalAllocations = mysqli_fetch_assoc($totalQuery)["total"];


$activeQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS active
     FROM allocation
     WHERE Status = 'Active'"
);

$activeAllocations = mysqli_fetch_assoc($activeQuery)["active"];


$completedQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS completed
     FROM allocation
     WHERE Status = 'Completed'"
);

$completedAllocations = mysqli_fetch_assoc($completedQuery)["completed"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Room Allocations | Hostel Management System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            margin: 0;
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .sidebar {
            width: 240px;
            height: 100vh;
            position: fixed;
            background: #17365d;
            color: white;
            padding-top: 25px;
        }

        .sidebar h4 {
            text-align: center;
            margin-bottom: 35px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 13px 22px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #294f7d;
        }

        .main-content {
            margin-left: 240px;
            padding: 30px;
        }

        .topbar {
            background: white;
            padding: 18px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 22px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .stat-card h6 {
            color: #777;
        }

        .stat-card h3 {
            color: #17365d;
            font-weight: bold;
        }

        .table-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .table th {
            background: #17365d;
            color: white;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .search-box {
            max-width: 300px;
        }

        @media(max-width: 768px) {

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                padding: 15px;
            }

        }

    </style>

</head>

<body>


<!-- Sidebar -->

<div class="sidebar">

    <h4>Hostel Admin</h4>

    <a href="../index.php">
        <i class="fa-solid fa-gauge"></i> Dashboard
    </a>

    <a href="../students/view.php">
        <i class="fa-solid fa-users"></i> Students
    </a>

    <a href="../hostels/view.php">
        <i class="fa-solid fa-building"></i> Hostels
    </a>

    <a href="../rooms/view.php">
        <i class="fa-solid fa-door-open"></i> Rooms
    </a>

    <a href="view.php" class="active">
        <i class="fa-solid fa-bed"></i> Allocations
    </a>

    <a href="../payments/view.php">
        <i class="fa-solid fa-money-bill"></i> Payments
    </a>

</div>


<!-- Main Content -->

<div class="main-content">


    <!-- Topbar -->

    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">

        <h4 class="mb-0">Room Allocation Management</h4>

        <a href="add.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Allocate Room
        </a>

    </div>


    <!-- Alerts -->

    <?php if (isset($_GET["added"]) && $_GET["added"] == "1"): ?>

        <div class="alert alert-success">
            Room allocated successfully!
        </div>

    <?php endif; ?>


    <?php if (isset($_GET["checkout"]) && $_GET["checkout"] == "1"): ?>

        <div class="alert alert-success">
            Student checked out successfully!
        </div>

    <?php elseif (isset($_GET["checkout"]) && $_GET["checkout"] == "0"): ?>

        <div class="alert alert-warning">
            Checkout could not be completed. The allocation may already be completed.
        </div>

    <?php endif; ?>


    <!-- Statistics -->

    <div class="row">

        <div class="col-md-4">

            <div class="stat-card">

                <h6>Total Allocations</h6>

                <h3>
                    <?php echo $totalAllocations; ?>
                </h3>

            </div>

        </div>


        <div class="col-md-4">

            <div class="stat-card">

                <h6>Active Allocations</h6>

                <h3>
                    <?php echo $activeAllocations; ?>
                </h3>

            </div>

        </div>


        <div class="col-md-4">

            <div class="stat-card">

                <h6>Completed Allocations</h6>

                <h3>
                    <?php echo $completedAllocations; ?>
                </h3>

            </div>

        </div>

    </div>


    <!-- Allocation Table -->

    <div class="table-container">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">

            <h5 class="mb-0">Allocation Records</h5>

            <input
                type="text"
                id="searchInput"
                class="form-control search-box"
                placeholder="Search allocations..."
                onkeyup="searchAllocations()"
            >

        </div>


        <div class="table-responsive">

            <table class="table table-bordered table-hover" id="allocationTable">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Student</th>
                        <th>Student ID</th>
                        <th>Course</th>
                        <th>Hostel</th>
                        <th>Room</th>
                        <th>Phone</th>
                        <th>Allocation Date</th>
                        <th>Checkout Date</th>
                        <th>Status</th>
                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (mysqli_num_rows($result) > 0): ?>

                        <?php while ($row = mysqli_fetch_assoc($result)): ?>

                            <tr>

                                <td>
                                    <?php echo $row["Allocation_ID"]; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["Student_Name"]); ?>
                                </td>

                                <td>
                                    <?php echo $row["Student_ID"]; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["Course"] ?? ""); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["Hostel_Name"]); ?>
                                </td>

                                <td>
                                    <?php echo $row["Room_No"]; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["Phone"] ?? ""); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["Allocation_Date"]); ?>
                                </td>

                                <td>
                                    <?php
                                    echo $row["Checkout_Date"]
                                        ? htmlspecialchars($row["Checkout_Date"])
                                        : "-";
                                    ?>
                                </td>

                                <td>

                                    <?php if ($row["Status"] == "Active"): ?>

                                        <span class="badge bg-success">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Completed
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?php if ($row["Status"] == "Active"): ?>

                                        <form
                                            method="POST"
                                            action=""
                                            onsubmit="return confirm('Are you sure you want to check out this student?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="allocation_id"
                                                value="<?php echo $row["Allocation_ID"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="checkout"
                                                class="btn btn-sm btn-warning"
                                            >

                                                <i class="fa-solid fa-right-from-bracket"></i>
                                                Checkout

                                            </button>

                                        </form>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No Action
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="11" class="text-center text-muted">

                                No allocation records found.

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<script>

    function searchAllocations() {

        let input = document.getElementById("searchInput");
        let filter = input.value.toLowerCase();

        let table = document.getElementById("allocationTable");
        let rows = table.getElementsByTagName("tr");

        for (let i = 1; i < rows.length; i++) {

            let text = rows[i].textContent.toLowerCase();

            rows[i].style.display =
                text.includes(filter) ? "" : "none";

        }

    }

</script>


</body>
</html>
