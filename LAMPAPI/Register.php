<?php
header("Content-Type: application/json");

error_reporting(E_ALL);
ini_set('display_errors', 1);

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function returnWithInfo($message)
{
    echo json_encode(["message" => $message, "error" => ""]);
    exit();
}

function returnWithError($err)
{
    echo json_encode(["error" => $err]);
    exit();
}

try {
    $inData = getRequestInfo();


    if ($inData === null) {
        returnWithError("Invalid JSON input");
    }

    $required = ['email', 'password', 'name', 'role', 'universityID'];
    foreach ($required as $field) {
        if (empty($inData[$field])) {
            returnWithError("Missing required field: $field");
        }
    }

    $conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
    if ($conn->connect_error) {
        returnWithError("Database connection failed: " . $conn->connect_error);
    }

    $check = $conn->prepare("SELECT email FROM users WHERE email = ?");
    if (!$check) {
        returnWithError("Prepare failed: " . $conn->error);
    }
    
    $check->bind_param("s", $inData["email"]);
    if (!$check->execute()) {
        returnWithError("Execute failed: " . $check->error);
    }
    $check->store_result();
    
    if ($check->num_rows > 0) {
        $check->close();
        returnWithError("Email already registered");
    }
    $check->close();

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, universityID) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        returnWithError("Prepare failed: " . $conn->error);
    }
    
    $bindResult = $stmt->bind_param("ssssi", 
        $inData["name"],
        $inData["email"],
        $inData["password"], 
        $inData["role"],
        $inData["universityID"]
    );
    
    if (!$bindResult) {
        returnWithError("Bind failed: " . $stmt->error);
    }

    if ($stmt->execute()) {
        returnWithInfo("User created successfully");
    } else {
        returnWithError("Registration failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    returnWithError("An error occurred: " . $e->getMessage());
}
?>