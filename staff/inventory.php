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
| IMAGE PATH HELPER
|--------------------------------------------------------------------------
|
| inventory.php is inside:
| /staff/
|
| Product images are inside:
| /assets/images/
|
| Therefore image paths stored in the database need to be converted
| to paths that work from the staff folder.
|
*/

function getProductImageUrl($imageUrl)
{
    $imageUrl = trim((string)$imageUrl);

    // No image stored
    if ($imageUrl === "") {
        return "";
    }

    /*
    |--------------------------------------------------------------
    | Full external URL
    |--------------------------------------------------------------
    */
    if (
        preg_match('/^https?:\/\//i', $imageUrl) ||
        str_starts_with($imageUrl, '//')
    ) {
        return $imageUrl;
    }

    /*
    |--------------------------------------------------------------
    | Already starts with ../
    | Example:
    | ../assets/images/Medicines/test.jpg
    |--------------------------------------------------------------
    */
    if (str_starts_with($imageUrl, '../')) {
        return $imageUrl;
    }

    /*
    |--------------------------------------------------------------
    | Absolute website path
    | Example:
    | /MediQuick_Pharmacy_Project/.../assets/images/...
    |--------------------------------------------------------------
    */
    if (str_starts_with($imageUrl, '/')) {
        return $imageUrl;
    }

    /*
    |--------------------------------------------------------------
    | Remove ./ if stored
    |--------------------------------------------------------------
    */
    $imageUrl = preg_replace('/^\.\//', '', $imageUrl);

    /*
    |--------------------------------------------------------------
    | Database contains:
    |
    | assets/images/Medicines/image.jpg
    |
    | From staff/inventory.php we need:
    |
    | ../assets/images/Medicines/image.jpg
    |--------------------------------------------------------------
    */
    if (str_starts_with($imageUrl, 'assets/images/')) {
        return '../' . $imageUrl;
    }

    /*
    |--------------------------------------------------------------
    | Database contains:
    |
    | images/Medicines/image.jpg
    |
    |--------------------------------------------------------------
    */
    if (str_starts_with($imageUrl, 'images/')) {
        return '../assets/' . $imageUrl;
    }

    /*
    |--------------------------------------------------------------
    | Database contains:
    |
    | Medicines/image.jpg
    | Wellness/image.jpg
    | Personal_Care/image.jpg
    |
    | Add:
    | ../assets/images/
    |--------------------------------------------------------------
    */
    return '../assets/images/' . ltrim($imageUrl, '/');
}

/*
|--------------------------------------------------------------------------
| UPDATE STOCK
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_stock'])) {

    $product_id = (int)($_POST['product_id'] ?? 0);
    $stock_qty = max(0, (int)($_POST['stock_qty'] ?? 0));

    if ($product_id > 0) {

        $stmt = $conn->prepare(
            "UPDATE products SET stock_qty=? WHERE product_id=?"
        );

        $stmt->bind_param("ii", $stock_qty, $product_id);

        if ($stmt->execute()) {
            $message = "Inventory updated successfully.";
        } else {
            $error = "Unable to update inventory.";
        }
    }
}

/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? "");
$category = trim($_GET['category'] ?? "");

$sql = "SELECT * FROM products WHERE 1=1";

$params = [];
$types = "";

if ($search !== "") {

    $sql .= " AND name LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

if ($category !== "") {

    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY stock_qty ASC, product_id DESC";

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

/*
|--------------------------------------------------------------------------
| CATEGORIES
|--------------------------------------------------------------------------
*/

$categories = [];

$catResult = $conn->query(
    "SELECT DISTINCT category
     FROM products
     WHERE category IS NOT NULL
     AND category <> ''
     ORDER BY category"
);

