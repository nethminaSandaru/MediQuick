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
| UPDATE PRESCRIPTION
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_prescription'])) {

    $prescription_id = (int)($_POST['prescription_id'] ?? 0);
    $status = trim($_POST['status'] ?? "");

    $allowed = [
        "Pending",
        "Approved",
        "Rejected",
        "Processing",
        "Completed"
    ];

    if ($prescription_id > 0 && in_array($status, $allowed, true)) {

        $stmt = $conn->prepare(
            "UPDATE prescriptions SET status=? WHERE prescription_id=?"
        );

        if ($stmt) {

            $stmt->bind_param("si", $status, $prescription_id);

            if ($stmt->execute()) {
                $message = "Prescription #$prescription_id updated.";
            } else {
                $error = "Could not update prescription.";
            }

        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}

/*
|--------------------------------------------------------------------------
| LOAD PRESCRIPTIONS
|--------------------------------------------------------------------------
*/

$prescriptions = [];

$sql = "
    SELECT
        p.*,
        u.first_name,
        u.last_name,
        u.email,
        u.phone
    FROM prescriptions p
    LEFT JOIN users u ON p.user_id = u.user_id
    ORDER BY p.prescription_id DESC
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $prescriptions[] = $row;
    }

}

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Prescription Management | MediQuick</title>

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

.sidebar {
    width:260px;
    background:linear-gradient(180deg,#312e81,#4f46e5,#7c3aed);
    color:white;
    padding:25px 18px;
    position:fixed;
    top:0;
    bottom:0;
    left:0;
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
    color:#4f46e5;
    border-radius:20px;
    margin:auto;
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
    background:#eef2ff;
    color:#4338ca;
    padding:10px 16px;
    border-radius:30px;
    font-weight:bold;
}

.alert {
    padding:15px 18px;
    border-radius:12px;
    margin-bottom:20px;
    font-weight:bold;
}

.success {
    background:#dcfce7;
    color:#166534;
}

.error {
    background:#fee2e2;
    color:#991b1b;
}

.card {
    background:white;
    border-radius:20px;
    padding:25px;
    box-shadow:0 10px 35px rgba(0,0,0,.06);
}

.table-wrapper {
    overflow-x:auto;
}

table {
    width:100%;
    border-collapse:collapse;
}

th {
    background:#eef2ff;
    color:#4338ca;
    padding:15px;
    text-align:left;
}

td {
    padding:15px;
    border-bottom:1px solid #edf2f4;
}

tr:hover td {
    background:#fafaff;
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

.status.Approved,
.status.Completed {
    background:#dcfce7;
    color:#166534;
}

.status.Rejected {
    background:#fee2e2;
    color:#991b1b;
}

.status.Processing {
    background:#dbeafe;
    color:#1d4ed8;
}

select {
    padding:9px;
    border:1px solid #dbe2e8;
    border-radius:9px;
}

button {
    border:0;
    padding:9px 13px;
    border-radius:9px;
    background:#4f46e5;
    color:white;
    font-weight:bold;
    cursor:pointer;
}

button:hover {
    background:#3730a3;
    transform:translateY(-2px);
}

.view-btn {
    display:inline-block;
    text-decoration:none;
    padding:9px 12px;
    border-radius:9px;
    background:#06b6d4;
    color:white;
    font-weight:bold;
}

.empty {
    text-align:center;
    padding:60px;
    color:#718096;
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
    <a href="prescriptions.php" class="active">📋 Prescriptions</a>
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

            <h1>Prescription Management 📋</h1>

            <p style="margin:6px 0;color:#718096;">
                Review and process customer prescriptions.
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

    <div class="card">

        <h2 style="margin-top:0;">
            Customer Prescriptions
        </h2>

        <div class="table-wrapper">

        <?php if (count($prescriptions) > 0): ?>

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Prescription</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

            <?php foreach ($prescriptions as $p): ?>

                <?php
                    $status = $p['status'] ?? 'Pending';

                    $file =
                        $p['file_path']
                        ?? $p['image_url']
                        ?? $p['prescription_file']
                        ?? $p['file']
                        ?? '';
                ?>

                <tr>

                    <td>
                        <strong>
                            #<?= (int)$p['prescription_id'] ?>
                        </strong>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''))
                        ) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($p['email'] ?? '') ?>
                    </td>

                    <td>

                        <?php if ($file): ?>

                            <a
                                class="view-btn"
                                href="../<?= htmlspecialchars($file) ?>"
                                target="_blank"
                            >
                                👁 View
                            </a>

                        <?php else: ?>

                            <span style="color:#718096;">
                                File unavailable
                            </span>

                        <?php endif; ?>

                    </td>

                    <td>
                        <?= htmlspecialchars(
                            $p['created_at'] ?? $p['uploaded_at'] ?? ''
                        ) ?>
                    </td>

                    <td>

                        <span class="status <?= htmlspecialchars($status) ?>">
                            <?= htmlspecialchars($status) ?>
                        </span>

                    </td>

                    <td>

                        <form method="post">

                            <input
                                type="hidden"
                                name="prescription_id"
                                value="<?= (int)$p['prescription_id'] ?>"
                            >

                            <select name="status">

                                <?php
                                foreach (
                                    ["Pending","Approved","Rejected","Processing","Completed"]
                                    as $s
                                ):
                                ?>

                                    <option
                                        value="<?= $s ?>"
                                        <?= $status === $s ? 'selected' : '' ?>
                                    >
                                        <?= $s ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <button
                                type="submit"
                                name="update_prescription"
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

                <div style="font-size:55px;">📋</div>

                <h3>No Prescriptions</h3>

                <p>
                    Uploaded prescriptions will appear here.
                </p>

            </div>

        <?php endif; ?>

        </div>

    </div>

</main>

</div>

</body>

</html>