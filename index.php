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
            padding: 15px;
            text-align: center;
        }
        .header a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }
        .header a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Moju</h1>

    <?php if ($is_logged_in): ?>
        <!-- 로그인 상태일 때 -->
        <a href="restaurants/restaurants.html">Restaurant</a>
        <a href="order.php">Order</a>
        <a href="signin/logout.php">Log Out</a>
    <?php else: ?>
        <!-- 로그인 안 했을 때 -->
        <a href="signin/signin.html">Sign In</a>
        <a href="signup/signup.html">Sign Up</a>
    <?php endif; ?>
</div>

<div class="content">
    <h2>Content goes here</h2>
    <p>This is the home page of the website.</p>
</div>

</body>
</html>