if ($catResult) {

    while ($cat = $catResult->fetch_assoc()) {
        $categories[] = $cat['category'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Inventory | MediQuick Staff</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f8fa;
    color: #26343d;
}

/* =========================================================
   LAYOUT
========================================================= */

.staff-layout {
    display: flex;
    min-height: 100vh;
}

/* =========================================================
   SIDEBAR
========================================================= */

.sidebar {
    width: 260px;
    background: linear-gradient(
        180deg,
        #064e3b,
        #059669,
        #10b981
    );

    color: white;
    padding: 25px 18px;

    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;

    box-shadow: 8px 0 30px rgba(0,0,0,.12);

    z-index: 1000;
}

.logo-area {
    text-align: center;
    margin-bottom: 30px;
}

.logo-circle {
    width: 60px;
    height: 60px;

    margin: auto;

    background: white;
    color: #059669;

    border-radius: 20px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 30px;

    box-shadow: 0 8px 20px rgba(0,0,0,.12);
}

.logo-area h2 {
    margin: 12px 0 3px;
}

.logo-area small {
    opacity: .75;
}

.sidebar a {
    display: block;

    padding: 13px 15px;

    color: white;
    text-decoration: none;

    margin: 7px 0;

    border-radius: 12px;

    font-weight: 600;

    transition: .3s;
}

.sidebar a:hover,
.sidebar a.active {
    background: rgba(255,255,255,.18);

    transform: translateX(5px);
}

.logout {
    margin-top: 25px !important;

    background: rgba(239,68,68,.2);
}

.logout:hover {
    background: rgba(239,68,68,.35) !important;
}

/* =========================================================
   MAIN CONTENT
========================================================= */

.main-content {
    margin-left: 260px;

    width: calc(100% - 260px);

    padding: 30px;
}

/* =========================================================
   TOP BAR
========================================================= */

.topbar {
    background: white;

    padding: 22px 28px;

    border-radius: 20px;

    margin-bottom: 25px;

    box-shadow: 0 8px 25px rgba(0,0,0,.06);

    display: flex;

    justify-content: space-between;

    align-items: center;
}

.topbar h1 {
    margin: 0;

    font-size: 28px;

    color: #17352f;
}

.topbar p {
    margin: 6px 0 0;

    color: #718096;
}

.staff-name {
    background: #ecfdf5;

    color: #047857;

    padding: 10px 16px;

    border-radius: 30px;

    font-weight: bold;

    white-space: nowrap;
}

/* =========================================================
   ALERTS
========================================================= */

.alert {
    padding: 15px;

    border-radius: 12px;

    margin-bottom: 20px;

    font-weight: bold;

    animation: fadeDown .4s ease;
}

.success {
    background: #dcfce7;

    color: #166534;

    border: 1px solid #bbf7d0;
}

.error {
    background: #fee2e2;

    color: #991b1b;

    border: 1px solid #fecaca;
}

/* =========================================================
   SEARCH
========================================================= */

.search-card {
    background: white;

    padding: 22px;

    border-radius: 20px;

    margin-bottom: 25px;

    box-shadow: 0 8px 25px rgba(0,0,0,.05);
}

.search-form {
    display: flex;

    gap: 12px;

    flex-wrap: wrap;
}

.search-form input,
.search-form select {
    padding: 13px 15px;

    border: 1px solid #dbe5e8;

    border-radius: 10px;

    min-width: 200px;

    background: white;

    color: #26343d;

    font-size: 14px;

    outline: none;

    transition: .25s;
}

.search-form input:focus,
.search-form select:focus {
    border-color: #059669;

    box-shadow: 0 0 0 3px rgba(5,150,105,.10);
}

.search-btn {
    background: #059669;

    color: white;

    border: 0;

    padding: 13px 22px;

    border-radius: 10px;

    font-weight: bold;

    cursor: pointer;

    transition: .25s;
}

.search-btn:hover {
    background: #047857;

    transform: translateY(-2px);
}

.reset-btn {
    background: #64748b;

    color: white;

    text-decoration: none;

    padding: 13px 20px;

    border-radius: 10px;

    font-weight: bold;

    transition: .25s;
}

.reset-btn:hover {
    background: #475569;

    transform: translateY(-2px);
}

/* =========================================================
   INVENTORY GRID
========================================================= */

.inventory-grid {
    display: grid;

    grid-template-columns:
        repeat(auto-fill, minmax(290px, 1fr));

    gap: 22px;
}

/* =========================================================
   PRODUCT CARD
========================================================= */

.product-card {
    background: white;

    border-radius: 20px;

    padding: 20px;

    box-shadow: 0 10px 30px rgba(0,0,0,.06);

    transition: .3s;

    animation: fadeUp .5s ease;

    border: 1px solid rgba(5,150,105,.05);

    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-7px);

    box-shadow: 0 18px 40px rgba(0,0,0,.10);

    border-color: rgba(5,150,105,.15);
}

/* =========================================================
   PRODUCT TOP
========================================================= */

.product-top {
    display: flex;

    justify-content: space-between;

    gap: 15px;

    align-items: flex-start;
}

/* =========================================================
   PRODUCT IMAGE
========================================================= */

.product-image {
    width: 100px;

    height: 100px;

    object-fit: contain;

    border-radius: 18px;

    background: #f0fdf4;

    padding: 10px;

    border: 1px solid #dcfce7;

    transition: .35s;

    display: block;
}

.product-card:hover .product-image {
    transform: scale(1.06);

    background: #ecfdf5;
}

/*
|--------------------------------------------------------------------------
| Image Error Fallback
|--------------------------------------------------------------------------
|
| If an old/wrong image path exists in the database, the image will
| automatically show the medicine emoji instead of a broken-image icon.
|
*/

.product-image-fallback {
    width: 100px;

    height: 100px;

    border-radius: 18px;

    background: #f0fdf4;

    border: 1px solid #dcfce7;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 42px;

    flex-shrink: 0;
}

/* =========================================================
   CATEGORY
========================================================= */

.category {
    display: inline-block;

    padding: 6px 10px;

    border-radius: 20px;

    background: #ecfdf5;

    color: #047857;

    font-size: 11px;

    font-weight: bold;

    margin-top: 15px;
}

/* =========================================================
   PRODUCT DETAILS
========================================================= */

.product-card h3 {
    margin: 12px 0 7px;

    color: #26343d;

    font-size: 19px;

    line-height: 1.35;
}

.price {
    font-weight: 800;

    color: #047857;

    font-size: 17px;
}

/* =========================================================
   STOCK
========================================================= */

.stock {
    padding: 8px 12px;

    border-radius: 20px;

    display: inline-block;

    font-weight: bold;

    font-size: 12px;

    white-space: nowrap;
}

.stock.good {
    background: #dcfce7;

    color: #166534;
}

.stock.low {
    background: #fef3c7;

    color: #92400e;
}

.stock.out {
    background: #fee2e2;

    color: #991b1b;
}

/* =========================================================
   STOCK FORM
========================================================= */

.stock-form {
    display: flex;

    gap: 8px;

    margin-top: 15px;
}

.stock-form input {
    flex: 1;

    padding: 11px 12px;

    border: 1px solid #dbe5e8;

    border-radius: 9px;

    outline: none;

    font-size: 14px;
}

.stock-form input:focus {
    border-color: #059669;

    box-shadow: 0 0 0 3px rgba(5,150,105,.10);
}

.stock-form button {
    border: 0;

    background: #059669;

    color: white;

    padding: 10px 14px;

    border-radius: 9px;

    font-weight: bold;

    cursor: pointer;

    transition: .25s;

    white-space: nowrap;
}

.stock-form button:hover {
    background: #047857;

    transform: translateY(-1px);
}

/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {
    background: white;

    padding: 60px 30px;

    margin-top: 25px;

    border-radius: 20px;

    text-align: center;

    box-shadow: 0 10px 30px rgba(0,0,0,.05);
}

.empty-state-icon {
    font-size: 55px;

    margin-bottom: 10px;
}

.empty-state h3 {
    margin: 10px 0 5px;
}

.empty-state p {
    color: #718096;

    margin: 0;
}

/* =========================================================
   ANIMATIONS
========================================================= */

@keyframes fadeUp {

    from {
        opacity: 0;

        transform: translateY(20px);
    }

    to {
        opacity: 1;

        transform: translateY(0);
    }

}

@keyframes fadeDown {

    from {
        opacity: 0;

        transform: translateY(-10px);
    }

    to {
        opacity: 1;

        transform: translateY(0);
    }

}

/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 900px) {

    .sidebar {
        position: relative;

        width: 100%;

        min-height: auto;

        padding: 20px;
    }

    .staff-layout {
        display: block;
    }

    .main-content {
        margin-left: 0;

        width: 100%;

        padding: 18px;
    }

    .topbar {
        flex-direction: column;

        align-items: flex-start;

        gap: 15px;
    }

}

