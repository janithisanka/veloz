<?php
$conn = getDBConnection();
$settingsResult = $conn->query("SELECT * FROM site_settings");
$siteSettings = [];
while ($row = $settingsResult->fetch_assoc()) {
    $siteSettings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : ''; ?>Veloz AutoHaus Colombo - Japanese Vehicle Imports Sri Lanka</title>
    <meta name="description" content="Veloz AutoHaus Colombo - Sri Lanka's trusted Japanese vehicle importer. Brand new and reconditioned Toyota, Honda, Nissan, Suzuki, and more at competitive prices. Part of the Veloz AutoHaus family.">
    <meta name="keywords" content="Japanese vehicles Sri Lanka, car imports Colombo, brand new cars Sri Lanka, reconditioned vehicles, Toyota Sri Lanka, Honda Sri Lanka, Veloz AutoHaus">
    <meta name="theme-color" content="#121217">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ===== Top Bar ===== */
        .top-bar {
            background: #121217;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 8px 0;
            font-size: 13px;
            color: #a1a1aa;
            font-family: 'Inter', sans-serif;
        }
        .top-bar .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .top-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .top-bar-left a,
        .top-bar-left span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #a1a1aa;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .top-bar-left a:hover {
            color: #d4af37;
        }
        .top-bar-left svg {
            flex-shrink: 0;
        }
        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .top-bar-right a {
            color: #71717a;
            transition: color 0.25s ease;
            display: inline-flex;
            align-items: center;
        }
        .top-bar-right a:hover {
            color: #d4af37;
        }

        /* ===== Header ===== */
        .header {
            background: #1d1d22;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            box-shadow: 0 2px 20px rgba(0,0,0,0.3);
            font-family: 'Inter', sans-serif;
        }
        .header .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 72px;
        }

        /* ===== Logo ===== */
        .logo {
            text-decoration: none;
            display: flex;
            flex-direction: column;
            line-height: 1;
        }
        .logo-text {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 26px;
            color: #ffffff;
            letter-spacing: -0.5px;
        }
        .logo-text .logo-blue {
            color: #d4af37;
        }
        .logo-sub {
            font-family: 'Inter', sans-serif;
            font-size: 10px;
            font-weight: 600;
            color: #eab308;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* ===== Navigation ===== */
        .main-nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 4px;
        }
        .main-nav a {
            display: block;
            padding: 10px 16px;
            color: #d4d4d8;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .main-nav a:hover {
            color: #ffffff;
            background: rgba(255,255,255,0.06);
        }
        .main-nav a.active {
            color: #d4af37;
            background: rgba(212,175,55,0.1);
        }

        /* ===== Header Actions ===== */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .btn-get-quote {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: #d4af37;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.25s ease;
            font-family: 'Inter', sans-serif;
            border: none;
            cursor: pointer;
        }
        .btn-get-quote:hover {
            background: #b8960c;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(212,175,55,0.35);
        }

        /* ===== Mobile Menu Button ===== */
        .mobile-menu-btn {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
            width: 42px;
            height: 42px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 8px;
            cursor: pointer;
            padding: 0;
            transition: background 0.2s ease;
        }
        .mobile-menu-btn:hover {
            background: rgba(255,255,255,0.1);
        }
        .mobile-menu-btn span {
            display: block;
            width: 20px;
            height: 2px;
            background: #d4d4d8;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        .mobile-menu-btn.active span:nth-child(1) {
            transform: translateY(7px) rotate(45deg);
        }
        .mobile-menu-btn.active span:nth-child(2) {
            opacity: 0;
        }
        .mobile-menu-btn.active span:nth-child(3) {
            transform: translateY(-7px) rotate(-45deg);
        }

        /* ===== Responsive ===== */
        @media (max-width: 1024px) {
            .main-nav a {
                padding: 10px 12px;
                font-size: 13px;
            }
        }
        @media (max-width: 900px) {
            .mobile-menu-btn {
                display: flex;
            }
            .main-nav {
                display: none;
                position: absolute;
                top: 72px;
                left: 0;
                right: 0;
                background: #1d1d22;
                border-top: 1px solid rgba(255,255,255,0.06);
                border-bottom: 1px solid rgba(255,255,255,0.06);
                box-shadow: 0 10px 30px rgba(0,0,0,0.4);
                padding: 12px 20px;
                z-index: 999;
            }
            .main-nav.open {
                display: block;
            }
            .main-nav ul {
                flex-direction: column;
                gap: 2px;
            }
            .main-nav a {
                padding: 12px 16px;
                font-size: 15px;
                border-radius: 8px;
            }
            .btn-get-quote-desktop {
                display: none;
            }
        }
        @media (max-width: 600px) {
            .top-bar-left {
                gap: 12px;
                font-size: 12px;
            }
            .top-bar-right {
                gap: 10px;
            }
            .top-bar-left .top-bar-email {
                display: none;
            }
            .logo-text {
                font-size: 22px;
            }
            .logo-sub {
                font-size: 9px;
                letter-spacing: 2.5px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="top-bar-left">
                    <a href="tel:<?php echo htmlspecialchars($siteSettings['phone'] ?? '+94760881409'); ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                            <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <?php echo htmlspecialchars($siteSettings['phone'] ?? '+94 76 088 1409'); ?>
                    </a>
                    <a href="mailto:<?php echo htmlspecialchars($siteSettings['email'] ?? 'info@velozautohaus.lk'); ?>" class="top-bar-email">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                            <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <?php echo htmlspecialchars($siteSettings['email'] ?? 'info@velozautohaus.lk'); ?>
                    </a>
                </div>
                <div class="top-bar-right">
                    <?php if (!empty($siteSettings['facebook'])): ?>
                        <a href="<?php echo htmlspecialchars($siteSettings['facebook']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($siteSettings['instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($siteSettings['instagram']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($siteSettings['youtube'])): ?>
                        <a href="<?php echo htmlspecialchars($siteSettings['youtube']); ?>" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                                <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($siteSettings['tiktok'])): ?>
                        <a href="<?php echo htmlspecialchars($siteSettings['tiktok']); ?>" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($siteSettings['whatsapp'])): ?>
                        <a href="https://wa.me/<?php echo htmlspecialchars($siteSettings['whatsapp']); ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <a href="<?php echo SITE_URL; ?>" class="logo">
                    <span class="logo-text">Veloz<span class="logo-blue">AutoHaus</span></span>
                    <span class="logo-sub">Colombo</span>
                </a>

                <!-- Navigation -->
                <nav class="main-nav" id="mainNav">
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' && !isset($_GET['page']) ? 'active' : ''; ?>">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/cars.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'cars.php' && !isset($_GET['condition']) ? 'active' : ''; ?>">Our Cars</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/cars.php?condition=brand_new" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'cars.php' && isset($_GET['condition']) && $_GET['condition'] === 'brand_new') ? 'active' : ''; ?>">Brand New</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/cars.php?condition=recondition" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'cars.php' && isset($_GET['condition']) && $_GET['condition'] === 'recondition') ? 'active' : ''; ?>">Reconditioned</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/cars.php?condition=pre_order" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'cars.php' && isset($_GET['condition']) && $_GET['condition'] === 'pre_order') ? 'active' : ''; ?>">Pre-Order</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                    </ul>
                </nav>

                <!-- Header Actions -->
                <div class="header-actions">
                    <a href="<?php echo SITE_URL; ?>/quote.php" class="btn-get-quote btn-get-quote-desktop">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Get Quote
                    </a>
                    <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
    </header>
