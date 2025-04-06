<?php

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);


if ($conn->connect_error) {
    die("connection failed: " . $conn->connect_error);
}


$locationID = $_GET['locationID'] ?? null;

if ($locationID === null || !is_numeric($locationID)) {
    $response = array("status" => "error", "message" => "invalid or missing locationID");
    header('Content-Type: application/json');
    echo json_encode($response);
    $conn->close();
    exit();
}


$stmt = $conn->prepare("SELECT locationID, name, lat, lon, address, place_id, room FROM locations WHERE locationID = ?");
$stmt->bind_param("i", $locationID); 

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $location = $result->fetch_assoc();
    $response = array("status" => "success", "location" => $location);
} else {
    $response = array("status" => "error", "message" => "location not found");
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>