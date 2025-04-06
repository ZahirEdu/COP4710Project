<?php
header("Content-Type: application/json");


function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function returnWithInfo($message)
{
    echo json_encode([
        "message" => $message,
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
    if (empty($inData["commentID"])) {
        returnWithError("Missing required field: commentID");
    }
    if (empty($inData["UID"])) {
        returnWithError("Missing required field: UID");
    }

    // Connect to database
    $conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
    if ($conn->connect_error) {
        returnWithError("Database connection failed: " . $conn->connect_error);
    }

    // Verify the comment exists and belongs to this user
    $stmt = $conn->prepare("SELECT commentID FROM eventComments WHERE commentID = ? AND UID = ?");
    $stmt->bind_param("ii", $inData["commentID"], $inData["UID"]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt->close();
        $conn->close();
        returnWithError("Comment not found or you don't have permission to delete it");
    }
    $stmt->close();

    // Delete the comment
    $stmt = $conn->prepare("DELETE FROM eventComments WHERE commentID = ? AND UID = ?");
    $stmt->bind_param("ii", $inData["commentID"], $inData["UID"]);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            returnWithInfo("Comment deleted successfully");
        } else {
            returnWithError("No comment was deleted");
        }
    } else {
        returnWithError("Failed to delete comment: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    returnWithError("An error occurred: " . $e->getMessage());
}
?>