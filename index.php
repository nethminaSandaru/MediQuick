<?php

require_once "config/db.php";

/*
|--------------------------------------------------------------------------
| Get Popular Products
|--------------------------------------------------------------------------
| Only display products that currently have stock.
| Latest products are displayed first.
*/

$result = $conn->query("
    SELECT *
    FROM products
    WHERE stock_qty > 0
    ORDER BY product_id DESC
    LIMIT 8
");

?>

<?php include "includes/header.php"; ?>


<!-- =========================================================
     HERO SECTION
========================================================= -->

<section class="hero">

    <div class="container hero-grid">


        <!-- LEFT SIDE -->

        <div class="hero-content">

            <span class="badge hero-badge">
                YOUR DIGITAL PHARMACY
            </span>


            <h1 class="animated-heading">

                Healthcare made

                <span>
                    simple &amp; safe.
                </span>

            </h1>


            <p class="hero-description">

                Order medicines, upload prescriptions and
                manage your healthcare needs from one trusted
                platform in Kurunegala.

            </p>


            <!-- SEARCH -->

            <form
                class="search-box"
                action="products.php"
                method="get"
            >

                <input
                    type="text"
                    name="search"
                    placeholder="Search medicines, wellness products..."
                    autocomplete="off"
                >

                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <i class="bi bi-search me-1"></i>

                    Search

                </button>

            </form>


            <!-- EXPLORE -->

            <a
                class="btn btn-dark hero-button"
                href="products.php"
            >

                Explore Medicines

                <span class="arrow-animation">
                    →
                </span>

            </a>

        </div>


        <!-- RIGHT SIDE IMAGE -->

        <div class="hero-image-wrapper">

            <div class="hero-image-glow"></div>

            <img
                class="hero-img"
                src="https://images.unsplash.com/photo-1585435557343-3b092031a831?auto=format&fit=crop&w=900&q=80"
                alt="MediQuick Online Pharmacy"
            >

        </div>

    </div>

</section>


<!-- =========================================================
     SHOP BY CATEGORY
========================================================= -->

<section class="section category-section">

    <div class="container">


        <div class="section-title reveal">

            <span class="section-mini-title">
                EXPLORE
            </span>

            <h2>
                Shop by category
            </h2>

            <p>
                Quality products for everyday healthcare
            </p>

        </div>


        <div class="cards category-cards">


            <!-- PRESCRIPTION -->

            <a
                class="card center category-card reveal"
                href="products.php?category=Prescription%20Medicines"
            >

                <div class="category-icon">
                    💊
                </div>

                <h3>
                    Prescription
                </h3>

                <p class="muted">
                    Medicines requiring a valid prescription.
                </p>

                <span class="category-link">
                    Explore →
                </span>

            </a>


            <!-- OTC -->

            <a
                class="card center category-card reveal"
                href="products.php?category=OTC%20Medicines"
            >

                <div class="category-icon">
                    🩹
                </div>

                <h3>
                    OTC Medicines
                </h3>

                <p class="muted">
                    Everyday medicines and first-aid essentials.
                </p>

                <span class="category-link">
                    Explore →
                </span>

            </a>


            <!-- WELLNESS -->

            <a
                class="card center category-card reveal"
                href="products.php?category=Wellness"
            >

                <div class="category-icon">
                    🌿
                </div>

                <h3>
                    Wellness
                </h3>

                <p class="muted">
                    Vitamins and healthy lifestyle products.
                </p>

                <span class="category-link">
                    Explore →
                </span>

            </a>


            <!-- PERSONAL CARE -->

            <a
                class="card center category-card reveal"
                href="products.php?category=Personal%20Care"
            >

                <div class="category-icon">
                    🧴
                </div>

                <h3>
                    Personal Care
                </h3>

                <p class="muted">
                    Trusted personal and hygiene products.
                </p>

                <span class="category-link">
                    Explore →
                </span>

            </a>


        </div>

    </div>

</section>


<!-- =========================================================
     POPULAR PRODUCTS
========================================================= -->

<section class="section popular-products-section">

    <div class="container">


        <div class="section-title reveal">

            <span class="section-mini-title">
                SHOP NOW
            </span>

            <h2>
                Popular products
            </h2>

            <p>
                Selected healthcare products available now
            </p>

        </div>


        <!--
        ========================================================
        FIXED PRODUCT GRID
        Same visual concept as products.php
        ========================================================
        -->

        <div class="home-products-grid">


            <?php if ($result && $result->num_rows > 0): ?>


                <?php
                $product_index = 0;
                ?>


                <?php while ($p = $result->fetch_assoc()): ?>


                    <div
                        class="home-product-card reveal"
                        style="--delay: <?= $product_index * 0.08 ?>s;"
                    >


                        <!-- IMAGE -->

                        <div class="home-product-image-wrapper">

                            <img
                                class="home-product-image"
                                src="<?= htmlspecialchars($p['image_url']) ?>"
                                alt="<?= htmlspecialchars($p['name']) ?>"
                                loading="lazy"
                                onerror="this.style.display='none';"
                            >


                            <?php if ((int)$p['stock_qty'] <= 5): ?>

                                <span class="low-stock-badge">

                                    <i class="bi bi-lightning-charge-fill"></i>

                                    Limited Stock

                                </span>

                            <?php endif; ?>

                        </div>


                        <!-- DETAILS -->

                        <div class="home-product-body">


                            <span class="home-product-category">

                                <?= htmlspecialchars(
                                    $p['category']
                                ) ?>

                            </span>


                            <h3 class="home-product-name">

                                <?= htmlspecialchars(
                                    $p['name']
                                ) ?>

                            </h3>


                            <div class="home-product-bottom">


                                <p class="home-product-price">

                                    LKR

                                    <?= number_format(
                                        $p['price'],
                                        2
                                    ) ?>

                                </p>


                                <span class="stock-indicator">

                                    <i class="bi bi-check-circle-fill"></i>

                                    Available

                                </span>

                            </div>


                            <a
                                class="home-product-button"
                                href="product.php?id=<?= (int)$p['product_id'] ?>"
                            >

                                <span>
                                    View Product
                                </span>

                                <i class="bi bi-arrow-right"></i>

                            </a>

                        </div>

                    </div>


                    <?php
                    $product_index++;
                    ?>


                <?php endwhile; ?>


            <?php else: ?>


                <div class="home-empty-products">

                    <div class="category-icon">
                        💊
                    </div>

                    <h3>
                        No products available
                    </h3>

                    <p>
                        There are currently no products
                        available in stock.
                    </p>

                    <a
                        href="products.php"
                        class="btn btn-primary"
                    >
                        Browse Products
                    </a>

                </div>


            <?php endif; ?>


        </div>


        <!-- VIEW ALL -->

        <?php if ($result && $result->num_rows > 0): ?>

            <div class="view-all-products reveal">

                <a
                    href="products.php"
                    class="btn btn-dark view-all-button"
                >

                    View All Products

                    <span>
                        →
                    </span>

                </a>

            </div>

        <?php endif; ?>


    </div>

</section>


<!-- =========================================================
     ABOUT MEDIQUICK
========================================================= -->

<section
    class="section about-section"
    id="about"
>

    <div class="container">


        <div class="section-title reveal">

            <span class="section-mini-title">
                ABOUT MEDIQUICK
            </span>

            <h2>
                Healthcare made easier for everyone
            </h2>

            <p>
                A trusted digital pharmacy designed around
                convenience, accessibility and responsible
                healthcare service.
            </p>

        </div>


        <div class="about-grid">


            <!-- ABOUT IMAGE -->

            <div class="about-image-box reveal">

                <div class="about-image-decoration"></div>

                <img
                    src="https://www.starmedicalassociates.com/wp-content/uploads/sites/539/2020/06/iStock-1140150522.jpg"
                >

                <div class="about-floating-card">

                    <i class="bi bi-heart-pulse-fill"></i>

                    <div>

                        <strong>
                            Trusted Care
                        </strong>

                        <span>
                            Every step of the way
                        </span>

                    </div>

                </div>

            </div>


            <!-- ABOUT TEXT -->

            <div class="about-content reveal">


                <span class="about-small-title">
                    OUR STORY
                </span>


                <h3>
                    More than an online pharmacy
                </h3>


                <p>
                    MediQuick is a digital pharmacy platform created
                    to make everyday healthcare shopping simpler and
                    more convenient for customers in Sri Lanka.
                </p>


                <p>
                    Our platform allows customers to explore healthcare
                    products, search for medicines, upload prescriptions,
                    manage orders and receive products through a convenient
                    online experience.
                </p>


                <!-- MISSION / VISION -->

                <div class="mission-vision-grid">


                    <div class="mission-card">

                        <div class="mission-icon">
                            <i class="bi bi-bullseye"></i>
                        </div>

                        <div>

                            <h4>
                                Our Mission
                            </h4>

                            <p>
                                To provide a simple, reliable and
                                accessible digital pharmacy experience
                                that helps customers manage their
                                everyday healthcare needs with confidence.
                            </p>

                        </div>

                    </div>


                    <div class="mission-card">

                        <div class="mission-icon">
                            <i class="bi bi-eye-fill"></i>
                        </div>

                        <div>

                            <h4>
                                Our Vision
                            </h4>

                            <p>
                                To become a trusted digital healthcare
                                platform that connects people with
                                quality pharmacy products and convenient
                                healthcare services.
                            </p>

                        </div>

                    </div>


                </div>


            </div>

        </div>

    </div>

</section>


<!-- =========================================================
                     MEDIQUICK HISTORY
========================================================= -->

<section class="section history-section">

    <div class="container">


        <div class="history-grid">


            <!-- HISTORY CONTENT -->

            <div class="history-content reveal">

                <span class="section-mini-title">
                    OUR JOURNEY
                </span>

                <h2>
                    The history of MediQuick
                </h2>


                <p>
                    MediQuick began with a simple idea:
                    make pharmacy services more convenient
                    for people who increasingly rely on digital
                    solutions in their everyday lives.
                </p>


                <p>
                    The concept was developed around the needs
                    of customers who wanted an easier way to
                    discover healthcare products without
                    spending unnecessary time searching through
                    different pharmacy shelves.
                </p>


                <p>
                    As the platform developed, prescription
                    uploading, product browsing, order tracking,
                    customer accounts and digital communication
                    became important parts of the MediQuick
                    experience.
                </p>


                <p>
                    Today, MediQuick represents a modern approach
                    to pharmacy services — combining technology,
                    convenience and customer-focused healthcare
                    support in one platform.
                </p>


                <!-- TIMELINE -->

                <div class="history-timeline">


                    <div class="timeline-item">

                        <div class="timeline-number">
                            01
                        </div>

                        <div>

                            <h4>
                                The Idea
                            </h4>

                            <p>
                                The MediQuick concept was created
                                to simplify access to pharmacy
                                products.
                            </p>

                        </div>

                    </div>


                    <div class="timeline-item">

                        <div class="timeline-number">
                            02
                        </div>

                        <div>

                            <h4>
                                Digital Development
                            </h4>

                            <p>
                                Product browsing, prescription
                                handling and online ordering
                                features were introduced.
                            </p>

                        </div>

                    </div>


                    <div class="timeline-item">

                        <div class="timeline-number">
                            03
                        </div>

                        <div>

                            <h4>
                                Customer First
                            </h4>

                            <p>
                                The platform continues to evolve
                                around convenience and better
                                healthcare experiences.
                            </p>

                        </div>

                    </div>


                </div>

            </div>


            <!-- HISTORY IMAGE -->

            <div class="history-image-wrapper reveal">

                <div class="history-image-glow"></div>

                <img
                    src="https://images.unsplash.com/photo-1587854692152-cbe660dbde88?auto=format&fit=crop&w=1000&q=80"
                    alt="Pharmacy History"
                    class="history-image"
                >


                <div class="history-year-card">

                    <i class="bi bi-hospital"></i>

                    <div>

                        <strong>
                            MediQuick
                        </strong>

                        <span>
                            Digital Pharmacy Journey
                        </span>

                    </div>

                </div>

            </div>


        </div>

    </div>

</section>


<!-- =========================================================
     OUR STAFF
========================================================= -->

<section class="section staff-section">

    <div class="container">


        <div class="section-title reveal">

            <span class="section-mini-title">
                OUR PROFESSIONAL TEAM
            </span>

            <h2>
                Meet our staff
            </h2>

            <p>
                A dedicated team focused on providing
                professional and friendly pharmacy support.
            </p>

        </div>


        <!-- =====================================================
             STAFF GRID
             3 PAIRS = 6 MEMBERS
        ====================================================== -->

        <div class="staff-grid">


            <!-- =================================================
                 MEMBER 1
            ================================================== -->

            <div class="staff-card reveal">

                <div class="staff-photo-wrapper">

                    <img
                        src="assets/images/staff_members_for_aboutus/girl1.jpg"
                        alt="Dr. Nethmi Perera"
                        class="staff-photo"
                    >

                    <span class="staff-number">
                        01
                    </span>

                </div>


                <div class="staff-body">

                    <span class="staff-role">
                        Senior Pharmacist
                    </span>

                    <h3>
                        Dr. Sophia Bennett
                    </h3>


                    <div class="staff-info">

                        <p>
                            <i class="bi bi-mortarboard-fill"></i>
                            B.Pharm, M.Pharm
                        </p>

                        <p>
                            <i class="bi bi-capsule-pill"></i>
                            Clinical Pharmacy
                        </p>

                        <p>
                            <i class="bi bi-award-fill"></i>
                            8+ Years Experience
                        </p>

                        <p>
                            <i class="bi bi-bookmark-star-fill"></i>
                            Certified Medication Therapy Specialist
                        </p>

                        <p>
                            <i class="bi bi-envelope-fill"></i>
                            Sophia@mediquick.lk
                        </p>

                        <p>
                            <i class="bi bi-telephone-fill"></i>
                            +94 77 245 6812
                        </p>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 MEMBER 2
            ================================================== -->

            <div class="staff-card reveal">

                <div class="staff-photo-wrapper">

                    <img
                        src="assets/images/staff_members_for_aboutus/men1.jpg"
                        alt="Mr. Kasun Fernando"
                        class="staff-photo"
                    >

                    <span class="staff-number">
                        02
                    </span>

                </div>


                <div class="staff-body">

                    <span class="staff-role">
                        Pharmacy Manager
                    </span>

                    <h3>
                        Mr. Ethan Mitchell
                    </h3>


                    <div class="staff-info">

                        <p>
                            <i class="bi bi-mortarboard-fill"></i>
                            B.Pharm, MBA
                        </p>

                        <p>
                            <i class="bi bi-capsule-pill"></i>
                            Pharmacy Management
                        </p>

                        <p>
                            <i class="bi bi-award-fill"></i>
                            11+ Years Experience
                        </p>

                        <p>
                            <i class="bi bi-bookmark-star-fill"></i>
                            Healthcare Administration
                        </p>

                        <p>
                            <i class="bi bi-envelope-fill"></i>
                            Mitchell@mediquick.lk
                        </p>

                        <p>
                            <i class="bi bi-telephone-fill"></i>
                            +94 76 318 4925
                        </p>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 MEMBER 3
            ================================================== -->

            <div class="staff-card reveal">

                <div class="staff-photo-wrapper">

                    <img
                        src="assets/images/staff_members_for_aboutus/girl2.jpg"
                        alt="Ms. Dinithi Jayawardena"
                        class="staff-photo"
                    >

                    <span class="staff-number">
                        03
                    </span>

                </div>


                <div class="staff-body">

                    <span class="staff-role">
                        Clinical Pharmacist
                    </span>

                    <h3>
                        Ms.Emily Anderson 
                    </h3>


                    <div class="staff-info">

                        <p>
                            <i class="bi bi-mortarboard-fill"></i>
                            B.Pharm, PGDip Clinical Pharmacy
                        </p>

                        <p>
                            <i class="bi bi-capsule-pill"></i>
                            Clinical &amp; Community Pharmacy
                        </p>

                        <p>
                            <i class="bi bi-award-fill"></i>
                            6+ Years Experience
                        </p>

                        <p>
                            <i class="bi bi-bookmark-star-fill"></i>
                            Patient Counselling Certification
                        </p>

                        <p>
                            <i class="bi bi-envelope-fill"></i>
                             Anderson@mediquick.lk
                        </p>

                        <p>
                            <i class="bi bi-telephone-fill"></i>
                            +94 71 583 7294
                        </p>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 MEMBER 4
            ================================================== -->

            <div class="staff-card reveal">

                <div class="staff-photo-wrapper">

                    <img
                        src="assets/images/staff_members_for_aboutus/men2.jpg"
                        alt="Mr. Tharindu Wijesinghe"
                        class="staff-photo"
                    >

                    <span class="staff-number">
                        04
                    </span>

                </div>


                <div class="staff-body">

                    <span class="staff-role">
                        Community Pharmacist
                    </span>

                    <h3>
                        Mr.Daniel Thompson
                    </h3>


                    <div class="staff-info">

                        <p>
                            <i class="bi bi-mortarboard-fill"></i>
                            B.Pharm, Dip. Healthcare
                        </p>

                        <p>
                            <i class="bi bi-capsule-pill"></i>
                            Community Pharmacy
                        </p>

                        <p>
                            <i class="bi bi-award-fill"></i>
                            7+ Years Experience
                        </p>

                        <p>
                            <i class="bi bi-bookmark-star-fill"></i>
                            First Aid &amp; Emergency Care
                        </p>

                        <p>
                            <i class="bi bi-envelope-fill"></i>
                             Thompson@mediquick.lk
                        </p>

                        <p>
                            <i class="bi bi-telephone-fill"></i>
                            +94 75 642 1938
                        </p>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 MEMBER 5
            ================================================== -->

            <div class="staff-card reveal">

                <div class="staff-photo-wrapper">

                    <img
                        src="assets/images/staff_members_for_aboutus/girl3.jpg"
                        alt="Ms. Hasini Ranasinghe"
                        class="staff-photo"
                    >

                    <span class="staff-number">
                        05
                    </span>

                </div>


                <div class="staff-body">

                    <span class="staff-role">
                        Pharmacy Assistant
                    </span>

                    <h3>
                        Ms.Olivia Carter
                    </h3>


                    <div class="staff-info">

                        <p>
                            <i class="bi bi-mortarboard-fill"></i>
                            Diploma in Pharmacy Practice
                        </p>

                        <p>
                            <i class="bi bi-capsule-pill"></i>
                            Pharmacy Operations
                        </p>

                        <p>
                            <i class="bi bi-award-fill"></i>
                            5+ Years Experience
                        </p>

                        <p>
                            <i class="bi bi-bookmark-star-fill"></i>
                            Customer Care &amp; Inventory
                        </p>

                        <p>
                            <i class="bi bi-envelope-fill"></i>
                            Olivia@mediquick.lk
                        </p>

                        <p>
                            <i class="bi bi-telephone-fill"></i>
                            +94 78 421 5367
                        </p>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 MEMBER 6
            ================================================== -->

            <div class="staff-card reveal">

                <div class="staff-photo-wrapper">

                    <img
                        src="assets/images/staff_members_for_aboutus/men3.jpg"
                        alt="Mr. Dilan Karunaratne"
                        class="staff-photo"
                    >

                    <span class="staff-number">
                        06
                    </span>

                </div>


                <div class="staff-body">

                    <span class="staff-role">
                        Healthcare Support Officer
                    </span>

                    <h3>
                        Mr.Lucas Anderson
                    </h3>


                    <div class="staff-info">

                        <p>
                            <i class="bi bi-mortarboard-fill"></i>
                            BSc Health Sciences
                        </p>

                        <p>
                            <i class="bi bi-capsule-pill"></i>
                            Pharmaceutical Support
                        </p>

                        <p>
                            <i class="bi bi-award-fill"></i>
                            5+ Years Experience
                        </p>

                        <p>
                            <i class="bi bi-bookmark-star-fill"></i>
                            Medical Inventory Management
                        </p>

                        <p>
                            <i class="bi bi-envelope-fill"></i>
                            Lucas@mediquick.lk
                        </p>

                        <p>
                            <i class="bi bi-telephone-fill"></i>
                            +94 72 395 8146
                        </p>

                    </div>

                </div>

            </div>


        </div>

    </div>

</section>


<!-- =========================================================
     WHY CHOOSE MEDIQUICK
========================================================= -->

<section class="section why-section">

    <div class="container">


        <div class="section-title reveal">

            <span class="section-mini-title">
                WHY US
            </span>

            <h2>
                Why choose MediQuick?
            </h2>

            <p>
                A simple and convenient way to manage
                your everyday healthcare needs.
            </p>

        </div>


        <div class="cards why-cards">


            <div class="card center why-card reveal">

                <div class="why-icon">
                    🚚
                </div>

                <h3>
                    Convenient Delivery
                </h3>

                <p class="muted">
                    Get your healthcare products delivered
                    conveniently to your preferred address.
                </p>

            </div>


            <div class="card center why-card reveal">

                <div class="why-icon">
                    📋
                </div>

                <h3>
                    Easy Prescription Upload
                </h3>

                <p class="muted">
                    Upload your prescription online and
                    let our pharmacy staff review it.
                </p>

            </div>


            <div class="card center why-card reveal">

                <div class="why-icon">
                    🔒
                </div>

                <h3>
                    Secure &amp; Reliable
                </h3>

                <p class="muted">
                    Your account and order information
                    are handled through a secure system.
                </p>

            </div>


        </div>

    </div>

</section>


<!-- =========================================================
     INDEX PAGE STYLES
========================================================= -->

<style>


/* =========================================================
   GLOBAL INDEX VARIABLES
========================================================= */

body {

    overflow-x: hidden;

}


/* =========================================================
   HERO
========================================================= */

.hero-content {

    position: relative;

    z-index: 2;

}


.hero-badge {

    animation:
        badgeAppear 0.8s ease;

}


@keyframes badgeAppear {

    from {

        opacity: 0;

        transform:
            translateY(-15px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}


.hero-description {

    max-width: 650px;

    line-height: 1.8;

}


.hero-button {

    margin-top: 15px;

    transition:
        transform 0.3s ease,
        box-shadow 0.3s ease;

}


.hero-button:hover {

    transform:
        translateY(-3px);

    box-shadow:
        0 12px 25px
        rgba(0, 0, 0, 0.18);

}


.arrow-animation {

    display: inline-block;

    margin-left: 7px;

    transition:
        transform 0.3s ease;

}


.hero-button:hover .arrow-animation {

    transform:
        translateX(6px);

}


.hero-image-wrapper {

    display: flex;

    justify-content: center;

    align-items: center;

    position: relative;

}


.hero-img {

    width: 100%;

    max-width: 550px;

    height: 430px;

    object-fit: cover;

    border-radius: 25px;

    position: relative;

    z-index: 2;

    box-shadow:
        0 20px 50px
        rgba(0, 0, 0, 0.12);

    animation:
        heroImageFloat 4s ease-in-out infinite;

}


.hero-image-glow {

    position: absolute;

    width: 280px;

    height: 280px;

    background:
        rgba(32, 164, 134, 0.18);

    border-radius: 50%;

    filter:
        blur(40px);

    animation:
        glowMove 5s ease-in-out infinite;

}


@keyframes heroImageFloat {

    0% {

        transform:
            translateY(0)
            rotate(0deg);

    }

    50% {

        transform:
            translateY(-10px)
            rotate(0.5deg);

    }

    100% {

        transform:
            translateY(0)
            rotate(0deg);

    }

}


@keyframes glowMove {

    0%,
    100% {

        transform:
            scale(1);

    }

    50% {

        transform:
            scale(1.2);

    }

}



/* =========================================================
   HEADING
========================================================= */

.animated-heading {

    animation:
        headingFadeIn 1s ease-out;

}


.animated-heading span {

    display: inline-block;

    animation:
        headingHighlight 2.5s ease-in-out infinite alternate;

}


@keyframes headingFadeIn {

    from {

        opacity: 0;

        transform:
            translateY(30px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}


@keyframes headingHighlight {

    from {

        transform:
            translateY(0);

    }

    to {

        transform:
            translateY(-6px);

    }

}



/* =========================================================
   SECTION TITLES
========================================================= */

.section-title {

    margin-bottom: 40px;

}


.section-mini-title {

    display: inline-block;

    color:
        #20a486;

    font-size: 12px;

    font-weight: 800;

    letter-spacing: 2px;

    margin-bottom: 8px;

}


.section-title h2 {

    position: relative;

}


.section-title h2::after {

    content: "";

    display: block;

    width: 55px;

    height: 4px;

    margin:
        13px auto 0;

    border-radius: 10px;

    background:
        linear-gradient(
            90deg,
            #20a486,
            #087f8c
        );

    animation:
        titleLine 2s ease-in-out infinite alternate;

}


@keyframes titleLine {

    from {

        width: 45px;

    }

    to {

        width: 75px;

    }

}



/* =========================================================
   CATEGORY CARDS
========================================================= */

.category-cards {

    align-items:
        stretch;

}


.category-card {

    display:
        flex;

    flex-direction:
        column;

    justify-content:
        center;

    align-items:
        center;

    min-height:
        260px;

    text-decoration:
        none;

    transition:
        transform 0.4s ease,
        box-shadow 0.4s ease;

}


.category-card:hover {

    transform:
        translateY(-9px);

    box-shadow:
        0 18px 40px
        rgba(0, 0, 0, 0.10);

}


.category-icon {

    width:
        75px;

    height:
        75px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        50%;

    background:
        #e5f8f4;

    font-size:
        38px;

    margin-bottom:
        18px;

    transition:
        transform 0.4s ease;

}


.category-card:hover .category-icon {

    transform:
        rotate(8deg)
        scale(1.08);

}


.category-card h3 {

    margin-bottom:
        10px;

}


.category-card p {

    max-width:
        220px;

    line-height:
        1.6;

}


.category-link {

    margin-top:
        10px;

    color:
        #087f8c;

    font-weight:
        700;

    font-size:
        14px;

}



/* =========================================================
   POPULAR PRODUCTS
   4 COLUMN GRID
========================================================= */

.home-products-grid {

    display:
        grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap:
        24px;

    align-items:
        stretch;

}


.home-product-card {

    background:
        #ffffff;

    border-radius:
        20px;

    overflow:
        hidden;

    display:
        flex;

    flex-direction:
        column;

    min-width:
        0;

    height:
        100%;

    box-shadow:
        0 10px 32px
        rgba(18, 80, 68, 0.07);

    border:
        1px solid
        rgba(32, 164, 134, 0.08);

    transition:
        transform 0.4s ease,
        box-shadow 0.4s ease,
        border-color 0.4s ease;

    animation-delay:
        var(--delay);

}


.home-product-card:hover {

    transform:
        translateY(-9px);

    box-shadow:
        0 20px 45px
        rgba(20, 125, 100, 0.15);

    border-color:
        rgba(32, 164, 134, 0.25);

}


.home-product-image-wrapper {

    width:
        100%;

    height:
        225px;

    min-height:
        225px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        #f5fbf9;

    position:
        relative;

    overflow:
        hidden;

}


.home-product-image {

    width:
        100%;

    height:
        225px;

    object-fit:
        contain;

    padding:
        17px;

    transition:
        transform 0.5s ease;

}


.home-product-card:hover
.home-product-image {

    transform:
        scale(1.08);

}


.low-stock-badge {

    position:
        absolute;

    top:
        12px;

    right:
        12px;

    background:
        #fff4e5;

    color:
        #d97706;

    border:
        1px solid
        #fed7aa;

    border-radius:
        50px;

    padding:
        6px 9px;

    font-size:
        10px;

    font-weight:
        700;

}


.home-product-body {

    padding:
        20px;

    display:
        flex;

    flex-direction:
        column;

    flex:
        1;

}


.home-product-category {

    font-size:
        10px;

    font-weight:
        800;

    letter-spacing:
        1px;

    text-transform:
        uppercase;

    color:
        #20a486;

    margin-bottom:
        8px;

}


.home-product-name {

    font-size:
        16px;

    line-height:
        1.45;

    min-height:
        47px;

    margin:
        0 0 15px;

    display:
        -webkit-box;

    -webkit-line-clamp:
        2;

    -webkit-box-orient:
        vertical;

    overflow:
        hidden;

    color:
        #17352f;

    transition:
        color 0.3s ease;

}


.home-product-card:hover
.home-product-name {

    color:
        #20a486;

}


.home-product-bottom {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        10px;

    margin-top:
        auto;

    margin-bottom:
        17px;

}


.home-product-price {

    margin:
        0;

    color:
        #147d64;

    font-size:
        17px;

    font-weight:
        800;

}


.stock-indicator {

    font-size:
        10px;

    color:
        #3d8d78;

    white-space:
        nowrap;

}


.stock-indicator i {

    margin-right:
        3px;

}


.home-product-button {

    width:
        100%;

    min-height:
        44px;

    border-radius:
        12px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        8px;

    text-decoration:
        none;

    background:
        #20a486;

    color:
        white;

    font-size:
        13px;

    font-weight:
        700;

    transition:
        background 0.3s ease,
        transform 0.3s ease,
        box-shadow 0.3s ease;

}


.home-product-button:hover {

    background:
        #147d64;

    color:
        white;

    transform:
        translateY(-2px);

    box-shadow:
        0 9px 20px
        rgba(32, 164, 134, 0.25);

}


.home-product-button i {

    transition:
        transform 0.3s ease;

}


.home-product-button:hover i {

    transform:
        translateX(5px);

}



/* =========================================================
   DARK MODE PRODUCT CARDS
========================================================= */

body.dark-theme
.home-product-card {

    background:
        #172a26;

    border-color:
        rgba(116, 221, 196, 0.10);

}


body.dark-theme
.home-product-image-wrapper {

    background:
        #12221f;

}


body.dark-theme
.home-product-name {

    color:
        #eafff9;

}



/* =========================================================
   EMPTY PRODUCTS
========================================================= */

.home-empty-products {

    grid-column:
        1 / -1;

    text-align:
        center;

    padding:
        70px 20px;

    background:
        #ffffff;

    border-radius:
        20px;

}


body.dark-theme
.home-empty-products {

    background:
        #172a26;

    color:
        #ecfffa;

}



/* =========================================================
   ABOUT SECTION
========================================================= */

.about-section {

    background:
        linear-gradient(
            135deg,
            #f4fbf9,
            #ffffff
        );

}


body.dark-theme
.about-section {

    background:
        linear-gradient(
            135deg,
            #101f1b,
            #0d1715
        );

}


.about-grid {

    display:
        grid;

    grid-template-columns:
        0.9fr 1.1fr;

    gap:
        65px;

    align-items:
        center;

}


.about-image-box {

    position:
        relative;

}


.about-main-image {

    width:
        100%;

    height:
        510px;

    object-fit:
        cover;

    border-radius:
        28px;

    position:
        relative;

    z-index:
        2;

    box-shadow:
        0 25px 55px
        rgba(0, 0, 0, 0.13);

    transition:
        transform 0.5s ease;

}


.about-image-box:hover
.about-main-image {

    transform:
        scale(1.025);

}


.about-image-decoration {

    position:
        absolute;

    width:
        190px;

    height:
        190px;

    background:
        #20a486;

    opacity:
        0.13;

    border-radius:
        50%;

    top:
        -25px;

    left:
        -25px;

    z-index:
        1;

}


.about-floating-card {

    position:
        absolute;

    right:
        -25px;

    bottom:
        35px;

    z-index:
        4;

    background:
        #ffffff;

    padding:
        17px 20px;

    border-radius:
        16px;

    display:
        flex;

    align-items:
        center;

    gap:
        12px;

    box-shadow:
        0 15px 35px
        rgba(0, 0, 0, 0.14);

    animation:
        floatingCard 3s ease-in-out infinite;

}


.about-floating-card i {

    width:
        42px;

    height:
        42px;

    border-radius:
        12px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    color:
        white;

    background:
        #20a486;

}


.about-floating-card strong {

    display:
        block;

    color:
        #17352f;

    font-size:
        13px;

}


.about-floating-card span {

    display:
        block;

    color:
        #78918b;

    font-size:
        10px;

    margin-top:
        2px;

}


@keyframes floatingCard {

    0%,
    100% {

        transform:
            translateY(0);

    }

    50% {

        transform:
            translateY(-7px);

    }

}


.about-small-title {

    color:
        #20a486;

    font-size:
        12px;

    letter-spacing:
        2px;

    font-weight:
        800;

}


.about-content h3 {

    font-size:
        32px;

    margin:
        8px 0 17px;

}


.about-content > p {

    line-height:
        1.85;

    color:
        #68827b;

}


.mission-vision-grid {

    display:
        grid;

    grid-template-columns:
        1fr 1fr;

    gap:
        17px;

    margin-top:
        25px;

}


.mission-card {

    background:
        rgba(255, 255, 255, 0.85);

    border:
        1px solid
        rgba(32, 164, 134, 0.12);

    padding:
        20px;

    border-radius:
        18px;

    display:
        flex;

    gap:
        14px;

    transition:
        transform 0.35s ease,
        box-shadow 0.35s ease;

}


.mission-card:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 12px 28px
        rgba(32, 164, 134, 0.10);

}


body.dark-theme
.mission-card {

    background:
        #172a26;

}


.mission-icon {

    width:
        43px;

    height:
        43px;

    min-width:
        43px;

    border-radius:
        12px;

    background:
        #e4f8f3;

    color:
        #147d64;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

}


.mission-card h4 {

    margin:
        0 0 6px;

    font-size:
        15px;

}


.mission-card p {

    margin:
        0;

    color:
        #68827b;

    font-size:
        12px;

    line-height:
        1.7;

}



/* =========================================================
   HISTORY SECTION
========================================================= */

.history-section {

    background:
        #ffffff;

}


body.dark-theme
.history-section {

    background:
        #0d1715;

}


.history-grid {

    display:
        grid;

    grid-template-columns:
        1.1fr 0.9fr;

    gap:
        70px;

    align-items:
        center;

}


.history-content h2 {

    font-size:
        38px;

    margin:
        8px 0 20px;

}


.history-content > p {

    color:
        #68827b;

    line-height:
        1.8;

}


.history-timeline {

    margin-top:
        28px;

}


.timeline-item {

    display:
        flex;

    gap:
        15px;

    margin-bottom:
        18px;

}


.timeline-number {

    width:
        38px;

    height:
        38px;

    min-width:
        38px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        50%;

    background:
        #e5f8f4;

    color:
        #147d64;

    font-size:
        11px;

    font-weight:
        800;

}


.timeline-item h4 {

    margin:
        0 0 5px;

    font-size:
        15px;

}


.timeline-item p {

    margin:
        0;

    color:
        #78918b;

    font-size:
        12px;

    line-height:
        1.6;

}


.history-image-wrapper {

    position:
        relative;

}


.history-image {

    width:
        100%;

    height:
        570px;

    object-fit:
        cover;

    border-radius:
        28px;

    position:
        relative;

    z-index:
        2;

    box-shadow:
        0 25px 55px
        rgba(0, 0, 0, 0.13);

}


.history-image-glow {

    position:
        absolute;

    width:
        200px;

    height:
        200px;

    border-radius:
        50%;

    background:
        rgba(32, 164, 134, 0.18);

    filter:
        blur(35px);

    right:
        -40px;

    top:
        -40px;

}


.history-year-card {

    position:
        absolute;

    z-index:
        4;

    bottom:
        30px;

    left:
        -25px;

    background:
        #ffffff;

    border-radius:
        17px;

    padding:
        17px 20px;

    display:
        flex;

    align-items:
        center;

    gap:
        12px;

    box-shadow:
        0 15px 35px
        rgba(0, 0, 0, 0.15);

}


.history-year-card i {

    width:
        43px;

    height:
        43px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        12px;

    background:
        #20a486;

    color:
        white;

}


.history-year-card strong {

    display:
        block;

    font-size:
        14px;

    color:
        #17352f;

}


.history-year-card span {

    display:
        block;

    color:
        #78918b;

    font-size:
        10px;

    margin-top:
        2px;

}



/* =========================================================
   STAFF SECTION
========================================================= */

.staff-section {

    background:
        #f4faf9;

}


body.dark-theme
.staff-section {

    background:
        #111f1c;

}


.staff-grid {

    display:
        grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap:
        25px;

}


.staff-card {

    background:
        #ffffff;

    border-radius:
        22px;

    overflow:
        hidden;

    border:
        1px solid
        rgba(32, 164, 134, 0.09);

    box-shadow:
        0 10px 32px
        rgba(18, 80, 68, 0.07);

    transition:
        transform 0.4s ease,
        box-shadow 0.4s ease;

}


.staff-card:hover {

    transform:
        translateY(-9px);

    box-shadow:
        0 22px 45px
        rgba(20, 125, 100, 0.15);

}


body.dark-theme
.staff-card {

    background:
        #172a26;

}


.staff-photo-wrapper {

    height:
        320px;

    background:
        #eaf7f4;

    position:
        relative;

    overflow:
        hidden;

}


.staff-photo {

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;

    object-position:
        center top;

    transition:
        transform 0.55s ease;

}


.staff-card:hover
.staff-photo {

    transform:
        scale(1.06);

}


.staff-number {

    position:
        absolute;

    top:
        15px;

    right:
        15px;

    width:
        38px;

    height:
        38px;

    border-radius:
        50%;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        rgba(255, 255, 255, 0.92);

    color:
        #147d64;

    font-size:
        11px;

    font-weight:
        800;

    backdrop-filter:
        blur(8px);

}


.staff-body {

    padding:
        22px;

}


.staff-role {

    display:
        inline-block;

    color:
        #20a486;

    text-transform:
        uppercase;

    letter-spacing:
        1px;

    font-size:
        10px;

    font-weight:
        800;

    margin-bottom:
        7px;

}


.staff-body h3 {

    margin:
        0 0 15px;

    font-size:
        20px;

}


.staff-info {

    border-top:
        1px solid
        rgba(32, 164, 134, 0.10);

    padding-top:
        13px;

}


.staff-info p {

    margin:
        8px 0;

    color:
        #708b83;

    font-size:
        11px;

    line-height:
        1.5;

    display:
        flex;

    align-items:
        flex-start;

    gap:
        8px;

}


.staff-info i {

    color:
        #20a486;

    font-size:
        13px;

    margin-top:
        1px;

}



/* =========================================================
   WHY
========================================================= */

.why-section {

    background:
        #f4faf9;

}


body.dark-theme
.why-section {

    background:
        #111f1c;

}


.why-cards {

    align-items:
        stretch;

}


.why-card {

    min-height:
        230px;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        center;

    transition:
        transform 0.35s ease;

}


.why-card:hover {

    transform:
        translateY(-7px);

}


.why-icon {

    font-size:
        40px;

    margin-bottom:
        15px;

}


.why-card p {

    max-width:
        300px;

    line-height:
        1.7;

}



/* =========================================================
   REVEAL ANIMATION
========================================================= */

.reveal {

    opacity:
        0;

    transform:
        translateY(30px);

    transition:
        opacity 0.8s ease,
        transform 0.8s ease;

}


.reveal.active {

    opacity:
        1;

    transform:
        translateY(0);

}



/* =========================================================
   DARK MODE EXTRA FIXES
========================================================= */

body.dark-theme
.about-floating-card,
body.dark-theme
.history-year-card {

    background:
        #172a26;

}


body.dark-theme
.about-floating-card strong,
body.dark-theme
.history-year-card strong {

    color:
        #ecfffa;

}


body.dark-theme
.mission-card p,
body.dark-theme
.about-content > p,
body.dark-theme
.history-content > p,
body.dark-theme
.timeline-item p {

    color:
        #a8c4bc;

}


body.dark-theme
.staff-info p {

    color:
        #a8c4bc;

}



/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1100px) {

    .home-products-grid {

        grid-template-columns:
            repeat(3, minmax(0, 1fr));

    }

    .staff-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

    }

}


@media (max-width: 900px) {

    .hero-grid {

        grid-template-columns:
            1fr;

    }


    .hero-content {

        text-align:
            center;

    }


    .search-box {

        margin-left:
            auto;

        margin-right:
            auto;

    }


    .hero-img {

        height:
            350px;

    }


    .home-products-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

    }


    .about-grid,
    .history-grid {

        grid-template-columns:
            1fr;

        gap:
            45px;

    }


    .about-image-box {

        max-width:
            650px;

        margin:
            auto;

    }


    .history-image-wrapper {

        max-width:
            650px;

        margin:
            auto;

    }


    .history-content {

        order:
            1;

    }


    .history-image-wrapper {

        order:
            2;

    }

}


@media (max-width: 650px) {

    .home-products-grid {

        grid-template-columns:
            1fr;

    }


    .staff-grid {

        grid-template-columns:
            1fr;

    }


    .mission-vision-grid {

        grid-template-columns:
            1fr;

    }


    .about-main-image {

        height:
            400px;

    }


    .history-image {

        height:
            400px;

    }


    .about-floating-card {

        right:
            10px;

    }


    .history-year-card {

        left:
            10px;

    }


    .animated-heading {

        font-size:
            36px;

    }


    .hero-img {

        height:
            280px;

        border-radius:
            18px;

    }


    .home-product-image-wrapper {

        height:
            240px;

        min-height:
            240px;

    }


    .home-product-image {

        height:
            240px;

    }

}

</style>


<!-- =========================================================
     SCROLL ANIMATION JAVASCRIPT
========================================================= -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        /* =====================================================
           REVEAL ELEMENTS WHEN SCROLLING
        ====================================================== */

        const revealElements =
            document.querySelectorAll(".reveal");


        const revealObserver =
            new IntersectionObserver(
                function (entries) {

                    entries.forEach(
                        function (entry) {

                            if (
                                entry.isIntersecting
                            ) {

                                entry.target.classList.add(
                                    "active"
                                );

                                revealObserver.unobserve(
                                    entry.target
                                );

                            }

                        }
                    );

                },
                {
                    threshold: 0.12
                }
            );


        revealElements.forEach(
            function (element) {

                revealObserver.observe(
                    element
                );

            }
        );


        /* =====================================================
           STAFF PHOTO FALLBACK
        ====================================================== */

        const staffImages =
            document.querySelectorAll(".staff-photo");


        staffImages.forEach(
            function (image) {

                image.addEventListener(
                    "error",
                    function () {

                        image.style.opacity = "0.35";

                    }
                );

            }
        );


        /* =====================================================
           PRODUCT IMAGE ANIMATION
        ====================================================== */

        const productImages =
            document.querySelectorAll(
                ".home-product-image"
            );


        productImages.forEach(
            function (image) {

                image.style.opacity = "0";

                image.style.transform =
                    "scale(0.94)";


                if (image.complete) {

                    setTimeout(
                        function () {

                            image.style.transition =
                                "opacity 0.6s ease, transform 0.6s ease";

                            image.style.opacity =
                                "1";

                            image.style.transform =
                                "scale(1)";

                        },
                        150
                    );

                } else {

                    image.addEventListener(
                        "load",
                        function () {

                            image.style.transition =
                                "opacity 0.6s ease, transform 0.6s ease";

                            image.style.opacity =
                                "1";

                            image.style.transform =
                                "scale(1)";

                        }
                    );

                }

            }
        );


        /* =====================================================
           SMOOTH ABOUT LINK
        ====================================================== */

        document
            .querySelectorAll(
                'a[href*="#about"]'
            )
            .forEach(
                function (link) {

                    link.addEventListener(
                        "click",
                        function () {

                            const target =
                                document.getElementById(
                                    "about"
                                );

                            if (target) {

                                setTimeout(
                                    function () {

                                        target.scrollIntoView(
                                            {
                                                behavior:
                                                    "smooth"
                                            }
                                        );

                                    },
                                    50
                                );

                            }

                        }
                    );

                }
            );

    }
);

</script>


<?php include "includes/footer.php"; ?>