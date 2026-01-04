<?php
// api/post_goods_issue.php
require_once '../includes/config.php';

// Kiểm tra có ID không
if (!isset($_GET['id'])) {
    die("Missing Order ID");
}

$id = intval($_GET['id']);

// Bắt đầu Transaction (để đảm bảo dữ liệu toàn vẹn)
$conn->begin_transaction();

try {
    // 1. Tạo Delivery Document (Bước M5 trong Flowchart - BẮT BUỘC CÓ)
    // Giả định carrier_id = 1 (Shopee Express)
    $tracking_no = 'SPX' . date('Ymd') . rand(100,999);
    
    // Câu lệnh chuẩn bị:
    $stmt = $conn->prepare("INSERT INTO delivery_document (sales_order_id, warehouse_id, carrier_id, tracking_no, status, ship_date) VALUES (?, 1, 1, ?, 'SHIPPED', NOW())");
    $stmt->bind_param("is", $id, $tracking_no);
    $stmt->execute();
    
    // Lấy ID phiếu giao vừa tạo
    $delivery_id = $conn->insert_id; 

    // 2. Lấy chi tiết đơn hàng để xử lý từng dòng
    $lines = $conn->query("SELECT * FROM sales_order_line WHERE sales_order_id = $id");
    
    while($line = $lines->fetch_assoc()) {
        // 2a. Tạo Delivery Line (Chi tiết phiếu giao)
        $conn->query("INSERT INTO delivery_line (delivery_id, sales_order_line_id, product_id, quantity) VALUES ($delivery_id, {$line['sales_order_line_id']}, {$line['product_id']}, {$line['quantity']})");

        // 2b. Trừ kho (Inventory Transaction) - Bước M6
        // Trigger trong SQL sẽ chạy ở đây. Nếu thiếu kho, nó sẽ quăng lỗi xuống phần catch
        $stmt_inv = $conn->prepare("INSERT INTO inventory_transaction (product_id, warehouse_id, sales_order_line_id, txn_type, quantity, created_by) VALUES (?, ?, ?, 'GOODS_ISSUE', ?, 1)");
        $stmt_inv->bind_param("iiii", $line['product_id'], $line['warehouse_id'], $line['sales_order_line_id'], $line['quantity']);
        $stmt_inv->execute();
    }

    // 3. Cập nhật trạng thái đơn hàng -> SHIPPED
    $conn->query("UPDATE sales_order SET status = 'SHIPPED', updated_at = NOW() WHERE sales_order_id = $id");
    
    // 4. Ghi log lịch sử
    $conn->query("INSERT INTO order_status_history (sales_order_id, old_status, new_status, changed_at) VALUES ($id, 'CONFIRMED', 'SHIPPED', NOW())");

    // Nếu mọi thứ ok thì Commit (Lưu)
    $conn->commit();
    
    // Chuyển hướng về trang Picking
    echo "<script>alert('Thành công! Đã tạo phiếu giao hàng và trừ kho.'); window.location.href='../picking.php';</script>";

} catch (mysqli_sql_exception $exception) {
    // Nếu có lỗi thì hoàn tác (Rollback) để không bị dữ liệu rác
    $conn->rollback(); 
    
    $msg = $exception->getMessage();
    // Kiểm tra xem có phải lỗi do Trigger báo thiếu kho không
    if (strpos($msg, 'Insufficient stock') !== false) {
        echo "<script>alert('LỖI: Không đủ tồn kho để xuất hàng! Vui lòng kiểm tra lại.'); window.location.href='../picking.php';</script>";
    } else {
        // Lỗi khác (Code sai, DB lỗi...)
        echo "Lỗi hệ thống: " . $msg;
        exit();
    }
}
?>