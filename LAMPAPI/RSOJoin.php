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

$rsoID = $data['rsoID'] ?? null;
$UID = $data['UID'] ?? null;

if ($rsoID === null || $UID === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "rsoID and UID are required in JSON"]);
    $conn->close();
    exit();
}

if (!is_numeric($rsoID) || !is_numeric($UID)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "rsoID and UID must be numeric in JSON"]);
    $conn->close();
    exit();
}

$checkStmt = $conn->prepare("SELECT rsoMemID FROM rsoMembers WHERE rsoID = ? AND UID = ?");
$checkStmt->bind_param("ii", $rsoID, $UID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "User is already a member of this RSO"]);
    $checkStmt->close();
    $conn->close();
    exit();
}
$checkStmt->close();

$insertStmt = $conn->prepare("INSERT INTO rsoMembers (rsoID, UID) VALUES (?, ?)");
$insertStmt->bind_param("ii", $rsoID, $UID);

if ($insertStmt->execute()) {
    $countStmt = $conn->prepare("SELECT COUNT(UID) AS memberCount FROM rsoMembers WHERE rsoID = ?");
    $countStmt->bind_param("i", $rsoID);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $row = $countResult->fetch_assoc();
    $memberCount = $row['memberCount'];
    $countStmt->close();

    http_response_code(201); // Created
    if ($memberCount > 4) {
        $updateStmt = $conn->prepare("UPDATE rsos SET status = 'active' WHERE rsoID = ?");
        $updateStmt->bind_param("i", $rsoID);
        $updateStmt->execute();
        $updateStmt->close();
        $response = ["status" => "success", "message" => "Successfully joined RSO. RSO status updated to active."];
    } else {
        $response = ["status" => "success", "message" => "Successfully joined RSO."];
    }
} else {
    http_response_code(500); // Internal Server Error
    $response = ["status" => "error", "message" => "Error joining RSO: " . $insertStmt->error];
}

$insertStmt->close();
$conn->close();

echo json_encode($response);
?>