<?php
include 'db_connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $id = $_POST['id'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $phone_number = $_POST['phone_number'];

    try {
        if ($type === 'player') {
            $position = $_POST['position'];
            $stmt = $conn->prepare("UPDATE player SET p_name=?, age=?, position=?, phone_number=? WHERE player_id=?");
            $stmt->bind_param("sisii", $name, $age, $position, $phone_number, $id);
        } else {
            $experience = $_POST['experience'];
            $stmt = $conn->prepare("UPDATE coach SET co_name=?, age=?, experience=?, phone_number=? WHERE coach_id=?");
            $stmt->bind_param("siiis", $name, $age, $experience, $phone_number, $id);
        }

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
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>