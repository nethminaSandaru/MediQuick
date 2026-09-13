<?php

session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'staff') {
    header("Location: ../login.php");
    exit;
}

$search = trim($_GET['search'] ?? "");

$customers = [];

$sql = "
    SELECT
        u.*,
        (
            SELECT COUNT(*)
            FROM orders o
            WHERE o.user_id = u.user_id
        ) AS order_count
    FROM users u
    WHERE u.role = 'customer'
";

if ($search !== "") {
    $sql .= "
        AND (
            u.first_name LIKE ?
            OR u.last_name LIKE ?
            OR u.email LIKE ?
            OR u.phone LIKE ?
        )
    ";
}

$sql .= " ORDER BY u.user_id DESC";

$stmt = $conn->prepare($sql);

if ($search !== "") {

    $like = "%" . $search . "%";

    $stmt->bind_param(
        "ssss",
        $like,
        $like,
        $like,
        $like
    );
}

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Customers | MediQuick Staff</title>

<style>

* {
    box-sizing:border-box;
}

body {
    margin:0;
    font-family:Arial,Helvetica,sans-serif;
    background:#f4f8fa;
    color:#26343d;
}

.staff-layout {
    display:flex;
    min-height:100vh;
}

.sidebar {
    width:260px;
    background:linear-gradient(180deg,#7c2d12,#ea580c,#f97316);
    color:white;
    padding:25px 18px;
    position:fixed;
    left:0;
    top:0;
    bottom:0;
    box-shadow:8px 0 30px rgba(0,0,0,.12);
}

.logo-area {
    text-align:center;
    margin-bottom:30px;
}

.logo-circle {
    width:60px;
    height:60px;
    margin:auto;
    border-radius:20px;
    background:white;
    color:#ea580c;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:30px;
}

.logo-area h2 {
    margin:12px 0 3px;
}

.logo-area small {
    opacity:.75;
}

.sidebar a {
    display:block;
    padding:13px 15px;
    margin:7px 0;
    color:white;
    text-decoration:none;
    border-radius:12px;
    font-weight:600;
    transition:.3s;
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

.main-content {
    margin-left:260px;
    width:calc(100% - 260px);
    padding:30px;
}

.topbar {
    background:white;
    padding:22px 28px;
    border-radius:20px;
    margin-bottom:25px;
    box-shadow:0 8px 25px rgba(0,0,0,.06);
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.topbar h1 {
    margin:0;
}

.staff-name {
    background:#fff7ed;
    color:#c2410c;
    padding:10px 16px;
    border-radius:30px;
    font-weight:bold;
}

.search-card {
    background:white;
    padding:20px;
    border-radius:20px;
    margin-bottom:25px;
    box-shadow:0 8px 25px rgba(0,0,0,.05);
}

.search-form {
    display:flex;
    gap:12px;
}

.search-form input {
    flex:1;
    padding:14px;
    border:1px solid #dbe5e8;
    border-radius:10px;
}

.search-form button {
    background:#ea580c;
    color:white;
    border:0;
    padding:13px 25px;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
}

.customer-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
    gap:22px;
}

.customer-card {
    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 10px 30px rgba(0,0,0,.06);
    transition:.3s;
    animation:fadeUp .5s ease;
}

.customer-card:hover {
    transform:translateY(-6px);
    box-shadow:0 18px 40px rgba(0,0,0,.10);
}

.avatar {
    width:65px;
    height:65px;
    border-radius:50%;
    background:linear-gradient(135deg,#fb923c,#ea580c);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:25px;
    font-weight:bold;
}

.customer-header {
    display:flex;
    align-items:center;
    gap:15px;
}

.customer-header h3 {
    margin:0 0 5px;
}

.customer-info {
    margin-top:20px;
}

.info-row {
    display:flex;
    gap:10px;
    padding:9px 0;
    border-bottom:1px solid #f0f2f3;
}

.info-label {
    font-weight:bold;
    min-width:75px;
}

.order-count {
    display:inline-block;
    margin-top:15px;
    padding:8px 13px;
    background:#fff7ed;
    color:#c2410c;
    border-radius:20px;
    font-weight:bold;
}

.status {
    display:inline-block;
    padding:6px 10px;
    border-radius:20px;
    font-size:11px;
    font-weight:bold;
}

.status-active {
    background:#dcfce7;
    color:#166534;
}

.status-pending {
    background:#fef3c7;
    color:#92400e;
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

    .search-form {
        flex-direction:column;
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
    <a href="orders.php">📦 Orders</a>
    <a href="prescriptions.php">📋 Prescriptions</a>
    <a href="inventory.php">📊 Inventory</a>
    <a href="customers.php" class="active">👥 Customers</a>
    <a href="inquiries.php">💬 Inquiries</a>
    <a href="profile.php">👤 My Profile</a>

    <a href="../index.php">🌐 View Website</a>

    <a href="../logout.php" class="logout">🚪 Logout</a>

</aside>

<main class="main-content">

    <div class="topbar">

        <div>

            <h1>Customer Management 👥</h1>

            <p style="margin:6px 0;color:#718096;">
                View registered MediQuick customers.
            </p>

        </div>

        <div class="staff-name">
            👨‍💼 <?= htmlspecialchars($_SESSION['user_name'] ?? 'Staff') ?>
        </div>

    </div>

    <div class="search-card">

        <form method="get" class="search-form">

            <input
                type="text"
                name="search"
                placeholder="Search by name, email or phone..."
                value="<?= htmlspecialchars($search) ?>"
            >

            <button>
                🔎 Search
            </button>

        </form>

    </div>

    <div class="customer-grid">

    <?php foreach ($customers as $customer): ?>

        <?php

        $fullName = trim(
            ($customer['first_name'] ?? '') .
            ' ' .
            ($customer['last_name'] ?? '')
        );

        $initial = strtoupper(
            substr($customer['first_name'] ?? 'C', 0, 1)
        );

        $status = (int)($customer['status'] ?? 0);

        ?>

        <div class="customer-card">

            <div class="customer-header">

                <div class="avatar">
                    <?= htmlspecialchars($initial) ?>
                </div>

                <div>

                    <h3>
                        <?= htmlspecialchars($fullName) ?>
                    </h3>

                    <?php if ($status === 0): ?>

                        <span class="status status-active">
                            Active
                        </span>

                    <?php else: ?>

                        <span class="status status-pending">
                            Pending
                        </span>

                    <?php endif; ?>

                </div>

            </div>

            <div class="customer-info">

                <div class="info-row">

                    <span class="info-label">📧 Email</span>

                    <span>
                        <?= htmlspecialchars($customer['email'] ?? '') ?>
                    </span>

                </div>

                <div class="info-row">

                    <span class="info-label">📱 Phone</span>

                    <span>
                        <?= htmlspecialchars($customer['phone'] ?? '') ?>
                    </span>

                </div>

                <div class="info-row">

                    <span class="info-label">📍 City</span>

                    <span>
                        <?= htmlspecialchars($customer['city'] ?? '') ?>
                    </span>

                </div>

                <div class="info-row">

                    <span class="info-label">🏠 Address</span>

                    <span>
                        <?= htmlspecialchars($customer['address'] ?? '') ?>
                    </span>

                </div>

                <span class="order-count">
                    📦 <?= (int)($customer['order_count'] ?? 0) ?>
                    Orders
                </span>

            </div>

        </div>

    <?php endforeach; ?>

    </div>

    <?php if (count($customers) === 0): ?>

        <div style="
            background:white;
            padding:60px;
            border-radius:20px;
            text-align:center;
        ">

            <div style="font-size:55px;">👥</div>

            <h3>No customers found</h3>

            <p style="color:#718096;">
                Try another search.
            </p>

        </div>

    <?php endif; ?>

</main>

</div>

</body>

</html>