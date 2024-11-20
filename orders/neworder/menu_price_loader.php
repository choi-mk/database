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

if (isset($_GET['menu_id'])) {
    $menu_id = intval($_GET['menu_id']);
    $stmt = $conn->prepare("SELECT price FROM menutbl WHERE menu_id = ?");
    $stmt->bind_param("i", $menu_id);
    $stmt->execute();
    $stmt->bind_result($price);
    $stmt->fetch();

    echo json_encode(['price' => $price]);
    $stmt->close();
}

$conn->close();
?>
