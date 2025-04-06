<?php
header("Content-Type: application/json");

// Get raw JSON input from Postman
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check if UID is provided
if (!isset($data['UID'])) {
    http_response_code(400);
    echo json_encode(["error" => "UID is required"]);
    exit;
}

$UID = $data['UID'];

function isSuperAdmin($conn, $UID) {
    $stmt = $conn->prepare("SELECT role FROM users WHERE UID = ?");
    $stmt->bind_param("i", $UID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['role'] === 'superadmin';
    }
    return false;
}

try {
    $conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
    if ($conn->connect_error) {
        throw new Exception("database connection failed");
    }

    // Check if user is super admin
    if (!isSuperAdmin($conn, $UID)) {
        http_response_code(403);
        throw new Exception("unauthorized");
    }

    $query = "SELECT 
                e.eventID,
                e.name,
                e.description,
                e.catID,
                c.name as categoryName,
                e.start_time,
                e.end_time,
                e.locationID,
                l.name as locationName,
                e.contactPhone,
                e.contactEmail,
                e.eventType,
                e.universityID,
                u.name as universityName,
                e.rsoID,
                r.name as rsoName,
                e.createdBy,
                creator.name as creatorUsername,
                e.approvalStatus,
                e.approvedBy
              FROM events e
              LEFT JOIN eventCat c ON e.catID = c.catID
              LEFT JOIN locations l ON e.locationID = l.locationID
              LEFT JOIN universities u ON e.universityID = u.universityID
              LEFT JOIN rsos r ON e.rsoID = r.rsoID
              LEFT JOIN users creator ON e.createdBy = creator.UID
              WHERE e.approvalStatus = 'pending'
              ORDER BY e.start_time ASC";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("query failed: " . $conn->error);
    }

    $pendingEvents = [];
    while ($row = $result->fetch_assoc()) {
        $pendingEvents[] = $row;
    }

    echo json_encode(["pending_events" => $pendingEvents]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>