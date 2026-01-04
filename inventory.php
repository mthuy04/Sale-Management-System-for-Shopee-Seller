<?php 
require_once 'includes/config.php';
include 'includes/header.php'; 
?>

<h4 class="fw-bold mb-4">Inventory Balance</h4>
<div class="alert alert-info py-2 small">
    <i class="bi bi-info-circle"></i> <strong>Real-time Data:</strong> Available = On Hand - Reserved. 
    (Data is maintained by DB Triggers).
</div>

<div class="table-responsive">
    <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
            <tr>
                <th class="text-start">Product</th>
                <th>Warehouse</th>
                <th class="text-primary">On Hand</th>
                <th class="text-warning">Reserved</th>
                <th class="text-success fw-bold">Available</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT p.sku, p.product_name, w.warehouse_name, ib.on_hand_qty, ib.reserved_qty 
                    FROM inventory_balance ib
                    JOIN product p ON ib.product_id = p.product_id
                    JOIN warehouse w ON ib.warehouse_id = w.warehouse_id
                    ORDER BY p.sku";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $avail = $row['on_hand_qty'] - $row['reserved_qty'];
                    echo "<tr>
                        <td class='text-start'><strong>{$row['sku']}</strong><br><small>{$row['product_name']}</small></td>
                        <td>{$row['warehouse_name']}</td>
                        <td class='bg-light'>{$row['on_hand_qty']}</td>
                        <td class='text-warning fw-bold'>{$row['reserved_qty']}</td>
                        <td class='text-success fw-bold fs-6'>{$avail}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No inventory data.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>