<?php
session_start();
$rest_id = isset($_GET['rest_id']) ? htmlspecialchars($_GET['rest_id']) : null;
$phone = $_SESSION['phone'];

$servername = "termproject.c3qoysmqqna6.ap-northeast-2.rds.amazonaws.com";
$username = "admin";
$password = "00000000";
$dbname = "moju";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT r.name, r.rest_id, m.food, m.price FROM restbl r JOIN menutbl m ON r.rest_id = m.rest_id WHERE r.rest_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$result = $stmt->get_result();

$menus = [];
$restaurant_name = null;
while ($row = $result->fetch_assoc()) {
    if (!$restaurant_name) {
        $restaurant_name = $row['name']; // 레스토랑 이름 저장
    }
    $menus[] = [
        'menu' => $row['food'],
        'price' => $row['price']
    ];
}
$stmt->close();

$sql = "SELECT o.* FROM ordertbl o
        WHERE  o.restaurant = ? and o.state = 'active';";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] =  $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurants</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sour+Gummy&display=swap">
    <link rel="stylesheet" href="../basic_style.css">


</head>
<body>
    <header>
        <a href="../index.php" class="header-link">
            <h1>MoJu</h1>
        </a>
    </header>

    <nav>
        <a href="restaurants.html" class="current-button">Restaurants</a>
        <a href="../orders/orders.html" class="orders-button">Orders</a>
        <a href="../signin/logout.php" class="logout-button">Logout</a>
    </nav>

    <main class="split-container">
        <!-- 좌측: 메뉴 목록 -->
        <div class="left-pane">
            <h2>Menu</h2>
            <div class="search-bar">
                <input type="text" id="search-input" placeholder="Search menu...">
            </div>
            <div id="menu-list" class="list-container">
                <!-- JavaScript로 메뉴 목록이 여기에 추가됩니다 -->
            </div>
        </div>
    
        <!-- 우측: Current Order -->
        <div class="right-pane">
            <h2>Current Order</h2>
            <div id="order-list" class="order-container">
                <!-- JavaScript로 현재 주문 내용이 여기에 추가됩니다 -->
            </div>
        </div>
    </main>


    <script>
        const menus = <?= json_encode($menus) ?>;
        const orders = <?= json_encode($orders) ?>;

        document.addEventListener("DOMContentLoaded", function () {
            const menuList = document.getElementById('menu-list');
            const searchInput = document.getElementById('search-input');
            const orderList = document.getElementById('order-list');
        
            // 메뉴 렌더링 함수
            function renderMenus(filteredMenus) {
                menuList.innerHTML = ''; // 초기화
                if (filteredMenus.length === 0) {
                    menuList.innerHTML = '<div class="error-message">No menus found.</div>';
                    return;
                }
                filteredMenus.forEach(menu => {
                    const box = document.createElement('div');
                    box.className = 'list-box';
                    box.innerHTML = `
                        <div class="list-menu">
                            <span>${menu.menu}</span>
                            <span>${menu.price.toLocaleString()} 원</span>
                        </div>
                    `;
                    menuList.appendChild(box);
                });
            }
            renderMenus(menus);
        
            // 검색 기능
            searchInput.addEventListener('input', function () {
                const query = searchInput.value.toLowerCase();
                const filteredMenus = menus.filter(menu =>
                    menu.menu.toLowerCase().includes(query)
                );
                renderMenus(filteredMenus);
            });

            function renderCurrentOrder(orders) { // 매개변수를 orders로 변경
                orderList.innerHTML = ''; // 초기화
                if (!orders || orders.length === 0) { // orders 배열 체크
                    orderList.innerHTML = '<div class="error-message">No items in the current order.</div>';
                    return;
                }
            
                orders.forEach(item => { // orders를 순회
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.order_id}</td>
                        <td>${item.time}</td>
                        <td>${item.goal_money}</td>
                        <td>${item.current_money}</td>
                        <td>${item.name}</td>
                        <td>
                            <button class="join-button" 
                                data-id="${item.order_id}" 
                                data-rest-id="${item.restaurant}" 
                                data-goal-id="${item.goal_money}" 
                                data-cur-id="${item.current_money}" 
                                data-time-id="${item.time}">
                                Join
                            </button>
                        </td>
                    `;
                    orderList.appendChild(row); // orderList에 row 추가

                    // Join 버튼 클릭 이벤트 추가
                    const joinButtons = document.querySelectorAll(".join-button");
                    joinButtons.forEach(button => {
                        button.addEventListener("click", function () {
                            const orderId = this.dataset.id;
                            const restId = this.dataset.restId;
                            const goalId = this.dataset.goalId;
                            const curId = this.dataset.curId;
                            const timeId = this.dataset.timeId;
                            // URL에 식당 ID와 주문 ID 포함
                            window.location.href = `../orders/join_order/join_form.php?order_id=${orderId}&rest_id=${restId}&goal_id=${goalId}&cur_id=${curId}&time_id=${timeId}`;
                        });
                    });
                });
            }
            renderCurrentOrder(orders);
        });
    </script>
</body>
</html>
