<?php
session_start();
require_once "config/db.php";

/* Get product ID */
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die("Invalid product ID.");
}

/* Get product */
$st = $conn->prepare("
    SELECT *
    FROM products
    WHERE product_id = ?
");

if (!$st) {
    die("Database error: " . $conn->error);
}

$st->bind_param("i", $id);
$st->execute();

$result = $st->get_result();
$p = $result->fetch_assoc();

if (!$p) {
    die("Product not found.");
}

/* Add to cart */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $qty = (int)($_POST['quantity'] ?? 1);

    if ($qty < 1) {
        $qty = 1;
    }

    $stock = (int)$p['stock_qty'];

    /* Check stock */
    if ($stock <= 0) {
        header("Location: products.php?id=" . $id . "&error=out_of_stock");
        exit;
    }

    /* Create cart session */
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    /* Existing quantity */
    $current_qty = isset($_SESSION['cart'][$id])
        ? (int)$_SESSION['cart'][$id]
        : 0;

    $new_qty = $current_qty + $qty;

    /* Do not allow quantity above stock */
    if ($new_qty > $stock) {
        $new_qty = $stock;
    }

    $_SESSION['cart'][$id] = $new_qty;

    /* Go to cart */
    header("Location: cart.php");
    exit;
}
?>

<?php include "includes/header.php"; ?>

<main class="section">

    <div class="container">

        <?php if (isset($_GET['error']) && $_GET['error'] === 'out_of_stock'): ?>

            <div class="alert error">
                Sorry, this product is currently out of stock.
            </div>

        <?php endif; ?>


        <div class="card product-detail-card">

            <!-- PRODUCT IMAGE -->
            <div class="product-detail-image">

                <img
                    src="<?= htmlspecialchars($p['image_url']) ?>"
                    alt="<?= htmlspecialchars($p['name']) ?>"
                >

            </div>


            <!-- PRODUCT INFORMATION -->
            <div class="product-detail-info">

                <span class="badge">
                    <?= htmlspecialchars($p['category']) ?>
                </span>

                <h1>
                    <?= htmlspecialchars($p['name']) ?>
                </h1>

                <p class="product-description">
                    <?= nl2br(htmlspecialchars($p['description'])) ?>
                </p>

                <h2 class="price">
                    LKR <?= number_format($p['price'], 2) ?>
                </h2>


                <?php if (!empty($p['dosage'])): ?>

                    <p>
                        <strong>Dosage:</strong>
                        <?= htmlspecialchars($p['dosage']) ?>
                    </p>

                <?php endif; ?>


                <?php if (!empty($p['safety_info'])): ?>

                    <p>
                        <strong>Safety Information:</strong>
                        <?= htmlspecialchars($p['safety_info']) ?>
                    </p>

                <?php endif; ?>


                <!-- STOCK -->
                <?php if ((int)$p['stock_qty'] > 0): ?>

                    <p class="stock-available">
                        ✓ In Stock
                        (<?= (int)$p['stock_qty'] ?> available)
                    </p>

                <?php else: ?>

                    <p class="stock-out">
                        ✕ Out of Stock
                    </p>

                <?php endif; ?>


                <!-- PRESCRIPTION PRODUCT -->
                <?php if ((int)$p['requires_prescription'] === 1): ?>

                    <div class="notice">
                        <strong>Prescription Required</strong>
                        <br>
                        A valid prescription is required before ordering this medicine.
                    </div>

                    <br>

                    <a
                        class="btn btn-dark"
                        href="prescription.php"
                    >
                        📄 Upload Prescription
                    </a>


                <!-- NORMAL PRODUCT -->
                <?php elseif ((int)$p['stock_qty'] > 0): ?>

                    <form method="post" class="add-cart-form">

                        <div class="field">

                            <label for="quantity">
                                Quantity
                            </label>

                            <input
                                type="number"
                                id="quantity"
                                name="quantity"
                                value="1"
                                min="1"
                                max="<?= (int)$p['stock_qty'] ?>"
                                required
                            >

                        </div>

                        <br>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            🛒 Add to Cart
                        </button>

                        <a
                            href="cart.php"
                            class="btn btn-outline"
                            style="margin-left:10px;"
                        >
                            View Cart
                        </a>

                    </form>

                <?php endif; ?>

            </div>

        </div>

    </div>

</main>


<style>

.product-detail-card {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 45px;
    padding: 40px;
    align-items: center;
}

.product-detail-image {
    width: 100%;
    height: 430px;
    background: #f4faf9;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.product-detail-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 30px;
}

.product-detail-info h1 {
    margin: 15px 0;
    font-size: 38px;
}

.product-description {
    line-height: 1.8;
    color: #555;
}

.price {
    color: #087f8c;
    margin: 25px 0;
}

.stock-available {
    color: #16803c;
    font-weight: 700;
}

.stock-out {
    color: #c62828;
    font-weight: 700;
}

.add-cart-form input {
    width: 120px;
}

@media (max-width: 768px) {

    .product-detail-card {
        grid-template-columns: 1fr;
        padding: 20px;
    }

    .product-detail-image {
        height: 300px;
    }

    .product-detail-info h1 {
        font-size: 28px;
    }

}

</style>


<?php include "includes/footer.php"; ?>