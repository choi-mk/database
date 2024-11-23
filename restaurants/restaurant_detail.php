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

$sql = "SELECT r.name, r.rest_id, m.food, m.price, m.img FROM restbl r JOIN menutbl m ON r.rest_id = m.rest_id WHERE r.rest_id = ?";
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
        'price' => $row['price'],
        'img' => $row['img']
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
    <style>
        .split-container {
            display: flex; /* Flexbox를 사용하여 좌우 정렬 */
            justify-content: space-between; /* 좌우로 요소를 정렬 */
            gap: 20px; /* 좌우 패널 사이의 간격 */
            padding: 20px; /* 컨테이너 내부 여백 */
            max-width: 1200px; /* 컨테이너의 최대 너비 설정 */
            margin: 0 auto; /* 가운데 정렬 */
            box-sizing: border-box;
        }

        .left-pane, .right-pane {
            flex: 1; /* 좌우 패널이 동일한 크기를 가짐 */
            min-width: 300px; /* 패널의 최소 너비 */
            background-color: #f9f9f9; /* 패널의 배경색 */
            padding: 20px; /* 패널 내부 여백 */
            border-radius: 8px; /* 모서리 둥글게 */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* 박스 그림자 추가 */
        }

        .left-pane {
            /* 추가 스타일이 필요하면 여기에 작성 */
        }

        .right-pane {
            /* 추가 스타일이 필요하면 여기에 작성 */
        }

        .search-bar {
            margin-bottom: 20px; /* 검색창과 메뉴 리스트 사이의 간격 */
        }

        .list-container, .order-container {
            max-height: 500px; /* 리스트 영역의 최대 높이 설정 */
            overflow-y: auto; /* 세로 스크롤 활성화 */
            background-color: #ffffff; /* 리스트 배경색 */
            padding: 10px;
            border: 1px solid #ddd; /* 테두리 추가 */
            border-radius: 5px; /* 모서리를 둥글게 */
        }
        .menu-img {
            width: 50px; /* 이미지 너비 */
            height: 50px; /* 이미지 높이 */
            margin-right: 10px; /* 텍스트와의 간격 */
            border-radius: 5px; /* 이미지 모서리를 둥글게 */
            object-fit: cover; /* 이미지 비율 유지 */
        }

        .list-box {
            display: flex;
            align-items: center;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .list-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .list-menu {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .menu-name {
            font-weight: bold;
            font-size: 16px;
        }

        .menu-price {
            color: #555;
        }


        </style>

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
                    box.style.display = 'flex';
                    box.style.alignItems = 'center';
                
                    box.innerHTML = `
                        <img src="../images/${menu.img}" alt="${menu.menu}" class="menu-img">
                        <div class="list-menu">
                            <span class="menu-name">${menu.menu}</span>
                            <span class="menu-price">${menu.price.toLocaleString()} 원</span>
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
        
            // 주문 렌더링 함수
            function renderCurrentOrder(orders) {
                orderList.innerHTML = ''; // 초기화
                if (!orders || orders.length === 0) {
                    orderList.innerHTML = '<div class="error-message">No items in the current order.</div>';
                    return;
                }
            
                orders.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.order_id}</td>
                        <td>${item.time}</td>
                        <td>${item.goal_money}</td>
                        <td>${item.current_money}</td>
                        <td>${item.address4}</td>
                        <td>
                            <button class="submit-btn" 
                                data-id="${item.order_id}" 
                                data-rest-id="${item.restaurant}" 
                                data-goal-id="${item.goal_money}" 
                                data-cur-id="${item.current_money}" 
                                data-time-id="${item.time}">
                                Join
                            </button>
                        </td>
                    `;
                    orderList.appendChild(row);
                });
            }
            renderCurrentOrder(orders);
        });
    </script>
</body>
</html>
