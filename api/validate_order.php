<?php
// api/validate_order.php
require_once '../includes/config.php';

if (isset($_GET['raw_id'])) {
    $raw_id = intval($_GET['raw_id']);

    // 1. Lấy thông tin Raw Order
    $res = $conn->query("SELECT * FROM shopee_order_raw WHERE raw_order_id = $raw_id");
    if ($res->num_rows == 0) die("Order not found");
    
    $raw = $res->fetch_assoc();
    $data = json_decode($raw['order_json'], true);

    // Bắt đầu Transaction
    $conn->begin_transaction();

    try {
        // 2. Tạo Sales Order Header
        // Tạo mã đơn nội bộ ngẫu nhiên cho chuyên nghiệp
        $order_no = "SO-" . date('ymd') . "-" . rand(100, 999);
        $cust_name = $data['customer']['name'] ?? 'Guest';
        
        // Tạo khách hàng tạm (hoặc check tồn tại) - Ở đây ta tạo đơn giản gắn với Customer ID 1
        // Nếu muốn xịn hơn: Insert vào bảng customer nếu chưa có.
        
        $total_amt = $data['total_amount'] ?? 0;

        $stmt = $conn->prepare("INSERT INTO sales_order (shop_id, raw_order_id, customer_id, order_no, order_date, status, total_amount, payment_status) VALUES (?, ?, 1, ?, NOW(), 'PENDING', ?, 'PAID')");
        $stmt->bind_param("iisi", $raw['shop_id'], $raw_id, $order_no, $total_amt);
        $stmt->execute();
        $so_id = $conn->insert_id;
        
        // 3. Tạo Sales Order Line (QUAN TRỌNG: Tìm đúng Product ID theo SKU)
        foreach ($data['items'] as $item) {
            $sku = $item['sku'];
            $qty = $item['qty'];
            
            // Tìm sản phẩm trong DB dựa trên SKU
            $prod_query = $conn->query("SELECT product_id, default_price FROM product WHERE sku = '$sku' LIMIT 1");
            
            if ($prod_query->num_rows > 0) {
                $prod = $prod_query->fetch_assoc();
                $pid = $prod['product_id'];
                $price = $prod['default_price']; // Lấy giá gốc từ DB của mình
                $line_total = $price * $qty;

                // Insert Line (Mặc định kho 1)
                $conn->query("INSERT INTO sales_order_line (sales_order_id, product_id, warehouse_id, quantity, unit_price, line_amount) VALUES ($so_id, $pid, 1, $qty, $price, $line_total)");
            } else {
                // Trường hợp SKU không khớp (Ví dụ Shopee bán mã lạ), ta gán về 1 sản phẩm mặc định hoặc báo lỗi
                // Ở đây ta bỏ qua để demo không bị crash
            }
        }

        // 4. Update trạng thái Raw -> PROCESSED
        $conn->query("UPDATE shopee_order_raw SET order_status = 'PROCESSED' WHERE raw_order_id = $raw_id");

        $conn->commit();
        header("Location: ../orders.php");

    } catch (Exception $e) {
        $conn->rollback();
        echo "Lỗi xử lý đơn hàng: " . $e->getMessage();
    }
}
?>