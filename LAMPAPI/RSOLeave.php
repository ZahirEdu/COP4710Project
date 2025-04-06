<?php
$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$rsoID = $_POST['rsoID'] ?? null;
$UID = $_POST['UID'] ?? null;


if ($rsoID === null || $UID === null) {
    $response = array("status" => "error", "message" => "rsoID and UID are required.");
    echo json_encode($response);
    $conn->close();
    exit();
}

if (!is_numeric($rsoID) || !is_numeric($UID)) {
    $response = array("status" => "error", "message" => "rsoID and UID must be numeric.");
    echo json_encode($response);
    $conn->close();
    exit();
}

$checkStmt = $conn->prepare("SELECT rsoMemID FROM rsoMembers WHERE rsoID = ? AND UID = ?");
$checkStmt->bind_param("ii", $rsoID, $UID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    $response = array("status" => "error", "message" => "User is not a member of this RSO.");
    echo json_encode($response);
    $checkStmt->close();
    $conn->close();
    exit();
}
$checkStmt->close();

$deleteStmt = $conn->prepare("DELETE FROM rsoMembers WHERE rsoID = ? AND UID = ?");
$deleteStmt->bind_param("ii", $rsoID, $UID);

if ($deleteStmt->execute()) {

    $countStmt = $conn->prepare("SELECT COUNT(UID) AS memberCount FROM rsoMembers WHERE rsoID = ?");
    $countStmt->bind_param("i", $rsoID);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $row = $countResult->fetch_assoc();
    $memberCount = $row['memberCount'];
    $countStmt->close();

    if ($memberCount < 5) {
        $updateStmt = $conn->prepare("UPDATE rsos SET status = 'inactive' WHERE rsoID = ?");
        $updateStmt->bind_param("i", $rsoID);
        $updateStmt->execute();
        $updateStmt->close();
        $response = array("status" => "success", "message" => "Successfully left RSO. RSO status updated to inactive.");
    } else {
        $response = array("status" => "success", "message" => "Successfully left RSO.");
    }
} else {
    $response = array("status" => "error", "message" => "Error leaving RSO: " . $deleteStmt->error);
}

$deleteStmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>