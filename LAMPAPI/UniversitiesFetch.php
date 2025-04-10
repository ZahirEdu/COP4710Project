<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed", "success" => false]); /
    exit();
}

try {

    $sql = "SELECT universityID, name FROM universities";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $universities = [];
    while ($row = $result->fetch_assoc()) {
        $universities[] = [
            'universityID' => $row['universityID'],
            'name' => $row['name']
        ];
    }

    echo json_encode(["success" => true, "universities" => $universities]); 

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage(), "success" => false]); 
}

$conn->close();
?>