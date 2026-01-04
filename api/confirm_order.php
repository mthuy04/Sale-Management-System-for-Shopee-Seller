<?php
require_once '../includes/config.php';
$id = $_GET['id'];

// 1. Cập nhật trạng thái Order
$conn->query("UPDATE sales_order SET status = 'CONFIRMED' WHERE sales_order_id = $id");

// 2. Tạo Reservation cho từng dòng (Trigger sẽ tự update Inventory Balance)
$lines = $conn->query("SELECT * FROM sales_order_line WHERE sales_order_id = $id");
while($line = $lines->fetch_assoc()) {
    $stmt = $conn->prepare("INSERT INTO inventory_reservation (sales_order_line_id, product_id, warehouse_id, reserved_qty, status) VALUES (?, ?, ?, ?, 'ACTIVE')");
    $stmt->bind_param("iiii", $line['sales_order_line_id'], $line['product_id'], $line['warehouse_id'], $line['quantity']);
    $stmt->execute();
}

header("Location: ../orders.php"); // Quay lại trang orders
?>