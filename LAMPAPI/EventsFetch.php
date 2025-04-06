<?php
header("Content-Type: application/json");

$input = json_decode(file_get_contents('php://input'), true);
$UID = $input['UID'] ?? null;
$universityID = $input['universityID'] ?? null;


if (!$UID) {
    http_response_code(400);
    echo json_encode(["error" => "UID is required"]);
    exit();
}

e
$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

try {
    $query = "SELECT 
                e.eventID
                e.name,
                e.description,
                e.catID,
                e.start_time,
                e.end_time,
                e.locationID,
                e.contactPhone,
                e.contactEmail,
                e.eventType,
                e.universityID,
                e.rsoID,
                e.approvedStatus
              FROM events e 
              WHERE e.approvalStatus = 'approved' AND (";
    
    $query .= "(e.eventType = 'public') ";
    

    if ($universityID) {
        $query .= "OR (e.eventType = 'private' AND e.universityID = ?) ";
    }
    
    $query .= "OR (e.eventType = 'rso' AND e.rsoID IN (
                SELECT rsoID FROM rsoMembers 
                WHERE userID = ?
              ))";
    

    $query .= ")";
    
    $stmt = $conn->prepare($query);
    
    if ($universityID) {
        $stmt->bind_param("ii", $universityID, $UID);
    } else {
        $stmt->bind_param("i", $UID);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $row['createdAt'] = date('m/d/Y', strtotime($row['createdAt']));
        $row['updatedAtt'] = date('m/d/Y', strtotime($row['updatedAtt']));
        $events[] = $row;
    }
    
    echo json_encode(["events" => $events]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    $conn->close();
}
?>