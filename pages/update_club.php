<?php
session_start();
if (!isset($_SESSION['userId'])) {
    die(json_encode(['success' => false, 'message' => 'Not authorized']));
}

require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $club_id = $_POST['club_id'];
    $c_name = $_POST['c_name'];
    $league_id = $_POST['league_id'];
    $location = $_POST['location'];

    try {
        $stmt = $conn->prepare("UPDATE club SET c_name = ?, league_id = ?, location = ? WHERE club_id = ?");
        $stmt->bind_param("sisi", $c_name, $league_id, $location, $club_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>