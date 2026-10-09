<?php

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";
    $course = trim($_POST["course"] ?? "");
    $year = trim($_POST["year"] ?? "");
    $gender = trim($_POST["gender"] ?? "");

    // Server-side validation
    if (!preg_match("/^[A-Za-z ]+$/", $name)) {
        $message = "Please enter a valid name.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $message = "Mobile number must contain 10 digits.";
    } elseif (!preg_match("/^(?=.*[A-Za-z])(?=.*[0-9]).{6,}$/", $password)) {
        $message = "Password must contain letters and numbers and be at least 6 characters.";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
    } elseif (!in_array($course, ["B.Tech IT", "B.Tech CSE", "B.Tech CE", "B.Tech ME"], true)) {
        $message = "Please select a valid course.";
    } elseif (!in_array($year, ["First Year", "Second Year", "Third Year", "Fourth Year"], true)) {
        $message = "Please select a valid year.";
    } elseif (!in_array($gender, ["Male", "Female", "Other"], true)) {
        $message = "Please select your gender.";
    } elseif (!isset($_POST["terms"])) {
        $message = "Please accept the terms and conditions.";
    } else {

        // CSV file in the same folder as register.php
        $filePath = __DIR__ . "/students.csv";

        $file = fopen($filePath, "a+");

        if ($file === false) {

            $message = "Unable to open students.csv. Check folder permissions.";

        } elseif (!flock($file, LOCK_EX)) {

            fclose($file);
            $message = "Unable to lock the CSV file.";

        } else {

            // Find the end of the file
            fseek($file, 0, SEEK_END);
            $fileSize = ftell($file);

            // Add headings if the file is empty
            $headerSaved = true;

            if ($fileSize === 0) {
                $headerSaved = fputcsv($file, [
                    "Name", "Email", "Mobile",
                    "Password Hash", "Course", "Year", "Gender"
                ]) !== false;
            }

            // Hash password; never store plain-text passwords
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Save registration
            $saved = false;

            if ($headerSaved) {
                $saved = fputcsv($file, [
                    $name,
                    $email,
                    $mobile,
                    $hashedPassword,
                    $course,
                    $year,
                    $gender
                ]);
            }

            fflush($file);
            flock($file, LOCK_UN);
            fclose($file);

            if ($saved !== false) {

                // Redirect only after the record has been written
                header("Location: index.html?registered=1");
                exit();

            } else {

                $message = "Registration could not be saved. Please try again.";

            }
        }
    }

    if ($message !== "") {
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Registration - StudentHub</title>

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
    margin: 25px auto;
}

section {
    background-color: white;
    padding: 25px;
    border-radius: 8px;
}

h2 {
    color: #2c6e9e;
}

label {
    font-weight: bold;
}

input:not([type="radio"]):not([type="checkbox"]):not([type="submit"]):not([type="reset"]),
select {
    width: 95%;
    padding: 9px;
    margin: 8px 0;
    box-sizing: border-box;
}

input[type="radio"],
input[type="checkbox"] {
    width: auto;
}

input[type="submit"],
input[type="reset"] {
    background-color: #2c6e9e;
    color: white;
    border: none;
    padding: 10px 20px;
    margin: 5px;
    cursor: pointer;
}

.error {
    color: red;
    font-weight: bold;
}

.success {
    color: green;
    font-weight: bold;
}

footer {
    background-color: #164a70;
    color: white;
    text-align: center;
    padding: 15px;
    margin-top: 25px;
}

@media (max-width: 600px) {
    main {
        width: 90%;
    }

    nav a {
        display: block;
        margin: 10px;
    }

    section {
        padding: 15px;
    }

    input:not([type="radio"]):not([type="checkbox"]):not([type="submit"]):not([type="reset"]),
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
        <p class="error">
            <?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="register.php">

        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name"
               placeholder="Enter your name" required>
        <br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email"
               placeholder="Enter your email" required>
        <br>

        <label for="mobile">Mobile Number:</label><br>
        <input type="tel" id="mobile" name="mobile"
               placeholder="Enter 10 digit mobile number"
               pattern="[0-9]{10}" required>
        <br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password"
               placeholder="Enter password" required>
        <br>

        <label for="confirmPassword">Confirm Password:</label><br>
        <input type="password" id="confirmPassword"
               name="confirmPassword"
               placeholder="Enter password again" required>
        <br>

        <label for="course">Course:</label><br>
        <select id="course" name="course" required>
            <option value="">Select Course</option>
            <option value="B.Tech IT">B.Tech IT</option>
            <option value="B.Tech CSE">B.Tech CSE</option>
            <option value="B.Tech CE">B.Tech CE</option>
            <option value="B.Tech ME">B.Tech ME</option>
        </select>
        <br>

        <label for="year">Year:</label><br>
        <select id="year" name="year" required>
            <option value="">Select Year</option>
            <option value="First Year">First Year</option>
            <option value="Second Year">Second Year</option>
            <option value="Third Year">Third Year</option>
            <option value="Fourth Year">Fourth Year</option>
        </select>

        <p><b>Gender:</b></p>

        <input type="radio" id="male" name="gender"
               value="Male" required>
        <label for="male">Male</label>

        <input type="radio" id="female" name="gender"
               value="Female">
        <label for="female">Female</label>

        <input type="radio" id="other" name="gender"
               value="Other">
        <label for="other">Other</label>

        <br><br>

        <input type="checkbox" id="terms" name="terms" value="yes" required>
        <label for="terms">I accept the Terms and Conditions</label>

        <br><br>

        <input type="submit" value="Register">
        <input type="reset" value="Clear">

    </form>

</section>
</main>

<footer>
    <p>&copy; 2026 StudentHub</p>
</footer>

</body>
</html>