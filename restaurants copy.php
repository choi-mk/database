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

    $sql = "SELECT rest_id, name, address1, address2, address3, img FROM restbl WHERE address1 = ? AND address2 = ? AND address3 = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $address1, $address2, $address3);
    $stmt->execute();
    $result = $stmt->get_result();

    $restaurants = [];
    while ($row = $result->fetch_assoc()) {
        $rest_id = $row['rest_id'];

        // 메뉴 정보를 가져오기 위한 쿼리
        $menuSql = "SELECT menu_name FROM menutbl WHERE rest_id = ?";
        $menuStmt = $conn->prepare($menuSql);
        $menuStmt->bind_param("i", $rest_id);
        $menuStmt->execute();
        $menuResult = $menuStmt->get_result();

        $menus = [];
        while ($menuRow = $menuResult->fetch_assoc()) {
            $menus[] = $menuRow['menu_name'];
        }

        // 레스토랑 정보에 메뉴 추가
        $row['menu'] = $menus;
        
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