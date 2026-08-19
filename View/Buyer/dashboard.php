<?php

$buyerName =
    $buyerName ?? "Nimal Perera";

$notificationCount =
    $notificationCount ?? 0;

$cartCount =
    $cartCount ?? 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Harvestly - Buyer Dashboard
    </title>


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="/Harvestly/css/Buyer/buyer-dashboard.css"
    >


    <!-- Fonts -->

    <link
        href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;600;700&family=Manrope:wght@400;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- Material Symbols -->

    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap"
        rel="stylesheet"
    >

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar">

    <div class="navbar-inner">


        <!-- LOGO -->

        <a
            href="/Harvestly/Controller/Buyer/DashboardController.php"
            class="logo"
        >
            <img src="/Harvestly/assets/harvestly-logo.jpeg" alt="Harvestly" style="height:34px;width:auto;display:block;object-fit:contain;">
        </a>



        <!-- DESKTOP NAVIGATION -->

        <div class="nav-links">

            <a
                href="/Harvestly/Controller/Buyer/DashboardController.php"
                class="nav-link active"
            >
                Home
            </a>


            <a
                href="/Harvestly/Controller/Buyer/ProductController.php"
                class="nav-link"
            >
                Products
            </a>


            <a
                href="#categories"
                class="nav-link"
            >
                Categories
            </a>


            <a
                href="#farmers"
                class="nav-link"
            >
                Farmers
            </a>


            <a
                href="#about"
                class="nav-link"
            >
                About
            </a>

        </div>



        <!-- RIGHT NAVIGATION -->

        <div class="nav-actions">


            <!-- SEARCH -->

            <div class="search-box">

                <input
                    type="text"
                    id="searchInput"
                    placeholder="Search products or farmers..."
                    autocomplete="off"
                >


                <button
                    type="button"
                    id="searchButton"
                    class="search-button"
                    aria-label="Search"
                    title="Search"
                >

                    <span class="material-symbols-outlined">
                        search
                    </span>

                </button>

            </div>



            <!-- NOTIFICATION -->

            <button
                type="button"
                class="icon-button"
                id="notificationButton"
                title="Notifications"
            >

                <span class="material-symbols-outlined">
                    notifications
                </span>


                <?php if ($notificationCount > 0): ?>

                    <span class="notification-badge">

                        <?php
                        echo htmlspecialchars(
                            $notificationCount
                        );
                        ?>

                    </span>

                <?php endif; ?>

            </button>



            <!-- CART -->

            <a
                href="/Harvestly/Controller/Buyer/CartController.php"
                class="icon-button cart-link"
                title="Shopping Cart"
            >

                <span class="material-symbols-outlined">
                    shopping_cart
                </span>


                <?php if ($cartCount > 0): ?>

                    <span class="cart-badge">

                        <?php
                        echo htmlspecialchars(
                            $cartCount
                        );
                        ?>

                    </span>

                <?php endif; ?>


            </a>



            <!-- AUTH -->

            <div class="auth-buttons">


                <a
                    href="/Harvestly/Controller/Buyer/AuthController.php"
                    class="login-button"
                >
                    Login
                </a>


                <a
                    href="/Harvestly/Controller/Buyer/RegistrationController.php"
                    class="register-button"
                >
                    Register
                </a>


            </div>



            <!-- MOBILE MENU -->

            <button
                type="button"
                class="mobile-menu-button"
                id="mobileMenuButton"
                aria-label="Open menu"
            >

                <span class="material-symbols-outlined">
                    menu
                </span>

            </button>


        </div>

    </div>



    <!-- =================================================
         MOBILE NAVIGATION
    ================================================== -->

    <div
        class="mobile-nav"
        id="mobileNav"
    >


        <a
            href="/Harvestly/Controller/Buyer/DashboardController.php"
        >
            Home
        </a>


        <a
            href="/Harvestly/Controller/Buyer/ProductController.php"
        >
            Products
        </a>


        <a href="#categories">
            Categories
        </a>


        <a href="#farmers">
            Farmers
        </a>


        <a href="#about">
            About
        </a>


        <a
            href="/Harvestly/Controller/Buyer/AuthController.php"
        >
            Login
        </a>


        <a
            href="/Harvestly/Controller/Buyer/RegistrationController.php"
        >
            Register
        </a>


        <a
            href="/Harvestly/Controller/Buyer/CartController.php"
        >
            Shopping Cart

            <?php if ($cartCount > 0): ?>

                (
                <?php
                echo htmlspecialchars(
                    $cartCount
                );
                ?>
                )

            <?php endif; ?>

        </a>

    </div>

