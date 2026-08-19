<?php
$buyer = isset($buyer) ? $buyer : [];
$orderStats = isset($orderStats) ? $orderStats : ['total'=>0,'delivered'=>0,'pending'=>0,'cancelled'=>0];
$success = isset($success) ? $success : '';
$error = isset($error) ? $error : '';
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Harvestly - Buyer Profile</title>

    <link
        rel="stylesheet"
        href="/Harvestly/css/Buyer/buyer-profile.css"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
        rel="stylesheet"
    >

</head>

<body>

<!-- ================= HEADER ================= -->

<nav class="top-navbar">

    <div class="navbar-inner">

        <a href="/Harvestly/Controller/Buyer/DashboardController.php" class="brand">

            <img src="/Harvestly/assets/harvestly-logo.jpeg" alt="Harvestly" style="height:34px;width:auto;display:block;object-fit:contain;">

        </a>

        <div class="desktop-nav">

            <a href="/Harvestly/Controller/Buyer/DashboardController.php">Home</a>

            <a href="/Harvestly/Controller/Buyer/ProductController.php">Products</a>

            <a href="/Harvestly/Controller/Buyer/DashboardController.php#categories">Categories</a>

            <a href="/Harvestly/Controller/Buyer/LandingController.php#farmers">Farmers</a>

            <a href="/Harvestly/Controller/Buyer/LandingController.php#about">About</a>

        </div>

        <div class="nav-actions">

            <a href="/Harvestly/Controller/Buyer/CartController.php" class="icon-btn">

                <span class="material-symbols-outlined">
                    shopping_cart
                </span>

                <span class="cart-count">3</span>

            </a>

            <button class="icon-btn">

                <span class="material-symbols-outlined">
                    notifications
                </span>

            </button>

            <div class="user-menu">

                <img
                    src="<?php echo $buyer["profile_image"]; ?>"
                    alt="Profile"
                    id="navProfileImage"
                >

                <span>
                    <?php echo $buyer["name"]; ?>
                </span>

                <a
                    href="/Harvestly/Controller/Buyer/LogoutController.php"
                    class="logout-link"
                    title="Logout"
                >
                    <span class="material-symbols-outlined">logout</span>
                </a>

            </div>

        </div>

    </div>

</nav>


<!-- ================= DASHBOARD ================= -->

