<?php

$products = $products ?? [];

$search = $search ?? "";

$district =
    $district ?? "All Districts";

$maxPrice =
    $maxPrice ?? 2000;

$organic =
    $organic ?? false;

$fresh =
    $fresh ?? false;

$stock =
    $stock ?? false;

$sort =
    $sort ?? "Newest";

$added =
    $added ?? false;

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
        Harvestly - Browse Products
    </title>


    <link
        rel="stylesheet"
        href="/Harvestly/css/Buyer/browse-products.css"
    >


    <link
        href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;600;700&family=Manrope:wght@400;600;700&display=swap"
        rel="stylesheet"
    >


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


        <a
            href="/Harvestly/Controller/Buyer/DashboardController.php"
            class="logo"
        >
            <img src="/Harvestly/assets/harvestly-logo.jpeg" alt="Harvestly" style="height:32px;width:auto;display:block;object-fit:contain;">
        </a>


        <div class="desktop-nav">

            <a
                href="/Harvestly/Controller/Buyer/DashboardController.php"
            >
                Home
            </a>


            <a
                href="/Harvestly/Controller/Buyer/ProductController.php"
                class="active"
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

        </div>


        <div class="nav-actions">

            <a
                href="/Harvestly/Controller/Buyer/AuthController.php"
                class="login-btn"
            >
                Login
            </a>


            <a
                href="/Harvestly/Controller/Buyer/RegistrationController.php"
                class="register-btn"
            >
                Register
            </a>


            <button
                type="button"
                class="mobile-menu"
                id="mobileMenu"
            >

                <span class="material-symbols-outlined">
                    menu
                </span>

            </button>

        </div>

    </div>


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

    </div>

</nav>



<!-- =====================================================
     MAIN
===================================================== -->

