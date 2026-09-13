<?php

session_start();

require_once "config/db.php";

$message = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name =
        trim($_POST['name'] ?? "");

    $email =
        trim($_POST['email'] ?? "");

    $message_text =
        trim($_POST['message'] ?? "");


    if (
        $name === "" ||
        $message_text === "" ||
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            "Please enter valid information.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO inquiries
            (name, email, message)
            VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "sss",
            $name,
            $email,
            $message_text
        );


        if ($stmt->execute()) {

            $message =
                "Thank you. Your inquiry has been submitted successfully.";

        } else {

            $error =
                "Unable to submit your inquiry. Please try again.";

        }
    }
}

?>

<?php include "includes/header.php"; ?>


<style>

/* =========================================================
                  CONTACT / COMPLAINT PAGE
   ========================================================= */

.contact-page {

    min-height: 78vh;

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


/* =========================================================
                          MAIN CONTAINER
   ========================================================= */

.contact-container {

    width: 100%;

    max-width: 1100px;

    margin: 0 auto;

    display: grid;

    grid-template-columns:
        420px 1fr;

    gap: 35px;

    align-items: stretch;
}


/* =========================================================
   LEFT IMAGE
   ========================================================= */

.contact-image-section {

    position: relative;

    min-height: 620px;

    overflow: hidden;

    border-radius: 35px;

    background: #dff7f2;

    box-shadow:
        0 25px 70px rgba(0,0,0,.12);

    animation:
        contactImageIn
        .9s
        ease;
}


.contact-image {

    width: 100%;

    height: 100%;

    min-height: 620px;

    object-fit: cover;

    display: block;

    transition:
        transform 1s ease;
}


.contact-image-section:hover
.contact-image {

    transform:
        scale(1.06);
}


/* Image overlay */

.contact-image-section::after {

    content: "";

    position: absolute;

    inset: 0;

    background:
        linear-gradient(
            180deg,
            rgba(3,60,54,.03),
            rgba(3,60,54,.75)
        );

    pointer-events: none;
}


/* =========================================================
                       IMAGE TEXT
   ========================================================= */

.contact-image-content {

    position: absolute;

    z-index: 2;

    left: 35px;

    right: 35px;

    bottom: 40px;

    color: white;

    animation:
        contactTextUp
        1s
        ease
        .2s
        both;
}


.contact-badge {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding:
        8px 15px;

    border-radius: 50px;

    background:
        rgba(255,255,255,.18);

    border:
        1px solid
        rgba(255,255,255,.25);

    backdrop-filter:
        blur(10px);

    font-size: 13px;

    font-weight: 800;

    letter-spacing: .7px;

    margin-bottom: 15px;

    animation:
        badgeFloat
        2.5s
        ease-in-out
        infinite;
}


.contact-image-content h1 {

    margin:
        0 0 14px;

    font-size:
        38px;

    line-height:
        1.15;

    font-weight:
        900;

    text-shadow:
        0 5px 20px
        rgba(0,0,0,.35);

    animation:
        titleFloat
        3s
        ease-in-out
        infinite;
}


.contact-image-content p {

    margin: 0;

    max-width:
        350px;

    font-size:
        16px;

    line-height:
        1.7;

    text-shadow:
        0 2px 10px
        rgba(0,0,0,.35);
}


/* =========================================================
   CONTACT FORM CARD
   ========================================================= */

.contact-form-card {

    background:
        white;

    padding:
        42px;

    border-radius:
        30px;

    box-shadow:
        0 25px 70px
        rgba(0,0,0,.10);

    animation:
        contactFormIn
        .8s
        ease;

    align-self:
        center;
}


.contact-form-card h2 {

    margin:
        0 0 8px;

    color:
        #17352f;

    font-size:
        30px;

    font-weight:
        900;

    animation:
        headingReveal
        .8s
        ease;
}


.contact-subtitle {

    color:
        #718096;

    margin-bottom:
        30px;

    line-height:
        1.6;
}


/* =========================================================
                         FORM
   ========================================================= */

.contact-form-card .form-grid {

    display:
        grid;

    grid-template-columns:
        repeat(
            2,
            minmax(0,1fr)
        );

    gap:
        20px;
}


.contact-form-card .field {

    margin-bottom:
        2px;
}


.contact-form-card .field.full {

    grid-column:
        1 / -1;
}


.contact-form-card label {

    display:
        block;

    margin-bottom:
        8px;

    color:
        #29443e;

    font-weight:
        700;
}


/* =========================================================
                          INPUTS
   ========================================================= */

.contact-form-card input,
.contact-form-card textarea {

    width:
        100%;

    box-sizing:
        border-box;

    border:
        1px solid
        #dce5e7;

    border-radius:
        13px;

    padding:
        14px 16px;

    outline:
        none;

    font-size:
        15px;

    font-family:
        inherit;

    background:
        #fff;

    transition:
        .3s ease;
}


.contact-form-card input {

    height:
        50px;
}


.contact-form-card textarea {

    resize:
        vertical;

    min-height:
        170px;

    line-height:
        1.6;
}


.contact-form-card input:focus,
.contact-form-card textarea:focus {

    border-color:
        #0d9488;

    box-shadow:
        0 0 0 4px
        rgba(13,148,136,.10);

    transform:
        translateY(-1px);
}


.contact-form-card input::placeholder,
.contact-form-card textarea::placeholder {

    color:
        #9aa9a6;
}


/* =========================================================
                      SUBMIT BUTTON
   ========================================================= */

.contact-submit-button {

    width:
        100%;

    border:
        none;

    padding:
        15px 20px;

    border-radius:
        13px;

    background:
        linear-gradient(
            135deg,
            #0f766e,
            #06b6d4
        );

    color:
        white;

    font-size:
        16px;

    font-weight:
        800;

    cursor:
        pointer;

    position:
        relative;

    overflow:
        hidden;

    transition:
        .3s ease;
}


.contact-submit-button::before {

    content:
        "";

    position:
        absolute;

    top:
        0;

    left:
        -100%;

    width:
        100%;

    height:
        100%;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(255,255,255,.3),
            transparent
        );

    transition:
        .6s;
}


