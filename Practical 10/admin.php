<?php
require_once "session.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["role"] !== "admin") {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Admin</title>
</head>

<body style="font-family: Arial; background:#eef6ff; padding:30px;">

    <h1>StudentHub Admin Panel</h1>

    <p>Welcome, <?php
        echo htmlspecialchars($_SESSION["name"], ENT_QUOTES, "UTF-8");
    ?>!</p>

    <p>You are logged in as an administrator.</p>

    <a href="logout.php">Logout</a>

</body>
</html>