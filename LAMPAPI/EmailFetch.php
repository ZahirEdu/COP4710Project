<?php
header('Content-Type: application/json');


$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
if ($conn->connect_error) {
    die(json_encode([
        'status' => 'error', 
        'message' => 'Connection failed: ' . $conn->connect_error
    ]));
}


$email = $_GET['email'] ?? null;

if (empty($email)) {
    http_response_code(400); 
    echo json_encode([
        'status' => 'error',
        'message' => 'Email parameter is required'
    ]);
    $conn->close();
    exit();
}


$email = filter_var($email, FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); 
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email format'
    ]);
    $conn->close();
    exit();
}


$stmt = $conn->prepare("SELECT UID FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404); 
    echo json_encode([
        'status' => 'error',
        'message' => 'User not found'
    ]);
} else {
    $user = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'UID' => $user['UID']
    ]);
}

$stmt->close();
$conn->close();
?>