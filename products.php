<?php
session_start();

require_once "config/db.php";

/* =========================================================
   CART INITIALIZATION
   ========================================================= */
if (!isset($_SESSION["cart"]) || !is_array($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

/* =========================================================
   HELPER FUNCTIONS
   ========================================================= */
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function getProductImageUrl($image_url)
{
    $image_url = trim((string)$image_url);

    if ($image_url === "") {
        return "data:image/svg+xml;charset=UTF-8," . rawurlencode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="500" viewBox="0 0 600 500">
                <rect width="600" height="500" fill="#f1f5f9"/>
                <text x="300" y="250" text-anchor="middle"
                      font-family="Arial" font-size="26" fill="#94a3b8">
                    No Image
                </text>
            </svg>'
        );
    }

    if (filter_var($image_url, FILTER_VALIDATE_URL)) {
        return $image_url;
    }

    if (strpos($image_url, "data:image/") === 0) {
        return $image_url;
    }

    return ltrim($image_url, "/\\");
}

/* =========================================================
                            ADD TO CART
   ========================================================= */
$cart_message = "";
$cart_message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_to_cart"])) {

    $product_id = isset($_POST["product_id"])
        ? (int)$_POST["product_id"]
        : 0;

    if ($product_id > 0) {

        $stmt = $conn->prepare("
            SELECT
                product_id,
                name,
                stock_qty,
                requires_prescription
            FROM products
            WHERE product_id = ?
            LIMIT 1
        ");

        if ($stmt) {

            $stmt->bind_param("i", $product_id);
            $stmt->execute();

            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            $stmt->close();

            if ($product) {

                /* -----------------------------------------
                   PRESCRIPTION CHECK
                   ----------------------------------------- */
                if ((int)$product["requires_prescription"] === 1) {

                    $cart_message =
                        "This product requires a prescription. Please upload your prescription first.";

                    $cart_message_type = "warning";

                } else {

                    $current_qty = isset($_SESSION["cart"][$product_id])
                        ? (int)$_SESSION["cart"][$product_id]
                        : 0;

                    $stock_qty = (int)$product["stock_qty"];

                    if ($stock_qty <= 0) {

                        $cart_message = "This product is currently out of stock.";
                        $cart_message_type = "danger";

                    } elseif ($current_qty >= $stock_qty) {

                        $cart_message =
                            "You have already added the maximum available stock.";

                        $cart_message_type = "danger";

                    } else {

                        /*
                         * Add one quantity.
                         * Existing cart operation is preserved.
                         */
                        $_SESSION["cart"][$product_id] = $current_qty + 1;

                        $cart_message =
                            $product["name"] . " added to your cart.";

                        $cart_message_type = "success";
                    }
                }

            } else {

                $cart_message = "Product not found.";
                $cart_message_type = "danger";
            }

        } else {

            $cart_message = "Unable to process your request.";
            $cart_message_type = "danger";
        }
    }
}

/* =========================================================
                       SEARCH
   ========================================================= */
$search = isset($_GET["search"])
    ? trim($_GET["search"])
    : "";

/* =========================================================
                      CATEGORY FILTER
   ========================================================= */
$category = isset($_GET["category"])
    ? trim($_GET["category"])
    : "";

/* =========================================================
                      PRODUCT DETAIL
   ========================================================= */
$product_detail = null;

if (isset($_GET["id"]) && (int)$_GET["id"] > 0) {

    $detail_id = (int)$_GET["id"];

    $stmt = $conn->prepare("
        SELECT *
        FROM products
        WHERE product_id = ?
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param("i", $detail_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $product_detail = $result->fetch_assoc();

        $stmt->close();
    }
}

/* =========================================================
                      PRODUCTS QUERY
   ========================================================= */
$products = [];

$sql = "
    SELECT
        product_id,
        name,
        category,
        price,
        stock_qty,
        description,
        dosage,
        safety_info,
        requires_prescription,
        image_url
    FROM products
    WHERE 1=1
";

$params = [];
$types = "";

/* SEARCH */
if ($search !== "") {

    $sql .= "
        AND (
            name LIKE ?
            OR description LIKE ?
            OR category LIKE ?
        )
    ";

    $search_param = "%" . $search . "%";

    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;

    $types .= "sss";
}

/* CATEGORY */
if ($category !== "") {

    $sql .= " AND category = ? ";

    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY product_id DESC";

$stmt = $conn->prepare($sql);

if ($stmt) {

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    $stmt->close();
}

/* =========================================================
                          CART COUNT
   ========================================================= */
$cart_count = 0;

foreach ($_SESSION["cart"] as $quantity) {
    $cart_count += (int)$quantity;
}

/* =========================================================
                    CATEGORIES
   ========================================================= */
$categories = [
    "Prescription Medicines",
    "OTC Medicines",
    "Wellness",
    "Personal Care",
    "Medical Devices",
    "First Aid",
    "Baby Care",
    "Cosmatics",
    "Other Pharmacy"
];

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>MediQuick - Products</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        /* =====================================================
                                GLOBAL
           ===================================================== */

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "Poppins", sans-serif;
            background:
                linear-gradient(
                    135deg,
                    #f5fffc 0%,
                    #ffffff 45%,
                    #f0fffb 100%
                );
            color: #17352f;
        }

        a {
            text-decoration: none;
        }

        /* =====================================================
                                NAVBAR
           ===================================================== */

        .main-navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(32, 164, 134, 0.12);
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .navbar-brand {
            font-size: 25px;
            font-weight: 700;
            color: #147d64 !important;
        }

        .navbar-brand i {
            animation: pulseIcon 2s infinite;
        }

        @keyframes pulseIcon {

            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.15);
            }

            100% {
                transform: scale(1);
            }

        }

        .nav-link {
            color: #355b53 !important;
            font-weight: 500;
            margin: 0 5px;
            transition: 0.3s ease;
        }

        .nav-link:hover {
            color: #20a486 !important;
            transform: translateY(-2px);
        }

        .cart-nav {
            position: relative;
            font-size: 20px;
        }

        .cart-badge {
            position: absolute;
            top: -7px;
            right: -12px;
            background: #20a486;
            color: white;
            font-size: 10px;
            min-width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            animation: cartBadgePulse 1.8s infinite;
        }

        @keyframes cartBadgePulse {

            0%, 100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.15);
            }

        }

        /* =====================================================
                                 HERO
           ===================================================== */

        .products-hero {
            padding: 65px 20px 45px;
            text-align: center;
        }

        .hero-title {
            font-size: 44px;
            font-weight: 700;
            color: #147d64;
            animation: textReveal 1s ease forwards;
        }

        .hero-title span {
            display: inline-block;
            animation: floatingText 3s ease-in-out infinite;
        }

        @keyframes textReveal {

            from {
                opacity: 0;
                transform: translateY(25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }

        @keyframes floatingText {

            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-4px);
            }

        }

        .hero-subtitle {
            max-width: 720px;
            margin: 15px auto 0;
            color: #68827b;
            font-size: 16px;
            line-height: 1.8;
            animation: fadeInUp 1.2s ease;
        }

        @keyframes fadeInUp {

            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }

        /* =====================================================
                        SEARCH SECTION
           ===================================================== */

        .search-wrapper {
            max-width: 1000px;
            margin: 0 auto 40px;
            padding: 0 20px;
        }

        .search-box {
            background: white;
            padding: 10px;
            border-radius: 18px;
            box-shadow:
                0 12px 40px rgba(20, 125, 100, 0.08);
            border: 1px solid rgba(32, 164, 134, 0.12);
        }

        .search-input {
            border: none !important;
            box-shadow: none !important;
            height: 52px;
            padding-left: 18px;
        }

        .search-btn {
            border: none;
            background: #20a486;
            color: white;
            border-radius: 13px;
            padding: 0 25px;
            font-weight: 600;
            transition: 0.3s;
        }

        .search-btn:hover {
            background: #147d64;
            transform: scale(1.03);
        }

        /* =====================================================
                           CATEGORY BUTTONS
           ===================================================== */

        .category-container {
            max-width: 1200px;
            margin: 0 auto 45px;
            padding: 0 20px;
            text-align: center;
        }

        .category-btn {
            display: inline-block;
            padding: 9px 17px;
            margin: 5px;
            border-radius: 50px;
            border: 1px solid #b8f2e6;
            background: white;
            color: #147d64;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .category-btn:hover,
        .category-btn.active {
            background: #20a486;
            color: white;
            border-color: #20a486;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(32, 164, 134, 0.18);
        }

        /* =====================================================
                                 ALERT
           ===================================================== */

        .message-container {
            max-width: 1000px;
            margin: 0 auto 30px;
            padding: 0 20px;
        }

        .custom-alert {
            border: none;
            border-radius: 15px;
            animation: alertAnimation 0.5s ease;
        }

        @keyframes alertAnimation {

            from {
                opacity: 0;
                transform: translateY(-15px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

        }

        /* =====================================================
                             PRODUCTS GRID
           ===================================================== */

        .products-container {
            max-width: 1250px;
            margin: 0 auto;
            padding: 0 20px 70px;
        }

        .product-card {
            height: 100%;
            border: none;
            border-radius: 22px;
            overflow: hidden;
            background: white;
            box-shadow:
                0 10px 35px rgba(18, 80, 68, 0.07);
            transition:
                transform 0.4s ease,
                box-shadow 0.4s ease;
            position: relative;
            animation: cardAppear 0.7s ease both;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow:
                0 22px 50px rgba(20, 125, 100, 0.16);
        }

        @keyframes cardAppear {

            from {
                opacity: 0;
                transform: translateY(25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }

        .product-image-wrapper {
            height: 235px;
            background: #f7fffd;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 18px;
            transition:
                transform 0.5s ease,
                filter 0.5s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.08);
            filter: brightness(1.03);
        }

        .prescription-badge {
            position: absolute;
            top: 13px;
            left: 13px;
            background: #fff0f0;
            color: #dc3545;
            border: 1px solid #ffd2d2;
            padding: 6px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            z-index: 3;
        }

        .product-content {
            padding: 22px;
        }

        .product-category {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #20a486;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .product-name {
            font-size: 17px;
            font-weight: 700;
            color: #17352f;
            min-height: 48px;
            transition: color 0.3s ease;
        }

        .product-card:hover .product-name {
            color: #20a486;
        }

        .product-price {
            color: #147d64;
            font-size: 22px;
            font-weight: 700;
            margin: 10px 0;
            animation: priceGlow 3s ease-in-out infinite;
        }

        @keyframes priceGlow {

            0%, 100% {
                transform: translateX(0);
            }

            50% {
                transform: translateX(2px);
            }

        }

        .stock-text {
            font-size: 12px;
            color: #78918b;
            margin-bottom: 15px;
        }

        /* =====================================================
                          ADD TO CART BUTTON
           ===================================================== */

        .add-cart-btn {
            width: 100%;
            min-height: 48px;
            border: none;
            border-radius: 13px;
            background: #20a486;
            color: white;
            font-size: 14px;
            font-weight: 600;
            position: relative;
            overflow: hidden;
            transition:
                background 0.3s ease,
                transform 0.25s ease,
                box-shadow 0.3s ease;
            cursor: pointer;
        }

        .add-cart-btn:hover {
            background: #147d64;
            transform: translateY(-2px);
            box-shadow:
                0 10px 22px rgba(20, 125, 100, 0.25);
        }

        .add-cart-btn:active {
            transform: scale(0.97);
        }

        /* RED AFTER ADDING */

        .add-cart-btn.added {
            background: #dc3545 !important;
            box-shadow:
                0 8px 20px rgba(220, 53, 69, 0.20);
        }

        .add-cart-btn.added:hover {
            background: #bb2d3b !important;
        }

        /* ICON */

        .cart-icon {
            display: inline-block;
            margin-right: 7px;
            font-size: 17px;
            vertical-align: middle;
            transition: transform 0.3s ease;
        }

        .add-cart-btn:hover .cart-icon {
            transform: translateX(3px) rotate(-5deg);
        }

        .add-cart-btn.added .cart-icon {
            animation: cartAdded 0.7s ease;
        }

        @keyframes cartAdded {

            0% {
                transform: scale(1) rotate(0deg);
            }

            35% {
                transform: scale(1.35) rotate(-10deg);
            }

            70% {
                transform: scale(0.9) rotate(8deg);
            }

            100% {
                transform: scale(1) rotate(0deg);
            }

        }

        /* LOADING SPINNER */

        .cart-spinner {
            display: none;
            width: 17px;
            height: 17px;
            border: 2px solid rgba(255,255,255,0.35);
            border-top-color: white;
            border-radius: 50%;
            animation: spinnerRotate 0.7s linear infinite;
            vertical-align: middle;
            margin-right: 8px;
        }

        .add-cart-btn.loading .cart-spinner {
            display: inline-block;
        }

        .add-cart-btn.loading .cart-icon {
            display: none;
        }

        @keyframes spinnerRotate {

            to {
                transform: rotate(360deg);
            }

        }

        /* =====================================================
                              RIPPLE EFFECT
           ===================================================== */

        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.35);
            transform: scale(0);
            animation: rippleAnimation 0.6s linear;
            pointer-events: none;
        }

        @keyframes rippleAnimation {

            to {
                transform: scale(4);
                opacity: 0;
            }

        }

        /* =====================================================
                                VIEW DETAILS
           ===================================================== */

        .view-details-btn {
            width: 100%;
            margin-top: 9px;
            min-height: 43px;
            border-radius: 12px;
            border: 1px solid #b8f2e6;
            background: #f6fffc;
            color: #147d64;
            font-size: 13px;
            font-weight: 600;
            transition: 0.3s;
        }

        .view-details-btn:hover {
            background: #e5faf4;
            border-color: #20a486;
            transform: translateY(-2px);
        }

        /* =====================================================
                                 OUT OF STOCK
           ===================================================== */

        .out-stock-btn {
            width: 100%;
            min-height: 48px;
            border: none;
            border-radius: 13px;
            background: #e9ecef;
            color: #6c757d;
            font-weight: 600;
            cursor: not-allowed;
        }

        /* =====================================================
                           PRODUCT DETAIL
           ===================================================== */

        .detail-card {
            max-width: 1100px;
            margin: 20px auto 70px;
            background: white;
            border-radius: 25px;
            padding: 35px;
            box-shadow:
                0 15px 50px rgba(18, 80, 68, 0.08);
            animation: fadeInUp 0.7s ease;
        }

        .detail-image {
            width: 100%;
            max-height: 450px;
            object-fit: contain;
            border-radius: 20px;
            background: #f8fffd;
            padding: 25px;
        }

        .detail-title {
            font-size: 32px;
            font-weight: 700;
            color: #147d64;
            margin-bottom: 10px;
        }

        .detail-price {
            color: #20a486;
            font-size: 30px;
            font-weight: 700;
            margin: 15px 0;
        }

        .detail-label {
            font-weight: 700;
            color: #17352f;
            margin-top: 20px;
            margin-bottom: 7px;
        }

        .detail-text {
            color: #6b827c;
            line-height: 1.8;
        }

        /* =====================================================
                           EMPTY STATE
           ===================================================== */

        .empty-products {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-products i {
            font-size: 60px;
            color: #b8dcd4;
            margin-bottom: 20px;
        }

        .empty-products h3 {
            color: #4b6b64;
            font-weight: 600;
        }

        .empty-products p {
            color: #849892;
        }

        /* =====================================================
                          RESPONSIVE
           ===================================================== */

        @media (max-width: 768px) {

            .hero-title {
                font-size: 32px;
            }

            .products-hero {
                padding-top: 45px;
            }

            .product-image-wrapper {
                height: 210px;
            }

            .detail-card {
                margin: 10px 15px 50px;
                padding: 20px;
            }

            .detail-title {
                font-size: 26px;
            }

        }

    </style>

</head>

<body>


<!-- =========================================================
     NAVBAR
     ========================================================= -->

<nav class="navbar navbar-expand-lg main-navbar">

    <div class="container">

        <a
            class="navbar-brand"
            href="index.php"
        >
            <i class="bi bi-capsule-pill me-2"></i>
            MediQuick
        </a>


        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
        >
            <span class="navbar-toggler-icon"></span>
        </button>


        <div
            class="collapse navbar-collapse"
            id="mainNavbar"
        >

            <ul class="navbar-nav mx-auto">

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="index.php"
                    >
                        Home
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link active"
                        href="products.php"
                    >
                        Medicines
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="prescription.php"
                    >
                        Prescription
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="track_order.php"
                    >
                        Track Order
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="contact.php"
                    >
                        Contact
                    </a>
                </li>

            </ul>


            <ul class="navbar-nav">

                <li class="nav-item">

                    <a
                        class="nav-link cart-nav"
                        href="cart.php"
                        title="Shopping Cart"
                    >

                        <i class="bi bi-cart3"></i>

                        <span
                            class="cart-badge"
                            id="cartBadge"
                        >
                            <?php echo $cart_count; ?>
                        </span>

                    </a>

                </li>

            </ul>

        </div>

    </div>

</nav>


<!-- =========================================================
                       HERO
     ========================================================= -->

<section class="products-hero">

    <h1 class="hero-title">

        <span>Shop with MediQuick</span>

    </h1>

    <p class="hero-subtitle">

        Find medicines, wellness products, personal care items,
        medical devices and more — all in one convenient place.

    </p>

</section>


<!-- =========================================================
                         SEARCH
     ========================================================= -->

<div class="search-wrapper">

    <form
        method="GET"
        action="products.php"
        class="search-box"
    >

        <div class="input-group">

            <span class="input-group-text bg-white border-0">

                <i class="bi bi-search text-success"></i>

            </span>

            <input
                type="text"
                name="search"
                class="form-control search-input"
                placeholder="Search medicines, products..."
                value="<?php echo e($search); ?>"
            >

            <button
                type="submit"
                class="search-btn"
            >

                <i class="bi bi-search me-1"></i>

                Search

            </button>

        </div>

    </form>

</div>


<!-- =========================================================
     CATEGORIES
     ========================================================= -->

<div class="category-container">

    <a
        href="products.php"
        class="category-btn <?php echo $category === "" ? "active" : ""; ?>"
    >
        All Products
    </a>


    <?php foreach ($categories as $cat): ?>

        <a
            href="products.php?category=<?php echo urlencode($cat); ?>"
            class="category-btn <?php echo $category === $cat ? "active" : ""; ?>"
        >

            <?php echo e($cat); ?>

        </a>

    <?php endforeach; ?>

</div>


<!-- =========================================================
                    MESSAGE
     ========================================================= -->

<?php if ($cart_message !== ""): ?>

<div class="message-container">

    <div
        class="alert alert-<?php echo e($cart_message_type); ?> custom-alert alert-dismissible fade show"
        role="alert"
    >

        <?php if ($cart_message_type === "success"): ?>

            <i class="bi bi-check-circle-fill me-2"></i>

        <?php elseif ($cart_message_type === "warning"): ?>

            <i class="bi bi-exclamation-triangle-fill me-2"></i>

        <?php else: ?>

            <i class="bi bi-x-circle-fill me-2"></i>

        <?php endif; ?>

        <?php echo e($cart_message); ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

</div>

<?php endif; ?>


<!-- =========================================================
                   PRODUCT DETAIL
     ========================================================= -->

<?php if ($product_detail): ?>

<div class="container">

    <div class="detail-card">

        <div class="row g-5 align-items-center">

            <div class="col-lg-6">

                <img
                    src="<?php echo e(getProductImageUrl($product_detail["image_url"])); ?>"
                    class="detail-image"
                    alt="<?php echo e($product_detail["name"]); ?>"
                    onerror="this.src='data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22600%22 height=%22500%22%3E%3Crect width=%22600%22 height=%22500%22 fill=%22%23f1f5f9%22/%3E%3Ctext x=%22300%22 y=%22250%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2226%22 fill=%22%2394a3b8%22%3ENo Image%3C/text%3E%3C/svg%3E';"
                >

            </div>


            <div class="col-lg-6">

                <div class="product-category">

                    <?php echo e($product_detail["category"]); ?>

                </div>


                <h2 class="detail-title">

                    <?php echo e($product_detail["name"]); ?>

                </h2>


                <div class="detail-price">

                    Rs.
                    <?php echo number_format((float)$product_detail["price"], 2); ?>

                </div>


                <div class="detail-text">

                    <?php echo nl2br(e($product_detail["description"])); ?>

                </div>


                <?php if (!empty($product_detail["dosage"])): ?>

                    <div class="detail-label">
                        Dosage / Usage
                    </div>

                    <div class="detail-text">
                        <?php echo nl2br(e($product_detail["dosage"])); ?>
                    </div>

                <?php endif; ?>


                <?php if (!empty($product_detail["safety_info"])): ?>

                    <div class="detail-label">
                        Safety Information
                    </div>

                    <div class="detail-text">
                        <?php echo nl2br(e($product_detail["safety_info"])); ?>
                    </div>

                <?php endif; ?>


                <div class="mt-4">

                    <?php if ((int)$product_detail["stock_qty"] <= 0): ?>

                        <button
                            class="out-stock-btn"
                            disabled
                        >
                            <i class="bi bi-x-circle me-2"></i>
                            Out of Stock
                        </button>

                    <?php elseif ((int)$product_detail["requires_prescription"] === 1): ?>

                        <a
                            href="prescription.php"
                            class="btn btn-danger w-100 py-3"
                        >

                            <i class="bi bi-file-earmark-medical me-2"></i>

                            Upload Prescription

                        </a>

                    <?php else: ?>

                        <?php
                        $detail_cart_qty =
                            isset($_SESSION["cart"][$product_detail["product_id"]])
                                ? (int)$_SESSION["cart"][$product_detail["product_id"]]
                                : 0;
                        ?>

                        <form
                            method="POST"
                            class="add-cart-form"
                        >

                            <input
                                type="hidden"
                                name="product_id"
                                value="<?php echo (int)$product_detail["product_id"]; ?>"
                            >

                            <input
                                type="hidden"
                                name="add_to_cart"
                                value="1"
                            >

                            <button
                                type="submit"
                                class="add-cart-btn <?php echo $detail_cart_qty > 0 ? "added" : ""; ?>"
                            >

                                <span class="cart-spinner"></span>

                                <i class="bi bi-cart-plus cart-icon"></i>

                                <span class="cart-button-text">

                                    <?php
                                    echo $detail_cart_qty > 0
                                        ? "Added to Cart"
                                        : "Add to Cart";
                                    ?>

                                </span>

                            </button>

                        </form>

                    <?php endif; ?>

                </div>


                <div class="mt-3">

                    <a
                        href="products.php"
                        class="view-details-btn d-flex align-items-center justify-content-center"
                    >

                        <i class="bi bi-arrow-left me-2"></i>

                        Back to Products

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>


<?php else: ?>


<!-- =========================================================
     PRODUCTS GRID
     ========================================================= -->

<div class="products-container">

    <?php if (!empty($products)): ?>

        <div class="row g-4">

            <?php foreach ($products as $index => $p): ?>

                <?php

                $product_id = (int)$p["product_id"];

                $current_cart_qty =
                    isset($_SESSION["cart"][$product_id])
                        ? (int)$_SESSION["cart"][$product_id]
                        : 0;

                $is_added = $current_cart_qty > 0;

                ?>

                <div
                    class="col-12 col-sm-6 col-lg-4 col-xl-3"
                >

                    <div
                        class="product-card"
                        style="animation-delay: <?php echo ($index * 0.05); ?>s;"
                    >


                        <!-- PRODUCT IMAGE -->

                        <div class="product-image-wrapper">

                            <?php if ((int)$p["requires_prescription"] === 1): ?>

                                <div class="prescription-badge">

                                    <i class="bi bi-file-earmark-medical me-1"></i>

                                    Prescription

                                </div>

                            <?php endif; ?>


                            <img
                                src="<?php echo e(getProductImageUrl($p["image_url"])); ?>"
                                class="product-image"
                                alt="<?php echo e($p["name"]); ?>"
                                loading="lazy"
                                onerror="this.src='data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22600%22 height=%22500%22%3E%3Crect width=%22600%22 height=%22500%22 fill=%22%23f1f5f9%22/%3E%3Ctext x=%22300%22 y=%22250%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2226%22 fill=%22%2394a3b8%22%3ENo Image%3C/text%3E%3C/svg%3E';"
                            >

                        </div>


                        <!-- PRODUCT CONTENT -->

                        <div class="product-content">


                            <div class="product-category">

                                <?php echo e($p["category"]); ?>

                            </div>


                            <div class="product-name">

                                <?php echo e($p["name"]); ?>

                            </div>


                            <div class="product-price">

                                Rs.
                                <?php echo number_format((float)$p["price"], 2); ?>

                            </div>


                            <div class="stock-text">

                                <?php if ((int)$p["stock_qty"] > 0): ?>

                                    <i class="bi bi-box-seam me-1"></i>

                                    <?php echo (int)$p["stock_qty"]; ?>
                                    available

                                <?php else: ?>

                                    <i class="bi bi-x-circle me-1"></i>

                                    Out of stock

                                <?php endif; ?>

                            </div>


                            <!-- ADD TO CART -->

                            <?php if ((int)$p["stock_qty"] <= 0): ?>

                                <button
                                    class="out-stock-btn"
                                    disabled
                                >

                                    <i class="bi bi-x-circle me-2"></i>

                                    Out of Stock

                                </button>


                            <?php elseif ((int)$p["requires_prescription"] === 1): ?>

                                <a
                                    href="prescription.php"
                                    class="btn btn-danger w-100 py-3"
                                >

                                    <i class="bi bi-file-earmark-medical me-2"></i>

                                    Upload Prescription

                                </a>


                            <?php else: ?>


                                <form
                                    method="POST"
                                    class="add-cart-form"
                                >

                                    <input
                                        type="hidden"
                                        name="product_id"
                                        value="<?php echo $product_id; ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="add_to_cart"
                                        value="1"
                                    >


                                    <button
                                        type="submit"
                                        class="add-cart-btn <?php echo $is_added ? "added" : ""; ?>"
                                    >

                                        <span class="cart-spinner"></span>

                                        <i class="bi bi-cart-plus cart-icon"></i>

                                        <span class="cart-button-text">

                                            <?php
                                            echo $is_added
                                                ? "Added to Cart"
                                                : "Add to Cart";
                                            ?>

                                        </span>

                                    </button>

                                </form>


                            <?php endif; ?>


                            <!-- VIEW DETAILS -->

                            <a
                                href="products.php?id=<?php echo $product_id; ?>"
                                class="view-details-btn d-flex align-items-center justify-content-center"
                            >

                                <i class="bi bi-eye me-2"></i>

                                View Details

                            </a>


                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>


    <?php else: ?>


        <div class="empty-products">

            <i class="bi bi-search"></i>

            <h3>
                No products found
            </h3>

            <p>
                Try another search term or select a different category.
            </p>

            <a
                href="products.php"
                class="btn btn-success mt-2 px-4 py-2"
            >

                <i class="bi bi-arrow-repeat me-2"></i>

                View All Products

            </a>

        </div>


    <?php endif; ?>

</div>

<?php endif; ?>


<!-- =========================================================
                      BOOTSTRAP JS
     ========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<script>

/* =========================================================
               ADD TO CART ANIMATION
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const cartForms =
        document.querySelectorAll(".add-cart-form");


    cartForms.forEach(function (form) {

        form.addEventListener("submit", function (event) {

            const button =
                form.querySelector(".add-cart-btn");

            if (!button) {
                return;
            }


            /*
             * Show loading animation
             */
            button.classList.add("loading");


            const buttonText =
                button.querySelector(".cart-button-text");

            if (buttonText) {

                buttonText.textContent =
                    "Adding...";

            }


            /*
             * Create ripple effect
             */
            const ripple =
                document.createElement("span");

            ripple.classList.add("ripple");

            const rect =
                button.getBoundingClientRect();

            ripple.style.width =
                Math.max(rect.width, rect.height) + "px";

            ripple.style.height =
                Math.max(rect.width, rect.height) + "px";

            ripple.style.left =
                (rect.width / 2) -
                (Math.max(rect.width, rect.height) / 2) +
                "px";

            ripple.style.top =
                (rect.height / 2) -
                (Math.max(rect.width, rect.height) / 2) +
                "px";

            button.appendChild(ripple);


            /*
             * After a short animation:
             * red + Added to Cart
             *
             * Form is NOT disabled permanently.
             * User can click again to add another quantity.
             */
            setTimeout(function () {

                button.classList.remove("loading");

                button.classList.add("added");


                if (buttonText) {

                    buttonText.textContent =
                        "Added to Cart";

                }


                /*
                 * Update cart icon
                 */
                const icon =
                    button.querySelector(".cart-icon");

                if (icon) {

                    icon.className =
                        "bi bi-cart-check-fill cart-icon";

                }


                /*
                 * Animate cart icon
                 */
                if (icon) {

                    icon.classList.remove("cart-bounce");

                    void icon.offsetWidth;

                    icon.classList.add("cart-bounce");

                }


            }, 450);


            /*
             * Allow normal PHP form submission.
             *
             * The timeout above lets the animation begin
             * before the browser submits the form.
             */
        });


        /*
         * If user clicks after page loaded and product
         * already exists in cart, keep red state.
         */
        const button =
            form.querySelector(".add-cart-btn");

        if (
            button &&
            button.classList.contains("added")
        ) {

            const icon =
                button.querySelector(".cart-icon");

            if (icon) {

                icon.className =
                    "bi bi-cart-check-fill cart-icon";

            }

        }

    });

});


