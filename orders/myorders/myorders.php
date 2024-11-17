<<<<<<< HEAD
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
=======
<?php

session_start();

if (isset($_SESSION['success'])) {
    echo "<p style='color: green;'>" . htmlspecialchars($_SESSION['success']) . "</p>";
    unset($_SESSION['success']);  // 메시지를 한 번만 표시
}

if (isset($_SESSION['error'])) {
    echo "<p style='color: red;'>" . htmlspecialchars($_SESSION['error']) . "</p>";
    unset($_SESSION['error']);
}

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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // PHP에서 전달된 메시지를 JavaScript에서 팝업으로 표시
            const successMessage = "<?= $success_message ?>";
            const errorMessage = "<?= $error_message ?>";

            if (successMessage) {
                alert(successMessage);
            }

            if (errorMessage) {
                alert(errorMessage);
            }
        });
    </script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sour+Gummy&display=swap">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #333;
            color: white;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        header h1 {
            font-family: 'Sour Gummy';
            font-size: 40px;
            margin: 0;
        }

        header a {
            color: white;
            text-decoration: none;
        }

        header a:hover {
            text-decoration: none;
            color: white;
        }

        nav {
            background: #555;
            color: #fff;
            display: flex;
            justify-content: center;
            padding: 10px 0;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            font-family: 'Sour Gummy';
            font-size: 20px;
            font-weight: 100;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .orders-button {
            font-weight: bold;
            color: #ffcc00;
        }

        main {
            padding: 20px;
            background: #fff;
            margin: 20px auto;
            max-width: 1200px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        footer {
            text-align: center;
            padding: 10px 0;
            background: #333;
            color: #fff;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        .tab-bar {
            display: flex;
            justify-content: center;
            background-color: #ddd;
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }

        .tab-bar a {
            color: #333;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: bold;
            margin: 0 5px;
            border-radius: 5px;
            background-color: #eee;
        }

        .tab-bar a:hover {
            background-color: #ccc;
        }

        .tab-bar .myorders-button {
            background-color: #ffcc00;
            color: white;
            font-weight: bold;
        }

        .nickname-block {
            color: white;
            font-weight: bold;
            background-color: #444;
            padding: 5px 10px;
            border-radius: 5px;
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

    <header>
        <a href="../../index.php" class="header-link">
            <h1>MoJu</h1>
        </a>
    </header>

    <nav>
        <a href="../../restaurants/restaurants.html" class="restaurants-button">Restaurants</a>
        <a href="../orders.html" class="orders-button">Orders</a>
        <a href="../../signin/logout.php" class="logout-button">Logout</a>
    </nav>

    <div class="tab-bar">
        <a href="../orders.html" class="neworder-button">Create New Order</a>
        <a href="myorders.php" class="myorders-button">My Orders</a>
    </div>
    
    <main>
        <h2>My Orders</h2>

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
>>>>>>> 1afc5de83d77efdc8c1bb5c7f96f781384b91eed
