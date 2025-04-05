<?php
header("Content-Type: application/json");

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function returnWithInfo($UID, $name, $email, $role, $universityID)
{
    echo json_encode([
        'id' => $UID,
        'name' => $name,
        'email' => $email,
        'role' => $role,
        'universityID' => $universityID,
        'error' => ''
    ]);
    exit();
}

function returnWithError($err)
{
    echo json_encode(['error' => $err]);
    exit();
}

$inData = getRequestInfo();

$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");

if ($conn->connect_error) {
    returnWithError("Database connection failed");
}

$stmt = $conn->prepare("SELECT UID, name, email, role, universityID FROM users WHERE email = ? AND password = ?");
$stmt->bind_param("ss", $inData["email"], $inData["password"]);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    returnWithInfo($row['UID'], $row['name'], $row['email'], $row['role'], $row['universityID']);
} else {
    returnWithError("Invalid credentials");
}

$stmt->close();
$conn->close();
?>