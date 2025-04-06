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

if ($checkResult->num_rows === 0) {
    http_response_code(404); // Not Found
    echo json_encode(["status" => "error", "message" => "User is not a member of this RSO."]);
    $checkStmt->close();
    $conn->close();
    exit();
}
$checkStmt->close();

$deleteStmt = $conn->prepare("DELETE FROM rsoMembers WHERE rsoID = ? AND UID = ?");
$deleteStmt->bind_param("ii", $rsoID, $UID);

if ($deleteStmt->execute()) {

    $countStmt = $conn->prepare("SELECT COUNT(UID) AS memberCount FROM rsoMembers WHERE rsoID = ?");
    $countStmt->bind_param("i", $rsoID);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $row = $countResult->fetch_assoc();
    $memberCount = $row['memberCount'];
    $countStmt->close();

    $responseMessage = "Successfully left RSO.";
    if ($memberCount < 5) {
        $updateStmt = $conn->prepare("UPDATE rsos SET status = 'inactive' WHERE rsoID = ?");
        $updateStmt->bind_param("i", $rsoID);
        $updateStmt->execute();
        $updateStmt->close();
        $responseMessage = "Successfully left RSO. RSO status updated to inactive.";
    }
    http_response_code(200); // OK
    $response = ["status" => "success", "message" => $responseMessage];

} else {
    http_response_code(500); // Internal Server Error
    $response = ["status" => "error", "message" => "Error leaving RSO: " . $deleteStmt->error];
}

$deleteStmt->close();
$conn->close();

echo json_encode($response);
?>