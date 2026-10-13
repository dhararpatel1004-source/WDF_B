<?php
require_once "db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get form data
    $name = trim($_POST["name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";
    $course = trim($_POST["course"] ?? "");
    $year = trim($_POST["year"] ?? "");

    // Backend validation
    if (!preg_match("/^[A-Za-z ]{2,100}$/", $name)) {
        $message = "Please enter a valid name.";

    } elseif (!preg_match("/^[A-Za-z0-9_]{3,50}$/", $username)) {
        $message = "Username must be 3-50 letters, numbers or underscores.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";

    } elseif (strlen($password) < 8) {
        $message = "Password must contain at least 8 characters.";

    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";

    } elseif (!in_array($course, [
        "B.Tech IT", "B.Tech CSE", "B.Tech CE", "B.Tech ME"
    ], true)) {
        $message = "Please select a valid course.";

    } elseif (!in_array($year, [
        "First Year", "Second Year", "Third Year", "Fourth Year"
    ], true)) {
        $message = "Please select a valid year.";

    } else {

        // Check duplicate username or email
        $sql = "SELECT student_id FROM students
                WHERE username = ? OR email = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
    $message = "Database error: " . $conn->error;
}
         else {

            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {

                $message = "Username or email already exists.";

            } else {

                // Hash password before saving
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Insert student using prepared statement
                $insert = $conn->prepare(
                    "INSERT INTO students
                    (name, username, email, password, course, year)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );
                if (!$insert) {
    die("SQL Error: " . $conn->error);
}

                if (!$insert) {
    $message = "Database error: " . $conn->error;
}
                else {

                    $insert->bind_param(
                        "ssssss",
                        $name,
                        $username,
                        $email,
                        $hashedPassword,
                        $course,
                        $year
                    );

                    if ($insert->execute()) {

                        $insert->close();
                        $stmt->close();
                        $conn->close();

                        // Redirect after successful registration
                        header("Location: index.html?registered=1");
                        exit();

                    } elseif ($insert->errno === 1062) {

                        $message = "Username or email already exists.";

                    }  else {
    $message = "Registration failed: " . $insert->error;
}

                    }

                    if ($insert instanceof mysqli_stmt) {
    $insert->close();
}
                }
            }

            if ($stmt instanceof mysqli_stmt) {
    $stmt->close();
}
        }
    }

    if ($message !== "") {
        $messageType = "error";
    }

?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>StudentHub Registration</title>

<style>




body {
    font-family: Arial, sans-serif;
    margin: 0;
    background-color: #eef6ff;
    color: #333;
}

header {
    background-color: #2c6e9e;
    color: white;
    text-align: center;
    padding: 20px;
}

nav {
    background-color: #164a70;
    text-align: center;
    padding: 12px;
}

nav a {
    color: white;
    text-decoration: none;
    margin: 8px;
}

main {
    width: 70%;
    max-width: 850px;
    margin: 30px auto;
}

section {
    background-color: white;
    padding: 28px;
    border-radius: 8px;
    box-sizing: border-box;
}

h2 {
    color: #2c6e9e;
    margin-top: 0;
    margin-bottom: 25px;
}

label {
    display: block;
    font-weight: bold;
    margin-top: 15px;
    margin-bottom: 7px;
}

input,
select {
    display: block;
    width: 100%;
    padding: 12px;
    margin: 0 0 16px;
    border: 1px solid #aaa;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 15px;
}

button {
    background-color: #2c6e9e;
    color: white;
    border: none;
    padding: 12px 22px;
    margin: 8px 5px 0 0;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #164a70;
}

.error {
    color: red;
    font-weight: bold;
}

@media (max-width: 600px) {
    main {
        width: 92%;
        margin: 18px auto;
    }

    section {
        padding: 18px;
    }

    nav a {
        display: inline-block;
        margin: 6px;
    }

    input,
    select {
        width: 100%;
    }
}


</style>
</head>

<body>

<header>
    <h1>StudentHub</h1>
    <p>Student Registration</p>
</header>

<nav>
    <a href="index.html">Home</a>
    <a href="about.html">About</a>
    <a href="register.php">Register</a>
    <a href="login.html">Login</a>
    <a href="dashboard.html">Dashboard</a>
    <a href="event.html">Events</a>
    <a href="profile.html">Profile</a>
    <a href="admin.html">Admin</a>
    <a href="faq.html">FAQ</a>
    <a href="feedback.php">Contact</a>
</nav>

<main>
<section>

    <h2>Registration Form</h2>

    <?php if ($message !== ""): ?>
        <p class="<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="register.php" onsubmit="return validateForm()">

        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name"
               minlength="2" maxlength="100"
               pattern="[A-Za-z ]{2,100}" required>

        <label for="username">Username:</label>
        <input type="text" id="username" name="username"
               minlength="3" maxlength="50"
               pattern="[A-Za-z0-9_]{3,50}" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email"
               maxlength="100" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password"
               minlength="8" required>

        <label for="confirmPassword">Confirm Password:</label>
        <input type="password" id="confirmPassword"
               name="confirmPassword" minlength="8" required>

        <label for="course">Course:</label>
        <select id="course" name="course" required>
            <option value="">Select Course</option>
            <option value="B.Tech IT">B.Tech IT</option>
            <option value="B.Tech CSE">B.Tech CSE</option>
            <option value="B.Tech CE">B.Tech CE</option>
            <option value="B.Tech ME">B.Tech ME</option>
        </select>

        <label for="year">Year:</label>
        <select id="year" name="year" required>
            <option value="">Select Year</option>
            <option value="First Year">First Year</option>
            <option value="Second Year">Second Year</option>
            <option value="Third Year">Third Year</option>
            <option value="Fourth Year">Fourth Year</option>
        </select>

        <button type="submit">Register</button>
        <button type="reset">Clear</button>

    </form>

</section>
</main>

<script>
function validateForm() {

    let password = document.getElementById("password").value;
    let confirmPassword =
        document.getElementById("confirmPassword").value;

    if (password !== confirmPassword) {
        alert("Passwords do not match.");
        return false;
    }

    return true;
}
</script>

</body>
</html>