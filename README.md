# HappyHome - E-commerce Logistics & Order Management System (OMS)

![Project Status](https://img.shields.io/badge/status-active-success.svg)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4.svg)
![MySQL](https://img.shields.io/badge/MySQL-Triggers-4479A1.svg)
![PowerBI](https://img.shields.io/badge/PowerBI-Dashboard-F2C811.svg)

**HappyHome OMS** is a comprehensive solution designed to streamline the order fulfillment process for e-commerce businesses (specifically Shopee). It automates the flow from order synchronization to inventory deduction using advanced **Database Triggers** and provides real-time insights via **Power BI**.

---

## 📸 Screenshots

### 1. Sales Performance Dashboard (Power BI)
<img width="980" height="553" alt="Ảnh màn hình 2025-12-31 lúc 21 32 02" src="https://github.com/user-attachments/assets/031f707f-73c3-42cd-a81d-b2dcb5290884" />

### 2. Dashboard (WEB UI)
<img width="1440" height="814" alt="Ảnh màn hình 2025-12-31 lúc 16 55 13" src="https://github.com/user-attachments/assets/d09cd7ce-bc87-4abd-a578-8d54f200f67e" />


### 3. Inventory Management
<img width="1440" height="781" alt="Ảnh màn hình 2026-01-04 lúc 16 40 39" src="https://github.com/user-attachments/assets/b04003df-74b9-4dfb-8c77-3cf130f48a67" />


---

## 🚀 Key Features

### 🛒 Order Management
- **Shopee Sync Simulation:** Simulates fetching orders from Shopee API (supports Random & Bulk order testing).
- **Order Validation:** Automatically validates raw JSON data against warehouse SKUs.
- **Workflow:** Pending -> Confirmed -> Picked & Shipped -> Delivered/Returned.

### 📦 Smart Inventory Control (The Core)
- **Real-time Deduction:** Stock is deducted immediately upon shipment.
- **Reservation System:** Stock is "Reserved" when an order is Confirmed, preventing overselling.
- **Negative Stock Protection:** Database constraints block any transaction that would cause stock to drop below zero.

### 📊 Business Intelligence
- **Star Schema Design:** Built for scalable analytics.
- **Key Metrics:** Monthly Revenue Trend, Return Rate, Top Products.
- **Power BI Integration:** Direct connection to MySQL for live reporting.

---

## 🛠 Tech Stack

* **Backend:** PHP (Native), Apache Web Server (XAMPP).
* **Database:** MySQL / MariaDB.
* **Frontend:** HTML5, CSS3, JavaScript, Bootstrap.
* **Analytics:** Microsoft Power BI Desktop/Service.

---

## 🧠 Database Architecture (Advanced)

The system relies heavily on **MySQL Triggers** to ensure data integrity without relying solely on application code:

1.  `trg_inventory_txn_no_negative`: **Prevents** goods issue if stock is insufficient.
2.  `trg_inventory_balance_update`: **Auto-updates** `on_hand_qty` in the balance table whenever a transaction occurs.
3.  `trg_reservation_insert`: **Locks** inventory (moves from Available to Reserved) upon Order Confirmation.
4.  `trg_salesorder_status_history`: **Auto-logs** every status change for audit trails.

---

## ⚙️ Installation & Setup

1.  **Clone the repository**
    ```bash
    git clone [https://github.com/mthuy04/happyhome-oms.git](https://github.com/mthuy04/Sale-Management-System-for-Shopee-Seller)]https://github.com/mthuy04/Sale-Management-System-for-Shopee-Seller
    ```

2.  **Database Setup**
    - Open phpMyAdmin.
    - Create a database named `sms_shopee`.
    - Import the `database/sms_shopee.sql` file.

3.  **Configure Connection**
    - Edit `includes/config.php`:
    ```php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "sms_shopee";
    ```

4.  **Run the Project**
    - Start Apache & MySQL in XAMPP.
    - Access via browser: `http://localhost/happyhome/`

---

## 📈 Usage Scenario (Demo Flow)

1.  **Sync Orders:** Go to *Shopee Orders* > Click **Sync API** to fetch new simulation orders.
2.  **Validate:** Review raw JSON data and convert to Sales Orders.
3.  **Confirm:** Approve the order (Trigger automatically reserves stock).
4.  **Pick & Ship:** Process the shipment (Trigger deducts physical stock).
5.  **Analyze:** Open Power BI to view the impact on revenue and inventory.

---

## 👤 Author

**[NGUYỄN MINH THUÝ[**
* **Role:** Full-stack Developer & Data Analyst
* **University:** VNUIS
* **Contact:** mthuy68.work@gmail.com

---
