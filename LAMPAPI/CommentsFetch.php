<?php
header("Content-Type: application/json");

// Database configuration
$db_host = "localhost";
$db_user = "Zahir";
$db_pass = "k9m2q5i0";
$db_name = "UniversityEventManagement";

// Get the event ID from request
$input = json_decode(file_get_contents('php://input'), true);
$eventID = $input['eventID'] ?? null;

// Validate input
if (!$eventID) {
    http_response_code(400);
    echo json_encode(["error" => "eventID is required"]);
    exit();
}

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

try {
    // Prepare query to get comments with user information
    $query = "SELECT 
                c.commentID,
                c.eventID,
                c.UID,
                u.username,
                u.first_name,
                u.last_name,
                c.commentText,
                c.createdAt,
                c.updatedAtt
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
        // Format timestamps
        $row['createdAt'] = date('Y-m-d H:i:s', strtotime($row['createdAt']));
        $row['updatedAtt'] = date('Y-m-d H:i:s', strtotime($row['updatedAtt']));
        
        // Add user display name
        $row['userDisplayName'] = $row['first_name'] . ' ' . $row['last_name'];
        
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