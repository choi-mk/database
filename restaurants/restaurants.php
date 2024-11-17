<?php
header('Content-Type: application/json');

// login 확인
session_start();

if (!isset($_SESSION['phone'])) {
    // 로그인되지 않은 경우 리디렉션 정보 반환
    echo json_encode(["redirect" => "../signin/signin.html"]);
    exit;
}

$servername = "termproject.c3qoysmqqna6.ap-northeast-2.rds.amazonaws.com";
$username = "admin";
$password = "00000000";
$dbname = "moju";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$phone = $_SESSION['phone'];

$sql = "SELECT address1, address2, address3 FROM memtbl WHERE phone = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();
$userAddress = $result->fetch_assoc();

if ($userAddress) {
    $address1 = $userAddress['address1'];
    $address2 = $userAddress['address2'];
    $address3 = $userAddress['address3'];

    $sql = "SELECT name, address1, address2, address3 FROM restbl WHERE address1 = ? AND address2 = ? AND address3 = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $address1, $address2, $address3);
    $stmt->execute();
    $result = $stmt->get_result();

    $restaurants = [];
    while ($row = $result->fetch_assoc()) {
        $restaurants[] = $row;
    }

    if (count($restaurants) > 0) {
        echo json_encode($restaurants);
    } else {
        echo json_encode(["message" => "배달가능한 식당이 없습니다"]);
    }
} else {
    echo json_encode(["error" => "User address not found"]);
}

$conn->close();
?>