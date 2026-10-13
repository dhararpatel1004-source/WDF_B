<?php
require_once "session.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["role"] === "admin") {
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Dashboard</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef6ff;
            margin: 0;
            color: #333;
        }

        header {
            background: #2c6e9e;
            color: white;
            text-align: center;
            padding: 25px;
        }

        main {
            max-width: 800px;
            width: 90%;
            margin: 30px auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-sizing: border-box;
        }

        a {
            display: inline-block;
            background: #2c6e9e;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            margin: 5px;
        }
    </style>
</head>

<body>

<header>
    <h1>StudentHub Dashboard</h1>
    <p>Welcome, <?php
        echo htmlspecialchars($_SESSION["name"], ENT_QUOTES, "UTF-8");
    ?>!</p>
</header>

<main>
    <h2>Your Dashboard</h2>
    <p>You have successfully logged in.</p>

    <a href="index.html">Home</a>
    <a href="events.html">Events</a>
    <a href="profile.html">Profile</a>
    <a href="logout.php">Logout</a>
</main>

</body>
</html>