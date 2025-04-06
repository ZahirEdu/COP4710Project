<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


header("Content-Type: application/json");

function getRequestInfo() {
    return json_decode(file_get_contents('php://input'), true);
}

function returnWithInfo($message, $eventID = null) {
    echo json_encode(["message" => $message,"eventID" => $eventID,"error" => ""]);
    exit();
}

function returnWithError($err) {
    echo json_encode(["error" => $err]);
    exit();
}

// Database connection details (replace with your actual credentials)
$db_host = "localhost";
$db_user = "Zahir";
$db_pass = "k9m2q5i0";
$db_name = "UniversityEventManagement";

try {
    $inData = getRequestInfo();

    //checking all fields
    $requiredFields = ['name', 'description', 'catID', 'start_time', 'end_time',
                             'locationID', 'contactPhone', 'contactEmail',
                             'eventType', 'UID', 'universityID'];

    foreach ($requiredFields as $field) {
        if (empty($inData[$field])) {
            returnWithError("missing required field: $field");
        }
    }

    //check rso
    if ($inData['eventType'] === 'rso' && empty($inData['rsoID'])) {
        returnWithError("RSO ID is required for RSO events");
    }

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        returnWithError("Database connection failed: " . $conn->connect_error);
    }

    $conn->begin_transaction();

    try {
        //check user role and if they are in rso
        $userRole = null;
        $isRSOAdmin = false;

        $stmt = $conn->prepare("SELECT role FROM users WHERE UID = ?");
        $stmt->bind_param("i", $inData['UID']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $userRole = $row['role'];
        } else {
            throw new Exception("user not found");
        }
        $stmt->close();

        //rso events, check membership status and admin
        //rso events, check membership status and admin
        if ($inData['eventType'] === 'rso') {
            $stmt = $conn->prepare("SELECT rsoID FROM rsoMembers WHERE rsoID = ? AND UID = ?");
            $stmt->bind_param("ii", $inData['rsoID'], $inData['UID']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("not a member of rso");
            }
            $result->free();
            $stmt->close();

            // Check if the user is the admin of the RSO
            $stmt = $conn->prepare("SELECT adminID FROM rsos WHERE rsoID = ? AND adminID = ?");
            $stmt->bind_param("ii", $inData['rsoID'], $inData['UID']);
            $stmt->execute();
            $result = $stmt->get_result();

            $isRSOAdmin = ($result->num_rows > 0);
            $result->free();
            $stmt->close();
        }

        //check if location exists
        $stmt = $conn->prepare("SELECT locationID FROM locations WHERE locationID = ?");
        $stmt->bind_param("i", $inData['locationID']);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {
            throw new Exception("location does not exists");
        }
        $stmt->close();

        //check time conflicts
        $stmt = $conn->prepare("
            SELECT e.eventID, e.name
            FROM events e
            JOIN locations l ON e.locationID = l.locationID
            WHERE e.locationID = ?
            AND (
                (e.start_time < ? AND e.end_time > ?) OR
                (e.start_time < ? AND e.end_time > ?) OR
                (e.start_time >= ? AND e.end_time <= ?)
            )
            AND e.approvalStatus = 'approved'
        ");

        $stmt->bind_param(
            "issssss",
            $inData['locationID'],
            $inData['end_time'], $inData['start_time'],
            $inData['end_time'], $inData['start_time'],
            $inData['start_time'], $inData['end_time']
        );

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $conflictingEvent = $result->fetch_assoc();
            throw new Exception("time conflict with event: " . $conflictingEvent['name']);
        }
        $stmt->close();

        //check approval status
        $approvalStatus = 'pending';

        if ($inData['eventType'] === 'public') {
            $approvalStatus = 'approved';
        } elseif ($userRole === 'superAdmin') {
            $approvalStatus = 'approved';
        } elseif ($inData['eventType'] === 'rso' && $isRSOAdmin) {
            $approvalStatus = 'approved';
        }

        //create event
        $stmt = $conn->prepare("
            INSERT INTO events (
                name, description, catID, start_time, end_time,
                locationID, contactPhone, contactEmail, eventType,
                universityID, rsoID, createdBy, approvalStatus
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $rsoIDValue = $inData['rsoID'] ?? null;

        $stmt->bind_param(
            "ssssssisssiis",
            $inData['name'],
            $inData['description'],
            $inData['catID'],
            $inData['start_time'],
            $inData['end_time'],
            $inData['locationID'],
            $inData['contactPhone'],
            $inData['contactEmail'],
            $inData['eventType'],
            $inData['universityID'],
            $rsoIDValue,
            $inData['UID'],
            $approvalStatus
        );

        if (!$stmt->execute()) {
            throw new Exception("failed to create event: " . $stmt->error);
        }

        $eventID = $conn->insert_id;
        $stmt->close();

        $conn->commit();

        returnWithInfo("event created successfully", $eventID);

    } catch (Exception $e) {
        $conn->rollback();
        returnWithError($e->getMessage());
    }

    $conn->close();

} catch (Exception $e) {
    returnWithError("an error occurred: " . $e->getMessage());
}
?>