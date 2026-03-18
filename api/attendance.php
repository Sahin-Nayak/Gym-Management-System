<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'checkin') {
    $memberId = (int)$_POST['member_id'];
    $now = date('Y-m-d H:i:s');
    $today = date('Y-m-d');

    $existing = $conn->query("SELECT id FROM attendance WHERE member_id = $memberId AND date = '$today' AND check_out IS NULL")->fetch_assoc();
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'Already checked in']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO attendance (member_id, check_in, date) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $memberId, $now, $today);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Checked in', 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed']);
    }
}

if ($action === 'checkout') {
    $attId = (int)$_POST['attendance_id'];
    $now = date('Y-m-d H:i:s');

    $conn->query("UPDATE attendance SET check_out = '$now' WHERE id = $attId");
    echo json_encode(['success' => true, 'message' => 'Checked out']);
}
?>
