<?php
session_start();  // 세션 시작

// 로그인 상태 확인
$is_logged_in = isset($_SESSION['phone']);  // 전화번호가 세션에 저장되어 있으면 로그인 상태
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sour+Gummy&display=swap">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .header {
        background-color: #333;
        color: white;
        height: 100px; /* header 크기 50px로 설정 */
        display: flex; /* flexbox로 중앙 정렬 */
        justify-content: center; /* 수평 중앙 정렬 */
        align-items: center; /* 수직 중앙 정렬 */
        text-align: center; /* 텍스트 수평 중앙 정렬 */
        }
        .header h1 {
            font-family: 'Sour Gummy';
            font-size: 40px; /* 글자 크기 조정 (기본값보다 작게 설정) */
            margin: 0; /* h1 태그의 기본 마진을 제거 */
        }

        nav {
            background: #555;
            color: #fff;
            display: flex;
            justify-content: center;
            padding: 10px 0;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            font-family: 'Sour Gummy';
            font-size: 20px;
            font-weight: 100;
        }

        nav a:hover {
            text-decoration: underline;
        }
        .nickname-block {
            color: white;
            font-weight: bold;
            background-color: #444;
            padding: 5px 10px;
            border-radius: 5px;
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 12px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>MoJu</h1>
</div>

<nav>
    <?php if ($is_logged_in): ?>
        <!-- 로그인 상태일 때 -->
        <a href="restaurants/restaurants.html">Restaurants</a>
        <a href="orders/orders.html">Orders</a>
        <a href="signin/logout.php">Logout</a>
        
        <!-- 오른쪽 위에 위치할 닉네임 블록 -->
        <div class="nickname-block">
            <?php echo $_SESSION['nickname']; ?> 님
        </div>
    <?php else: ?>
        <!-- 로그인 안 했을 때 -->
        <a href="restaurants/restaurants.html">Restaurant</a>
        <a href="orders/orders.html">Order</a>
        <a href="signin/signin.html">Sign In/Up</a>
    <?php endif; ?>
</nav>

<div class="content">
    <h2>Content goes here</h2>
    <p>This is the home page of the website.</p>
</div>

</body>
</html>
