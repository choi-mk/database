<?php
session_start();

// 로그인 여부 확인
if (!isset($_SESSION['phone'])) {
    echo "로그인 후 이용할 수 있습니다. <a href='login.html'>로그인 페이지로</a>";
    exit;
}

echo "안녕하세요, " . $_SESSION['nickname'] . "님!";
echo "<br><a href='logout.php'>로그아웃</a>";
?>
