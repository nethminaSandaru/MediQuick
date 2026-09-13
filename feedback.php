<?php

session_start();

require_once "config/db.php";


// =========================================================
// USER INFORMATION
// =========================================================

$isLoggedIn = isset($_SESSION['user_id']);

$currentUserId = $isLoggedIn
    ? (int)$_SESSION['user_id']
    : 0;


// =========================================================
// MESSAGES
// =========================================================

$success = "";
$error = "";


// =========================================================
// FEEDBACK IMAGE DIRECTORY
// =========================================================

$feedbackImageDirectory =
    __DIR__ . "/assets/images/feedback_profiles/";

$feedbackImageWebPath =
    "assets/images/feedback_profiles/";


// Create directory automatically if it does not exist

if (!is_dir($feedbackImageDirectory)) {

    @mkdir(
        $feedbackImageDirectory,
        0777,
        true
    );
}


// =========================================================
// IMAGE UPLOAD FUNCTION
// =========================================================

function uploadFeedbackImage(
    $file,
    $feedbackImageDirectory,
    $feedbackImageWebPath
) {

    // No image selected

    if (
        !isset($file) ||
        !isset($file['error']) ||
        $file['error'] === UPLOAD_ERR_NO_FILE
    ) {

        return [
            "success" => true,
            "path" => null
        ];
    }


    // Upload error

    if ($file['error'] !== UPLOAD_ERR_OK) {

        return [
            "success" => false,
            "error" => "Unable to upload the profile image."
        ];
    }


    // Maximum image size = 5MB

    if ($file['size'] > 5 * 1024 * 1024) {

        return [
            "success" => false,
            "error" => "Profile image must be smaller than 5MB."
        ];
    }


    // Get MIME type

    $imageInfo = @getimagesize(
        $file['tmp_name']
    );


    if ($imageInfo === false) {

        return [
            "success" => false,
            "error" => "Please select a valid image."
        ];
    }


    $allowedTypes = [

        "image/jpeg" => "jpg",

        "image/png" => "png",

        "image/webp" => "webp"

    ];


    $mimeType = $imageInfo['mime'] ?? "";


    if (!isset($allowedTypes[$mimeType])) {

        return [
            "success" => false,
            "error" => "Only JPG, PNG and WEBP images are allowed."
        ];
    }


    // Generate unique filename

    $extension =
        $allowedTypes[$mimeType];

    $fileName =
        "feedback_" .
        time() .
        "_" .
        bin2hex(random_bytes(5)) .
        "." .
        $extension;


    $destination =
        $feedbackImageDirectory .
        $fileName;


    if (
        !move_uploaded_file(
            $file['tmp_name'],
            $destination
        )
    ) {

        return [
            "success" => false,
            "error" => "Unable to save the profile image."
        ];
    }


    return [

        "success" => true,

        "path" =>
            $feedbackImageWebPath .
            $fileName
    ];
}


// =========================================================
// DELETE OLD LOCAL IMAGE
// =========================================================

function deleteFeedbackImage($imagePath)
{

    if (!$imagePath) {

        return;
    }


    /*
     * Only delete images belonging to the
     * feedback_profiles folder.
     */

    if (
        strpos(
            $imagePath,
            "assets/images/feedback_profiles/"
        ) !== 0
    ) {

        return;
    }


    $fullPath =
        __DIR__ . "/" .
        $imagePath;


    if (is_file($fullPath)) {

        @unlink($fullPath);
    }
}


// =========================================================
// EDIT MODE
// =========================================================

$editFeedback = null;


