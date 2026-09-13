<?php

session_start();

require_once "config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $first_name = trim($_POST['first_name'] ?? "");
    $last_name = trim($_POST['last_name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $phone = trim($_POST['phone'] ?? "");
    $address = trim($_POST['address'] ?? "");
    $city = trim($_POST['city'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";
    $terms = isset($_POST['terms']);


    /* =====================================================
                        VALIDATION
       ===================================================== */

    if (
        $first_name === "" ||
        $last_name === "" ||
        $address === "" ||
        $city === ""
    ) {

        $error = "Please fill in all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (!preg_match('/^\+?[0-9]{9,12}$/', $phone)) {

        $error = "Please enter a valid phone number.";

    } elseif (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[^A-Za-z0-9]/', $password)
    ) {

        $error = "Password must contain at least 8 characters, uppercase, lowercase, number and special character.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } elseif (!$terms) {

        $error = "Please agree to the Terms and Conditions.";

    } else {

        /* =================================================
           CHECK DUPLICATE EMAIL / PHONE
           ================================================= */

        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE email = ? OR phone = ?"
        );

        $check->bind_param(
            "ss",
            $email,
            $phone
        );

        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $error = "Email or phone number is already registered.";

        } else {

            /* =============================================
                             PASSWORD HASH
               ============================================= */

            $hashed_password =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

            /*
             * 1 = Pending
             * 0 = Approved by admin
             */

            $status = 1;
            $role = "customer";


            /* =============================================
                                 INSERT USER
               ============================================= */

            $insert = $conn->prepare(
                "INSERT INTO users
                (
                    first_name,
                    last_name,
                    email,
                    phone,
                    password,
                    address,
                    city,
                    status,
                    role
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $insert->bind_param(
                "sssssssis",
                $first_name,
                $last_name,
                $email,
                $phone,
                $hashed_password,
                $address,
                $city,
                $status,
                $role
            );


            if ($insert->execute()) {

                header(
                    "Location: login.php?registered=1"
                );

                exit;

            } else {

                $error =
                    "Registration failed. Please try again.";

            }
        }
    }
}

?>

<?php include "includes/header.php"; ?>


<style>

/* =========================================================
                          REGISTER PAGE
   ========================================================= */

.register-page {
    min-height: 80vh;
    padding: 55px 20px;
    background:
        radial-gradient(
            circle at top left,
            rgba(32,164,134,.12),
            transparent 35%
        ),
        radial-gradient(
            circle at bottom right,
            rgba(59,130,246,.12),
            transparent 35%
        ),
        #f7fbfc;
}

/* Main two-column layout */

.register-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;

    display: grid;
    grid-template-columns: 420px 1fr;

    gap: 35px;
    align-items: stretch;
}

/* =========================================================
   LEFT IMAGE SECTION
   ========================================================= */

.register-image-section {
    position: relative;
    min-height: 720px;

    overflow: hidden;

    border-radius: 35px;

    background: #dff7f2;

    box-shadow:
        0 25px 70px rgba(0,0,0,.12);

    animation: registerImageIn .9s ease;
}

.register-image {
    width: 100%;
    height: 100%;
    min-height: 720px;

    object-fit: cover;

    display: block;

    transition: transform 1s ease;
}

.register-image-section:hover .register-image {
    transform: scale(1.05);
}

/* Image dark overlay */

.register-image-section::after {
    content: "";

    position: absolute;

    inset: 0;

    background:
        linear-gradient(
            180deg,
            rgba(4,55,50,.03),
            rgba(4,55,50,.68)
        );

    pointer-events: none;
}

/* =========================================================
   IMAGE TEXT
   ========================================================= */

.register-image-content {
    position: absolute;

    z-index: 2;

    left: 35px;
    right: 35px;
    bottom: 40px;

    color: white;

    animation:
        registerTextUp
        1s ease
        .2s
        both;
}

.register-image-content .small-title {
    display: inline-block;

    padding: 7px 14px;

    border-radius: 50px;

    background: rgba(255,255,255,.18);

    backdrop-filter: blur(8px);

    font-size: 13px;

    font-weight: 800;

    letter-spacing: 1px;

    margin-bottom: 15px;

    animation:
        badgeFloat
        2.5s
        ease-in-out
        infinite;
}

.register-image-content h1 {
    margin: 0 0 12px;

    font-size: 39px;

    line-height: 1.15;

    font-weight: 900;

    text-shadow:
        0 5px 20px rgba(0,0,0,.35);

    animation:
        registerFloatingText
        3s
        ease-in-out
        infinite;
}

.register-image-content p {
    margin: 0;

    max-width: 350px;

    font-size: 16px;

    line-height: 1.7;

    text-shadow:
        0 2px 10px rgba(0,0,0,.35);
}

/* =========================================================
   FORM CARD
   ========================================================= */

.register-form-card {
    background: white;

    border-radius: 30px;

    padding: 42px;

    box-shadow:
        0 25px 70px rgba(0,0,0,.10);

    animation:
        registerFormIn
        .8s
        ease;
}

/* Heading */

.register-form-card h2 {
    margin: 0 0 8px;

    font-size: 30px;

    font-weight: 900;

    color: #17352f;

    animation:
        headingReveal
        .8s
        ease;
}

.register-subtitle {
    color: #718096;

    margin-bottom: 30px;

    line-height: 1.6;
}

/* =========================================================
                             FORM GRID
   ========================================================= */

.register-form-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 20px;
}

.register-form-grid .field.full {
    grid-column: 1 / -1;
}

/* =========================================================
                        FORM FIELDS
   ========================================================= */

.register-form-card .field {
    margin-bottom: 2px;
}

.register-form-card label {
    display: block;

    margin-bottom: 8px;

    font-weight: 700;

    color: #29443e;
}

.register-form-card input[type="text"],
.register-form-card input[type="email"],
.register-form-card input[type="tel"],
.register-form-card input[type="password"] {

    width: 100%;

    box-sizing: border-box;

    padding: 14px 16px;

    border:
        1px solid
        #dce5e7;

    border-radius: 12px;

    outline: none;

    font-size: 15px;

    background: #fff;

    transition:
        .3s ease;
}

.register-form-card input:focus {

    border-color:
        #0d9488;

    box-shadow:
        0 0 0 4px
        rgba(13,148,136,.10);

    transform:
        translateY(-1px);
}

.register-form-card input::placeholder {
    color: #9aa9a6;
}

/* =========================================================
                          PASSWORD WRAPPER
   ========================================================= */

.register-password-wrapper {
    position: relative;

    width: 100%;
}

.register-password-wrapper input {
    padding-right: 52px !important;
}

/* Password eye */

.register-password-toggle {
    position: absolute;

    right: 9px;

    top: 50%;

    transform:
        translateY(-50%);

    width: 39px;
    height: 39px;

    border: none;

    background: transparent;

    color: #6b7f7a;

    cursor: pointer;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;

    transition:
        .25s ease;
}

.register-password-toggle:hover {

    color: #087f8c;

    background:
        rgba(32,164,134,.10);

    transform:
        translateY(-50%)
        scale(1.08);
}

/* =========================================================
                       PASSWORD STRENGTH
   ========================================================= */

#passwordStrength {

    display: block;

    margin-top: 7px;

    font-size: 13px;

    font-weight: 700;
}

