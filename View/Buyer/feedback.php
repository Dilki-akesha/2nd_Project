<?php
$orderId = $orderId ?? 'ORD-2026-001';
$farmerName = $farmerName ?? "Sunil's Organic Farm";
$reviewMessage = $reviewMessage ?? '';
$complaintMessage = $complaintMessage ?? '';
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Harvestly - Order Feedback</title>

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;600;700&family=Manrope:wght@400;600;700&display=swap"
          rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
          rel="stylesheet">

    <link rel="stylesheet"
          href="/Harvestly/css/Buyer/feedback.css">

</head>

<body>


<!-- =========================
     NAVBAR
========================= -->

<header class="navbar">

    <div class="navbar-container">

        <a href="/Harvestly/Controller/Buyer/DashboardController.php" class="logo">

            <img src="/Harvestly/assets/harvestly-logo.jpeg" alt="Harvestly" style="height:34px;width:auto;display:block;object-fit:contain;">

        </a>


        <nav class="desktop-nav">

            <a href="/Harvestly/Controller/Buyer/DashboardController.php">Home</a>

            <a href="/Harvestly/Controller/Buyer/DashboardController.php">Products</a>

            <a href="/Harvestly/Controller/Buyer/DashboardController.php">Categories</a>

            <a href="/Harvestly/Controller/Buyer/DashboardController.php">Farmers</a>

            <a href="/Harvestly/Controller/Buyer/DashboardController.php">About</a>

        </nav>


        <div class="nav-actions">

            <button
                type="button"
                class="login-btn">

                Login

            </button>


            <button
                type="button"
                class="register-btn">

                Register

            </button>


            <button
                type="button"
                class="menu-btn"
                id="menuBtn">

                <span class="material-symbols-outlined">
                    menu
                </span>

            </button>

        </div>

    </div>


    <nav
        class="mobile-nav"
        id="mobileNav">

        <a href="/Harvestly/Controller/Buyer/DashboardController.php">Home</a>

        <a href="/Harvestly/Controller/Buyer/ProductController.php">Products</a>

        <a href="/Harvestly/Controller/Buyer/DashboardController.php">Categories</a>

        <a href="/Harvestly/Controller/Buyer/DashboardController.php">Farmers</a>

        <a href="/Harvestly/Controller/Buyer/DashboardController.php">About</a>

    </nav>

</header>



<!-- =========================
     MAIN
========================= -->