if (
    $isLoggedIn &&
    isset($_GET['edit'])
) {

    $editId =
        (int)$_GET['edit'];


    $stmt = $conn->prepare(
        "SELECT *
         FROM feedback
         WHERE feedback_id = ?
         AND user_id = ?
         LIMIT 1"
    );


    $stmt->bind_param(
        "ii",
        $editId,
        $currentUserId
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    if (
        $result->num_rows === 1
    ) {

        $editFeedback =
            $result->fetch_assoc();

    } else {

        $error =
            "You can only edit your own feedback.";
    }
}


// =========================================================
// ADD / UPDATE / DELETE
// =========================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {

    $action =
        $_POST['action'] ?? "";


    // =====================================================
    // ADD FEEDBACK
    // =====================================================

    if ($action === "add") {


        if (!$isLoggedIn) {

            $error =
                "Please login before submitting feedback.";

        } else {


            $name =
                trim($_POST['name'] ?? "");


            $email =
                trim($_POST['email'] ?? "");


            $message =
                trim($_POST['message'] ?? "");


            $rating =
                (int)(
                    $_POST['rating'] ?? 0
                );


            // -------------------------------------------------
            // VALIDATION
            // -------------------------------------------------

            if ($name === "") {

                $error =
                    "Please enter your name.";

            } elseif (
                !filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                $error =
                    "Please enter a valid email address.";

            } elseif ($message === "") {

                $error =
                    "Please write your feedback.";

            } elseif (
                strlen($message) < 5
            ) {

                $error =
                    "Feedback must contain at least 5 characters.";

            } elseif (
                $rating < 1 ||
                $rating > 5
            ) {

                $error =
                    "Please select a rating from 1 to 5.";

            } else {


                // -------------------------------------------------
                // IMAGE
                // -------------------------------------------------

                $imageResult =
                    uploadFeedbackImage(
                        $_FILES['profile_image'] ?? null,
                        $feedbackImageDirectory,
                        $feedbackImageWebPath
                    );


                if (
                    !$imageResult['success']
                ) {

                    $error =
                        $imageResult['error'];

                } else {


                    /*
                     * If user did not upload an image,
                     * create a random avatar.
                     */

                    if (
                        $imageResult['path'] === null
                    ) {

                        $profileImage =
                            "https://i.pravatar.cc/150?img=" .
                            rand(1, 70);

                    } else {

                        $profileImage =
                            $imageResult['path'];
                    }


                    // -------------------------------------------------
                    // INSERT
                    // -------------------------------------------------

                    $stmt =
                        $conn->prepare(
                            "INSERT INTO feedback
                            (
                                user_id,
                                name,
                                email,
                                profile_image,
                                message,
                                rating
                            )
                            VALUES (?, ?, ?, ?, ?, ?)"
                        );


                    $stmt->bind_param(
                        "issssi",
                        $currentUserId,
                        $name,
                        $email,
                        $profileImage,
                        $message,
                        $rating
                    );


                    if ($stmt->execute()) {

                        $success =
                            "Thank you! Your feedback has been submitted successfully.";

                    } else {

                        /*
                         * If database insert failed and a local
                         * image was uploaded, remove it.
                         */

                        if (
                            $imageResult['path'] !== null
                        ) {

                            deleteFeedbackImage(
                                $imageResult['path']
                            );
                        }


                        $error =
                            "Unable to submit your feedback. Please try again.";
                    }
                }
            }
        }
    }


    // =====================================================
    // UPDATE FEEDBACK
    // =====================================================

    elseif ($action === "update") {


        if (!$isLoggedIn) {

            $error =
                "Please login before editing feedback.";

        } else {


            $feedbackId =
                (int)(
                    $_POST['feedback_id'] ?? 0
                );


            $name =
                trim($_POST['name'] ?? "");


            $email =
                trim($_POST['email'] ?? "");


            $message =
                trim($_POST['message'] ?? "");


            $rating =
                (int)(
                    $_POST['rating'] ?? 0
                );


            if ($feedbackId <= 0) {

                $error =
                    "Invalid feedback.";

            } elseif ($name === "") {

                $error =
                    "Please enter your name.";

            } elseif (
                !filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                $error =
                    "Please enter a valid email address.";

            } elseif ($message === "") {

                $error =
                    "Please write your feedback.";

            } elseif (
                strlen($message) < 5
            ) {

                $error =
                    "Feedback must contain at least 5 characters.";

            } elseif (
                $rating < 1 ||
                $rating > 5
            ) {

                $error =
                    "Please select a rating from 1 to 5.";

            } else {


                // -------------------------------------------------
                // GET CURRENT FEEDBACK
                // -------------------------------------------------

                $check =
                    $conn->prepare(
                        "SELECT profile_image
                         FROM feedback
                         WHERE feedback_id = ?
                         AND user_id = ?
                         LIMIT 1"
                    );


                $check->bind_param(
                    "ii",
                    $feedbackId,
                    $currentUserId
                );


                $check->execute();


                $oldResult =
                    $check->get_result();


                if (
                    $oldResult->num_rows !== 1
                ) {

                    $error =
                        "You cannot edit this feedback.";

                } else {


                    $oldFeedback =
                        $oldResult->fetch_assoc();


                    $oldImage =
                        $oldFeedback['profile_image'] ?? "";


                    // -------------------------------------------------
                    // CHECK NEW IMAGE
                    // -------------------------------------------------

                    $newImageSelected =
                        isset(
                            $_FILES['profile_image']
                        ) &&
                        isset(
                            $_FILES['profile_image']['error']
                        ) &&
                        $_FILES['profile_image']['error']
                        !== UPLOAD_ERR_NO_FILE;


                    if ($newImageSelected) {


                        $imageResult =
                            uploadFeedbackImage(
                                $_FILES['profile_image'],
                                $feedbackImageDirectory,
                                $feedbackImageWebPath
                            );


                        if (
                            !$imageResult['success']
                        ) {

                            $error =
                                $imageResult['error'];

                        } else {


                            $newProfileImage =
                                $imageResult['path'];


                            // -------------------------------------------------
                            // UPDATE WITH NEW IMAGE
                            // -------------------------------------------------

                            $stmt =
                                $conn->prepare(
                                    "UPDATE feedback
                                     SET
                                        name = ?,
                                        email = ?,
                                        message = ?,
                                        rating = ?,
                                        profile_image = ?
                                     WHERE feedback_id = ?
                                     AND user_id = ?"
                                );


                            $stmt->bind_param(
                                "sssissi",
                                $name,
                                $email,
                                $message,
                                $rating,
                                $newProfileImage,
                                $feedbackId,
                                $currentUserId
                            );


                            if (
                                $stmt->execute()
                            ) {


                                /*
                                 * Delete old local image
                                 * after successful update.
                                 */

                                deleteFeedbackImage(
                                    $oldImage
                                );


                                $success =
                                    "Your feedback has been updated successfully.";

                            } else {


                                /*
                                 * Remove newly uploaded image
                                 * if update failed.
                                 */

                                deleteFeedbackImage(
                                    $newProfileImage
                                );


                                $error =
                                    "Unable to update your feedback. Please try again.";
                            }
                        }

                    } else {


                        // -------------------------------------------------
                        // UPDATE WITHOUT CHANGING IMAGE
                        // -------------------------------------------------

                        $stmt =
                            $conn->prepare(
                                "UPDATE feedback
                                 SET
                                    name = ?,
                                    email = ?,
                                    message = ?,
                                    rating = ?
                                 WHERE feedback_id = ?
                                 AND user_id = ?"
                            );


                        $stmt->bind_param(
                            "sssiii",
                            $name,
                            $email,
                            $message,
                            $rating,
                            $feedbackId,
                            $currentUserId
                        );


                        if (
                            $stmt->execute()
                        ) {

                            $success =
                                "Your feedback has been updated successfully.";

                        } else {

                            $error =
                                "Unable to update your feedback. Please try again.";
                        }
                    }
                }
            }
        }
    }


    // =====================================================
    // DELETE FEEDBACK
    // =====================================================

    elseif ($action === "delete") {


        if (!$isLoggedIn) {

            $error =
                "Please login before deleting feedback.";

        } else {


            $feedbackId =
                (int)(
                    $_POST['feedback_id'] ?? 0
                );


            if ($feedbackId <= 0) {

                $error =
                    "Invalid feedback.";

            } else {


                // Get image before deleting

                $imageStmt =
                    $conn->prepare(
                        "SELECT profile_image
                         FROM feedback
                         WHERE feedback_id = ?
                         AND user_id = ?
                         LIMIT 1"
                    );


                $imageStmt->bind_param(
                    "ii",
                    $feedbackId,
                    $currentUserId
                );


                $imageStmt->execute();


                $imageResult =
                    $imageStmt->get_result();


                if (
                    $imageResult->num_rows === 0
                ) {

                    $error =
                        "You cannot delete this feedback.";

                } else {


                    $imageRow =
                        $imageResult->fetch_assoc();


                    $imageToDelete =
                        $imageRow['profile_image'] ?? "";


                    $stmt =
                        $conn->prepare(
                            "DELETE FROM feedback
                             WHERE feedback_id = ?
                             AND user_id = ?"
                        );


                    $stmt->bind_param(
                        "ii",
                        $feedbackId,
                        $currentUserId
                    );


                    if (
                        $stmt->execute() &&
                        $stmt->affected_rows > 0
                    ) {


                        /*
                         * Delete local profile image.
                         */

                        deleteFeedbackImage(
                            $imageToDelete
                        );


                        $success =
                            "Your feedback has been deleted successfully.";

                    } else {

                        $error =
                            "Unable to delete your feedback.";
                    }
                }
            }
        }
    }
}


