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
$sql = "SELECT j.order_id, m.food, m.price, r.name
        FROM jointbl j
        JOIN menutbl m ON j.menu = m.menu_id
        JOIN restbl r ON m.rest_id = r.rest_id
        WHERE j.mem_id = ?
        ORDER BY j.order_id";   // mem_id가 1인 주문만 찾음

$stmt = $conn->prepare($sql);

$stmt->bind_param("s",$phone);

$stmt->execute();

// 쿼리 실행
$result = $stmt->get_result();

// 결과 저장
$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
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
        <a href="../orders.html" class="neworder-button">Create New Order</a>
        <a href="myorders.php" class="cur-button">My Orders</a>
    </div>
    
    <main>
        <h2 class="my-orders-title">My Orders</h2>


        <?php
        if (count($orders) > 0):
            $current_order_id = null;
            foreach ($orders as $order):
                // 새로운 order_id를 만날 때마다 새로운 블록을 시작
                if ($order['order_id'] != $current_order_id): ?>
                    <?php if ($current_order_id !== null): ?>
                        </div> <!-- 이전 블록 닫기 -->
                    <?php endif; ?>
                    <div class="order-block">
                        <h3>Restaurant: <?php echo htmlspecialchars($order['name']); ?></h3>
                    <?php $current_order_id = $order['order_id']; ?>
                <?php endif; ?>
                
                <div class="order-item">
                    <strong>Food:</strong> <?php echo htmlspecialchars($order['food']); ?><br>
                    <strong>Price:</strong> <?php echo htmlspecialchars($order['price']); ?> 원
                </div>

            <?php endforeach; ?>
            </div> <!-- 마지막 블록 닫기 -->
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    
    </main>



</body>
</html>
