<?php
session_start();

require_once "config/db.php";

/* Get product ID */
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

/* If no product ID, go back */
if ($product_id <= 0) {
    header("Location: products.php");
    exit;
}

/* Check product */
$sql = "SELECT product_id, name, price, stock_qty, image_url 
        FROM products 
        WHERE product_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

/* Check stock */
if ((int)$product['stock_qty'] <= 0) {
    header("Location: products.php?message=out_of_stock");
    exit;
}

/* Create cart */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* Add product */
if (isset($_SESSION['cart'][$product_id])) {

    $_SESSION['cart'][$product_id]++;

    /* Do not exceed available stock */
    if ($_SESSION['cart'][$product_id] > (int)$product['stock_qty']) {
        $_SESSION['cart'][$product_id] = (int)$product['stock_qty'];
    }

} else {

    $_SESSION['cart'][$product_id] = 1;
}

/* Go to cart */
header("Location: cart.php");
exit;
?>