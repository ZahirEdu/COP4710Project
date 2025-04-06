<?php
$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$eventID = $_POST['eventID'] ?? null;
$UID = $_POST['UID'] ?? null;
$rating = $_POST['rating'] ?? null;

if ($eventID === null || $UID === null || $rating === null) {
    $response = array("status" => "error", "message" => "eventID, UID, and rating are required");
    echo json_encode($response);
    $conn->close();
    exit();
}

if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
    $response = array("status" => "error", "message" => "rating must be a number between 1 and 5");
    echo json_encode($response);
    $conn->close();
    exit();
}

$checkStmt = $conn->prepare("SELECT ratingID FROM eventRatings WHERE eventID = ? AND UID = ?");
$checkStmt->bind_param("ii", $eventID, $UID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    $response = array("status" => "error", "message" => "you already made a rating");
    echo json_encode($response);
    $checkStmt->close();
    $conn->close();
    exit();
}
$checkStmt->close();

$stmt = $conn->prepare("INSERT INTO eventRatings (eventID, UID, rating) VALUES (?, ?, ?)");
$stmt->bind_param("iii", $eventID, $UID, $rating);

if ($stmt->execute()) {
    $response = array("status" => "success", "message" => "raiting created successfully");
} else {
    $response = array("status" => "error", "message" => "error submitting rating: " . $stmt->error);
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>