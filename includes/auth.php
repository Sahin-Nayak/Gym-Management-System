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

function generateResetToken($email) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 1) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expiry, $email);
        $stmt->execute();
        
        return ['success' => true, 'token' => $token];
    }
    
    return ['success' => false, 'message' => 'Email not found.'];
}

function resetPassword($token, $newPassword) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $user['id']);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Password reset successful.'];
        }
    }
    
    return ['success' => false, 'message' => 'Invalid or expired token.'];
}

// ── Forgot Password: Step 1 — verify email + username match ──
function verifyIdentity($email, $username) {
    global $conn;
    if (empty($email) || empty($username)) {
        return ['success' => false, 'message' => 'Please fill in both fields.'];
    }
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND username = ? AND is_active = 1");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        return ['success' => true, 'user_id' => $row['id']];
    }
    return ['success' => false, 'message' => 'No account found with that email and username combination.'];
}

// ── Forgot Password: Step 2 — set new password directly (no token needed) ──
function resetPasswordDirect($userId, $newPassword) {
    global $conn;
    $userId = (int)$userId;
    if ($userId <= 0) {
        return ['success' => false, 'message' => 'Invalid session. Please start over.'];
    }
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
    $stmt->bind_param("si", $hash, $userId);
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