@media(max-width: 600px) {

    .main-content {
        padding: 12px;
    }

    .topbar {
        padding: 20px;
    }

    .topbar h1 {
        font-size: 23px;
    }

    .search-card {
        padding: 16px;
    }

    .search-form {
        flex-direction: column;
    }

    .search-form input,
    .search-form select,
    .search-btn,
    .reset-btn {
        width: 100%;
        min-width: 0;
    }

    .inventory-grid {
        grid-template-columns: 1fr;
    }

    .product-card {
        padding: 18px;
    }

}

/* =========================================================
   REDUCED MOTION
========================================================= */

@media(prefers-reduced-motion: reduce) {

    *,
    *::before,
    *::after {
        animation-duration: .01ms !important;

        animation-iteration-count: 1 !important;

        transition-duration: .01ms !important;
    }

}

</style>

</head>

<body>

<div class="staff-layout">

<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="sidebar">

    <div class="logo-area">

        <div class="logo-circle">✚</div>

        <h2>MediQuick</h2>

        <small>Staff Portal</small>

    </div>

    <a href="dashboard.php">
        🏠 Dashboard
    </a>

    <a href="orders.php">
        📦 Orders
    </a>

    <a href="prescriptions.php">
        📋 Prescriptions
    </a>

    <a href="inventory.php" class="active">
        📊 Inventory
    </a>

    <a href="customers.php">
        👥 Customers
    </a>

    <a href="inquiries.php">
        💬 Inquiries
    </a>

    <a href="profile.php">
        👤 My Profile
    </a>

    <a href="../index.php">
        🌐 View Website
    </a>

    <a href="../logout.php" class="logout">
        🚪 Logout
    </a>

