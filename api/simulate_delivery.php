<?php
// api/simulate_delivery.php
require_once '../includes/config.php';

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // 1. Cập nhật trạng thái Order -> DELIVERED
    $stmt = $conn->prepare("UPDATE sales_order SET status = 'DELIVERED', updated_at = NOW() WHERE sales_order_id = ?");
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        // 2. Ghi log lịch sử
        $conn->query("INSERT INTO order_status_history (sales_order_id, old_status, new_status, changed_at) 
                      VALUES ($id, 'SHIPPED', 'DELIVERED', NOW())");
    }
}

// Quay về trang cũ
header("Location: ../orders.php");
exit();
?>