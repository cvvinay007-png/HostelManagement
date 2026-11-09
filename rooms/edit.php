
<?php
include '../config/db.php';

if (
    !isset($_GET['hostel_id'], $_GET['room_no']) ||
    !ctype_digit($_GET['hostel_id']) ||
    !ctype_digit($_GET['room_no'])
) {
    die("Invalid room details.");
}

$hostelId = (int)$_GET['hostel_id'];
$roomNo = (int)$_GET['room_no'];
$error = "";

// Fetch room and hostel information
$stmt = mysqli_prepare(
    $conn,
    "SELECT r.*, h.Hostel_Name
     FROM room r
     JOIN hostel h ON r.Hostel_ID = h.Hostel_ID
     WHERE r.Hostel_ID = ? AND r.Room_No = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $hostelId, $roomNo);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$room = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$room) {
    die("Room not found.");
}

// Update room
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $floor = filter_var($_POST['floor'], FILTER_VALIDATE_INT);
    $capacity = filter_var($_POST['capacity'], FILTER_VALIDATE_INT);

    if ($floor === false || $floor < 0 ||
        $capacity === false || $capacity < 1) {

        $error = "Please enter valid floor and capacity values.";

    } else {

        // Check active occupants
        $stmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS total
             FROM allocation
             WHERE Hostel_ID = ?
               AND Room_No = ?
               AND Status = 'Active'"
        );

        mysqli_stmt_bind_param($stmt, "ii", $hostelId, $roomNo);
        mysqli_stmt_execute($stmt);

        $occupancyResult = mysqli_stmt_get_result($stmt);
        $occupancy = mysqli_fetch_assoc($occupancyResult);
        mysqli_stmt_close($stmt);

        if ($capacity < (int)$occupancy['total']) {

            $error = "Capacity cannot be less than the current active occupants (" .
                     (int)$occupancy['total'] . ").";

        } else {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE room
                 SET Floor = ?, Capacity = ?
                 WHERE Hostel_ID = ? AND Room_No = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iiii",
                $floor,
                $capacity,
                $hostelId,
                $roomNo
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: view.php?updated=1");
                exit();
            } else {
                $error = "Update failed: " . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);
        }

        $room['Floor'] = $floor;
        $room['Capacity'] = $capacity;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Room | Hostel Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

<style>
body {
    background: #f4f6fb;
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #1e293b;
}

.container {
    max-width: 800px;
    margin-top: 45px;
}

.form-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,.05);
}

.form-label {
    font-weight: 600;
}

.form-control {
    padding: 11px;
    border-radius: 8px;
}

.btn-primary {
    background: #2563eb;
    border: none;
}
</style>

</head>

<body>

<div class="container">

    <h2 class="fw-bold mb-2">Edit Room</h2>

    <p class="text-muted mb-4">
        Update room information.
    </p>

    <div class="form-card">

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Hostel</label>

                <input type="text" class="form-control"
                       value="<?php echo htmlspecialchars($room['Hostel_Name']); ?>"
                       readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Room Number</label>

                <input type="text" class="form-control"
                       value="<?php echo (int)$room['Room_No']; ?>"
                       readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Floor Number</label>

                <input type="number" name="floor"
                       class="form-control"
                       min="0" required
                       value="<?php echo (int)$room['Floor']; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Room Capacity</label>

                <input type="number" name="capacity"
                       class="form-control"
                       min="1" required
                       value="<?php echo (int)$room['Capacity']; ?>">
            </div>

            <hr>

            <button type="submit" class="btn btn-primary">
                Save Changes
            </button>

            <a href="view.php" class="btn btn-secondary">
                Cancel
            </a>

        </form>

    </div>

</div>

</body>
</html>