</nav>



<!-- =====================================================
     MAIN
===================================================== -->

<main class="main-container">


    <!-- =================================================
         HERO
    ================================================== -->

    <section class="hero-banner">


        <!-- BACKGROUND IMAGE -->

        <div class="hero-image"></div>


        <!-- OVERLAY -->

        <div class="hero-overlay">


            <div class="hero-content">


                <h1>

                    Welcome back,

                    <?php
                    echo htmlspecialchars(
                        $buyerName
                    );
                    ?>

                    !

                </h1>


                <p>

                    Discover the freshest produce from local
                    Sri Lankan farmers, delivered straight to
                    your door.

                </p>


                <div class="hero-buttons">


                    <a
                        href="/Harvestly/Controller/Buyer/ProductController.php"
                        class="shop-button"
                    >
                        Shop Now
                    </a>


                    <a
                        href="#offers"
                        class="offers-button"
                    >
                        View Offers
                    </a>


                </div>


            </div>


        </div>

    </section>



    <!-- =================================================
         CATEGORIES
    ================================================== -->

    <section
        id="categories"
        class="dashboard-section"
    >

        <div class="section-inner">

            <h2>
                Fresh Categories
            </h2>

            <p>
                Browse fresh vegetables and produce from
                trusted Sri Lankan farmers.
            </p>


            <a
                href="/Harvestly/Controller/Buyer/ProductController.php"
                class="shop-button"
            >
                Browse Products
            </a>

        </div>

    </section>



    <!-- =================================================
         FARMERS
    ================================================== -->

    <section
        id="farmers"
        class="dashboard-section"
    >

        <div class="section-inner">

            <h2>
                Trusted Local Farmers
            </h2>

            <p>
                Buy directly from verified farmers across
                Sri Lanka.
            </p>

        </div>

    </section>



    <!-- =================================================
         OFFERS
    ================================================== -->

    <section
        id="offers"
        class="dashboard-section"
    >

        <div class="section-inner">

            <h2>
                Fresh Offers
            </h2>

            <p>
                Discover fresh offers and seasonal produce
                available from local farms.
            </p>


            <a
                href="/Harvestly/Controller/Buyer/ProductController.php"
                class="shop-button"
            >
                Shop Now
            </a>

        </div>

    </section>



    <!-- =================================================
         ABOUT
    ================================================== -->

    <section
        id="about"
        class="dashboard-section"
    >

        <div class="section-inner">

            <h2>
                About Harvestly
            </h2>

            <p>
                Harvestly connects Sri Lankan buyers directly
                with trusted local farmers, bringing fresh
                farm produce straight to your table.
            </p>

        </div>

    </section>



    <!-- =================================================
         FOOTER
    ================================================== -->

    <footer class="footer">

        <div class="footer-inner">


            <!-- BRAND -->

            <div class="footer-brand">

                <span class="footer-logo">
                    Harvestly
                </span>


                <p>
                    © 2026 Harvestly.
                    Bridging Sri Lankan Fields to Your Table.
                </p>

            </div>



            <!-- LINKS -->

            <div class="footer-links">


                <a href="#about">
                    About Harvestly
                </a>


                <a
                    href="/Harvestly/Controller/Buyer/ProductController.php"
                >
                    Quick Links
                </a>


                <a href="#contact">
                    Contact Us
                </a>


                <a href="#privacy">
                    Privacy Policy
                </a>


                <a href="#terms">
                    Terms of Service
                </a>


            </div>


        </div>

    </footer>


</main>



<script src="/Harvestly/js/Buyer/buyer-dashboard.js"></script>


</body>

</html>