<?php

session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'staff') {
    header("Location: ../login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$message = "";
$error = "";

/*
|--------------------------------------------------------------------------
| UPDATE PROFILE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {

    $first_name = trim($_POST['first_name'] ?? "");
    $last_name  = trim($_POST['last_name'] ?? "");
    $phone      = trim($_POST['phone'] ?? "");
    $address    = trim($_POST['address'] ?? "");
    $city       = trim($_POST['city'] ?? "");

    if (!$first_name || !$last_name || !$phone) {

        $error = "Please complete the required fields.";

    } else {

        $stmt = $conn->prepare("
            UPDATE users
            SET first_name=?, last_name=?, phone=?, address=?, city=?
            WHERE user_id=?
        ");

        $stmt->bind_param(
            "sssssi",
            $first_name,
            $last_name,
            $phone,
            $address,
            $city,
            $user_id
        );

        if ($stmt->execute()) {

            $_SESSION['user_name'] =
                $first_name . " " . $last_name;

            $message = "Profile updated successfully.";

        } else {

            $error = "Unable to update profile.";
        }
    }
}

/*
|--------------------------------------------------------------------------
| CHANGE PASSWORD
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['change_password'])) {

    $current = $_POST['current_password'] ?? "";
    $new     = $_POST['new_password'] ?? "";
    $confirm = $_POST['confirm_password'] ?? "";

    $stmt = $conn->prepare(
        "SELECT password FROM users WHERE user_id=?"
    );

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !password_verify($current, $user['password'])) {

        $error = "Current password is incorrect.";

    } elseif (strlen($new) < 8) {

        $error = "New password must contain at least 8 characters.";

    } elseif ($new !== $confirm) {

        $error = "New passwords do not match.";

    } else {

        $hash = password_hash($new, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "UPDATE users SET password=? WHERE user_id=?"
        );

        $stmt->bind_param(
            "si",
            $hash,
            $user_id
        );

        if ($stmt->execute()) {

            $message = "Password changed successfully.";

        } else {

            $error = "Could not change password.";
        }
    }
}

/*
|--------------------------------------------------------------------------
| GET USER
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT * FROM users WHERE user_id=?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("Staff account not found.");
}

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>My Profile | MediQuick Staff</title>

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
    background:linear-gradient(180deg,#4c1d95,#7c3aed,#a855f7);
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
    margin:auto;
    background:white;
    color:#7c3aed;
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
    background:#f5f3ff;
    color:#6d28d9;
    padding:10px 16px;
    border-radius:30px;
    font-weight:bold;
}

.alert {
    padding:15px;
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

.profile-grid {
    display:grid;
    grid-template-columns:320px 1fr;
    gap:25px;
}

.profile-card,
.form-card {
    background:white;
    border-radius:20px;
    padding:28px;
    box-shadow:0 10px 35px rgba(0,0,0,.06);
}

.profile-card {
    text-align:center;
}

.avatar {
    width:100px;
    height:100px;
    margin:0 auto 20px;
    border-radius:30px;
    background:linear-gradient(135deg,#8b5cf6,#6d28d9);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:40px;
    font-weight:bold;
    box-shadow:0 15px 35px rgba(109,40,217,.25);
}

.role-badge {
    display:inline-block;
    padding:8px 15px;
    border-radius:30px;
    background:#ede9fe;
    color:#6d28d9;
    font-weight:bold;
}

.form-grid {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}

.field {
    margin-bottom:16px;
}

.field.full {
    grid-column:1/-1;
}

label {
    display:block;
    margin-bottom:7px;
    font-weight:bold;
}

input {
    width:100%;
    padding:13px 14px;
    border:1px solid #dbe3e7;
    border-radius:10px;
    outline:none;
}

input:focus {
    border-color:#7c3aed;
    box-shadow:0 0 0 4px rgba(124,58,237,.10);
}

.save-btn {
    border:0;
    background:linear-gradient(135deg,#7c3aed,#a855f7);
    color:white;
    padding:13px 22px;
    border-radius:10px;
    font-weight:bold;
    cursor:pointer;
    transition:.3s;
}

.save-btn:hover {
    transform:translateY(-3px);
    box-shadow:0 12px 25px rgba(124,58,237,.25);
}

.info-box {
    margin-top:25px;
    background:#faf5ff;
    border-radius:15px;
    padding:18px;
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

    .profile-grid {
        grid-template-columns:1fr;
    }

    .form-grid {
        grid-template-columns:1fr;
    }

    .field.full {
        grid-column:auto;
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
    <a href="inquiries.php">💬 Inquiries</a>
    <a href="profile.php" class="active">👤 My Profile</a>

    <a href="../index.php">🌐 View Website</a>

    <a href="../logout.php" class="logout">🚪 Logout</a>

</aside>

<main class="main-content">

    <div class="topbar">

        <div>

            <h1>My Staff Profile 👤</h1>

            <p style="margin:6px 0;color:#718096;">
                Manage your MediQuick staff account.
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

    <div class="profile-grid">

        <div class="profile-card">

            <div class="avatar">

                <?= htmlspecialchars(
                    strtoupper(
                        substr($user['first_name'] ?? 'S', 0, 1)
                    )
                ) ?>

            </div>

            <h2>
                <?= htmlspecialchars(
                    ($user['first_name'] ?? '') .
                    ' ' .
                    ($user['last_name'] ?? '')
                ) ?>
            </h2>

            <p style="color:#718096;">
                <?= htmlspecialchars($user['email'] ?? '') ?>
            </p>

            <span class="role-badge">
                👨‍💼 Pharmacy Staff
            </span>

            <div class="info-box">

                <strong>MediQuick Pharmacy</strong>

                <p style="color:#718096;line-height:1.6;">
                    Staff members help manage daily pharmacy
                    operations, orders, prescriptions,
                    inventory and customer inquiries.
                </p>

            </div>

        </div>

        <div>

            <div class="form-card">

                <h2>Personal Information</h2>

                <p style="color:#718096;">
                    Update your staff account details.
                </p>

                <form method="post">

                    <div class="form-grid">

                        <div class="field">

                            <label>First Name</label>

                            <input
                                name="first_name"
                                value="<?= htmlspecialchars($user['first_name'] ?? '') ?>"
                                required
                            >

                        </div>

                        <div class="field">

                            <label>Last Name</label>

                            <input
                                name="last_name"
                                value="<?= htmlspecialchars($user['last_name'] ?? '') ?>"
                                required
                            >

                        </div>

                        <div class="field">

                            <label>Email</label>

                            <input
                                value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                disabled
                            >

                        </div>

                        <div class="field">

                            <label>Phone</label>

                            <input
                                name="phone"
                                value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                required
                            >

                        </div>

                        <div class="field full">

                            <label>Address</label>

                            <input
                                name="address"
                                value="<?= htmlspecialchars($user['address'] ?? '') ?>"
                            >

                        </div>

                        <div class="field">

                            <label>City</label>

                            <input
                                name="city"
                                value="<?= htmlspecialchars($user['city'] ?? '') ?>"
                            >

                        </div>

                    </div>

                    <button
                        type="submit"
                        name="update_profile"
                        class="save-btn"
                    >
                        💾 Save Profile
                    </button>

                </form>

            </div>

            <br>

            <div class="form-card">

                <h2>Change Password 🔐</h2>

                <p style="color:#718096;">
                    Keep your staff account secure.
                </p>

                <form method="post">

                    <div class="field">

                        <label>Current Password</label>

                        <input
                            type="password"
                            name="current_password"
                            required
                        >

                    </div>

                    <div class="form-grid">

                        <div class="field">

                            <label>New Password</label>

                            <input
                                type="password"
                                name="new_password"
                                minlength="8"
                                required
                            >

                        </div>

                        <div class="field">

                            <label>Confirm Password</label>

                            <input
                                type="password"
                                name="confirm_password"
                                minlength="8"
                                required
                            >

                        </div>

                    </div>

                    <button
                        type="submit"
                        name="change_password"
                        class="save-btn"
                    >
                        🔐 Change Password
                    </button>

                </form>

            </div>

        </div>

    </div>

</main>

</div>

</body>

</html>