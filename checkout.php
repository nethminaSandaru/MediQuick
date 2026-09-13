<?php

session_start();

require_once "config/db.php";

/*
--------------------------------------------------------------------------
                    Customer must login
--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/*
--------------------------------------------------------------------------
                       Cart cannot be empty
--------------------------------------------------------------------------
*/

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

/*
--------------------------------------------------------------------------
                                 Get customer
--------------------------------------------------------------------------
*/

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT first_name, last_name, email, phone, address, city
    FROM users
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

/*
--------------------------------------------------------------------------
                                Get products
--------------------------------------------------------------------------
*/

$cart_products = [];
$grand_total = 0;

foreach ($_SESSION['cart'] as $product_id => $quantity) {

    $product_id = (int)$product_id;
    $quantity = (int)$quantity;

    $stmt = $conn->prepare("
        SELECT
            product_id,
            name,
            price,
            image_url,
            stock_qty
        FROM products
        WHERE product_id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    $product = $stmt->get_result()->fetch_assoc();

    if (!$product || (int)$product['stock_qty'] <= 0) {
        unset($_SESSION['cart'][$product_id]);
        continue;
    }

    if ($quantity > (int)$product['stock_qty']) {
        $quantity = (int)$product['stock_qty'];
        $_SESSION['cart'][$product_id] = $quantity;
    }

    $subtotal = $product['price'] * $quantity;

    $product['quantity'] = $quantity;
    $product['subtotal'] = $subtotal;

    $cart_products[] = $product;

    $grand_total += $subtotal;
}

if (empty($cart_products)) {
    header("Location: cart.php");
    exit;
}

?>

<?php include "includes/header.php"; ?>

<style>

.checkout-page {
    padding: 50px 0 80px;
}

.checkout-title {
    text-align: center;
    margin-bottom: 35px;
}

.checkout-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 30px;
    align-items: start;
}

.checkout-card {
    background: white;
    border-radius: 18px;
    padding: 30px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.07);
}

.checkout-card h2 {
    margin-bottom: 20px;
}

.customer-info {
    display: grid;
    gap: 14px;
}

.info-row {
    padding: 14px;
    background: #f7faf9;
    border-radius: 10px;
}

.info-row strong {
    display: block;
    margin-bottom: 4px;
}

.checkout-product {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #edf0f2;
}

.checkout-product:last-child {
    border-bottom: none;
}

.checkout-product img {
    width: 70px;
    height: 70px;
    object-fit: contain;
    background: #f4faf9;
    border-radius: 10px;
    padding: 7px;
}

.checkout-product-info {
    flex: 1;
}

.checkout-product-name {
    font-weight: 700;
}

.checkout-product-price {
    color: #087f8c;
    font-weight: 700;
}

.total-box {
    border-top: 1px solid #ddd;
    margin-top: 20px;
    padding-top: 20px;
}

.total-line {
    display: flex;
    justify-content: space-between;
    font-size: 22px;
    font-weight: 800;
}

.cod-box {
    margin-top: 25px;
    padding: 18px;
    border-radius: 12px;
    background: #f0faf8;
    border: 1px solid #cceee8;
}

.cod-box strong {
    display: block;
    margin-bottom: 5px;
}

.place-order-btn {
    width: 100%;
    border: none;
    cursor: pointer;
    margin-top: 22px;
    padding: 16px;
    font-size: 17px;
}

@media(max-width: 850px) {

    .checkout-layout {
        grid-template-columns: 1fr;
    }

}

</style>

