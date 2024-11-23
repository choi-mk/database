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
$nickname = $_SESSION['nickname'];


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

    // 레스토랑 정보 가져오기
    $sql = "SELECT rest_id, name, address1, address2, address3, img FROM restbl WHERE address1 = ? AND address2 = ? AND address3 = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $address1, $address2, $address3);
    $stmt->execute();
    $result = $stmt->get_result();

    $restaurants = [];
    while ($row = $result->fetch_assoc()) {
        // 각 레스토랑의 메뉴 가져오기
        $rest_id = $row['rest_id'];
        $sql_menu = "SELECT food, price FROM menutbl WHERE rest_id = ?";
        $stmt_menu = $conn->prepare($sql_menu);
        $stmt_menu->bind_param("i", $rest_id);
        $stmt_menu->execute();
        $menu_result = $stmt_menu->get_result();

        $menu = [];
        while ($menu_item = $menu_result->fetch_assoc()) {
            $menu[] = $menu_item;
        }

        // 메뉴를 레스토랑 정보에 추가
        $row['menu'] = $menu;

        $restaurants[] = $row;
    }

    if (count($restaurants) > 0) {
        echo json_encode([
            'nickname' => $nickname,
            'restaurants' => $restaurants
        ]);
    } else {
        echo json_encode(["message" => "배달가능한 식당이 없습니다"]);
    }
} else {
    echo json_encode(["error" => "User address not found"]);
}

$conn->close();
?>
