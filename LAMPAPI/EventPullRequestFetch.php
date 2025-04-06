<?php
header("Content-Type: application/json");

session_start();
$UID = $_SESSION['UID'] ?? null; 


function isSuperAdmin($conn, $UID) {
    $stmt = $conn->prepare("SELECT role FROM users WHERE UID = ?");
    $stmt->bind_param("i", $UID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['role'] === 'superAdmin';
    }
    return false;
}

try {

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        throw new Exception("database connection failed");
    }

    //check if user is super admin
    if (!$UID || !isSuperAdmin($conn, $UID)) {
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
                creator.username as creatorUsername,
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

        $row['createdAt'] = date('m/d/Y', strtotime($row['createdAt']));
        $row['updatedAtt'] = date('m/d/Y', strtotime($row['updatedAtt']));
        
        $pendingEvents[] = $row;
    }

    echo json_encode(["pending events" => $pendingEvents]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>