<main class="checkout-page">

    <div class="container">

        <div class="checkout-title">

            <h1>Checkout</h1>

            <p>
                Confirm your details and place your order.
            </p>

        </div>

        <div class="checkout-layout">

            <!-- CUSTOMER INFORMATION -->

            <div class="checkout-card">

                <h2>📍 Delivery Information</h2>

                <div class="customer-info">

                    <div class="info-row">

                        <strong>Customer</strong>

                        <?= htmlspecialchars(
                            $user['first_name'] . ' ' . $user['last_name']
                        ) ?>

                    </div>

                    <div class="info-row">

                        <strong>Email</strong>

                        <?= htmlspecialchars($user['email']) ?>

                    </div>

                    <div class="info-row">

                        <strong>Phone</strong>

                        <?= htmlspecialchars($user['phone']) ?>

                    </div>

                    <div class="info-row">

                        <strong>Delivery Address</strong>

                        <?= htmlspecialchars($user['address']) ?><br>

                        <?= htmlspecialchars($user['city']) ?>

                    </div>

                </div>

                <h2 style="margin-top:35px;">
                    🛍️ Your Products
                </h2>

                <?php foreach ($cart_products as $item): ?>

                    <div class="checkout-product">

                        <img
                            src="<?= htmlspecialchars($item['image_url']) ?>"
                            alt="<?= htmlspecialchars($item['name']) ?>"
                        >

                        <div class="checkout-product-info">

                            <div class="checkout-product-name">
                                <?= htmlspecialchars($item['name']) ?>
                            </div>

                            <div>
                                Quantity:
                                <?= (int)$item['quantity'] ?>
                            </div>

                            <div class="checkout-product-price">

                                LKR <?= number_format($item['subtotal'], 2) ?>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>


            <!-- ORDER SUMMARY -->

            <div class="checkout-card">

                <h2>Order Summary</h2>

                <?php foreach ($cart_products as $item): ?>

                    <div class="checkout-product">

                        <div class="checkout-product-info">

                            <div class="checkout-product-name">
                                <?= htmlspecialchars($item['name']) ?>
                            </div>

                            <div>
                                <?= (int)$item['quantity'] ?> ×
                                LKR <?= number_format($item['price'], 2) ?>
                            </div>

                        </div>

                        <strong>
                            LKR <?= number_format($item['subtotal'], 2) ?>
                        </strong>

                    </div>

                <?php endforeach; ?>

                <div class="total-box">

                    <div class="total-line">

                        <span>Total</span>

                        <span>
                            LKR <?= number_format($grand_total, 2) ?>
                        </span>

                    </div>

                </div>

                <div class="cod-box">

                    <strong>💵 Cash on Delivery</strong>

                    <span>
                        Pay safely in cash when your order is delivered to you.
                    </span>

                </div>

                <button
                    type="button"
                    class="btn btn-primary place-order-btn"
                    onclick="openCODPopup()"
                >
                    Place Order – Cash on Delivery
                </button>

                <a
                    href="cart.php"
                    style="display:block;text-align:center;margin-top:15px;"
                >
                    ← Back to Cart
                </a>

            </div>

        </div>

    </div>

</main>


<!-- COD POPUP -->

<div
    id="codPopup"
    style="
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,0.55);
        z-index:9999;
        align-items:center;
        justify-content:center;
        padding:20px;
    "
>

    <div
        style="
            background:white;
            max-width:500px;
            width:100%;
            border-radius:20px;
            padding:35px;
            text-align:center;
            box-shadow:0 20px 60px rgba(0,0,0,0.25);
        "
    >

        <div style="font-size:50px;">
            💵
        </div>

        <h2 style="margin:15px 0;">
            Cash on Delivery
        </h2>

        <p style="color:#666;line-height:1.6;">

            Your order will be delivered to your address.
            Please keep

            <strong>
                LKR <?= number_format($grand_total, 2) ?>
            </strong>

            ready and pay the delivery person when your order arrives.

        </p>

        <div
            style="
                display:flex;
                gap:12px;
                margin-top:25px;
            "
        >

            <button
                type="button"
                onclick="closeCODPopup()"
                class="btn btn-outline"
                style="flex:1;"
            >
                Cancel
            </button>

            <form
                action="place_order.php"
                method="post"
                style="flex:1;"
            >

                <input
                    type="hidden"
                    name="payment_method"
                    value="Cash on Delivery"
                >

                <button
                    type="submit"
                    class="btn btn-primary"
                    style="
                        width:100%;
                        border:none;
                        cursor:pointer;
                    "
                >
                    Confirm Order
                </button>

            </form>

        </div>

    </div>

</div>


<script>

function openCODPopup() {

    document.getElementById("codPopup").style.display = "flex";

}

function closeCODPopup() {

    document.getElementById("codPopup").style.display = "none";

}

document.getElementById("codPopup").addEventListener("click", function(event) {

    if (event.target === this) {
        closeCODPopup();
    }

});

</script>

<?php include "includes/footer.php"; ?>