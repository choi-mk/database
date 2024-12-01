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

// 1. UPDATE 쿼리 실행
$update_sql_deliveryfee = "
WITH RankedOrders AS (
    SELECT o.order_id, d.fee,
        ROW_NUMBER() OVER (PARTITION BY o.order_id ORDER BY d.fee ASC) AS rn
    FROM ordertbl o
    JOIN deliveryfee d 
        ON d.rest_id = o.restaurant
        AND o.current_money >= d.amount
)
UPDATE ordertbl o
LEFT JOIN restbl r ON o.restaurant = r.rest_id
LEFT JOIN RankedOrders rnk ON o.order_id = rnk.order_id AND rnk.rn = 1
SET o.cur_deliver = 
    CASE 
        WHEN o.current_money < r.minprice THEN NULL
        ELSE rnk.fee
    END;
";
$update_sql_cooking = "UPDATE ordertbl SET state = 'cooking' WHERE current_money >= goal_money AND DATE_ADD(NOW(), INTERVAL 9 HOUR) >= time";
$update_sql_inactive = "UPDATE ordertbl SET state = 'inactive' WHERE current_money < goal_money AND DATE_ADD(NOW(), INTERVAL 9 HOUR) >= time";
$conn->query($update_sql_deliveryfee);
$conn->query($update_sql_cooking);
$conn->query($update_sql_inactive);





$sql = "SELECT r.name, r.rest_id, m.food, m.price, m.img, r.minprice FROM restbl r JOIN menutbl m ON r.rest_id = m.rest_id WHERE r.rest_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$result = $stmt->get_result();

$menus = [];
$restaurant_name = null;
$minprice = null;
while ($row = $result->fetch_assoc()) {
    if (!$restaurant_name or !$minprice) {
        $restaurant_name = $row['name']; // 레스토랑 이름 저장
        $minprice = $row['minprice'];
    }
    $menus[] = [
        'menu' => $row['food'],
        'price' => $row['price'],
        'img' => $row['img']
    ];
}
$stmt->close();

$sql = "
    SELECT o.*, r.name, r.img
    FROM jointbl j
    JOIN ordertbl o ON j.order_id = o.order_id
    JOIN restbl r ON o.restaurant = r.rest_id
    JOIN deliverable d ON r.rest_id = d.rest_id 
    WHERE j.mem_id != ? and o.state = 'active' AND d.phone = ? AND o.restaurant = ?
    AND o.order_id NOT IN (SELECT DISTINCT order_id FROM jointbl WHERE mem_id = ?)
    GROUP BY o.order_id
    ORDER BY o.order_id ASC;
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssis", $phone, $phone, $rest_id, $phone);

$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] =  $row;
}
$stmt->close();

$sql = "SELECT d.amount, d.fee FROM deliveryfee d WHERE d.rest_id = ?;";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rest_id);
$stmt->execute();
$result = $stmt->get_result();

$fees = [];
while ($row = $result->fetch_assoc()) {
    $fees[] =  $row;
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
    display: flex; /* Flexbox 사용 */
    gap: 20px; /* 패널 간격 */
    padding: 20px; /* 컨테이너 내부 여백 */
    max-width: 1200px; /* 최대 너비 */
    margin: 0 auto; /* 가운데 정렬 */
    box-sizing: border-box;
}

.left-pane {
    flex: 4; /* 비율 3 */
    min-width: 200px; /* 최소 너비 */
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.right-pane {
    flex: 6; /* 비율 7 */
    min-width: 400px; /* 최소 너비 */
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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

        #delivery-fee {
            margin-bottom: 20px; /* 배달료와 Current Order 사이 간격 */
            padding: 18px;
        }
        #delivery-fee .min-price {
            margin-bottom: 5px; /* 최소 주문 금액과 배달료 리스트 사이 간격 */
        }
        #delivery-fee .fee-list {
            margin-top: 0; /* 배달료 리스트 위쪽 간격 제거 */
            list-style-type: none; /* 불릿 제거 */
            padding-left: 20px;
        }

        .fee-list li {
            margin-bottom: 5px;
            font-size: 14px;
        }
        h4 + #delivery-fee {
            margin-top: -5px; /* 간격을 5px로 줄임 */
        }

        h4 {
            font-weight: bold;
            padding-left: 15px;
            margin-bottom: 0px; /* h4의 아래쪽 여백을 줄임 */
        }

        .details-button {
            background-color: #f1f1f1; /* 연한 회색 배경 */
            border: 0px; 
            border-radius: 4px; /* 둥근 모서리 */
            padding: 5px 5px; /* 버튼 내부 여백 */
            font-size: 11px; /* 글자 크기 */
        }

        .details-button:active {
            background-color: #0056b3; /* 클릭 시 더 어두운 파란색 배경 */
            border-color: #003f7f; /* 클릭 시 테두리 색상 */
        }



        </style>

</head>

