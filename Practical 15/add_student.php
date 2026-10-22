<?php

require_once "db.php";

$name = "Diya Panara";
$email = "diya@example.com";
$password = password_hash("diya123", PASSWORD_DEFAULT);
$course = "B.Tech IT";
$year = "First Year";

$sql = "INSERT INTO students
        (name, email, password, course, year)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sssss",
    $name,
    $email,
    $password,
    $course,
    $year
);

if ($stmt->execute()) {
    echo "Student record added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();

?>