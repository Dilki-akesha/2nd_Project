<?php
/**
 * Harvestly Master Entry Point & MVC Front Controller
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/auth/controllers/AuthController.php';
require_once __DIR__ . '/admin/controllers/AdminController.php';

$action = $_GET['action'] ?? null;
$adminAction = $_GET['admin_action'] ?? null;
$page = $_GET['page'] ?? 'landing';

// 1. Handle Auth POST Actions
if ($action) {
    $authCtrl = new AuthController();
    switch ($action) {
        case 'login':
            $authCtrl->handleLogin();
            break;
        case 'signup_buyer':
            $authCtrl->handleBuyerSignup();
            break;
        case 'signup_farmer':
            $authCtrl->handleFarmerSignup();
            break;
        case 'signup_courier':
            $authCtrl->handleCourierSignup();
            break;
        case 'logout':
            $authCtrl->handleLogout();
            break;
    }
    exit();
}

// 2. Handle Admin POST Actions
if ($adminAction) {
    $adminCtrl = new AdminController();
    $adminCtrl->handleAdminAction();
    exit();
}

// 3. Render Page Views
require_once __DIR__ . '/includes/header.php';

$isAdminPage = (strpos($page, 'admin_') === 0);

if ($isAdminPage) {
    echo '<div class="app-container">';
    require_once __DIR__ . '/includes/sidebar.php';
    echo '<div class="main-content">';
    $adminCtrl = new AdminController();
    $adminCtrl->renderView($page);
    echo '</div>'; // close main-content
    echo '</div>'; // close app-container
} else {
    echo '<div class="main-content" style="padding-top: 80px;">';
    switch ($page) {
        case 'login':
            require_once __DIR__ . '/auth/views/login.php';
            break;
        case 'role_select':
            require_once __DIR__ . '/auth/views/role_select.php';
            break;
        case 'signup_buyer':
            require_once __DIR__ . '/auth/views/signup_buyer.php';
            break;
        case 'signup_farmer':
            require_once __DIR__ . '/auth/views/signup_farmer.php';
            break;
        case 'signup_courier':
            require_once __DIR__ . '/auth/views/signup_courier.php';
            break;
        case 'pending_approval':
            require_once __DIR__ . '/auth/views/pending_approval.php';
            break;
        case 'landing':
        default:
            renderLandingPage();
            break;
    }
    echo '</div>';
}

require_once __DIR__ . '/includes/footer.php';

/**
 * Render Public Landing Page (Main Interface)
 */
