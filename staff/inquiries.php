<?php

session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'staff') {
    header("Location: ../login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['mark_read'])) {

    $id = (int)($_POST['inquiry_id'] ?? 0);

    /*
     * This works if your inquiries table contains a status column.
     */

    $stmt = $conn->prepare(
        "UPDATE inquiries SET status='Read' WHERE inquiry_id=?"
    );

    if ($stmt) {

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $message = "Inquiry marked as read.";
        }
    }
}

$inquiries = [];

$result = $conn->query(
    "SELECT * FROM inquiries ORDER BY inquiry_id DESC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $inquiries[] = $row;
    }

}

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Customer Inquiries | MediQuick</title>

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
    background:linear-gradient(180deg,#164e63,#0891b2,#06b6d4);
    color:white;
    padding:25px 18px;
    position:fixed;
    top:0;
    bottom:0;
    left:0;
}

.logo-area {
    text-align:center;
    margin-bottom:30px;
}

.logo-circle {
    width:60px;
    height:60px;
    margin:auto;
    background:white;
    color:#0891b2;
    border-radius:20px;
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
    color:white;
    text-decoration:none;
    padding:13px 15px;
    margin:7px 0;
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
    background:#ecfeff;
    color:#0e7490;
    padding:10px 16px;
    border-radius:30px;
    font-weight:bold;
}

.alert {
    background:#dcfce7;
    color:#166534;
    padding:15px;
    border-radius:12px;
    margin-bottom:20px;
    font-weight:bold;
}

.inquiry-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(330px,1fr));
    gap:22px;
}

.inquiry-card {
    background:white;
    padding:25px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.06);
    transition:.3s;
    animation:fadeUp .5s ease;
}

.inquiry-card:hover {
    transform:translateY(-6px);
    box-shadow:0 18px 40px rgba(0,0,0,.10);
}

.inquiry-head {
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:15px;
}

.inquiry-icon {
    width:50px;
    height:50px;
    border-radius:15px;
    background:#cffafe;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:23px;
}

.inquiry-card h3 {
    margin:0;
}

.inquiry-meta {
    color:#718096;
    font-size:13px;
    margin-top:5px;
}

.message-box {
    background:#f8fafc;
    padding:17px;
    border-radius:14px;
    margin-top:18px;
    line-height:1.7;
}

.read-btn {
    border:0;
    background:#0891b2;
    color:white;
    padding:10px 15px;
    border-radius:9px;
    font-weight:bold;
    cursor:pointer;
    margin-top:15px;
}

.read-badge {
    background:#dcfce7;
    color:#166534;
    padding:6px 10px;
    border-radius:20px;
    font-size:11px;
    font-weight:bold;
}

.new-badge {
    background:#fef3c7;
    color:#92400e;
    padding:6px 10px;
    border-radius:20px;
    font-size:11px;
    font-weight:bold;
}

.empty {
    background:white;
    padding:60px;
    border-radius:20px;
    text-align:center;
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
    <a href="customers.php">👥 Customers</a>
    <a href="inquiries.php" class="active">💬 Inquiries</a>
    <a href="profile.php">👤 My Profile</a>

    <a href="../index.php">🌐 View Website</a>

    <a href="../logout.php" class="logout">🚪 Logout</a>

</aside>

<main class="main-content">

    <div class="topbar">

        <div>

            <h1>Customer Inquiries 💬</h1>

            <p style="margin:6px 0;color:#718096;">
                Read customer questions and messages.
            </p>

        </div>

        <div class="staff-name">
            👨‍💼 <?= htmlspecialchars($_SESSION['user_name'] ?? 'Staff') ?>
        </div>

    </div>

    <?php if ($message): ?>

        <div class="alert">
            ✅ <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <div class="inquiry-grid">

    <?php foreach ($inquiries as $inquiry): ?>

        <?php

        $status = $inquiry['status'] ?? 'New';

        ?>

        <div class="inquiry-card">

            <div class="inquiry-head">

                <div style="display:flex;gap:13px;">

                    <div class="inquiry-icon">
                        💬
                    </div>

                    <div>

                        <h3>
                            <?= htmlspecialchars($inquiry['name'] ?? 'Customer') ?>
                        </h3>

                        <div class="inquiry-meta">
                            <?= htmlspecialchars($inquiry['email'] ?? '') ?>
                        </div>

                    </div>

                </div>

                <?php if (strtolower($status) === 'read'): ?>

                    <span class="read-badge">
                        Read
                    </span>

                <?php else: ?>

                    <span class="new-badge">
                        New
                    </span>

                <?php endif; ?>

            </div>

            <div class="message-box">

                <?= nl2br(
                    htmlspecialchars($inquiry['message'] ?? '')
                ) ?>

            </div>

            <div style="font-size:12px;color:#718096;margin-top:12px;">

                🕒
                <?= htmlspecialchars(
                    $inquiry['created_at'] ?? ''
                ) ?>

            </div>

            <?php if (strtolower($status) !== 'read'): ?>

                <form method="post">

                    <input
                        type="hidden"
                        name="inquiry_id"
                        value="<?= (int)$inquiry['inquiry_id'] ?>"
                    >

                    <button
                        class="read-btn"
                        name="mark_read"
                    >
                        ✓ Mark as Read
                    </button>

                </form>

            <?php endif; ?>

        </div>

    <?php endforeach; ?>

    </div>

    <?php if (count($inquiries) === 0): ?>

        <div class="empty">

            <div style="font-size:55px;">💬</div>

            <h3>No Customer Inquiries</h3>

            <p style="color:#718096;">
                Customer messages will appear here.
            </p>

        </div>

    <?php endif; ?>

</main>

</div>

</body>

</html>