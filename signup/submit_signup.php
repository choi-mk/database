<?php
// 사용자가 제출한 데이터를 가져옵니다
$name = $_POST['name'];
$nickname = $_POST['nickname'];
$account = $_POST['account'];
$address = $_POST['address'];
$phone = $_POST['phone'];

// 데이터 유효성 검사 (예: 비어있는 필드가 없는지 확인)
if (empty($name) || empty($nickname) || empty($account) || empty($address) || empty($phone)) {
    echo "모든 필드를 입력해주세요.";
    exit;
}

// 예시: MySQL 데이터베이스에 데이터 저장 (DB 연결 코드 추가 필요)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_database";

// DB 연결
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
