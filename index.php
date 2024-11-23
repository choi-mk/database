<?php
session_start();  // 세션 시작

// 로그인 상태 확인
$is_logged_in = isset($_SESSION['phone']);  // 세션에 phone이 저장되어 있으면 로그인 상태
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sour+Gummy&display=swap">
    <link rel="stylesheet" href="basic_style.css"> <!-- CSS 파일 경로 확인 -->
</head>
<body>

<header>
    <a href="index.php" class="header-link">
        <h1>MoJu</h1>
    </a>
</header>

<nav>
    <?php if ($is_logged_in): ?>
        <!-- 로그인 상태일 때 -->
        <a href="restaurants/restaurants.html">Restaurants</a>
        <a href="orders/orders.html">Orders</a>
        <a href="signin/logout.php">Logout</a>
        
        <!-- 오른쪽 위에 위치할 닉네임 블록 -->
        <div class="nickname-block">
            <button id="nickname-button" class="nickname-button" 
                onclick="window.location.href='mypage/mypage.php'">
                <?php echo htmlspecialchars($_SESSION['nickname']); ?> 님
            </button>
        </div>

    <?php else: ?>
        <!-- 로그인 안 했을 때 -->
        <a href="restaurants/restaurants.html">Restaurants</a>
        <a href="orders/orders.html">Orders</a>
        <a href="signin/signin.html">Sign In/Up</a>
    <?php endif; ?>
</nav>

<div class="content">
    <h2>Content goes here</h2>
    <p>This is the home page of the website.</p>
</div>

</body>
</html>