<main class="main-container">


    <!-- HEADER -->

    <header class="page-header">

        <h1>
            Browse Fresh Vegetables
        </h1>


        <p>
            Explore fresh vegetables directly from trusted
            Sri Lankan farmers.
        </p>

    </header>


    <!-- ADDED MESSAGE -->

    <?php if ($added): ?>

        <div
            style="
                margin-bottom:20px;
                padding:14px 18px;
                border-radius:8px;
                background:#e8f5e9;
                color:#14532d;
                font-weight:600;
            "
        >

            Product added to cart successfully.


            <a
                href="/Harvestly/Controller/Buyer/CartController.php"
                style="
                    margin-left:10px;
                    text-decoration:underline;
                "
            >
                View Cart
            </a>

        </div>

    <?php endif; ?>



    <div class="browse-layout">


        <!-- =================================================
             FILTER SIDEBAR
        ================================================== -->

        <aside class="filters-sidebar">


            <form
                method="GET"
                action="/Harvestly/Controller/Buyer/ProductController.php"
                class="filter-card"
            >


                <h2>
                    Filters
                </h2>


                <!-- SEARCH -->

                <div class="filter-group">

                    <label for="search">
                        Search
                    </label>


                    <div class="filter-search">

                        <span class="material-symbols-outlined">
                            search
                        </span>


                        <input
                            type="text"
                            id="search"
                            name="search"
                            placeholder="Product or Farmer..."
                            value="<?php
                                echo htmlspecialchars(
                                    $search
                                );
                            ?>"
                        >

                    </div>

                </div>


                <!-- DISTRICT -->

                <div class="filter-group">

                    <label for="district">
                        District
                    </label>


                    <select
                        id="district"
                        name="district"
                    >

                        <option
                            value="All Districts"
                            <?php
                            echo $district === "All Districts"
                                ? "selected"
                                : "";
                            ?>
                        >
                            All Districts
                        </option>


                        <option
                            value="Nuwara Eliya"
                            <?php
                            echo $district === "Nuwara Eliya"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Nuwara Eliya
                        </option>


                        <option
                            value="Badulla"
                            <?php
                            echo $district === "Badulla"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Badulla
                        </option>


                        <option
                            value="Kandy"
                            <?php
                            echo $district === "Kandy"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Kandy
                        </option>

                    </select>

                </div>


                <!-- PRICE -->

                <div class="filter-group">

                    <label for="maxPrice">
                        Maximum Price (LKR)
                    </label>


                    <input
                        type="range"
                        id="maxPrice"
                        name="maxPrice"
                        min="100"
                        max="2000"
                        value="<?php
                            echo htmlspecialchars(
                                $maxPrice
                            );
                        ?>"
                        oninput="document.getElementById('priceValue').textContent = Number(this.value).toLocaleString()"
                    >


                    <div class="price-values">

                        <span>
                            100
                        </span>


                        <span id="priceValue">

                            <?php
                            echo number_format(
                                $maxPrice
                            );
                            ?>

                        </span>


                        <span>
                            2,000
                        </span>

                    </div>

                </div>


                <!-- CHECKBOXES -->

                <div class="checkbox-group">


                    <label>

                        <input
                            type="checkbox"
                            name="organic"
                            value="1"
                            <?php
                            echo $organic
                                ? "checked"
                                : "";
                            ?>
                        >

                        <span>
                            Organic Certified
                        </span>

                    </label>


                    <label>

                        <input
                            type="checkbox"
                            name="fresh"
                            value="1"
                            <?php
                            echo $fresh
                                ? "checked"
                                : "";
                            ?>
                        >

                        <span>
                            Fresh Today
                        </span>

                    </label>


                    <label>

                        <input
                            type="checkbox"
                            name="stock"
                            value="1"
                            <?php
                            echo $stock
                                ? "checked"
                                : "";
                            ?>
                        >

                        <span>
                            In Stock
                        </span>

                    </label>

                </div>


                <!-- PRESERVE SORT -->

                <input
                    type="hidden"
                    name="sort"
                    value="<?php
                        echo htmlspecialchars(
                            $sort
                        );
                    ?>"
                >


                <!-- APPLY -->

                <button
                    type="submit"
                    class="register-btn"
                    style="
                        width:100%;
                        border:none;
                        margin-top:20px;
                        cursor:pointer;
                    "
                >
                    Apply Filters
                </button>


                <!-- CLEAR -->

                <a
                    href="/Harvestly/Controller/Buyer/ProductController.php"
                    style="
                        display:block;
                        text-align:center;
                        margin-top:12px;
                    "
                >
                    Clear Filters
                </a>


            </form>

        </aside>



        <!-- =================================================
             PRODUCTS
        ================================================== -->

        <section class="products-area">


            <!-- =================================================
                 SORT
            ================================================== -->

            <div class="sort-bar">


                <span class="result-count">

                    Showing
                    <?php echo count($products); ?>
                    results

                </span>


                <form
                    method="GET"
                    action="/Harvestly/Controller/Buyer/ProductController.php"
                    class="sort-control"
                >


                    <!-- Preserve search -->

                    <input
                        type="hidden"
                        name="search"
                        value="<?php
                            echo htmlspecialchars(
                                $search
                            );
                        ?>"
                    >


                    <!-- Preserve district -->

                    <input
                        type="hidden"
                        name="district"
                        value="<?php
                            echo htmlspecialchars(
                                $district
                            );
                        ?>"
                    >


                    <!-- Preserve price -->

                    <input
                        type="hidden"
                        name="maxPrice"
                        value="<?php
                            echo htmlspecialchars(
                                $maxPrice
                            );
                        ?>"
                    >


                    <!-- Preserve organic -->

                    <?php if ($organic): ?>

                        <input
                            type="hidden"
                            name="organic"
                            value="1"
                        >

                    <?php endif; ?>


                    <!-- Preserve fresh -->

                    <?php if ($fresh): ?>

                        <input
                            type="hidden"
                            name="fresh"
                            value="1"
                        >

                    <?php endif; ?>


                    <!-- Preserve stock -->

                    <?php if ($stock): ?>

                        <input
                            type="hidden"
                            name="stock"
                            value="1"
                        >

                    <?php endif; ?>


                    <label for="sortSelect">
                        Sort by:
                    </label>


                    <select
                        id="sortSelect"
                        name="sort"
                        onchange="this.form.submit()"
                    >

                        <option
                            value="Newest"
                            <?php
                            echo $sort === "Newest"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Newest
                        </option>


                        <option
                            value="Price: Low to High"
                            <?php
                            echo $sort === "Price: Low to High"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Price: Low to High
                        </option>


                        <option
                            value="Price: High to Low"
                            <?php
                            echo $sort === "Price: High to Low"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Price: High to Low
                        </option>


                        <option
                            value="Best Rated"
                            <?php
                            echo $sort === "Best Rated"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Best Rated
                        </option>


                        <option
                            value="Popular"
                            <?php
                            echo $sort === "Popular"
                                ? "selected"
                                : "";
                            ?>
                        >
                            Popular
                        </option>

                    </select>

                </form>

            </div>



            <!-- =================================================
                 PRODUCT GRID
            ================================================== -->

            <div class="product-grid">


                <?php if (count($products) > 0): ?>


                    <?php foreach ($products as $product): ?>


                        <article
                            class="product-card"
                        >


                            <!-- IMAGE -->

                            <div class="product-image">


                                <img
                                    src="<?php
                                        echo htmlspecialchars(
                                            $product["image"]
                                        );
                                    ?>"
                                    alt="<?php
                                        echo htmlspecialchars(
                                            $product["name"]
                                        );
                                    ?>"
                                >


                                <div class="product-badges">


                                    <?php if ($product["fresh"]): ?>

                                        <span class="fresh-badge">
                                            FRESH TODAY
                                        </span>

                                    <?php endif; ?>


                                    <?php if ($product["organic"]): ?>

                                        <span class="organic-badge">
                                            ORGANIC
                                        </span>

                                    <?php endif; ?>


                                </div>


                                <button
                                    type="button"
                                    class="favorite-btn"
                                    aria-label="Favorite"
                                >

                                    <span class="material-symbols-outlined">
                                        favorite
                                    </span>

                                </button>


                            </div>



                            <!-- CONTENT -->

                            <div class="product-content">


                                <div class="product-title-row">


                                    <h3>

                                        <?php
                                        echo htmlspecialchars(
                                            $product["name"]
                                        );
                                        ?>

                                    </h3>


                                    <span class="product-price">

                                        LKR

                                        <?php
                                        echo htmlspecialchars(
                                            $product["price"]
                                        );
                                        ?>


                                        <small>

                                            /

                                            <?php
                                            echo htmlspecialchars(
                                                $product["unit"]
                                            );
                                            ?>

                                        </small>

                                    </span>


                                </div>


                                <div class="farmer-info">


                                    <span class="material-symbols-outlined">
                                        storefront
                                    </span>


                                    <span>

                                        <?php
                                        echo htmlspecialchars(
                                            $product["farmer"]
                                        );
                                        ?>

                                    </span>


                                </div>


                                <div class="rating">


                                    <span class="material-symbols-outlined star">
                                        star
                                    </span>


                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $product["rating"]
                                        );
                                        ?>

                                    </strong>


                                    <span>

                                        (
                                        <?php
                                        echo htmlspecialchars(
                                            $product["reviews"]
                                        );
                                        ?>
                                        reviews)

                                    </span>


                                </div>



                                <!-- BUTTONS -->

                                <div class="product-buttons">


                                    <!-- ADD -->

                                    <a
                                        href="/Harvestly/Controller/Buyer/ProductController.php?action=add_to_cart&id=<?php echo urlencode($product["id"]); ?>"
                                        class="add-button"
                                    >

                                        <span class="material-symbols-outlined">
                                            shopping_cart
                                        </span>

                                        Add

                                    </a>


                                    <!-- DETAILS -->

                                    <a
                                        href="/Harvestly/Controller/Buyer/ProductDetailsController.php?id=<?php echo urlencode($product["id"]); ?>"
                                        class="details-button"
                                    >

                                        Details

                                    </a>


                                </div>

                            </div>

                        </article>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="no-products">

                        <span class="material-symbols-outlined">
                            search_off
                        </span>


                        <h3>
                            No products found
                        </h3>


                        <p>
                            Try changing your filters.
                        </p>

                    </div>


                <?php endif; ?>


            </div>

        </section>

    </div>

</main>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="footer">

    <div class="footer-inner">


        <div class="footer-brand">

            <span>
                Harvestly
            </span>


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



<script src="/Harvestly/js/Buyer/browse-products.js"></script>




</body>

</html>