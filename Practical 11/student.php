<?php
include "db.php";

$message = "";
$messageType = "";

if (isset($_POST["add_student"])) {

    $full_name = trim($_POST["full_name"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $course = trim($_POST["course"]);
    $year = trim($_POST["year"]);
    $gender = trim($_POST["gender"]);

    if ($full_name == "" || $username == "" || $email == "" ||
        $course == "" || $year == "" || $gender == "") {

        $message = "Please fill all fields.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } else {



        $check = $conn->prepare(
            "SELECT student_id FROM student_records
             WHERE username = ? OR email = ?"
        );

        $check->bind_param("ss", $username, $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Username or email already exists.";
            $messageType = "error";

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO student_records
                (full_name, username, email, course, year, gender)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssssss",
                $full_name,
                $username,
                $email,
                $course,
                $year,
                $gender
            );

            if ($stmt->execute()) {

                $message = "Student added successfully!";
                $messageType = "success";

            } else {

                $message = "Failed to add student.";
                $messageType = "error";
            }

            $stmt->close();
        }

        $check->close();
    }
}

if (isset($_POST["delete_student"])) {

    $student_id = intval($_POST["student_id"]);

    $stmt = $conn->prepare(
        "DELETE FROM student_records WHERE student_id = ?"
    );

    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {

        $message = "Student deleted successfully!";
        $messageType = "success";

    } else {

        $message = "Failed to delete student.";
        $messageType = "error";
    }

    $stmt->close();
}


if (isset($_POST["update_student"])) {

    $student_id = intval($_POST["student_id"]);

    $full_name = trim($_POST["full_name"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $course = trim($_POST["course"]);
    $year = trim($_POST["year"]);
    $gender = trim($_POST["gender"]);

    if ($full_name == "" || $username == "" || $email == "" ||
        $course == "" || $year == "" || $gender == "") {

        $message = "Please fill all fields.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email.";
        $messageType = "error";

    } else {


        $check = $conn->prepare(
            "SELECT student_id FROM student_records
             WHERE (username = ? OR email = ?)
             AND student_id != ?"
        );

        $check->bind_param(
            "ssi",
            $username,
            $email,
            $student_id
        );

        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Username or email already exists.";
            $messageType = "error";

        } else {

            $stmt = $conn->prepare(
                "UPDATE student_records
                 SET full_name = ?,
                     username = ?,
                     email = ?,
                     course = ?,
                     year = ?,
                     gender = ?
                 WHERE student_id = ?"
            );

            $stmt->bind_param(
                "ssssssi",
                $full_name,
                $username,
                $email,
                $course,
                $year,
                $gender,
                $student_id
            );

            if ($stmt->execute()) {

                $message = "Student updated successfully!";
                $messageType = "success";

            } else {

                $message = "Failed to update student.";
                $messageType = "error";
            }

            $stmt->close();
        }

        $check->close();
    }
}
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$course_filter = isset($_GET["course_filter"]) ? trim($_GET["course_filter"]) : "";
$year_filter = isset($_GET["year_filter"]) ? trim($_GET["year_filter"]) : "";

$sql = "SELECT * FROM student_records WHERE 1=1";

$params = [];
$types = "";

if ($search != "") {

    $sql .= " AND (full_name LIKE ? OR username LIKE ? OR email LIKE ?)";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "sss";
}

if ($course_filter != "") {

    $sql .= " AND course = ?";

    $params[] = $course_filter;
    $types .= "s";
}

if ($year_filter != "") {

    $sql .= " AND year = ?";

    $params[] = $year_filter;
    $types .= "s";
}

$sql .= " ORDER BY student_id DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {

    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$students = $stmt->get_result();
$editStudent = null;

if (isset($_GET["edit"])) {

    $edit_id = intval($_GET["edit"]);

    $editStmt = $conn->prepare(
        "SELECT * FROM student_records WHERE student_id = ?"
    );

    $editStmt->bind_param("i", $edit_id);
    $editStmt->execute();

    $editResult = $editStmt->get_result();

    if ($editResult->num_rows == 1) {

        $editStudent = $editResult->fetch_assoc();
    }

    $editStmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Management - StudentHub</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #eef5fc;
    color: #222;
}

header {
    background-color: #2878ad;
    color: white;
    text-align: center;
    padding: 35px 20px;
}

header h1 {
    margin: 0;
    font-size: 32px;
}

header p {
    margin-top: 10px;
}

nav {
    background-color: #14527a;
    text-align: center;
    padding: 15px;
}

nav a {
    color: white;
    text-decoration: none;
    margin: 0 10px;
}

nav a:hover {
    text-decoration: underline;
}

.container {
    width: 90%;
    max-width: 1100px;
    margin: 30px auto;
}

.box {
    background-color: white;
    padding: 25px;
    margin-bottom: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

h2 {
    color: #2878ad;
    margin-top: 0;
}

.form-group {
    margin-bottom: 15px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 6px;
}

input,
select {
    width: 100%;
    padding: 11px;
    border: 1px solid #aaa;
    border-radius: 4px;
    font-size: 15px;
}

button {
    background-color: #2878ad;
    color: white;
    border: none;
    padding: 11px 18px;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 5px;
}

button:hover {
    background-color: #1d608c;
}

.cancel {
    background-color: #777;
    text-decoration: none;
    color: white;
    padding: 11px 18px;
    border-radius: 4px;
}

.delete {
    background-color: #d9534f;
}

.delete:hover {
    background-color: #b52b27;
}

.message {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 5px;
    font-weight: bold;
}

.success {
    background-color: #d4edda;
    color: #155724;
}

.error {
    background-color: #f8d7da;
    color: #721c24;
}

.search-area {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 10px;
    align-items: end;
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th,
td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
}

th {
    background-color: #2878ad;
    color: white;
}

tr:nth-child(even) {
    background-color: #f5f5f5;
}

.actions {
    white-space: nowrap;
}

.no-data {
    text-align: center;
    padding: 20px;
    color: #777;
}

@media (max-width: 700px) {

    .search-area {
        grid-template-columns: 1fr;
    }

    nav a {
        display: inline-block;
        margin: 5px;
    }

    .container {
        width: 95%;
    }

    table {
        min-width: 850px;
    }

}

</style>

</head>

<body>

<header>

<h1>StudentHub</h1>

<p>Student Management</p>

</header>


<nav>

<a href="index.html">Home</a>
<a href="about.html">About</a>
<a href="register.php">Register</a>
<a href="login.php">Login</a>
<a href="dashboard.php">Dashboard</a>
<a href="events.html">Events</a>
<a href="profile.html">Profile</a>
<a href="students.php">Students</a>
<a href="faq.html">FAQ</a>
<a href="contact.html">Contact</a>

</nav>


<div class="container">

<?php if ($message != ""): ?>

<div class="message <?php echo $messageType; ?>">

<?php echo htmlspecialchars($message); ?>

</div>

<?php endif; ?>

<div class="box">

<?php if ($editStudent): ?>

<h2>Edit Student</h2>

<form method="POST" action="students.php">

<input
    type="hidden"
    name="student_id"
    value="<?php echo $editStudent['student_id']; ?>"
>

<div class="form-group">

<label>Full Name</label>

<input
    type="text"
    name="full_name"
    value="<?php echo htmlspecialchars($editStudent['full_name']); ?>"
    required
>

</div>


<div class="form-group">

<label>Username</label>

<input
    type="text"
    name="username"
    value="<?php echo htmlspecialchars($editStudent['username']); ?>"
    required
>

</div>


<div class="form-group">

<label>Email</label>

<input
    type="email"
    name="email"
    value="<?php echo htmlspecialchars($editStudent['email']); ?>"
    required
>

</div>


<div class="form-group">

<label>Course</label>

<select name="course" required>

<option value="">Select Course</option>

<option value="B.Tech IT"
<?php if ($editStudent['course'] == "B.Tech IT") echo "selected"; ?>>
B.Tech IT
</option>

<option value="B.Tech CSE"
<?php if ($editStudent['course'] == "B.Tech CSE") echo "selected"; ?>>
B.Tech CSE
</option>

<option value="B.Tech CE"
<?php if ($editStudent['course'] == "B.Tech CE") echo "selected"; ?>>
B.Tech CE
</option>

<option value="B.Tech EC"
<?php if ($editStudent['course'] == "B.Tech EC") echo "selected"; ?>>
B.Tech EC
</option>

</select>

</div>


<div class="form-group">

<label>Year</label>

<select name="year" required>

<option value="">Select Year</option>

<option value="First Year"
<?php if ($editStudent['year'] == "First Year") echo "selected"; ?>>
First Year
</option>

<option value="Second Year"
<?php if ($editStudent['year'] == "Second Year") echo "selected"; ?>>
Second Year
</option>

<option value="Third Year"
<?php if ($editStudent['year'] == "Third Year") echo "selected"; ?>>
Third Year
</option>

<option value="Fourth Year"
<?php if ($editStudent['year'] == "Fourth Year") echo "selected"; ?>>
Fourth Year
</option>

</select>

</div>


<div class="form-group">

<label>Gender</label>

<select name="gender" required>

<option value="">Select Gender</option>

<option value="Male"
<?php if ($editStudent['gender'] == "Male") echo "selected"; ?>>
Male
</option>

<option value="Female"
<?php if ($editStudent['gender'] == "Female") echo "selected"; ?>>
Female
</option>

<option value="Other"
<?php if ($editStudent['gender'] == "Other") echo "selected"; ?>>
Other
</option>

</select>

</div>


<button type="submit" name="update_student">
Update Student
</button>

<a href="students.php" class="cancel">
Cancel
</a>

</form>


<?php else: ?>


<h2>Add Student</h2>

<form method="POST" action="students.php">

<div class="form-group">

<label>Full Name</label>

<input
    type="text"
    name="full_name"
    placeholder="Enter full name"
    required
>

</div>


<div class="form-group">

<label>Username</label>

<input
    type="text"
    name="username"
    placeholder="Enter username"
    required
>

</div>


<div class="form-group">

<label>Email</label>

<input
    type="email"
    name="email"
    placeholder="example@gmail.com"
    required
>

</div>


<div class="form-group">

<label>Course</label>

<select name="course" required>

<option value="">Select Course</option>

<option value="B.Tech IT">B.Tech IT</option>

<option value="B.Tech CSE">B.Tech CSE</option>

<option value="B.Tech CE">B.Tech CE</option>

<option value="B.Tech EC">B.Tech EC</option>

</select>

</div>


<div class="form-group">

<label>Year</label>

<select name="year" required>

<option value="">Select Year</option>

<option value="First Year">First Year</option>

<option value="Second Year">Second Year</option>

<option value="Third Year">Third Year</option>

<option value="Fourth Year">Fourth Year</option>

</select>

</div>


<div class="form-group">

<label>Gender</label>

<select name="gender" required>

<option value="">Select Gender</option>

<option value="Male">Male</option>

<option value="Female">Female</option>

<option value="Other">Other</option>

</select>

</div>


<button type="submit" name="add_student">
Add Student
</button>

</form>


<?php endif; ?>

</div>

<div class="box">

<h2>Search & Filter Students</h2>

<form method="GET" action="students.php">

<div class="search-area">

<div>

<label>Search</label>

<input
    type="text"
    name="search"
    placeholder="Search name, username or email"
    value="<?php echo htmlspecialchars($search); ?>"
>

</div>


<div>

<label>Course</label>

<select name="course_filter">

<option value="">All Courses</option>

<option value="B.Tech IT"
<?php if ($course_filter == "B.Tech IT") echo "selected"; ?>>
B.Tech IT
</option>

<option value="B.Tech CSE"
<?php if ($course_filter == "B.Tech CSE") echo "selected"; ?>>
B.Tech CSE
</option>

<option value="B.Tech CE"
<?php if ($course_filter == "B.Tech CE") echo "selected"; ?>>
B.Tech CE
</option>

<option value="B.Tech EC"
<?php if ($course_filter == "B.Tech EC") echo "selected"; ?>>
B.Tech EC
</option>

</select>

</div>


<div>

<label>Year</label>

<select name="year_filter">

<option value="">All Years</option>

<option value="First Year"
<?php if ($year_filter == "First Year") echo "selected"; ?>>
First Year
</option>

<option value="Second Year"
<?php if ($year_filter == "Second Year") echo "selected"; ?>>
Second Year
</option>

<option value="Third Year"
<?php if ($year_filter == "Third Year") echo "selected"; ?>>
Third Year
</option>

<option value="Fourth Year"
<?php if ($year_filter == "Fourth Year") echo "selected"; ?>>
Fourth Year
</option>

</select>

</div>


<div>

<button type="submit">
Search
</button>

<a href="students.php" class="cancel">
Clear
</a>

</div>

</div>

</form>

</div>


<div class="box">

<h2>Student Records</h2>

<div class="table-container">

<table>

<thead>

<tr>

<th>ID</th>

<th>Full Name</th>

<th>Username</th>

<th>Email</th>

<th>Course</th>

<th>Year</th>

<th>Gender</th>

<th>Actions</th>

</tr>

</thead>


<tbody>

<?php if ($students->num_rows > 0): ?>

<?php while ($student = $students->fetch_assoc()): ?>

<tr>

<td>
<?php echo $student["student_id"]; ?>
</td>

<td>
<?php echo htmlspecialchars($student["full_name"]); ?>
</td>

<td>
<?php echo htmlspecialchars($student["username"]); ?>
</td>

<td>
<?php echo htmlspecialchars($student["email"]); ?>
</td>

<td>
<?php echo htmlspecialchars($student["course"]); ?>
</td>

<td>
<?php echo htmlspecialchars($student["year"]); ?>
</td>

<td>
<?php echo htmlspecialchars($student["gender"]); ?>
</td>

<td class="actions">

<a
    href="students.php?edit=<?php echo $student['student_id']; ?>"
>
<button type="button">
Edit
</button>
</a>


<form
    method="POST"
    action="students.php"
    style="display:inline;"
    onsubmit="return confirm('Are you sure you want to delete this student?');"
>

<input
    type="hidden"
    name="student_id"
    value="<?php echo $student['student_id']; ?>"
>

<button
    type="submit"
    name="delete_student"
    class="delete"
>
Delete
</button>

</form>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="8" class="no-data">

No student records found.

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>


</div>

</body>

</html>

<?php
$stmt->close();
$conn->close();
?>