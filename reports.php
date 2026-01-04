<?php 
require_once 'includes/config.php';
include 'includes/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold" style="color: var(--dark);">Báo Cáo Quản Trị</h4>
    <button class="btn btn-white shadow-sm border"><i class="bi bi-download"></i> Xuất Excel</button>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Top Sản Phẩm Bán Chạy</h5>
                <div class="table-responsive shadow-none border-0 p-0">
                    <table class="table table-sm align-middle">
                        <thead class="bg-light"><tr><th>SKU</th><th>Tên SP</th><th class="text-center">Số lượng</th></tr></thead>
                        <tbody>
                            <?php
                            // Query lấy trực tiếp từ Report
                            $sql = "SELECT p.sku, p.product_name, SUM(sol.quantity) AS total_qty
                                    FROM sales_order so
                                    JOIN sales_order_line sol ON so.sales_order_id = sol.sales_order_id
                                    JOIN product p ON sol.product_id = p.product_id
                                    WHERE so.status IN ('SHIPPED','DELIVERED')
                                    GROUP BY p.sku, p.product_name
                                    ORDER BY total_qty DESC LIMIT 5";
                            $res = $conn->query($sql);
                            if($res->num_rows > 0) {
                                while($row = $res->fetch_assoc()){
                                    echo "<tr>
                                        <td><span class='badge bg-light text-dark border'>{$row['sku']}</span></td>
                                        <td>{$row['product_name']}</td>
                                        <td class='fw-bold text-center text-primary'>{$row['total_qty']}</td>
                                    </tr>";
                                } 
                            } else { echo "<tr><td colspan='3' class='text-center text-muted'>Chưa có dữ liệu bán hàng</td></tr>"; }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Giá Trị Tồn Kho Theo Kho</h5>
                <?php
                $sql_val = "SELECT w.warehouse_name, SUM(ib.on_hand_qty * p.default_price) as total_val, COUNT(p.product_id) as sku_count
                            FROM inventory_balance ib 
                            JOIN product p ON ib.product_id = p.product_id 
                            JOIN warehouse w ON ib.warehouse_id = w.warehouse_id
                            GROUP BY w.warehouse_name";
                $res_val = $conn->query($sql_val);
                while($row = $res_val->fetch_assoc()){
                ?>
                <div class="d-flex align-items-center justify-content-between p-3 mb-3 bg-light rounded-3 border border-white shadow-sm">
                    <div>
                        <div class="fw-bold text-dark"><?php echo $row['warehouse_name']; ?></div>
                        <small class="text-muted"><?php echo $row['sku_count']; ?> mã sản phẩm</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-success fs-5"><?php echo number_format($row['total_val']); ?> ₫</div>
                        <small class="text-muted">Tổng giá trị</small>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>