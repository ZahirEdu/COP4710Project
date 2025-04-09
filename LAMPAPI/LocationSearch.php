<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");

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

$address = $data['address'] ?? null;

if (empty($address)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "address is missing in JSON"]);
    $conn->close();
    exit();
}

$stmt = $conn->prepare("SELECT locationID FROM locations WHERE address = ?");
$stmt->bind_param("s", $address);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $locations = $result->fetch_all(MYSQLI_ASSOC);
    $response = ["status" => "success", "locations" => $locations];
} else {
    $response = ["status" => "error", "message" => "no location found"];
    http_response_code(404); // Not Found
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>