/* =========================================================
                             TERMS
   ========================================================= */

.terms-field {
    margin-top: 4px;
}

.terms-label {

    display: flex !important;

    align-items: center;

    gap: 9px;

    cursor: pointer;

    font-size: 14px;

    line-height: 1.5;
}

.terms-label input[type="checkbox"] {

    width: 18px;

    height: 18px;

    accent-color:
        #20a486;

    cursor: pointer;

    flex-shrink: 0;
}

/* =========================================================
                  CREATE ACCOUNT BUTTON
   ========================================================= */

.register-button {

    width: 100%;

    border: none;

    padding: 15px 20px;

    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            #0f766e,
            #06b6d4
        );

    color: white;

    font-size: 16px;

    font-weight: 800;

    cursor: pointer;

    position: relative;

    overflow: hidden;

    transition:
        .3s ease;
}

.register-button::before {

    content: "";

    position: absolute;

    top: 0;

    left: -100%;

    width: 100%;

    height: 100%;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(255,255,255,.28),
            transparent
        );

    transition:
        .6s;
}

.register-button:hover::before {
    left: 100%;
}

.register-button:hover {

    transform:
        translateY(-3px);

    box-shadow:
        0 14px 28px
        rgba(13,148,136,.25);
}

.register-button:active {
    transform: translateY(0);
}

/* =========================================================
                        LOGIN LINK
   ========================================================= */

.register-login-text {

    text-align: center;

    margin-top: 25px;

    color: #718096;
}

.register-login-text a {

    color: #087f8c;

    font-weight: 800;

    text-decoration: none;

    transition: .25s;
}

.register-login-text a:hover {

    color: #0f766e;

    text-decoration: underline;
}

/* =========================================================
   ANIMATIONS
   ========================================================= */

@keyframes registerImageIn {

    from {
        opacity: 0;
        transform:
            translateX(-45px);
    }

    to {
        opacity: 1;
        transform:
            translateX(0);
    }
}

@keyframes registerFormIn {

    from {
        opacity: 0;
        transform:
            translateX(35px)
            scale(.98);
    }

    to {
        opacity: 1;
        transform:
            translateX(0)
            scale(1);
    }
}

