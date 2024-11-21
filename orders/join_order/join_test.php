<?php
// POST 데이터 확인
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<pre>';
    print_r($_POST); // 모든 POST 데이터를 출력
    echo '</pre>';
    exit; // 데이터 확인 후 추가 실행 방지
}