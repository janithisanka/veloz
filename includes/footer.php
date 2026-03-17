    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-main">
            <div class="container">
                <div class="footer-grid">
                    <!-- Column 1: About -->
                    <div class="footer-col footer-about">
                        <a href="<?php echo SITE_URL; ?>" class="footer-logo">
                            <span class="footer-logo-text">Veloz<span class="footer-logo-blue">Autohaus</span></span>
                            <span class="footer-logo-sub">Colombo</span>
                        </a>
                        <p class="footer-about-text">
                            <?php echo htmlspecialchars($siteSettings['about_text'] ?? 'Sri Lanka\'s trusted Japanese vehicle importer. We bring you quality brand new and reconditioned vehicles at competitive prices.'); ?>
                        </p>
                        <p class="footer-family-text">
                            Proudly part of the <a href="https://velozautohaus.com.au/" target="_blank" rel="noopener noreferrer" style="color:#d4af37;text-decoration:none;font-weight:700;">Veloz Autohaus Australia</a> family. Delivering the same quality and trust to Sri Lanka.
                        </p>
                        <div class="footer-social">
                            <?php if (!empty($siteSettings['facebook'])): ?>
                                <a href="<?php echo htmlspecialchars($siteSettings['facebook']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="social-icon">
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($siteSettings['instagram'])): ?>
                                <a href="<?php echo htmlspecialchars($siteSettings['instagram']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="social-icon">
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($siteSettings['whatsapp'])): ?>
                                <a href="https://wa.me/<?php echo htmlspecialchars($siteSettings['whatsapp']); ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" class="social-icon social-whatsapp">
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($siteSettings['youtube'])): ?>
                                <a href="<?php echo htmlspecialchars($siteSettings['youtube']); ?>" target="_blank" rel="noopener noreferrer" aria-label="YouTube" class="social-icon">
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                                        <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($siteSettings['tiktok'])): ?>
                                <a href="<?php echo htmlspecialchars($siteSettings['tiktok']); ?>" target="_blank" rel="noopener noreferrer" aria-label="TikTok" class="social-icon">
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                                        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Column 2: Quick Links -->
                    <div class="footer-col footer-links">
                        <h4 class="footer-heading">Quick Links</h4>
                        <ul>
                            <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/cars.php">Our Cars</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/cars.php?condition=brand_new">Brand New</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/cars.php?condition=recondition">Reconditioned</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/cars.php?condition=pre_order">Pre-Order</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/quote.php">Request Quote</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/contact.php">Contact Us</a></li>
                        </ul>
                    </div>

                    <!-- Column 3: Popular Brands -->
                    <div class="footer-col footer-links">
                        <h4 class="footer-heading">Popular Brands</h4>
                        <ul>
                            <li><a href="<?php echo SITE_URL; ?>/cars.php?brand=1">Toyota</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/cars.php?brand=2">Honda</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/cars.php?brand=3">Nissan</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/cars.php?brand=4">Mazda</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/cars.php?brand=5">Suzuki</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/cars.php?brand=6">Mitsubishi</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/cars.php?brand=7">Subaru</a></li>
                        </ul>
                    </div>

                    <!-- Column 4: Contact Us -->
                    <div class="footer-col footer-contact">
                        <h4 class="footer-heading">Contact Us</h4>
                        <ul>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span><?php echo nl2br(htmlspecialchars($siteSettings['address'] ?? 'Colombo, Sri Lanka')); ?></span>
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <a href="tel:<?php echo htmlspecialchars($siteSettings['phone'] ?? '+94760881409'); ?>">
                                    <?php echo htmlspecialchars($siteSettings['phone'] ?? '+94 76 088 1409'); ?>
                                </a>
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                <a href="https://wa.me/<?php echo htmlspecialchars($siteSettings['whatsapp'] ?? '94760881409'); ?>">
                                    WhatsApp: <?php echo htmlspecialchars($siteSettings['phone'] ?? '+94 76 088 1409'); ?>
                                </a>
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <a href="mailto:<?php echo htmlspecialchars($siteSettings['email'] ?? 'info@velozautohaus.lk'); ?>">
                                    <?php echo htmlspecialchars($siteSettings['email'] ?? 'info@velozautohaus.lk'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> Veloz Autohaus Colombo. All rights reserved.</p>
                    <p class="footer-bottom-tagline">Japanese Vehicle Imports | Part of the Veloz Autohaus Family</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/<?php echo htmlspecialchars($siteSettings['whatsapp'] ?? '94760881409'); ?>?text=<?php echo rawurlencode('Hi, I\'m interested in your vehicles at Veloz Autohaus Colombo'); ?>" class="whatsapp-float" target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp">
        <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
        <span class="whatsapp-float-text">Chat with us</span>
    </a>

    <style>
        /* ===== Footer ===== */
        .site-footer {
            font-family: 'Inter', sans-serif;
            background: #121217;
            color: #a1a1aa;
        }
        .site-footer .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .footer-main {
            padding: 60px 0 40px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1.4fr 0.8fr 0.8fr 1fr;
            gap: 48px;
        }

        /* Footer Logo */
        .footer-logo {
            display: flex;
            flex-direction: column;
            text-decoration: none;
            line-height: 1;
            margin-bottom: 20px;
        }
        .footer-logo-text {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 24px;
            color: #ffffff;
            letter-spacing: -0.5px;
        }
        .footer-logo-blue {
            color: #d4af37;
        }
        .footer-logo-sub {
            font-family: 'Inter', sans-serif;
            font-size: 9px;
            font-weight: 600;
            color: #eab308;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* Footer About */
        .footer-about-text {
            font-size: 14px;
            line-height: 1.7;
            color: #71717a;
            margin-bottom: 12px;
        }
        .footer-family-text {
            font-size: 13px;
            line-height: 1.6;
            color: #52525b;
            border-left: 2px solid #d4af37;
            padding-left: 12px;
            margin-bottom: 20px;
        }
        .footer-family-text strong {
            color: #a1a1aa;
        }

        /* Footer Social Icons */
        .footer-social {
            display: flex;
            gap: 10px;
        }
        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            color: #a1a1aa;
            transition: all 0.25s ease;
        }
        .social-icon:hover {
            background: #d4af37;
            border-color: #d4af37;
            color: #ffffff;
            transform: translateY(-2px);
        }
        .social-icon.social-whatsapp:hover {
            background: #25d366;
            border-color: #25d366;
        }

        /* Footer Headings */
        .footer-heading {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
            margin: 0 0 20px 0;
            position: relative;
            padding-bottom: 12px;
        }
        .footer-heading::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 30px;
            height: 2px;
            background: #d4af37;
            border-radius: 2px;
        }

        /* Footer Links */
        .footer-links ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .footer-links li {
            margin-bottom: 10px;
        }
        .footer-links a {
            color: #71717a;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .footer-links a::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #3f3f46;
            transition: background 0.2s ease;
        }
        .footer-links a:hover {
            color: #d4af37;
            padding-left: 4px;
        }
        .footer-links a:hover::before {
            background: #d4af37;
        }

        /* Footer Contact */
        .footer-contact ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .footer-contact li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 16px;
            font-size: 14px;
            line-height: 1.6;
            color: #71717a;
        }
        .footer-contact li svg {
            flex-shrink: 0;
            margin-top: 2px;
            color: #d4af37;
        }
        .footer-contact a {
            color: #71717a;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .footer-contact a:hover {
            color: #d4af37;
        }

        /* Footer Bottom */
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.06);
            padding: 20px 0;
        }
        .footer-bottom-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .footer-bottom p {
            font-size: 13px;
            color: #52525b;
            margin: 0;
        }
        .footer-bottom-tagline {
            font-size: 12px !important;
            color: #3f3f46 !important;
        }

        /* ===== WhatsApp Floating Button ===== */
        .whatsapp-float {
            position: fixed !important;
            bottom: 24px !important;
            right: 24px !important;
            z-index: 9999 !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            background: #25d366 !important;
            color: #ffffff !important;
            padding: 14px 20px !important;
            border-radius: 50px !important;
            text-decoration: none !important;
            box-shadow: 0 4px 20px rgba(37,211,102,0.4) !important;
            transition: all 0.3s ease !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            font-family: 'Inter', sans-serif !important;
            white-space: nowrap !important;
            width: auto !important;
            height: auto !important;
        }
        .whatsapp-float:hover {
            background: #22c55e;
            transform: translateY(-3px);
            box-shadow: 0 6px 30px rgba(37,211,102,0.5);
        }
        .whatsapp-float svg {
            flex-shrink: 0;
        }
        .whatsapp-float-text {
            display: inline;
        }

        /* ===== Footer Responsive ===== */
        @media (max-width: 1024px) {
            .footer-grid {
                grid-template-columns: 1.2fr 1fr 1fr;
                gap: 36px;
            }
            .footer-contact {
                grid-column: 1 / -1;
            }
        }
        @media (max-width: 768px) {
            .footer-main {
                padding: 40px 0 30px;
            }
            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 32px;
            }
            .footer-about {
                grid-column: 1 / -1;
            }
            .footer-contact {
                grid-column: 1 / -1;
            }
        }
        @media (max-width: 500px) {
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 28px;
            }
            .footer-about,
            .footer-contact {
                grid-column: auto;
            }
            .footer-bottom-content {
                flex-direction: column;
                text-align: center;
            }
            .whatsapp-float-text {
                display: none;
            }
            .whatsapp-float {
                padding: 14px;
                border-radius: 50%;
            }
        }
    </style>

    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
<?php
// Close database connection
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
