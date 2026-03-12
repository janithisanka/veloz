<?php
require_once 'includes/config.php';

$conn = getDBConnection();

// Suppress errors for tables that might not exist yet
mysqli_report(MYSQLI_REPORT_OFF);

// -------------------------------------------------------------------
// 1. Featured cars
// -------------------------------------------------------------------
$featuredCars = $conn->query("
    SELECT c.*, cb.name AS brand_name, cc.name AS category_name
    FROM cars c
    LEFT JOIN car_brands cb ON c.brand_id = cb.id
    LEFT JOIN car_categories cc ON c.category_id = cc.id
    WHERE c.is_featured = 1 AND c.is_available = 1
    ORDER BY c.created_at DESC
    LIMIT 6
");

// -------------------------------------------------------------------
// 2. Latest cars
// -------------------------------------------------------------------
$latestCars = $conn->query("
    SELECT c.*, cb.name AS brand_name
    FROM cars c
    LEFT JOIN car_brands cb ON c.brand_id = cb.id
    WHERE c.is_available = 1
    ORDER BY c.created_at DESC
    LIMIT 8
");

// -------------------------------------------------------------------
// 3. Brands
// -------------------------------------------------------------------
$brands = $conn->query("SELECT * FROM car_brands ORDER BY name LIMIT 12");

// -------------------------------------------------------------------
// 4. Aggregate counts
// -------------------------------------------------------------------
$totalCars      = $conn->query("SELECT COUNT(*) AS count FROM cars WHERE is_available = 1")->fetch_assoc()['count'];
$brandNewCount  = $conn->query("SELECT COUNT(*) AS count FROM cars WHERE is_available = 1 AND condition_type = 'brand_new'")->fetch_assoc()['count'];
$reconditionCount = $conn->query("SELECT COUNT(*) AS count FROM cars WHERE is_available = 1 AND condition_type = 'recondition'")->fetch_assoc()['count'];

// -------------------------------------------------------------------
// 5. Gallery items (table may not exist)
// -------------------------------------------------------------------
$galleryItems = [];
try {
    $galleryResult = $conn->query("
        SELECT * FROM gallery
        WHERE is_active = 1
        AND gallery_type IN ('delivery','imported','customer')
        ORDER BY sort_order ASC, id DESC
        LIMIT 20
    ");
    if ($galleryResult && $galleryResult->num_rows > 0) {
        while ($row = $galleryResult->fetch_assoc()) {
            $galleryItems[] = $row;
        }
    }
} catch (Exception $e) {
    // gallery table does not exist yet - that is fine
}

// -------------------------------------------------------------------
// 6. Posts (table may not exist)
// -------------------------------------------------------------------
$posts = [];
try {
    $postsResult = $conn->query("
        SELECT * FROM posts
        WHERE is_active = 1
        ORDER BY created_at DESC
        LIMIT 12
    ");
    if ($postsResult && $postsResult->num_rows > 0) {
        while ($row = $postsResult->fetch_assoc()) {
            $posts[] = $row;
        }
    }
} catch (Exception $e) {
    // posts table does not exist yet - that is fine
}

// -------------------------------------------------------------------
// 7. Hybrid count for category card
// -------------------------------------------------------------------
$hybridCountResult = $conn->query("SELECT COUNT(*) AS count FROM cars WHERE is_available = 1 AND fuel_type = 'hybrid'");
$hybridCount = ($hybridCountResult) ? $hybridCountResult->fetch_assoc()['count'] : 0;

$suvCountResult = $conn->query("SELECT COUNT(*) AS count FROM cars WHERE is_available = 1 AND body_type = 'suv'");
$suvCount = ($suvCountResult) ? $suvCountResult->fetch_assoc()['count'] : 0;

include 'includes/header.php';
?>

<!-- ============================================================
     INLINE STYLES - Homepage-specific dark-themed overrides
     ============================================================ -->
<style>
/* ---------- Hero ---------- */
.hp-hero {
    position: relative;
    min-height: 600px;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #0a0e17 0%, #0d1b2a 40%, #1a1a2e 100%);
    overflow: hidden;
    padding: 80px 0;
}
.hp-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 20% 50%, rgba(212,175,55,0.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(212,175,55,0.06) 0%, transparent 40%);
    pointer-events: none;
}
.hp-hero-inner {
    position: relative;
    z-index: 2;
    max-width: 720px;
}
.hp-hero-badge {
    display: inline-block;
    padding: 6px 16px;
    background: rgba(212,175,55,0.15);
    color: #e6c35a;
    font-size: 13px;
    font-weight: 600;
    border-radius: 20px;
    margin-bottom: 20px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.hp-hero h1 {
    font-size: clamp(2rem, 5vw, 3.2rem);
    color: #ffffff;
    line-height: 1.15;
    margin-bottom: 18px;
}
.hp-hero h1 span {
    background: linear-gradient(90deg, #d4af37, #e6c35a);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.hp-hero p {
    color: #94a3b8;
    font-size: 1.1rem;
    line-height: 1.7;
    margin-bottom: 32px;
}
.hp-hero-btns {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 48px;
}
.hp-hero-btns .btn-hero-primary {
    padding: 14px 32px;
    background: linear-gradient(135deg, #d4af37, #b8960c);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.hp-hero-btns .btn-hero-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(212,175,55,0.35);
}
.hp-hero-btns .btn-hero-outline {
    padding: 14px 32px;
    background: transparent;
    color: #e2e8f0;
    border: 2px solid rgba(148,163,184,0.3);
    border-radius: 8px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: border-color 0.2s, color 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.hp-hero-btns .btn-hero-outline:hover {
    border-color: #d4af37;
    color: #e6c35a;
}
.hp-hero-stats {
    display: flex;
    gap: 40px;
    flex-wrap: wrap;
}
.hp-hero-stat {
    text-align: left;
}
.hp-hero-stat .num {
    display: block;
    font-family: var(--font-heading);
    font-size: 2rem;
    font-weight: 800;
    color: #ffffff;
}
.hp-hero-stat .lbl {
    color: #64748b;
    font-size: 0.85rem;
    font-weight: 500;
}

/* ---------- Section commons ---------- */
.hp-section {
    padding: 80px 0;
    background: #0d1117;
}
.hp-section.alt {
    background: #0a0e17;
}
.hp-section-header {
    text-align: center;
    margin-bottom: 48px;
}
.hp-section-badge {
    display: inline-block;
    padding: 5px 14px;
    background: rgba(212,175,55,0.12);
    color: #e6c35a;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    border-radius: 20px;
    margin-bottom: 12px;
}
.hp-section-header h2 {
    color: #f1f5f9;
    font-size: clamp(1.5rem, 3vw, 2rem);
    margin-bottom: 8px;
}
.hp-section-header p {
    color: #64748b;
    font-size: 1rem;
    max-width: 560px;
    margin: 0 auto;
}

/* ---------- Why Choose Us ---------- */
.hp-features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
}
.hp-feature-card {
    background: #161b26;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    padding: 32px 24px;
    transition: transform 0.3s, border-color 0.3s;
}
.hp-feature-card:hover {
    transform: translateY(-4px);
    border-color: rgba(212,175,55,0.3);
}
.hp-feature-icon {
    width: 52px;
    height: 52px;
    background: rgba(212,175,55,0.12);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    color: #d4af37;
}
.hp-feature-icon svg {
    width: 26px;
    height: 26px;
}
.hp-feature-card h3 {
    color: #e2e8f0;
    font-size: 1.1rem;
    margin-bottom: 10px;
}
.hp-feature-card p {
    color: #64748b;
    font-size: 0.92rem;
    line-height: 1.65;
}

/* ---------- Car cards grid ---------- */
.hp-cars-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
}
.hp-car-card {
    background: #161b26;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.3s, border-color 0.3s;
}
.hp-car-card:hover {
    transform: translateY(-4px);
    border-color: rgba(212,175,55,0.25);
}
.hp-car-img {
    position: relative;
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: #1e2432;
}
.hp-car-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s;
}
.hp-car-card:hover .hp-car-img img {
    transform: scale(1.05);
}
.hp-car-img .no-image {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #475569;
    font-size: 0.9rem;
}
.hp-car-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.hp-car-badge.brand_new {
    background: #b8960c;
    color: #fff;
}
.hp-car-badge.recondition {
    background: #06b6d4;
    color: #fff;
}
.hp-car-badge.featured-tag {
    top: 12px;
    left: auto;
    right: 12px;
    background: rgba(250,204,21,0.9);
    color: #1a1a2e;
}
.hp-car-body {
    padding: 20px;
}
.hp-car-body h3 {
    color: #e2e8f0;
    font-size: 1.05rem;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.hp-car-year {
    color: #64748b;
    font-size: 0.85rem;
    margin-bottom: 14px;
}
.hp-car-specs {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 18px;
}
.hp-car-specs span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #94a3b8;
    font-size: 0.82rem;
}
.hp-car-specs svg {
    width: 14px;
    height: 14px;
    opacity: 0.6;
}
.hp-car-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: 1px solid rgba(255,255,255,0.06);
}
.hp-car-price {
    font-family: var(--font-heading);
    font-weight: 800;
    font-size: 1.1rem;
    color: #facc15;
}
.hp-car-footer .btn-details {
    padding: 8px 18px;
    background: rgba(212,175,55,0.12);
    color: #e6c35a;
    border: none;
    border-radius: 6px;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s;
}
.hp-car-footer .btn-details:hover {
    background: rgba(212,175,55,0.25);
}
.hp-section-footer {
    text-align: center;
    margin-top: 40px;
}
.hp-section-footer .btn-view-all {
    padding: 12px 32px;
    background: transparent;
    color: #e6c35a;
    border: 2px solid rgba(212,175,55,0.3);
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s, border-color 0.2s;
}
.hp-section-footer .btn-view-all:hover {
    background: rgba(212,175,55,0.1);
    border-color: #d4af37;
}

/* ---------- Pre-Order CTA ---------- */
.hp-preorder-cta {
    padding: 80px 0;
    background: linear-gradient(135deg, #1e3a5f 0%, #b8960c 50%, #9a7b0a 100%);
    text-align: center;
}
.hp-preorder-cta h2 {
    color: #ffffff;
    font-size: clamp(1.5rem, 3.5vw, 2.2rem);
    margin-bottom: 14px;
}
.hp-preorder-cta p {
    color: rgba(255,255,255,0.8);
    font-size: 1.05rem;
    margin-bottom: 32px;
    max-width: 560px;
    margin-left: auto;
    margin-right: auto;
}
.hp-preorder-cta .btn-preorder {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 16px 40px;
    background: #ffffff;
    color: #9a7b0a;
    border: none;
    border-radius: 8px;
    font-weight: 800;
    font-size: 15px;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
}
.hp-preorder-cta .btn-preorder:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
}

/* ---------- Slideshow / Gallery ---------- */
.hp-slideshow-section {
    padding: 80px 0;
    background: #0a0e17;
    overflow: hidden;
}
.hp-slideshow-track-wrapper {
    position: relative;
    overflow: hidden;
    margin-top: 12px;
}
.hp-slideshow-track {
    display: flex;
    gap: 20px;
    width: max-content;
    animation: hpSlideScroll 40s linear infinite;
}
.hp-slideshow-track:hover {
    animation-play-state: paused;
}
@keyframes hpSlideScroll {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.hp-gallery-card {
    flex: 0 0 300px;
    height: 220px;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    background: #161b26;
}
.hp-gallery-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.hp-gallery-card .overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 14px 16px;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    color: #e2e8f0;
    font-size: 0.85rem;
    font-weight: 600;
}
.hp-placeholder-msg {
    text-align: center;
    color: #475569;
    font-size: 0.95rem;
    padding: 40px 20px;
}

/* ---------- Posts slideshow ---------- */
.hp-posts-track {
    display: flex;
    gap: 20px;
    width: max-content;
    animation: hpPostsScroll 50s linear infinite;
}
.hp-posts-track:hover {
    animation-play-state: paused;
}
@keyframes hpPostsScroll {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.hp-post-card {
    flex: 0 0 320px;
    background: #161b26;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    overflow: hidden;
    transition: border-color 0.3s;
}
.hp-post-card:hover {
    border-color: rgba(212,175,55,0.25);
}
.hp-post-img {
    width: 100%;
    height: 180px;
    overflow: hidden;
    background: #1e2432;
}
.hp-post-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.hp-post-body {
    padding: 18px;
}
.hp-post-body h3 {
    color: #e2e8f0;
    font-size: 0.95rem;
    margin-bottom: 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.hp-post-date {
    color: #64748b;
    font-size: 0.8rem;
    margin-bottom: 10px;
}
.hp-post-body .read-more {
    color: #e6c35a;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}
.hp-post-body .read-more:hover {
    color: #93bbfd;
}

/* ---------- Categories ---------- */
.hp-categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}
.hp-category-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    padding: 36px 20px;
    background: #161b26;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    text-decoration: none;
    transition: transform 0.3s, border-color 0.3s;
}
.hp-category-card:hover {
    transform: translateY(-4px);
    border-color: rgba(212,175,55,0.3);
}
.hp-category-icon {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(212,175,55,0.12);
    border-radius: 12px;
    color: #d4af37;
}
.hp-category-icon svg {
    width: 28px;
    height: 28px;
}
.hp-category-card h3 {
    color: #e2e8f0;
    font-size: 1.05rem;
}
.hp-category-card .count {
    color: #64748b;
    font-size: 0.85rem;
    margin-top: -8px;
}

/* ---------- Brands ---------- */
.hp-brands-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 14px;
}
.hp-brand-btn {
    display: inline-flex;
    align-items: center;
    padding: 10px 24px;
    background: #161b26;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    color: #cbd5e1;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: background 0.2s, border-color 0.2s, color 0.2s;
}
.hp-brand-btn:hover {
    background: rgba(212,175,55,0.12);
    border-color: rgba(212,175,55,0.3);
    color: #e6c35a;
}

/* ---------- Final CTA ---------- */
.hp-final-cta {
    padding: 80px 0;
    background: linear-gradient(135deg, #0d1b2a 0%, #1a1a2e 100%);
    text-align: center;
}
.hp-final-cta h2 {
    color: #f1f5f9;
    font-size: clamp(1.5rem, 3.5vw, 2.2rem);
    margin-bottom: 14px;
}
.hp-final-cta p {
    color: #94a3b8;
    font-size: 1.05rem;
    max-width: 520px;
    margin: 0 auto 32px;
}
.hp-final-cta .btn-quote {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 16px 40px;
    background: linear-gradient(135deg, #d4af37, #b8960c);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 800;
    font-size: 15px;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
}
.hp-final-cta .btn-quote:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(212,175,55,0.35);
}

/* ---------- Responsive ---------- */
@media (max-width: 768px) {
    .hp-hero { min-height: auto; padding: 50px 0; }
    .hp-hero-stats { gap: 24px; }
    .hp-cars-grid { grid-template-columns: 1fr; }
    .hp-features-grid { grid-template-columns: 1fr 1fr; }
    .hp-categories-grid { grid-template-columns: 1fr 1fr; }
    .hp-gallery-card { flex: 0 0 240px; height: 170px; }
    .hp-post-card { flex: 0 0 260px; }
}
@media (max-width: 480px) {
    .hp-features-grid { grid-template-columns: 1fr; }
    .hp-categories-grid { grid-template-columns: 1fr; }
}
</style>


<!-- ============================================================
     SECTION 1 : HERO
     ============================================================ -->
<section class="hp-hero">
    <div class="container">
        <div class="hp-hero-inner">
            <span class="hp-hero-badge">Sri Lanka's #1 Japanese Vehicle Importer</span>
            <h1>Import Your Dream Car <span>from Japan</span></h1>
            <p>Get the lowest prices in Sri Lanka on brand new and reconditioned Japanese vehicles. Fully transparent pricing, rigorous inspections, and end-to-end support from auction to your doorstep in Colombo.</p>
            <div class="hp-hero-btns">
                <a href="cars.php" class="btn-hero-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M15 12H3m12 0l-4-4m4 4l-4 4M21 5v14"/></svg>
                    View Cars
                </a>
                <a href="preorder.php" class="btn-hero-outline">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M12 4v16m8-8H4"/></svg>
                    Pre-Order Now
                </a>
            </div>
            <div class="hp-hero-stats">
                <div class="hp-hero-stat">
                    <span class="num"><?php echo $totalCars; ?>+</span>
                    <span class="lbl">Cars Available</span>
                </div>
                <div class="hp-hero-stat">
                    <span class="num">500+</span>
                    <span class="lbl">Happy Customers</span>
                </div>
                <div class="hp-hero-stat">
                    <span class="num">10+</span>
                    <span class="lbl">Years Experience</span>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ============================================================
     SECTION 2 : WHY CHOOSE US
     ============================================================ -->
<section class="hp-section alt">
    <div class="container">
        <div class="hp-section-header">
            <span class="hp-section-badge">Why Choose Us</span>
            <h2>The Veloz AutoHaus Advantage</h2>
            <p>Four reasons thousands of Sri Lankans trust us with their next vehicle</p>
        </div>
        <div class="hp-features-grid">
            <!-- Lowest Prices -->
            <div class="hp-feature-card">
                <div class="hp-feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3>Lowest Prices Guaranteed</h3>
                <p>Direct imports from Japan mean no middlemen. We pass the savings directly to you with fully transparent pricing.</p>
            </div>
            <!-- Quality Assured -->
            <div class="hp-feature-card">
                <div class="hp-feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3>Quality Assured</h3>
                <p>Every vehicle undergoes rigorous inspection. We provide auction grade sheets and complete vehicle history reports.</p>
            </div>
            <!-- Fast Delivery -->
            <div class="hp-feature-card">
                <div class="hp-feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3>Fast Delivery</h3>
                <p>Quick processing and shipping. Your car arrives in Sri Lanka within 4-6 weeks of order confirmation.</p>
            </div>
            <!-- Full Support -->
            <div class="hp-feature-card">
                <div class="hp-feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3>Full Support</h3>
                <p>From selection to registration, we handle everything. Plus ongoing after-sales service and spare parts support.</p>
            </div>
        </div>
    </div>
</section>


<!-- ============================================================
     SECTION 3 : FEATURED CARS
     ============================================================ -->
<?php if ($featuredCars && $featuredCars->num_rows > 0): ?>
<section class="hp-section">
    <div class="container">
        <div class="hp-section-header">
            <span class="hp-section-badge">Featured Vehicles</span>
            <h2>Hand-Picked Premium Cars</h2>
            <p>Our top selections offering the best value for money</p>
        </div>
        <div class="hp-cars-grid">
            <?php while ($car = $featuredCars->fetch_assoc()): ?>
            <div class="hp-car-card">
                <div class="hp-car-img">
                    <?php if (!empty($car['main_image'])): ?>
                        <img src="uploads/cars/<?php echo htmlspecialchars($car['main_image']); ?>"
                             alt="<?php echo htmlspecialchars($car['brand_name'] . ' ' . $car['model']); ?>"
                             loading="lazy">
                    <?php else: ?>
                        <div class="no-image">No Image</div>
                    <?php endif; ?>
                    <span class="hp-car-badge <?php echo htmlspecialchars($car['condition_type']); ?>">
                        <?php echo $car['condition_type'] === 'brand_new' ? 'Brand New' : 'Recondition'; ?>
                    </span>
                    <?php if ($car['is_featured']): ?>
                        <span class="hp-car-badge featured-tag">Featured</span>
                    <?php endif; ?>
                </div>
                <div class="hp-car-body">
                    <h3><?php echo htmlspecialchars($car['brand_name'] . ' ' . $car['model']); ?></h3>
                    <p class="hp-car-year"><?php echo (int) $car['year']; ?></p>
                    <div class="hp-car-specs">
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <?php echo htmlspecialchars($car['engine_capacity']); ?>
                        </span>
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            <?php echo ucfirst(htmlspecialchars($car['fuel_type'])); ?>
                        </span>
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <?php echo ucfirst(htmlspecialchars($car['transmission'])); ?>
                        </span>
                    </div>
                    <div class="hp-car-footer">
                        <span class="hp-car-price"><?php echo formatPrice($car['price']); ?></span>
                        <a href="car-details.php?id=<?php echo (int) $car['id']; ?>" class="btn-details">View Details</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="hp-section-footer">
            <a href="cars.php" class="btn-view-all">View All Cars &rarr;</a>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ============================================================
     SECTION 4 : PRE-ORDER CTA
     ============================================================ -->
<section class="hp-preorder-cta">
    <div class="container">
        <h2>Can't Find Your Dream Car? We'll Import It For You!</h2>
        <p>Tell us the exact make, model, and specs you want and our team in Japan will source it at the best auction price.</p>
        <a href="preorder.php" class="btn-preorder">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M12 4v16m8-8H4"/></svg>
            Pre-Order Now
        </a>
    </div>
</section>


<!-- ============================================================
     SECTION 5 : CUSTOMER GALLERY SLIDESHOW
     ============================================================ -->
<section class="hp-slideshow-section" data-slideshow="gallery">
    <div class="container">
        <div class="hp-section-header">
            <span class="hp-section-badge">Gallery</span>
            <h2>Happy Customers &amp; Imported Cars</h2>
            <p>Deliveries, imports, and smiling customers from across Sri Lanka</p>
        </div>
    </div>
    <?php if (count($galleryItems) > 0): ?>
    <div class="hp-slideshow-track-wrapper">
        <div class="hp-slideshow-track" data-direction="left" data-speed="40">
            <?php
            // Duplicate items so the loop appears seamless
            $galleryDuped = array_merge($galleryItems, $galleryItems);
            foreach ($galleryDuped as $gi):
            ?>
            <div class="hp-gallery-card">
                <?php if (!empty($gi['image_path'])): ?>
                    <img src="uploads/gallery/<?php echo htmlspecialchars($gi['image_path']); ?>"
                         alt="<?php echo htmlspecialchars($gi['title'] ?? 'Gallery'); ?>"
                         loading="lazy">
                <?php endif; ?>
                <?php if (!empty($gi['title'])): ?>
                    <div class="overlay"><?php echo htmlspecialchars($gi['title']); ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="container">
        <p class="hp-placeholder-msg">Gallery photos coming soon. Stay tuned for customer deliveries and freshly imported vehicles!</p>
    </div>
    <?php endif; ?>
</section>


<!-- ============================================================
     SECTION 6 : POSTS / LATEST UPDATES SLIDESHOW
     ============================================================ -->
<section class="hp-section" data-slideshow="posts">
    <div class="container">
        <div class="hp-section-header">
            <span class="hp-section-badge">News &amp; Updates</span>
            <h2>Latest Updates</h2>
            <p>Stay informed with our latest news, promos, and social updates</p>
        </div>
    </div>
    <?php if (count($posts) > 0): ?>
    <div class="hp-slideshow-track-wrapper" style="padding: 0 20px;">
        <div class="hp-posts-track" data-direction="left" data-speed="50">
            <?php
            $postsDuped = array_merge($posts, $posts);
            foreach ($postsDuped as $post):
            ?>
            <div class="hp-post-card">
                <div class="hp-post-img">
                    <?php if (!empty($post['image_path'])): ?>
                        <img src="uploads/posts/<?php echo htmlspecialchars($post['image_path']); ?>"
                             alt="<?php echo htmlspecialchars($post['title']); ?>"
                             loading="lazy">
                    <?php endif; ?>
                </div>
                <div class="hp-post-body">
                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                    <p class="hp-post-date"><?php echo date('M d, Y', strtotime($post['created_at'] ?? 'now')); ?></p>
                    <?php if (!empty($post['external_link'])): ?>
                        <a href="<?php echo htmlspecialchars($post['external_link']); ?>" class="read-more" target="_blank">Read More &rarr;</a>
                    <?php else: ?>
                        <span class="read-more" style="color:#475569;">
                            <?php echo ucfirst(htmlspecialchars($post['post_type'] ?? 'news')); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="container">
        <p class="hp-placeholder-msg">No updates at the moment. Check back soon for the latest news and promotions!</p>
    </div>
    <?php endif; ?>
</section>


<!-- ============================================================
     SECTION 7 : BROWSE CATEGORIES
     ============================================================ -->
<section class="hp-section alt">
    <div class="container">
        <div class="hp-section-header">
            <span class="hp-section-badge">Browse by Type</span>
            <h2>Find Your Perfect Car</h2>
        </div>
        <div class="hp-categories-grid">
            <!-- Brand New -->
            <a href="cars.php?condition=brand_new" class="hp-category-card">
                <div class="hp-category-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <h3>Brand New</h3>
                <span class="count"><?php echo $brandNewCount; ?> vehicles</span>
            </a>
            <!-- Reconditioned -->
            <a href="cars.php?condition=recondition" class="hp-category-card">
                <div class="hp-category-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3>Reconditioned</h3>
                <span class="count"><?php echo $reconditionCount; ?> vehicles</span>
            </a>
            <!-- Hybrid -->
            <a href="cars.php?fuel=hybrid" class="hp-category-card">
                <div class="hp-category-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3>Hybrid</h3>
                <span class="count"><?php echo $hybridCount; ?> vehicles</span>
            </a>
            <!-- SUV -->
            <a href="cars.php?body=suv" class="hp-category-card">
                <div class="hp-category-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                </div>
                <h3>SUV</h3>
                <span class="count"><?php echo $suvCount; ?> vehicles</span>
            </a>
        </div>
    </div>
</section>


<!-- ============================================================
     SECTION 8 : POPULAR BRANDS
     ============================================================ -->
<?php if ($brands && $brands->num_rows > 0): ?>
<section class="hp-section">
    <div class="container">
        <div class="hp-section-header">
            <span class="hp-section-badge">Popular Brands</span>
            <h2>Japanese Quality You Can Trust</h2>
        </div>
        <div class="hp-brands-grid">
            <?php while ($brand = $brands->fetch_assoc()): ?>
                <a href="cars.php?brand=<?php echo (int) $brand['id']; ?>" class="hp-brand-btn">
                    <?php echo htmlspecialchars($brand['name']); ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ============================================================
     SECTION 9 : LATEST CARS
     ============================================================ -->
<?php if ($latestCars && $latestCars->num_rows > 0): ?>
<section class="hp-section alt">
    <div class="container">
        <div class="hp-section-header">
            <span class="hp-section-badge">New Arrivals</span>
            <h2>Recently Added Vehicles</h2>
            <p>The newest additions to our inventory, fresh from Japan</p>
        </div>
        <div class="hp-cars-grid">
            <?php while ($car = $latestCars->fetch_assoc()): ?>
            <div class="hp-car-card">
                <div class="hp-car-img">
                    <?php if (!empty($car['main_image'])): ?>
                        <img src="uploads/cars/<?php echo htmlspecialchars($car['main_image']); ?>"
                             alt="<?php echo htmlspecialchars($car['brand_name'] . ' ' . $car['model']); ?>"
                             loading="lazy">
                    <?php else: ?>
                        <div class="no-image">No Image</div>
                    <?php endif; ?>
                    <span class="hp-car-badge <?php echo htmlspecialchars($car['condition_type']); ?>">
                        <?php echo $car['condition_type'] === 'brand_new' ? 'Brand New' : 'Recondition'; ?>
                    </span>
                </div>
                <div class="hp-car-body">
                    <h3><?php echo htmlspecialchars($car['brand_name'] . ' ' . $car['model']); ?></h3>
                    <p class="hp-car-year"><?php echo (int) $car['year']; ?></p>
                    <div class="hp-car-specs">
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <?php echo htmlspecialchars($car['engine_capacity']); ?>
                        </span>
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            <?php echo ucfirst(htmlspecialchars($car['fuel_type'])); ?>
                        </span>
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <?php echo ucfirst(htmlspecialchars($car['transmission'])); ?>
                        </span>
                    </div>
                    <div class="hp-car-footer">
                        <span class="hp-car-price"><?php echo formatPrice($car['price']); ?></span>
                        <a href="car-details.php?id=<?php echo (int) $car['id']; ?>" class="btn-details">View Details</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ============================================================
     SECTION 10 : FINAL CTA
     ============================================================ -->
<section class="hp-final-cta">
    <div class="container">
        <h2>Ready to Import Your Next Vehicle?</h2>
        <p>Get a no-obligation quote today. Our team will find the best deal from Japanese auctions tailored to your budget.</p>
        <a href="quote.php" class="btn-quote">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Get a Free Quote
        </a>
    </div>
</section>


<!-- ============================================================
     MINIMAL JS - Auto-scroll enhancement for slideshows
     ============================================================ -->
<script>
(function () {
    // Dynamically adjust animation duration based on content width
    document.querySelectorAll('.hp-slideshow-track, .hp-posts-track').forEach(function (track) {
        var speed = parseInt(track.getAttribute('data-speed')) || 40;
        var children = track.children.length;
        // More items = slower overall scroll so each card is visible
        if (children > 0) {
            var duration = Math.max(speed, children * 3);
            track.style.animationDuration = duration + 's';
        }
    });

    // Pause on touch for mobile
    document.querySelectorAll('.hp-slideshow-track-wrapper').forEach(function (wrapper) {
        var track = wrapper.querySelector('.hp-slideshow-track, .hp-posts-track');
        if (!track) return;
        wrapper.addEventListener('touchstart', function () {
            track.style.animationPlayState = 'paused';
        }, { passive: true });
        wrapper.addEventListener('touchend', function () {
            track.style.animationPlayState = 'running';
        }, { passive: true });
    });
})();
</script>

<?php include 'includes/footer.php'; ?>
