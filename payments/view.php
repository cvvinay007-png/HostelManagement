
<?php
include "../config/db.php";

// Fetch payment records
$query = "
    SELECT
        p.Payment_ID,
        p.Student_ID,
        s.Name AS Student_Name,
        s.Course,
        s.Phone,
        p.Amount,
        p.Payment_Date,
        p.Payment_Type,
        p.Payment_Status
    FROM payment p
    INNER JOIN student s ON p.Student_ID = s.Student_ID
    ORDER BY p.Payment_ID DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Payment query error: " . mysqli_error($conn));
}

// Calculate payment statistics
$statsQuery = mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS total_payments,
        COALESCE(SUM(CASE WHEN Payment_Status = 'Paid' THEN Amount ELSE 0 END), 0) AS collected,
        COALESCE(SUM(CASE WHEN Payment_Status = 'Pending' THEN Amount ELSE 0 END), 0) AS pending,
        COALESCE(SUM(CASE WHEN Payment_Status = 'Failed' THEN 1 ELSE 0 END), 0) AS failed
     FROM payment"
);

if (!$statsQuery) {
    die("Statistics query error: " . mysqli_error($conn));
}

$stats = mysqli_fetch_assoc($statsQuery);

$payments = [];

while ($row = mysqli_fetch_assoc($result)) {
    $payments[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Payment Management | Hostel Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          rel="stylesheet">

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

    <a href="../allocations/view.php">
        <i class="fa-solid fa-bed"></i> Allocations
    </a>

    <a href="view.php" class="active">
        <i class="fa-solid fa-money-bill"></i> Payments
    </a>

</div>


<!-- Main Content -->

<div class="main-content">

    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-3">

        <h4 class="mb-0">Payment Management</h4>

        <a href="add.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add Payment
        </a>

    </div>


    <?php if (isset($_GET["added"]) && $_GET["added"] == "1"): ?>

        <div class="alert alert-success">
            Payment recorded successfully!
        </div>

    <?php endif; ?>


    <!-- Statistics -->

    <div class="row">

        <div class="col-md-3">
            <div class="stat-card">
                <h6>Total Payments</h6>
                <h3><?php echo $stats["total_payments"]; ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <h6>Total Collected</h6>
                <h3 class="text-success">
                    ₹<?php echo number_format((float)$stats["collected"], 2); ?>
                </h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <h6>Pending Amount</h6>
                <h3 class="text-warning">
                    ₹<?php echo number_format((float)$stats["pending"], 2); ?>
                </h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <h6>Failed Payments</h6>
                <h3 class="text-danger">
                    <?php echo $stats["failed"]; ?>
                </h3>
            </div>
        </div>

    </div>


    <!-- Payment History -->

    <div class="table-container">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">

            <h5 class="mb-0">Payment History</h5>

            <input
                type="text"
                id="searchInput"
                class="form-control search-box"
                placeholder="Search payments..."
                onkeyup="searchPayments()"
            >

        </div>


        <div class="table-responsive">

            <table class="table table-bordered table-hover" id="paymentTable">

                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Student</th>
                        <th>Student ID</th>
                        <th>Course</th>
                        <th>Phone</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Payment Type</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($payments) > 0): ?>

                        <?php foreach ($payments as $row): ?>

                            <tr>

                                <td><?php echo $row["Payment_ID"]; ?></td>

                                <td>
                                    <?php echo htmlspecialchars($row["Student_Name"]); ?>
                                </td>

                                <td><?php echo $row["Student_ID"]; ?></td>

                                <td>
                                    <?php echo htmlspecialchars($row["Course"] ?? ""); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["Phone"] ?? ""); ?>
                                </td>

                                <td>
                                    ₹<?php echo number_format((float)$row["Amount"], 2); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["Payment_Date"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["Payment_Type"] ?? ""); ?>
                                </td>

                                <td>

                                    <?php if ($row["Payment_Status"] == "Paid"): ?>

                                        <span class="badge bg-success">Paid</span>

                                    <?php elseif ($row["Payment_Status"] == "Pending"): ?>

                                        <span class="badge bg-warning text-dark">Pending</span>

                                    <?php else: ?>

                                        <span class="badge bg-danger">Failed</span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                No payment records found.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<script>

function searchPayments() {

    let input = document.getElementById("searchInput");
    let filter = input.value.toLowerCase();

    let table = document.getElementById("paymentTable");
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
