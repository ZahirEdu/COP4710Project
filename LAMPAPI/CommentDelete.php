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
    
    //checking if fields are present
    if (empty($inData["commentID"])) {
        returnWithError("missing required field: commentID");
    }
    if (empty($inData["UID"])) {
        returnWithError("missing required field: UID");
    }

    $conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
    if ($conn->connect_error) {
        returnWithError("database connection failed: " . $conn->connect_error);
    }

    //checking that comment exits and belongs to user
    $stmt = $conn->prepare("SELECT commentID FROM eventComments WHERE commentID = ? AND UID = ?");
    $stmt->bind_param("ii", $inData["commentID"], $inData["UID"]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt->close();
        $conn->close();
        returnWithError("comment not found or unauthorized");
    }
    $stmt->close();

    //delete comment
    $stmt = $conn->prepare("DELETE FROM eventComments WHERE commentID = ? AND UID = ?");
    $stmt->bind_param("ii", $inData["commentID"], $inData["UID"]);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            returnWithInfo("comment deleted successfully");
        } else {
            returnWithError("no comment was deleted");
        }
    } else {
        returnWithError("failed to delete comment: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    returnWithError("An error occurred: " . $e->getMessage());
}
?>