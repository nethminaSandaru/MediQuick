<?php

session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'staff') {
    header("Location: ../login.php");
    exit;
}

$message = "";
$error = "";

/*
|--------------------------------------------------------------------------
| UPDATE ORDER STATUS
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_status'])) {

    $order_id = (int)($_POST['order_id'] ?? 0);
    $status = trim($_POST['status'] ?? "");

    $allowed = [
        "Pending",
        "Processing",
        "Confirmed",
        "Ready",
        "Shipped",
        "Delivered",
        "Cancelled"
    ];

    if ($order_id > 0 && in_array($status, $allowed, true)) {

        $stmt = $conn->prepare(
            "UPDATE orders SET status=? WHERE order_id=?"
        );

        $stmt->bind_param("si", $status, $order_id);

        if ($stmt->execute()) {
            $message = "Order #$order_id updated successfully.";
        } else {
            $error = "Unable to update the order.";
        }
    }
}

/*
|--------------------------------------------------------------------------
| GET ORDERS
|--------------------------------------------------------------------------
*/

$orders = [];

$sql = "
    SELECT
        o.*,
        u.first_name,
        u.last_name,
        u.email,
        u.phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_id DESC
";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Staff Orders | MediQuick</title>

<style>

* {
    box-sizing:border-box;
}

body {
    margin:0;
    font-family:Arial,Helvetica,sans-serif;
    background:#f4f8fa;
    color:#24323d;
}

.staff-layout {
    display:flex;
    min-height:100vh;
}

/* SIDEBAR */

