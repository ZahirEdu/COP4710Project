<?php
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("connection failed: " . $conn->connect_error);
}

$address = $_GET['address'] ?? null;

if (empty($address)) {
    $response = array("status" => "error", "message" => "address is missing");
    header('Content-Type: application/json');
    echo json_encode($response);
    $conn->close();
    exit();
}

$stmt = $conn->prepare("SELECT locationID, name, lat, lon, address, place_id, room FROM locations WHERE address = ?");
$stmt->bind_param("s", $address); 

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $locations = $result->fetch_all(MYSQLI_ASSOC);
    $response = array("status" => "success", "locations" => $locations);
} else {
    $response = array("status" => "error", "message" => "no location found");
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>