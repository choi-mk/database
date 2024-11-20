<<<<<<< HEAD
<?php
session_start();


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
$sql = "SELECT o.*, r.name FROM ordertbl o
        JOIN restbl r ON o.restaurant = r.rest_id
        WHERE o.state = 'active'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    // 데이터 배열로 저장
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    // JSON 형식으로 반환
    echo json_encode($orders);
} else {
    echo json_encode(["message" => "You have no orders."]);
}

$conn->close();
?>
=======
<?php
session_start();


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


$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 주문 내역 쿼리
$sql = "SELECT * FROM orders WHERE user_id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Your Orders</h2>";
    while ($row = $result->fetch_assoc()) {
        echo "<p>Order ID: " . $row['order_id'] . "<br>";
        echo "Order Date: " . $row['order_date'] . "<br>";
        echo "Order Details: " . $row['order_details'] . "</p>";
    }
} else {
    echo "You have no orders.";
}

$conn->close();
?>
>>>>>>> e480ebc436270c6e5db50323884979fb6d70b0e5