.contact-submit-button:hover::before {

    left:
        100%;
}


.contact-submit-button:hover {

    transform:
        translateY(-3px);

    box-shadow:
        0 14px 30px
        rgba(13,148,136,.25);
}


.contact-submit-button:active {

    transform:
        translateY(0);
}


/* =========================================================
                       SMALL INFO BOXES
   ========================================================= */

.contact-info-row {

    display:
        grid;

    grid-template-columns:
        repeat(2,1fr);

    gap:
        12px;

    margin-top:
        25px;
}


.contact-info-box {

    padding:
        14px;

    border-radius:
        14px;

    background:
        #f4fbf9;

    border:
        1px solid
        rgba(32,164,134,.12);

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    color:
        #31544c;

    font-size:
        13px;

    font-weight:
        700;
}


.contact-info-box i {

    width:
        34px;

    height:
        34px;

    border-radius:
        10px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        rgba(32,164,134,.12);

    color:
        #087f8c;

    font-size:
        16px;
}


/* =========================================================
                          ANIMATIONS
   ========================================================= */

@keyframes contactImageIn {

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


@keyframes contactFormIn {

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


@keyframes contactTextUp {

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


@keyframes titleFloat {

    0%,100% {

        transform:
            translateY(0);
    }

    50% {

        transform:
            translateY(-5px);
    }
}


@keyframes badgeFloat {

    0%,100% {

        transform:
            translateY(0);
    }

    50% {

        transform:
            translateY(-4px);
    }
}


@keyframes headingReveal {

    from {

        opacity:
            0;

        transform:
            translateY(15px);
    }

    to {

        opacity:
            1;

        transform:
            translateY(0);
    }
}


/* =========================================================
                          DARK MODE
   ========================================================= */

body.dark-theme .contact-page {

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


body.dark-theme
.contact-form-card {

    background:
        #172a26;

    color:
        #ecfffa;
}


body.dark-theme
.contact-form-card h2 {

    color:
        #ecfffa;
}


body.dark-theme
.contact-subtitle {

    color:
        #a8c4bc;
}


body.dark-theme
.contact-form-card label {

    color:
        #d7eee8;
}


body.dark-theme
.contact-form-card input,
body.dark-theme
.contact-form-card textarea {

    background:
        #10211d;

    color:
        #ecfffa;

    border-color:
        rgba(116,221,196,.20);
}


body.dark-theme
.contact-form-card input::placeholder,
body.dark-theme
.contact-form-card textarea::placeholder {

    color:
        #78958d;
}


body.dark-theme
.contact-info-box {

    background:
        #10211d;

    color:
        #c6ddd7;

    border-color:
        rgba(116,221,196,.12);
}


/* =========================================================
                        RESPONSIVE
   ========================================================= */

@media (max-width: 950px) {

    .contact-container {

        grid-template-columns:
            1fr;

        max-width:
            750px;
    }


    .contact-image-section {

        min-height:
            380px;

        max-height:
            430px;
    }


    .contact-image {

        min-height:
            380px;

        max-height:
            430px;
    }


    .contact-image-content h1 {

        font-size:
            32px;
    }
}


@media (max-width: 650px) {

    .contact-page {

        padding:
            35px 15px;
    }


    .contact-form-card {

        padding:
            30px 22px;

        border-radius:
            25px;
    }


    .contact-form-card h2 {

        font-size:
            26px;
    }


    .contact-form-card .form-grid {

        grid-template-columns:
            1fr;
    }


    .contact-form-card .field.full {

        grid-column:
            auto;
    }


    .contact-image-section {

        min-height:
            320px;

        max-height:
            370px;

        border-radius:
            25px;
    }


    .contact-image {

        min-height:
            320px;

        max-height:
            370px;
    }


    .contact-image-content {

        left:
            24px;

        right:
            24px;

        bottom:
            25px;
    }


    .contact-image-content h1 {

        font-size:
            27px;
    }


    .contact-image-content p {

        font-size:
            14px;
    }


    .contact-info-row {

        grid-template-columns:
            1fr;
    }
}

</style>


<main class="contact-page">

    <div class="contact-container">


        <!-- =================================================
                               LEFT IMAGE
             ================================================= -->

        <div class="contact-image-section">

            <img
                src="https://images.unsplash.com/photo-1551076805-e1869033e561?auto=format&fit=crop&w=900&q=85"
                alt="MediQuick Customer Support"
                class="contact-image"
            >

            <div class="contact-image-content">

                <div class="contact-badge">

                    <i class="bi bi-headset"></i>

                    MEDIQUICK SUPPORT

                </div>

                <h1>
                    We're Here to Help
                </h1>

                <p>
                    Have a question, complaint, or problem
                    with your order? Send us a message and
                    our MediQuick team will be happy to assist you.
                </p>

            </div>

        </div>


        <!-- =================================================
                            CONTACT FORM
             ================================================= -->

        <div class="contact-form-card">

            <h2>
                Contact MediQuick
            </h2>

            <p class="contact-subtitle">
                Have a question or complaint? Our team is happy to help.
            </p>


            <?php if ($message): ?>

                <div class="alert success">

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <?php if ($error): ?>

                <div class="alert error">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <form method="post">


                <div class="form-grid">


                    <!-- NAME -->

                    <div class="field">

                        <label for="name">
                            Your Name *
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter your name"
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
                            required
                        >

                    </div>


                    <!-- MESSAGE -->

                    <div class="field full">

                        <label for="message">
                            Message / Complaint *
                        </label>

                        <textarea
                            id="message"
                            name="message"
                            rows="7"
                            placeholder="Write your message or complaint here..."
                            required
                        ></textarea>

                    </div>


                </div>


                <br>


                <button
                    type="submit"
                    class="contact-submit-button"
                >

                    <i class="bi bi-send-fill"></i>

                    &nbsp;

                    Send Inquiry

                </button>


            </form>


            <div class="contact-info-row">

                <div class="contact-info-box">

                    <i class="bi bi-shield-check"></i>

                    <span>
                        Secure & Confidential
                    </span>

                </div>


                <div class="contact-info-box">

                    <i class="bi bi-chat-dots"></i>

                    <span>
                        Customer Support
                    </span>

                </div>

            </div>

        </div>

    </div>

</main>


<?php include "includes/footer.php"; ?>