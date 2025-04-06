<?php
$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $_POST['name'] ?? null;
$description = $_POST['description'] ?? null;
$universityID = $_POST['universityID'] ?? null;
$adminID = $_POST['adminID'] ?? null; /
$status = $_POST['status'] ?? null; 

if (empty($name) || $universityID === null || $adminID === null) {
    $response = array("status" => "error", "message" => "Name, universityID, and adminID are required");
    echo json_encode($response);
    $conn->close();
    exit();
}

if (!is_numeric($universityID) || !is_numeric($adminID)) {
    $response = array("status" => "error", "message" => "universityID and adminID must be numeric");
    echo json_encode($response);
    $conn->close();
    exit();
}

$stmt = $conn->prepare("INSERT INTO rsos (name, description, universityID, adminID, status) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssiis", $name, $description, $universityID, $adminID, $status);

if ($stmt->execute()) {
    $response = array("status" => "success", "message" => "rso created successfully ", "rsoID" => $conn->insert_id);
} else {
    $response = array("status" => "error", "message" => "error creating RSO: " . $stmt->error);
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>