</aside>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<main class="main-content">

    <!-- TOP BAR -->

    <div class="topbar">

        <div>

            <h1>
                Inventory Management 📊
            </h1>

            <p>
                Monitor medicine stock and update quantities.
            </p>

        </div>

        <div class="staff-name">

            👨‍💼
            <?= htmlspecialchars($_SESSION['user_name'] ?? 'Staff') ?>

        </div>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if ($message): ?>

        <div class="alert success">

            ✅ <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <!-- ERROR MESSAGE -->

    <?php if ($error): ?>

        <div class="alert error">

            ❌ <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         SEARCH CARD
    ================================================= -->

    <div class="search-card">

        <form method="get" class="search-form">

            <input
                type="text"
                name="search"
                placeholder="Search medicine..."
                value="<?= htmlspecialchars($search) ?>"
            >

            <select name="category">

                <option value="">
                    All Categories
                </option>

                <?php foreach ($categories as $cat): ?>

                    <option
                        value="<?= htmlspecialchars($cat) ?>"
                        <?= $category === $cat ? 'selected' : '' ?>
                    >

                        <?= htmlspecialchars($cat) ?>

                    </option>

                <?php endforeach; ?>

            </select>

            <button
                type="submit"
                class="search-btn"
            >
                🔎 Search
            </button>

            <a
                href="inventory.php"
                class="reset-btn"
            >
                Reset
            </a>

        </form>

    </div>


    <!-- =================================================
         INVENTORY PRODUCTS
    ================================================= -->

    <div class="inventory-grid">

    <?php foreach ($products as $product): ?>

        <?php

        $stock = (int)$product['stock_qty'];

        if ($stock <= 0) {

            $stockClass = "out";
            $stockText = "Out of Stock";

        } elseif ($stock <= 10) {

            $stockClass = "low";
            $stockText = "Low Stock";

        } else {

            $stockClass = "good";
            $stockText = "In Stock";

        }

        /*
        |--------------------------------------------------------------------------
        | FIX PRODUCT IMAGE PATH
        |--------------------------------------------------------------------------
        */

        $productImage = getProductImageUrl(
            $product['image_url'] ?? ''
        );

        ?>

        <div class="product-card">

            <!-- PRODUCT TOP -->

            <div class="product-top">

                <!-- PRODUCT IMAGE -->

                <?php if ($productImage !== ""): ?>

                    <img
                        class="product-image"
                        src="<?= htmlspecialchars($productImage) ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        loading="lazy"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                    >

                    <!-- IMAGE FALLBACK -->

                    <div
                        class="product-image-fallback"
                        style="display:none;"
                    >
                        💊
                    </div>

                <?php else: ?>

                    <div class="product-image-fallback">
                        💊
                    </div>

                <?php endif; ?>


                <!-- STOCK INFORMATION -->

                <div style="text-align:right;">

                    <span class="stock <?= $stockClass ?>">

                        <?= $stockText ?>

                    </span>

                    <div style="margin-top:10px;">

                        <strong>
                            <?= $stock ?>
                        </strong>

                        units

                    </div>

                </div>

            </div>


            <!-- CATEGORY -->

            <span class="category">

                <?= htmlspecialchars($product['category']) ?>

            </span>


            <!-- PRODUCT NAME -->

            <h3>

                <?= htmlspecialchars($product['name']) ?>

            </h3>


            <!-- PRICE -->

            <div class="price">

                LKR
                <?= number_format((float)$product['price'], 2) ?>

            </div>


            <!-- STOCK UPDATE FORM -->

            <form
                method="post"
                class="stock-form"
            >

                <input
                    type="hidden"
                    name="product_id"
                    value="<?= (int)$product['product_id'] ?>"
                >

                <input
                    type="number"
                    name="stock_qty"
                    min="0"
                    value="<?= $stock ?>"
                    required
                >

                <button
                    type="submit"
                    name="update_stock"
                >
                    Update
                </button>

            </form>

        </div>

    <?php endforeach; ?>

    </div>


    <!-- =================================================
         NO PRODUCTS
    ================================================= -->

    <?php if (count($products) === 0): ?>

        <div class="empty-state">

            <div class="empty-state-icon">
                🔎
            </div>

            <h3>
                No products found
            </h3>

            <p>
                Try another medicine name or category.
            </p>

        </div>

    <?php endif; ?>


</main>

</div>

</body>

</html>