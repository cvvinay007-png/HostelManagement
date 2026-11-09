
<?php
include "../config/db.php";

$error = "";

// Fetch students who do not have an active allocation
$studentQuery = "
    SELECT s.Student_ID, s.Name, s.Gender
    FROM student s
    WHERE NOT EXISTS (
        SELECT 1
        FROM allocation a
        WHERE a.Student_ID = s.Student_ID
        AND a.Status = 'Active'
    )
    ORDER BY s.Name
";

$students = mysqli_query($conn, $studentQuery);

// Fetch rooms with their hostel details and current occupancy
$roomQuery = "
    SELECT
        r.Hostel_ID,
        r.Room_No,
        r.Floor,
        r.Capacity,
        h.Hostel_Name,
        h.Gender AS Hostel_Gender,
        (
            SELECT COUNT(*)
            FROM allocation a
            WHERE a.Hostel_ID = r.Hostel_ID
            AND a.Room_No = r.Room_No
            AND a.Status = 'Active'
        ) AS Occupied
    FROM room r
    JOIN hostel h ON r.Hostel_ID = h.Hostel_ID
    ORDER BY h.Hostel_Name, r.Room_No
";

$rooms = mysqli_query($conn, $roomQuery);


// Process allocation form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = filter_input(INPUT_POST, "student_id", FILTER_VALIDATE_INT);
    $hostel_id = filter_input(INPUT_POST, "hostel_id", FILTER_VALIDATE_INT);
    $room_no = filter_input(INPUT_POST, "room_no", FILTER_VALIDATE_INT);

    if (!$student_id || !$hostel_id || !$room_no) {

        $error = "Please select a valid student and room.";

    } else {

        try {

            mysqli_begin_transaction($conn);

            // 1. Lock and verify the student
            $studentStmt = mysqli_prepare(
                $conn,
                "SELECT Student_ID, Gender
                 FROM student
                 WHERE Student_ID = ?
                 FOR UPDATE"
            );

            mysqli_stmt_bind_param($studentStmt, "i", $student_id);
            mysqli_stmt_execute($studentStmt);

            $studentResult = mysqli_stmt_get_result($studentStmt);
            $student = mysqli_fetch_assoc($studentResult);

            mysqli_stmt_close($studentStmt);

            if (!$student) {
                throw new Exception("Selected student does not exist.");
            }

            // 2. Lock and verify the selected room and hostel
            $roomStmt = mysqli_prepare(
                $conn,
                "SELECT
                    r.Hostel_ID,
                    r.Room_No,
                    r.Capacity,
                    h.Gender AS Hostel_Gender
                 FROM room r
                 JOIN hostel h ON r.Hostel_ID = h.Hostel_ID
                 WHERE r.Hostel_ID = ?
                 AND r.Room_No = ?
                 FOR UPDATE"
            );

            mysqli_stmt_bind_param(
                $roomStmt,
                "ii",
                $hostel_id,
                $room_no
            );

            mysqli_stmt_execute($roomStmt);

            $roomResult = mysqli_stmt_get_result($roomStmt);
            $room = mysqli_fetch_assoc($roomResult);

            mysqli_stmt_close($roomStmt);

            if (!$room) {
                throw new Exception("Selected room does not exist.");
            }

            // 3. Check student and hostel gender
            if (
                strtolower(trim($student["Gender"] ?? "")) !==
                strtolower(trim($room["Hostel_Gender"] ?? ""))
            ) {
                throw new Exception(
                    "Student gender does not match the selected hostel."
                );
            }

            // 4. Check whether student already has an active allocation
            $activeStudentStmt = mysqli_prepare(
                $conn,
                "SELECT Allocation_ID
                 FROM allocation
                 WHERE Student_ID = ?
                 AND Status = 'Active'
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $activeStudentStmt,
                "i",
                $student_id
            );

            mysqli_stmt_execute($activeStudentStmt);

            $activeStudentResult = mysqli_stmt_get_result($activeStudentStmt);
            $activeStudent = mysqli_fetch_assoc($activeStudentResult);

            mysqli_stmt_close($activeStudentStmt);

            if ($activeStudent) {
                throw new Exception(
                    "This student already has an active room allocation."
                );
            }

            // 5. Count current occupants in the selected room
            $occupancyStmt = mysqli_prepare(
                $conn,
                "SELECT COUNT(*) AS Occupied
                 FROM allocation
                 WHERE Hostel_ID = ?
                 AND Room_No = ?
                 AND Status = 'Active'"
            );

            mysqli_stmt_bind_param(
                $occupancyStmt,
                "ii",
                $hostel_id,
                $room_no
            );

            mysqli_stmt_execute($occupancyStmt);

            $occupancyResult = mysqli_stmt_get_result($occupancyStmt);
            $occupancy = mysqli_fetch_assoc($occupancyResult);

            mysqli_stmt_close($occupancyStmt);

            // 6. Check room capacity
            if ($occupancy["Occupied"] >= $room["Capacity"]) {
                throw new Exception(
                    "This room is already full. Please select another room."
                );
            }

            // 7. Insert allocation
            $insertStmt = mysqli_prepare(
                $conn,
                "INSERT INTO allocation
                 (Student_ID, Hostel_ID, Room_No, Allocation_Date, Status)
                 VALUES (?, ?, ?, CURDATE(), 'Active')"
            );

            mysqli_stmt_bind_param(
                $insertStmt,
                "iii",
                $student_id,
                $hostel_id,
                $room_no
            );

            if (!mysqli_stmt_execute($insertStmt)) {
                throw new Exception(
                    "Could not create allocation: " .
                    mysqli_stmt_error($insertStmt)
                );
            }

            mysqli_stmt_close($insertStmt);

            // 8. Commit transaction
            mysqli_commit($conn);

            header("Location: view.php?added=1");
            exit();

        } catch (Throwable $e) {

            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Allocate Room | Hostel Management System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

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
            margin-bottom: 25px;
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

        <h3 class="page-title">Allocate Room to Student</h3>

        <p class="text-muted">
            Select a student and assign an available hostel room.
        </p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>


        <form method="POST" action="">

            <!-- Student Selection -->
            <div class="mb-4">

                <label class="form-label">Select Student</label>

                <select
                    name="student_id"
                    class="form-select"
                    required
                >

                    <option value="">-- Select Student --</option>

                    <?php while ($studentRow = mysqli_fetch_assoc($students)): ?>

                        <option
                            value="<?php echo $studentRow['Student_ID']; ?>"
                            <?php
                            if (
                                isset($_POST['student_id']) &&
                                $_POST['student_id'] == $studentRow['Student_ID']
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            <?php
                            echo htmlspecialchars(
                                $studentRow['Name'] .
                                " (ID: " . $studentRow['Student_ID'] . ")"
                            );
                            ?>
                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- Room Selection -->
            <div class="mb-4">

                <label class="form-label">Select Room</label>

                <select
                    name="hostel_id"
                    id="hostel_id"
                    class="form-select mb-3"
                    required
                >

                    <option value="">-- Select Hostel --</option>

                    <?php
                    // Build hostel options from the room list
                    $hostelOptions = mysqli_query(
                        $conn,
                        "SELECT Hostel_ID, Hostel_Name
                         FROM hostel
                         ORDER BY Hostel_Name"
                    );

                    while ($hostelRow = mysqli_fetch_assoc($hostelOptions)):
                    ?>

                        <option
                            value="<?php echo $hostelRow['Hostel_ID']; ?>"
                            <?php
                            if (
                                isset($_POST['hostel_id']) &&
                                $_POST['hostel_id'] == $hostelRow['Hostel_ID']
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            <?php echo htmlspecialchars($hostelRow['Hostel_Name']); ?>
                        </option>

                    <?php endwhile; ?>

                </select>


                <select
                    name="room_no"
                    id="room_no"
                    class="form-select"
                    required
                >

                    <option value="">-- Select Room Number --</option>

                    <?php while ($roomRow = mysqli_fetch_assoc($rooms)): ?>

                        <?php
                        $available =
                            $roomRow['Capacity'] - $roomRow['Occupied'];
                        ?>

                        <option
                            value="<?php echo $roomRow['Room_No']; ?>"
                            data-hostel="<?php echo $roomRow['Hostel_ID']; ?>"
                            <?php
                            if (
                                isset($_POST['hostel_id'], $_POST['room_no']) &&
                                $_POST['hostel_id'] == $roomRow['Hostel_ID'] &&
                                $_POST['room_no'] == $roomRow['Room_No']
                            ) {
                                echo "selected";
                            }
                            ?>
                        >
                            <?php
                            echo "Room " . $roomRow['Room_No'] .
                                " | Floor " . $roomRow['Floor'] .
                                " | Available: " . $available .
                                "/" . $roomRow['Capacity'];
                            ?>
                        </option>

                    <?php endwhile; ?>

                </select>

                <small class="text-muted">
                    Rooms are grouped by the selected hostel.
                    Full rooms cannot be allocated.
                </small>

            </div>


            <!-- Allocation Date -->
            <div class="mb-4">

                <label class="form-label">Allocation Date</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?php echo date('d-m-Y'); ?>"
                    readonly
                >

                <small class="text-muted">
                    The allocation date is saved automatically.
                </small>

            </div>


            <!-- Status -->
            <div class="mb-4">

                <label class="form-label">Allocation Status</label>

                <input
                    type="text"
                    class="form-control"
                    value="Active"
                    readonly
                >

            </div>


            <button type="submit" class="btn btn-primary px-4">
                Allocate Room
            </button>

            <a href="view.php" class="btn btn-secondary px-4">
                Cancel
            </a>

        </form>

    </div>

</div>


<script>

    const hostelSelect = document.getElementById("hostel_id");
    const roomSelect = document.getElementById("room_no");

    function filterRooms() {

        const selectedHostel = hostelSelect.value;

        const options = roomSelect.querySelectorAll("option");

        options.forEach(function(option) {

            if (option.value === "") {
                option.hidden = false;
                return;
            }

            const roomHostel = option.dataset.hostel;

            option.hidden = (
                selectedHostel !== "" &&
                roomHostel !== selectedHostel
            );

        });

        if (
            roomSelect.selectedOptions.length > 0 &&
            roomSelect.selectedOptions[0].hidden
        ) {
            roomSelect.value = "";
        }
    }

    hostelSelect.addEventListener("change", filterRooms);

    // Filter the room list on initial page load
    filterRooms();

</script>

</body>
</html>
