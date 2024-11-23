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

$phone = $_SESSION['phone'];

if (isset($_GET['rest_id']) && isset($_GET['order_id'])) {
    $rest_id = intval($_GET['rest_id']);
    $menu_stmt = $conn->prepare("select j.amount, m.*
                                from menutbl m
                                join jointbl j on j.menu = m.menu_id
                                where rest_id = ? and j.mem_id = ? and order_id");
    
    $menu_stmt->bind_param("is", $rest_id, $phone);
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
