<?php
session_start();
session_unset();
session_destroy();

// 로그아웃 후 index.html로 리디렉션
header("Location: ../index.html");
exit();
?>
