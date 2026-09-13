<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/db.php";

/*
--------------------------------------------------------------------------
 MediQuick Project Base URL
--------------------------------------------------------------------------
*/

$base_url = "/MediQuick_Pharmacy_Project/MediQuick_Pharmacy_Project/MediQuick_Pharmacy";

/*
--------------------------------------------------------------------------
 Cart Count
--------------------------------------------------------------------------
*/

$cart_count = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <!-- =========================================================
                            BASIC META
    ========================================================== -->

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="MediQuick Pharmacy - Your trusted online pharmacy in Kurunegala, Sri Lanka."
    >

    <meta
        name="author"
        content="MediQuick Pharmacy"
    >

    <!-- =========================================================
                         PAGE TITLE
    ========================================================== -->

    <title>MediQuick Pharmacy</title>

    <!-- =========================================================
                           GOOGLE FONT
    ========================================================== -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <!-- =========================================================
                      BOOTSTRAP ICONS
    ========================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- =========================================================
                      MEDIQUICK CSS
    ========================================================== -->

    <link
        rel="stylesheet"
        href="<?= $base_url ?>/assets/css/style.css"
    >

    <!-- =========================================================
                   GLOBAL THEME + HEADER CSS
    ========================================================== -->

    <style>

        :root {

            --mq-primary: #20a486;
            --mq-primary-dark: #147d64;
            --mq-secondary: #087f8c;

            --mq-body-bg: #ffffff;
            --mq-section-bg: #f5fbf9;
            --mq-card-bg: #ffffff;

            --mq-text: #17352f;
            --mq-text-secondary: #68827b;

            --mq-border:
                rgba(32, 164, 134, 0.15);

            --mq-shadow:
                0 12px 35px rgba(0, 0, 0, 0.08);

            --mq-transition:
                all 0.35s ease;
        }


        /* =====================================================
                          DARK THEME
        ===================================================== */

        body.dark-theme {

            --mq-body-bg: #0d1715;
            --mq-section-bg: #111f1c;
            --mq-card-bg: #172a26;

            --mq-text: #ecfffa;
            --mq-text-secondary: #a8c4bc;

            --mq-border:
                rgba(116, 221, 196, 0.16);

            --mq-shadow:
                0 15px 40px rgba(0, 0, 0, 0.35);

            background:
                var(--mq-body-bg) !important;

            color:
                var(--mq-text) !important;
        }


        body {

            transition:
                background 0.4s ease,
                color 0.4s ease;
        }


        /* =====================================================
                             HEADER
        ===================================================== */

        .site-header {

            transition:
                background 0.4s ease,
                border-color 0.4s ease,
                box-shadow 0.4s ease;

            position: relative;
            z-index: 1000;
        }


        /* Keep all header items on one line */

        .site-header .nav-wrap {

            display: flex;
            align-items: center;

            gap: 22px;

            min-height: 78px;
        }


        /* =====================================================
                        REAL MEDIQUICK LOGO
        ===================================================== */

        .site-header .logo {

            display: flex;
            align-items: center;

            flex-shrink: 0;

            text-decoration: none;

            white-space: nowrap;

            overflow: visible;
        }


        .site-header .logo-image {

            width: 185px;
            height: auto;

            display: block;

            object-fit: contain;

            transform-origin: center center;

            animation:
                mediQuickLogoRotate 8s ease-in-out infinite;

            transition:
                transform 0.35s ease,
                filter 0.35s ease;
        }


        .site-header .logo:hover .logo-image {

            animation-play-state: paused;

            transform:
                scale(1.04)
                rotate(4deg);
        }


        @keyframes mediQuickLogoRotate {

            0% {

                transform:
                    rotate(0deg);
            }

            15% {

                transform:
                    rotate(3deg);
            }

            30% {

                transform:
                    rotate(-3deg);
            }

            45% {

                transform:
                    rotate(2deg);
            }

            60% {

                transform:
                    rotate(-2deg);
            }

            75% {

                transform:
                    rotate(1deg);
            }

            100% {

                transform:
                    rotate(0deg);
            }
        }


        /* =====================================================
                               NAVIGATION
        ===================================================== */

        .main-nav {

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 18px;

            flex: 1;

            white-space: nowrap;
        }


        .main-nav > a,
        .main-nav .dropdown > a {

            white-space: nowrap;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;
        }


        .main-nav a {

            white-space: nowrap;
        }


        /* =====================================================
                             DROPDOWN
        ===================================================== */

        .dropdown {

            position: relative;

            white-space: nowrap;
        }


        .dropdown-menu {

            white-space: nowrap;
        }


        /* =====================================================
                          RIGHT SIDE ACTIONS
        ===================================================== */

        .nav-actions {

            display: flex;

            align-items: center;

            justify-content: flex-end;

            gap: 11px;

            flex-shrink: 0;

            white-space: nowrap;
        }


        .nav-actions .btn {

            white-space: nowrap;

            flex-shrink: 0;
        }


        /* =====================================================
                             THEME BUTTON
        ===================================================== */

        .theme-toggle {

            width: 43px;

            height: 43px;

            border-radius: 50%;

            border:
                1px solid var(--mq-border);

            background:
                var(--mq-card-bg);

            color:
                var(--mq-text);

            display: flex;

            align-items: center;

            justify-content: center;

            cursor: pointer;

            font-size: 18px;

            transition:
                transform 0.3s ease,
                background 0.3s ease,
                color 0.3s ease,
                box-shadow 0.3s ease;

            flex-shrink: 0;
        }


        .theme-toggle:hover {

            transform:
                rotate(18deg)
                scale(1.08);

            box-shadow:
                0 8px 20px
                rgba(32, 164, 134, 0.18);
        }


        .theme-toggle i {

            transition:
                transform 0.35s ease;
        }


        /* =====================================================
                            DIGITAL CLOCK
        ===================================================== */

        .digital-clock {

            min-width: 125px;

            height: 43px;

            padding: 0 13px;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    rgba(32, 164, 134, 0.10),
                    rgba(8, 127, 140, 0.08)
                );

            border:
                1px solid var(--mq-border);

            color:
                var(--mq-primary-dark);

            font-family:
                "Courier New",
                monospace;

            font-size: 14px;

            font-weight: 700;

            letter-spacing: 1px;

            white-space: nowrap;

            box-shadow:
                0 5px 18px
                rgba(32, 164, 134, 0.08);

            transition:
                all 0.35s ease;

            flex-shrink: 0;
        }


        .digital-clock:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 8px 22px
                rgba(32, 164, 134, 0.16);
        }


        .digital-clock .clock-icon {

            font-size: 16px;

            animation:
                clockPulse 2s ease-in-out infinite;
        }


        .digital-clock .clock-time {

            font-variant-numeric:
                tabular-nums;

            white-space: nowrap;
        }


        @keyframes clockPulse {

            0%,
            100% {

                transform:
                    scale(1);
            }

            50% {

                transform:
                    scale(1.12);
            }
        }


        /* =====================================================
                           DARK CLOCK
        ===================================================== */

        body.dark-theme .digital-clock {

            background:
                linear-gradient(
                    135deg,
                    rgba(32, 164, 134, 0.18),
                    rgba(8, 127, 140, 0.15)
                );

            border-color:
                rgba(116, 221, 196, 0.20);

            color:
                #8ce8d1;

            box-shadow:
                0 6px 22px
                rgba(0, 0, 0, 0.25);
        }


        /* =====================================================
                           DARK HEADER
        ===================================================== */

        body.dark-theme .site-header {

            background:
                rgba(15, 30, 27, 0.96) !important;

            border-bottom:
                1px solid rgba(116, 221, 196, 0.12);

            box-shadow:
                0 8px 30px rgba(0, 0, 0, 0.25);
        }


        body.dark-theme .main-nav a {

            color:
                #d9eee8 !important;
        }


        body.dark-theme .main-nav a:hover {

            color:
                #69d8bd !important;
        }


        /* =====================================================
                         DARK BUTTONS
        ===================================================== */

        body.dark-theme .btn-outline {

            color:
                #bcefe2 !important;

            border-color:
                rgba(116, 221, 196, 0.4) !important;
        }


        body.dark-theme .btn-outline:hover {

            background:
                rgba(32, 164, 134, 0.15) !important;
        }


        /* =====================================================
                          DARK DROPDOWN
        ===================================================== */

        body.dark-theme .dropdown-menu {

            background:
                #172a26 !important;

            border-color:
                rgba(116, 221, 196, 0.15) !important;

            box-shadow:
                0 15px 35px rgba(0, 0, 0, 0.35);
        }


        body.dark-theme .dropdown-menu a {

            color:
                #d9eee8 !important;
        }


        body.dark-theme .dropdown-menu a:hover {

            background:
                rgba(32, 164, 134, 0.15) !important;
        }


        /* =====================================================
                     COMMON DARK MODE
        ===================================================== */

        body.dark-theme .card,
        body.dark-theme .product-card,
        body.dark-theme .category-card,
        body.dark-theme .why-card {

            background:
                var(--mq-card-bg) !important;

            color:
                var(--mq-text) !important;
        }


        body.dark-theme h1,
        body.dark-theme h2,
        body.dark-theme h3,
        body.dark-theme h4,
        body.dark-theme h5,
        body.dark-theme h6 {

            color:
                var(--mq-text);
        }


        body.dark-theme p,
        body.dark-theme .muted {

            color:
                var(--mq-text-secondary) !important;
        }


        /* =====================================================
                             CART
        ===================================================== */

        .cart {

            position: relative;

            transition:
                transform 0.3s ease;

            flex-shrink: 0;
        }


        .cart:hover {

            transform:
                translateY(-2px)
                scale(1.05);
        }


        .cart span {

            animation:
                cartCounterPulse 2s infinite;
        }


        @keyframes cartCounterPulse {

            0%,
            100% {

                transform:
                    scale(1);
            }

            50% {

                transform:
                    scale(1.12);
            }
        }


        /* =====================================================
           THEME FADE
        ===================================================== */

        .theme-transition {

            animation:
                themeFade 0.45s ease;
        }


        @keyframes themeFade {

            from {

                opacity:
                    0.75;
            }

            to {

                opacity:
                    1;
            }
        }


        /* =====================================================
                           RESPONSIVE HEADER
        ===================================================== */

        @media (max-width: 1250px) {

            .site-header .nav-wrap {

                gap: 14px;
            }


            .site-header .logo-image {

                width: 155px;
            }


            .main-nav {

                gap: 12px;
            }


            .main-nav a {

                font-size: 13px;
            }


            .nav-actions {

                gap: 7px;
            }


            .nav-actions .btn {

                padding-left: 10px;
                padding-right: 10px;
            }
        }


        @media (max-width: 1050px) {

            .site-header .nav-wrap {

                flex-wrap: wrap;

                padding-top: 10px;

                padding-bottom: 10px;
            }


            .main-nav {

                order: 3;

                width: 100%;

                justify-content: center;

                flex-wrap: nowrap;

                overflow-x: auto;

                padding-top: 8px;

                padding-bottom: 4px;

                scrollbar-width: thin;
            }


            .main-nav > a,
            .main-nav .dropdown {

                flex-shrink: 0;
            }
        }


        @media (max-width: 700px) {

            .site-header .nav-wrap {

                min-height: auto;
            }


            .site-header .logo-image {

                width: 145px;
            }


            .nav-actions {

                margin-left: auto;
            }


            .digital-clock {

                min-width: 105px;

                height: 40px;

                font-size: 12px;

                padding: 0 8px;
            }


            .theme-toggle {

                width: 40px;

                height: 40px;
            }


            .nav-actions .btn {

                padding: 7px 9px;

                font-size: 12px;
            }


            .main-nav {

                justify-content: flex-start;

                gap: 14px;
            }
        }


        @media (max-width: 520px) {

            .site-header .logo-image {

                width: 130px;
            }


            .nav-actions .btn {

                display: none;
            }


            .digital-clock {

                min-width: 96px;

                font-size: 11px;
            }


            .digital-clock .clock-icon {

                display: none;
            }


            .main-nav a {

                font-size: 12px;
            }
        }


        @media (max-width: 380px) {

            .site-header .logo-image {

                width: 115px;
            }


            .digital-clock {

                min-width: 88px;
            }
        }

    </style>


    <!-- =========================================================
                   LOAD SAVED THEME BEFORE PAGE DISPLAY
    ========================================================== -->

    <script>

        (function () {

            const savedTheme =
                localStorage.getItem("mediquick-theme");

            if (savedTheme === "dark") {

                document.addEventListener(
                    "DOMContentLoaded",
                    function () {

                        document.body.classList.add(
                            "dark-theme"
                        );

                    }
                );
            }

        })();

    </script>

