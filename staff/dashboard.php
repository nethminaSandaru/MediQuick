<?php

session_start();

require_once "../config/db.php";


/* =========================================================
   STAFF ACCESS PROTECTION
   ========================================================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit;

}


/* Only staff can access this page */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {

    header("Location: ../index.php");
    exit;

}


/* =========================================================
   STAFF INFORMATION
   ========================================================= */

$staff_name = $_SESSION['user_name'] ?? 'Staff Member';


/* =========================================================
   DASHBOARD STATISTICS
   ========================================================= */


/* Total orders */

$total_orders = 0;

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
");

if ($result) {

    $row = $result->fetch_assoc();

    $total_orders = (int)$row['total'];

}


/* Pending orders */

$pending_orders = 0;

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE LOWER(status) = 'pending'
");

if ($result) {

    $row = $result->fetch_assoc();

    $pending_orders = (int)$row['total'];

}


/* Total products */

$total_products = 0;

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM products
");

if ($result) {

    $row = $result->fetch_assoc();

    $total_products = (int)$row['total'];

}


/* Low stock */

$low_stock = 0;

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM products
    WHERE stock_qty <= 10
");

if ($result) {

    $row = $result->fetch_assoc();

    $low_stock = (int)$row['total'];

}


/* Pending prescriptions */

$pending_prescriptions = 0;

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM prescriptions
    WHERE LOWER(status) = 'pending'
");

if ($result) {

    $row = $result->fetch_assoc();

    $pending_prescriptions = (int)$row['total'];

}


/* Customer inquiries */

$total_inquiries = 0;

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM inquiries
");

if ($result) {

    $row = $result->fetch_assoc();

    $total_inquiries = (int)$row['total'];

}


/* =========================================================
   RECENT ORDERS
   ========================================================= */

$recent_orders = [];

$result = $conn->query("
    SELECT
        o.order_id,
        o.total_amount,
        o.payment_method,
        o.status,
        o.created_at,
        u.first_name,
        u.last_name
    FROM orders o
    LEFT JOIN users u
        ON o.user_id = u.user_id
    ORDER BY o.order_id DESC
    LIMIT 6
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $recent_orders[] = $row;

    }

}


/* =========================================================
   LOW STOCK PRODUCTS
   ========================================================= */

$low_stock_products = [];