<body>
    <header>
        <a href="../index.php" class="header-link">
            <h1>MoJu</h1>
        </a>
        <div class="nickname-block">
            <button id="nickname-button" class="nickname-button" 
                onclick="window.location.href='../mypage/mypage.php'">
                <?php echo htmlspecialchars($_SESSION['nickname']); ?> 님
            </button>
        </div>
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
            <h4>Delivery fee</h4>
            <div id="delivery-fee" >
                <!-- 최소주문금액 r.minprice, amount별 배달 fee 표시-->
            </div>
            <h2>Current Order</h2>
            
            <div id="order-list" >
                <table>
                    <thead>
                        <tr>
                            <th>Order Time</th>
                            <th>Goal Money</th>
                            <th>Current Money</th>
                            <th>Delivery Fee</th>
                            <th>Delivery Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="order-rows">
                        <!-- JavaScript로 주문 내용이 여기에 추가됩니다 -->
                    </tbody>
                </table>
            </div>

            
        </div>
    </main>

    <a href="../orders/neworder/neworder_form.php" class="floating-button" id="new-order-btn" data-rest-id="<?= $rest_id ?>">+</a>

    <script>
        const menus = <?= json_encode($menus) ?>;
        const orders = <?= json_encode($orders) ?>;
        const fees = <?= json_encode($fees) ?>;
        const minPrice = <?= json_encode($minprice) ?>;

        console.log('Menus:', menus);
        console.log('Orders:', orders);
        console.log('Fees:', fees);

        document.addEventListener("DOMContentLoaded", function () {
            const deliveryFeeContainer = document.getElementById('delivery-fee');

            function renderDeliveryFee(fees, minPrice) {
                deliveryFeeContainer.innerHTML = ''; // 기존 내용을 초기화

                const minFee = fees.length > 0 ? Math.min(...fees.map(fee => fee.fee)) : 0;
                const maxFee = fees.length > 0 ? Math.max(...fees.map(fee => fee.fee)) : 0;

                // 최소 주문 금액 표시
                const minPriceElement = document.createElement('div');
                minPriceElement.className = 'min-price';
                minPriceElement.innerHTML = `최소 주문 금액: ${minPrice.toLocaleString()} 원`;
                deliveryFeeContainer.appendChild(minPriceElement);

                const deliveryFeeInfo = document.createElement('div');
                deliveryFeeInfo.className = 'delivery-fee-info';
                deliveryFeeInfo.innerHTML = `배달비: ${minFee.toLocaleString()} ~ ${maxFee.toLocaleString()} 원`;

                const detailsButton = document.createElement('button');
                detailsButton.textContent = '[자세히]';
                detailsButton.style.marginLeft = '10px';
                detailsButton.style.cursor = 'pointer';
                detailsButton.className = 'details-button';

                detailsButton.addEventListener('click', function () {
                    feeList.style.display = feeList.style.display === 'none' ? 'block' : 'none';
                    detailsButton.textContent = feeList.style.display === 'none' ? '[자세히]' : '[접기]';
                });

                deliveryFeeInfo.appendChild(detailsButton);
                deliveryFeeContainer.appendChild(deliveryFeeInfo);
            
                // 배달료 리스트 표시
                if (fees.length === 0) {
                    deliveryFeeContainer.innerHTML += '<div class="error-message">배달료 정보가 없습니다.</div>';
                    return;
                }
            
                const feeList = document.createElement('ul');
                feeList.className = 'fee-list';
                feeList.style.display = 'none'; // 기본적으로 숨김 처리

            
                fees.forEach(fee => {
                    const feeItem = document.createElement('li');
                    feeItem.innerHTML = `
                        <span>${fee.amount.toLocaleString()} 원 이상: ${fee.fee.toLocaleString()} 원</span>
                    `;
                    feeList.appendChild(feeItem);
                });
            
                deliveryFeeContainer.appendChild(feeList);
            }
        
            // 데이터 렌더링
            
            renderDeliveryFee(fees, minPrice);
            const floatingButton = document.getElementById("new-order-btn");

            floatingButton.addEventListener("click", function (event) {
                // Prevent default link behavior
                event.preventDefault();
            
                // Get the rest_id from the data attribute
                const restId = this.dataset.restId;
            
                // Redirect to the new order form with the rest_id as a query parameter
                window.location.href = `../orders/neworder/neworder_form.php?rest_id=${restId}`;
            });
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
                const orderList = document.getElementById('order-rows'); // tbody
                orderList.innerHTML = ''; // 초기화
                if (!orders || orders.length === 0) {
                    orderList.innerHTML = '<div class="error-message">No items in the current order.</div>';
                    return;
                }
                
            
            
                orders.forEach(item => {
                    const row = document.createElement('tr');
                    let curDeliverText = '';
                    const curDeliver = parseFloat(item.cur_deliver);  // 숫자로 변환
                    const participantsNum = parseInt(item.participants_num);  // 정수로 변환
                    
                    if (isNaN(curDeliver) || isNaN(participantsNum) || participantsNum <= 0) {
                        curDeliverText = '최소주문금액미만'; // 최소 주문 금액 미만인 경우
                    } else {
                        const deliveryFeePerParticipant = Math.floor(curDeliver / participantsNum);  // 소수점 버림
                        curDeliverText = `${deliveryFeePerParticipant}`;
                    }
                    row.innerHTML = `
                        <td>${item.time}</td>
                        <td>${item.goal_money}</td>
                        <td>${item.current_money}</td>
                        <td>${curDeliverText}</td>
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
                    // Join 버튼 클릭 이벤트 추가
                    const joinButtons = document.querySelectorAll(".submit-btn");
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
