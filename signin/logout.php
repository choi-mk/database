<?php
session_start();
session_unset();
session_destroy();

echo "로그아웃 되었습니다. <a href='login.html'>로그인 페이지로</a>";
?>