<main class="main-container">


    <section class="page-heading">

        <h1>
            Order Feedback
        </h1>

        <p>
            Help us maintain quality by sharing your experience for
            Order #<?= htmlspecialchars($orderId) ?>.
        </p>

    </section>



    <div class="content-grid">


        <!-- =========================
             LEFT - REVIEW
        ========================= -->

        <section class="review-card">

            <h2>
                Rate Your Harvest
            </h2>


            <?php if ($reviewMessage): ?>

                <div class="message success-message">

                    <?= htmlspecialchars($reviewMessage) ?>

                </div>

            <?php endif; ?>


            <form method="POST"
                  action="/Harvestly/Controller/Buyer/FeedbackController.php"
                  id="reviewForm">


                <!-- Farmer Rating -->

                <div class="rating-section">

                    <label>
                        Farmer Rating
                        (<?= htmlspecialchars($farmerName) ?>)
                    </label>


                    <div
                        class="stars"
                        data-rating="farmer">

                        <button
                            type="button"
                            class="star"
                            data-value="1">

                            ★

                        </button>

                        <button
                            type="button"
                            class="star"
                            data-value="2">

                            ★

                        </button>

                        <button
                            type="button"
                            class="star"
                            data-value="3">

                            ★

                        </button>

                        <button
                            type="button"
                            class="star"
                            data-value="4">

                            ★

                        </button>

                        <button
                            type="button"
                            class="star"
                            data-value="5">

                            ★

                        </button>

                    </div>


                    <input
                        type="hidden"
                        name="farmer_rating"
                        id="farmerRating"
                        value="0">


                    <textarea
                        name="quality_comment"
                        placeholder="How was the quality of the produce?"
                        rows="4"></textarea>

                </div>



                <!-- Delivery Rating -->

                <div class="rating-section">

                    <label>
                        Delivery Experience
                    </label>


                    <div
                        class="stars"
                        data-rating="delivery">

                        <button
                            type="button"
                            class="star"
                            data-value="1">

                            ★

                        </button>

                        <button
                            type="button"
                            class="star"
                            data-value="2">

                            ★

                        </button>

                        <button
                            type="button"
                            class="star"
                            data-value="3">

                            ★

                        </button>

                        <button
                            type="button"
                            class="star"
                            data-value="4">

                            ★

                        </button>

                        <button
                            type="button"
                            class="star"
                            data-value="5">

                            ★

                        </button>

                    </div>


                    <input
                        type="hidden"
                        name="delivery_rating"
                        id="deliveryRating"
                        value="0">


                    <textarea
                        name="delivery_comment"
                        placeholder="Any comments on the delivery speed or packaging?"
                        rows="3"></textarea>

                </div>



                <div class="review-submit">

                    <button
                        type="submit"
                        name="submit_review"
                        class="primary-btn">

                        Submit Review

                    </button>

                </div>

            </form>

        </section>



        <!-- =========================
             RIGHT - COMPLAINT
        ========================= -->

        <section class="complaint-card">


            <div class="complaint-heading">

                <span class="material-symbols-outlined">
                    report_problem
                </span>

                <h2>
                    Submit a Complaint
                </h2>

            </div>


            <p class="complaint-description">

                If something went wrong with your order,
                please let us know so we can make it right.

            </p>


            <?php if ($complaintMessage): ?>

                <div class="message complaint-message">

                    <?= htmlspecialchars($complaintMessage) ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                action="/Harvestly/Controller/Buyer/FeedbackController.php"
                enctype="multipart/form-data"
                id="complaintForm">


                <!-- Category -->

                <div class="form-group">

                    <label for="category">
                        Category
                    </label>

                    <select
                        name="category"
                        id="category">

                        <option
                            value=""
                            selected
                            disabled>

                            Select an issue...

                        </option>

                        <option value="quality">
                            Produce Quality
                        </option>

                        <option value="delivery">
                            Delivery Issues
                        </option>

                        <option value="price">
                            Pricing / Billing
                        </option>

                        <option value="other">
                            Other
                        </option>

                    </select>

                </div>



                <!-- Details -->

                <div class="form-group">

                    <label for="details">
                        Details
                    </label>

                    <textarea
                        name="details"
                        id="details"
                        rows="5"
                        placeholder="Please describe the issue in detail..."></textarea>

                </div>



                <!-- Upload -->

                <div class="form-group">

                    <label>
                        Attach Photos (Optional)
                    </label>


                    <label
                        class="upload-box"
                        for="photos">

                        <span class="material-symbols-outlined">
                            add_a_photo
                        </span>

                        <span>
                            Drag and drop or click to upload
                        </span>

                        <small id="fileName">
                            No file selected
                        </small>

                    </label>


                    <input
                        type="file"
                        name="photos[]"
                        id="photos"
                        accept="image/*"
                        multiple>

                </div>



                <button
                    type="submit"
                    name="submit_complaint"
                    class="complaint-btn">

                    File Complaint

                </button>


            </form>

        </section>

    </div>

</main>



<!-- =========================
     FOOTER
========================= -->

<footer class="footer">

    <div class="footer-container">


        <div class="footer-brand">

            <h2>
                Harvestly
            </h2>

            <p>
                © 2026 Harvestly.
                Bridging Sri Lankan Fields to Your Table.
            </p>

        </div>


        <div class="footer-links">

            <a href="/Harvestly/Controller/Buyer/DashboardController.php">
                About Harvestly
            </a>

            <a href="/Harvestly/Controller/Buyer/DashboardController.php">
                Quick Links
            </a>

        </div>


        <div class="footer-links">

            <a href="/Harvestly/Controller/Buyer/DashboardController.php">
                Contact Us
            </a>

            <a href="/Harvestly/Controller/Buyer/DashboardController.php">
                Privacy Policy
            </a>

            <a href="/Harvestly/Controller/Buyer/DashboardController.php">
                Terms of Service
            </a>

        </div>

    </div>

</footer>


<script src="/Harvestly/js/Buyer/feedback.js"></script>

</body>

</html>