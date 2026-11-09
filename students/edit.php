
<?php
include '../config/db.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("Invalid student ID");
}

$id = (int) $_GET['id'];
$error = "";

// Fetch existing student
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM student WHERE Student_ID = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$student) {
    die("Student not found");
}

// Update student
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name']);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $year = (int) $_POST['year'];
    $address = trim($_POST['address']);

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE student SET
        Name = ?, Gender = ?, DOB = ?, Phone = ?,
        Email = ?, Course = ?, Year = ?, Address = ?
        WHERE Student_ID = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssisi",
        $name, $gender, $dob, $phone,
        $email, $course, $year, $address, $id
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f4f6fb;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .container {
            max-width: 850px;
            margin-top: 45px;
        }

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
        }

        .form-label {
            font-weight: 600;
        }

        .form-control, .form-select {
            padding: 11px;
            border-radius: 8px;
        }
    </style>
</head>

<body>

<div class="container">

    <h2 class="fw-bold mb-2">Edit Student</h2>
    <p class="text-muted mb-4">Update student information.</p>

    <div class="form-card">

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Student Name</label>
                    <input type="text" name="name" class="form-control"
                        maxlength="100" required
                        value="<?php echo htmlspecialchars($student['Name']); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <?php foreach (['Male','Female','Other'] as $g): ?>
                            <option value="<?php echo $g; ?>"
                                <?php echo $student['Gender'] === $g ? 'selected' : ''; ?>>
                                <?php echo $g; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" class="form-control"
                        required value="<?php echo htmlspecialchars($student['DOB']); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control"
                        maxlength="15" required
                        value="<?php echo htmlspecialchars($student['Phone']); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                        maxlength="100" required
                        value="<?php echo htmlspecialchars($student['Email']); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Course</label>
                    <input type="text" name="course" class="form-control"
                        maxlength="100" required
                        value="<?php echo htmlspecialchars($student['Course']); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Academic Year</label>
                    <select name="year" class="form-select" required>
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <option value="<?php echo $i; ?>"
                                <?php echo (int)$student['Year'] === $i ? 'selected' : ''; ?>>
                                Year <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control"
                        rows="3" required><?php echo htmlspecialchars($student['Address']); ?></textarea>
                </div>

            </div>

            <hr class="my-4">

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
