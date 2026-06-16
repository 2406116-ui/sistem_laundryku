<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = hash('sha256', $_POST['password']);
    $sql = "SELECT * FROM user WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $_SESSION['login'] = true;
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LaundryKu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', system-ui, 'Segoe UI', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(270deg, #1e2a3a, #0f172a, #1e3a8a, #0f172a);
            background-size: 600% 600%;
            animation: gradientMove 12s ease infinite;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-container {
            max-width: 400px;
            width: 100%;
            background: #f1f5f9; /* abu muda */
            border-radius: 28px;
            box-shadow: 0 25px 45px -12px rgba(0,0,0,0.4);
            padding: 40px 32px;
            text-align: center;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .logo {
            margin-bottom: 24px;
        }

        .logo-icon {
            background: #3b82f6;
            width: 65px;
            height: 65px;
            border-radius: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            margin-bottom: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.3px;
        }

        .logo p {
            color: #475569;
            font-size: 14px;
            margin-top: 6px;
        }

        .welcome {
            margin: 24px 0 28px;
        }

        .welcome p {
            font-size: 18px;
            font-weight: 500;
            color: #1e293b;
            line-height: 1.4;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 6px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            font-size: 14px;
            background: white;
            transition: 0.2s;
        }

        .input-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
            background: white;
        }

        button {
            width: 100%;
            background: #0f172a;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 8px;
        }

        button:hover {
            background: #1e293b;
            transform: scale(1.02);
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 16px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: center;
        }

        .demo-info {
            margin-top: 24px;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="logo">
        <div class="logo-icon">🧺</div>
        <h1>LaundryKu</h1>
        <p>Sistem Informasi Laundry</p>
    </div>
    <div class="welcome">
        <p>Biar ngurus laundry jadi rapi dan nyaman</p>
    </div>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="input-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Masukkan username" required autofocus>
        </div>
        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>
        </div>
        <button type="submit">Masuk</button>
    </form>
    <div class="demo-info">Demo: admin / admin123</div>
</div>
</body>
</html>