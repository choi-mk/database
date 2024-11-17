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
        function loadMenu() {
            const restId = document.getElementById('restaurant').value;
            const menuSelect = document.getElementById('menu');
            menuSelect.innerHTML = '<option value="">메뉴 로드 중...</option>';

            if (restId) {
                fetch(`menu_loader.php?rest_id=${restId}`)
                    .then(response => response.json())
                    .then(data => {
                        menuSelect.innerHTML = '<option value="">메뉴를 선택하세요</option>';
                        data.forEach(menu => {
                            const option = document.createElement('option');
                            option.value = menu.menu_id;
                            option.textContent = menu.food;
                            menuSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading menu:', error);
                        menuSelect.innerHTML = '<option value="">메뉴를 불러오는 데 실패했습니다</option>';
                    });
            } else {
                menuSelect.innerHTML = '<option value="">먼저 식당을 선택하세요</option>';
            }
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
        .form-group input {
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
    </style>
</head>
<body>
<div class="neworder-form">
    <h2>New Order</h2>

    <form action="neworder.php" method="post">
        <div class="form-group">
            <label for="restaurant">주문할 식당</label>
            <select id="restaurant" name="restaurant" required onchange="loadMenu()">
                <option value="">식당을 선택하세요</option>
                <?php foreach ($restaurants as $restaurant): ?>
                    <option value="<?= htmlspecialchars($restaurant['rest_id']) ?>">
                        <?= htmlspecialchars($restaurant['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="menu">주문할 메뉴</label>
            <select id="menu" name="menu" required>
                <option value="">먼저 식당을 선택하세요</option>
            </select>
        </div>

        <div class="form-group">
            <label for="time">주문 마감 시간</label>
            <input type="datetime-local" id="time" name="time" required>
        </div>

        <div class="form-group">
            <label for="goal_money">목표 금액</label>
            <input type="number" id="goal_money" name="goal_money" required>
        </div>

        <div class="form-group">
            <label for="amount">수량</label>
            <input type="number" id="amount" name="amount" required>
        </div>

        <div class="form-group">
            <input type="submit" value="New Order">
        </div>
    </form>
</div>
</body>
</html>