.sidebar {
    width:260px;
    background:linear-gradient(180deg,#064e4a,#087f8c,#0e7490);
    color:white;
    padding:25px 18px;
    position:fixed;
    left:0;
    top:0;
    bottom:0;
    z-index:10;
    box-shadow:8px 0 30px rgba(0,0,0,.12);
}

.logo-area {
    text-align:center;
    margin-bottom:30px;
}

.logo-circle {
    width:60px;
    height:60px;
    background:white;
    color:#087f8c;
    border-radius:20px;
    margin:auto;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:30px;
    font-weight:bold;
    box-shadow:0 10px 25px rgba(0,0,0,.15);
}

.logo-area h2 {
    margin:12px 0 3px;
}

.logo-area small {
    opacity:.75;
}

.sidebar a {
    display:block;
    color:white;
    text-decoration:none;
    padding:13px 15px;
    margin:7px 0;
    border-radius:12px;
    transition:.3s;
    font-weight:600;
}

.sidebar a:hover,
.sidebar a.active {
    background:rgba(255,255,255,.18);
    transform:translateX(5px);
}

.logout {
    margin-top:25px !important;
    background:rgba(239,68,68,.2);
}

/* CONTENT */

.main-content {
    margin-left:260px;
    width:calc(100% - 260px);
    padding:30px;
}

.topbar {
    background:white;
    border-radius:20px;
    padding:22px 28px;
    margin-bottom:25px;
    box-shadow:0 8px 25px rgba(0,0,0,.06);
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.topbar h1 {
    margin:0;
    font-size:28px;
}

.staff-name {
    background:#ecfeff;
    color:#0f766e;
    padding:10px 16px;
    border-radius:30px;
    font-weight:bold;
}

.alert {
    padding:15px 18px;
    border-radius:12px;
    margin-bottom:20px;
    font-weight:600;
}

.success {
    background:#dcfce7;
    color:#166534;
}

.error {
    background:#fee2e2;
    color:#991b1b;
}

/* TABLE CARD */

.table-card {
    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 10px 35px rgba(0,0,0,.06);
    overflow:hidden;
    animation:fadeUp .6s ease;
}

.table-wrapper {
    overflow-x:auto;
}

table {
    width:100%;
    border-collapse:collapse;
}

th {
    background:#ecfeff;
    color:#0f766e;
    padding:15px;
    text-align:left;
    white-space:nowrap;
}

td {
    padding:15px;
    border-bottom:1px solid #edf2f4;
    vertical-align:middle;
}

tr {
    transition:.2s;
}

tbody tr:hover {
    background:#f8ffff;
}

.status {
    display:inline-block;
    padding:7px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:800;
}

.status.Pending {
    background:#fef3c7;
    color:#92400e;
}

.status.Processing {
    background:#dbeafe;
    color:#1d4ed8;
}

.status.Confirmed,
.status.Ready {
    background:#e0e7ff;
    color:#4338ca;
}

.status.Shipped {
    background:#cffafe;
    color:#0e7490;
}

.status.Delivered {
    background:#dcfce7;
    color:#166534;
}

.status.Cancelled {
    background:#fee2e2;
    color:#991b1b;
}

select {
    border:1px solid #d7e3e6;
    padding:9px;
    border-radius:9px;
    background:white;
}

.update-btn {
    border:0;
    background:#087f8c;
    color:white;
    padding:9px 13px;
    border-radius:9px;
    cursor:pointer;
    font-weight:bold;
    transition:.3s;
}

.update-btn:hover {
    background:#065f68;
    transform:translateY(-2px);
}

.empty {
    text-align:center;
    padding:60px;
    color:#718096;
}

@keyframes fadeUp {
    from {
        opacity:0;
        transform:translateY(20px);
    }
    to {
        opacity:1;
        transform:translateY(0);
    }
}

@media(max-width:900px) {

    .sidebar {
        position:relative;
        width:100%;
        min-height:auto;
    }

    .staff-layout {
        display:block;
    }

    .main-content {
        margin-left:0;
        width:100%;
        padding:18px;
    }

    .topbar {
        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }
}

</style>

</head>

<body>

<div class="staff-layout">

<aside class="sidebar">

    <div class="logo-area">

        <div class="logo-circle">✚</div>

        <h2>MediQuick</h2>

        <small>Staff Portal</small>

    </div>

    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="orders.php" class="active">📦 Orders</a>
    <a href="prescriptions.php">📋 Prescriptions</a>
    <a href="inventory.php">📊 Inventory</a>
    <a href="customers.php">👥 Customers</a>
    <a href="inquiries.php">💬 Inquiries</a>
    <a href="profile.php">👤 My Profile</a>

    <a href="../index.php">🌐 View Website</a>

    <a href="../logout.php" class="logout">🚪 Logout</a>

</aside>

<main class="main-content">

    <div class="topbar">

        <div>
            <h1>Order Management 📦</h1>
            <p style="margin:6px 0 0;color:#718096;">
                Manage customer orders and delivery progress.
            </p>
        </div>

        <div class="staff-name">
            👨‍💼 <?= htmlspecialchars($_SESSION['user_name'] ?? 'Staff') ?>
        </div>

    </div>

    <?php if ($message): ?>
        <div class="alert success">
            ✅ <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert error">
            ❌ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="table-card">

        <h2 style="margin-top:0;">Customer Orders</h2>

        <div class="table-wrapper">

        <?php if (count($orders) > 0): ?>

        <table>

            <thead>

                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Change</th>
                </tr>

            </thead>

            <tbody>

            <?php foreach ($orders as $order): ?>

                <?php
                    $currentStatus = $order['status'] ?? 'Pending';
                ?>

                <tr>

                    <td>
                        <strong>
                            #<?= (int)$order['order_id'] ?>
                        </strong>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''))
                        ) ?>
                    </td>

                    <td>
                        <small>
                            <?= htmlspecialchars($order['email'] ?? '') ?><br>
                            <?= htmlspecialchars($order['phone'] ?? '') ?>
                        </small>
                    </td>

                    <td>
                        <?= htmlspecialchars($order['created_at'] ?? '') ?>
                    </td>

                    <td>
                        <strong>
                            LKR <?= number_format((float)($order['total_amount'] ?? 0),2) ?>
                        </strong>
                    </td>

                    <td>
                        <?= htmlspecialchars($order['payment_method'] ?? 'COD') ?>
                    </td>

                    <td>

                        <span class="status <?= htmlspecialchars($currentStatus) ?>">
                            <?= htmlspecialchars($currentStatus) ?>
                        </span>

                    </td>

                    <td>

                        <form method="post">

                            <input
                                type="hidden"
                                name="order_id"
                                value="<?= (int)$order['order_id'] ?>"
                            >

                            <select name="status">

                                <?php foreach (
                                    ["Pending","Processing","Confirmed","Ready","Shipped","Delivered","Cancelled"]
                                    as $s
                                ): ?>

                                    <option
                                        value="<?= $s ?>"
                                        <?= $currentStatus === $s ? 'selected' : '' ?>
                                    >
                                        <?= $s ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <button
                                type="submit"
                                name="update_status"
                                class="update-btn"
                            >
                                Update
                            </button>

                        </form>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

        <?php else: ?>

            <div class="empty">
                <div style="font-size:50px;">📦</div>
                <h3>No Orders Yet</h3>
                <p>
                    Customer orders will appear here after checkout.
                </p>
            </div>

        <?php endif; ?>

        </div>

    </div>

</main>

</div>

</body>
</html>