// =========================================================
// GET ALL FEEDBACK
// =========================================================

$feedbackList = [];


$result =
    $conn->query(
        "SELECT
            feedback_id,
            user_id,
            name,
            email,
            profile_image,
            message,
            rating,
            created_at,
            updated_at
         FROM feedback
         ORDER BY created_at DESC"
    );


if ($result) {

    while (
        $row =
        $result->fetch_assoc()
    ) {

        $feedbackList[] =
            $row;
    }
}


// =========================================================
// CALCULATE RATING
// =========================================================

$totalFeedback =
    count($feedbackList);


$averageRating = 0;


if ($totalFeedback > 0) {


    $ratingTotal = 0;


    foreach (
        $feedbackList
        as $feedback
    ) {

        $ratingTotal +=
            (int)$feedback['rating'];
    }


    $averageRating =
        round(
            $ratingTotal /
            $totalFeedback,
            1
        );
}


require_once "includes/header.php";

?>

<style>

/* =========================================================
   FEEDBACK PAGE
   ========================================================= */

.feedback-page {

    padding: 70px 0 90px;

    background:
        radial-gradient(
            circle at top left,
            rgba(32, 164, 134, 0.08),
            transparent 35%
        ),
        var(--mq-body-bg);

    min-height: 100vh;

}


/* =========================================================
                         HERO
   ========================================================= */

.feedback-hero {

    text-align: center;

    max-width: 800px;

    margin: 0 auto 45px;

}


.feedback-badge {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 8px 18px;

    border-radius: 50px;

    background: rgba(32, 164, 134, 0.12);

    color: var(--mq-primary-dark);

    font-size: 13px;

    font-weight: 800;

    letter-spacing: 1px;

    animation: badgeFloat 2.5s ease-in-out infinite;

}


.feedback-hero h1 {

    margin: 20px 0 12px;

    color: var(--mq-text);

    font-size: clamp(36px, 5vw, 58px);

    font-weight: 900;

    line-height: 1.05;

}


.feedback-hero h1 span {

    color: var(--mq-primary);

}


.feedback-hero p {

    color: var(--mq-text-secondary);

    font-size: 17px;

    line-height: 1.8;

    margin: 0;

}


@keyframes badgeFloat {

    0%,
    100% {

        transform: translateY(0);

    }

    50% {

        transform: translateY(-5px);

    }

}


/* =========================================================
                         RATING SUMMARY
   ========================================================= */

.feedback-summary {

    width: min(100% - 30px, 850px);

    margin: 0 auto 55px;

    padding: 25px;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 35px;

    border: 1px solid var(--mq-border);

    border-radius: 25px;

    background: var(--mq-card-bg);

    box-shadow: var(--mq-shadow);

}


.rating-number {

    font-size: 42px;

    font-weight: 900;

    color: var(--mq-text);

}


.rating-stars {

    display: flex;

    gap: 3px;

}


.rating-stars i {

    color: #ffc107;

    font-size: 21px;

    animation: starPulse 1.8s infinite;

}


.rating-stars i:nth-child(2) {

    animation-delay: .1s;

}


.rating-stars i:nth-child(3) {

    animation-delay: .2s;

}


.rating-stars i:nth-child(4) {

    animation-delay: .3s;

}


.rating-stars i:nth-child(5) {

    animation-delay: .4s;

}


@keyframes starPulse {

    0%,
    100% {

        transform: scale(1);

    }

    50% {

        transform: scale(1.18);

    }

}


.rating-summary-text {

    color: var(--mq-text-secondary);

    font-size: 14px;

}


/* =========================================================
   ADD FEEDBACK BUTTON
   ========================================================= */

.feedback-action {

    text-align: center;

    margin-bottom: 45px;

}


.add-feedback-button {

    border: none;

    border-radius: 14px;

    padding: 14px 25px;

    background: linear-gradient(
        135deg,
        var(--mq-primary),
        var(--mq-secondary)
    );

    color: #fff;

    font-size: 15px;

    font-weight: 800;

    cursor: pointer;

    box-shadow:
        0 12px 25px rgba(32, 164, 134, .22);

    transition: all .3s ease;

}


