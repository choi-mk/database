<?php
session_start();
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
    "SELECT r.rest_id, r.name 
    FROM restbl r
    JOIN deliverable d ON r.rest_id = d.rest_id
    WHERE d.phone = ?"
);
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
    <title>Join Order</title>
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
        .joinorder-container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .joinorder-container h2 {
            margin-bottom: 1rem;
        }
        .input-group {
            margin-bottom: 1rem;
        }
        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 14px;
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
        .submit-btn {
            width: 100%;
            padding: 0.75rem;
            font-size: 16px;
            color: #fff;
            background-color: #333;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .submit-btn:hover {
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
<div class="joinorder-container">
    <h2>Join Order</h2>

    <form action="newjoin.php" method="post">
        <!-- 식당 선택 및 표시 -->
        <div class="input-group">
            <label for="restaurant">주문할 식당</label>
            <?php if ($selected_rest_id): ?>
                <p id="restaurant"><?= htmlspecialchars($restaurants[0]['name']) ?></p>
            <?php endif; ?>
        </div>


        <!-- 메뉴 선택 -->
        <div class="input-group">
            <label for="menu">주문할 메뉴</label>
            <select id="menu" name="menu" required>
                <option value="">메뉴를 선택하세요</option>
            </select>
        </div>

        <!-- 주문 마감 시간 -->
        <div class="input-group">
            <label for="time">주문 마감 시간</label>
            <input type="datetime-local" id="time" name="time" required>
        </div>

        <!-- 목표 금액 -->
        <div class="input-group">
            <label for="goal_money">목표 금액</label>
            <p id="goal_money"><?= htmlspecialchars($selected_goal_id) ? htmlspecialchars($selected_goal_id) : "목표 금액 없음" ?></p>

        </div>


        <!-- 수량 -->
        <div class="input-group">
            <label for="amount">현재 금액</label>
            <input type="number" id="amount" name="amount" required>
        </div>

        <!-- 제출 버튼 -->
        <div class="input-group">
            <button type="submit" class="submit-btn">Join Order</button>
        </div>
    </form>
</div>
</body>
</html>
