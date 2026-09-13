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
 Cart must contain products
--------------------------------------------------------------------------
*/

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$payment_method = "Cash on Delivery";

/*
--------------------------------------------------------------------------
 Start transaction
--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {

    /*
    --------------------------------------------------------------------------
     Calculate total and verify stock
    --------------------------------------------------------------------------
    */

    $cart_items = [];
    $total_amount = 0;

    foreach ($_SESSION['cart'] as $product_id => $quantity) {

        $product_id = (int)$product_id;
        $quantity = (int)$quantity;

        if ($quantity <= 0) {
            continue;
        }

        $stmt = $conn->prepare("
            SELECT
                product_id,
                name,
                price,
                stock_qty
            FROM products
            WHERE product_id = ?
            FOR UPDATE
        ");

        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) {
            throw new Exception("One of the products is no longer available.");
        }

        if ((int)$product['stock_qty'] < $quantity) {
            throw new Exception(
                $product['name'] . " does not have enough stock."
            );
        }

        $subtotal = $product['price'] * $quantity;

        $total_amount += $subtotal;

        $cart_items[] = [
            'product_id' => $product_id,
            'name' => $product['name'],
            'quantity' => $quantity,
            'price' => $product['price']
        ];
    }

    if (empty($cart_items)) {
        throw new Exception("Your cart is empty.");
    }

    /*
    -------------------------------------------------------------------------
     Create order
    --------------------------------------------------------------------------
    */

    $status = "Pending";

    $stmt = $conn->prepare("
        INSERT INTO orders
        (
            user_id,
            total_amount,
            payment_method,
            status
        )
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "idss",
        $user_id,
        $total_amount,
        $payment_method,
        $status
    );

    if (!$stmt->execute()) {
        throw new Exception("Unable to create order.");
    }

    $order_id = $conn->insert_id;

    /*
    --------------------------------------------------------------------------
     Insert order items + reduce stock
    --------------------------------------------------------------------------
    */

    foreach ($cart_items as $item) {

        $stmt = $conn->prepare("
            INSERT INTO order_items
            (
                order_id,
                product_id,
                quantity,
                unit_price
            )
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiid",
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        );

        if (!$stmt->execute()) {
            throw new Exception("Unable to save order items.");
        }

        /*
        | Reduce product stock
        */

        $stmt = $conn->prepare("
            UPDATE products
            SET stock_qty = stock_qty - ?
            WHERE product_id = ?
        ");

        $stmt->bind_param(
            "ii",
            $item['quantity'],
            $item['product_id']
        );

        if (!$stmt->execute()) {
            throw new Exception("Unable to update stock.");
        }
    }

    /*
    --------------------------------------------------------------------------
     Everything successful
    --------------------------------------------------------------------------
    */

    $conn->commit();

    /*
    --------------------------------------------------------------------------
     Empty shopping cart
    --------------------------------------------------------------------------
    */

    $_SESSION['cart'] = [];

    /*
    --------------------------------------------------------------------------
     Store latest order ID
    --------------------------------------------------------------------------
    */

    $_SESSION['last_order_id'] = $order_id;

    /*
    --------------------------------------------------------------------------
     Redirect
    --------------------------------------------------------------------------
    */

    header("Location: order_success.php?order_id=" . $order_id);
    exit;

} catch (Exception $e) {

    /*
    --------------------------------------------------------------------------
     Rollback if anything fails
    --------------------------------------------------------------------------
    */

    $conn->rollback();

    $_SESSION['order_error'] = $e->getMessage();

    header("Location: checkout.php?error=1");
    exit;
}

?>