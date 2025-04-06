<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
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
$description = $data['description'] ?? null;
$universityID = $data['universityID'] ?? null;
$adminID = $data['adminID'] ?? null;
$status = $data['status'] ?? null;

if (empty($name) || $universityID === null || $adminID === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Name, universityID, and adminID are required in JSON"]);
    $conn->close();
    exit();
}

if (!is_numeric($universityID) || !is_numeric($adminID)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "universityID and adminID must be numeric in JSON"]);
    $conn->close();
    exit();
}

$stmt = $conn->prepare("INSERT INTO rsos (name, description, universityID, adminID, status) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssiis", $name, $description, $universityID, $adminID, $status);

if ($stmt->execute()) {
    http_response_code(201); // Created
    $response = ["status" => "success", "message" => "rso created successfully ", "rsoID" => $conn->insert_id];
} else {
    http_response_code(500); // Internal Server Error
    $response = ["status" => "error", "message" => "error creating RSO: " . $stmt->error];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>