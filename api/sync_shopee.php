<?php
// api/sync_shopee.php
require_once '../includes/config.php';

// 1. Danh sách các SKU thực tế đang có trong kho (Bạn vừa thêm vào DB)
$available_skus = [
    'PILLOW-001', 
    'BED-S001', 
    'BED-S002', 
    'BOX-M001', 
    'BOX-S002', 
    'DEC-L001'
];

// 2. Random 1 hoặc 2 sản phẩm cho đơn hàng này
$num_items = rand(1, 2); 
$order_items = [];
$total_order_amount = 0;

// Lấy ngẫu nhiên các key từ mảng
$random_keys = array_rand($available_skus, $num_items);
if (!is_array($random_keys)) $random_keys = [$random_keys];

foreach ($random_keys as $key) {
    $sku = $available_skus[$key];
    $qty = rand(1, 3); // Số lượng ngẫu nhiên 1-3
    
    // Giả lập giá tiền (Logic đúng là phải query DB, nhưng ở bước Raw này ta giả lập giá Shopee trả về)
    $price_mock = rand(50, 500) * 1000; 
    
    $order_items[] = [
        'sku' => $sku,
        'qty' => $qty,
        'price' => $price_mock
    ];
    $total_order_amount += ($price_mock * $qty);
}

// 3. Tạo thông tin đơn hàng giả lập
$rand_suffix = rand(10000, 99999);
$shopee_sn = "SHOPEE_2512_" . $rand_suffix;

// Cấu trúc JSON chứa items đã random
$order_json = json_encode([
    'items' => $order_items,
    'total_amount' => $total_order_amount,
    'customer' => [
        'name' => 'Khách Shopee ' . substr($rand_suffix, 0, 3),
        'phone' => '09' . rand(10000000, 99999999)
    ]
]);

// 4. Insert vào bảng Raw
$stmt = $conn->prepare("INSERT INTO shopee_order_raw (shop_id, shopee_order_sn, order_status, payment_status, order_time, order_json, imported_at) VALUES (1, ?, 'READY_TO_SHIP', 'PAID', NOW(), ?, NOW())");
$stmt->bind_param("ss", $shopee_sn, $order_json);

if ($stmt->execute()) {
    echo "<script>alert('Đã đồng bộ đơn mới: $shopee_sn (Tổng: " . number_format($total_order_amount) . "đ)'); window.location.href='../orders.php?view=raw';</script>";
} else {
    echo "Error: " . $conn->error;
}
?>