<?php
session_start();
// Nếu đã đăng nhập rồi thì đá về index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

// Xử lý khi nhấn nút Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // DEMO: Login cứng (Để test cho nhanh, sau này thay bằng query DB)
    // User: admin / Pass: 123456
    if ($username === 'admin' && $password === '123456') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'Sales Admin'; // Lưu role để sau này if/else menu
        
        header("Location: index.php");
        exit();
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HappyHome Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: #fff; }
        .btn-orange { background-color: #ee4d2d; color: white; }
        .btn-orange:hover { background-color: #d73211; color: white; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold" style="color: #ee4d2d;">HappyHome</h3>
            <p class="text-muted">Sales Management System</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger py-2 text-center small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="admin" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" value="123456" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-orange py-2">Đăng nhập</button>
            </div>
            <div class="text-center mt-3">
                <small class="text-muted">Demo: admin / 123456</small>
            </div>
        </form>
    </div>
</body>
</html>