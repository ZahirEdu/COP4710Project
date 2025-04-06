<?php
header('Content-Type: application/json');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "connection failed: " . $conn->connect_error]));
}

// Get raw JSON data from the request body
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Invalid JSON data"]);
    $conn->close();
    exit();
}

$name = $data['name'] ?? null;
$lat = $data['lat'] ?? null;
$lon = $data['lon'] ?? null;
$address = $data['address'] ?? null;
$place_id = $data['place_id'] ?? null;
$room = $data['room'] ?? null;

if (empty($name) || $lat === null || $lon === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "name, latitude, and longitude are required"]);
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
        echo json_encode(["status" => "info", "message" => "location with the same address and room already exists", "locationID" => $existingLocation['locationID']]);
        $checkStmt->close();
        $conn->close();
        exit();
    }
    $checkStmt->close();
}

$stmt = $conn->prepare("INSERT INTO locations (name, lat, lon, address, place_id, room) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sddsss", $name, $lat, $lon, $address, $place_id, $room);

if ($stmt->execute()) {
    $response = ["status" => "success", "message" => "location created successfully", "locationID" => $conn->insert_id];
    http_response_code(201); // Created
} else {
    $response = ["status" => "error", "message" => "error: " . $stmt->error];
    http_response_code(500); // Internal Server Error
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>