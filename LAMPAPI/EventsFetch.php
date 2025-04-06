<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");

// Database connection details
$db_host = "localhost";
$db_user = "Zahir";
$db_pass = "k9m2q5i0";
$db_name = "UniversityEventManagement";

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $UID = $input['UID'] ?? null;
    $universityID = $input['universityID'] ?? null;


    if (!$UID) {
        http_response_code(400);
        echo json_encode(["error" => "UID parameter is required in the URL"]);
        exit();
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
                e.approvalStatus
              FROM events e
              LEFT JOIN eventCat c ON e.catID = c.catID
              LEFT JOIN locations l ON e.locationID = l.locationID
              LEFT JOIN universities u ON e.universityID = u.universityID
              LEFT JOIN rsos r ON e.rsoID = r.rsoID
              LEFT JOIN users creator ON e.createdBy = creator.UID
              WHERE e.approvalStatus = 'approved'
              AND (
                  e.eventType = 'public' OR
                  (e.eventType = 'private' AND e.universityID = ?) OR
                  (e.eventType = 'rso' AND e.rsoID IN (
                      SELECT rsoID
                      FROM rsoMembers
                      WHERE UID = ?
                  ))
              )
              ORDER BY e.start_time ASC";

$stmt = $conn->prepare($query);

if (!$stmt) {
    throw new Exception("Error preparing query: " . $conn->error);
}

if ($universityID) {
    $stmt->bind_param("ii", $universityID, $UID); // Likely line 80
} else {
    // If no universityID is provided, bind a placeholder that won't match for private events
    $dummyUniversityID = -1;
    $stmt->bind_param("ii", $dummyUniversityID, $UID); // Corrected line
}

    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        throw new Exception("Error executing query: " . $stmt->error);
    }

    $approvedEvents = [];
    while ($row = $result->fetch_assoc()) {
        $approvedEvents[] = $row;
    }

    echo json_encode(["approved_events" => $approvedEvents]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>