</head>


<body>


<!-- =========================================================
                      HEADER / NAVIGATION
========================================================== -->

<header class="site-header">

    <div class="container nav-wrap">


        <!-- =====================================================
             REAL LOGO
        ====================================================== -->

        <a
            class="logo"
            href="<?= $base_url ?>/index.php"
            aria-label="MediQuick Pharmacy Home"
        >

            <img
                src="<?= $base_url ?>/assets/images/logo/logo.png"
                alt="MediQuick Pharmacy"
                class="logo-image"
            >

        </a>


        <!-- =====================================================
                            NAVIGATION
        ====================================================== -->

        <nav class="main-nav">

            <a href="<?= $base_url ?>/index.php">
                Home
            </a>


            <a href="<?= $base_url ?>/products.php">
                Medicines
            </a>


            <!-- CATEGORY DROPDOWN -->

            <div class="dropdown">

                <a
                    href="<?= $base_url ?>/products.php"
                    class="dropdown-toggle"
                >
                    Categories ▾
                </a>


                <div class="dropdown-menu">

                    <a
                        href="<?= $base_url ?>/products.php?category=Prescription%20Medicines"
                    >
                        Prescription Medicines
                    </a>


                    <a
                        href="<?= $base_url ?>/products.php?category=OTC%20Medicines"
                    >
                        OTC Medicines
                    </a>


                    <a
                        href="<?= $base_url ?>/products.php?category=Wellness"
                    >
                        Wellness
                    </a>


                    <a
                        href="<?= $base_url ?>/products.php?category=Personal%20Care"
                    >
                        Personal Care
                    </a>


                    <a
                        href="<?= $base_url ?>/products.php?category=Medical%20Devices"
                    >
                        Medical Devices
                    </a>


                    <a
                        href="<?= $base_url ?>/products.php?category=First%20Aid"
                    >
                        First Aid
                    </a>


                    <a
                        href="<?= $base_url ?>/products.php?category=Baby%20Care"
                    >
                        Baby Care
                    </a>


                    <a
                        href="<?= $base_url ?>/products.php?category=Cosmatics"
                    >
                        Cosmetics
                    </a>

                </div>

            </div>


            <a href="<?= $base_url ?>/prescription.php">
                Upload Prescription
            </a>


            <a href="<?= $base_url ?>/track_order.php">
                Track Order
            </a>


            <a href="<?= $base_url ?>/index.php#about">
                About Us
            </a>


            <a href="<?= $base_url ?>/contact.php">
                Contact
            </a>

        </nav>


        <!-- =====================================================
             RIGHT SIDE ACTIONS
        ====================================================== -->

        <div class="nav-actions">


            <!-- THEME -->

            <button
                type="button"
                class="theme-toggle"
                id="themeToggle"
                title="Change theme"
                aria-label="Change theme"
            >

                <i
                    class="bi bi-sun-fill"
                    id="themeIcon"
                ></i>

            </button>


            <!-- DIGITAL CLOCK -->

            <div
                class="digital-clock"
                id="digitalClock"
                title="Current local time"
                aria-label="Current local time"
            >

                <i
                    class="bi bi-clock-fill clock-icon"
                ></i>

                <span
                    class="clock-time"
                    id="clockTime"
                >
                    00:00:00
                </span>

            </div>


            <!-- CART -->

            <a
                class="cart"
                href="<?= $base_url ?>/cart.php"
                title="Shopping Cart"
            >

                <i class="bi bi-cart3"></i>

                <span>
                    <?= $cart_count ?>
                </span>

            </a>


            <?php if (isset($_SESSION['user_id'])): ?>

                <!-- LOGGED-IN USER -->

                <a
                    class="btn btn-outline"
                    href="<?= $base_url ?>/profile.php"
                >
                    My Account
                </a>


                <a
                    class="btn btn-dark"
                    href="<?= $base_url ?>/logout.php"
                >
                    Logout
                </a>


            <?php else: ?>

                <!-- GUEST USER -->

                <a
                    class="btn btn-outline"
                    href="<?= $base_url ?>/login.php"
                >
                    Login
                </a>


                <a
                    class="btn btn-primary"
                    href="<?= $base_url ?>/register.php"
                >
                    Register
                </a>

            <?php endif; ?>


        </div>

    </div>

