<?php
session_start();

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
    "SELECT r.rest_id, r.name 
    FROM restbl r
    JOIN deliverable d ON r.rest_id = d.rest_id
    WHERE d.phone = ?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

$restaurants = [];
while ($row = $result->fetch_assoc()) {
    $restaurants[] = $row;
}
$stmt->close();
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order</title>
    <script>
        let menuPrices = {};

        function loadMenu() {
            const restId = document.getElementById('restaurant').value;
            const menuContainer = document.getElementById('menu-container');
            menuContainer.innerHTML = '';

            if (restId) {
                fetch(`menu_loader.php?rest_id=${restId}`)
                    .then(response => response.json())
                    .then(data => {
                        menuContainer.innerHTML = '<strong>메뉴</strong>';
                        data.forEach(menu => {
                            menuPrices[menu.menu_id] = menu.price;
                        
                            const menuItem = document.createElement('div');
                            menuItem.className = 'menu-item';
                            menuItem.innerHTML = `
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
                menuContainer.innerHTML = '<p>먼저 식당을 선택하세요.</p>';
            }
        }

        function updateTotalPrice() {
            let totalPrice = 0;
            for (const menuId in menuPrices) {
                const amount = parseInt(document.getElementById(`amount-${menuId}`)?.value || 0);
                totalPrice += menuPrices[menuId] * amount;
            }
            document.getElementById('total-price').innerHTML = `총 가격: ${totalPrice.toLocaleString()} 원`;
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

        .signup-form { 
            width: 300px; 
            padding: 20px; 
            background-color: #fff; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
            border-radius: 8px; 
        }

        .signup-form h2 { 
            text-align: center; 
            margin-bottom: 20px; 
        }

        .form-group { 
            margin-bottom: 15px; 
        }

        .form-group label { 
            display: block; 
            margin-bottom: 5px; 
        }

        .form-group input, 
        .form-group select { 
            width: 100%; 
            padding: 8px; 
            box-sizing: border-box; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
        }

        .form-group input[type="submit"] { 
            background-color: #333; 
            color: #fff; 
            border: none; 
            cursor: pointer; 
            font-weight: bold; 
        }

        .form-group input[type="submit"]:hover { 
            background-color: #555; 
        }

        .error-message { 
            color: red; 
            margin-bottom: 15px; 
            text-align: center; 
        }

        .menu-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 10px; 
            padding-left: 20px;
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
<div class="neworder-form">
    <h2>New Order</h2>

    <form action="neworder.php" method="post">
        <div class="form-group">
            <label for="restaurant"><h3>주문할 식당</h3></label>
            <select id="restaurant" name="restaurant" required onchange="loadMenu()">
                <option value="">식당을 선택하세요</option>
                <?php foreach ($restaurants as $restaurant): ?>
                    <option value="<?= htmlspecialchars($restaurant['rest_id']) ?>">
                        <?= htmlspecialchars($restaurant['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="menu-container"></div>

        <div id="total-price" style="margin-top: 10px; font-weight: bold;">총 가격: 0 원</div>

        <div class="form-group">
            <label for="time"><br>주문 마감 시간</label>
            <input type="datetime-local" id="time" name="time" required>
        </div>
        <div class="form-group">
            <label for="goal_money">목표 금액</label>
            <input type="number" id="goal_money" name="goal_money" required>
        </div>
        <div class="form-group">
            <input type="submit" value="New Order">
        </div>
    </form>
</div>
</body>
</html>