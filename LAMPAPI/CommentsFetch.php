<?php
header("Content-Type: application/json");

//get eventID
$input = json_decode(file_get_contents('php://input'), true);
$eventID = $input['eventID'] ?? null;

//check event ID
if (!$eventID) {
    http_response_code(400);
    echo json_encode(["error" => "eventID is required"]);
    exit();
}

$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

try {
    $query = "SELECT c.commentID, c.eventID, c.UID, c.commentText, c.createdAt, c.updatedAtt
              FROM eventComments c
              JOIN users u ON c.UID = u.UID
              WHERE c.eventID = ?
              ORDER BY c.createdAt DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $eventID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $row['createdAt'] = $row['createdAt']; 
        $row['updatedAtt'] = $row['updatedAtt']; 

        $comments[] = $row;
    }
    
    echo json_encode(["comments" => $comments]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    $conn->close();
}
?>