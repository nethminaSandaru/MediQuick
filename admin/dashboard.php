<?php

require_once "auth.php";
require_once "../config/db.php";


/* =========================================================
   DASHBOARD STATISTICS
   ========================================================= */

$customer_count = 0;
$product_count = 0;
$order_count = 0;
$pending_prescriptions = 0;
$pending_users = 0;
$pending_orders = 0;


/* Customers */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'customer'"
);

if ($result) {
    $customer_count =
        $result->fetch_assoc()['total'];
}


/* Products */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM products"
);

if ($result) {
    $product_count =
        $result->fetch_assoc()['total'];
}


/* Orders */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM orders"
);

if ($result) {
    $order_count =
        $result->fetch_assoc()['total'];
}


/* Pending prescriptions */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM prescriptions
     WHERE status = 'Pending'"
);

if ($result) {
    $pending_prescriptions =
        $result->fetch_assoc()['total'];
}


/* Pending customer accounts */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'customer'
     AND status = 1"
);

if ($result) {
    $pending_users =
        $result->fetch_assoc()['total'];
}


/* Pending orders */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM orders
     WHERE status = 'Pending'"
);

if ($result) {
    $pending_orders =
        $result->fetch_assoc()['total'];
}


/* =========================================================
   RECENT ORDERS
   ========================================================= */

