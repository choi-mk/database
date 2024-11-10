<?php
// 로그인 처리 후
session_start();
//$_SESSION['phone'] = $phone; // 로그인한 사용자의 ID 저장
// 데이터베이스 연결
$servername = "termproject.c3qoysmqqna6.ap-northeast-2.rds.amazonaws.com";
$username = "admin";
$password = "00000000";
$dbname = "moju";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 로그인 폼에서 받은 데이터
$phone = $_POST['phone'];

// SQL 쿼리로 데이터베이스에서 사용자 검색
$sql = "SELECT * FROM memtbl WHERE phone = '$phone'";
$result = $conn->query($sql);

// 사용자가 존재하면 세션에 사용자 정보 저장 후 로그인 성공
if ($result->num_rows > 0) {
    $_SESSION['phone'] = $phone;
    echo "로그인 성공!<br>";
    echo "<a href='welcome.php'>홈페이지로 이동</a>";
} else {
    echo "전화번호가 잘못되었습니다.";
}

$conn->close();











?>
