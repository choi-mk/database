<?php
session_start();

date_default_timezone_set('Asia/Seoul');

if (!isset($_SESSION['phone'])) {
    // 로그인되지 않은 경우 리디렉션 정보 반환
    echo json_encode(["redirect" => "../signin/signin.html"]);
    exit;
}

// 사용자 ID 가져오기
$phone = $_SESSION['phone']; 

// 데이터베이스 연결
$servername = "termproject.c3qoysmqqna6.ap-northeast-2.rds.amazonaws.com";
$username = "admin";
$password = "00000000";
$dbname = "moju";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 주문 내역 쿼리
$sql = "
    -- 조건을 만족하는 레코드를 cooking으로 업데이트
    UPDATE ordertbl
    SET state = 'cooking'
    WHERE current_money >= goal_money AND DATE_ADD(NOW(), INTERVAL 9 HOUR) >= time;



    -- 조건을 만족하지 않는 레코드를 inactive로 업데이트
    UPDATE ordertbl
    SET state = 'inactive'
    WHERE current_money < goal_money AND DATE_ADD(NOW(), INTERVAL 9 HOUR) >= time;

    -- 업데이트 후 active 상태인 레코드와 관련 데이터를 SELECT
    SELECT o.*, r.name 
    FROM ordertbl o
    JOIN restbl r ON o.restaurant = r.rest_id
    WHERE o.state = 'active';
";

if ($conn->multi_query($sql)) {
    $orders = []; // 결과를 저장할 배열

    do {
        // 결과 세트 처리
        if ($result = $conn->store_result()) {
            // SELECT 쿼리의 결과만 처리
            if ($result->field_count > 0) { // SELECT 쿼리 여부 확인
                while ($row = $result->fetch_assoc()) {
                    $orders[] = $row;
                }
            }
            $result->free(); // 결과 해제
        }
    } while ($conn->next_result()); // 다음 결과 세트로 이동

    if (!empty($orders)) {
        // 결과가 있을 경우 JSON으로 반환
        echo json_encode($orders);
    } else {
        echo json_encode(["message" => "You have no orders."]);
    }
} else {
    echo json_encode(["error" => "Query execution failed: " . $conn->error]);
}

$conn->close();
?>
