<?php
header("Content-Type: application/json");



function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function returnWithInfo($message, $commentID = null)
{
    echo json_encode(["message" => $message,"commentID" => $commentID,"error" => ""]);
    exit();
}

function returnWithError($err)
{
    echo json_encode(["error" => $err]);
    exit();
}

try {
    $inData = getRequestInfo();
    
    $requiredFields = ['eventID', 'UID', 'commentText'];
    foreach ($requiredFields as $field) {
        if (empty($inData[$field])) {
            returnWithError("missing required field: $field");
        }
    }

    $conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
    if ($conn->connect_error) {
        returnWithError("database connection failed: " . $conn->connect_error);
    }

    //checking to see if event is exsits and is is approved, redundant
    $stmt = $conn->prepare("SELECT eventID FROM events WHERE eventID = ? AND approvalStatus = 'approved'");
    $stmt->bind_param("i", $inData["eventID"]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt->close();
        $conn->close();
        returnWithError("rvent not found or not approved");
    }
    $stmt->close();

    //check to see if user exsits
    $stmt = $conn->prepare("SELECT UID FROM users WHERE UID = ?");
    $stmt->bind_param("i", $inData["UID"]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt->close();
        $conn->close();
        returnWithError("user not found");
    }
    $stmt->close();

    //inserting new comment 
    $stmt = $conn->prepare("INSERT INTO eventComments (eventID, UID, commentText) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $inData["eventID"],$inData["UID"],$inData["commentText"]);

    if ($stmt->execute()) {
        $commentID = $conn->insert_id;
        returnWithInfo("vomment created successfully", $commentID);
    } else {
        returnWithError("gailed to create comment: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    returnWithError("an error occurred: " . $e->getMessage());
}
?>