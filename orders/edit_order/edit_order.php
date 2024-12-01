<?php
session_start();
$order_id = isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : null;
$my_price = isset($_GET['price']) ? htmlspecialchars($_GET['price']) : 0;  // 기본값 0으로 설정

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

// 세션에서 사용자 번호 가져오기
$phone = $_SESSION['phone'];

// 식당 목록 가져오기
$stmt = $conn->prepare(
    "SELECT o.*, r.name 
    FROM ordertbl o 
    JOIN restbl r ON o.restaurant = r.rest_id
    WHERE o.order_id = ?"
);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$restaurant = $row['name'];
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../basic_style.css">
    <title>Edit Order</title>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
    // 페이지가 로드되면 메뉴를 자동으로 로드하고, my_price 표시
    loadMenu();
      // 초기 총 금액 업데이트
});

let menuPrices = {};
let initialPrice = <?php echo htmlspecialchars($my_price ?? 0); ?>;  // 초기값 my_price
let currentMoney = <?php echo isset($row['current_money']) ? htmlspecialchars($row['current_money']) : 'null'; ?>;

let goalMoney = <?php echo isset($row['goal_money']) ? htmlspecialchars($row['goal_money']) : 'null'; ?>;

function loadMenu() {
    const restId = "<?php echo $row['restaurant']; ?>"; 
    const orderId = "<?php echo $row['order_id']; ?>"; 
    const menuContainer = document.getElementById('menu-container');
    menuContainer.innerHTML = '';

    if (restId) {
        fetch(`menu_loader.php?rest_id=${restId}&order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(menu => {
                    menuPrices[menu.menu_id] = menu.price;

                    // amount를 동적으로 가져와 기본값으로 설정
                    const amount = menu.amount || 0;

                    const menuItem = document.createElement('div');
                    menuItem.className = 'menu-item';
                    menuItem.innerHTML = `
                        <img src="../../images/${menu.img}" alt="${menu.food}" class="menu-img">
                        <label>${menu.food} (${menu.price.toLocaleString()} 원)</label>
                        <input type="number" id="amount-${menu.menu_id}" 
                            name="amount[${menu.menu_id}]" 
                            value="${amount}" 
                            min="0" 
                            oninput="updateTotalPrice()">`;  // 실시간 갱신
                    menuContainer.appendChild(menuItem);
                });
                updateTotalPrice();
            })
            .catch(error => {
                console.error('Error loading menu:', error);
                menuContainer.innerHTML = `<p>메뉴를 불러오는 데 실패했습니다. 에러 메시지: ${error.message}</p>`;
            });
    } else {
        menuContainer.innerHTML = '<p>식당 ID가 없습니다.</p>';
    }
}

function loadMenu() {
    const restId = "<?php echo $row['restaurant']; ?>"; 
    const orderId = "<?php echo $row['order_id']; ?>"; 
    const menuContainer = document.getElementById('menu-container');
    menuContainer.innerHTML = '';

    if (restId) {
        fetch(`menu_loader.php?rest_id=${restId}&order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(menu => {
                    menuPrices[menu.menu_id] = menu.price;

                    // amount를 동적으로 가져와 기본값으로 설정
                    const amount = menu.amount || 0;

                    const menuItem = document.createElement('div');
                    menuItem.className = 'menu-item';
                    menuItem.innerHTML = `
                        <img src="../../images/${menu.img}" alt="${menu.food}" class="menu-img">
                        <label>${menu.food} (${menu.price.toLocaleString()} 원)</label>
                        <input type="number" id="amount-${menu.menu_id}" 
                            name="amount[${menu.menu_id}]" 
                            value="${amount}" 
                            min="0" 
                            oninput="updateTotalPrice()">`;  // 실시간 갱신
                    menuContainer.appendChild(menuItem);
                });
                updateTotalPrice(); // 메뉴 로드 후 최초 총 금액 계산
            })
            .catch(error => {
                console.error('Error loading menu:', error);
                menuContainer.innerHTML = `<p>메뉴를 불러오는 데 실패했습니다. 에러 메시지: ${error.message}</p>`;
            });
    } else {
        menuContainer.innerHTML = '<p>식당 ID가 없습니다.</p>';
    }
}