</header>


<!-- =========================================================
                THEME + DIGITAL CLOCK JAVASCRIPT
========================================================== -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        /* =====================================================
                               THEME
        ===================================================== */

        const toggle =
            document.getElementById("themeToggle");

        const icon =
            document.getElementById("themeIcon");


        if (toggle && icon) {


            function updateThemeIcon() {

                if (
                    document.body.classList.contains(
                        "dark-theme"
                    )
                ) {

                    icon.className =
                        "bi bi-moon-stars-fill";

                    toggle.title =
                        "Switch to light mode";

                    toggle.setAttribute(
                        "aria-label",
                        "Switch to light mode"
                    );

                } else {

                    icon.className =
                        "bi bi-sun-fill";

                    toggle.title =
                        "Switch to dark mode";

                    toggle.setAttribute(
                        "aria-label",
                        "Switch to dark mode"
                    );
                }

            }


            updateThemeIcon();


            toggle.addEventListener(
                "click",
                function () {

                    document.body.classList.add(
                        "theme-transition"
                    );


                    document.body.classList.toggle(
                        "dark-theme"
                    );


                    const isDark =
                        document.body.classList.contains(
                            "dark-theme"
                        );


                    localStorage.setItem(
                        "mediquick-theme",
                        isDark
                            ? "dark"
                            : "light"
                    );


                    updateThemeIcon();


                    setTimeout(
                        function () {

                            document.body.classList.remove(
                                "theme-transition"
                            );

                        },
                        500
                    );

                }
            );

        }


        /* =====================================================
                            DIGITAL CLOCK
        ===================================================== */

        const clock =
            document.getElementById("clockTime");


        function updateClock() {

            if (!clock) {
                return;
            }


            const now =
                new Date();


            let hours =
                now.getHours();

            const minutes =
                now.getMinutes();

            const seconds =
                now.getSeconds();


            /* 12-HOUR FORMAT */

            const period =
                hours >= 12
                    ? "PM"
                    : "AM";


            hours =
                hours % 12;


            if (hours === 0) {
                hours = 12;
            }


            /* ADD ZERO */

            const formattedHours =
                String(hours).padStart(
                    2,
                    "0"
                );


            const formattedMinutes =
                String(minutes).padStart(
                    2,
                    "0"
                );


            const formattedSeconds =
                String(seconds).padStart(
                    2,
                    "0"
                );


            clock.textContent =
                formattedHours +
                ":" +
                formattedMinutes +
                ":" +
                formattedSeconds +
                " " +
                period;

        }


        updateClock();


        setInterval(
            updateClock,
            1000
        );

    }

);

</script>


<!-- =========================================================
            MAIN WEBSITE CONTENT STARTS AFTER THIS
========================================================= -->