<div class="dashboard-container">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="sidebar-card">

            <ul>

                <li>

                    <a href="/Harvestly/Controller/Buyer/DashboardController.php">

                        <span class="material-symbols-outlined">
                            home
                        </span>

                        <span>Home</span>

                    </a>

                </li>

                <li>

                    <a href="/Harvestly/Controller/Buyer/OrdersController.php">

                        <span class="material-symbols-outlined">
                            receipt_long
                        </span>

                        <span>Orders</span>

                    </a>

                </li>

                <li>

                    <a href="/Harvestly/Controller/Buyer/NotificationsController.php">

                        <span class="material-symbols-outlined">
                            notifications
                        </span>

                        <span>Notifications</span>

                    </a>

                </li>

                <li>

                    <a
                        href="/Harvestly/Controller/Buyer/ProfileController.php"
                        class="active"
                    >

                        <span class="material-symbols-outlined">
                            person
                        </span>

                        <span>Profile</span>

                    </a>

                </li>

            </ul>

        </div>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main-content">

        <div class="page-heading">

            <h1>My Profile</h1>

            <p>
                Manage your Harvestly buyer account and delivery details.
            </p>

        </div>


        <?php if ($success): ?>

            <div class="success-message">

                <span class="material-symbols-outlined">
                    check_circle
                </span>

                <?php echo $success; ?>

            </div>

        <?php endif; ?>


        <!-- PROFILE CARD -->

        <section class="profile-card">

            <div class="profile-header">

                <div class="profile-image-wrapper">

                    <img
                        src="<?php echo $buyer["profile_image"]; ?>"
                        id="profilePreview"
                        alt="Buyer Profile"
                    >

                    <label
                        for="profileImage"
                        class="camera-button"
                    >

                        <span class="material-symbols-outlined">
                            photo_camera
                        </span>

                    </label>

                </div>

                <div class="profile-title">

                    <h2>
                        <?php echo $buyer["name"]; ?>
                    </h2>

                    <p>
                        Buyer Account
                    </p>

                    <span class="member-badge">

                        <span class="material-symbols-outlined">
                            verified
                        </span>

                        Verified Buyer

                    </span>

                </div>

            </div>


            <!-- FORM -->

            <form
                method="POST"
                enctype="multipart/form-data"
                id="profileForm"
            >

                <input
                    type="file"
                    name="profile_image"
                    id="profileImage"
                    accept="image/png,image/jpeg,image/webp"
                    hidden
                >


                <div class="section-title">

                    <span class="material-symbols-outlined">
                        person
                    </span>

                    <div>

                        <h3>Personal Information</h3>

                        <p>
                            Update your basic account information.
                        </p>

                    </div>

                </div>


                <div class="form-grid">

                    <div class="form-group">

                        <label>Full Name</label>

                        <input
                            type="text"
                            name="name"
                            value="<?php echo $buyer["name"]; ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>Email Address</label>

                        <input
                            type="email"
                            name="email"
                            value="<?php echo $buyer["email"]; ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>Phone Number</label>

                        <input
                            type="text"
                            name="phone"
                            value="<?php echo $buyer["phone"]; ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label>Registered City</label>

                        <select name="city">

                            <option
                                <?php echo $buyer["city"] === "Colombo" ? "selected" : ""; ?>
                            >
                                Colombo
                            </option>

                            <option
                                <?php echo $buyer["city"] === "Kandy" ? "selected" : ""; ?>
                            >
                                Kandy
                            </option>

                            <option
                                <?php echo $buyer["city"] === "Galle" ? "selected" : ""; ?>
                            >
                                Galle
                            </option>

                            <option
                                <?php echo $buyer["city"] === "Ratnapura" ? "selected" : ""; ?>
                            >
                                Ratnapura
                            </option>

                            <option
                                <?php echo $buyer["city"] === "Kurunegala" ? "selected" : ""; ?>
                            >
                                Kurunegala
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>District</label>

                        <select name="district">

                            <option
                                <?php echo $buyer["district"] === "Colombo" ? "selected" : ""; ?>
                            >
                                Colombo
                            </option>

                            <option>Kandy</option>

                            <option>Galle</option>

                            <option>Ratnapura</option>

                            <option>Gampaha</option>

                            <option>Kalutara</option>

                        </select>

                    </div>


                    <div class="form-group full-width">

                        <label>Delivery Address</label>

                        <textarea
                            name="address"
                            rows="3"
                            required
                        ><?php echo $buyer["address"]; ?></textarea>

                    </div>

                </div>


                <div class="form-actions">

                    <button
                        type="button"
                        class="cancel-btn"
                        onclick="resetProfile()"
                    >

                        Cancel

                    </button>

                    <button
                        type="submit"
                        class="save-btn"
                    >

                        <span class="material-symbols-outlined">
                            save
                        </span>

                        Save Changes

                    </button>

                </div>

            </form>

        </section>


        <!-- ORDER SUMMARY -->

        <section class="stats-section">

            <div class="section-title">

                <span class="material-symbols-outlined">
                    shopping_bag
                </span>

                <div>

                    <h3>Order History</h3>

                    <p>
                        Overview of your Harvestly purchases.
                    </p>

                </div>

            </div>


            <div class="stats-grid">

                <div class="stat-card">

                    <div class="stat-icon">

                        <span class="material-symbols-outlined">
                            shopping_bag
                        </span>

                    </div>

                    <div>

                        <span>Total Orders</span>

                        <strong>
                            <?php echo $orderStats["total"]; ?>
                        </strong>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">

                        <span class="material-symbols-outlined">
                            check_circle
                        </span>

                    </div>

                    <div>

                        <span>Delivered</span>

                        <strong>
                            <?php echo $orderStats["delivered"]; ?>
                        </strong>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">

                        <span class="material-symbols-outlined">
                            local_shipping
                        </span>

                    </div>

                    <div>

                        <span>Pending</span>

                        <strong>
                            <?php echo $orderStats["pending"]; ?>
                        </strong>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">

                        <span class="material-symbols-outlined">
                            cancel
                        </span>

                    </div>

                    <div>

                        <span>Cancelled</span>

                        <strong>
                            <?php echo $orderStats["cancelled"]; ?>
                        </strong>

                    </div>

                </div>

            </div>

        </section>


        <!-- ACCOUNT INFO -->

        <section class="account-card">

            <div>

                <span class="material-symbols-outlined">
                    calendar_month
                </span>

                <div>

                    <strong>Member Since</strong>

                    <p>
                        <?php echo $buyer["joined"]; ?>
                    </p>

                </div>

            </div>


            <a href="/Harvestly/Controller/Buyer/OrdersController.php">

                View Order History

                <span class="material-symbols-outlined">
                    arrow_forward
                </span>

            </a>

        </section>

    </main>

</div>


<footer class="footer">

    <div>

        <strong>Harvestly</strong>

        <p>
            © 2026 Harvestly. Bridging Sri Lankan Fields to Your Table.
        </p>

    </div>

</footer>


<script src="/Harvestly/js/Buyer/buyer-profile.js"></script>

</body>

</html>