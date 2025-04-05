<?php
header("Content-Type: application/json");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function returnWithInfo($id, $name, $email, $role, $universityID)
{
    $retValue = [
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'role' => $role,
        'universityID' => $universityID,
        'error' => ''
    ];
    sendResultInfoAsJson($retValue);
}

function returnWithError($err)
{
    $retValue = ['error' => $err];
    sendResultInfoAsJson($retValue);
}

function sendResultInfoAsJson($obj)
{
    echo json_encode($obj);
}

try {
    $inData = getRequestInfo();
    
    if (!isset($inData['email']) || !isset($inData['password'])) {
        returnWithError("Email and password required");
        exit();
    }

    $conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
    
    if ($conn->connect_error) {
        returnWithError("Database connection error: " . $conn->connect_error);
        exit();
    }

    // Prepare statement with password verification
    $stmt = $conn->prepare("SELECT UID, name, email, role, universityID, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $inData["email"]);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verify password (assuming passwords are hashed in database)
        if (password_verify($inData['password'], $row['password'])) {
            returnWithInfo($row['UID'], $row['name'], $row['email'], $row['role'], $row['universityID']);
        } else {
            returnWithError("Invalid credentials");
        }
    } else {
        returnWithError("User not found");
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    returnWithError("An error occurred: " . $e->getMessage());
}
?>