<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION["student_id"];

$stmt = $conn->prepare(
    "SELECT student_id, name, email, role
     FROM students
     WHERE student_id = ?"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$userResult = $stmt->get_result();

if ($userResult->num_rows == 0) {
    die("User not found.");
}

$user = $userResult->fetch_assoc();

$stmt->close();

$role = strtolower(trim($user["role"]));

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST"
    && isset($_POST["register_event"])) {

    $event_id = intval($_POST["event_id"]);

    if ($event_id <= 0) {

        $message = "Invalid event.";

    } else {


        $checkEvent = $conn->prepare(
            "SELECT event_id FROM events WHERE event_id = ?"
        );

        $checkEvent->bind_param("i", $event_id);
        $checkEvent->execute();

        $eventResult = $checkEvent->get_result();

        if ($eventResult->num_rows == 0) {

            $message = "Event not found.";

        } else {

            $checkEvent->close();

            $checkRegistration = $conn->prepare(
                "SELECT registration_id
                 FROM registrations
                 WHERE student_id = ?
                 AND event_id = ?"
            );

            $checkRegistration->bind_param(
                "ii",
                $student_id,
                $event_id
            );

            $checkRegistration->execute();

            $registrationResult =
                $checkRegistration->get_result();

            if ($registrationResult->num_rows > 0) {

                $message =
                    "You are already registered for this event.";

            } else {

                $checkRegistration->close();

                /* INSERT REGISTRATION */

                $insert = $conn->prepare(
                    "INSERT INTO registrations
                    (student_id, event_id, registration_date)
                    VALUES (?, ?, NOW())"
                );

                if (!$insert) {

                    $message =
                        "Database error: " . $conn->error;

                } else {

                    $insert->bind_param(
                        "ii",
                        $student_id,
                        $event_id
                    );

                    if ($insert->execute()) {

                        $message =
                            "Successfully registered for the event.";

                    } else {

                        $message =
                            "Registration failed: "
                            . $insert->error;
                    }

                    $insert->close();
                }
            }
        }
    }
}

if ($role === "admin"
    && $_SERVER["REQUEST_METHOD"] == "POST"
    && isset($_POST["add_event"])) {

    $event_name = trim($_POST["event_name"] ?? "");
    $event_date = $_POST["event_date"] ?? "";
    $venue = trim($_POST["venue"] ?? "");

    if ($event_name == ""
        || $event_date == ""
        || $venue == "") {

        $message = "Please fill all fields.";

    } else {

        $poster = "";

        /* Poster upload */

        if (isset($_FILES["poster"])
            && $_FILES["poster"]["error"] == 0) {

            $allowed_types = [
                "image/jpeg",
                "image/png",
                "image/jpg"
            ];

            $max_size = 2 * 1024 * 1024;

            if (!in_array(
                $_FILES["poster"]["type"],
                $allowed_types
            )) {

                $message =
                    "Only JPG, JPEG and PNG files are allowed.";

            } elseif (
                $_FILES["poster"]["size"] > $max_size
            ) {

                $message =
                    "Poster size must be less than 2 MB.";

            } else {

                $upload_folder = "uploads/";

                if (!is_dir($upload_folder)) {
                    mkdir($upload_folder, 0777, true);
                }

                $extension =
                    pathinfo(
                        $_FILES["poster"]["name"],
                        PATHINFO_EXTENSION
                    );

                $file_name =
                    time() . "_" . uniqid()
                    . "." . $extension;

                $poster =
                    $upload_folder . $file_name;

                if (!move_uploaded_file(
                    $_FILES["poster"]["tmp_name"],
                    $poster
                )) {

                    $message =
                        "Failed to upload poster.";

                    $poster = "";
                }
            }
        }

        if ($message == "") {

            $stmt = $conn->prepare(
                "INSERT INTO events
                (event_name, event_date, venue, poster)
                VALUES (?, ?, ?, ?)"
            );

            if (!$stmt) {

                $message =
                    "Database error: " . $conn->error;

            } else {

                $stmt->bind_param(
                    "ssss",
                    $event_name,
                    $event_date,
                    $venue,
                    $poster
                );

                if ($stmt->execute()) {

                    $message =
                        "Event added successfully.";

                } else {

                    $message =
                        "Failed to add event: "
                        . $stmt->error;
                }

                $stmt->close();
            }
        }
    }
}
if ($role === "admin"
    && isset($_GET["delete"])) {

    $event_id = intval($_GET["delete"]);

    if ($event_id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM events WHERE event_id = ?"
        );

        $stmt->bind_param("i", $event_id);

        if ($stmt->execute()) {

            $message =
                "Event deleted successfully.";

        } else {

            $message =
                "Failed to delete event.";
        }

        $stmt->close();
    }
}

$events = $conn->query(
    "SELECT event_id,
            event_name,
            event_date,
            venue,
            poster
     FROM events
     ORDER BY event_date ASC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>StudentHub Events</title>

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
    max-width: 1100px;
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
    border-radius: 4px;
}

button:hover {
    background: #174f76;
}

.message {
    background: #dff0d8;
    color: #256029;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 5px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
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

.delete {
    color: red;
    text-decoration: none;
}

.register-button {
    background: #28a745;
}

.register-button:hover {
    background: #218838;
}

</style>

</head>


<body>

<header>

<h1>StudentHub</h1>

<p>Events</p>

</header>


<div class="container">


<?php if ($message != ""): ?>

<div class="message">

<?php echo htmlspecialchars($message); ?>

</div>

<?php endif; ?>


<?php if ($role === "admin"): ?>

<div class="box">

<h2>Add Event</h2>

<form method="POST"
      enctype="multipart/form-data">

<label>Event Name:</label>

<input type="text"
       name="event_name"
       required>


<label>Event Date:</label>

<input type="date"
       name="event_date"
       required>


<label>Venue:</label>

<input type="text"
       name="venue"
       required>


<label>Event Poster:</label>

<input type="file"
       name="poster"
       accept=".jpg,.jpeg,.png">

<p>
Allowed: JPG, JPEG, PNG | Maximum size: 2 MB
</p>


<button type="submit"
        name="add_event">

Add Event

</button>

</form>

</div>

<?php endif; ?>

<div class="box">

<h2>Available Events</h2>

<table>

<tr>

<th>ID</th>

<th>Event Name</th>

<th>Date</th>

<th>Venue</th>

<th>Poster</th>

<th>Action</th>

</tr>


<?php if ($events && $events->num_rows > 0): ?>

<?php while ($event = $events->fetch_assoc()): ?>

<tr>

<td>
<?php echo $event["event_id"]; ?>
</td>

<td>
<?php echo htmlspecialchars(
    $event["event_name"]
); ?>
</td>

<td>
<?php echo htmlspecialchars(
    $event["event_date"]
); ?>
</td>

<td>
<?php echo htmlspecialchars(
    $event["venue"]
); ?>
</td>


<td>

<?php if (!empty($event["poster"])): ?>

<img src="<?php echo htmlspecialchars(
    $event["poster"]
); ?>">

<?php else: ?>

No poster

<?php endif; ?>

</td>

<td>

<?php if ($role === "admin"): ?>

<a class="delete"
   href="event.php?delete=<?php echo $event["event_id"]; ?>"
   onclick="return confirm('Delete this event?');">

Delete

</a>

<?php else: ?>

<form method="POST"
      style="margin:0;">

<input type="hidden"
       name="event_id"
       value="<?php echo $event["event_id"]; ?>">

<button type="submit"
        name="register_event"
        class="register-button">

Register

</button>

</form>


<?php endif; ?>


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

<?php

$conn->close();

?>