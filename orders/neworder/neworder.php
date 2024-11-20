<?php
session_start();

// 사용자가 제출한 데이터를 가져옵니다
$restaurant = $_POST['restaurant'];
$time = $_POST['time'];
$goal_money = $_POST['goal_money'];
$amount_data = $_POST['amount'];
$phone = $_SESSION['phone'];

// 데이터 유효성 검사 (예: 비어있는 필드가 없는지 확인)
if (empty($restaurant) || empty($time) || empty($goal_money) || empty($amount_data)) {
    $error = "모든 필드를 입력해주세요.";
    echo "<script>alert('" . addslashes($error) . "'); history.back();</script>";
    exit;
}

// DB 연결 정보
$servername = "termproject.c3qoysmqqna6.ap-northeast-2.rds.amazonaws.com";
$username = "admin";
$password = "00000000";
$dbname = "moju";

// DB 연결
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL 쿼리 준비 및 실행
$stmt = $conn->prepare("SELECT delivery_expense FROM restbl WHERE rest_id = ?");
$stmt->bind_param("i", $restaurant);  
$stmt->execute();
$stmt->bind_result($cur_deliver);
$stmt->fetch();
$stmt->close();

$current_money = 0;

// 메뉴별로 가격 계산
foreach ($amount_data as $menu => $amount) {
    $stmt = $conn->prepare("SELECT price FROM menutbl WHERE rest_id = ? AND menu_id = ?");
    $stmt->bind_param("ii", $restaurant, $menu);
    $stmt->execute();
    $stmt->bind_result($menu_price);
    $stmt->fetch();
    $stmt->close();

    $current_money += $menu_price * $amount;
}

// ordertbl에 데이터 삽입
$stmt = $conn->prepare(
    "INSERT INTO ordertbl (time, state, current_money, goal_money, participants_num, cur_deliver, leader, restaurant) 
     VALUES (?, 'active', ?, ?, 1, ?, ?, ?)"
);
$stmt->bind_param("siiiis", $time, $current_money, $goal_money, $cur_deliver, $phone, $restaurant);

if ($stmt->execute()) {
    $order_id = $conn->insert_id; // 삽입된 order_id 가져오기

    // 각 menu-amount에 대해 jointbl에 데이터 삽입
    $stmt = $conn->prepare("INSERT INTO jointbl (mem_id, order_id, menu, amount) VALUES (?, ?, ?, ?)");
    foreach ($amount_data as $menu => $amount) {
        $stmt->bind_param("siii", $phone, $order_id, $menu, $amount);
        if (!$stmt->execute()) {
            $error = "jointbl 삽입 중 에러: " . $stmt->error;
            echo "<script>alert('" . addslashes($error) . "'); history.back();</script>";
            exit;
        }
    }

    $success_message = "주문이 접수되었습니다.";
    echo "<script>alert('" . addslashes($success_message) . "'); window.location.href='../myorders/myorders.php';</script>";
} else {
    $error = "ordertbl 삽입 중 에러: " . $stmt->error;
    echo "<script>alert('" . addslashes($error) . "'); history.back();</script>";
}

$stmt->close();
$conn->close();
?>