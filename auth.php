<?php
// We must start the session to remember the user
session_start();

// --- 1. Database Connection ---
$server_name = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "login_project"; // Use the SAME database as V1

$conn = new mysqli($server_name, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    // We send a JSON error memo
    sendResponse('error', 'Connection failed: ' . $conn->connect_error);
}

// --- 2. The Main "Router" ---
// We check the 'action' field from our form
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'login') {
        handleLogin();
    } elseif ($_POST['action'] == 'register') {
        handleRegister();
    }
}

// --- Helper function to send our JSON "memo" ---
function sendResponse($status, $message, $redirect = null) {
    $response = ['status' => $status, 'message' => $message];
    if ($redirect) {
        $response['redirect'] = $redirect; // Add redirect URL if login is successful
    }
    header('Content-Type: application/json'); // Tell the browser this is JSON
    echo json_encode($response); // Echo the memo and stop the script
    exit;
}

// --- 3. Login Logic Function ---
function handleLogin() {
    global $conn; // Get the connection from the global scope

    if (!isset($_POST['username'], $_POST['password'])) {
        sendResponse('error', 'Please fill out all fields.');
    }

    $sql = "SELECT id, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($_POST['password'], $user['password'])) {
            // SUCCESS!
            session_regenerate_id();
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['name'] = $_POST['username'];
            $_SESSION['id'] = $user['id'];
            
            // Send the success memo, telling JS to redirect
            sendResponse('success', 'Login successful! Redirecting...', 'welcome.php');
        } else {
            sendResponse('error', 'Incorrect username or password!');
        }
    } else {
        sendResponse('error', 'Incorrect username or password!');
    }
    $stmt->close();
}

// --- 4. Register Logic Function ---
function handleRegister() {
    global $conn;

    if (!isset($_POST['username'], $_POST['password'], $_POST['confirm_password'])) {
        sendResponse('error', 'Please fill out all fields.');
    }

    // Check if passwords match
    if ($_POST['password'] != $_POST['confirm_password']) {
        sendResponse('error', 'Passwords do not match!');
    }

    // Check if username is already taken
    $sql_check = "SELECT id FROM users WHERE username = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $_POST['username']);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        sendResponse('error', 'Username already taken!');
    }
    $stmt_check->close();

    // Hash the password
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert the new user
    $sql_insert = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ss", $_POST['username'], $password_hash);

    if ($stmt_insert->execute()) {
        sendResponse('success', 'Registration successful! Please login.');
    } else {
        sendResponse('error', 'Registration failed. Please try again.');
    }
    $stmt_insert->close();
}

$conn->close();
?>