<?php 
require_once 'includes/config.php';
include 'includes/header.php'; 
?>

<h4 class="fw-bold mb-4">Picking & Post Goods Issue</h4>

<div class="card mb-4">
    <div class="card-header bg-white fw-bold">Ready for Picking (Confirmed Orders)</div>
    <div class="card-body p-0">
        <div class="table-responsive border-0">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Order No</th>
                        <th>Warehouse</th>
                        <th>Items (Preview)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
    <i class="bi bi-printer"></i> Print List
</button>
                <tbody>
                    <?php
                    // Chỉ lấy đơn đã Confirm để Pick
                    $sql = "SELECT so.sales_order_id, so.order_no, w.warehouse_name 
                            FROM sales_order so
                            JOIN sales_order_line sol ON so.sales_order_id = sol.sales_order_id
                            JOIN warehouse w ON sol.warehouse_id = w.warehouse_id
                            WHERE so.status = 'CONFIRMED'
                            GROUP BY so.sales_order_id";
                    
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0):
                        while($row = $result->fetch_assoc()):
                            // Lấy chi tiết items
                            $s_id = $row['sales_order_id'];
                            $items_res = $conn->query("SELECT p.sku, sol.quantity FROM sales_order_line sol JOIN product p ON sol.product_id = p.product_id WHERE sales_order_id = $s_id");
                            $items_str = [];
                            while($i = $items_res->fetch_assoc()) $items_str[] = "{$i['sku']} (x{$i['quantity']})";
                    ?>
                    <tr>
                        <td class="fw-bold"><?php echo $row['order_no']; ?></td>
                        <td><?php echo $row['warehouse_name']; ?></td>
                        <td><?php echo implode(", ", $items_str); ?></td>
                        <td>
                            <a href="api/post_goods_issue.php?id=<?php echo $row['sales_order_id']; ?>" 
                               class="btn btn-success btn-sm"
                               onclick="return confirm('Confirm Goods Issue? Inventory will be deducted.');">
                               <i class="bi bi-box-seam"></i> Pick & Ship
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">No orders ready for picking.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>