.add-feedback-button:hover {

    transform: translateY(-4px);

    box-shadow:
        0 18px 32px rgba(32, 164, 134, .3);

}


/* =========================================================
                        FEEDBACK FORM
   ========================================================= */

.feedback-form-wrapper {

    width: min(100% - 30px, 850px);

    margin: 0 auto 60px;

    padding: 32px;

    background: var(--mq-card-bg);

    border: 1px solid var(--mq-border);

    border-radius: 28px;

    box-shadow: var(--mq-shadow);

    display: none;

    animation: formOpen .45s ease;

}


.feedback-form-wrapper.show {

    display: block;

}


@keyframes formOpen {

    from {

        opacity: 0;

        transform:
            translateY(20px)
            scale(.98);

    }

    to {

        opacity: 1;

        transform:
            translateY(0)
            scale(1);

    }

}


.feedback-form-title {

    margin-bottom: 25px;

}


.feedback-form-title h2 {

    margin: 0 0 6px;

    color: var(--mq-text);

    font-size: 25px;

    font-weight: 800;

}


.feedback-form-title p {

    margin: 0;

    color: var(--mq-text-secondary);

}


.feedback-form-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 20px;

}


.feedback-field {

    margin-bottom: 18px;

}


.feedback-field.full {

    grid-column: 1 / -1;

}


.feedback-field label {

    display: block;

    margin-bottom: 8px;

    color: var(--mq-text);

    font-size: 14px;

    font-weight: 700;

}


.feedback-field input,
.feedback-field textarea {

    width: 100%;

    border: 1px solid var(--mq-border);

    border-radius: 13px;

    padding: 13px 15px;

    background: var(--mq-section-bg);

    color: var(--mq-text);

    outline: none;

    transition: all .3s ease;

    font-family: inherit;

}


.feedback-field textarea {

    min-height: 140px;

    resize: vertical;

}


.feedback-field input:focus,
.feedback-field textarea:focus {

    border-color: var(--mq-primary);

    box-shadow:
        0 0 0 4px rgba(32, 164, 134, .1);

}


/* =========================================================
                       IMAGE UPLOAD
   ========================================================= */

.feedback-image-upload {

    display: flex;

    align-items: center;

    gap: 18px;

    padding: 15px;

    border: 1px dashed var(--mq-border);

    border-radius: 15px;

    background: var(--mq-section-bg);

}


.feedback-image-preview {

    width: 70px;

    height: 70px;

    border-radius: 50%;

    object-fit: cover;

    border: 3px solid rgba(32, 164, 134, .18);

    display: none;

}


.feedback-image-placeholder {

    width: 70px;

    height: 70px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: rgba(32, 164, 134, .1);

    color: var(--mq-primary);

    font-size: 27px;

    flex-shrink: 0;

}


.feedback-image-controls {

    flex: 1;

}


.feedback-image-controls input[type="file"] {

    padding: 10px;

    cursor: pointer;

}


.feedback-image-help {

    margin-top: 6px;

    color: var(--mq-text-secondary);

    font-size: 12px;

}


/* =========================================================
                    STAR SELECTOR
   ========================================================= */

.star-selector {

    display: flex;

    flex-direction: row-reverse;

    justify-content: flex-end;

    gap: 5px;

}


.star-selector input {

    display: none;

}


.star-selector label {

    margin: 0;

    color: #c9c9c9;

    font-size: 32px;

    cursor: pointer;

    transition: all .2s ease;

}


.star-selector label:hover,
.star-selector label:hover ~ label,
.star-selector input:checked ~ label {

    color: #ffc107;

    transform: scale(1.12);

    text-shadow:
        0 0 10px rgba(255, 193, 7, .35);

}


.feedback-submit-row {

    display: flex;

    gap: 12px;

    margin-top: 5px;

}


.submit-feedback {

    border: none;

    border-radius: 13px;

    padding: 13px 22px;

    background: var(--mq-primary);

    color: #fff;

    font-weight: 800;

    cursor: pointer;

    transition: all .3s ease;

}


.submit-feedback:hover {

    background: var(--mq-primary-dark);

    transform: translateY(-2px);

}


.cancel-feedback {

    border: 1px solid var(--mq-border);

    border-radius: 13px;

    padding: 13px 22px;

    background: transparent;

    color: var(--mq-text);

    font-weight: 700;

    cursor: pointer;

}


/* =========================================================
                        ALERTS
   ========================================================= */

.feedback-alert {

    width: min(100% - 30px, 850px);

    margin: 0 auto 25px;

    padding: 14px 18px;

    border-radius: 14px;

    font-weight: 700;

    animation: alertIn .4s ease;

}


.feedback-success {

    background: rgba(32, 164, 134, .12);

    border: 1px solid rgba(32, 164, 134, .3);

    color: var(--mq-primary-dark);

}


.feedback-error {

    background: rgba(220, 53, 69, .1);

    border: 1px solid rgba(220, 53, 69, .25);

    color: #c62828;

}


