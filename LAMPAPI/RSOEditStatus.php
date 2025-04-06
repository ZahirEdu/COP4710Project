<?php
$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$rsoID = $_POST['rsoID'] ?? null;
$status = $_POST['status'] ?? null;

if ($rsoID === null || $status === null) {
    $response = array("status" => "error", "message" => "rsoID and status are required");
    echo json_encode($response);
    $conn->close();
    exit();
}

if (!is_numeric($rsoID)) {
    $response = array("status" => "error", "message" => "rsoID must be numeric");
    echo json_encode($response);
    $conn->close();
    exit();
}

$stmt = $conn->prepare("UPDATE rsos SET status = ? WHERE rsoID = ?");
$stmt->bind_param("si", $status, $rsoID);


if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $response = array("status" => "success", "message" => "RSO status updated successfully");
    } else {
        $response = array("status" => "info", "message" => "No RSO found with the provided ID, or the status was already the same");
    }
} else {
    $response = array("status" => "error", "message" => "error updating RSO status: " . $stmt->error);
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>