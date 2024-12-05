<?php
session_start(); // 세션 시작

// 로그인 상태 확인
$is_logged_in = isset($_SESSION['phone']);  // 세션에 phone이 저장되어 있으면 로그인 상태


// 로그인 상태가 아니라면 바로 종료
if (!$is_logged_in) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}
$phone = $_SESSION['phone']; 

// POST로 요청 받은 order_id와 price
$order_id = $_POST['order_id']; 
$price = $_POST['price'];  
// 데이터베이스 연결
$servername = "termproject.c3qoysmqqna6.ap-northeast-2.rds.amazonaws.com";
$username = "admin";
$password = "00000000";
$dbname = "moju";

// MySQLi 연결
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 트랜잭션 시작
$conn->begin_transaction();

try {
    // 주문 삭제 쿼리 실행 (예시: jointbl에서 해당 주문 삭제)
    $sql_delete = "DELETE FROM jointbl WHERE order_id = ? AND mem_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("is", $order_id, $phone);
    $stmt_delete->execute();

    // current_money 값 업데이트 (원래 값에서 price를 뺌)
    $sql_update = "UPDATE ordertbl SET current_money = current_money - ? WHERE order_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ii", $price, $order_id);  // price는 수치값이므로 double(실수형)로 처리
    $stmt_update->execute();

    // 커밋 (변경사항을 반영)
    $conn->commit();

    // 결과 확인
    if ($stmt_delete->affected_rows > 0) {
        echo json_encode(['success' => true]);  // 삭제 성공 시 JSON 응답
    } else {
        throw new Exception('Order deletion failed');
    }
} catch (Exception $e) {
    // 트랜잭션 롤백 (오류 발생 시)
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// 연결 종료
$conn->close();
?>