<div class="header">
    <h1>Simulasi Mobil Bekas</h1>
    <div class="header-bottom">
        <div class="dealer-info">
            <div class="dealer-name"><?php echo htmlspecialchars($_SESSION['dealer_name'] ?? 'Nama Dealer', ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="dealer-area"><?php echo htmlspecialchars($_SESSION['area_new'] ?? 'Nama Area', ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <div class="menu-container">
            <button class="menu-button" id="menuButton" onclick="toggleMenu()">
                <div class="menu-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>
            <div class="dropdown-menu" id="dropdownMenu">
                <a href="logout.php" class="dropdown-item logout">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    </div>
</div>