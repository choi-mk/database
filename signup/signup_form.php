<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .signup-form {
            width: 300px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .signup-form h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group input[type="submit"] {
            background-color: #333;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .form-group input[type="submit"]:hover {
            background-color: #555;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="signup-form">
    <h2>Sign Up</h2>
    
    <!-- 에러 메시지 출력 -->
    <?php if (isset($_GET['error'])): ?>
        <div class="error-message">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form action="signup.php" method="post">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="nickname">Nickname</label>
            <input type="text" id="nickname" name="nickname" required>
        </div>
        <div class="form-group">
            <label for="account">Account Number</label>
            <input type="text" id="account" name="account" pattern="\d+" title="숫자만 입력 가능합니다" required>
        </div>
        <div class="form-group">
            <label for="address1">시/군/구</label>
            <input type="text" id="address1" name="address1" required>
        </div>
        <div class="form-group">
            <label for="address2">읍/면</label>
            <input type="text" id="address2" name="address2" required>
        </div>
        <div class="form-group">
            <label for="address3">동/리</label>
            <input type="text" id="address3" name="address3" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" pattern="\d+" title="숫자만 입력 가능합니다" required>
        </div>
        <div class="form-group">
            <input type="submit" value="Sign Up">
        </div>
    </form>
</div>

</body>
</html>
