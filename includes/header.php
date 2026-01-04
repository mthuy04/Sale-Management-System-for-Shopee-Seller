<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HappyHome - Premium Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav id="sidebar">
    <div class="brand-box">
        <div class="brand-icon">
            <i class="bi bi-tsunami"></i> </div>
        <div>
            <h5 class="fw-bold mb-0" style="color: var(--dark); font-size: 1.1rem;">HappyHome</h5>
            <small style="color: var(--grey); font-size: 0.75rem;">Water Flow Admin</small>
        </div>
    </div>
    
    <div class="nav-content">
        <div class="nav-header">Tổng Quan</div>
        <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>

        <div class="nav-header">Kinh Doanh</div>
        <a href="orders.php?view=raw" class="nav-link <?php echo (isset($_GET['view']) && $_GET['view'] == 'raw') ? 'active' : ''; ?>">
            <i class="bi bi-cloud-download-fill"></i> Đơn Shopee
        </a>
        <a href="orders.php" class="nav-link <?php echo ($current_page == 'orders.php' && !isset($_GET['view'])) ? 'active' : ''; ?>">
            <i class="bi bi-receipt-cutoff"></i> Quản Lý Đơn
        </a>
        
        <div class="nav-header">Vận Hành</div>
        <a href="picking.php" class="nav-link <?php echo ($current_page == 'picking.php') ? 'active' : ''; ?>">
            <i class="bi bi-box2-fill"></i> Soạn Hàng
        </a>
        <a href="inventory.php" class="nav-link <?php echo ($current_page == 'inventory.php') ? 'active' : ''; ?>">
            <i class="bi bi-shop-window"></i> Kho Hàng
        </a>
    </div>
    <div class="nav-header">Tài Chính & Báo Cáo</div>
<a href="reports.php" class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
    <i class="bi bi-pie-chart-fill"></i> Báo Cáo
</a>

    <div style="position: absolute; bottom: 24px; left: 0; right: 0;">
        <div class="user-profile">
            <div class="avatar">
                <?php echo substr($_SESSION['username'], 0, 1); ?>
            </div>
            <div style="flex: 1;">
                <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?php echo $_SESSION['username']; ?></div>
                <small class="text-success fw-bold" style="font-size: 0.7rem;">● Online</small>
            </div>
            <a href="logout.php" class="text-secondary" title="Logout"><i class="bi bi-box-arrow-right fs-5"></i></a>
        </div>
    </div>
</nav>


<main id="main-content">