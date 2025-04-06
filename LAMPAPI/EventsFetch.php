<?php
header("Content-Type: application/json");

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$UID = $input['UID'] ?? null;
$universityID = $input['universityID'] ?? null;

// Validate input
if (!$UID) {
    http_response_code(400);
    echo json_encode(["error" => "UID is required"]);
    exit();
}

// Connect to database
$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

try {
    // Prepare the base query with all requested fields
    $query = "SELECT 
                e.event_id as eventID,
                e.name,
                e.description,
                e.category_id as catID,
                e.start_time,
                e.end_time,
                e.location_id as locationID,
                e.contact_phone as contactPhone,
                e.contact_email as contactEmail,
                e.event_type as eventType,
                e.university_id as universityID,
                e.rso_id as rsoID,
                e.approval_status as approvedStatus
              FROM events e 
              WHERE e.approval_status = 'approved' AND (";
    
    // Public events condition
    $query .= "(e.event_type = 'public') ";
    
    // Add private events condition if universityID is provided
    if ($universityID) {
        $query .= "OR (e.event_type = 'private' AND e.university_id = ?) ";
    }
    
    // Add RSO events condition
    $query .= "OR (e.event_type = 'rso' AND e.rso_id IN (
                SELECT rso_id FROM rso_members 
                WHERE user_id = ?
              ))";
    
    // Close the WHERE clause
    $query .= ")";
    
    // Prepare and bind parameters
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
        // Format datetime fields if needed
        $row['start_time'] = date('H:i:s', strtotime($row['start_time']));
        $row['end_time'] = date('H:i:s', strtotime($row['end_time']));
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