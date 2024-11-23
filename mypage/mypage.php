<?php
session_start();

// 데이터베이스 연결 정보
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

// 세션에서 사용자 ID 가져오기 (예: phone을 식별자로 사용)
$phone = $_SESSION['phone'];

// 사용자 정보 가져오기
$sql = "SELECT * FROM memtbl WHERE phone = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit;
}

// 정보 업데이트 처리
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $nickname = $_POST['nickname'];
    $account = $_POST['account'];

    $address1 = $_POST['address1'];
    $address2 = $_POST['address2'];
    $address3 = $_POST['address3'];

    $update_sql = "UPDATE memtbl SET name = ?, nickname = ?, account = ?, address1 = ?, address2 = ?, address3 = ? WHERE phone = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssss", $name, $nickname, $account, $address1, $address2, $address3, $phone);
    
    if ($update_stmt->execute()) {
        $success_message = "Profile updated successfully.";
        // 업데이트 후 사용자 정보 다시 가져오기
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $error_message = "Error updating profile: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sour+Gummy&display=swap">
    <link rel="stylesheet" href="../basic_style.css">
    <title>My Page</title>
    
    <style>
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            
        }
        .submit-btn {
            width: 100%;
            padding: 0.75rem;
            font-size: 16px;
            color: var(--subtitle-text-color);
            background-color: var(--subtitle-color);
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background-color: var(--title-color);
        }

        .input-group input {
            margin-bottom: 5px; /* 입력 필드 간 간격 설정 */
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
                    onclick="window.location.href='mypage.php'">
                    <?php echo htmlspecialchars($_SESSION['nickname']); ?> 님
                </button>
            </div>
    </header>

    <nav>
        <a href="../restaurants/restaurants.html" class="current-button">Restaurants</a>
        <a href="../orders/orders.html" class="orders-button">Orders</a>
        <a href="../signin/logout.php" class="logout-button">Logout</a>
    </nav>
    <div class="form-container">
        <h2>My Page</h2>
        
        <?php if (isset($success_message)): ?>
            <div class="message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="input-group">
            <label for="phone">PhoneNumber:</label>
            <span id="phone"><?php echo htmlspecialchars($user['phone']); ?></span>
        </div>
        
        <h3>Update Profile</h3>
        <form method="POST" action="">
            <div class="input-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="input-group">
                <label for="nickname">Nickname:</label>
                <input type="nickname" id="nickname" name="nickname" value="<?php echo htmlspecialchars($user['nickname']); ?>" required>
            </div>
            
            <div class="input-group">
                <label for="account">Account:</label>
                <input type="account" id="account" name="account" value="<?php echo htmlspecialchars($user['account']); ?>" required>
            </div>
            <div class="input-group">
                <label for="address1">Address:</label>
                <input type="text" id="address1" name="address1" value="<?php echo htmlspecialchars($user['address1']); ?>" required>
                <input type="text" id="address2" name="address2" value="<?php echo htmlspecialchars($user['address2']); ?>" required>
                <input type="text" id="address3" name="address3" value="<?php echo htmlspecialchars($user['address3']); ?>" required>
            </div>
            <div class="input-group">
                <button class="submit-btn">Update</button>
            </div>
        </form>
    </div>
</body>
</html>