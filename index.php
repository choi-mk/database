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
    <title>MoJu - 모두의 주문</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sour+Gummy&display=swap">
    <link href="https://fonts.googleapis.com/css?family=Gothic+A1:100,600,700" rel="stylesheet">
    <link rel="stylesheet" href="basic_style.css"> <!-- CSS 파일 경로 확인 -->
    <style>
        /* 추가 스타일 */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #FFDEE9, #B5FFFC);
            color: #333;
        }
        header {
            background-color: #89A8B2;
            color: white;
            text-align: center;
            padding: 1rem 0;
        }
        
        .content {
            text-align: center;
            padding: 2rem 1rem;
            animation: fadeIn 2s ease-in-out;
        }
        .content h1 {
            font-family: 'Gothic A1', sans-serif; /* 글씨체 적용 */
            font-size: 2rem;
            margin: 2rem 0;
        }
        .hero-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            display: block;
            margin-bottom: 1rem;
        }
        .cta-button {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            margin: 1rem 0.5rem;
            font-size: 1.2rem;
            font-weight: bold;
            color: white;
            background-color: #00a183;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .cta-button:hover {
            background-color: #006354;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.9rem;
            color: #555;
        }
    </style>
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
    <h1>MoJu - 모두의 주문</h1>
    <p>가까운 이웃과 함께 주문하는 즐거움!</p>
    <p>비싼 배달비 걱정 없는 새로운 배달 경험을 시작해보세요.</p>
    <p>MoJu와 함께 오늘도 행복한 주문 생활을 즐겨보세요!</p>
    <a href="restaurants/restaurants.html" class="cta-button">지금 주문 시작하기</a>
    <a href="signin/signin.html" class="cta-button">회원 가입하기</a>
</div>

<footer>
    <p>&copy; 2024 MoJu - 모두의 주문. All rights reserved.</p>
</footer>

</body>
</html>
