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

// restaurant 페이지에서 연결된 경우
$rest_id = isset($_GET['rest_id']) ? htmlspecialchars($_GET['rest_id']) : null;

if ($rest_id) {
    // 전달된 rest_id의 유효성 확인
    $stmt = $conn->prepare("SELECT name FROM restbl WHERE rest_id = ?");
    $stmt->bind_param("i", $rest_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $restaurant = $result->fetch_assoc();
        $selected_restaurant_name = $restaurant['name'];
    } else {
        $error_message = "Invalid Restaurant ID.";
    }
    $stmt->close();
}

// 식당 목록 가져오기
$stmt = $conn->prepare(
    "SELECT r.rest_id, r.name, r.minprice
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
    <link rel="stylesheet" href="../../basic_style.css">
    <title>New Order</title>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const restId = "<?= $rest_id ?? '' ?>"; // PHP에서 전달된 rest_id

            if (restId) {
                const restaurantSelect = document.getElementById("restaurant");
                restaurantSelect.value = restId; // 전달된 rest_id를 선택값으로 설정
                loadMenu(); // 선택된 레스토랑의 메뉴 로드
            }
        });
        let menuPrices = {};

        function loadMenu() {
            const restId = document.getElementById('restaurant').value;
            const menuContainer = document.getElementById('menu-container');
            menuContainer.innerHTML = '';

            if (restId) {
                fetch(`menu_loader.php?rest_id=${restId}`)
                    .then(response => response.json())
                    .then(data => {
                        menuContainer.innerHTML = '<strong>Menu</strong>';
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
                menuContainer.innerHTML = '<p>먼저 식당을 선택하세요.</p>';
            }
        }

        function updateTotalPrice() {
            let totalPrice = 0;
            for (const menuId in menuPrices) {
                const amount = parseInt(document.getElementById(`amount-${menuId}`)?.value || 0);
                totalPrice += menuPrices[menuId] * amount;
            }
            document.getElementById('total-price').innerHTML = `Total Price: ${totalPrice.toLocaleString()} 원`;
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
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
    <h2>New Order</h2>

    <form action="neworder.php" method="post">
        <div class="input-group">
            <label for="restaurant"><h3>Restaurant to Order</h3></label>
            <select id="restaurant" name="restaurant" required onchange="loadMenu()">
                <option value="">Choose Restaurant</option>
                <?php foreach ($restaurants as $restaurant): ?>
                    <option value="<?= htmlspecialchars($restaurant['rest_id']) ?>"
                        <?= ($restaurant['rest_id'] == $rest_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($restaurant['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="menu-container"></div>

        <div id="total-price" style="margin-top: 10px; font-weight: bold;">Total Price: 0 원</div>

        <div class="form-group">
            <label for="time"><br>Order Closing Time</label>
            <input type="datetime-local" id="time" name="time" required>
        </div>
        <?php $minprice = $restaurant['minprice']; ?>

        <div class="form-group">
            <label for="goal_money">Goal Money</label>
            <input type="number" id="goal_money" name="goal_money" required min="<?= htmlspecialchars($minprice) ?>" 
                placeholder="Enter goal money (min: <?= htmlspecialchars($minprice) ?>)">
        </div>
        <div class="form-group">
            <label for="goal_money">Delivery Address</label>
            <input type="text" id="delivery_address" name="delivery_address" required>
        </div>
        <button type="submit" class="submit-btn">New Order</button>
    </form>
</div>
</body>
</html>
