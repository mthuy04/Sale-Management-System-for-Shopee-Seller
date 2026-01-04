/* assets/js/app.js */

// --- 1. MOCK DATA ---
let inventory = [
    { id: 1, sku: "PIL-001", name: "Premium Latex Pillow", warehouse: "HCM Main", onHand: 100, reserved: 0 },
    { id: 2, sku: "BED-S02", name: "Cotton Bed Sheet Set", warehouse: "HCM Main", onHand: 50, reserved: 0 },
    { id: 3, sku: "BOX-M03", name: "Storage Box M", warehouse: "Promo WH", onHand: 200, reserved: 0 },
];

let rawOrders = [
    { id: 'RAW01', sn: '251218ABC123', total: 550000, status: 'UNPROCESSED' }
];

let salesOrders = [
    { 
        id: 'SO-1001', sn: '251217OLD001', customer: 'Nguyen Van A', date: '2025-12-17', status: 'CONFIRMED', 
        items: [{sku: "PIL-001", qty: 2}], warehouse: "HCM Main", total: 1100000 
    },
    { 
        id: 'SO-1002', sn: '251218XYZ789', customer: 'Tran Thi B', date: '2025-12-18', status: 'NEW', 
        items: [{sku: "BED-S02", qty: 1}], warehouse: "HCM Main", total: 450000 
    }
];

// --- 2. INITIALIZATION ---
document.addEventListener('DOMContentLoaded', () => {
    // Tự động chạy các hàm render tùy theo trang hiện tại
    const path = window.location.pathname;
    
    // Khởi tạo tính toán inventory
    initInventoryCalc();

    if(path.includes('index.php') || path === '/') renderDashboard();
    if(path.includes('orders.php')) { renderSalesOrders(); renderRawOrders(); }
    if(path.includes('inventory.php')) renderInventory();
    if(path.includes('picking.php')) renderPicking();
});

// --- 3. LOGIC FUNCTIONS ---

function initInventoryCalc() {
    // Reset reserved để tính lại từ đầu dựa trên đơn Confirmed
    inventory.forEach(i => i.reserved = 0);
    salesOrders.forEach(order => {
        if(order.status === 'CONFIRMED') {
            order.items.forEach(item => {
                let inv = inventory.find(i => i.sku === item.sku);
                if(inv) inv.reserved += item.qty;
            });
        }
    });
}

function showToast(msg) {
    const toastEl = document.getElementById('liveToast');
    if(toastEl) {
        document.getElementById('toast-msg').innerText = msg;
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    } else {
        alert(msg);
    }
}

// Chức năng Confirm (Trang Orders)
function confirmOrder(soId) {
    const order = salesOrders.find(o => o.id === soId);
    if(!order) return;

    // Check stock
    let canReserve = true;
    order.items.forEach(item => {
        let inv = inventory.find(i => i.sku === item.sku);
        if((inv.onHand - inv.reserved) < item.qty) canReserve = false;
    });

    if(canReserve) {
        order.status = 'CONFIRMED';
        initInventoryCalc(); // Recalculate logic
        showToast(`Order ${soId} Confirmed & Reserved.`);
        renderSalesOrders(); 
    } else {
        alert("Insufficient Stock!");
    }
}

// Chức năng Post Goods Issue (Trang Picking)
function postGoodsIssue(soId) {
    const order = salesOrders.find(o => o.id === soId);
    if(confirm(`Confirm Goods Issue for ${soId}?`)) {
        order.status = 'SHIPPED';
        // Trừ kho thật
        order.items.forEach(item => {
            let inv = inventory.find(i => i.sku === item.sku);
            inv.onHand -= item.qty;
        });
        initInventoryCalc(); // Recalculate (release reserved)
        
        showToast(`GI Posted for ${soId}. Inventory Deducted.`);
        renderPicking();
        // Add log
        const log = document.getElementById('delivery-log');
        if(log) log.innerHTML = `<li class="list-group-item text-success"><i class="bi bi-check"></i> Shipped ${soId}</li>` + log.innerHTML;
    }
}

function syncShopee() {
    showToast("Syncing with Shopee API...");
    // Mock sync logic here
}

// --- 4. RENDER FUNCTIONS (HTML Generation) ---

function renderDashboard() {
    const pendingEl = document.getElementById('kpi-pending');
    if(pendingEl) pendingEl.innerText = salesOrders.filter(o => o.status === 'NEW').length;
    
    const shipEl = document.getElementById('kpi-ship');
    if(shipEl) shipEl.innerText = salesOrders.filter(o => o.status === 'CONFIRMED').length;

    const logEl = document.getElementById('activity-log');
    if(logEl) logEl.innerHTML = '<li class="list-group-item px-0">System initialized ready for operations.</li>';
}

function renderSalesOrders() {
    const el = document.getElementById('sales-order-list');
    if(!el) return;
    
    el.innerHTML = salesOrders.map(order => {
        let badge = order.status === 'NEW' ? 'bg-info' : (order.status === 'CONFIRMED' ? 'bg-primary' : 'bg-success');
        let btn = order.status === 'NEW' 
            ? `<button class="btn btn-sm btn-outline-primary" onclick="confirmOrder('${order.id}')">Confirm</button>` 
            : `<span class="text-muted small">Locked</span>`;
            
        return `<tr>
            <td><strong>${order.id}</strong><br><small>${order.sn}</small></td>
            <td>${order.date}</td>
            <td>${order.customer}</td>
            <td><span class="badge ${badge}">${order.status}</span></td>
            <td>${order.status === 'NEW' ? '<span class="text-success small">Stock OK</span>' : '-'}</td>
            <td>${btn}</td>
        </tr>`;
    }).join('');
}

function renderRawOrders() {
    const el = document.getElementById('raw-order-list');
    if(!el) return;
    el.innerHTML = rawOrders.map(r => `<tr><td>${r.sn}</td><td>${r.total}</td><td>${r.status}</td><td><button class="btn btn-sm btn-light">Validate</button></td></tr>`).join('');
}

function renderInventory() {
    const el = document.getElementById('inventory-list');
    if(!el) return;
    
    el.innerHTML = inventory.map(item => {
        const avail = item.onHand - item.reserved;
        return `<tr>
            <td class="text-start fw-bold">${item.sku}<br><small class="fw-normal text-muted">${item.name}</small></td>
            <td>${item.warehouse}</td>
            <td class="bg-light">${item.onHand}</td>
            <td class="text-warning fw-bold">${item.reserved}</td>
            <td class="text-success fw-bold">${avail}</td>
            <td>${avail > 5 ? '<span class="badge bg-success">OK</span>' : '<span class="badge bg-danger">Low</span>'}</td>
        </tr>`;
    }).join('');
}

function renderPicking() {
    const el = document.getElementById('picking-list');
    if(!el) return;
    
    const readyOrders = salesOrders.filter(o => o.status === 'CONFIRMED');
    if(readyOrders.length === 0) {
        el.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No orders ready for picking.</td></tr>';
        return;
    }

    el.innerHTML = readyOrders.map(order => `<tr>
        <td class="fw-bold">${order.id}</td>
        <td>${order.warehouse}</td>
        <td>${order.items[0].sku} (x${order.items[0].qty})</td>
        <td>Shopee Express</td>
        <td><button class="btn btn-success btn-sm" onclick="postGoodsIssue('${order.id}')">Pick & Ship</button></td>
    </tr>`).join('');
}