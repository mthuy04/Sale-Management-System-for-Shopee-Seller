<?php 
require_once 'includes/config.php';
include 'includes/header.php'; 

$view = isset($_GET['view']) ? $_GET['view'] : 'sales'; 
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Sales Orders</h4>
    <button class="btn btn-outline-primary btn-sm" onclick="alert('Simulated: Syncing orders from Shopee...')">
    <a href="api/sync_shopee.php" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-arrow-repeat"></i> Sync Shopee API
</a>
    </button>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link <?php echo ($view != 'raw') ? 'active' : ''; ?>" href="orders.php?view=sales">All Orders</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($view == 'raw') ? 'active' : ''; ?>" href="orders.php?view=raw">Raw Orders</a>
        </li>
    </ul>

    <?php if($view != 'raw'): ?>
    <form method="GET" class="d-flex align-items-center gap-2">
        <input type="hidden" name="view" value="sales">
        <select name="status_filter" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 150px;">
            <option value="">-- All Status --</option>
            <option value="PENDING" <?php if($status_filter=='PENDING') echo 'selected'; ?>>Pending</option>
            <option value="CONFIRMED" <?php if($status_filter=='CONFIRMED') echo 'selected'; ?>>Confirmed</option>
            <option value="SHIPPED" <?php if($status_filter=='SHIPPED') echo 'selected'; ?>>Shipped</option>
            <option value="DELIVERED" <?php if($status_filter=='DELIVERED') echo 'selected'; ?>>Delivered</option>
            <option value="RETURNED" <?php if($status_filter=='RETURNED') echo 'selected'; ?>>Returned</option>
        </select>
    </form>
    <?php endif; ?>
</div>

