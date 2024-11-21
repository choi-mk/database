<?php
session_start();

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

$phone = $_SESSION['phone'];

// 준비된 쿼리 사용
$sql = "SELECT j.order_id, m.food, m.price, r.name, o.state 
        FROM jointbl j
        JOIN menutbl m ON j.menu = m.menu_id
        JOIN restbl r ON m.rest_id = r.rest_id
        JOIN ordertbl o ON o.order_id = j.order_id
        WHERE j.mem_id = ?
        ORDER BY 
            FIELD(o.state, 'cooking', 'active', 'inactive'), 
            j.order_id ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $phone);
$stmt->execute();

// 쿼리 실행 및 데이터 가져오기
$result = $stmt->get_result();

// 결과 저장 (order_id로 그룹화)
$orders_grouped = [];
while ($row = $result->fetch_assoc()) {
    $order_id = $row['order_id'];
    if (!isset($orders_grouped[$order_id])) {
        $orders_grouped[$order_id] = [
            'order_id' => $order_id,
            'name' => $row['name'],
            'foods' => [],
            'price' => 0,
            'state' => $row['state']
        ];
    }
    $orders_grouped[$order_id]['foods'][] = $row['food'];
    $orders_grouped[$order_id]['price'] += $row['price'];
}

// 연결 종료
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sour+Gummy&display=swap">
    <link rel="stylesheet" href="../../basic_style.css">


</head>
<body>
    <header>
        <a href="../../index.php" class="header-link">
            <h1>MoJu</h1>
        </a>
    </header>

    <nav>
        <a href="../../restaurants/restaurants.html" class="restaurants-button">Restaurants</a>
        <a href="../orders.html" class="current-button">Orders</a>
        <a href="../../signin/logout.php" class="logout-button">Logout</a>
    </nav>

    <div class="tab-bar">
        <a href="../orders.html" class="neworder-button">Current Order</a>
        <a href="myorders.php" class="cur-button">My Orders</a>
    </div>
    
    <main>
        <h2 class="my-orders-title">My Orders</h2>

        <?php if (count($orders_grouped) > 0): ?>
            <div class="list-container">
                <?php foreach ($orders_grouped as $order): ?>
                    <div class="list-box">
                        <div class="order-state <?php 
                            echo ($order['state'] === 'cooking') ? 'state-cooking' :
                                 (($order['state'] === 'active') ? 'state-active' : 'state-inactive'); ?>">
                            <?php echo htmlspecialchars($order['state']); ?>
                        </div>
                        <div class="order-restaurant">
                            <?php echo htmlspecialchars($order['name']); ?>
                        </div>
                        <div class="order-foods">
                            <?php echo htmlspecialchars(implode(", ", $order['foods'])); ?>
                        </div>
                        <div class="order-price">
                            <?php echo htmlspecialchars($order['price']); ?> 원
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </main>
</body>
</html>