$recent_orders = $conn->query(
    "SELECT
        o.order_id,
        o.total_amount,
        o.status,
        o.created_at,
        u.first_name,
        u.last_name
     FROM orders o
     JOIN users u
        ON o.user_id = u.user_id
     ORDER BY o.order_id DESC
     LIMIT 5"
);

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Admin Dashboard - MediQuick
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        body {
            background: #f3f8f7;
        }


        .admin-wrapper {
            min-height: 100vh;
        }


        /* ADMIN HEADER */

        .admin-header {
            background: #073f47;
            color: white;
            padding: 18px 0;
        }


        .admin-header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }


        .admin-logo {
            font-size: 23px;
            font-weight: 800;
        }


        .admin-logo span {
            color: #72ddd0;
        }


        .admin-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }


        .admin-user-name {
            font-size: 14px;
        }


        .admin-logout {
            background: white;
            color: #073f47;
            padding: 9px 15px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
        }


        /* SIDEBAR + CONTENT */

        .admin-layout {
            display: grid;
            grid-template-columns: 230px 1fr;
            min-height: calc(100vh - 70px);
        }


        .admin-sidebar {
            background: white;
            border-right: 1px solid #d9e8e5;
            padding: 25px 15px;
        }


        .sidebar-title {
            color: #718087;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            margin: 0 12px 12px;
        }


        .admin-sidebar a {
            display: block;
            padding: 12px 14px;
            margin-bottom: 5px;
            border-radius: 8px;
            color: #42545a;
            font-size: 14px;
            font-weight: 600;
        }


        .admin-sidebar a:hover {
            background: #e5f8f4;
            color: #087f8c;
        }


        .admin-content {
            padding: 35px;
        }


        .dashboard-heading {
            margin-bottom: 30px;
        }


        .dashboard-heading h1 {
            color: #17323a;
            font-size: 32px;
            margin-bottom: 5px;
        }


        .dashboard-heading p {
            color: #718087;
        }


        /* STATISTICS */

        .stats-grid {
            display: grid;
            grid-template-columns:
                repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }


        .stat-card {
            background: white;
            border: 1px solid #d9e8e5;
            border-radius: 14px;
            padding: 22px;
            box-shadow:
                0 5px 20px rgba(0,0,0,0.04);
        }


        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            background: #e5f8f4;
            color: #087f8c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            margin-bottom: 15px;
        }


        .stat-number {
            font-size: 30px;
            font-weight: 800;
            color: #17323a;
        }


        .stat-title {
            font-size: 13px;
            color: #718087;
            margin-top: 3px;
        }


        /* QUICK ACTIONS */

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 25px;
        }


        .admin-card {
            background: white;
            border: 1px solid #d9e8e5;
            border-radius: 14px;
            padding: 25px;
            box-shadow:
                0 5px 20px rgba(0,0,0,0.04);
        }


        .admin-card h2 {
            color: #17323a;
            font-size: 20px;
            margin-bottom: 20px;
        }


        .quick-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }


        .quick-action {
            border: 1px solid #d9e8e5;
            border-radius: 10px;
            padding: 17px;
            transition: 0.3s;
        }


        .quick-action:hover {
            border-color: #087f8c;
            background: #eefaf7;
        }


        .quick-action strong {
            display: block;
            color: #17323a;
            margin-bottom: 4px;
        }


        .quick-action span {
            font-size: 12px;
            color: #718087;
        }


        /* ALERT BOXES */

        .alert-grid {
            display: grid;
            gap: 12px;
        }


        .admin-alert {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;

            padding: 15px;

            border-radius: 10px;

            background: #eefaf7;

            border: 1px solid #cdece6;
        }


        .admin-alert strong {
            display: block;
            color: #17323a;
        }


        .admin-alert span {
            font-size: 12px;
            color: #718087;
        }


        .admin-alert-number {
            color: #087f8c;
            font-size: 22px;
            font-weight: 800;
        }


        /* TABLE */

        .recent-orders {
            margin-top: 25px;
        }


        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }


        .admin-table th {
            text-align: left;
            padding: 12px;
            background: #eefaf7;
            color: #17323a;
            font-size: 12px;
        }


        .admin-table td {
            padding: 13px 12px;
            border-bottom: 1px solid #d9e8e5;
            font-size: 13px;
        }


        /* RESPONSIVE */

        @media(max-width:1000px) {

            .stats-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

        }


        @media(max-width:700px) {

            .admin-layout {
                grid-template-columns: 1fr;
            }

            .admin-sidebar {
                border-right: none;
                border-bottom: 1px solid #d9e8e5;
            }

            .admin-content {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .admin-header-inner {
                flex-direction: column;
                align-items: flex-start;
            }

        }

    </style>

</head>


<body>


<div class="admin-wrapper">


    <!-- =================================================
         ADMIN HEADER
         ================================================= -->

    <header class="admin-header">

        <div class="container admin-header-inner">

            <div class="admin-logo">

                ✚
                <span>MediQuick</span>
                Admin Panel

            </div>


            <div class="admin-user">

                <span class="admin-user-name">

                    Welcome,
                    <?= htmlspecialchars(
                        $_SESSION['user_name'] ?? 'Administrator'
                    ) ?>

                </span>


                <a
                    href="../logout.php"
                    class="admin-logout"
                >
                    Logout
                </a>

            </div>

        </div>

    </header>



    <!-- =================================================
         ADMIN LAYOUT
         ================================================= -->

    <div class="admin-layout">


        <!-- SIDEBAR -->

        <aside class="admin-sidebar">


            <div class="sidebar-title">
                Main Menu
            </div>


            <a href="dashboard.php">
                📊 Dashboard
            </a>


            <a href="users.php">
                👥 Manage Customers
            </a>


            <a href="products.php">
                💊 Manage Products
            </a>


            <a href="orders.php">
                📦 Manage Orders
            </a>


            <a href="prescriptions.php">
                📄 Prescriptions
            </a>


            <a href="inquiries.php">
                💬 Customer Inquiries
            </a>


            <div
                class="sidebar-title"
                style="margin-top:30px;"
            >
                Website
            </div>


            <a href="../index.php">
                🏠 View Website
            </a>


            <a href="../logout.php">
                🚪 Logout
            </a>


        </aside>



        <!-- MAIN CONTENT -->

        <main class="admin-content">


            <div class="dashboard-heading">

                <h1>
                    Admin Dashboard
                </h1>

                <p>
                    Manage MediQuick Pharmacy operations from one place.
                </p>

            </div>



            <!-- =================================================
                 STATISTICS
                 ================================================= -->

            <div class="stats-grid">


                <div class="stat-card">

                    <div class="stat-icon">
                        👥
                    </div>

                    <div class="stat-number">
                        <?= $customer_count ?>
                    </div>

                    <div class="stat-title">
                        Total Customers
                    </div>

                </div>



                <div class="stat-card">

                    <div class="stat-icon">
                        💊
                    </div>

                    <div class="stat-number">
                        <?= $product_count ?>
                    </div>

                    <div class="stat-title">
                        Total Products
                    </div>

                </div>



                <div class="stat-card">

                    <div class="stat-icon">
                        📦
                    </div>

                    <div class="stat-number">
                        <?= $order_count ?>
                    </div>

                    <div class="stat-title">
                        Total Orders
                    </div>

                </div>



                <div class="stat-card">

                    <div class="stat-icon">
                        📄
                    </div>

                    <div class="stat-number">
                        <?= $pending_prescriptions ?>
                    </div>

                    <div class="stat-title">
                        Pending Prescriptions
                    </div>

                </div>


            </div>



            <!-- =================================================
                 DASHBOARD CONTENT
                 ================================================= -->

            <div class="dashboard-grid">


                <!-- QUICK ACTIONS -->

                <div class="admin-card">

                    <h2>
                        Quick Actions
                    </h2>


                    <div class="quick-actions">


                        <a
                            class="quick-action"
                            href="users.php"
                        >

                            <strong>
                                👥 Manage Customers
                            </strong>

                            <span>
                                Approve customer accounts
                            </span>

                        </a>


                        <a
                            class="quick-action"
                            href="products.php"
                        >

                            <strong>
                                💊 Manage Products
                            </strong>

                            <span>
                                Add and manage medicines
                            </span>

                        </a>


                        <a
                            class="quick-action"
                            href="orders.php"
                        >

                            <strong>
                                📦 Manage Orders
                            </strong>

                            <span>
                                Process customer orders
                            </span>

                        </a>


                        <a
                            class="quick-action"
                            href="prescriptions.php"
                        >

                            <strong>
                                📄 Review Prescriptions
                            </strong>

                            <span>
                                Verify uploaded prescriptions
                            </span>

                        </a>


                        <a
                            class="quick-action"
                            href="inquiries.php"
                        >

                            <strong>
                                💬 Customer Inquiries
                            </strong>

                            <span>
                                View customer messages
                            </span>

                        </a>


                        <a
                            class="quick-action"
                            href="../index.php"
                        >

                            <strong>
                                🌐 View Website
                            </strong>

                            <span>
                                Open customer website
                            </span>

                        </a>


                    </div>

                </div>



                <!-- ALERTS -->

                <div class="admin-card">

                    <h2>
                        Action Required
                    </h2>


                    <div class="alert-grid">


                        <a
                            href="users.php"
                            class="admin-alert"
                        >

                            <div>

                                <strong>
                                    Customer Approvals
                                </strong>

                                <span>
                                    New accounts waiting
                                </span>

                            </div>


                            <div class="admin-alert-number">
                                <?= $pending_users ?>
                            </div>

                        </a>



                        <a
                            href="prescriptions.php"
                            class="admin-alert"
                        >

                            <div>

                                <strong>
                                    Prescriptions
                                </strong>

                                <span>
                                    Need verification
                                </span>

                            </div>


                            <div class="admin-alert-number">
                                <?= $pending_prescriptions ?>
                            </div>

                        </a>



                        <a
                            href="orders.php"
                            class="admin-alert"
                        >

                            <div>

                                <strong>
                                    New Orders
                                </strong>

                                <span>
                                    Waiting for processing
                                </span>

                            </div>


                            <div class="admin-alert-number">
                                <?= $pending_orders ?>
                            </div>

                        </a>


                    </div>

                </div>


            </div>



            <!-- =================================================
                 RECENT ORDERS
                 ================================================= -->

            <div class="admin-card recent-orders">

                <h2>
                    Recent Orders
                </h2>


                <?php if (
                    $recent_orders &&
                    $recent_orders->num_rows > 0
                ): ?>


                    <div style="overflow-x:auto;">

                        <table class="admin-table">

                            <thead>

                                <tr>

                                    <th>
                                        Order
                                    </th>

                                    <th>
                                        Customer
                                    </th>

                                    <th>
                                        Date
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


                            <?php while (
                                $order =
                                $recent_orders->fetch_assoc()
                            ): ?>


                                <tr>

                                    <td>
                                        #<?= (int)$order['order_id'] ?>
                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $order['first_name'] .
                                            " " .
                                            $order['last_name']
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $order['created_at']
                                        ) ?>

                                    </td>


                                    <td>

                                        LKR
                                        <?= number_format(
                                            $order['total_amount'],
                                            2
                                        ) ?>

                                    </td>


                                    <td>

                                        <span class="badge">

                                            <?= htmlspecialchars(
                                                $order['status']
                                            ) ?>

                                        </span>

                                    </td>

                                </tr>


                            <?php endwhile; ?>


                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <div class="card center">

                        <h3>
                            No Orders Yet
                        </h3>

                        <p class="muted">
                            Customer orders will appear here.
                        </p>

                    </div>


                <?php endif; ?>


            </div>


        </main>

    </div>

</div>


</body>

</html>