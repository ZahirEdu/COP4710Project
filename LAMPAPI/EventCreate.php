<?php
header("Content-Type: application/json");

// Database configuration
$db_host = "localhost";
$db_user = "Zahir";
$db_pass = "k9m2q5i0";
$db_name = "UniversityEventManagement";

function getRequestInfo() {
    return json_decode(file_get_contents('php://input'), true);
}

function returnWithInfo($message, $eventID = null) {
    echo json_encode([
        "message" => $message,
        "eventID" => $eventID,
        "error" => ""
    ]);
    exit();
}

function returnWithError($err) {
    echo json_encode(["error" => $err]);
    exit();
}

try {
    $inData = getRequestInfo();
    
    // Validate required fields
    $requiredFields = [
        'name', 'description', 'category_id', 'start_time', 'end_time', 
        'date', 'location_id', 'contact_phone', 'contact_email', 
        'event_type', 'UID', 'university_id'
    ];
    
    foreach ($requiredFields as $field) {
        if (empty($inData[$field])) {
            returnWithError("Missing required field: $field");
        }
    }

    // Additional RSO validation
    if ($inData['event_type'] === 'rso' && empty($inData['rso_id'])) {
        returnWithError("RSO ID is required for RSO events");
    }

    // Connect to database
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        returnWithError("Database connection failed: " . $conn->connect_error);
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // 1. Check user role and RSO membership if needed
        $userRole = null;
        $isRSOAdmin = false;
        
        $stmt = $conn->prepare("SELECT role FROM users WHERE UID = ?");
        $stmt->bind_param("i", $inData['UID']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $userRole = $row['role'];
        } else {
            throw new Exception("User not found");
        }
        $stmt->close();

        // For RSO events, verify membership and admin status
        if ($inData['event_type'] === 'rso') {
            $stmt = $conn->prepare("SELECT is_admin FROM rso_members WHERE rso_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $inData['rso_id'], $inData['UID']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("You are not a member of this RSO");
            }
            
            $row = $result->fetch_assoc();
            $isRSOAdmin = (bool)$row['is_admin'];
            $stmt->close();
        }

        // 2. Check location exists
        $stmt = $conn->prepare("SELECT location_id FROM locations WHERE location_id = ?");
        $stmt->bind_param("i", $inData['location_id']);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("Specified location does not exist");
        }
        $stmt->close();

        // 3. Check for event time conflicts
        $stmt = $conn->prepare("
            SELECT e.event_id, e.name 
            FROM events e
            JOIN locations l ON e.location_id = l.location_id
            WHERE e.location_id = ?
            AND e.date = ?
            AND (
                (e.start_time < ? AND e.end_time > ?) OR  -- New event starts during existing
                (e.start_time < ? AND e.end_time > ?) OR  -- New event ends during existing
                (e.start_time >= ? AND e.end_time <= ?)   -- New event completely within existing
            )
            AND e.approval_status = 'approved'
        ");
        
        $stmt->bind_param(
            "isssssss", 
            $inData['location_id'],
            $inData['date'],
            $inData['end_time'], $inData['start_time'],
            $inData['end_time'], $inData['start_time'],
            $inData['start_time'], $inData['end_time']
        );
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $conflictingEvent = $result->fetch_assoc();
            throw new Exception("Time conflict with event: " . $conflictingEvent['name']);
        }
        $stmt->close();

        // 4. Determine approval status
        $approvalStatus = 'pending';
        
        if ($inData['event_type'] === 'public') {
            $approvalStatus = 'approved';
        } elseif ($userRole === 'super_admin') {
            $approvalStatus = 'approved';
        } elseif ($inData['event_type'] === 'rso' && $isRSOAdmin) {
            $approvalStatus = 'approved';
        }

        // 5. Create the event
        $stmt = $conn->prepare("
            INSERT INTO events (
                name, description, category_id, start_time, end_time, date,
                location_id, contact_phone, contact_email, event_type,
                university_id, rso_id, created_by, approval_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "ssssssisssiiis",
            $inData['name'],
            $inData['description'],
            $inData['category_id'],
            $inData['start_time'],
            $inData['end_time'],
            $inData['date'],
            $inData['location_id'],
            $inData['contact_phone'],
            $inData['contact_email'],
            $inData['event_type'],
            $inData['university_id'],
            $inData['rso_id'] ?? null,
            $inData['UID'],
            $approvalStatus
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create event: " . $stmt->error);
        }
        
        $eventID = $conn->insert_id;
        $stmt->close();

        // Commit transaction if all steps succeeded
        $conn->commit();
        
        returnWithInfo("Event created successfully", $eventID);

    } catch (Exception $e) {
        // Roll back transaction on any error
        $conn->rollback();
        returnWithError($e->getMessage());
    }

    $conn->close();

} catch (Exception $e) {
    returnWithError("An error occurred: " . $e->getMessage());
}
?>