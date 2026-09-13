<?php

session_start();

require_once "config/db.php";

/*
--------------------------------------------------------------------------
                     Create cart if it doesn't exist
--------------------------------------------------------------------------
*/

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/*
--------------------------------------------------------------------------
                                  Remove item
--------------------------------------------------------------------------
*/

if (isset($_GET['remove'])) {

    $remove_id = (int)$_GET['remove'];

    unset($_SESSION['cart'][$remove_id]);

    header("Location: cart.php");
    exit;
}

/*
--------------------------------------------------------------------------
                               Update quantities
--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {

    $quantities = $_POST['quantity'] ?? [];

    foreach ($quantities as $product_id => $quantity) {

        $product_id = (int)$product_id;
        $quantity = (int)$quantity;

        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
            continue;
        }

        /*
        | Check stock
        */

        $stmt = $conn->prepare("
            SELECT stock_qty
            FROM products
            WHERE product_id = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) {
            unset($_SESSION['cart'][$product_id]);
            continue;
        }

        $stock = (int)$product['stock_qty'];

        if ($quantity > $stock) {
            $quantity = $stock;
        }

        $_SESSION['cart'][$product_id] = $quantity;
    }

    header("Location: cart.php");
    exit;
}

/*
--------------------------------------------------------------------------
                          Get cart products
--------------------------------------------------------------------------
*/

$cart_products = [];
$grand_total = 0;

if (!empty($_SESSION['cart'])) {

    foreach ($_SESSION['cart'] as $product_id => $quantity) {

        $product_id = (int)$product_id;

        $stmt = $conn->prepare("
            SELECT
                product_id,
                name,
                price,
                image_url,
                stock_qty,
                category
            FROM products
            WHERE product_id = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) {
            unset($_SESSION['cart'][$product_id]);
            continue;
        }

        $quantity = (int)$quantity;

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
}

?>

<?php include "includes/header.php"; ?>

<style>

.cart-page {
    padding: 50px 0 80px;
}

.cart-title {
    text-align: center;
    margin-bottom: 35px;
}

.cart-title h1 {
    margin-bottom: 8px;
}

.cart-title p {
    color: #6b7280;
}

.cart-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
    align-items: start;
}

.cart-items {
    background: white;
    border-radius: 18px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.07);
    overflow: hidden;
}

.cart-item {
    display: grid;
    grid-template-columns: 110px 1fr 130px 150px 50px;
    gap: 20px;
    align-items: center;
    padding: 22px;
    border-bottom: 1px solid #edf0f2;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 100px;
    height: 100px;
    background: #f4faf9;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 10px;
}

.cart-item-name {
    font-weight: 800;
    font-size: 17px;
    margin-bottom: 5px;
}

.cart-item-category {
    color: #6b7280;
    font-size: 13px;
}

.cart-item-price {
    font-weight: 700;
}

.quantity-input {
    width: 80px;
    padding: 10px;
    text-align: center;
    border: 1px solid #d8dee3;
    border-radius: 8px;
}

.remove-btn {
    color: #dc2626;
    text-decoration: none;
    font-size: 22px;
}

.cart-summary {
    background: white;
    padding: 28px;
    border-radius: 18px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.07);
    position: sticky;
    top: 20px;
}

.cart-summary h2 {
    margin-bottom: 25px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
}

.summary-total {
    border-top: 1px solid #e5e7eb;
    margin-top: 10px;
    padding-top: 18px;
    font-size: 21px;
    font-weight: 800;
}

.checkout-btn {
    width: 100%;
    margin-top: 20px;
    padding: 15px;
    border: none;
    cursor: pointer;
    font-size: 16px;
}

.update-btn {
    margin: 20px;
}

.empty-cart {
    text-align: center;
    padding: 70px 20px;
}

.empty-cart-icon {
    font-size: 55px;
    margin-bottom: 20px;
}

.empty-cart h2 {
    margin-bottom: 10px;
}

.empty-cart p {
    color: #6b7280;
    margin-bottom: 25px;
}

