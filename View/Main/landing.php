<?php
/**
 * Harvestly public landing page.
 * Category tiles, featured products and counts all come from the database.
 * If a table has no records the section shows a proper empty state - no fake
 * fallback products are ever displayed.
 */

$categories = $categories ?? [];
$featuredProducts = $featuredProducts ?? [];

/* Illustration shown for a category tile. Every file below exists in assets/images. */
$categoryImage = static function (string $name): string {
    $t = strtolower($name);
    if (str_contains($t, 'fruit')) return 'assets/images/fruits.jpg';
    if (str_contains($t, 'leaf') || str_contains($t, 'green')) return 'assets/images/leafy green.jpg';
    if (str_contains($t, 'spice') || str_contains($t, 'staple')) return 'assets/images/spices.jpg';
    if (str_contains($t, 'veg')) return 'assets/images/vegfr.jpg';
    return 'assets/images/vegetables and fruits.jpg';
};
?>

<main class="landing-page flex-1 flex flex-col relative w-full bg-surface">

    <!-- Hero -->
    <section class="landing-hero" style="background-color:var(--color-background);padding:60px 24px 80px;overflow:hidden">
        <div style="max-width:1280px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:48px;align-items:center">
            <div style="display:flex;flex-direction:column;gap:24px">
                <div style="display:inline-flex;align-items:center;gap:8px;align-self:flex-start;padding:6px 16px;border-radius:9999px;background-color:var(--color-secondary-container);color:var(--color-on-secondary-container);font-size:13px;font-weight:700">
                    Direct Farm-to-Table Marketplace
                </div>
                <h1 style="font-size:46px;font-weight:800;line-height:1.15;color:var(--color-on-surface);letter-spacing:-.02em;margin:0">
                    Connecting Sri Lankan Farmers
                    <span style="color:var(--color-primary);display:block">Directly to Your Doorstep</span>
                </h1>
                <p style="font-size:18px;color:var(--color-on-surface-variant);line-height:1.6;max-width:540px;margin:0">
                    Browse fresh produce from approved Farmers and have it delivered by verified
                    Courier Partner organisations.
                </p>

                <div style="display:flex;gap:14px;flex-wrap:wrap">
                    <a href="index.php?page=products" class="btn btn-primary" style="padding:14px 28px;font-size:15px">Browse Products &rarr;</a>
                    <a href="index.php?page=role_select" class="btn btn-outline" style="padding:14px 28px;font-size:15px">Join Harvestly</a>
                </div>
            </div>

            <div class="landing-hero-media" style="position:relative">
                <div style="border-radius:var(--radius-xl);overflow:hidden;box-shadow:var(--shadow-lg);border:4px solid #fff;background:var(--color-surface-container-high);position:relative;aspect-ratio:4/3.5">
                    <img src="<?= e(url('assets/images/main image.jpg')) ?>" alt="Fresh Sri Lankan produce harvest" style="width:100%;height:100%;object-fit:cover">
                </div>
            </div>
        </div>
    </section>

    <!-- Categories, read from product_categories -->
    <section id="categories" style="background-color:#fff;padding:60px 24px">
        <div style="max-width:1280px;margin:0 auto;display:flex;flex-direction:column;gap:32px">
            <div style="text-align:center">
                <span style="font-size:12px;font-weight:800;color:var(--color-primary);letter-spacing:1px;text-transform:uppercase">AGRICULTURAL PRODUCE</span>
                <h2 style="font-size:32px;font-weight:800;color:var(--color-on-surface);margin:4px 0 0">Explore Product Categories</h2>
            </div>

            <?php if (!$categories): ?>
                <p class="notice" style="text-align:center;margin:0">
                    No product categories have been published yet. Please check back soon.
                </p>
            <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px">
                <?php foreach ($categories as $category):
                    $name = (string)$category['category_name'];
                    $count = (int)($category['product_count'] ?? 0);
                ?>
                <a href="index.php?page=products&amp;category=<?= urlencode($name) ?>"
                   style="text-decoration:none;background:var(--color-surface-container-low);padding:20px;border-radius:var(--radius-lg);border:1px solid var(--color-outline-variant);text-align:center;display:flex;flex-direction:column;align-items:center;gap:12px">
                    <img src="<?= e(url($categoryImage($name))) ?>" alt="<?= e($name) ?>" style="width:100px;height:100px;object-fit:cover;border-radius:var(--radius-md)">
                    <h3 style="font-size:18px;font-weight:700;color:var(--color-on-surface);margin:0"><?= e($name) ?></h3>
                    <span style="font-size:12px;color:var(--color-outline)">
                        <?= $count ?> <?= $count === 1 ? 'listing' : 'listings' ?>
                    </span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Featured produce, read from products -->
    <section id="featured-produce" style="background-color:var(--color-surface-container-low);padding:80px 24px">
        <div style="max-width:1280px;margin:0 auto;display:flex;flex-direction:column;gap:32px">
            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-end;gap:16px;border-bottom:1px solid var(--color-outline-variant);padding-bottom:16px">
                <div>
                    <span style="font-size:12px;font-weight:800;color:var(--color-primary);letter-spacing:1px;text-transform:uppercase">DIRECT HARVEST</span>
                    <h2 style="font-size:32px;font-weight:800;color:var(--color-on-surface);margin:4px 0 0">Fresh Today from Local Farms</h2>
                </div>
                <a href="index.php?page=products" class="btn btn-outline">View All Products &rarr;</a>
            </div>

            <?php if (!$featuredProducts): ?>
                <p class="notice" style="text-align:center;margin:0">
                    No produce listings are available right now. Farmers are still preparing their
                    harvests &mdash; please check back soon.
                </p>
            <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px">
                <?php foreach ($featuredProducts as $product):
                    $image = trim((string)($product['image_path'] ?? ''));
                    if ($image === '') {
                        $image = getProductImage((string)$product['title'], (string)$product['category_name']);
                    }
                    if (!preg_match('#^https?://#i', $image) && !str_starts_with($image, '/')) {
                        $image = url($image);
                    }
                ?>
                <article style="background:#fff;border-radius:var(--radius-lg);border:1px solid var(--color-outline-variant);overflow:hidden;display:flex;flex-direction:column;box-shadow:var(--shadow-sm)">
                    <a href="index.php?page=product_details&amp;id=<?= (int)$product['id'] ?>" style="display:block;height:220px;overflow:hidden;position:relative;background:var(--color-surface-container)">
                        <img src="<?= e($image) ?>" alt="<?= e($product['title']) ?>" style="width:100%;height:100%;object-fit:cover">
                        <span class="badge badge-info" style="position:absolute;bottom:12px;left:12px">
                            <?= e(harvestlyStatusLabel((string)$product['listing_type'])) ?>
                        </span>
                    </a>
                    <div style="padding:20px;display:flex;flex-direction:column;gap:12px;flex:1">
                        <div>
                            <h3 style="font-size:18px;font-weight:700;color:var(--color-on-surface);margin:0">
                                <?= e($product['title']) ?>
                            </h3>
                            <div style="font-size:13px;color:var(--color-on-surface-variant);margin-top:2px">
                                <?= e($product['farmer_name']) ?>
                                <?php if (!empty($product['pickup_district'])): ?>
                                    &middot; <?= e($product['pickup_district']) ?> district
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto;padding-top:12px;border-top:1px solid var(--color-outline-variant)">
                            <div>
                                <span style="font-size:22px;font-weight:800;color:var(--color-primary)"><?= harvestlyMoney($product['unit_price']) ?></span>
                                <span style="font-size:12px;color:var(--color-outline)"> / <?= e($product['unit_label']) ?></span>
                            </div>
                            <a href="index.php?page=product_details&amp;id=<?= (int)$product['id'] ?>" class="btn btn-primary btn-sm">View Product</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- How it works -->
    <section id="how-it-works" class="how-it-works-section">
        <div class="how-it-works-inner">
            <div class="how-it-works-intro">
                <span class="section-eyebrow">HOW IT WORKS</span>
                <h2>How Harvestly Works</h2>
                <p>Farmers, Buyers and Courier Partners in one marketplace.</p>
            </div>

            <div class="how-it-works-steps">
                <article class="how-it-works-step">
                    <div class="step-number">01</div>
                    <div class="step-icon">&#127793;</div>
                    <span class="step-role">Farmer &amp; Buyer</span>
                    <h3>List &amp; Discover Products</h3>
                    <p>Farmers list produce. Buyers browse and order.</p>
                </article>

                <article class="how-it-works-step">
                    <div class="step-number">02</div>
                    <div class="step-icon">&#128666;</div>
                    <span class="step-role">Order &amp; Delivery</span>
                    <h3>Order &amp; Deliver</h3>
                    <p>A Courier Partner delivers to your district.</p>
                </article>

                <article class="how-it-works-step">
                    <div class="step-number">03</div>
                    <div class="step-icon">&#10003;</div>
                    <span class="step-role">Buyer Community</span>
                    <h3>Confirm &amp; Review</h3>
                    <p>Confirm receipt, then review or report an issue.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- About -->
    <section id="about-us" style="background-color:var(--color-surface-container-low);padding:80px 24px">
        <div style="max-width:1280px;margin:0 auto;display:flex;flex-direction:column;gap:32px">
            <div style="text-align:center;max-width:800px;margin:0 auto">
                <span style="font-size:12px;font-weight:800;color:var(--color-primary);letter-spacing:1px;text-transform:uppercase">ABOUT HARVESTLY</span>
                <h2 style="font-size:36px;font-weight:800;color:var(--color-on-surface);margin:4px 0 0">Empowering Sri Lanka's Agricultural Ecosystem</h2>
                <p style="font-size:16px;color:var(--color-on-surface-variant);margin-top:16px;line-height:1.7">
                    Harvestly is a farmer-to-buyer marketplace connecting local Sri Lankan growers,
                    household Buyers, and approved Courier Partner delivery organisations.
                </p>
            </div>
        </div>
    </section>
</main>
