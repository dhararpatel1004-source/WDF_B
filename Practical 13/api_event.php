<?php

header("Content-Type: application/json");

$host = "localhost";
$user = "root";
$password = "";
$database = "studenthub";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

$conn->set_charset("utf8mb4");

$method = $_SERVER["REQUEST_METHOD"];


/* =========================
   GET - SHOW ALL EVENTS
   ========================= */

if ($method === "GET") {

    $sql = "SELECT event_id, event_name, event_date, venue 
            FROM events 
            ORDER BY event_date ASC";

    $result = $conn->query($sql);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "GET Error: " . $conn->error
        ]);
        exit;
    }

    $events = [];

    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $events
    ]);

    exit;
}


/* =========================
   POST - ADD EVENT
   ========================= */

if ($method === "POST") {

    $input = file_get_contents("php://input");

    $data = json_decode($input, true);

    if ($data === null) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid JSON received"
        ]);
        exit;
    }

    $event_name = trim($data["event_name"] ?? "");
    $event_date = trim($data["event_date"] ?? "");
    $venue = trim($data["venue"] ?? "");

    if ($event_name === "" || $event_date === "" || $venue === "") {
        echo json_encode([
            "success" => false,
            "message" => "All fields are required"
        ]);
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO events (event_name, event_date, venue)
         VALUES (?, ?, ?)"
    );

    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Prepare Error: " . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param(
        "sss",
        $event_name,
        $event_date,
        $venue
    );

    if ($stmt->execute()) {

        echo json_encode([
            "success" => true,
            "message" => "Event added successfully",
            "event_id" => $stmt->insert_id
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Insert Error: " . $stmt->error
        ]);
    }

    $stmt->close();
    exit;
}


/* =========================
   PUT - UPDATE EVENT
   ========================= */

if ($method === "PUT") {

    $input = file_get_contents("php://input");

    $data = json_decode($input, true);

    if ($data === null) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid JSON received"
        ]);
        exit;
    }

    $event_id = intval($data["event_id"] ?? 0);
    $event_name = trim($data["event_name"] ?? "");
    $event_date = trim($data["event_date"] ?? "");
    $venue = trim($data["venue"] ?? "");

    if ($event_id <= 0) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid event ID"
        ]);
        exit;
    }

    if ($event_name === "" || $event_date === "" || $venue === "") {
        echo json_encode([
            "success" => false,
            "message" => "All fields are required"
        ]);
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE events
         SET event_name = ?, event_date = ?, venue = ?
         WHERE event_id = ?"
    );

    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Prepare Error: " . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param(
        "sssi",
        $event_name,
        $event_date,
        $venue,
        $event_id
    );

    if ($stmt->execute()) {

        echo json_encode([
            "success" => true,
            "message" => "Event updated successfully"
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Update Error: " . $stmt->error
        ]);
    }

    $stmt->close();
    exit;
}


/* =========================
   DELETE - DELETE EVENT
   ========================= */

if ($method === "DELETE") {

    $input = file_get_contents("php://input");

    $data = json_decode($input, true);

    if ($data === null) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid JSON received"
        ]);
        exit;
    }

    $event_id = intval($data["event_id"] ?? 0);

    if ($event_id <= 0) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid event ID"
        ]);
        exit;
    }

    $stmt = $conn->prepare(
        "DELETE FROM events WHERE event_id = ?"
    );

    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Prepare Error: " . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param("i", $event_id);

    if ($stmt->execute()) {

        echo json_encode([
            "success" => true,
            "message" => "Event deleted successfully"
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Delete Error: " . $stmt->error
        ]);
    }

    $stmt->close();
    exit;
}


/* =========================
   INVALID METHOD
   ========================= */

echo json_encode([
    "success" => false,
    "message" => "Invalid request method"
]);

$conn->close();

?>