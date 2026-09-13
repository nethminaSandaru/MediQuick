<!-- =========================================================
     FOOTER
========================================================= -->

<footer class="footer">


    <div class="container footer-grid">


        <!-- =================================================
                       ABOUT MEDIQUICK
        ================================================== -->

        <div class="footer-column">

            <h3>

                <i class="bi bi-capsule-pill"></i>

                MediQuick

            </h3>


            <p>

                Your trusted online pharmacy for medicines,

                wellness products and personal care.

            </p>


            <p>

                <i class="bi bi-geo-alt-fill"></i>

                Kurunegala, Sri Lanka

            </p>

        </div>


        <!-- =================================================
                      QUICK LINKS
        ================================================== -->

        <div class="footer-column">

            <h4>
                Quick Links
            </h4>


            <a href="<?= $base_url ?>/index.php">

                <i class="bi bi-house-door-fill"></i>

                Home

            </a>


            <a href="<?= $base_url ?>/products.php">

                <i class="bi bi-grid-fill"></i>

                Products

            </a>


            <a href="<?= $base_url ?>/prescription.php">

                <i class="bi bi-file-earmark-medical-fill"></i>

                Upload Prescription

            </a>


            <a href="<?= $base_url ?>/track_order.php">

                <i class="bi bi-truck"></i>

                Track Order

            </a>


            <!-- OUR BRANCHES -->

            <a
                href="<?= $base_url ?>/branches.php"
                class="branches-footer-link"
            >

                <i class="bi bi-buildings-fill"></i>

                Our Branches

            </a>


            <!-- FEEDBACK -->

            <a href="<?= $base_url ?>/feedback.php">

                <i class="bi bi-chat-heart-fill"></i>

                Customer Feedback

            </a>

        </div>


        <!-- =================================================
             MY ACCOUNT
        ================================================== -->

        <div class="footer-column">

            <h4>
                My Account
            </h4>


            <a href="<?= $base_url ?>/profile.php">

                <i class="bi bi-person-circle"></i>

                My Account

            </a>


            <a href="<?= $base_url ?>/cart.php">

                <i class="bi bi-cart-fill"></i>

                My Cart

            </a>


            <a href="<?= $base_url ?>/contact.php">

                <i class="bi bi-envelope-fill"></i>

                Contact Us

            </a>


            <a href="<?= $base_url ?>/feedback.php">

                <i class="bi bi-star-fill"></i>

                Feedback

            </a>


            <a href="<?= $base_url ?>/privacy.php">

                <i class="bi bi-shield-lock-fill"></i>

                Privacy Policy

            </a>

        </div>


        <!-- =================================================
             CONTACT
        ================================================== -->

        <div class="footer-column">

            <h4>
                Contact Us
            </h4>


            <p>

                <i class="bi bi-telephone-fill"></i>

                +94 37 222 4567

            </p>


            <p>

                <i class="bi bi-envelope-fill"></i>

                hello@mediquick.lk

            </p>


            <p>

                <i class="bi bi-geo-alt-fill"></i>

                Kurunegala, Sri Lanka

            </p>


            <!-- =================================================
                 SOCIAL MEDIA
            ================================================== -->

            <div class="social-links">


                <a
                    href="#"
                    aria-label="Facebook"
                    title="Facebook"
                >

                    <i class="bi bi-facebook"></i>

                </a>


                <a
                    href="#"
                    aria-label="Instagram"
                    title="Instagram"
                >

                    <i class="bi bi-instagram"></i>

                </a>


                <a
                    href="#"
                    aria-label="Twitter"
                    title="Twitter"
                >

                    <i class="bi bi-twitter-x"></i>

                </a>


            </div>

        </div>

    </div>


    <!-- =========================================================
         FOOTER BOTTOM
    ========================================================== -->

    <div class="footer-bottom">


        <div class="container">


            <p>

                © <?= date("Y") ?>

                MediQuick Pharmacy.

                All rights reserved.

            </p>


            <div class="footer-bottom-links">


                <a href="<?= $base_url ?>/privacy.php">

                    Privacy Policy

                </a>


                <span>|</span>


                <a href="#">

                    Terms &amp; Conditions

                </a>


                <span>|</span>


                <a href="<?= $base_url ?>/feedback.php">

                    Feedback

                </a>

            </div>

        </div>

    </div>

