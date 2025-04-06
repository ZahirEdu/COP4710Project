<?php
header("Content-Type: application/json");

function returnWithInfo($message, $UID = null, $universityID = null)
{
    echo json_encode([
        "message" => $message,
        "UID" => $UID,
        "universityId" => $universityID,
        "error" => ""
    ]);
    exit();
}

function returnWithError($err)
{
    echo json_encode(["error" => $err]);
    exit();
}

try {
    $inData = getRequestInfo();
    

    $requiredUserFields = ['name', 'email', 'password'];
    $requiredUniFields = ['uniName', 'description', 'studentCount'];
    
    foreach ($requiredUserFields as $field) {
        if (empty($inData[$field])) {
            returnWithError("Missing required user field: $field");
        }
    }
    
    foreach ($requiredUniFields as $field) {
        if (empty($inData[$field])) {
            returnWithError("Missing required university field: $field");
        }
    }

  
    $conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
    if ($conn->connect_error) {
        returnWithError("Database connection failed: " . $conn->connect_error);
    }


    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'superAdmin')");
        $stmt->bind_param("sss", 
            $inData["name"],
            $inData["email"],
            $inData["password"], 
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create user: " . $stmt->error);
        }
        
        $UID = $conn->insert_id;
        $stmt->close();

        $locationName = $inData["uniName"];
        $stmt = $conn->prepare("INSERT INTO locations (name, latitude, longitude) VALUES (?, 0, 0)");
        $stmt->bind_param("s", $locationName);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create location: " . $stmt->error);
        }
        
        $locationID = $conn->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO universities (name, locationID, description, studentCount, superAdminID) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii",
            $inData["uniName"],
            $locationID, 
            $inData["description"],
            $inData["student_count"],
            $UID 
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create university: " . $stmt->error);
        }
        
        $universityID = $conn->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("UPDATE users SET universityID = ? WHERE UID = ?");
        $stmt->bind_param("ii", $universityID, $UID);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update user with university ID: " . $stmt->error);
        }
        
        $stmt->close();

        $conn->commit();
        
        returnWithInfo("University and super admin created successfully", $UID, $universityID);

    } catch (Exception $e) {
        $conn->rollback();
        returnWithError($e->getMessage());
    }

    $conn->close();

} catch (Exception $e) {
    returnWithError("An error occurred: " . $e->getMessage());
}

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}
?>