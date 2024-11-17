<?php
// 사용자가 제출한 데이터를 가져옵니다
$name = $_POST['name'];
$nickname = $_POST['nickname'];
$account = $_POST['account'];
$address1 = $_POST['address1'];
$address2 = $_POST['address2'];
$address3 = $_POST['address3'];
$phone = $_POST['phone'];

// 데이터 유효성 검사 (예: 비어있는 필드가 없는지 확인)
if (empty($name) || empty($nickname) || empty($account) || empty($address1) || empty($address2) || empty($address3) || empty($phone)) {
    $error = "모든 필드를 입력해주세요.";
    header("Location: signup_form.php?error=" . urlencode($error));
    exit;
}

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

// 핸드폰 번호 중복 확인 쿼리
$checkSql = "SELECT * FROM memtbl WHERE phone = '$phone'";
$result = $conn->query($checkSql);

if ($result->num_rows > 0) {
    // 중복된 경우 메시지 전달
    $error = "이미 가입된 전화번호입니다. 다른 번호를 입력해주세요.";
    header("Location: signup_form.php?error=" . urlencode($error));
    $conn->close();
    exit;
}

// SQL 쿼리 준비 및 실행
$sql = "INSERT INTO memtbl (name, nickname, account, address1, address2, address3, phone) VALUES ('$name', '$nickname', '$account', '$address1', '$address2', '$address3', '$phone')";

if ($conn->query($sql) === TRUE) {
    // 회원가입 성공 후 index.php로 리디렉션
    header("Location: ../index.php");
    exit;
} else {
    $error = "에러: " . $conn->error;
    header("Location: signup_form.php?error=" . urlencode($error));
}

// 연결 종료
$conn->close();
?>
