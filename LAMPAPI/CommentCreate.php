<?php
header("Content-Type: application/json");



function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function returnWithInfo($message, $commentID = null)
{
    echo json_encode([
        "message" => $message,
        "commentID" => $commentID,
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
    
    // Validate required fields
    $requiredFields = ['eventID', 'UID', 'commentText'];
    foreach ($requiredFields as $field) {
        if (empty($inData[$field])) {
            returnWithError("Missing required field: $field");
        }
    }

    // Connect to database
    $conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
    if ($conn->connect_error) {
        returnWithError("Database connection failed: " . $conn->connect_error);
    }

    // First verify the event exists and is approved
    $stmt = $conn->prepare("SELECT eventID FROM events WHERE eventID = ? AND approval_status = 'approved'");
    $stmt->bind_param("i", $inData["eventID"]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt->close();
        $conn->close();
        returnWithError("Event not found or not approved");
    }
    $stmt->close();

    // Verify user exists
    $stmt = $conn->prepare("SELECT UID FROM users WHERE UID = ?");
    $stmt->bind_param("i", $inData["UID"]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt->close();
        $conn->close();
        returnWithError("User not found");
    }
    $stmt->close();

    // Insert new comment
    $stmt = $conn->prepare("INSERT INTO eventComments (eventID, UID, commentText) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", 
        $inData["eventID"],
        $inData["UID"],
        $inData["commentText"]
    );

    if ($stmt->execute()) {
        $commentID = $conn->insert_id;
        returnWithInfo("Comment created successfully", $commentID);
    } else {
        returnWithError("Failed to create comment: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    returnWithError("An error occurred: " . $e->getMessage());
}
?>