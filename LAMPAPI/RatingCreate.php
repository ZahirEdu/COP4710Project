<?php
header("Content-Type: application/json");

$input = json_decode(file_get_contents('php://input'), true);
$eventID = $input['eventID'] ?? null;
$UID = $input['UID'] ?? null;
$rating = $input['rating'] ?? null;

if ($eventID === null || $UID === null || $rating === null) {
    $response = array("status" => "error", "message" => "eventID, UID, and rating are required in the JSON body");
    echo json_encode($response);
    $conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement"); // Initialize connection even if error
    if (isset($conn)) $conn->close();
    exit();
}

if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
    $response = array("status" => "error", "message" => "rating must be a number between 1 and 5");
    echo json_encode($response);
    $conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement"); // Initialize connection even if error
    if (isset($conn)) $conn->close();
    exit();
}

$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
if ($conn->connect_error) {
    $response = array("status" => "error", "message" => "Database connection failed: " . $conn->connect_error);
    echo json_encode($response);
    exit();
}

$checkStmt = $conn->prepare("SELECT ratingID FROM eventRatings WHERE eventID = ? AND UID = ?");
$checkStmt->bind_param("ii", $eventID, $UID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    $response = array("status" => "error", "message" => "you have already rated this event");
    echo json_encode($response);
    $checkStmt->close();
    $conn->close();
    exit();
}
$checkStmt->close();

$stmt = $conn->prepare("INSERT INTO eventRatings (eventID, UID, rating) VALUES (?, ?, ?)");
$stmt->bind_param("iii", $eventID, $UID, $rating);

if ($stmt->execute()) {
    $response = array("status" => "success", "message" => "rating created successfully");
} else {
    $response = array("status" => "error", "message" => "error submitting rating: " . $stmt->error);
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>