/* =========================================================
                         CART ICON BOUNCE
   ========================================================= */

const bounceStyle =
document.createElement("style");

bounceStyle.innerHTML = `

    .cart-bounce {

        animation:
            cartBounceAnimation
            0.7s
            ease;

    }

    @keyframes cartBounceAnimation {

        0% {
            transform: scale(1);
        }

        30% {
            transform: scale(1.4) rotate(-8deg);
        }

        60% {
            transform: scale(0.85) rotate(8deg);
        }

        100% {
            transform: scale(1) rotate(0deg);
        }

    }

`;

document.head.appendChild(bounceStyle);


/* =========================================================
                   PRODUCT CARD TEXT STAGGER ANIMATION
   ========================================================= */

const productCards =
document.querySelectorAll(".product-card");

productCards.forEach(function(card, index) {

    card.style.animationDelay =
        (index * 0.06) + "s";

});


/* =========================================================
   IMAGE LOAD ANIMATION
   ========================================================= */

const productImages =
document.querySelectorAll(".product-image");

productImages.forEach(function(image) {

    image.style.opacity = "0";
    image.style.transform = "scale(0.92)";

    if (image.complete) {

        image.style.transition =
            "opacity 0.6s ease, transform 0.6s ease";

        setTimeout(function() {

            image.style.opacity = "1";
            image.style.transform = "scale(1)";

        }, 100);

    } else {

        image.addEventListener("load", function() {

            image.style.transition =
                "opacity 0.6s ease, transform 0.6s ease";

            image.style.opacity = "1";
            image.style.transform = "scale(1)";

        });

    }

});

</script>

</body>
</html>