@keyframes registerTextUp {

    from {
        opacity: 0;
        transform:
            translateY(35px);
    }

    to {
        opacity: 1;
        transform:
            translateY(0);
    }
}

@keyframes headingReveal {

    from {
        opacity: 0;
        transform:
            translateY(15px);
    }

    to {
        opacity: 1;
        transform:
            translateY(0);
    }
}

@keyframes registerFloatingText {

    0%, 100% {
        transform:
            translateY(0);
    }

    50% {
        transform:
            translateY(-5px);
    }
}

@keyframes badgeFloat {

    0%, 100% {
        transform:
            translateY(0);
    }

    50% {
        transform:
            translateY(-4px);
    }
}

/* =========================================================
                        DARK MODE
   ========================================================= */

body.dark-theme .register-page {

    background:
        radial-gradient(
            circle at top left,
            rgba(32,164,134,.15),
            transparent 35%
        ),
        radial-gradient(
            circle at bottom right,
            rgba(59,130,246,.10),
            transparent 35%
        ),
        #0d1715;
}

body.dark-theme .register-form-card {

    background:
        #172a26;

    color:
        #ecfffa;
}

body.dark-theme .register-form-card h2 {

    color:
        #ecfffa;
}

body.dark-theme .register-subtitle {

    color:
        #a8c4bc;
}

body.dark-theme .register-form-card label {

    color:
        #d7eee8;
}

body.dark-theme
.register-form-card input[type="text"],
body.dark-theme
.register-form-card input[type="email"],
body.dark-theme
.register-form-card input[type="tel"],
body.dark-theme
.register-form-card input[type="password"] {

    background:
        #10211d;

    color:
        #ecfffa;

    border-color:
        rgba(116,221,196,.20);
}

body.dark-theme
.register-form-card input::placeholder {

    color:
        #78958d;
}

body.dark-theme
.register-password-toggle {

    color:
        #9ab7af;
}

body.dark-theme
.register-login-text {

    color:
        #a8c4bc;
}

/* =========================================================
                       RESPONSIVE
   ========================================================= */

@media (max-width: 1000px) {

    .register-container {

        grid-template-columns:
            1fr;

        max-width:
            750px;
    }

    .register-image-section {

        min-height:
            380px;

        max-height:
            430px;
    }

    .register-image {

        min-height:
            380px;

        max-height:
            430px;
    }

    .register-image-content h1 {
        font-size: 32px;
    }
}

@media (max-width: 700px) {

    .register-page {
        padding: 35px 15px;
    }

    .register-form-grid {

        grid-template-columns:
            1fr;
    }

    .register-form-grid .field.full {

        grid-column:
            auto;
    }

    .register-form-card {

        padding:
            30px 22px;

        border-radius:
            25px;
    }

    .register-form-card h2 {
        font-size:
            25px;
    }

    .register-image-section {

        min-height:
            320px;

        max-height:
            370px;

        border-radius:
            25px;
    }

    .register-image {

        min-height:
            320px;

        max-height:
            370px;
    }

    .register-image-content {

        left:
            24px;

        right:
            24px;

        bottom:
            25px;
    }

    .register-image-content h1 {
        font-size:
            27px;
    }

    .register-image-content p {
        font-size:
            14px;
    }
}

@media (max-width: 450px) {

    .register-form-card {

        padding:
            25px 18px;
    }

    .register-image-content h1 {

        font-size:
            24px;
    }

    .register-image-content p {

        font-size:
            13px;
    }
}

</style>


