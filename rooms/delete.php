
<?php
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: view.php");
    exit();
}

if (
    !isset($_POST['hostel_id'], $_POST['room_no']) ||
    !ctype_digit($_POST['hostel_id']) ||
    !ctype_digit($_POST['room_no'])
) {
    die("Invalid room details.");
}

$hostelId = (int)$_POST['hostel_id'];
$roomNo = (int)$_POST['room_no'];

// Check whether room exists
$stmt = mysqli_prepare(
    $conn,
    "SELECT Room_No FROM room
     WHERE Hostel_ID = ? AND Room_No = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $hostelId, $roomNo);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$room = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$room) {
    die("Room not found.");
}

// Check whether any allocation references this room
$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM allocation
     WHERE Hostel_ID = ? AND Room_No = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $hostelId, $roomNo);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ((int)$data['total'] > 0) {
    die(
        "Cannot delete this room because allocation records exist. " .
        "Preserve the allocation history and deactivate or manage the room instead."
    );
}

// Delete room
$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM room
     WHERE Hostel_ID = ? AND Room_No = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $hostelId, $roomNo);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: view.php?deleted=1");
    exit();
}

$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

die("Unable to delete room: " . htmlspecialchars($error));
?>
