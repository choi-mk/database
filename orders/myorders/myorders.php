<?php
session_start();  // 세션 시작

// 로그인 상태 확인
$is_logged_in = isset($_SESSION['phone']);  // 세션에 phone이 저장되어 있으면 로그인 상태

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
$sql = "SELECT j.order_id, m.food, m.price, r.name, o.state, r.img
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
            'state' => $row['state'], 
            'img' => $row['img']
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
        <style>

        .order-state {
            font-weight: bold;
            display: inline-block;
            width: 80px;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 14px;
            text-align: center;
            background-color: #d2e9e4;
            margin-bottom: 0px;
            margin-top: 20px;
        }

        .order-content {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            clear: both; /* float가 다음 콘텐츠에 영향을 주지 않도록 */
        }

        .list-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start; 
            margin: 0 auto; /* My Orders와 동일한 위치로 조정 */
            gap: 5px;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 10px;
        }

        .list-box {
            width: 100%; /* 부모 컨테이너의 너비에 맞춤 */
            max-width: 1200px; /* 부모와 같은 최대 너비로 제한 */
            margin: 0 auto;
            margin-top: 0;
            display: flex;
            flex-direction: column;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-sizing: border-box;
            background-color: #ffffff;
        }

        .list-box:hover {
            transform: scale(1.0);
            border-color: #ddd;
        }


        .order-img {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
        }

        .order-details {
            flex: 1; /* 이미지 옆 공간을 균일하게 채움 */
        }

        .list-restaurant {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .order-foods {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .order-price {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
    </style>


</head>
<body>
    <header>
        <a href="../../index.php" class="header-link">
            <h1>MoJu</h1>
        </a>
        <?php if ($is_logged_in): ?>
            <!-- 로그인 상태일 때 닉네임 표시 -->
            <div class="nickname-block">
                <button id="nickname-button" class="nickname-button" 
                    onclick="window.location.href='../../mypage/mypage.php'">
                    <?php echo htmlspecialchars($_SESSION['nickname']); ?> 님
                </button>
            </div>
        <?php endif; ?>
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
                    <div class="order-state <?php 
                        echo ($order['state'] === 'cooking') ? 'state-cooking' :
                             (($order['state'] === 'active') ? 'state-active' : 'state-inactive'); ?>">
                        <?php echo htmlspecialchars($order['state']); ?>
                    </div>
                    <div class="list-box">
                        <div class="order-content">
                            <img src="../../images/<?php echo htmlspecialchars($order['img']); ?>" alt="<?php echo htmlspecialchars($order['name']); ?>" class="order-img">
                            <div class="order-details">
                                <div class="list-restaurant">
                                    <?php echo htmlspecialchars($order['name']); ?>
                                </div>
                                <div class="order-foods">
                                    <?php echo htmlspecialchars(implode(", ", $order['foods'])); ?>
                                </div>
                                <div class="order-price">
                                    <?php echo htmlspecialchars($order['price']); ?> 원
                                </div>
                            </div>
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
