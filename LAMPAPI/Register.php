<?php
header("Content-Type: application/json");


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
    $check->bind_param("s", $inData["email"]);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        $check->close();
        returnWithError("Email already registered");
    }
    $check->close();

    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, name, role, universityID) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", 
        $inData["name"],
        $inData["email"],
        $inData["password"], 
        $inData["role"],
        $inData["universityID"]
    );

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