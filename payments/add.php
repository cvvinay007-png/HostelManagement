
<?php
include "../config/db.php";

$error = "";

// Fetch students
$studentQuery = mysqli_query(
    $conn,
    "SELECT Student_ID, Name
     FROM student
     ORDER BY Name"
);

if (!$studentQuery) {
    die("Student query error: " . mysqli_error($conn));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = filter_input(INPUT_POST, "student_id", FILTER_VALIDATE_INT);
    $amount = trim($_POST["amount"] ?? "");
    $payment_date = trim($_POST["payment_date"] ?? "");
    $payment_type = trim($_POST["payment_type"] ?? "");
    $payment_status = trim($_POST["payment_status"] ?? "");

    $allowedTypes = ["Cash", "Online UPI", "Bank Transfer", "DD"];
    $allowedStatuses = ["Paid", "Pending", "Failed"];

    if (!$student_id) {
        $error = "Please select a student.";
    }
    elseif (
        !is_numeric($amount) ||
        (float)$amount <= 0 ||
        (float)$amount > 99999999.99
    ) {
        $error = "Please enter a valid payment amount.";
    }
    elseif (
        !DateTime::createFromFormat("!Y-m-d", $payment_date) ||
        DateTime::createFromFormat("!Y-m-d", $payment_date)->format("Y-m-d") !== $payment_date
    ) {
        $error = "Please select a valid payment date.";
    }
    elseif (!in_array($payment_type, $allowedTypes, true)) {
        $error = "Please select a valid payment type.";
    }
    elseif (!in_array($payment_status, $allowedStatuses, true)) {
        $error = "Please select a valid payment status.";
    }
    else {

        // Verify student
        $checkStmt = mysqli_prepare(
            $conn,
            "SELECT Student_ID FROM student WHERE Student_ID = ?"
        );

        if (!$checkStmt) {
            die("SQL error: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($checkStmt, "i", $student_id);
        mysqli_stmt_execute($checkStmt);

        $checkResult = mysqli_stmt_get_result($checkStmt);
        $student = mysqli_fetch_assoc($checkResult);

        mysqli_stmt_close($checkStmt);

        if (!$student) {
            $error = "Selected student does not exist.";
        } else {

            // Insert payment using the actual table structure
            $insertStmt = mysqli_prepare(
                $conn,
                "INSERT INTO payment
                (Student_ID, Amount, Payment_Date, Payment_Type, Payment_Status)
                VALUES (?, ?, ?, ?, ?)"
            );

            if (!$insertStmt) {
                die("Payment SQL error: " . mysqli_error($conn));
            }

            $amount = (float)$amount;

            mysqli_stmt_bind_param(
                $insertStmt,
                "idsss",
                $student_id,
                $amount,
                $payment_date,
                $payment_type,
                $payment_status
            );

            if (mysqli_stmt_execute($insertStmt)) {

                mysqli_stmt_close($insertStmt);

                header("Location: view.php?added=1");
                exit();

            } else {

                $error = "Payment insertion failed: " .
                    mysqli_stmt_error($insertStmt);

                mysqli_stmt_close($insertStmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Payment | Hostel Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .form-container {
            max-width: 700px;
            margin: 50px auto;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .page-title {
            color: #17365d;
            font-weight: bold;
        }

        .form-label {
            font-weight: 600;
        }

        .btn-primary {
            background: #17365d;
            border: none;
        }

        .btn-primary:hover {
            background: #0d2745;
        }
    </style>
</head>

<body>

<div class="container">
    <div class="form-container">

        <h3 class="page-title">Record New Payment</h3>

        <p class="text-muted mb-4">
            Enter the payment details for a student.
        </p>

        <?php if ($error !== ""): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="mb-3">
                <label class="form-label">Select Student</label>

                <select name="student_id" class="form-select" required>
                    <option value="">-- Select Student --</option>

                    <?php while ($row = mysqli_fetch_assoc($studentQuery)): ?>
                        <option
                            value="<?php echo $row['Student_ID']; ?>"
                            <?php
                            echo (
                                isset($_POST['student_id']) &&
                                $_POST['student_id'] == $row['Student_ID']
                            ) ? "selected" : "";
                            ?>
                        >
                            <?php
                            echo htmlspecialchars(
                                $row['Name'] . " (ID: " . $row['Student_ID'] . ")"
                            );
                            ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Payment Amount (₹)</label>

                <input
                    type="number"
                    name="amount"
                    class="form-control"
                    min="0.01"
                    max="99999999.99"
                    step="0.01"
                    required
                    value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>"
                    placeholder="Enter amount"
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Payment Date</label>

                <input
                    type="date"
                    name="payment_date"
                    class="form-control"
                    required
                    value="<?php echo htmlspecialchars($_POST['payment_date'] ?? date('Y-m-d')); ?>"
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Payment Type</label>

                <select name="payment_type" class="form-select" required>
                    <option value="">-- Select Payment Type --</option>

                    <?php foreach (["Cash", "Online UPI", "Bank Transfer", "DD"] as $type): ?>
                        <option
                            value="<?php echo $type; ?>"
                            <?php echo (($_POST['payment_type'] ?? '') === $type) ? "selected" : ""; ?>
                        >
                            <?php echo $type; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Payment Status</label>

                <select name="payment_status" class="form-select" required>
                    <option value="">-- Select Payment Status --</option>

                    <?php foreach (["Paid", "Pending", "Failed"] as $status): ?>
                        <option
                            value="<?php echo $status; ?>"
                            <?php echo (($_POST['payment_status'] ?? '') === $status) ? "selected" : ""; ?>
                        >
                            <?php echo $status; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary px-4">
                Save Payment
            </button>

            <a href="view.php" class="btn btn-secondary px-4">
                Cancel
            </a>

        </form>
    </div>
</div>

</body>
</html>