</footer>


<!-- =========================================================
     FOOTER CSS
========================================================= -->

<style>

.footer {

    position: relative;
}


/* =========================================================
   FOOTER COLUMNS
========================================================= */

.footer-column h3 {

    display: flex;

    align-items: center;

    gap: 8px;
}


.footer-column h3 i {

    color:
        var(--mq-primary);
}


.footer-column p {

    display: flex;

    align-items: flex-start;

    gap: 8px;
}


.footer-column p i {

    color:
        var(--mq-primary);

    margin-top: 3px;

    flex-shrink: 0;
}


.footer-column a {

    display: flex;

    align-items: center;

    gap: 8px;

    white-space: nowrap;
}


.footer-column a i {

    width: 18px;

    color:
        var(--mq-primary);

    transition:
        transform .25s ease;
}


.footer-column a:hover i {

    transform:
        translateX(3px);
}


/* =========================================================
                   OUR BRANCHES SPECIAL ANIMATION
========================================================= */

.branches-footer-link {

    position: relative;

    transition:
        transform .3s ease,
        color .3s ease;
}


.branches-footer-link::after {

    content: "";

    position: absolute;

    left: 26px;

    right: 0;

    bottom: -3px;

    height: 2px;

    background:
        var(--mq-primary);

    transform:
        scaleX(0);

    transform-origin:
        left;

    transition:
        transform .3s ease;
}


.branches-footer-link:hover {

    transform:
        translateX(4px);
}


.branches-footer-link:hover::after {

    transform:
        scaleX(1);
}


/* =========================================================
                  SOCIAL MEDIA
========================================================= */

.social-links {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-top: 18px;
}


.social-links a {

    width: 42px;

    height: 42px;

    padding: 0 !important;

    display: flex !important;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background:
        rgba(32, 164, 134, .1);

    color:
        var(--mq-primary);

    text-decoration: none;

    border:
        1px solid
        rgba(32, 164, 134, .15);

    transition:
        transform .3s ease,
        background .3s ease,
        color .3s ease,
        box-shadow .3s ease;
}


.social-links a i {

    width: auto !important;

    color:
        inherit;

    font-size: 17px;

    transform:
        none !important;
}


.social-links a:hover {

    transform:
        translateY(-5px)
        scale(1.06);

    background:
        var(--mq-primary);

    color:
        #fff;

    box-shadow:
        0 10px 25px
        rgba(32, 164, 134, .28);
}


/* =========================================================
   FOOTER BOTTOM
========================================================= */

.footer-bottom-links {

    display: flex;

    align-items: center;

    gap: 10px;

    flex-wrap: wrap;
}


.footer-bottom-links a {

    transition:
        color .25s ease;
}


.footer-bottom-links a:hover {

    color:
        var(--mq-primary);
}


/* =========================================================
   DARK THEME
========================================================= */

body.dark-theme .social-links a {

    background:
        rgba(116, 221, 196, .08);

    border-color:
        rgba(116, 221, 196, .14);
}


body.dark-theme .social-links a:hover {

    background:
        var(--mq-primary);

    color:
        #fff;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 600px) {

    .social-links {

        margin-top: 15px;
    }


    .social-links a {

        width: 40px;

        height: 40px;
    }


    .footer-bottom-links {

        justify-content: center;

        margin-top: 10px;
    }

}

</style>


<!-- JAVASCRIPT -->

<script src="<?= $base_url ?>/assets/js/script.js"></script>


</body>

</html>