<?php 
require_once 'includes/config.php';
include 'includes/header.php'; 

// Data Queries
$sql_pending = "SELECT COUNT(*) as count FROM sales_order WHERE status = 'PENDING'";
$count_pending = $conn->query($sql_pending)->fetch_assoc()['count'];
$sql_confirmed = "SELECT COUNT(*) as count FROM sales_order WHERE status = 'CONFIRMED'";
$count_confirmed = $conn->query($sql_confirmed)->fetch_assoc()['count'];
$sql_rev = "SELECT SUM(total_amount) as total FROM sales_order WHERE DATE(order_date) = CURDATE()";
$revenue = $conn->query($sql_rev)->fetch_assoc()['total'] ?? 0;
?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h3 class="fw-bold mb-1" style="color: var(--dark);">Tổng Quan</h3>
        <p class="text-muted mb-0">Chào bạn, chúc một ngày "Tiền vào như nước"! 🌊</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-white shadow-sm border"><i class="bi bi-filter"></i> Lọc</button>
        <button class="btn btn-white shadow-sm border"><i class="bi bi-calendar3 me-2"></i> <?php echo date('d/m/Y'); ?></button>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-value"><?php echo $count_pending; ?></div>
                    <div class="kpi-label">Chờ Xác Nhận</div>
                </div>
                <div class="kpi-icon gold">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="badge bg-warning"><i class="bi bi-exclamation-circle"></i> Cần xử lý</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-value"><?php echo $count_confirmed; ?></div>
                    <div class="kpi-label">Cần Giao Hàng</div>
                </div>
                <div class="kpi-icon water">
                    <i class="bi bi-truck"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="badge bg-primary">Sẵn sàng ship</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-value text-success"><?php echo number_format($revenue/1000); ?>k</div>
                    <div class="kpi-label">Doanh Thu Ngày</div>
                </div>
                <div class="kpi-icon wood">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="badge bg-success"><i class="bi bi-graph-up-arrow"></i> Tăng trưởng</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-value">0</div>
                    <div class="kpi-label">Đơn Hoàn Trả</div>
                </div>
                <div class="kpi-icon metal">
                    <i class="bi bi-arrow-return-left"></i>
                </div>
            </div>
            <div class="mt-3">
                <span class="badge bg-light text-dark border">Ổn định</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Dòng Chảy Đơn Hàng (Gần đây)</h5>
                    <a href="orders.php" class="btn btn-sm btn-light text-primary fw-bold">Xem tất cả</a>
                </div>
                <div class="table-responsive shadow-none border-0 p-0">
                    <table class="table align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Mã Đơn</th>
                                <th>Trạng Thái Cũ</th>
                                <th>Trạng Thái Mới</th>
                                <th>Thời Gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_log = "SELECT * FROM order_status_history ORDER BY changed_at DESC LIMIT 5";
                            $res_log = $conn->query($sql_log);
                            if ($res_log->num_rows > 0) {
                                while($row = $res_log->fetch_assoc()) {
                                    echo "<tr>
                                        <td><span class='fw-bold text-dark'>#{$row['sales_order_id']}</span></td>
                                        <td><span class='text-muted'>{$row['old_status']}</span></td>
                                        <td><span class='badge bg-info'>{$row['new_status']}</span></td>
                                        <td class='text-muted'>" . date('H:i d/m', strtotime($row['changed_at'])) . "</td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center py-5 text-muted'><i class='bi bi-inbox fs-1 d-block mb-2 opacity-50'></i>Chưa có dữ liệu trôi về</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card h-100 text-white" style="background: var(--primary-gradient); position: relative; overflow: hidden;">
            <div style="position: absolute; top: -20px; right: -20px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -40px; left: -40px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
            
            <div class="card-body p-4 d-flex flex-column justify-content-between" style="position: relative; z-index: 1;">
                <div>
                    <div class="mb-3 p-3 bg-white bg-opacity-25 rounded-3 d-inline-block">
                        <i class="bi bi-cloud-arrow-down-fill fs-3"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Đồng bộ Shopee</h4>
                    <p class="opacity-90 mb-4">Hút dữ liệu đơn hàng mới nhất về hệ thống. Khơi thông dòng chảy hàng hóa.</p>
                </div>
                <button onclick="location.href='api/sync_shopee.php'" class="btn btn-white w-100 text-primary fw-bold py-3 shadow-sm" style="background: white;">
                    <i class="bi bi-arrow-repeat me-2"></i> Sync Ngay
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>