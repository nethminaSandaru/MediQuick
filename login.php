<?php
session_start();
require_once "config/db.php";

$error = "";
$success = isset($_GET['registered'])
    ? "Registration successful. Your account is pending administrator approval."
    : "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $e = trim($_POST['email'] ?? "");
    $p = $_POST['password'] ?? "";

    if (!$e || !$p) {
        $error = "Please enter your email and password.";
    } else {

        $s = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
        $s->bind_param("s", $e);
        $s->execute();

        $u = $s->get_result()->fetch_assoc();

        if (!$u || !password_verify($p, $u['password'])) {

            $error = "Invalid email or password.";

        } elseif ((int)$u['status'] !== 0) {

            $error = "Your account is awaiting administrator approval.";

        } else {

            $_SESSION['user_id'] = $u['user_id'];
            $_SESSION['user_name'] = $u['first_name'] . " " . $u['last_name'];
            $_SESSION['role'] = strtolower($u['role']);

            /*
             * ROLE BASED DASHBOARD
             */

            if ($_SESSION['role'] === "admin") {

                header("Location: admin/dashboard.php");
                exit;

            } elseif ($_SESSION['role'] === "staff") {

                header("Location: staff/dashboard.php");
                exit;

            } else {

                header("Location: index.php");
                exit;
            }
        }
    }
}
?>

<?php include "includes/header.php"; ?>

<style>

/* =========================================================
   LOGIN PAGE
   ========================================================= */

.login-page {
    min-height: 78vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 55px 20px;
    background:
        radial-gradient(circle at top left, rgba(13,148,136,.13), transparent 35%),
        radial-gradient(circle at bottom right, rgba(59,130,246,.13), transparent 35%),
        #f7fbfc;
}

/* Main Login Container */

.login-container {
    width: 100%;
    max-width: 1050px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 35px;
    align-items: stretch;
}

/* =========================================================
   LEFT IMAGE SECTION
   ========================================================= */

.login-image-section {
    position: relative;
    min-height: 650px;
    overflow: hidden;
    border-radius: 35px;
    background: #dff7f2;
    box-shadow: 0 25px 70px rgba(0,0,0,.12);
    animation: imageSlideIn .9s ease;
}

.login-image-section::after {
    content: "";
    position: absolute;
    inset: 0;
    background:
        linear-gradient(
            180deg,
            rgba(4,55,50,.05),
            rgba(4,55,50,.55)
        );
    pointer-events: none;
}

.login-image {
    width: 100%;
    height: 100%;
    min-height: 650px;
    object-fit: cover;
    display: block;
    transition: transform 1s ease;
}

.login-image-section:hover .login-image {
    transform: scale(1.05);
}

/* Image text */

.login-image-content {
    position: absolute;
    z-index: 2;
    left: 35px;
    right: 35px;
    bottom: 35px;
    color: white;
    animation: textUp .9s ease .25s both;
}

.login-image-content h1 {
    font-size: 38px;
    font-weight: 900;
    margin: 0 0 12px;
    text-shadow: 0 4px 15px rgba(0,0,0,.3);
    animation: floatingText 3s ease-in-out infinite;
}

.login-image-content p {
    font-size: 16px;
    line-height: 1.7;
    margin: 0;
    max-width: 480px;
    text-shadow: 0 2px 10px rgba(0,0,0,.3);
}

/* =========================================================
   LOGIN BOX
   ========================================================= */

.login-box {
    width: 100%;
    box-sizing: border-box;
    background: white;
    padding: 45px;
    border-radius: 30px;
    box-shadow: 0 25px 70px rgba(0,0,0,.10);
    animation: loginIn .8s ease;
    align-self: center;
}

/* Login Icon */

.login-icon {
    width: 75px;
    height: 75px;
    margin: 0 auto 20px;
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 35px;
    background: linear-gradient(135deg,#0f766e,#06b6d4);
    color: white;
    box-shadow: 0 12px 30px rgba(15,118,110,.25);
    animation: iconPulse 2.5s ease-in-out infinite;
}

.login-box h2 {
    text-align: center;
    margin-bottom: 8px;
    color: #17352f;
    font-weight: 900;
    animation: titleReveal .8s ease;
}

.login-subtitle {
    text-align: center;
    color: #718096;
    margin-bottom: 30px;
    animation: titleReveal .9s ease;
}

/* =========================================================
   FORM
   ========================================================= */

.login-box .field {
    margin-bottom: 20px;
}

.login-box label {
    display: block;
    margin-bottom: 8px;
    font-weight: 700;
    color: #29443e;
}

.login-box input {
    width: 100%;
    box-sizing: border-box;
    padding: 14px 16px;
    border: 1px solid #dce5e7;
    border-radius: 12px;
    outline: none;
    transition: .3s;
    font-size: 15px;
    background: #fff;
}

.login-box input:focus {
    border-color: #0d9488;
    box-shadow: 0 0 0 4px rgba(13,148,136,.10);
    transform: translateY(-1px);
}

/* =========================================================
   PASSWORD WRAPPER
   ========================================================= */

.password-wrapper {
    position: relative;
    width: 100%;
}

.password-wrapper input {
    padding-right: 52px;
}

/* Show Password Button */

.password-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 38px;
    height: 38px;
    border: none;
    background: transparent;
    color: #6b7f7a;
    cursor: pointer;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: .25s ease;
}

.password-toggle:hover {
    background: rgba(32,164,134,.10);
    color: #087f8c;
    transform: translateY(-50%) scale(1.08);
}

/* =========================================================
   LOGIN BUTTON
   ========================================================= */

