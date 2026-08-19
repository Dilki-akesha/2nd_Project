<?php

// This View must receive $orders from OrdersController.php

if (!isset($orders) || !is_array($orders)) {
    header("Location: ../../Controller/Buyer/OrdersController.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Harvestly - My Orders</title>


    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;600;700&family=Manrope:wght@400;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- Material Symbols -->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,0"
        rel="stylesheet"
    >


    <!-- Orders CSS -->
    <link
        rel="stylesheet"
        href="/Harvestly/css/Buyer/orders.css"
    >

</head>


<body>


<!-- =====================================================
     HEADER
===================================================== -->

<header class="top-navbar">

    <div class="navbar-inner">


        <!-- LOGO -->

        <a
            href="/Harvestly/Controller/Buyer/LandingController.php"
            class="brand"
        >

            <img src="/Harvestly/assets/harvestly-logo.jpeg" alt="Harvestly" style="height:34px;width:auto;display:block;object-fit:contain;">

        </a>


        <!-- DESKTOP NAV -->

        <nav class="desktop-nav">

            <a href="/Harvestly/Controller/Buyer/LandingController.php">
                Home
            </a>

            <a href="/Harvestly/Controller/Buyer/ProductController.php">
                Products
            </a>

            <a href="/Harvestly/Controller/Buyer/DashboardController.php#categories">
                Categories
            </a>

            <a href="/Harvestly/Controller/Buyer/LandingController.php#farmers">
                Farmers
            </a>

            <a href="/Harvestly/Controller/Buyer/LandingController.php#about">
                About
            </a>

        </nav>


        <!-- ACTIONS -->

        <div class="nav-actions">

            <button
                class="login-btn"
                onclick="goLogin()"
            >
                Login
            </button>


            <button
                class="register-btn"
                onclick="goRegister()"
            >
                Register
            </button>


            <button
                class="mobile-menu"
                onclick="toggleMobileMenu()"
            >

                <span class="material-symbols-outlined">
                    menu
                </span>

            </button>

        </div>

    </div>

</header>


<!-- =====================================================
     MOBILE NAV
===================================================== -->

<div
    id="mobileNav"
    class="mobile-nav"
>

    <a href="/Harvestly/Controller/Buyer/LandingController.php">
        Home
    </a>

    <a href="/Harvestly/Controller/Buyer/ProductController.php">
        Products
    </a>

    <a href="/Harvestly/Controller/Buyer/DashboardController.php#categories">
        Categories
    </a>

    <a href="/Harvestly/Controller/Buyer/LandingController.php#farmers">
        Farmers
    </a>

    <a href="/Harvestly/Controller/Buyer/LandingController.php#about">
        About
    </a>

</div>



<!-- =====================================================
     DASHBOARD
===================================================== -->

<div class="dashboard-container">


    <!-- =================================================
         SIDEBAR
    ================================================== -->

    <aside class="sidebar">

        <div class="sidebar-card">

            <ul>


                <!-- Dashboard -->

                <li>

                    <a
                        href="/Harvestly/Controller/Buyer/DashboardController.php"
                    >

                        <span class="material-symbols-outlined">
                            dashboard
                        </span>

                        <span>
                            Dashboard
                        </span>

                    </a>

                </li>


                <!-- Orders -->

                <li>

                    <a
                        href="/Harvestly/Controller/Buyer/OrdersController.php"
                        class="active"
                    >

                        <span class="material-symbols-outlined">
                            receipt_long
                        </span>

                        <span>
                            Orders
                        </span>

                    </a>

                </li>


                <!-- Wishlist -->

                <li>

                    <a href="/Harvestly/Controller/Buyer/ProductController.php">

                        <span class="material-symbols-outlined">
                            favorite
                        </span>

                        <span>
                            Wishlist
                        </span>

                    </a>

                </li>


                <!-- Settings -->

                <li>

                    <a href="/Harvestly/Controller/Buyer/ProfileController.php">

                        <span class="material-symbols-outlined">
                            settings
                        </span>

                        <span>
                            Settings
                        </span>

                    </a>

                </li>

            </ul>

        </div>

    </aside>



    <!-- =================================================
         MAIN CONTENT
    ================================================== -->

    <main class="main-content">


        <!-- PAGE HEADING -->

        <div class="page-heading">

            <h1>
                My Orders
            </h1>

            <p>
                Track and manage your recent farm-fresh purchases.
            </p>

        </div>



        <!-- =================================================
             ORDERS LIST
        ================================================== -->

        <div class="orders-list">


            <?php if (empty($orders)): ?>


                <!-- NO ORDERS -->

                <div class="empty-orders">

                    <span class="material-symbols-outlined">
                        shopping_bag
                    </span>

                    <h2>
                        No Orders Yet
                    </h2>

                    <p>
                        You haven't placed any orders yet.
                    </p>

                </div>


            <?php else: ?>


                <?php foreach ($orders as $order): ?>


                    <!-- =================================================
                         ORDER CARD
                    ================================================== -->

                    <div class="order-card">


                        <!-- ORDER TOP -->

                        <div class="order-top">


                            <!-- ORDER INFORMATION -->

                            <div>

                                <p class="order-number">

                                    Order #

                                    <?= htmlspecialchars(
                                        $order["id"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>

                                </p>


                                <p class="order-date">

                                    Placed on

                                    <?= htmlspecialchars(
                                        $order["date"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>

                                </p>

                            </div>



                            <!-- ORDER SUMMARY -->

                            <div class="order-summary">


                                <!-- STATUS -->

                                <span
                                    class="status-badge
                                    <?= htmlspecialchars(
                                        $order["status_class"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>"
                                >

                                    <?= htmlspecialchars(
                                        $order["status"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>

                                </span>


                                <!-- PRICE -->

                                <p class="order-price">

                                    Rs.

                                    <?= number_format(
                                        $order["total"]
                                    ) ?>

                                </p>

                            </div>

                        </div>



                        <!-- =================================================
                             ORDER BOTTOM
                        ================================================== -->

                        <div class="order-bottom">


                            <!-- PRODUCT IMAGES -->

                            <div class="product-images">


                                <?php if (!empty($order["images"])): ?>


                                    <?php foreach (
                                        $order["images"]
                                        as $image
                                    ): ?>


                                        <img
                                            src="<?= htmlspecialchars(
                                                $image,
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>"
                                            alt="Order Product"
                                        >


                                    <?php endforeach; ?>


                                <?php endif; ?>



                                <!-- EXTRA PRODUCTS -->

                                <?php if (
                                    !empty($order["extra"])
                                ): ?>

                                    <div class="extra-products">

                                        <?= htmlspecialchars(
                                            $order["extra"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>

                                    </div>

                                <?php endif; ?>


                            </div>



                            <!-- =================================================
                                 ORDER BUTTON
                            ================================================== -->

                            <button
                                type="button"
                                class="order-action tracking"
                                onclick="viewTracking('<?= htmlspecialchars($order["id"], ENT_QUOTES, "UTF-8") ?>')"
                            >
                                <span class="material-symbols-outlined">
                                    <?= htmlspecialchars($order["button_icon"], ENT_QUOTES, "UTF-8") ?>
                                </span>
                                <?= htmlspecialchars($order["button"], ENT_QUOTES, "UTF-8") ?>
                            </button>


                        </div>


                    </div>


                <?php endforeach; ?>


            <?php endif; ?>


        </div>


    </main>


</div>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="footer">

    <div class="footer-grid">


        <!-- BRAND -->

        <div class="footer-brand">

            <h2>
                Harvestly
            </h2>

            <p>
                © 2026 Harvestly.
                Bridging Sri Lankan Fields to Your Table.
            </p>

        </div>



        <!-- COMPANY -->

        <div class="footer-column">

            <h4>
                Company
            </h4>

            <a href="/Harvestly/Controller/Buyer/LandingController.php#about">
                About Harvestly
            </a>

            <a href="/Harvestly/Controller/Buyer/DashboardController.php">
                Contact Us
            </a>

        </div>



        <!-- RESOURCES -->

        <div class="footer-column">

            <h4>
                Resources
            </h4>

            <a href="/Harvestly/Controller/Buyer/ProductController.php">
                Products
            </a>

            <a href="/Harvestly/Controller/Buyer/DashboardController.php#categories">
                Categories
            </a>

        </div>



        <!-- LEGAL -->

        <div class="footer-column">

            <h4>
                Legal
            </h4>

            <a href="/Harvestly/Controller/Buyer/DashboardController.php">
                Privacy Policy
            </a>

            <a href="/Harvestly/Controller/Buyer/DashboardController.php">
                Terms of Service
            </a>

        </div>


    </div>

</footer>



<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script src="/Harvestly/js/Buyer/orders.js"></script>


</body>

</html>