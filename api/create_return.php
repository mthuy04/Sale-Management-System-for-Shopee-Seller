<?php
// api/create_return.php
require_once '../includes/config.php';

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 1. Đánh dấu đơn gốc là RETURNED
    $conn->query("UPDATE sales_order SET status = 'RETURNED', updated_at = NOW() WHERE sales_order_id = $id");

    // 2. Tạo Return Order header
    // Lấy thông tin khách hàng từ đơn gốc để tạo đơn trả
    $stmt = $conn->prepare("INSERT INTO return_order (sales_order_id, customer_id, reason, status, created_at) 
                            SELECT sales_order_id, customer_id, 'Customer Changed Mind', 'COMPLETED', NOW() 
                            FROM sales_order WHERE sales_order_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $return_id = $conn->insert_id;

    // 3. Tạo Return Lines (Chi tiết hàng trả)
    // Giả định khách trả lại toàn bộ sản phẩm trong đơn
    // Lưu ý: Trigger 'trg_returnline_create_inventory_txn' trong SQL của bạn sẽ tự động bắt sự kiện INSERT này
    // và tạo InventoryTransaction (RETURN_IN) để cộng lại tồn kho.
    $lines = $conn->query("SELECT sales_order_line_id, product_id, quantity FROM sales_order_line WHERE sales_order_id = $id");
    
    if($lines) {
        $stmt_line = $conn->prepare("INSERT INTO return_line (return_order_id, sales_order_line_id, product_id, quantity, disposition) VALUES (?, ?, ?, ?, 'RESTOCK')");
        while($l = $lines->fetch_assoc()){
            $stmt_line->bind_param("iiii", $return_id, $l['sales_order_line_id'], $l['product_id'], $l['quantity']);
            $stmt_line->execute();
        }
    }
    
    // 4. Log lịch sử
    $conn->query("INSERT INTO order_status_history (sales_order_id, old_status, new_status, changed_at) VALUES ($id, 'DELIVERED', 'RETURNED', NOW())");
}

header("Location: ../orders.php");
exit();
?>