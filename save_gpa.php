<?php
// --- 1. The Bouncer & Data Check ---
session_start();

// We must check if the user is logged in AND if we received the data
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    sendResponse('error', 'You are not logged in.');
}
if (!isset($_POST['new_gpa'], $_POST['new_credits'])) {
    sendResponse('error', 'Missing data to save.');
}

// --- 2. Database Connection ---
$server_name = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "login_project";

$conn = new mysqli($server_name, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    sendResponse('error', 'Connection failed.');
}

// --- 3. The Secure UPDATE Query ---
$new_gpa = $_POST['new_gpa'];
$new_credits = $_POST['new_credits'];
$user_id = $_SESSION['id']; // Get the user's ID from their session

// This is the SQL command to UPDATE an existing row
$sql = "UPDATE users SET current_cgpa = ?, credits_completed = ? WHERE id = ?";

$stmt = $conn->prepare($sql);

// We bind THREE parameters: a decimal (d), an integer (i), and an integer (i)
$stmt->bind_param("dii", $new_gpa, $new_credits, $user_id);

if ($stmt->execute()) {
    sendResponse('success', 'Your new GPA has been saved!');
} else {
    sendResponse('error', 'Could not save your data.');
}

$stmt->close();
$conn->close();

// --- 4. Our JSON "Memo" Helper Function ---
function sendResponse($status, $message) {
    $response = ['status' => $status, 'message' => $message];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>