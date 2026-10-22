<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "studenthub";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");



if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$stmt = $conn->prepare(
    "SELECT student_id, name, username, email, role
     FROM students
     WHERE student_id = ?"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$currentUser = $result->fetch_assoc();

$stmt->close();

if (strtolower(trim($currentUser['role'])) !== "admin") {
    header("Location: dashboard.php");
    exit();
}


$studentResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM students
     WHERE role = 'student'"
);

$totalStudents = 0;

if ($studentResult) {
    $row = $studentResult->fetch_assoc();
    $totalStudents = $row['total'];
}


$userResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM students"
);

$totalUsers = 0;

if ($userResult) {
    $row = $userResult->fetch_assoc();
    $totalUsers = $row['total'];
}


$eventResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM events"
);

$totalEvents = 0;

if ($eventResult) {
    $row = $eventResult->fetch_assoc();
    $totalEvents = $row['total'];
}

$latestRegistrations = [];

$sql = "SELECT
            r.registration_id,
            r.registration_date,
            s.name AS student_name,
            s.email,
            e.event_name,
            e.event_date
        FROM registrations r
        LEFT JOIN students s
            ON r.student_id = s.student_id
        LEFT JOIN events e
            ON r.event_id = e.event_id
        ORDER BY r.registration_date DESC
        LIMIT 10";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $latestRegistrations[] = $row;
    }

} else {

    die("Registration database error: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>StudentHub Admin Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #eef6ff;
        }

        .navbar {
            background: #2874a6;
            color: white;
            padding: 18px 35px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
        }

        .menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .menu a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            padding: 8px 12px;
            border-radius: 5px;
        }

        .menu a:hover {
            background: rgba(255,255,255,0.2);
        }

        .logout {
            background: #e74c3c;
        }
        .container {
            max-width: 1200px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .welcome {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;

            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .welcome h1 {
            margin-top: 0;
            color: #2874a6;
        }


        .stats {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(220px, 1fr));

            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;

            box-shadow: 0 2px 10px rgba(0,0,0,0.08);

            text-align: center;
        }

        .card h3 {
            margin: 0;
            color: #555;
            font-size: 18px;
        }

        .number {
            font-size: 40px;
            font-weight: bold;
            color: #2874a6;
            margin-top: 10px;
        }

        .registration-box {
            background: white;
            padding: 25px;
            border-radius: 10px;

            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .registration-box h2 {
            margin-top: 0;
            color: #2874a6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background: #2874a6;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f5faff;
        }

        .no-data {
            text-align: center;
            padding: 25px;
            color: #777;
        }

        @media(max-width: 700px) {

            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            .menu {
                flex-wrap: wrap;
                justify-content: center;
            }

            table {
                font-size: 13px;
            }

        }

    </style>

</head>


<body>

<div class="navbar">

    <div class="logo">
        StudentHub Admin
    </div>

    <div class="menu">


        <a href="dashboard.php">
            Dashboard
        </a>

        <?php if ($currentUser['role'] === 'admin'): ?>

    

            <a href="event.html">
                Manage Events
            </a>

            <a href="admin.php">
                Admin Panel
            </a>

        <?php else: ?>

    

            <a href="events.php">
                Events
            </a>

            <a href="profile.php">
                Profile
            </a>

        <?php endif; ?>


        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>

</div>

<div class="container">


    <div class="welcome">

        <h1>
            Admin Dashboard
        </h1>

        <p>
            Welcome,
            <strong>
                <?php echo htmlspecialchars($currentUser['name']); ?>
            </strong>!
        </p>

        <p>
            You are logged in as an
            <strong>Administrator</strong>.
        </p>

    </div>

    <div class="stats">


        <div class="card">

            <h3>
                Total Users
            </h3>

            <div class="number">
                <?php echo $totalUsers; ?>
            </div>

        </div>


        <div class="card">

            <h3>
                Total Students
            </h3>

            <div class="number">
                <?php echo $totalStudents; ?>
            </div>

        </div>


        <div class="card">

            <h3>
                Total Events
            </h3>

            <div class="number">
                <?php echo $totalEvents; ?>
            </div>

        </div>


    </div>

    <div class="section">
    <h2>Latest Registrations</h2>

    <?php if (count($latestRegistrations) > 0): ?>

        <table>
            <tr>
                <th>Student</th>
                <th>Email</th>
                <th>Event</th>
                <th>Event Date</th>
                <th>Registration Date</th>
            </tr>

            <?php foreach ($latestRegistrations as $registration): ?>

                <tr>
                    <td>
                        <?php echo htmlspecialchars($registration['student_name']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($registration['email']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($registration['event_name']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($registration['event_date']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($registration['registration_date']); ?>
                    </td>
                </tr>

            <?php endforeach; ?>

        </table>

    <?php else: ?>

        <p>No registrations found.</p>

    <?php endif; ?>
</div>

</div>


</body>
</html>

<?php
$conn->close();
?>