function renderLandingPage() {
    ?>
    <main class="flex-1 flex flex-col relative w-full bg-surface">
        <!-- Hero Section -->
        <section style="background-color: var(--color-background); padding: 60px 24px 80px; overflow: hidden;">
            <div style="max-width: 1280px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 48px; align-items: center;">
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <div style="display: inline-flex; align-items: center; gap: 8px; align-self: flex-start; padding: 6px 16px; border-radius: 9999px; background-color: var(--color-secondary-container); color: var(--color-on-secondary-container); font-size: 13px; font-weight: 700;">
                        🌱 Island-wide Direct Farm-to-Table Marketplace
                    </div>
                    <h1 style="font-size: 46px; font-weight: 800; line-height: 1.15; color: var(--color-on-surface); letter-spacing: -0.02em;">
                        Connecting Sri Lankan Farmers <span style="color: var(--color-primary); display: block;">Directly to Your Doorstep</span>
                    </h1>
                    <p style="font-size: 18px; color: var(--color-on-surface-variant); line-height: 1.6; max-width: 540px;">
                        Fresh organic harvest sourced straight from local growers across Nuwara Eliya, Dambulla, Matale, and Jaffna — delivered fast and fresh by verified logistics partners with zero middlemen.
                    </p>

                    <!-- Search & Location Filter Bar -->
                    <div style="background: #fff; border-radius: var(--radius-xl); padding: 12px; box-shadow: var(--shadow-md); border: 1px solid var(--color-outline-variant); max-width: 580px;">
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 6px; padding: 10px 14px; background: var(--color-surface-container-low); border-radius: var(--radius-md);">
                                📍
                                <select style="background: transparent; border: none; font-weight: 600; font-size: 13px; outline: none; cursor: pointer;">
                                    <option>Colombo & Suburbs</option>
                                    <option>Kandy & Central</option>
                                    <option>Galle & South Coast</option>
                                    <option>All Sri Lanka</option>
                                </select>
                            </div>
                            <div style="flex: 1; min-width: 180px; display: flex; align-items: center; gap: 8px; padding: 10px 14px; background: var(--color-surface-container-low); border-radius: var(--radius-md);">
                                🔍
                                <input type="text" placeholder="Search organic vegetables, fruits, highland greens..." style="border: none; background: transparent; width: 100%; outline: none; font-size: 14px;">
                            </div>
                            <button type="button" class="btn btn-primary">Search &rarr;</button>
                        </div>
                    </div>

                    <!-- Platform Stats -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 12px;">
                        <div style="background: #fff; padding: 16px; border-radius: var(--radius-lg); border: 1px solid var(--color-outline-variant);">
                            <span style="font-size: 28px; font-weight: 800; color: var(--color-primary); display: block;">100%</span>
                            <span style="font-size: 13px; font-weight: 700; color: var(--color-on-surface);">Farm Direct</span>
                            <span style="font-size: 11px; color: var(--color-outline);">Straight from local growers</span>
                        </div>
                        <div style="background: #fff; padding: 16px; border-radius: var(--radius-lg); border: 1px solid var(--color-outline-variant);">
                            <span style="font-size: 28px; font-weight: 800; color: var(--color-primary); display: block;">&lt;24h</span>
                            <span style="font-size: 13px; font-weight: 700; color: var(--color-on-surface);">Harvest to Home</span>
                            <span style="font-size: 11px; color: var(--color-outline);">Direct rapid transit</span>
                        </div>
                        <div style="background: #fff; padding: 16px; border-radius: var(--radius-lg); border: 1px solid var(--color-outline-variant);">
                            <span style="font-size: 28px; font-weight: 800; color: var(--color-primary); display: block;">88%+</span>
                            <span style="font-size: 13px; font-weight: 700; color: var(--color-on-surface);">To Local Grower</span>
                            <span style="font-size: 11px; color: var(--color-outline);">Fair escrow pay</span>
                        </div>
                    </div>
                </div>

                <div style="position: relative;">
                    <div style="border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-lg); border: 4px solid #fff; background: var(--color-surface-container-high); position: relative; aspect-ratio: 4/3.5;">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCogezLWDU4ICGDbwDYqpBYwSWgFCll3PGuXiuuWdNcx4rdncSFREsuDMCxiogbrAACvTqn_5uSTKRjhiAZboHDtJSeZ1V4n90iuMn32HZrl5l3B-nOVpsN8vHuJu9yO59SowyUgRQMtLo_vGQLi7Ypn297MrUnGdRb5jQqSJVS2LCbIlApTBI8RZ7dBaDVps2_Bp8fs5_4Z6-HW92hR0dKZJhS_dRNOV9AFCN-cCni1RZykDX_MXyQnQ" alt="Fresh Sri Lankan Produce Harvest" style="width: 100%; height: 100%; object-fit: cover;">
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.65) 0%, transparent 60%);"></div>
                        <div style="position: absolute; bottom: 20px; left: 20px; right: 20px; color: #fff; text-align: left; display: flex; justify-content: space-between; align-items: flex-end;">
                            <div>
                                <span style="font-size: 13px; font-weight: 600; color: #cbffc2;">Direct from Upcountry</span>
                                <h3 style="font-size: 20px; font-weight: 800; color: #fff; margin-top: 2px;">Nuwara Eliya & Central Valleys</h3>
                            </div>
                            <span class="badge badge-success" style="padding: 6px 14px; font-size: 12px; backdrop-filter: blur(8px); background: rgba(255,255,255,0.95); color: var(--color-primary);">✓ Certified Farm</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Produce Grid -->
        <section id="featured-produce" style="background-color: var(--color-surface-container-low); padding: 80px 24px;">
            <div style="max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 32px;">
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 16px; border-bottom: 1px solid var(--color-outline-variant); padding-bottom: 16px;">
                    <div>
                        <span style="font-size: 12px; font-weight: 800; color: var(--color-primary); letter-spacing: 1px; text-transform: uppercase;">DIRECT HARVEST</span>
                        <h2 style="font-size: 32px; font-weight: 800; color: var(--color-on-surface);">Fresh Today from Local Farms</h2>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px;">
                    <!-- Product 1: Nuwara Eliya Crisp Carrots -->
                    <article style="background: #fff; border-radius: var(--radius-lg); border: 1px solid var(--color-outline-variant); overflow: hidden; display: flex; flex-direction: column; box-shadow: var(--shadow-sm);">
                        <div style="height: 220px; overflow: hidden; position: relative; background: var(--color-surface-container);">
                            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCogezLWDU4ICGDbwDYqpBYwSWgFCll3PGuXiuuWdNcx4rdncSFREsuDMCxiogbrAACvTqn_5uSTKRjhiAZboHDtJSeZ1V4n90iuMn32HZrl5l3B-nOVpsN8vHuJu9yO59SowyUgRQMtLo_vGQLi7Ypn297MrUnGdRb5jQqSJVS2LCbIlApTBI8RZ7dBaDVps2_Bp8fs5_4Z6-HW92hR0dKZJhS_dRNOV9AFCN-cCni1RZykDX_MXyQnQ" alt="Nuwara Eliya Crisp Carrots" style="width: 100%; height: 100%; object-fit: cover;">
                            <span class="badge badge-success" style="position: absolute; bottom: 12px; left: 12px; backdrop-filter: blur(6px); background: rgba(255,255,255,0.9); border: 1px solid rgba(0,0,0,0.1);">★ 4.9 (128)</span>
                        </div>
                        <div style="padding: 20px; display: flex; flex-direction: column; gap: 12px; flex: 1;">
                            <div>
                                <h3 style="font-size: 18px; font-weight: 700; color: var(--color-on-surface);">Nuwara Eliya Crisp Carrots</h3>
                                <div style="font-size: 13px; color: var(--color-on-surface-variant); margin-top: 2px;">✓ Sunlight Highlands Farm</div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 12px; border-top: 1px solid var(--color-outline-variant);">
                                <div>
                                    <span style="font-size: 22px; font-weight: 800; color: var(--color-primary);">Rs. 420</span>
                                    <span style="font-size: 12px; color: var(--color-outline);"> / kg</span>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm btn-add-cart">Add to Cart</button>
                            </div>
                        </div>
                    </article>

                    <!-- Product 2: Dambulla Red Onions -->
                    <article style="background: #fff; border-radius: var(--radius-lg); border: 1px solid var(--color-outline-variant); overflow: hidden; display: flex; flex-direction: column; box-shadow: var(--shadow-sm);">
                        <div style="height: 220px; overflow: hidden; position: relative; background: var(--color-surface-container);">
                            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDVYGd8vEqijGYizIBAWGUCAW5TRRFT7dKb3OH7ax9kiR_brobGO1bCRz_ONVysYmJpdOe5NiOtNp66dtiOB1jEnYVIWXw3Ht5NpEkSv4nyDy37jzl2UeVBOdeYpbtS3eJnzhQDhECFucS5mQS8G34jPLKamXrPV9atg38G8shcGAFKtTvssE-oTe2d2OyfxQEvqiYV8dkchrnLhx9Xl8Gof6wss_6_cczUrR1V-MF7QpmWkwvQt5Kzzw" alt="Dambulla Red Onions" style="width: 100%; height: 100%; object-fit: cover;">
                            <span class="badge badge-success" style="position: absolute; bottom: 12px; left: 12px; backdrop-filter: blur(6px); background: rgba(255,255,255,0.9); border: 1px solid rgba(0,0,0,0.1);">★ 4.8 (94)</span>
                        </div>
                        <div style="padding: 20px; display: flex; flex-direction: column; gap: 12px; flex: 1;">
                            <div>
                                <h3 style="font-size: 18px; font-weight: 700; color: var(--color-on-surface);">Dambulla Red Onions</h3>
                                <div style="font-size: 13px; color: var(--color-on-surface-variant); margin-top: 2px;">✓ Mahaweli Green Growers</div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 12px; border-top: 1px solid var(--color-outline-variant);">
                                <div>
                                    <span style="font-size: 22px; font-weight: 800; color: var(--color-primary);">Rs. 380</span>
                                    <span style="font-size: 12px; color: var(--color-outline);"> / kg</span>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm btn-add-cart">Add to Cart</button>
                            </div>
                        </div>
                    </article>

                    <!-- Product 3: Jaffna Karutha Colomban Mangoes -->
                    <article style="background: #fff; border-radius: var(--radius-lg); border: 1px solid var(--color-outline-variant); overflow: hidden; display: flex; flex-direction: column; box-shadow: var(--shadow-sm);">
                        <div style="height: 220px; overflow: hidden; position: relative; background: var(--color-surface-container);">
                            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAfyj-2mGN8qUdjPjczGkJve7q9EwteooC0jljDGJ4JzmUDuCYh3Omvnf_Jf0bIQJpFXjeT9mL31ZqLcza9VwR2FG5GJou2F9mOds5zmvR8HxIZjtjsEvz8Ij1Vu83EmBVjqE9Iktknt1jzxtfIdKFkNX6bQ_JaMsgyCl3v7WlMTbSUXsZjGA1ajQj5DmKZtavnyRPQUW4Ajk-6U8JxLq1e4h-tPwWxIdRuRzeuCC63odWkZaO-y-pQbQ" alt="Jaffna Karutha Colomban Mangoes" style="width: 100%; height: 100%; object-fit: cover;">
                            <span class="badge badge-success" style="position: absolute; bottom: 12px; left: 12px; backdrop-filter: blur(6px); background: rgba(255,255,255,0.9); border: 1px solid rgba(0,0,0,0.1);">★ 5.0 (210)</span>
                        </div>
                        <div style="padding: 20px; display: flex; flex-direction: column; gap: 12px; flex: 1;">
                            <div>
                                <h3 style="font-size: 18px; font-weight: 700; color: var(--color-on-surface);">Jaffna Karutha Colomban</h3>
                                <div style="font-size: 13px; color: var(--color-on-surface-variant); margin-top: 2px;">✓ Vadamarachchi Orchards</div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 12px; border-top: 1px solid var(--color-outline-variant);">
                                <div>
                                    <span style="font-size: 22px; font-weight: 800; color: var(--color-primary);">Rs. 650</span>
                                    <span style="font-size: 12px; color: var(--color-outline);"> / kg</span>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm btn-add-cart">Add to Cart</button>
                            </div>
                        </div>
                    </article>

                    <!-- Product 4: Highland Gotu Kola Bundle -->
                    <article style="background: #fff; border-radius: var(--radius-lg); border: 1px solid var(--color-outline-variant); overflow: hidden; display: flex; flex-direction: column; box-shadow: var(--shadow-sm);">
                        <div style="height: 220px; overflow: hidden; position: relative; background: var(--color-surface-container);">
                            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuB_6w9nX6fgxxTHVL7Llwe3e4uGMjwg4SuQe-iNx921u52bQlo9fXn5_Q_mrcIrSIRw8JKbC-2Evt40aArAeVVxMwS0qcTP8HgpPxWkZJyy_-ufTF8WX4BwIMWhnSYqdrrRAeqnFmasoMq9anu_JLW23ti4Gm9oN2ipA0CvXhvGK6JsQZJGcqYHZNn-woLz44KGXXYFTcGMdn8QuTEk2Ox1aQnrzTkJGESmvRVRACUksnFDkFXO1zT9zQ" alt="Highland Gotu Kola Bundle" style="width: 100%; height: 100%; object-fit: cover;">
                            <span class="badge badge-success" style="position: absolute; bottom: 12px; left: 12px; backdrop-filter: blur(6px); background: rgba(255,255,255,0.9); border: 1px solid rgba(0,0,0,0.1);">★ 4.9 (82)</span>
                        </div>
                        <div style="padding: 20px; display: flex; flex-direction: column; gap: 12px; flex: 1;">
                            <div>
                                <h3 style="font-size: 18px; font-weight: 700; color: var(--color-on-surface);">Highland Gotu Kola Bundle</h3>
                                <div style="font-size: 13px; color: var(--color-on-surface-variant); margin-top: 2px;">✓ Kandy Organic Valley</div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 12px; border-top: 1px solid var(--color-outline-variant);">
                                <div>
                                    <span style="font-size: 22px; font-weight: 800; color: var(--color-primary);">Rs. 160</span>
                                    <span style="font-size: 12px; color: var(--color-outline);"> / bundle</span>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm btn-add-cart">Add to Cart</button>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section id="how-it-works" style="background-color: #fff; padding: 80px 24px;">
            <div style="max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 48px; text-align: center;">
                <div>
                    <span style="font-size: 12px; font-weight: 800; color: var(--color-primary); letter-spacing: 1px; text-transform: uppercase;">TRANSPARENT PROCESS</span>
                    <h2 style="font-size: 36px; font-weight: 800; color: var(--color-on-surface); margin-top: 4px;">How Harvestly Works</h2>
                    <p style="font-size: 16px; color: var(--color-on-surface-variant); margin-top: 8px;">Zero middlemen. Full freshness. Delivered directly from the soil to your kitchen.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px; text-align: left;">
                    <div style="padding: 32px; border-radius: var(--radius-xl); background: var(--color-surface-container-low); border: 1px solid var(--color-outline-variant); display: flex; flex-direction: column; gap: 16px;">
                        <div style="font-size: 40px;">👨‍🌾</div>
                        <span class="badge badge-info" style="align-self: flex-start;">Step 1</span>
                        <h3 style="font-size: 20px; font-weight: 700; color: var(--color-on-surface);">Farmer lists produce</h3>
                        <p style="font-size: 14px; color: var(--color-on-surface-variant); line-height: 1.6;">Local farmers list fresh harvests at fair producer prices with explicit harvest dates and region origins.</p>
                    </div>

                    <div style="padding: 32px; border-radius: var(--radius-xl); background: var(--color-surface-container-low); border: 1px solid var(--color-outline-variant); display: flex; flex-direction: column; gap: 16px;">
                        <div style="font-size: 40px;">💳</div>
                        <span class="badge badge-info" style="align-self: flex-start;">Step 2</span>
                        <h3 style="font-size: 20px; font-weight: 700; color: var(--color-on-surface);">Buyer orders & pays securely</h3>
                        <p style="font-size: 14px; color: var(--color-on-surface-variant); line-height: 1.6;">Choose fresh produce straight from the source with escrow protection that safeguards your funds until doorstep arrival.</p>
                    </div>

                    <div style="padding: 32px; border-radius: var(--radius-xl); background: var(--color-surface-container-low); border: 1px solid var(--color-outline-variant); display: flex; flex-direction: column; gap: 16px;">
                        <div style="font-size: 40px;">🚚</div>
                        <span class="badge badge-info" style="align-self: flex-start;">Step 3</span>
                        <h3 style="font-size: 20px; font-weight: 700; color: var(--color-on-surface);">Courier delivers to door</h3>
                        <p style="font-size: 14px; color: var(--color-on-surface-variant); line-height: 1.6;">Our verified Courier Partner picks up directly from the farm and delivers straight to your doorstep.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Trust Pillars Section -->
        <section id="trust-pillars" style="background-color: var(--color-surface-container-low); padding: 80px 24px;">
            <div style="max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 48px; text-align: center;">
                <div>
                    <span style="font-size: 12px; font-weight: 800; color: var(--color-primary); letter-spacing: 1px; text-transform: uppercase;">QUALITY ASSURANCE</span>
                    <h2 style="font-size: 36px; font-weight: 800; color: var(--color-on-surface); margin-top: 4px;">Why Buy with Harvestly?</h2>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; text-align: left;">
                    <div style="background: #fff; padding: 24px; border-radius: var(--radius-lg); border: 1px solid var(--color-outline-variant); display: flex; flex-direction: column; gap: 12px;">
                        <div style="font-size: 32px;">🛡️</div>
                        <h3 style="font-size: 18px; font-weight: 700; color: var(--color-on-surface);">Verified Courier Partners</h3>
                        <p style="font-size: 14px; color: var(--color-on-surface-variant); line-height: 1.5;">Every logistics partner is vetted with official Business Registration Number (BRN) checks.</p>
                    </div>

                    <div style="background: #fff; padding: 24px; border-radius: var(--radius-lg); border: 1px solid var(--color-outline-variant); display: flex; flex-direction: column; gap: 12px;">
                        <div style="font-size: 32px;">🔒</div>
                        <h3 style="font-size: 18px; font-weight: 700; color: var(--color-on-surface);">Secure Escrow Payments</h3>
                        <p style="font-size: 14px; color: var(--color-on-surface-variant); line-height: 1.5;">Transactions protected with fair farmer escrow release once doorstep delivery is confirmed.</p>
                    </div>

                    <div style="background: #fff; padding: 24px; border-radius: var(--radius-lg); border: 1px solid var(--color-outline-variant); display: flex; flex-direction: column; gap: 12px;">
                        <div style="font-size: 32px;">🏷️</div>
                        <h3 style="font-size: 18px; font-weight: 700; color: var(--color-on-surface);">Transparent Fair Pricing</h3>
                        <p style="font-size: 14px; color: var(--color-on-surface-variant); line-height: 1.5;">No predatory broker markups. 88%+ of revenue goes straight to Sri Lankan growers.</p>
                    </div>

                    <div style="background: #fff; padding: 24px; border-radius: var(--radius-lg); border: 1px solid var(--color-outline-variant); display: flex; flex-direction: column; gap: 12px;">
                        <div style="font-size: 32px;">🏡</div>
                        <h3 style="font-size: 18px; font-weight: 700; color: var(--color-on-surface);">Island-wide Doorstep Transit</h3>
                        <p style="font-size: 14px; color: var(--color-on-surface-variant); line-height: 1.5;">Standard door-to-door direct delivery connecting agricultural districts to urban households.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Partner Banners -->
        <section id="partners" style="background-color: #fff; padding: 80px 24px;">
            <div style="max-width: 1280px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px;">
                <div style="background: var(--color-primary); color: #fff; padding: 40px; border-radius: var(--radius-xl); display: flex; flex-direction: column; gap: 20px;">
                    <div style="font-size: 40px;">🚜</div>
                    <h3 style="font-size: 26px; font-weight: 800;">Are you a Sri Lankan Farmer?</h3>
                    <p style="font-size: 15px; color: rgba(255,255,255,0.9); line-height: 1.6;">List your seasonal harvest and connect directly with thousands of buyers across the island at fair producer prices with guaranteed escrow payouts.</p>
                    <a href="index.php?page=signup_farmer" class="btn" style="background: #fff; color: var(--color-primary); align-self: flex-start; margin-top: 8px;">Apply as Farmer &rarr;</a>
                </div>

                <div style="background: var(--color-surface-container); color: var(--color-on-surface); padding: 40px; border-radius: var(--radius-xl); display: flex; flex-direction: column; gap: 20px; border: 1px solid var(--color-outline-variant);">
                    <div style="font-size: 40px;">🚚</div>
                    <h3 style="font-size: 26px; font-weight: 800;">Join our Logistics Fleet</h3>
                    <p style="font-size: 15px; color: var(--color-on-surface-variant); line-height: 1.6;">Registered delivery company? Partner with us for guaranteed scheduled delivery routes directly from farms to customer kitchens.</p>
                    <a href="index.php?page=signup_courier" class="btn btn-primary" style="align-self: flex-start; margin-top: 8px;">Register Company Fleet &rarr;</a>
                </div>
            </div>
        </section>
    </main>
    <?php
}