<div class="tab-content">
    <div class="tab-pane fade <?php echo ($view != 'raw') ? 'show active' : ''; ?>">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Order No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Tạo câu Query có lọc theo status
                    $sql = "SELECT so.*, c.full_name 
                            FROM sales_order so 
                            LEFT JOIN customer c ON so.customer_id = c.customer_id ";
                    
                    if($status_filter != '') {
                        $sql .= " WHERE so.status = '$status_filter' ";
                    }
                    $sql .= " ORDER BY so.created_at DESC";

                    $result = $conn->query($sql);
                    $modals = []; // Mảng chứa HTML modal để in ra sau cùng

                    if ($result->num_rows > 0):
                        while($row = $result->fetch_assoc()):
                            // Logic màu sắc badge
                            $badge = match($row['status']) {
                                'PENDING' => 'bg-warning text-dark',
                                'CONFIRMED' => 'bg-primary',
                                'SHIPPED' => 'bg-info text-dark',
                                'DELIVERED' => 'bg-success',
                                'RETURNED' => 'bg-danger',
                                default => 'bg-secondary'
                            };

                            // --- TẠO MODAL VIEW CHO DÒNG NÀY (Lưu vào biến, in sau) ---
                            $oid = $row['sales_order_id'];
                            $modalID = "orderModal" . $oid;
                            
                            // Lấy chi tiết items cho Modal
                            $items_res = $conn->query("SELECT p.sku, p.product_name, sol.quantity, w.warehouse_name 
                                                     FROM sales_order_line sol 
                                                     JOIN product p ON sol.product_id = p.product_id 
                                                     JOIN warehouse w ON sol.warehouse_id = w.warehouse_id 
                                                     WHERE sales_order_id = $oid");
                            
                            $items_html = "";
                            while($item = $items_res->fetch_assoc()){
                                $items_html .= "<tr>
                                    <td>{$item['sku']}</td>
                                    <td>{$item['product_name']}</td>
                                    <td>{$item['quantity']}</td>
                                    <td>{$item['warehouse_name']}</td>
                                </tr>";
                            }

                            $modals[] = "
                            <div class='modal fade' id='$modalID' tabindex='-1'>
                                <div class='modal-dialog modal-lg'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title'>Order #{$row['order_no']}</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <p><strong>Customer:</strong> {$row['full_name']} | <strong>Date:</strong> {$row['order_date']}</p>
                                            <table class='table table-bordered table-sm'>
                                                <thead class='table-light'><tr><th>SKU</th><th>Name</th><th>Qty</th><th>Warehouse</th></tr></thead>
                                                <tbody>$items_html</tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>";
                            // -----------------------------------------------------------
                    ?>
                    <tr>
                        <td><strong><?php echo $row['order_no']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['order_date'])); ?></td>
                        <td><?php echo $row['full_name']; ?></td>
                        <td><?php echo number_format($row['total_amount']); ?> ₫</td>
                        <td><span class="badge <?php echo $badge; ?>"><?php echo $row['status']; ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#<?php echo $modalID; ?>" title="View Detail">
                                <i class="bi bi-eye"></i>
                            </button>

                            <?php if($row['status'] == 'PENDING'): ?>
                                <a href="api/confirm_order.php?id=<?php echo $oid; ?>" class="btn btn-sm btn-outline-primary" title="Confirm Order">
                                    <i class="bi bi-check-lg"></i>
                                </a>

                            <?php elseif($row['status'] == 'SHIPPED'): ?>
                                <a href="api/simulate_delivery.php?id=<?php echo $oid; ?>" class="btn btn-sm btn-outline-success" title="Mark Delivered">
                                    <i class="bi bi-truck"></i>
                                </a>

                            <?php elseif($row['status'] == 'DELIVERED'): ?>
                                <a href="api/create_return.php?id=<?php echo $oid; ?>" class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Create Return for this order? Inventory will be restocked.');" title="Return Order">
                                    <i class="bi bi-arrow-return-left"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No sales orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="tab-pane fade <?php echo ($view == 'raw') ? 'show active' : ''; ?>">
        <div class="alert alert-light border d-flex justify-content-between align-items-center">
            <span><i class="bi bi-info-circle me-2"></i>Đơn hàng được đồng bộ từ Shopee API (Giả lập).</span>
            <a href="api/sync_shopee.php" class="btn btn-sm btn-primary"><i class="bi bi-arrow-repeat"></i> Kéo Đơn Mới</a>
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr><th>SN</th><th>Shop</th><th>Trạng Thái</th><th>Hành Động</th></tr></thead>
                <tbody>
                    <?php
                    $sql_raw = "SELECT * FROM shopee_order_raw ORDER BY imported_at DESC LIMIT 20";
                    $res_raw = $conn->query($sql_raw);
                    if ($res_raw && $res_raw->num_rows > 0) {
                        while($r = $res_raw->fetch_assoc()) {
                            // Parse JSON để lấy tổng tiền hiển thị cho đẹp
                            $data = json_decode($r['order_json'], true);
                            $total_display = isset($data['total_amount']) ? number_format($data['total_amount']) . ' ₫' : '---';

                            echo "<tr>
                                <td class='fw-bold'>{$r['shopee_order_sn']}</td>
                                <td>Shop ID {$r['shop_id']}</td>
                                <td>
                                    <span class='badge " . ($r['order_status'] == 'PROCESSED' ? 'bg-success' : 'bg-warning text-dark') . "'>
                                        {$r['order_status']}
                                    </span>
                                </td>
                                <td>";
                            
                            // NÚT VALIDATE: Chỉ hiện khi chưa xử lý
                            if($r['order_status'] != 'PROCESSED') {
                                echo "<a href='api/validate_order.php?raw_id={$r['raw_order_id']}' class='btn btn-sm btn-outline-primary'>
                                        <i class='bi bi-check2-square'></i> Duyệt Đơn
                                      </a>";
                            } else {
                                echo "<span class='text-muted small'><i class='bi bi-check-all'></i> Đã tạo SO</span>";
                            }
                            
                            echo "</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center py-5 text-muted'>Chưa có đơn hàng raw nào. Hãy bấm Sync.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
if(isset($modals)) {
    foreach($modals as $m) echo $m; 
}
?>

<?php include 'includes/footer.php'; ?>