function updateTotalPrice() {
    let myPrice = 0;
    let totalPrice = currentMoney - initialPrice;

    // 각 메뉴 항목에 대해 가격과 수량을 계산
    for (const menuId in menuPrices) {
        const amount = parseInt(document.getElementById(`amount-${menuId}`).value || 0);
        myPrice += menuPrices[menuId] * amount;  // 메뉴 가격 * 수량
    }

    // 총 금액을 계산할 때 initialPrice를 수정하지 않음
    totalPrice += myPrice;

    // 목표 금액에 따른 남은 금액 계산
    let remainingToGoal = goalMoney !== null ? Math.max(0, goalMoney - totalPrice) : null;

    // 목표 금액 정보 표시
    let goalText = goalMoney !== null
        ? `목표 금액까지 ${remainingToGoal.toLocaleString()} 원`
        : "목표 금액 없음";

    // UI 업데이트
    let change = myPrice - initialPrice;
    let changeText = '';
    if (change > 0) {
        changeText = ` (${change.toLocaleString()} 원 추가 결제 예정)`;
    } else if (change < 0) {
        let changeAbs = Math.abs(change)
        changeText = ` (${changeAbs.toLocaleString()} 원 환불 예정)`;
    }

    document.getElementById('total-price').innerHTML = `
        지불한 금액: ${initialPrice.toLocaleString()} 원 <br>
        내 주문 금액: ${myPrice.toLocaleString()}${changeText} <br>
        현재 주문 금액: ${totalPrice.toLocaleString()} 원 (${goalText})
    `;

    // 숨겨진 input 값 업데이트
    document.getElementById('my-price').value = myPrice;
    document.getElementById('total-price-hidden').value = totalPrice;
    document.getElementById('change').value = change;
}



    </script>
    <style>
        /* 기존 스타일 그대로 유지 */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }

        .input-group {
            margin-bottom: 1rem;
        }
        .input-group select,
        .input-group input {
            width: 100%;
            padding: 0.75rem;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .input-group select {
            background-color: #f9f9f9;
        }

        #restaurant {
            font-weight: bold;
            font-size: 24px;
            text-align: center;
        }

        /* 메뉴 박스 스타일 */
        .menu-box {
            display: flex;
            flex-direction: column;
            gap: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #fff;
        }

        .menu-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 10px; 
            padding-left: 20px;
        }

        .menu-img {
            width: 50px;
            height: 50px;
            margin-right: 10px;
            border-radius: 5px;
            object-fit: cover;
        }

        .menu-item label { 
            flex: 1; 
            margin-right: 10px; 
        }

        .menu-item input { 
            width: 60px; /* 수량 입력 칸의 너비 */ 
            text-align: right; 
            padding: 6px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
        }   
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Order</h2>

    <form action="edit.php" method="post">
        <!-- 식당 선택 및 표시 -->
        <div class="input-group">
            <p id="restaurant"><strong><?= htmlspecialchars($row['name']) ?></strong></p>
            <input type="hidden" id="restaurant-id" name="rest_id" value="<?= htmlspecialchars($row['restaurant']) ?>"> 
        </div>

        <!-- 메뉴 섹션 -->
        <div id="menu-container" class="menu-box"></div>

        <!-- 현재 금액 -->
        <div class="input-group">
            <div id="total-price" style="margin-top: 10px;">
                현재 금액: <?= htmlspecialchars($row['current_money'] ?? '0') ?>원
                (목표 금액  <?= htmlspecialchars($row['goal_money']) ? htmlspecialchars($row['goal_money']) : "목표 금액 없음" ?>원)
            </div>
        </div>



        <input type="hidden" id="total-price-hidden" name="total_price" value="0"> <!-- totalPrice 값 -->
        <input type="hidden" id="my-price" name="my_price" value="0"> <!-- myPrice 값 -->
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id) ?>"> <!-- order_id 값 -->
        <input type="hidden" id="change" name="change" value="0"> <!-- 차액 -->

        <div class="input-group">
            <button type="submit" class="submit-btn">Edit Order</button>
        </div>
    </form>
</div>
</body>
</html>
