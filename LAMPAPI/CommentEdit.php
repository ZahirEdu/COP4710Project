<?php
header("Content-Type: application/json");

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function returnWithInfo($message)
{
    echo json_encode(["message" => $message,"error" => ""]);
    exit();
}

function returnWithError($err)
{
    echo json_encode(["error" => $err]);
    exit();
}

try {
    $inData = getRequestInfo();
    
    //checking fields
    $requiredFields = ['commentID', 'UID', 'commentText'];
    foreach ($requiredFields as $field) {
        if (empty($inData[$field])) {
            returnWithError("missing required field: $field");
        }
    }

    $conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
    if ($conn->connect_error) {
        returnWithError("database connection failed: " . $conn->connect_error);
    }

    //making sure comment belongs to user
    $stmt = $conn->prepare("SELECT commentID FROM eventComments WHERE commentID = ? AND UID = ?");
    $stmt->bind_param("ii", $inData["commentID"], $inData["UID"]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt->close();
        $conn->close();
        returnWithError("comment not found or you don't have permission to edit it");
    }
    $stmt->close();

    //updaiting comment
    $stmt = $conn->prepare("UPDATE eventComments SET commentText = ?, updatedAtt = CURRENT_TIMESTAMP WHERE commentID = ?");
    $stmt->bind_param("si", $inData["commentText"],$inData["commentID"]);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            returnWithInfo("comment updated successfully");
        } else {
            returnWithError("no changes made");
        }
    } else {
        returnWithError("failed to update comment: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    returnWithError("an error occurred: " . $e->getMessage());
}
?>