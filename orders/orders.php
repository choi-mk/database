<?php
session_start();

date_default_timezone_set('Asia/Seoul');

if (!isset($_SESSION['phone'])) {
    // 로그인되지 않은 경우 리디렉션 정보 반환
    echo json_encode(["redirect" => "../signin/signin.html"]);
    exit;
}

$nickname = $_SESSION['nickname'];

// 사용자 ID 가져오기
$phone = $_SESSION['phone'];

// 데이터베이스 연결
$servername = "termproject.c3qoysmqqna6.ap-northeast-2.rds.amazonaws.com";
$username = "admin";
$password = "00000000";
$dbname = "moju";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// 1. UPDATE 쿼리 실행
$update_sql_cooking = "UPDATE ordertbl SET state = 'cooking' WHERE current_money >= goal_money AND DATE_ADD(NOW(), INTERVAL 9 HOUR) >= time";
$update_sql_inactive = "UPDATE ordertbl SET state = 'inactive' WHERE current_money < goal_money AND DATE_ADD(NOW(), INTERVAL 9 HOUR) >= time";

$conn->query($update_sql_cooking);
$conn->query($update_sql_inactive);

// 2. SELECT 쿼리 실행 (Prepared Statement 사용)
$select_sql = "
    SELECT o.*, r.name 
    FROM ordertbl o
    JOIN restbl r ON o.restaurant = r.rest_id
    JOIN deliverable d ON r.rest_id = d.rest_id 
    WHERE o.state = 'active' AND d.phone = ?
";

$stmt = $conn->prepare($select_sql);
$stmt->bind_param("s", $phone);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $orders = [];

    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    echo json_encode([
        'nickname' => $nickname,
        'orders' => $orders
    ]);
} else {
    echo json_encode(["error" => "Query execution failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