$result = $conn->query("
    SELECT
        product_id,
        name,
        category,
        stock_qty,
        price
    FROM products
    WHERE stock_qty <= 10
    ORDER BY stock_qty ASC
    LIMIT 6
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $low_stock_products[] = $row;

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Staff Dashboard | MediQuick Pharmacy</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f3f8f8;

            color: #213f43;

        }


        a {
            text-decoration: none;
        }


        /* =====================================================
           SIDEBAR
           ===================================================== */

        .staff-sidebar {

            position: fixed;

            left: 0;
            top: 0;
            bottom: 0;

            width: 255px;

            background:
                linear-gradient(
                    180deg,
                    #087f8c 0%,
                    #05636d 100%
                );

            color: white;

            padding: 25px 18px;

            z-index: 100;

        }


        .staff-logo {

            display: flex;

            align-items: center;

            gap: 10px;

            padding: 0 12px 25px;

            border-bottom:
                1px solid
                rgba(255,255,255,0.18);

        }


        .staff-logo-icon {

            width: 42px;
            height: 42px;

            border-radius: 12px;

            background: white;

            color: #087f8c;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 24px;

            font-weight: 900;

        }


        .staff-logo-text strong {

            display: block;

            font-size: 20px;

        }


        .staff-logo-text span {

            display: block;

            font-size: 11px;

            opacity: .75;

            margin-top: 3px;

        }


        /* NAVIGATION */

        .staff-nav {

            margin-top: 28px;

        }


        .staff-nav-title {

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: 1.2px;

            opacity: .65;

            padding: 0 12px;

            margin-bottom: 10px;

        }


        .staff-nav a {

            display: flex;

            align-items: center;

            gap: 12px;

            color: white;

            padding: 13px 12px;

            border-radius: 10px;

            margin-bottom: 5px;

            font-size: 14px;

            transition: .2s;

        }


        .staff-nav a:hover,
        .staff-nav a.active {

            background:
                rgba(255,255,255,0.15);

        }


        .staff-nav-icon {

            width: 24px;

            text-align: center;

            font-size: 17px;

        }


        .staff-sidebar-bottom {

            position: absolute;

            left: 18px;
            right: 18px;
            bottom: 20px;

        }


        .back-site {

            display: block;

            text-align: center;

            color: white;

            border:
                1px solid
                rgba(255,255,255,.3);

            padding: 10px;

            border-radius: 9px;

            font-size: 13px;

            margin-bottom: 8px;

        }


        .logout-link {

            display: block;

            text-align: center;

            color: #ffffff;

            background:
                rgba(0,0,0,.16);

            padding: 10px;

            border-radius: 9px;

            font-size: 13px;

        }


        /* =====================================================
           MAIN
           ===================================================== */

        .staff-main {

            margin-left: 255px;

            min-height: 100vh;

            padding: 28px 35px 50px;

        }


        /* TOP BAR */

        .topbar {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 30px;

        }


        .welcome-title h1 {

            margin: 0;

            color: #173d42;

            font-size: 28px;

        }


        .welcome-title p {

            margin: 7px 0 0;

            color: #74878a;

            font-size: 14px;

        }


        .staff-user {

            display: flex;

            align-items: center;

            gap: 12px;

            background: white;

            padding: 9px 14px;

            border-radius: 14px;

            box-shadow:
                0 4px 18px
                rgba(30,70,75,.06);

        }


        .staff-avatar {

            width: 40px;
            height: 40px;

            border-radius: 50%;

            background: #dff5f2;

            color: #087f8c;

            display: flex;

            align-items: center;

            justify-content: center;

            font-weight: 800;

        }


        .staff-user strong {

            display: block;

            font-size: 13px;

        }


        .staff-user span {

            font-size: 11px;

            color: #849497;

        }


        /* =====================================================
           QUICK NOTICE
           ===================================================== */

        .notice-banner {

            background:
                linear-gradient(
                    135deg,
                    #ffffff,
                    #edf9f7
                );

            border:
                1px solid
                #d9eeeb;

            border-radius: 18px;

            padding: 20px 24px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 25px;

        }


        .notice-left {

            display: flex;

            gap: 15px;

            align-items: center;

        }


        .notice-icon {

            width: 45px;
            height: 45px;

            border-radius: 12px;

            background: #dff5f2;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 21px;

        }


        .notice-banner strong {

            color: #17464b;

            font-size: 14px;

        }


        .notice-banner p {

            margin: 4px 0 0;

            color: #738386;

            font-size: 12px;

        }


        .date-badge {

            background: white;

            border: 1px solid #dcebea;

            padding: 9px 14px;

            border-radius: 10px;

            color: #587074;

            font-size: 12px;

        }


        /* =====================================================
           STAT CARDS
           ===================================================== */

        .stats-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 25px;

        }


        .stat-card {

            background: white;

            border-radius: 17px;

            padding: 20px;

            border:
                1px solid
                #e3eeee;

            box-shadow:
                0 5px 20px
                rgba(25,70,75,.05);

            position: relative;

            overflow: hidden;

        }


        .stat-card::after {

            content: "";

            position: absolute;

            width: 70px;
            height: 70px;

            border-radius: 50%;

            right: -30px;
            top: -30px;

            background: #eef8f7;

        }


        .stat-icon {

            width: 43px;
            height: 43px;

            border-radius: 11px;

            background: #e3f6f3;

            color: #087f8c;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 19px;

            margin-bottom: 15px;

        }


        .stat-card h3 {

            margin: 0;

            font-size: 27px;

            color: #183f44;

        }


        .stat-card p {

            margin: 5px 0 0;

            color: #78898c;

            font-size: 12px;

        }


        /* =====================================================
           CONTENT GRID
           ===================================================== */

        .content-grid {

            display: grid;

            grid-template-columns:
                1.6fr 1fr;

            gap: 22px;

            margin-bottom: 25px;

        }


        .panel {

            background: white;

            border-radius: 18px;

            border:
                1px solid
                #e2eded;

            box-shadow:
                0 5px 20px
                rgba(25,70,75,.05);

            overflow: hidden;

        }


        .panel-header {

            padding: 20px 22px;

            border-bottom:
                1px solid
                #edf2f2;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .panel-header h2 {

            margin: 0;

            color: #1d4247;

            font-size: 17px;

        }


        .panel-header span {

            font-size: 11px;

            color: #829093;

        }


        .panel-body {

            padding: 0 20px 10px;

        }


        /* =====================================================
           ORDERS TABLE
           ===================================================== */

        .order-table {

            width: 100%;

            border-collapse: collapse;

        }


        .order-table th {

            text-align: left;

            font-size: 10px;

            text-transform: uppercase;

            color: #8b999b;

            padding: 14px 5px;

            letter-spacing: .5px;

        }


        .order-table td {

            padding: 13px 5px;

            border-top:
                1px solid
                #f0f4f4;

            font-size: 12px;

            color: #53686b;

        }


        .order-number {

            font-weight: 800;

            color: #087f8c;

        }


        .customer-name {

            font-weight: 700;

            color: #304f53;

        }


        .status {

            display: inline-block;

            padding: 5px 9px;

            border-radius: 15px;

            font-size: 10px;

            font-weight: 800;

            background: #edf5f5;

            color: #587074;

        }


        .status.pending {

            background: #fff3d8;

            color: #9a720c;

        }


        .status.processing {

            background: #e4f1ff;

            color: #28659c;

        }


        .status.completed,
        .status.delivered {

            background: #e8f7ed;

            color: #258044;

        }


        /* =====================================================
           LOW STOCK
           ===================================================== */

        .stock-item {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 14px 2px;

            border-bottom:
                1px solid
                #f0f4f4;

        }


        .stock-item:last-child {

            border-bottom: none;

        }


        .stock-name {

            font-weight: 700;

            color: #334f53;

            font-size: 13px;

        }


        .stock-category {

            color: #899799;

            font-size: 10px;

            margin-top: 3px;

        }


        .stock-number {

            min-width: 55px;

            text-align: center;

            padding: 6px 8px;

            border-radius: 8px;

            font-size: 11px;

            font-weight: 800;

        }


        .stock-danger {

            background: #fde9e9;

            color: #c03939;

        }


        .stock-warning {

            background: #fff3d9;

            color: #9a720c;

        }


        .no-data {

            text-align: center;

            padding: 35px 15px;

            color: #8b999b;

            font-size: 13px;

        }


        /* =====================================================
           QUICK ACTIONS
           ===================================================== */

        .quick-actions {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 16px;

        }


        .quick-action {

            background: white;

            border:
                1px solid
                #e3eeee;

            border-radius: 16px;

            padding: 20px;

            text-align: center;

            color: #31555a;

            box-shadow:
                0 5px 18px
                rgba(25,70,75,.04);

            transition: .2s;

        }


        .quick-action:hover {

            transform: translateY(-4px);

            box-shadow:
                0 12px 25px
                rgba(25,70,75,.09);

        }


        .quick-action-icon {

            width: 45px;
            height: 45px;

            margin: 0 auto 10px;

            border-radius: 12px;

            background: #e3f6f3;

            color: #087f8c;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 20px;

        }


        .quick-action strong {

            display: block;

            font-size: 13px;

        }


        .quick-action span {

            display: block;

            color: #899799;

            font-size: 10px;

            margin-top: 4px;

        }


        /* =====================================================
           MOBILE
           ===================================================== */

        @media(max-width: 1100px) {

            .stats-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

            .quick-actions {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media(max-width: 850px) {

            .staff-sidebar {

                position: relative;

                width: 100%;

                height: auto;

            }

            .staff-sidebar-bottom {

                position: relative;

                left: auto;
                right: auto;
                bottom: auto;

                margin-top: 20px;

            }

            .staff-main {

                margin-left: 0;

                padding: 20px;

            }

            .content-grid {

                grid-template-columns: 1fr;

            }

            .filter-form {

                grid-template-columns: 1fr;

            }

        }


        @media(max-width: 550px) {

            .stats-grid {

                grid-template-columns: 1fr;

            }

            .quick-actions {

                grid-template-columns: 1fr;

            }

            .topbar {

                flex-direction: column;

                align-items: flex-start;

                gap: 15px;

            }

            .notice-banner {

                flex-direction: column;

                align-items: flex-start;

                gap: 15px;

            }

            .order-table {

                font-size: 10px;

            }

            .panel-body {

                overflow-x: auto;

            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
     ========================================================= -->

<aside class="staff-sidebar">


    <div class="staff-logo">

        <div class="staff-logo-icon">
            ✚
        </div>

        <div class="staff-logo-text">

            <strong>MediQuick</strong>

            <span>PHARMACY STAFF</span>

        </div>

    </div>


    <nav class="staff-nav">

        <div class="staff-nav-title">
            Main Menu
        </div>


        <a
            href="dashboard.php"
            class="active"
        >

            <span class="staff-nav-icon">▦</span>

            Dashboard

        </a>


        <a href="orders.php">

            <span class="staff-nav-icon">🛍</span>

            Orders

        </a>


        <a href="prescriptions.php">

            <span class="staff-nav-icon">📄</span>

            Prescriptions

        </a>


        <a href="inventory.php">

            <span class="staff-nav-icon">📦</span>

            Inventory

        </a>


        <div
            class="staff-nav-title"
            style="margin-top:25px;"
        >
            Customer Care
        </div>


        <a href="customers.php">

            <span class="staff-nav-icon">👥</span>

            Customers

        </a>


        <a href="inquiries.php">

            <span class="staff-nav-icon">💬</span>

            Inquiries

        </a>


        <a href="profile.php">

            <span class="staff-nav-icon">⚙</span>

            My Profile

        </a>

    </nav>


    <div class="staff-sidebar-bottom">

        <a
            href="../index.php"
            class="back-site"
        >
            ← Back to Website
        </a>


        <a
            href="../logout.php"
            class="logout-link"
        >
            Logout
        </a>

    </div>

</aside>



<!-- =========================================================
     MAIN CONTENT
     ========================================================= -->

<main class="staff-main">


    <!-- TOP BAR -->

    <div class="topbar">

        <div class="welcome-title">

            <h1>
                Good evening, <?= htmlspecialchars($staff_name) ?> 👋
            </h1>

            <p>
                Here's what's happening at MediQuick Pharmacy today.
            </p>

        </div>


        <div class="staff-user">

            <div class="staff-avatar">

                <?= strtoupper(substr($staff_name, 0, 1)) ?>

            </div>

            <div>

                <strong>
                    <?= htmlspecialchars($staff_name) ?>
                </strong>

                <span>
                    Pharmacy Staff
                </span>

            </div>

        </div>

    </div>



    <!-- NOTICE -->

    <div class="notice-banner">

        <div class="notice-left">

            <div class="notice-icon">
                💊
            </div>

            <div>

                <strong>
                    Staff Operations Center
                </strong>

                <p>
                    Review orders, prescriptions and stock levels
                    to keep customer service running smoothly.
                </p>

            </div>

        </div>


        <div class="date-badge">

            <?= date("l, d M Y") ?>

        </div>

    </div>



    <!-- STATISTICS -->

    <div class="stats-grid">


        <div class="stat-card">

            <div class="stat-icon">
                🛍
            </div>

            <h3>
                <?= $total_orders ?>
            </h3>

            <p>
                Total Orders
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ⏳
            </div>

            <h3>
                <?= $pending_orders ?>
            </h3>

            <p>
                Pending Orders
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                📄
            </div>

            <h3>
                <?= $pending_prescriptions ?>
            </h3>

            <p>
                Pending Prescriptions
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ⚠
            </div>

            <h3>
                <?= $low_stock ?>
            </h3>

            <p>
                Low Stock Products
            </p>

        </div>

    </div>



    <!-- RECENT ORDERS + LOW STOCK -->

    <div class="content-grid">


        <!-- RECENT ORDERS -->

        <section class="panel">

            <div class="panel-header">

                <h2>
                    Recent Orders
                </h2>

                <span>
                    Latest 6 orders
                </span>

            </div>


            <div class="panel-body">

                <?php if (!empty($recent_orders)): ?>

                    <table class="order-table">

                        <thead>

                            <tr>

                                <th>
                                    Order
                                </th>

                                <th>
                                    Customer
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($recent_orders as $order): ?>

                            <?php

                            $status =
                                strtolower(
                                    trim(
                                        $order['status']
                                    )
                                );

                            ?>

                            <tr>

                                <td>

                                    <span class="order-number">

                                        #<?= (int)$order['order_id'] ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="customer-name">

                                        <?= htmlspecialchars(
                                            trim(
                                                ($order['first_name'] ?? '') .
                                                ' ' .
                                                ($order['last_name'] ?? '')
                                            )
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    LKR
                                    <?= number_format(
                                        $order['total_amount'],
                                        2
                                    ) ?>

                                </td>


                                <td>

                                    <span
                                        class="status <?= htmlspecialchars($status) ?>"
                                    >

                                        <?= htmlspecialchars(
                                            ucfirst($status)
                                        ) ?>

                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php else: ?>

                    <div class="no-data">

                        🛍

                        <br><br>

                        No orders have been placed yet.

                    </div>

                <?php endif; ?>

            </div>

        </section>



        <!-- LOW STOCK -->

        <section class="panel">

            <div class="panel-header">

                <h2>
                    Low Stock Alert
                </h2>

                <span>
                    10 or fewer
                </span>

            </div>


            <div class="panel-body">

                <?php if (!empty($low_stock_products)): ?>

                    <?php foreach ($low_stock_products as $product): ?>

                        <div class="stock-item">

                            <div>

                                <div class="stock-name">

                                    <?= htmlspecialchars(
                                        $product['name']
                                    ) ?>

                                </div>

                                <div class="stock-category">

                                    <?= htmlspecialchars(
                                        $product['category']
                                    ) ?>

                                </div>

                            </div>


                            <div
                                class="stock-number
                                <?= (int)$product['stock_qty'] <= 5
                                    ? 'stock-danger'
                                    : 'stock-warning' ?>"
                            >

                                <?= (int)$product['stock_qty'] ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="no-data">

                        ✓

                        <br><br>

                        All products have sufficient stock.

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </div>



    <!-- QUICK ACTIONS -->

    <section class="panel">

        <div class="panel-header">

            <h2>
                Quick Actions
            </h2>

            <span>
                Staff shortcuts
            </span>

        </div>


        <div
            class="panel-body"
            style="padding:20px;"
        >

            <div class="quick-actions">


                <a
                    href="orders.php"
                    class="quick-action"
                >

                    <div class="quick-action-icon">
                        🛍
                    </div>

                    <strong>
                        Manage Orders
                    </strong>

                    <span>
                        View customer orders
                    </span>

                </a>


                <a
                    href="prescriptions.php"
                    class="quick-action"
                >

                    <div class="quick-action-icon">
                        📄
                    </div>

                    <strong>
                        Prescriptions
                    </strong>

                    <span>
                        Review prescriptions
                    </span>

                </a>


                <a
                    href="inventory.php"
                    class="quick-action"
                >

                    <div class="quick-action-icon">
                        📦
                    </div>

                    <strong>
                        Check Inventory
                    </strong>

                    <span>
                        Monitor medicine stock
                    </span>

                </a>


                <a
                    href="inquiries.php"
                    class="quick-action"
                >

                    <div class="quick-action-icon">
                        💬
                    </div>

                    <strong>
                        Customer Inquiries
                    </strong>

                    <span>
                        Respond to customers
                    </span>

                </a>


            </div>

        </div>

    </section>


</main>


</body>

</html>