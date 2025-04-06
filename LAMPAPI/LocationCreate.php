<?php

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("connection failed: " . $conn->connect_error);
}

$name = $_POST['name'] ?? null;
$lat = $_POST['lat'] ?? null;
$lon = $_POST['lon'] ?? null;
$address = $_POST['address'] ?? null;
$place_id = $_POST['place_id'] ?? null;
$room = $_POST['room'] ?? null;


if (empty($name) || $lat === null || $lon === null) {
    $response = array("status" => "error", "message" => "name, latitude, and longitude are required");
    echo json_encode($response);
    $conn->close();
    exit();
}

if (!empty($address) && !empty($room)) {
    $checkStmt = $conn->prepare("SELECT locationID FROM locations WHERE address = ? AND room = ?");
    $checkStmt->bind_param("ss", $address, $room);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $existingLocation = $checkResult->fetch_assoc();
        $response = array("status" => "info", "message" => "location with the same address and room already exists", "locationID" => $existingLocation['locationID']);
        echo json_encode($response);
        $checkStmt->close();
        $conn->close();
        exit();
    }
    $checkStmt->close();
}

$stmt = $conn->prepare("INSERT INTO locations (name, lat, lon, address, place_id, room) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sddsss", $name, $lat, $lon, $address, $place_id, $room);

nt
if ($stmt->execute()) {
    $response = array("status" => "success", "message" => "location created successfully", "locationID" => $conn->insert_id);
} else {
    $response = array("status" => "error", "message" => "error: " . $stmt->error);
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>