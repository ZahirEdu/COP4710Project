<?php
$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$eventID = $_GET['eventID'] ?? null;

if ($eventID === null || !is_numeric($eventID)) {
    $response = array("status" => "error", "message" => "invalid or missing eventID.");
    header('Content-Type: application/json');
    echo json_encode($response);
    $conn->close();
    exit();
}

$stmt = $conn->prepare("SELECT ratingID, UID, rating FROM eventRatings WHERE eventID = ?");
$stmt->bind_param("i", $eventID); 

$stmt->execute();

$result = $stmt->get_result();

$ratings = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ratings[] = $row;
    }
    $response = array("status" => "success", "ratings" => $ratings);
} else {
    $response = array("status" => "info", "message" => "no ratings found");
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>