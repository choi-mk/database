<?php
session_start();
// 사용자가 제출한 데이터를 가져옵니다
$restaurant = $_POST['rest_id'] ?? null;
$total_price = $_POST['total_price'] ?? null;
$amount_data = $_POST['amount'] ?? null;
$my_price = $_POST['my_price'] ?? null;
$order_id = $_POST['order_id'] ?? null;
$phone = $_SESSION['phone'] ?? null;

// 데이터 유효성 검사 (예: 비어있는 필드가 없는지 확인)
if (empty($restaurant) || empty($total_price) || empty($amount_data) || empty($total_price) || empty($my_price) || empty($order_id)) {
    $error = "모든 필드를 입력해주세요.";
    echo "<script>";
    echo "alert('{$error}');";
    echo "</script>";
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


// ordertbl에 데이터 삽입
$stmt = $conn->prepare(
    "UPDATE ordertbl
     SET 
         current_money = ?, 
         participants_num = participants_num + 1
     WHERE order_id = ?"
);
$stmt->bind_param("ii", $total_price,$order_id);

if ($stmt->execute()) {
    // jointbl에 데이터 삽입
    $stmt = $conn->prepare("INSERT INTO jointbl (mem_id, order_id, menu, amount) VALUES (?, ?, ?, ?)");
    foreach ($amount_data as $menu => $amount) {
        $stmt->bind_param("siii", $phone, $order_id, $menu, $amount);
        if (!$stmt->execute()) {
            $error = "jointbl 삽입 중 에러: " . $stmt->error;
            echo "<script>alert('" . addslashes($error) . "'); history.back();</script>";
            exit;
        }
    }
    $success_message = "주문이 접수되었습니다. \n$my_price 원이 등록된 계좌로 결제됩니다.";
    echo "<script>
        const message = " . json_encode($success_message) . ";
        alert(message);
        window.location.href = '../myorders/myorders.php';
      </script>";
} else {
    $error = "ordertbl update 중 에러: " . $stmt->error;
    header("Location: edit_order.php?error=" . urlencode($error));
    exit;
}

$stmt->close();
$conn->close();
?>