@keyframes alertIn {

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
                       FEEDBACK GRID
   ========================================================= */

.feedback-container {

    width: min(100% - 30px, 1250px);

    margin: 0 auto;

}


.feedback-grid {

    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 25px;

}


.feedback-card {

    position: relative;

    padding: 27px;

    border-radius: 25px;

    background: var(--mq-card-bg);

    border: 1px solid var(--mq-border);

    box-shadow: var(--mq-shadow);

    overflow: hidden;

    transition:
        transform .35s ease,
        box-shadow .35s ease,
        border-color .35s ease;

    animation: cardAppear .6s ease both;

}


.feedback-card::before {

    content: "";

    position: absolute;

    top: 0;

    left: 0;

    width: 100%;

    height: 4px;

    background: linear-gradient(
        90deg,
        var(--mq-primary),
        var(--mq-secondary)
    );

    transform: scaleX(0);

    transform-origin: left;

    transition: transform .4s ease;

}


.feedback-card:hover {

    transform: translateY(-9px);

    box-shadow:
        0 22px 50px rgba(0, 0, 0, .12);

    border-color:
        rgba(32, 164, 134, .3);

}


.feedback-card:hover::before {

    transform: scaleX(1);

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


/* =========================================================
                     CUSTOMER
   ========================================================= */

.feedback-user {

    display: flex;

    align-items: center;

    gap: 14px;

    margin-bottom: 17px;

}


.feedback-avatar {

    width: 58px;

    height: 58px;

    flex: 0 0 58px;

    border-radius: 50%;

    object-fit: cover;

    border: 3px solid rgba(32, 164, 134, .18);

    transition: all .35s ease;

}


.feedback-card:hover .feedback-avatar {

    transform:
        scale(1.08)
        rotate(3deg);

    border-color:
        var(--mq-primary);

}


.feedback-user-info {

    min-width: 0;

}


.feedback-user-info h3 {

    margin: 0;

    color: var(--mq-text);

    font-size: 17px;

    font-weight: 800;

}


.feedback-user-info p {

    margin: 3px 0 0;

    color: var(--mq-text-secondary);

    font-size: 12px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

}


/* =========================================================
                       STARS
   ========================================================= */

.feedback-stars {

    display: flex;

    gap: 3px;

    margin-bottom: 15px;

}


.feedback-stars i {

    color: #ffc107;

    font-size: 17px;

}


.feedback-stars i.empty {

    color: #d3d3d3;

}


.feedback-card:hover
.feedback-stars i:not(.empty) {

    animation:
        starBounce .45s ease;

}


@keyframes starBounce {

    0% {

        transform: scale(1);

    }

    50% {

        transform: scale(1.25);

    }

    100% {

        transform: scale(1);

    }

}


/* =========================================================
                           MESSAGE
   ========================================================= */

.feedback-message {

    color: var(--mq-text-secondary);

    font-size: 14px;

    line-height: 1.75;

    min-height: 95px;

    margin-bottom: 20px;

}


.feedback-message::before {

    content: "“";

    font-size: 35px;

    font-weight: 900;

    color: var(--mq-primary);

    vertical-align: -12px;

    margin-right: 4px;

}


.feedback-date {

    display: flex;

    align-items: center;

    gap: 6px;

    color: var(--mq-text-secondary);

    font-size: 12px;

}


/* =========================================================
                        MANAGE BUTTONS
   ========================================================= */

.feedback-manage {

    display: flex;

    gap: 8px;

    margin-top: 18px;

    padding-top: 16px;

    border-top: 1px solid var(--mq-border);

}


.feedback-edit,
.feedback-delete {

    border-radius: 10px;

    padding: 8px 13px;

    font-size: 12px;

    font-weight: 800;

    text-decoration: none;

    transition: all .25s ease;

}


.feedback-edit {

    background:
        rgba(32, 164, 134, .12);

    color:
        var(--mq-primary-dark);

}


.feedback-delete {

    border: none;

    background:
        rgba(220, 53, 69, .1);

    color: #d32f2f;

    cursor: pointer;

}


.feedback-edit:hover,
.feedback-delete:hover {

    transform: translateY(-2px);

}


/* =========================================================
                          EMPTY
   ========================================================= */

.feedback-empty {

    text-align: center;

    padding: 70px 20px;

    color: var(--mq-text-secondary);

}


.feedback-empty i {

    font-size: 55px;

    color: var(--mq-primary);

    margin-bottom: 15px;

}


/* =========================================================
                      LOGIN NOTICE
   ========================================================= */

.login-notice {

    width: min(100% - 30px, 850px);

    margin: 0 auto 45px;

    padding: 17px 20px;

    border-radius: 15px;

    background:
        rgba(32, 164, 134, .08);

    border:
        1px solid rgba(32, 164, 134, .18);

    color:
        var(--mq-text-secondary);

    text-align: center;

}


.login-notice a {

    color:
        var(--mq-primary);

    font-weight: 800;

    text-decoration: none;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1000px) {

    .feedback-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

    }

}


@media (max-width: 700px) {

    .feedback-page {

        padding: 50px 0 70px;

    }


    .feedback-summary {

        flex-direction: column;

        gap: 8px;

        text-align: center;

    }


    .feedback-form-wrapper {

        padding: 22px;

    }


    .feedback-form-grid {

        grid-template-columns: 1fr;

        gap: 0;

    }


    .feedback-field.full {

        grid-column: auto;

    }


    .feedback-grid {

        grid-template-columns: 1fr;

    }


    .feedback-card {

        padding: 23px;

    }


    .feedback-submit-row {

        flex-direction: column;

    }


    .submit-feedback,
    .cancel-feedback {

        width: 100%;

    }


    .feedback-image-upload {

        align-items: flex-start;

    }

}

</style>


<!-- =========================================================
                       FEEDBACK PAGE
     ========================================================= -->

<main class="feedback-page">


    <!-- =====================================================
                            HERO
         ===================================================== -->

    <section class="feedback-hero">


        <div class="feedback-badge">

            <i class="bi bi-chat-heart-fill"></i>

            CUSTOMER FEEDBACK

        </div>


        <h1>

            What Our Customers

            <span>Say</span>

        </h1>


        <p>

            Your experience matters to us.
            Share your MediQuick experience and help us
            improve our service for everyone.

        </p>

    </section>


    <!-- =====================================================
                             ALERTS
         ===================================================== -->

    <?php if ($success !== ""): ?>

        <div class="feedback-alert feedback-success">

            <i class="bi bi-check-circle-fill"></i>

            <?= htmlspecialchars($success) ?>

        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="feedback-alert feedback-error">

            <i class="bi bi-exclamation-circle-fill"></i>

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- =====================================================
                    RATING SUMMARY
         ===================================================== -->

    <div class="feedback-summary">


        <div>

            <div class="rating-number">

                <?= number_format(
                    $averageRating,
                    1
                ) ?>

            </div>

        </div>


        <div>

            <div class="rating-stars">

                <?php

                $roundedRating =
                    round($averageRating);

                for (
                    $i = 1;
                    $i <= 5;
                    $i++
                ):

                ?>

                    <?php if (
                        $i <= $roundedRating
                    ): ?>

                        <i class="bi bi-star-fill"></i>

                    <?php else: ?>

                        <i class="bi bi-star"></i>

                    <?php endif; ?>

                <?php endfor; ?>

            </div>


            <div class="rating-summary-text">

                Based on

                <?= $totalFeedback ?>

                customer feedback
                <?= $totalFeedback === 1
                    ? ''
                    : 's'
                ?>

            </div>

        </div>

    </div>


    <!-- =====================================================
                      ADD FEEDBACK
         ===================================================== -->

    <?php if ($isLoggedIn): ?>


        <div class="feedback-action">


            <button
                type="button"
                class="add-feedback-button"
                id="openFeedbackForm"
            >

                <i class="bi bi-pencil-square"></i>

                Share Your Feedback

            </button>


        </div>


    <?php else: ?>


        <div class="login-notice">

            <i class="bi bi-person-circle"></i>

            Please

            <a
                href="<?= $base_url ?>/login.php"
            >

                login

            </a>

            to submit, edit or manage your feedback.

        </div>


    <?php endif; ?>


    <!-- =====================================================
                         FEEDBACK FORM
         ===================================================== -->

    <?php if ($isLoggedIn): ?>


        <section
            class="feedback-form-wrapper
            <?= $editFeedback ? 'show' : '' ?>"
            id="feedbackFormWrapper"
        >


            <div class="feedback-form-title">


                <h2>

                    <?= $editFeedback
                        ? 'Edit Your Feedback'
                        : 'Share Your Experience'
                    ?>

                </h2>


                <p>

                    Tell us about your MediQuick experience.

                </p>


            </div>


            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <?php if ($editFeedback): ?>


                    <input
                        type="hidden"
                        name="action"
                        value="update"
                    >


                    <input
                        type="hidden"
                        name="feedback_id"
                        value="<?= (int)$editFeedback['feedback_id'] ?>"
                    >


                <?php else: ?>


                    <input
                        type="hidden"
                        name="action"
                        value="add"
                    >


                <?php endif; ?>


                <div class="feedback-form-grid">


                    <!-- =================================================
                                            NAME
                         ================================================= -->

                    <div class="feedback-field">


                        <label for="feedbackName">

                            Full Name *

                        </label>


                        <input
                            type="text"
                            id="feedbackName"
                            name="name"
                            maxlength="120"
                            placeholder="Enter your name"
                            value="<?= $editFeedback
                                ? htmlspecialchars(
                                    $editFeedback['name']
                                )
                                : ''
                            ?>"
                            required
                        >


                    </div>


                    <!-- =================================================
                         EMAIL
                         ================================================= -->

                    <div class="feedback-field">


                        <label for="feedbackEmail">

                            Email Address *

                        </label>


                        <input
                            type="email"
                            id="feedbackEmail"
                            name="email"
                            maxlength="150"
                            placeholder="Enter your email"
                            value="<?= $editFeedback
                                ? htmlspecialchars(
                                    $editFeedback['email']
                                )
                                : ''
                            ?>"
                            required
                        >


                    </div>


                    <!-- =================================================
                                       PROFILE IMAGE
                         ================================================= -->

                    <div class="feedback-field full">


                        <label>

                            Profile Image

                        </label>


                        <div class="feedback-image-upload">


                            <?php

                            $currentImage = "";

                            if ($editFeedback) {

                                $currentImage =
                                    trim(
                                        $editFeedback[
                                            'profile_image'
                                        ] ?? ""
                                    );
                            }

                            ?>


                            <?php if (
                                $currentImage !== ""
                            ): ?>


                                <img
                                    src="<?= htmlspecialchars(
                                        $currentImage
                                    ) ?>"
                                    alt="Current profile image"
                                    class="feedback-image-preview"
                                    id="feedbackImagePreview"
                                    style="display:block;"
                                >


                                <div
                                    class="feedback-image-placeholder"
                                    id="feedbackImagePlaceholder"
                                    style="display:none;"
                                >

                                    <i class="bi bi-person-fill"></i>

                                </div>


                            <?php else: ?>


                                <img
                                    src=""
                                    alt="Profile preview"
                                    class="feedback-image-preview"
                                    id="feedbackImagePreview"
                                >


                                <div
                                    class="feedback-image-placeholder"
                                    id="feedbackImagePlaceholder"
                                >

                                    <i class="bi bi-person-fill"></i>

                                </div>


                            <?php endif; ?>


                            <div
                                class="feedback-image-controls"
                            >


                                <input
                                    type="file"
                                    name="profile_image"
                                    id="feedbackImage"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                >


                                <div
                                    class="feedback-image-help"
                                >

                                    JPG, PNG or WEBP.
                                    Maximum 5MB.

                                    <?php if (
                                        $editFeedback
                                    ): ?>

                                        Choose a new image
                                        only if you want
                                        to replace the
                                        current image.

                                    <?php else: ?>

                                        You can upload your
                                        own profile image.
                                        If you do not upload
                                        one, a random avatar
                                        will be used.

                                    <?php endif; ?>

                                </div>


                            </div>


                        </div>


                    </div>


                    <!-- =================================================
                                             RATING
                         ================================================= -->

                    <div class="feedback-field full">


                        <label>

                            Your Rating *

                        </label>


                        <div class="star-selector">


                            <?php

                            $selectedRating =
                                $editFeedback
                                ? (int)$editFeedback['rating']
                                : 0;

                            ?>


                            <input
                                type="radio"
                                id="star5"
                                name="rating"
                                value="5"
                                <?= $selectedRating === 5
                                    ? 'checked'
                                    : ''
                                ?>
                                required
                            >


                            <label
                                for="star5"
                                title="5 Stars"
                            >

                                ★

                            </label>


                            <input
                                type="radio"
                                id="star4"
                                name="rating"
                                value="4"
                                <?= $selectedRating === 4
                                    ? 'checked'
                                    : ''
                                ?>
                            >


                            <label
                                for="star4"
                                title="4 Stars"
                            >

                                ★

                            </label>


                            <input
                                type="radio"
                                id="star3"
                                name="rating"
                                value="3"
                                <?= $selectedRating === 3
                                    ? 'checked'
                                    : ''
                                ?>
                            >


                            <label
                                for="star3"
                                title="3 Stars"
                            >

                                ★

                            </label>


                            <input
                                type="radio"
                                id="star2"
                                name="rating"
                                value="2"
                                <?= $selectedRating === 2
                                    ? 'checked'
                                    : ''
                                ?>
                            >


                            <label
                                for="star2"
                                title="2 Stars"
                            >

                                ★

                            </label>


                            <input
                                type="radio"
                                id="star1"
                                name="rating"
                                value="1"
                                <?= $selectedRating === 1
                                    ? 'checked'
                                    : ''
                                ?>
                            >


                            <label
                                for="star1"
                                title="1 Star"
                            >

                                ★

                            </label>


                        </div>


                    </div>


                    <!-- =================================================
                                             MESSAGE
                         ================================================= -->

                    <div class="feedback-field full">


                        <label for="feedbackMessage">

                            Your Feedback *

                        </label>


                        <textarea
                            id="feedbackMessage"
                            name="message"
                            maxlength="2000"
                            placeholder="Write your experience with MediQuick..."
                            required
                        ><?= $editFeedback
                            ? htmlspecialchars(
                                $editFeedback['message']
                            )
                            : ''
                        ?></textarea>


                    </div>


                </div>


                <!-- =====================================================
                                             BUTTONS
                     ===================================================== -->

                <div class="feedback-submit-row">


                    <button
                        type="submit"
                        class="submit-feedback"
                    >


                        <i class="bi bi-send-fill"></i>


                        <?= $editFeedback
                            ? 'Update Feedback'
                            : 'Submit Feedback'
                        ?>


                    </button>


                    <button
                        type="button"
                        class="cancel-feedback"
                        id="cancelFeedbackForm"
                    >

                        Cancel

                    </button>


                </div>


            </form>


        </section>


    <?php endif; ?>


    <!-- =====================================================
                              ALL FEEDBACK
         ===================================================== -->

    <section class="feedback-container">


        <?php if (
            $totalFeedback > 0
        ): ?>


            <div class="feedback-grid">


                <?php foreach (
                    $feedbackList
                    as $index => $feedback
                ): ?>


                    <article
                        class="feedback-card"
                        style="animation-delay:
                        <?= min(
                            $index * 0.08,
                            0.5
                        ) ?>s;"
                    >


                        <!-- =============================================
                                          CUSTOMER
                             ============================================= -->

                        <div class="feedback-user">


                            <?php

                            $avatar =
                                trim(
                                    $feedback[
                                        'profile_image'
                                    ] ?? ""
                                );


                            if (
                                $avatar === ""
                            ) {

                                $avatar =
                                    "https://i.pravatar.cc/150?img=" .
                                    (
                                        (
                                            $feedback[
                                                'feedback_id'
                                            ] % 70
                                        ) + 1
                                    );
                            }

                            ?>


                            <img
                                src="<?= htmlspecialchars(
                                    $avatar
                                ) ?>"
                                alt="<?= htmlspecialchars(
                                    $feedback['name']
                                ) ?>"
                                class="feedback-avatar"
                                loading="lazy"
                                onerror="this.src='https://i.pravatar.cc/150?img=1';"
                            >


                            <div
                                class="feedback-user-info"
                            >


                                <h3>

                                    <?= htmlspecialchars(
                                        $feedback['name']
                                    ) ?>

                                </h3>


                                <p>

                                    <?= htmlspecialchars(
                                        $feedback['email']
                                    ) ?>

                                </p>


                            </div>


                        </div>


                        <!-- =============================================
                                             STARS
                             ============================================= -->

                        <div class="feedback-stars">


                            <?php

                            $cardRating =
                                (int)$feedback['rating'];


                            for (
                                $star = 1;
                                $star <= 5;
                                $star++
                            ):

                            ?>


                                <?php if (
                                    $star <= $cardRating
                                ): ?>


                                    <i
                                        class="bi bi-star-fill"
                                    ></i>


                                <?php else: ?>


                                    <i
                                        class="bi bi-star-fill empty"
                                    ></i>


                                <?php endif; ?>


                            <?php endfor; ?>


                        </div>


                        <!-- =============================================
                                              MESSAGE
                             ============================================= -->

                        <div
                            class="feedback-message"
                        >

                            <?= nl2br(
                                htmlspecialchars(
                                    $feedback['message']
                                )
                            ) ?>


                        </div>


                        <!-- =============================================
                                                   DATE
                             ============================================= -->

                        <div
                            class="feedback-date"
                        >


                            <i
                                class="bi bi-calendar3"
                            ></i>


                            <?= date(
                                "F d, Y",
                                strtotime(
                                    $feedback['created_at']
                                )
                            ) ?>


                        </div>


                        <!-- =============================================
                             OWNER MANAGEMENT
                             ============================================= -->

                        <?php

                        $isOwner =
                            $isLoggedIn &&
                            (int)$feedback['user_id']
                            === $currentUserId;

                        ?>


                        <?php if ($isOwner): ?>


                            <div
                                class="feedback-manage"
                            >


                                <a
                                    href="<?= $base_url ?>/feedback.php?edit=<?= (int)$feedback['feedback_id'] ?>"
                                    class="feedback-edit"
                                >


                                    <i
                                        class="bi bi-pencil-fill"
                                    ></i>


                                    Edit


                                </a>


                                <form
                                    method="POST"
                                    onsubmit="return confirmDeleteFeedback();"
                                >


                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete"
                                    >


                                    <input
                                        type="hidden"
                                        name="feedback_id"
                                        value="<?= (int)$feedback['feedback_id'] ?>"
                                    >


                                    <button
                                        type="submit"
                                        class="feedback-delete"
                                    >


                                        <i
                                            class="bi bi-trash-fill"
                                        ></i>


                                        Delete


                                    </button>


                                </form>


                            </div>


                        <?php endif; ?>


                    </article>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <div
                class="feedback-empty"
            >


                <i
                    class="bi bi-chat-square-heart"
                ></i>


                <h2>

                    No feedback yet

                </h2>


                <p>

                    Be the first customer to share your experience.

                </p>


            </div>


        <?php endif; ?>


    </section>


