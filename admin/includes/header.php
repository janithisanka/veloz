<header class="admin-header">
    <button class="sidebar-toggle" id="sidebarToggle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <div class="header-right">
        <a href="../" target="_blank" class="btn btn-sm btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            View Site
        </a>
        <div class="admin-user">
            <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
        </div>
    </div>
</header>
