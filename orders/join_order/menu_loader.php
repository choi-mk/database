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
    $menu_stmt = $conn->prepare("SELECT menu_id, food FROM menutbl WHERE rest_id = ?");
    $menu_stmt->bind_param("i", $rest_id);
    $menu_stmt->execute();
    $menu_result = $menu_stmt->get_result();
    $menus = [];

    while ($menu_row = $menu_result->fetch_assoc()) {
        $menus[] = $menu_row;
    }

    echo json_encode($menus);
    $menu_stmt->close();
    $conn->close();
    exit;
}
?>