</main>


<!-- =========================================================
     JAVASCRIPT
     ========================================================= -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        // =====================================================
        // OPEN FORM
        // =====================================================

        const openButton =
            document.getElementById(
                "openFeedbackForm"
            );


        const formWrapper =
            document.getElementById(
                "feedbackFormWrapper"
            );


        const cancelButton =
            document.getElementById(
                "cancelFeedbackForm"
            );


        if (
            openButton &&
            formWrapper
        ) {


            openButton.addEventListener(
                "click",
                function () {


                    formWrapper.classList.add(
                        "show"
                    );


                    formWrapper.scrollIntoView(
                        {
                            behavior: "smooth",
                            block: "center"
                        }
                    );

                }
            );

        }


        // =====================================================
        // CANCEL FORM
        // =====================================================

        if (
            cancelButton &&
            formWrapper
        ) {


            cancelButton.addEventListener(
                "click",
                function () {


                    formWrapper.classList.remove(
                        "show"
                    );


                    window.history.replaceState(
                        {},
                        document.title,
                        "feedback.php"
                    );

                }
            );

        }


        // =====================================================
        // IMAGE PREVIEW
        // =====================================================

        const imageInput =
            document.getElementById(
                "feedbackImage"
            );


        const imagePreview =
            document.getElementById(
                "feedbackImagePreview"
            );


        const imagePlaceholder =
            document.getElementById(
                "feedbackImagePlaceholder"
            );


        if (
            imageInput &&
            imagePreview
        ) {


            imageInput.addEventListener(
                "change",
                function () {


                    const file =
                        this.files[0];


                    if (!file) {

                        return;
                    }


                    // Check file size

                    if (
                        file.size >
                        5 * 1024 * 1024
                    ) {


                        alert(
                            "Profile image must be smaller than 5MB."
                        );


                        this.value = "";

                        return;

                    }


                    // Check image type

                    const allowedTypes = [

                        "image/jpeg",

                        "image/png",

                        "image/webp"

                    ];


                    if (
                        !allowedTypes.includes(
                            file.type
                        )
                    ) {


                        alert(
                            "Only JPG, PNG and WEBP images are allowed."
                        );


                        this.value = "";

                        return;

                    }


                    const reader =
                        new FileReader();


                    reader.onload =
                        function (event) {


                            imagePreview.src =
                                event.target.result;


                            imagePreview.style.display =
                                "block";


                            if (
                                imagePlaceholder
                            ) {

                                imagePlaceholder.style.display =
                                    "none";
                            }

                        };


                    reader.readAsDataURL(
                        file
                    );

                }
            );

        }


        // =====================================================
        // STAR ANIMATION
        // =====================================================

        const starInputs =
            document.querySelectorAll(
                ".star-selector input"
            );


        starInputs.forEach(
            function (input) {


                input.addEventListener(
                    "change",
                    function () {


                        const labels =
                            document.querySelectorAll(
                                ".star-selector label"
                            );


                        labels.forEach(
                            function (label) {


                                label.style.animation =
                                    "none";


                                void label.offsetWidth;


                                label.style.animation =
                                    "starBounce .35s ease";

                            }
                        );

                    }
                );

            }
        );


        // =====================================================
        // FORM LOADING
        // =====================================================

        const feedbackForms =
            document.querySelectorAll(
                ".feedback-form-wrapper form"
            );


        feedbackForms.forEach(
            function (form) {


                form.addEventListener(
                    "submit",
                    function () {


                        const button =
                            form.querySelector(
                                ".submit-feedback"
                            );


                        if (button) {


                            button.disabled =
                                true;


                            button.innerHTML =
                                '<i class="bi bi-hourglass-split"></i> Processing...';

                        }

                    }
                );

            }
        );

    }
);


// =========================================================
// DELETE CONFIRMATION
// =========================================================

function confirmDeleteFeedback()
{

    return confirm(
        "Are you sure you want to delete your feedback?"
    );

}

</script>


<?php

require_once "includes/footer.php";

?>