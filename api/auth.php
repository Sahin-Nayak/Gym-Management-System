<?php
require_once __DIR__ . '/config.php';

function loginUser($username, $password) {
    global $conn;
    $username = sanitize($username);
    
    $stmt = $conn->prepare("SELECT id, username, email, password, role, is_active FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Account is deactivated. Contact admin.'];
        }
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            return ['success' => true, 'role' => $user['role']];
        }
    }
    
    return ['success' => false, 'message' => 'Invalid username or password.'];
}

function registerUser($username, $email, $password, $role = 'member') {
    global $conn;
    
    // Check if username or email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'Username or email already exists.'];
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);
    
    if ($stmt->execute()) {
        return ['success' => true, 'user_id' => $stmt->insert_id];
    }
    
    return ['success' => false, 'message' => 'Registration failed. Try again.'];
}

function changePassword($userId, $currentPassword, $newPassword) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($currentPassword, $user['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $userId);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Password changed successfully.'];
            }
        } else {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }
    }
    
    return ['success' => false, 'message' => 'Failed to change password.'];
}

// Step 1 of forgot-password: verify email + username both belong to same account
function verifyIdentity($email, $username) {
    global $conn;
    $email    = sanitize($email);
    $username = sanitize($username);

    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ? AND username = ? AND is_active = 1");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        return ['success' => true, 'user_id' => $user['id']];
    }

    return ['success' => false, 'message' => 'No account found with that email and username combination.'];
}

// Step 2 of forgot-password: set new password using verified user_id stored in session
function resetPasswordDirect($userId, $newPassword) {
    global $conn;
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt   = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
    $stmt->bind_param("si", $hashed, $userId);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        return ['success' => true];
    }
    return ['success' => false, 'message' => 'Failed to update password. Try again.'];
}

function logout() {
    session_destroy();
    redirect('home.php');
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('index.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('index.php');
    }
}

function requireTrainer() {
    requireLogin();
    if (!isTrainer() && !isAdmin()) {
        redirect('index.php');
    }
}
?>
