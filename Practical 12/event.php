<?php
session_start();

require_once "db.php";

/* Check login */
if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

/* Get role from database using the logged-in student's ID */
$student_id = $_SESSION["student_id"];

$sql = "SELECT role FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Student account not found.");
}

$user = $result->fetch_assoc();

$stmt->close();

/* Check admin */
if ($user["role"] !== "admin") {
    die("
        <h2>Admin Access Required</h2>
        <p>Your current role is: " . htmlspecialchars($user["role"]) . "</p>
        <p>Please login using an admin account.</p>
        <a href='dashboard.php'>Back to Dashboard</a>
    ");
}

/* ---------------- ADD EVENT ---------------- */

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_event"])) {

    $event_name = trim($_POST["event_name"]);
    $event_date = $_POST["event_date"];
    $venue = trim($_POST["venue"]);

    if ($event_name == "" || $event_date == "" || $venue == "") {

        $message = "Please fill all fields.";

    } else {

        $poster = "";

        /* Poster upload */
        if (isset($_FILES["poster"]) && $_FILES["poster"]["error"] == 0) {

            $allowed_types = ["image/jpeg", "image/png", "image/jpg"];
            $max_size = 2 * 1024 * 1024; // 2 MB

            if (!in_array($_FILES["poster"]["type"], $allowed_types)) {

                $message = "Only JPG, JPEG and PNG files are allowed.";

            } elseif ($_FILES["poster"]["size"] > $max_size) {

                $message = "Poster size must be less than 2 MB.";

            } else {

                $upload_folder = "uploads/";

                if (!is_dir($upload_folder)) {
                    mkdir($upload_folder, 0777, true);
                }

                $file_name = time() . "_" . basename($_FILES["poster"]["name"]);

                $poster = $upload_folder . $file_name;

                move_uploaded_file(
                    $_FILES["poster"]["tmp_name"],
                    $poster
                );
            }
        }

        if ($message == "") {

            $sql = "INSERT INTO events
                    (event_name, event_date, venue, poster)
                    VALUES (?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {

                $message = "Database error: " . $conn->error;

            } else {

                $stmt->bind_param(
                    "ssss",
                    $event_name,
                    $event_date,
                    $venue,
                    $poster
                );

                if ($stmt->execute()) {
                    $message = "Event added successfully.";
                } else {
                    $message = "Failed to add event: " . $stmt->error;
                }

                $stmt->close();
            }
        }
    }
}

/* ---------------- DELETE EVENT ---------------- */

if (isset($_GET["delete"])) {

    $event_id = intval($_GET["delete"]);

    $sql = "DELETE FROM events WHERE event_id = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt) {

        $stmt->bind_param("i", $event_id);

        if ($stmt->execute()) {
            $message = "Event deleted successfully.";
        } else {
            $message = "Failed to delete event.";
        }

        $stmt->close();
    }
}

/* ---------------- VIEW EVENTS ---------------- */

$result = $conn->query(
    "SELECT event_id, event_name, event_date, venue, poster
     FROM events
     ORDER BY event_date ASC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>Event Management - StudentHub</title>

<style>

body {
    font-family: Arial, sans-serif;
    background: #eef6ff;
    margin: 0;
}

header {
    background: #2c6e9e;
    color: white;
    text-align: center;
    padding: 25px;
}

.container {
    width: 90%;
    max-width: 1000px;
    margin: 30px auto;
}

.box {
    background: white;
    padding: 25px;
    margin-bottom: 25px;
    border-radius: 8px;
}

input {
    width: 100%;
    padding: 10px;
    margin: 8px 0 15px;
    box-sizing: border-box;
}

button {
    background: #2c6e9e;
    color: white;
    border: none;
    padding: 11px 20px;
    cursor: pointer;
}

button:hover {
    background: #174f76;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: left;
}

th {
    background: #2c6e9e;
    color: white;
}

img {
    width: 100px;
    height: 70px;
    object-fit: cover;
}

.message {
    background: #dff0d8;
    color: #256029;
    padding: 12px;
    margin-bottom: 20px;
}

.delete {
    color: red;
    text-decoration: none;
}

</style>

</head>

<body>

<header>

<h1>StudentHub</h1>
<p>Admin Event Management</p>

</header>

<div class="container">

<div class="box">

<h2>Add Event</h2>

<?php if ($message != ""): ?>

<div class="message">
<?php echo htmlspecialchars($message); ?>
</div>

<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<label>Event Name:</label>

<input
    type="text"
    name="event_name"
    required
>

<label>Event Date:</label>

<input
    type="date"
    name="event_date"
    required
>

<label>Venue:</label>

<input
    type="text"
    name="venue"
    required
>

<label>Event Poster:</label>

<input
    type="file"
    name="poster"
    accept=".jpg,.jpeg,.png"
>

<p>Allowed: JPG, JPEG, PNG | Maximum size: 2 MB</p>

<button type="submit" name="add_event">
Add Event
</button>

</form>

</div>


<div class="box">

<h2>All Events</h2>

<table>

<tr>

<th>ID</th>
<th>Event Name</th>
<th>Date</th>
<th>Venue</th>
<th>Poster</th>
<th>Action</th>

</tr>

<?php if ($result && $result->num_rows > 0): ?>

<?php while ($event = $result->fetch_assoc()): ?>

<tr>

<td>
<?php echo $event["event_id"]; ?>
</td>

<td>
<?php echo htmlspecialchars($event["event_name"]); ?>
</td>

<td>
<?php echo htmlspecialchars($event["event_date"]); ?>
</td>

<td>
<?php echo htmlspecialchars($event["venue"]); ?>
</td>

<td>

<?php if (!empty($event["poster"])): ?>

<img src="<?php echo htmlspecialchars($event["poster"]); ?>">

<?php else: ?>

No poster

<?php endif; ?>

</td>

<td>

<a
    class="delete"
    href="event.php?delete=<?php echo $event["event_id"]; ?>"
    onclick="return confirm('Delete this event?');"
>
Delete
</a>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="6">
No events found.
</td>

</tr>

<?php endif; ?>

</table>

</div>

</div>

</body>

</html>