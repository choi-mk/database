<?php
session_start();
$order_id =  isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : null;
$selected_rest_id = isset($_GET['rest_id']) ? htmlspecialchars($_GET['rest_id']) : null;
$selected_goal_id = isset($_GET['goal_id']) ? htmlspecialchars($_GET['goal_id']) : null;
$selected_cur_id = isset($_GET['cur_id']) ? htmlspecialchars($_GET['cur_id']) : null;
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
    "SELECT r.name FROM restbl r WHERE r.rest_id = ?"
);
$stmt->bind_param("i", $selected_rest_id);
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
    <title>Join Order</title>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // 페이지가 로드되면 메뉴를 자동으로 로드
            loadMenu();
            updateTotalPrice();  // 초기 총 금액 업데이트
        });
        let selectedCurId = <?= htmlspecialchars($selected_cur_id ?? '0') ?>;
        let selectedGoalId = <?= htmlspecialchars($selected_goal_id ?? 'null') ?>;
        let menuPrices = {};

        function loadMenu() {
            const restId = "<?= $selected_rest_id ?>"; 
            const menuContainer = document.getElementById('menu-container');
            menuContainer.innerHTML = '';
        
            if (restId) {
                fetch(`menu_loader.php?rest_id=${restId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(menu => {
                            menuPrices[menu.menu_id] = menu.price;
                        
                            const menuItem = document.createElement('div');
                            menuItem.className = 'menu-item';
                            menuItem.innerHTML = `
                                <img src="../../images/${menu.img}" alt="${menu.food}" class="menu-img">
                                <label>${menu.food} (${menu.price.toLocaleString()} 원)</label>
                                <input type="number" id="amount-${menu.menu_id}" name="amount[${menu.menu_id}]" value="0" min="0" oninput="updateTotalPrice()">
                            `;
                            menuContainer.appendChild(menuItem);
                        });
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
            let totalPrice = selectedCurId;
            for (const menuId in menuPrices) {
                const amount = parseInt(document.getElementById(`amount-${menuId}`)?.value || 0);
                totalPrice += menuPrices[menuId] * amount;
            }

            let myPrice = totalPrice - selectedCurId;
            let remainingToGoal = selectedGoalId !== null ? Math.max(0, selectedGoalId - totalPrice) : null;

            let goalText = selectedGoalId !== null 
                ? `목표 금액까지 ${remainingToGoal.toLocaleString()} 원` 
                : "목표 금액 없음";

            document.getElementById('total-price').innerHTML = `
                지불할 금액: ${myPrice.toLocaleString()} 원 <br>
                현재 주문 금액: ${totalPrice.toLocaleString()} 원 (${goalText})
            `;
            document.getElementById('my-price').value = myPrice;
            document.getElementById('total-price-hidden').value = totalPrice;
        }



    </script>
    <style>
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
    <h2>Join Order</h2>

    <form action="join.php" method="post" >
        <!-- 식당 선택 및 표시 -->
        <!-- HTML 내 Restaurant Section -->
        <div class="input-group">
            <?php if ($selected_rest_id): ?>
                <p id="restaurant"><strong><?= htmlspecialchars($restaurant) ?></strong></p>
                <input type="hidden" name="rest_id" value="<?= htmlspecialchars($selected_rest_id) ?>">
            <?php endif; ?>
        </div>

        <!-- 메뉴 섹션 -->
        <div class="input-group">
            <div id="menu-container" class="menu-box">
            </div>
        </div>

        
        <!-- 현재 금액 -->
        <div class="input-group">
            <div id="total-price" style="margin-top: 10px;">
                현재 금액: <?= htmlspecialchars($selected_cur_id ?? '0') ?>원
                (목표 금액  <?= htmlspecialchars($selected_goal_id) ? htmlspecialchars($selected_goal_id) : "목표 금액 없음" ?>원)
            </div>
        </div>

        <input type="hidden" id="total-price-hidden" name="total_price" value="0"> <!-- totalPrice 값 -->
        <input type="hidden" id="my-price" name="my_price" value="0"> <!-- myPrice 값 -->
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id) ?>"> <!-- order_id 값 -->

        <!-- 제출 버튼 -->
        <div class= "input-group" >
            <button type="join" class="submit-btn">Join Order</button>
        </div>
    </form>
</div>
</body>
</html>
