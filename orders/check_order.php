<?php
// 데이터베이스 연결 설정
$servername = "termproject.c3qoysmqqna6.ap-northeast-2.rds.amazonaws.com";
$username = "admin";
$password = "00000000";
$dbname = "moju";


// 데이터베이스 연결
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 오류 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// POST로 전달된 주문 ID 받기
if (isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    // SQL 쿼리로 주문 정보 조회
    $sql = "SELECT * FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // 결과가 있으면 주문 정보 출력
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        echo "<h3>Order Details</h3>";
        echo "<p>Order ID: " . $order['order_id'] . "</p>";
        echo "<p>Customer Name: " . $order['customer_name'] . "</p>";
        echo "<p>Order Date: " . $order['order_date'] . "</p>";
        echo "<p>Order Status: " . $order['status'] . "</p>";
        echo "<p>Total Price: $" . $order['total_price'] . "</p>";
        // 주문에 대한 다른 정보 추가 가능
    } else {
        echo "<p>No order found with that ID.</p>";
    }

    $stmt->close();
} else {
    echo "<p>Please provide an order ID.</p>";
}

$conn->close();
?>
