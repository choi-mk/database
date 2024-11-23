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
$update_sql_deliveryfee = "WITH RankedOrders AS (
                            SELECT o.order_id, d.fee,
                                ROW_NUMBER() OVER (PARTITION BY o.order_id ORDER BY d.fee ASC) AS rn
                            FROM ordertbl o
                            JOIN deliveryfee d 
                            ON d.rest_id = o.restaurant
                            AND o.current_money >= d.amount
                        )
                        UPDATE ordertbl o
                        JOIN RankedOrders r ON o.order_id = r.order_id
                        SET o.cur_deliver = r.fee
                        WHERE r.rn = 1;";
$update_sql_cooking = "UPDATE ordertbl SET state = 'cooking' WHERE current_money >= goal_money AND DATE_ADD(NOW(), INTERVAL 9 HOUR) >= time";
$update_sql_inactive = "UPDATE ordertbl SET state = 'inactive' WHERE current_money < goal_money AND DATE_ADD(NOW(), INTERVAL 9 HOUR) >= time";
$conn->query($update_sql_deliveryfee);
$conn->query($update_sql_cooking);
$conn->query($update_sql_inactive);

// 2. SELECT 쿼리 실행 (Prepared Statement 사용)
$select_sql = "
    SELECT o.*, r.name, r.img
    FROM jointbl j
    JOIN ordertbl o ON j.order_id = o.order_id
    JOIN restbl r ON o.restaurant = r.rest_id
    JOIN deliverable d ON r.rest_id = d.rest_id 
    WHERE j.mem_id != ? and o.state = 'active' AND d.phone = ?
    AND o.order_id NOT IN (SELECT DISTINCT order_id FROM jointbl WHERE mem_id = ?)
    GROUP BY o.order_id
    ORDER BY o.order_id ASC;
";

$stmt = $conn->prepare($select_sql);
$stmt->bind_param("sss", $phone, $phone, $phone);

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
