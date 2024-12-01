<?php
session_start();

// DB 연결 정보
$servername = "termproject.c3qoysmqqna6.ap-northeast-2.rds.amazonaws.com";
$username = "admin";
$password = "00000000";
$dbname = "moju";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['rest_id'])) {
    $rest_id = intval($_GET['rest_id']);
    $stmt = $conn->prepare("SELECT * FROM deliveryfee WHERE rest_id = ?");
    $stmt->bind_param("i", $rest_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fees = [];

    while ($row = $result->fetch_assoc()) {
        $fees[] = $row;
    }

    echo json_encode($fees);
    $stmt->close();
    $conn->close();
    exit;
}
?>