@media(max-width: 900px) {

    .cart-layout {
        grid-template-columns: 1fr;
    }

    .cart-item {
        grid-template-columns: 90px 1fr;
    }

    .cart-item-price,
    .cart-item-quantity,
    .cart-item-subtotal,
    .cart-item-remove {
        grid-column: 2;
    }

    .cart-summary {
        position: static;
    }
}

</style>

<main class="cart-page">

    <div class="container">

        <div class="cart-title">
            <h1>🛒 Your Shopping Cart</h1>
            <p>Review your medicines before placing your order.</p>
        </div>

        <?php if (empty($cart_products)): ?>

            <div class="cart-items empty-cart">

                <div class="empty-cart-icon">
                    🛒
                </div>

                <h2>Your cart is empty</h2>

                <p>
                    You haven't added any products yet.
                </p>

                <a href="products.php" class="btn btn-primary">
                    Browse Medicines
                </a>

            </div>

        <?php else: ?>

            <form method="post">

                <div class="cart-layout">

                    <div class="cart-items">

                        <?php foreach ($cart_products as $item): ?>

                            <div class="cart-item">

                                <div class="cart-item-image">

                                    <img
                                        src="<?= htmlspecialchars($item['image_url']) ?>"
                                        alt="<?= htmlspecialchars($item['name']) ?>"
                                    >

                                </div>

                                <div>

                                    <div class="cart-item-name">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </div>

                                    <div class="cart-item-category">
                                        <?= htmlspecialchars($item['category']) ?>
                                    </div>

                                </div>

                                <div class="cart-item-price">

                                    LKR <?= number_format($item['price'], 2) ?>

                                </div>

                                <div class="cart-item-quantity">

                                    <label>
                                        Quantity
                                    </label>

                                    <br>

                                    <input
                                        class="quantity-input"
                                        type="number"
                                        name="quantity[<?= (int)$item['product_id'] ?>]"
                                        value="<?= (int)$item['quantity'] ?>"
                                        min="1"
                                        max="<?= (int)$item['stock_qty'] ?>"
                                    >

                                </div>

                                <div class="cart-item-subtotal">

                                    <strong>
                                        LKR <?= number_format($item['subtotal'], 2) ?>
                                    </strong>

                                    <br>

                                    <a
                                        class="remove-btn"
                                        href="cart.php?remove=<?= (int)$item['product_id'] ?>"
                                        title="Remove"
                                    >
                                        🗑️
                                    </a>

                                </div>

                            </div>

                        <?php endforeach; ?>

                        <button
                            type="submit"
                            name="update_cart"
                            class="btn btn-outline update-btn"
                        >
                            🔄 Update Cart
                        </button>

                    </div>

                    <div class="cart-summary">

                        <h2>Order Summary</h2>

                        <div class="summary-row">
                            <span>Items</span>
                            <strong><?= count($cart_products) ?></strong>
                        </div>

                        <div class="summary-row">
                            <span>Subtotal</span>
                            <strong>
                                LKR <?= number_format($grand_total, 2) ?>
                            </strong>
                        </div>

                        <div class="summary-row">
                            <span>Delivery</span>
                            <strong>Free</strong>
                        </div>

                        <div class="summary-row summary-total">

                            <span>Total</span>

                            <span>
                                LKR <?= number_format($grand_total, 2) ?>
                            </span>

                        </div>

                        <?php if (isset($_SESSION['user_id'])): ?>

                            <a
                                href="checkout.php"
                                class="btn btn-primary checkout-btn"
                            >
                                Proceed to Checkout →
                            </a>

                        <?php else: ?>

                            <a
                                href="login.php"
                                class="btn btn-primary checkout-btn"
                            >
                                Login to Checkout →
                            </a>

                            <p style="text-align:center;margin-top:12px;color:#6b7280;font-size:13px;">
                                Please login before placing your order.
                            </p>

                        <?php endif; ?>

                    </div>

                </div>

            </form>

        <?php endif; ?>

    </div>

</main>

<?php include "includes/footer.php"; ?>