<main class="register-page">

    <div class="register-container">


        <!-- =================================================
             LEFT IMAGE
             ================================================= -->

        <div class="register-image-section">

            <img
                src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRSyT1TgW1khsVer8ha-9_rJb2TwdYw3nKZFm39tnxTaQ&s=10"
                alt="MediQuick Healthcare"
                class="register-image"
            >

            <div class="register-image-content">

                <span class="small-title">
                    MEDIQUICK PHARMACY
                </span>

                <h1>
                    Start Your Health Journey
                </h1>

                <p>
                    Create your MediQuick account and enjoy
                    a safer, easier and more convenient
                    online pharmacy experience.
                </p>

            </div>

        </div>


        <!-- =================================================
                               REGISTER FORM
             ================================================= -->

        <div class="register-form-card">

            <h2>
                Create Your MediQuick Account
            </h2>

            <p class="register-subtitle">
                Join MediQuick for safer and easier online healthcare.
            </p>


            <?php if ($error): ?>

                <div class="alert error">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <form
                method="post"
                id="registerForm"
                novalidate
            >

                <div class="register-form-grid">


                    <!-- FIRST NAME -->

                    <div class="field">

                        <label for="first_name">
                            First Name *
                        </label>

                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            placeholder="Enter your first name"
                            value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- LAST NAME -->

                    <div class="field">

                        <label for="last_name">
                            Last Name *
                        </label>

                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            placeholder="Enter your last name"
                            value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="field">

                        <label for="email">
                            Email Address *
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="example@gmail.com"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- PHONE -->

                    <div class="field">

                        <label for="phone">
                            Phone Number *
                        </label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="+94771234567"
                            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- ADDRESS -->

                    <div class="field full">

                        <label for="address">
                            Address *
                        </label>

                        <input
                            type="text"
                            id="address"
                            name="address"
                            placeholder="Enter your full address"
                            value="<?= htmlspecialchars($_POST['address'] ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- CITY -->

                    <div class="field">

                        <label for="city">
                            City *
                        </label>

                        <input
                            type="text"
                            id="city"
                            name="city"
                            placeholder="e.g. Kurunegala"
                            value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- PASSWORD -->

                    <div class="field">

                        <label for="password">
                            Password *
                        </label>

                        <div class="register-password-wrapper">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Create a strong password"
                                required
                            >

                            <button
                                type="button"
                                class="register-password-toggle"
                                data-target="password"
                                aria-label="Show password"
                                title="Show password"
                            >
                                <i class="bi bi-eye"></i>
                            </button>

                        </div>

                        <small id="passwordStrength"></small>

                    </div>


                    <!-- CONFIRM PASSWORD -->

                    <div class="field">

                        <label for="confirm_password">
                            Confirm Password *
                        </label>

                        <div class="register-password-wrapper">

                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Re-enter your password"
                                required
                            >

                            <button
                                type="button"
                                class="register-password-toggle"
                                data-target="confirm_password"
                                aria-label="Show password"
                                title="Show password"
                            >
                                <i class="bi bi-eye"></i>
                            </button>

                        </div>

                    </div>


                    <!-- TERMS -->

                    <div class="field full terms-field">

                        <label class="terms-label">

                            <input
                                type="checkbox"
                                name="terms"
                                value="1"
                                required
                            >

                            <span>
                                I agree to the
                                <strong>
                                    Terms and Conditions
                                </strong>.
                            </span>

                        </label>

                    </div>

                </div>


                <br>


                <!-- CREATE ACCOUNT -->

                <button
                    type="submit"
                    class="register-button"
                >
                    Create Account
                </button>

            </form>


            <p class="register-login-text">

                Already have an account?

                <a href="login.php">
                    Login here
                </a>

            </p>

        </div>

    </div>

</main>


<script>

/* =========================================================
                     SHOW / HIDE PASSWORD
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const passwordToggles =
        document.querySelectorAll(
            ".register-password-toggle"
        );

    passwordToggles.forEach(function (toggle) {

        toggle.addEventListener("click", function () {

            const targetId =
                this.getAttribute("data-target");

            const input =
                document.getElementById(targetId);

            const icon =
                this.querySelector("i");

            if (!input) {
                return;
            }

            if (input.type === "password") {

                input.type = "text";

                icon.className =
                    "bi bi-eye-slash-fill";

                this.setAttribute(
                    "aria-label",
                    "Hide password"
                );

                this.setAttribute(
                    "title",
                    "Hide password"
                );

            } else {

                input.type = "password";

                icon.className =
                    "bi bi-eye";

                this.setAttribute(
                    "aria-label",
                    "Show password"
                );

                this.setAttribute(
                    "title",
                    "Show password"
                );

            }

        });

    });


    /* =====================================================
                PASSWORD STRENGTH DISPLAY
       ===================================================== */

    const password =
        document.getElementById("password");

    const passwordStrength =
        document.getElementById("passwordStrength");


    if (password && passwordStrength) {

        password.addEventListener("input", function () {

            const value =
                password.value;

            let score = 0;

            if (value.length >= 8) {
                score++;
            }

            if (/[A-Z]/.test(value)) {
                score++;
            }

            if (/[a-z]/.test(value)) {
                score++;
            }

            if (/[0-9]/.test(value)) {
                score++;
            }

            if (/[^A-Za-z0-9]/.test(value)) {
                score++;
            }


            if (value.length === 0) {

                passwordStrength.textContent = "";

            } else if (score <= 2) {

                passwordStrength.textContent =
                    "Weak password";

                passwordStrength.style.color =
                    "#dc2626";

            } else if (score <= 4) {

                passwordStrength.textContent =
                    "Medium password";

                passwordStrength.style.color =
                    "#d97706";

            } else {

                passwordStrength.textContent =
                    "Strong password ✓";

                passwordStrength.style.color =
                    "#16a34a";
            }

        });

    }

});

</script>


<?php include "includes/footer.php"; ?>