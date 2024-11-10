<?php
$servername = "termproject.c3qoysmqqna6.ap-northeast-2.rds.amazonaws.com";
$username = "admin";
$password = "00000000";
$dbname = "moju";

// DB 연결
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// signin 상태인지 확인
ob_start();
session_start();

if (isset($_SESSION['user_id'])) {
    echo "Logged in as phone number: " . htmlspecialchars($_SESSION['phone']);
} else {
    header("Location: signup.php");
    exit;
}

ob_end_flush();
// 로그인 된 사용자의 배달가능 레스토랑 필터링
// SQL 쿼리 준비
$sql = "INSERT INTO users (name, nickname, account, address, phone) VALUES ('$name', '$nickname', '$account', '$address', '$phone')";

// 쿼리 실행
if ($conn->query($sql) === TRUE) {
    echo "회원가입이 완료되었습니다!";
} else {
    echo "에러: " . $sql . "<br>" . $conn->error;
}

// 연결 종료
$conn->close();
?>
