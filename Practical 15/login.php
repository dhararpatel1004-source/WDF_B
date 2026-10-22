<?php
require_once "session.php";
require_once "db.php";

$error = "";

if (isset($_SESSION["student_id"])) {
    if ($_SESSION["role"] === "admin") {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $identity = trim($_POST["identity"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($identity === "" || $password === "") {

        $error = "Please enter your username/email and password.";

    } else {

        $sql = "SELECT student_id, name, username, email,
                       password, role
                FROM students
                WHERE username = ? OR email = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = "Login is temporarily unavailable.";
        } else {

            $stmt->bind_param("ss", $identity, $identity);
            $stmt->execute();

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify(
                $password,
                $user["password"]
            )) {

        
                session_regenerate_id(true);

                $_SESSION["student_id"] = $user["student_id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["role"] = $user["role"];
                $_SESSION["last_activity"] = time();

                $stmt->close();
                $conn->close();

                // Role-based redirection
                if ($user["role"] === "admin") {
                    header("Location: admin.php");
                } else {
                    header("Location: dashboard.php");
                }

                exit();

            } else {

                $error = "Invalid username/email or password.";

            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Login</title>

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
            padding: 20px;
        }

        main {
            max-width: 450px;
            width: 90%;
            margin: 35px auto;
            background: white;
            padding: 25px;
            box-sizing: border-box;
            border-radius: 8px;
        }

        label {
            display: block;
            margin: 15px 0 7px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 11px;
            box-sizing: border-box;
        }

        button {
            background: #2c6e9e;
            color: white;
            border: none;
            padding: 12px 20px;
            margin-top: 18px;
            cursor: pointer;
        }

        .error {
            color: red;
        }
    </style>
</head>

<body>

<header>
    <h1>StudentHub</h1>
    <p>Login to your account</p>
</header>

<main>
    <h2>Login Form</h2>

    <?php if (isset($_GET["timeout"])): ?>
        <p class="error">Your session expired. Please log in again.</p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p class="error">
            <?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="login.php">

        <label for="identity">Username or Email:</label>
        <input type="text" id="identity" name="identity" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>

    </form>
</main>

</body>
</html>