.login-button {
    width: 100%;
    border: 0;
    padding: 15px;
    border-radius: 12px;
    background: linear-gradient(135deg,#0f766e,#06b6d4);
    color: white;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
    transition: .3s;
    position: relative;
    overflow: hidden;
}

.login-button::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255,255,255,.25),
        transparent
    );
    transition: .6s;
}

.login-button:hover::before {
    left: 100%;
}

.login-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 25px rgba(13,148,136,.25);
}

.login-button:active {
    transform: translateY(0);
}

/* =========================================================
   ANIMATIONS
   ========================================================= */

@keyframes loginIn {
    from {
        opacity: 0;
        transform: translateY(30px) scale(.97);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes imageSlideIn {
    from {
        opacity: 0;
        transform: translateX(-45px);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes textUp {
    from {
        opacity: 0;
        transform: translateY(25px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes titleReveal {
    from {
        opacity: 0;
        transform: translateY(15px);
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
        transform: translateY(-5px);
    }
}

@keyframes iconPulse {
    0%, 100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.05);
    }
}

/* =========================================================
   DARK MODE
   ========================================================= */

body.dark-theme .login-page {
    background:
        radial-gradient(circle at top left, rgba(13,148,136,.15), transparent 35%),
        radial-gradient(circle at bottom right, rgba(59,130,246,.10), transparent 35%),
        #0d1715;
}

body.dark-theme .login-box {
    background: #172a26;
    color: #ecfffa;
}

body.dark-theme .login-box h2 {
    color: #ecfffa;
}

body.dark-theme .login-subtitle {
    color: #a8c4bc;
}

body.dark-theme .login-box label {
    color: #d7eee8;
}

body.dark-theme .login-box input {
    background: #10211d;
    color: #ecfffa;
    border-color: rgba(116,221,196,.20);
}

body.dark-theme .login-box input::placeholder {
    color: #78958d;
}

body.dark-theme .password-toggle {
    color: #9ab7af;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 850px) {

    .login-container {
        grid-template-columns: 1fr;
        max-width: 600px;
    }

    .login-image-section {
        min-height: 330px;
        max-height: 400px;
    }

    .login-image {
        min-height: 330px;
        max-height: 400px;
    }

    .login-image-content h1 {
        font-size: 30px;
    }

    .login-box {
        padding: 35px 28px;
    }
}

@media (max-width: 500px) {

    .login-page {
        padding: 30px 15px;
    }

    .login-image-section {
        min-height: 280px;
        max-height: 330px;
        border-radius: 25px;
    }

    .login-image {
        min-height: 280px;
        max-height: 330px;
    }

    .login-image-content {
        left: 22px;
        right: 22px;
        bottom: 22px;
    }

    .login-image-content h1 {
        font-size: 25px;
    }

    .login-image-content p {
        font-size: 14px;
    }

    .login-box {
        padding: 30px 20px;
        border-radius: 25px;
    }
}

</style>

<main class="login-page">

    <div class="login-container">

        <!-- =====================================================
             LEFT IMAGE
             ===================================================== -->

        <div class="login-image-section">

            <img
                src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQsAF86BKfXDmyAq4EANH_erprpSv1GinF8LaIA624nMw&s=10"
                alt="MediQuick Pharmacy"
                class="login-image"
            >

            <div class="login-image-content">

                <h1>Welcome to MediQuick</h1>

                <p>
                    Your trusted online pharmacy for safe,
                    convenient and reliable healthcare products.
                </p>

            </div>

        </div>


        <!-- =====================================================
             LOGIN FORM
             ===================================================== -->

        <div class="login-box">

            <div class="login-icon">✚</div>

            <h2>Welcome Back</h2>

            <p class="login-subtitle">
                Login to your MediQuick Pharmacy account
            </p>

            <?php if ($error): ?>

                <div class="alert error">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <?php if ($success): ?>

                <div class="alert success">
                    <?= htmlspecialchars($success) ?>
                </div>

            <?php endif; ?>


            <form method="post">

                <!-- EMAIL -->

                <div class="field">

                    <label for="loginEmail">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="loginEmail"
                        name="email"
                        placeholder="you@example.com"
                        required
                    >

                </div>


                <!-- PASSWORD -->

                <div class="field">

                    <label for="loginPassword">
                        Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="loginPassword"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            id="loginPasswordToggle"
                            aria-label="Show password"
                            title="Show password"
                        >
                            <i class="bi bi-eye"></i>
                        </button>

                    </div>

                </div>


                <button
                    class="login-button"
                    type="submit"
                >
                    🔐 Login to MediQuick
                </button>

            </form>


            <p style="text-align:center;margin-top:25px;">

                New customer?

                <a
                    href="register.php"
                    style="color:#087f8c;font-weight:800;"
                >
                    Create an account
                </a>

            </p>

        </div>

    </div>

</main>


<script>

/* =========================================================
   LOGIN PASSWORD SHOW / HIDE
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const passwordInput =
        document.getElementById("loginPassword");

    const passwordToggle =
        document.getElementById("loginPasswordToggle");

    if (passwordInput && passwordToggle) {

        passwordToggle.addEventListener("click", function () {

            if (passwordInput.type === "password") {

                passwordInput.type = "text";

                passwordToggle.innerHTML =
                    '<i class="bi bi-eye-slash-fill"></i>';

                passwordToggle.setAttribute(
                    "aria-label",
                    "Hide password"
                );

                passwordToggle.setAttribute(
                    "title",
                    "Hide password"
                );

            } else {

                passwordInput.type = "password";

                passwordToggle.innerHTML =
                    '<i class="bi bi-eye"></i>';

                passwordToggle.setAttribute(
                    "aria-label",
                    "Show password"
                );

                passwordToggle.setAttribute(
                    "title",
                    "Show password"
                );

            }

        });

    }

});

</script>


<?php include "includes/footer.php"; ?>