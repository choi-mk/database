<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../basic_style.css">
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
        
    </style>
</head>
<body>

<div class="container">
    <h2>Sign Up</h2>

    <form action="signup.php" method="post">
        <div class="input-group">
            <input type="text" id="name" name="name" placeholder="Name" required>
        </div>
        <div class="input-group">
            <input type="text" id="nickname" name="nickname" placeholder="Nickname" required>
        </div>
        <div class="input-group">
            <input type="text" id="account" name="account" pattern="\d+" title="숫자만 입력 가능합니다" placeholder="Account Number" required>
        </div>
        <div class="input-group">
            <input type="text" id="address1" name="address1" placeholder="시/군/구" required>
        </div>
        <div class="input-group">
            <input type="text" id="address2" name="address2" placeholder="읍/면" required>
        </div>
        <div class="input-group">
            <input type="text" id="address3" name="address3" placeholder="동/리" required>
        </div>
        <div class="input-group">
            <input type="tel" id="phone" name="phone" pattern="\d+" title="숫자만 입력 가능합니다" value="<?= htmlspecialchars($_GET['phone'] ?? '') ?>" placeholder="Phone Number" required>
            <?php if (isset($_GET['phone_error'])): ?>
                <div class="error-message"><?= htmlspecialchars($_GET['phone_error']) ?></div>
            <?php endif; ?>
        </div>
        <button type="submit" class="submit-btn">Sign Up</button>
    </form>
</div>

</body>
</html>
