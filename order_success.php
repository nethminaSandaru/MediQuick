<?php

session_start();

require_once "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = isset($_GET['order_id'])
    ? (int)$_GET['order_id']
    : (int)($_SESSION['last_order_id'] ?? 0);

if ($order_id <= 0) {
    header("Location: index.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT
        order_id,
        total_amount,
        payment_method,
        status,
        created_at
    FROM orders
    WHERE order_id = ?
    AND user_id = ?
    LIMIT 1
");

$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();

$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: index.php");
    exit;
}

?>

<?php include "includes/header.php"; ?>

<style>

.success-page {
    padding: 80px 20px;
}

.success-card {
    max-width: 650px;
    margin: auto;
    background: white;
    padding: 50px 35px;
    border-radius: 22px;
    text-align: center;
    box-shadow: 0 15px 50px rgba(0,0,0,0.08);
}

.success-icon {
    width: 85px;
    height: 85px;
    border-radius: 50%;
    margin: 0 auto 25px;
    background: #e6f8f3;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 45px;
}

.success-card h1 {
    margin-bottom: 12px;
}

.success-card p {
    color: #6b7280;
    line-height: 1.7;
}

.order-number {
    background: #f4faf9;
    border-radius: 12px;
    padding: 18px;
    margin: 25px 0;
    font-size: 20px;
    font-weight: 800;
}

.success-details {
    text-align: left;
    margin-top: 25px;
}

.success-detail {
    display: flex;
    justify-content: space-between;
    padding: 13px 0;
    border-bottom: 1px solid #edf0f2;
}

.success-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 30px;
}

@media(max-width:600px) {

    .success-actions {
        flex-direction: column;
    }

}

</style>

<main class="success-page">

    <div class="success-card">

        <div class="success-icon">
            ✓
        </div>

        <h1>Order Placed Successfully!</h1>

        <p>
            Thank you for shopping with MediQuick Pharmacy.
            Your order has been received and will be processed shortly.
        </p>

        <div class="order-number">

            Order #<?= (int)$order['order_id'] ?>

        </div>

        <div class="success-details">

            <div class="success-detail">

                <span>Total</span>

                <strong>
                    LKR <?= number_format($order['total_amount'], 2) ?>
                </strong>

            </div>

            <div class="success-detail">

                <span>Payment</span>

                <strong>
                    <?= htmlspecialchars($order['payment_method']) ?>
                </strong>

            </div>

            <div class="success-detail">

                <span>Status</span>

                <strong>
                    <?= htmlspecialchars($order['status']) ?>
                </strong>

            </div>

            <div class="success-detail">

                <span>Delivery</span>

                <strong>
                    Kurunegala / Your registered address
                </strong>

            </div>

        </div>

        <p style="margin-top:25px;">

            💵 Please pay the order amount in cash when your
            order is delivered.

        </p>

        <div class="success-actions">

            <a
                href="track_order.php"
                class="btn btn-primary"
            >
                📦 Track My Order
            </a>

            <a
                href="products.php"
                class="btn btn-outline"
            >
                Continue Shopping
            </a>

        </div>

    </div>

</main>

<?php include "includes/footer.php"; ?>