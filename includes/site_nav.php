<?php
/**
 * Shared site navigation for public-facing pages
 *
 * Usage:
 *   require_once(__DIR__ . '/../includes/site_nav.php');
 *   render_site_nav(['base_path' => '../', 'is_home' => false]);
 */
if (!function_exists('render_site_nav')) {
    function render_site_nav($options = [])
    {
        $base_path = $options['base_path'] ?? '';
        $is_home = $options['is_home'] ?? false;

        $discover_link = $is_home ? '#discover' : $base_path . 'index.php#discover';
        $happening_link = $is_home ? '#happening' : $base_path . 'index.php#happening';

        ?>
        <style>
            :root {
                /* Dark theme (default) */
                --bg-primary: #0a0a0a;
                --bg-secondary: #1a1a1a;
                --bg-card: #1a1a1a;
                --text-primary: #ffffff;
                --text-secondary: #9b9ba1;
                --text-muted: #9b9ba1;
                --border-color: rgba(39, 39, 42, 0.7);
                --nav-bg: rgba(5, 5, 5, 0.95);
            }

            [data-theme="light"] {
                /* Light theme */
                --bg-primary: #ffffff;
                --bg-secondary: #f5f5f5;
                --bg-card: #ffffff;
                --text-primary: #0a0a0a;
                --text-secondary: #525252;
                --text-muted: #737373;
                --border-color: rgba(229, 229, 229, 0.8);
                --nav-bg: rgba(255, 255, 255, 0.95);
            }

            body {
                background-color: var(--bg-primary);
                color: var(--text-primary);
                transition: background-color 0.3s ease, color 0.3s ease;
            }

            .go-nav {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 50;
                font-family: 'Inter', 'Segoe UI', sans-serif;
                color: var(--text-primary);
                background: var(--nav-bg);
                backdrop-filter: blur(14px);
                border-bottom: 1px solid var(--border-color);
                transition: background-color 0.3s ease, border-color 0.3s ease;
            }

        body {
                margin: 0;
            }

            .go-nav a {
                color: inherit;
                text-decoration: none;
            }

            .go-nav__container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 14px 20px;
                display: flex;
                align-items: center;
                justify-content: space-between;
        }

            .go-nav__logo {
                font-weight: 700;
                letter-spacing: 0.08em;
                font-size: 18px;
                color: var(--text-primary);
                transition: color 0.3s ease;
            }

            .go-nav__links {
                display: flex;
                gap: 24px;
                font-size: 15px;
                font-weight: 500;
                color: var(--text-secondary);
                align-items: center;
            }

            .go-nav__links a {
                transition: color 0.2s;
                color: var(--text-secondary);
            }

            .go-nav__links a:hover {
                color: var(--text-primary);
            }

            .go-nav__links a.go-nav__host {
                color: #FF6B35;
                display: flex;
                align-items: center;
                gap: 4px;
            }

            .go-nav__links a.go-nav__host:hover {
                color: #ff8c66;
            }

            .go-nav__icons {
                display: flex;
                gap: 8px;
                align-items: center;
            }

            .go-nav__icon {
                width: 36px;
                height: 36px;
                min-width: 36px;
                min-height: 36px;
                border: 0;
                border-radius: 0;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: var(--text-secondary);
                cursor: pointer;
                transition: all 0.2s ease;
                background: transparent;
                padding: 0;
                margin: 0;
                box-sizing: border-box;
                outline: none;
            }

            .go-nav__icon svg {
                width: 20px;
                height: 20px;
                stroke: currentColor;
                fill: none;
                stroke-width: 1.5;
            }

            .go-nav__icon:hover {
                color: var(--text-primary);
            }

            .go-nav__icon:active {
                transform: scale(0.95);
            }

            .go-nav__notifications-wrapper {
                position: relative;
            }

            .go-nav__notification-badge {
                position: absolute;
                top: -4px;
                right: -4px;
                background: #FF6B35;
                color: white;
                border-radius: 10px;
                min-width: 18px;
                height: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 11px;
                font-weight: 600;
                padding: 0 5px;
                border: 2px solid var(--nav-bg);
            }

            .go-nav__notifications-dropdown {
                position: absolute;
                top: calc(100% + 12px);
                right: 0;
                width: 380px;
                max-width: calc(100vw - 40px);
                max-height: 500px;
                background: var(--bg-card);
                border: 1px solid var(--border-color);
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                overflow: hidden;
                z-index: 1000;
            }

            .go-nav__notifications-header {
                padding: 16px;
                border-bottom: 1px solid var(--border-color);
                display: flex;
                justify-content: space-between;
                align-items: center;
        }

            .go-nav__mark-all-read {
                background: none;
                border: none;
                color: var(--accent);
                font-size: 13px;
                font-weight: 500;
                cursor: pointer;
                padding: 4px 8px;
                border-radius: 4px;
                transition: background-color 0.2s;
            }

            .go-nav__mark-all-read:hover {
                background: var(--bg-secondary);
            }

            .go-nav__notifications-list {
                max-height: 400px;
                overflow-y: auto;
            }

            .go-nav__notification-item {
                padding: 12px 16px;
                border-bottom: 1px solid var(--border-color);
                cursor: pointer;
                transition: background-color 0.2s;
                display: flex;
                gap: 12px;
                align-items: flex-start;
        }

            .go-nav__notification-item:hover {
                background: var(--bg-secondary);
            }

            .go-nav__notification-item.unread {
                background: rgba(255, 107, 53, 0.05);
            }

            .go-nav__notification-item.unread:hover {
                background: rgba(255, 107, 53, 0.1);
            }

            .go-nav__notification-icon {
                width: 40px;
                height: 40px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                background: var(--bg-secondary);
            }

            .go-nav__notification-content {
                flex: 1;
                min-width: 0;
            }

            .go-nav__notification-title {
                font-weight: 600;
                font-size: 14px;
                color: var(--text-primary);
                margin-bottom: 4px;
            }

            .go-nav__notification-message {
                font-size: 13px;
                color: var(--text-secondary);
                line-height: 1.4;
                margin-bottom: 4px;
            }

            .go-nav__notification-time {
                font-size: 12px;
                color: var(--text-muted);
            }

            .go-nav__notifications-empty {
                padding: 40px 20px;
                text-align: center;
                color: var(--text-secondary);
            }

            .go-nav__notifications-footer {
                padding: 12px 16px;
                border-top: 1px solid var(--border-color);
                text-align: center;
        }

            @media (max-width: 640px) {
                .go-nav__notifications-dropdown {
                    width: 320px;
                    right: -10px;
                }
            }

            .go-nav__menu-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                min-width: 36px;
                min-height: 36px;
                border: 1px solid var(--border-color);
                border-radius: 50%;
                background: var(--bg-secondary);
                color: var(--text-primary);
                font-size: 13px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
                outline: none;
                padding: 0;
            }

            .go-nav__menu-btn:hover {
                border-color: rgba(255, 107, 53, 0.5);
                background: rgba(255, 107, 53, 0.15);
                color: #FF6B35;
            }

            @media (max-width: 960px) {
                .go-nav__links {
                    gap: 16px;
                    font-size: 14px;
                }

                .go-nav__container {
                    padding: 12px 16px;
                }

                body:not(.has-banner) {
                    padding-top: 70px;
            }
        }
        </style>
        <header class="go-nav">
            <div class="go-nav__container">
                <a href="<?php echo $base_path; ?>index.php" class="go-nav__logo">GO OUTSIDE</a>

                <nav class="go-nav__links">
                    <a href="<?php echo $discover_link; ?>">Discover</a>
                    <a href="<?php echo $base_path; ?>public/search.php">Venues</a>
                    <a href="<?php echo $happening_link; ?>">Activities</a>
                    <a href="<?php echo $base_path; ?>public/register.php" class="go-nav__host">
                        <span>★</span> Become a Host
                    </a>
                </nav>

                <div class="go-nav__icons">
                    <button class="go-nav__icon" id="themeToggle" aria-label="Toggle theme">
                        <!-- Moon icon (for dark mode - shown when light mode is active) -->
                        <svg id="moonIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="display: none;">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                        </svg>
                        <!-- Sun icon (for light mode - shown when dark mode is active) -->
                        <svg id="sunIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="5" />
                            <line x1="12" y1="1" x2="12" y2="3" />
                            <line x1="12" y1="21" x2="12" y2="23" />
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                            <line x1="1" y1="12" x2="3" y2="12" />
                            <line x1="21" y1="12" x2="23" y2="12" />
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                        </svg>
                    </button>
                        <?php if (is_logged_in()): ?>
                    <div class="go-nav__notifications-wrapper">
                        <button class="go-nav__icon relative" id="notificationsBtn" aria-label="Notifications">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                            </svg>
                            <span id="notificationBadge" class="go-nav__notification-badge" style="display: none;">0</span>
                        </button>
                        <div class="go-nav__notifications-dropdown" id="notificationsDropdown" style="display: none;">
                            <div class="go-nav__notifications-header">
                                <h3 style="color: var(--text-primary); font-weight: 600; font-size: 16px;">Notifications</h3>
                                <button id="markAllReadBtn" class="go-nav__mark-all-read" style="display: none;">Mark all as read</button>
                            </div>
                            <div class="go-nav__notifications-list" id="notificationsList">
                                <div class="go-nav__notifications-loading" style="padding: 20px; text-align: center; color: var(--text-secondary);">
                                    Loading notifications...
                                </div>
                            </div>
                            <div class="go-nav__notifications-footer">
                                <a href="<?php echo $base_path; ?>public/profile.php?tab=notifications" style="color: var(--accent); text-decoration: none; font-size: 14px; font-weight: 500;">
                                    View all notifications
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="go-nav__menu-wrapper">
                        <button class="go-nav__menu-btn" id="menuBtn" aria-label="Menu">
                            <?php
                            if (is_logged_in()) {
                                $user = get_user();
                                $initials = 'U';
                                if (!empty($user['name'])) {
                                    $parts = explode(' ', $user['name']);
                                    if (count($parts) >= 2) {
                                        $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
                                    } else {
                                        $initials = strtoupper(substr($parts[0], 0, 2));
                                    }
                                }
                                echo htmlspecialchars($initials);
                            } else {
                                echo 'M';
                            }
                            ?>
                        </button>
                        <div class="go-nav__dropdown" id="menuDropdown" style="display: none;">
                            <?php if (!is_logged_in()): ?>
                                <a href="#" class="go-nav__dropdown-item" id="openLoginModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                                        <polyline points="10 17 15 12 10 7" />
                                        <line x1="15" y1="12" x2="3" y2="12" />
                                    </svg>
                                    Log in
                                </a>
                                <a href="#" class="go-nav__dropdown-item" id="openSignupModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                    Sign up
                                </a>
                                <div class="go-nav__dropdown-separator"></div>
                            <?php else: ?>
                                <a href="<?php echo $base_path; ?>public/profile.php" class="go-nav__dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                    Profile
                                </a>
                                <a href="<?php echo $base_path; ?>public/profile.php?tab=collections" class="go-nav__dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                    </svg>
                                    Saved
                                </a>
                                <a href="<?php echo $base_path; ?>public/profile.php?tab=bookings" class="go-nav__dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    My Bookings
                                </a>

                                <?php if (is_venue_owner() || is_admin()): ?>
                                    <div class="go-nav__dropdown-separator"></div>
                                    <a href="<?php echo $base_path; ?>public/owner_dashboard.php" class="go-nav__dropdown-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                            <polyline points="9 22 9 12 15 12 15 22" />
                                        </svg>
                                        Host Dashboard
                                    </a>
                                <?php endif; ?>

                            <?php if (is_admin()): ?>
                                    <a href="<?php echo $base_path; ?>admin/dashboard.php" class="go-nav__dropdown-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                        </svg>
                                        Admin
                                    </a>
                                <?php endif; ?>

                                <div class="go-nav__dropdown-separator"></div>
                            <?php endif; ?>

                            <a href="<?php echo $base_path; ?>public/profile.php" class="go-nav__dropdown-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="3" />
                                    <path
                                        d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
                                </svg>
                                Settings
                            </a>

                            <?php if (is_logged_in()): ?>
                                <a href="<?php echo $base_path; ?>actions/logout_action.php" class="go-nav__dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                        <polyline points="16 17 21 12 16 7" />
                                        <line x1="21" y1="12" x2="9" y2="12" />
                                    </svg>
                                    Log out
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Login Modal -->
        <div id="loginModal" class="go-modal" style="display: none;">
            <div class="go-modal__overlay"></div>
            <div class="go-modal__content">
                <button class="go-modal__close" id="closeLoginModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
                <div class="go-modal__header">
                    <h2>Welcome Back</h2>
                    <p>Login to your account</p>
                </div>
                <div id="loginAlertMessage" style="display: none;"></div>
                <form id="loginModalForm">
                    <div class="go-form-group">
                        <label>Email Address</label>
                        <input type="email" id="modalEmail" name="email" class="go-input" placeholder="Enter your email"
                            required>
                    </div>
                    <div class="go-form-group">
                        <label>Password</label>
                        <div class="go-input-wrapper">
                            <input type="password" id="modalPassword" name="password" class="go-input"
                                placeholder="Enter your password" required>
                            <button type="button" class="go-input-toggle" id="toggleModalPassword">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="eye-icon">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="eye-off-icon" style="display: none;">
                                    <path
                                        d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                    <line x1="1" y1="1" x2="23" y2="23" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="go-btn go-btn-primary" id="loginModalBtn">
                        <span id="loginModalBtnText">Login</span>
                        <span id="loginModalBtnLoader" style="display: none;">
                            <svg class="go-spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                            </svg>
                            Logging in...
                        </span>
                    </button>
                </form>
                <div class="go-modal__footer">
                    <p>Don't have an account? <a href="#" id="switchToSignup">Sign up</a></p>
                    <p><a href="<?php echo $base_path; ?>public/login.php">Open full login page</a></p>
                </div>
            </div>
        </div>

        <!-- Signup Modal -->
        <div id="signupModal" class="go-modal" style="display: none;">
            <div class="go-modal__overlay"></div>
            <div class="go-modal__content go-modal__content-large">
                <button class="go-modal__close" id="closeSignupModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
                <div class="go-modal__header">
                    <h2>Create Account</h2>
                    <p>Join Go Outside to discover amazing venues</p>
                </div>
                <div id="signupAlertMessage" style="display: none;"></div>
                <form id="signupModalForm">
                    <div class="go-form-row">
                        <div class="go-form-group">
                            <label>Full Name *</label>
                            <input type="text" id="modalName" name="name" class="go-input" placeholder="John Doe" required>
                        </div>
                        <div class="go-form-group">
                            <label>Email Address *</label>
                            <input type="email" id="modalSignupEmail" name="email" class="go-input"
                                placeholder="john@example.com" required>
                        </div>
                    </div>
                    <div class="go-form-row">
                        <div class="go-form-group">
                            <label>Country *</label>
                            <select id="modalCountry" name="country" class="go-input" required>
                                <option value="">Select Country</option>
                                <option value="Ghana">Ghana</option>
                                <option value="Nigeria">Nigeria</option>
                                <option value="Kenya">Kenya</option>
                                <option value="South Africa">South Africa</option>
                                <option value="Ivory Coast">Ivory Coast</option>
                                <option value="Senegal">Senegal</option>
                                <option value="Tanzania">Tanzania</option>
                                <option value="Uganda">Uganda</option>
                                <option value="Rwanda">Rwanda</option>
                                <option value="Ethiopia">Ethiopia</option>
                            </select>
                        </div>
                        <div class="go-form-group">
                            <label>City *</label>
                            <input type="text" id="modalCity" name="city" class="go-input" placeholder="Accra" required>
                        </div>
                    </div>
                    <div class="go-form-row">
                        <div class="go-form-group">
                            <label>Contact Number *</label>
                            <input type="tel" id="modalContact" name="contact" class="go-input" placeholder="+233 24 123 4567"
                                required>
                        </div>
                        <div class="go-form-group">
                            <label>Password * (Min 8 characters)</label>
                            <div class="go-input-wrapper">
                                <input type="password" id="modalSignupPassword" name="password" class="go-input"
                                    placeholder="Enter strong password" minlength="8" required>
                                <button type="button" class="go-input-toggle" id="toggleModalSignupPassword">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="eye-icon">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="eye-off-icon" style="display: none;">
                                        <path
                                            d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                        <line x1="1" y1="1" x2="23" y2="23" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="go-form-group">
                        <label>Account Type *</label>
                        <div class="go-role-options">
                            <label class="go-role-option selected">
                                <input type="radio" name="role" value="2" checked>
                                <div>
                                    <div class="go-role-label">Customer</div>
                                    <div class="go-role-desc">Browse and book venues</div>
                                </div>
                            </label>
                            <label class="go-role-option">
                                <input type="radio" name="role" value="3">
                                <div>
                                    <div class="go-role-label">Venue Owner</div>
                                    <div class="go-role-desc">List and manage venues</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="go-btn go-btn-primary" id="signupModalBtn">
                        <span id="signupModalBtnText">Create Account</span>
                        <span id="signupModalBtnLoader" style="display: none;">
                            <svg class="go-spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                            </svg>
                            Creating account...
                        </span>
                    </button>
                </form>
                <div class="go-modal__footer">
                    <p>Already have an account? <a href="#" id="switchToLogin">Log in</a></p>
                    <p><a href="<?php echo $base_path; ?>public/register.php">Open full registration page</a></p>
                </div>
            </div>
        </div>

        <style>
            .go-nav__menu-wrapper {
                position: relative;
            }

            .go-nav__dropdown {
                position: absolute;
                top: calc(100% + 8px);
                right: 0;
                min-width: 200px;
                background: var(--bg-card);
                border: 1px solid var(--border-color);
                border-radius: 8px;
                padding: 8px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                backdrop-filter: blur(14px);
                z-index: 100;
            }

            .go-nav__dropdown-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 12px;
                color: var(--text-primary);
                text-decoration: none;
                font-size: 14px;
                border-radius: 6px;
                transition: all 0.2s;
            }

            .go-nav__dropdown-item:hover {
                background: var(--bg-secondary);
                color: var(--text-primary);
            }

            .go-nav__dropdown-item svg {
                width: 16px;
                height: 16px;
                stroke: currentColor;
            }

            .go-nav__dropdown-separator {
                height: 1px;
                background: var(--border-color);
                margin: 8px 0;
            }

            .go-modal {
                position: fixed;
                inset: 0;
                z-index: 1000;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .go-modal__overlay {
                position: absolute;
                inset: 0;
                background: rgba(0, 0, 0, 0.7);
                backdrop-filter: blur(4px);
            }

            .go-modal__content {
                position: relative;
                background: #0a0a0a;
                border: 1px solid rgba(39, 39, 42, 0.7);
                border-radius: 12px;
                padding: 32px;
                max-width: 450px;
                width: 100%;
                max-height: 90vh;
                overflow-y: auto;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
            }

            .go-modal__content-large {
                max-width: 600px;
            }

            .go-modal__close {
                position: absolute;
                top: 16px;
                right: 16px;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: transparent;
                border: none;
                color: #9b9ba1;
                cursor: pointer;
                border-radius: 6px;
                transition: all 0.2s;
            }

            .go-modal__close:hover {
                background: rgba(250, 250, 250, 0.08);
                color: #fafafa;
            }

            .go-modal__header {
                text-align: center;
                margin-bottom: 24px;
            }

            .go-modal__header h2 {
                font-size: 24px;
                font-weight: 700;
                color: #ffffff;
                margin-bottom: 8px;
            }

            .go-modal__header p {
                color: #9b9ba1;
                font-size: 14px;
            }

            .go-form-group {
                margin-bottom: 20px;
            }

            .go-form-group label {
                display: block;
                color: #ffffff;
                font-size: 14px;
                font-weight: 500;
                margin-bottom: 8px;
            }

            .go-form-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }

            .go-input {
                width: 100%;
                padding: 12px 16px;
                background: #1a1a1a;
                border: 1px solid rgba(39, 39, 42, 0.7);
                border-radius: 8px;
                color: #ffffff;
                font-size: 14px;
                transition: all 0.2s;
            }

            .go-input:focus {
                outline: none;
                border-color: #FF6B35;
                box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
            }

            .go-input-wrapper {
                position: relative;
            }

            .go-input-toggle {
                position: absolute;
                right: 12px;
                top: 50%;
                transform: translateY(-50%);
                background: transparent;
                border: none;
                color: #9b9ba1;
                cursor: pointer;
                padding: 4px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .go-input-toggle:hover {
                color: #d4d4d8;
            }

            .go-btn {
                width: 100%;
                padding: 12px 24px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                border: none;
                cursor: pointer;
                transition: all 0.2s;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }

            .go-btn-primary {
                background: #FF6B35;
                color: #ffffff;
            }

            .go-btn-primary:hover {
                background: #ff8c66;
            }

            .go-btn-primary:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }

            .go-spinner {
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                from {
                    transform: rotate(0deg);
                }

                to {
                    transform: rotate(360deg);
                }
            }

            .go-role-options {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }

            .go-role-option {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 16px;
                background: #1a1a1a;
                border: 2px solid rgba(39, 39, 42, 0.7);
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.2s;
            }

            .go-role-option:hover {
                border-color: #FF6B35;
            }

            .go-role-option.selected {
                border-color: #FF6B35;
                background: rgba(255, 107, 53, 0.1);
            }

            .go-role-option input[type="radio"] {
                margin: 0;
            }

            .go-role-label {
                color: #ffffff;
                font-weight: 600;
                font-size: 14px;
                margin-bottom: 4px;
            }

            .go-role-desc {
                color: #9b9ba1;
                font-size: 12px;
            }

            .go-modal__footer {
                margin-top: 24px;
                text-align: center;
            }

            .go-modal__footer p {
                color: #9b9ba1;
                font-size: 14px;
                margin: 8px 0;
            }

            .go-modal__footer a {
                color: #FF6B35;
                text-decoration: none;
            }

            .go-modal__footer a:hover {
                color: #ff8c66;
                text-decoration: underline;
            }

            #loginAlertMessage,
            #signupAlertMessage {
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 20px;
                font-size: 14px;
            }

            #loginAlertMessage.alert-success,
            #signupAlertMessage.alert-success {
                background: rgba(34, 197, 94, 0.1);
                border: 1px solid rgba(34, 197, 94, 0.3);
                color: #4ade80;
            }

            #loginAlertMessage.alert-danger,
            #signupAlertMessage.alert-danger {
                background: rgba(239, 68, 68, 0.1);
                border: 1px solid rgba(239, 68, 68, 0.3);
                color: #f87171;
            }
        </style>

        <script>
            (function () {
                // Theme Toggle Functionality
                function initThemeToggle() {
                    const themeToggle = document.getElementById('themeToggle');
                    const moonIcon = document.getElementById('moonIcon');
                    const sunIcon = document.getElementById('sunIcon');
                    const html = document.documentElement;

                    // Get saved theme or default to 'dark'
                    const currentTheme = localStorage.getItem('theme') || 'dark';
                    
                    // Apply saved theme on page load
                    html.setAttribute('data-theme', currentTheme);
                    updateThemeIcon(currentTheme, moonIcon, sunIcon);

                    // Toggle theme on button click
                    if (themeToggle) {
                        themeToggle.addEventListener('click', function() {
                            const currentTheme = html.getAttribute('data-theme');
                            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                            
                            html.setAttribute('data-theme', newTheme);
                            localStorage.setItem('theme', newTheme);
                            updateThemeIcon(newTheme, moonIcon, sunIcon);
                        });
                    }
                }

                function updateThemeIcon(theme, moonIcon, sunIcon) {
                    if (theme === 'light') {
                        // Light mode active - show moon icon (to switch back to dark)
                        if (moonIcon) moonIcon.style.display = 'block';
                        if (sunIcon) sunIcon.style.display = 'none';
                    } else {
                        // Dark mode active - show sun icon (to switch to light)
                        if (moonIcon) moonIcon.style.display = 'none';
                        if (sunIcon) sunIcon.style.display = 'block';
                    }
                }

                // Initialize theme toggle on page load
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initThemeToggle);
                } else {
                    initThemeToggle();
                }

                const menuBtn = document.getElementById('menuBtn');
                const menuDropdown = document.getElementById('menuDropdown');
                const loginModal = document.getElementById('loginModal');
                const signupModal = document.getElementById('signupModal');
                const openLoginModal = document.getElementById('openLoginModal');
                const openSignupModal = document.getElementById('openSignupModal');
                const closeLoginModal = document.getElementById('closeLoginModal');
                const closeSignupModal = document.getElementById('closeSignupModal');
                const switchToSignup = document.getElementById('switchToSignup');
                const switchToLogin = document.getElementById('switchToLogin');

                // Toggle menu dropdown
                if (menuBtn && menuDropdown) {
                    menuBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        menuDropdown.style.display = menuDropdown.style.display === 'none' ? 'block' : 'none';
                    });

                    document.addEventListener('click', function (e) {
                        if (!menuBtn.contains(e.target) && !menuDropdown.contains(e.target)) {
                            menuDropdown.style.display = 'none';
                        }
                    });
                }

                // Open login modal
                if (openLoginModal) {
                    openLoginModal.addEventListener('click', function (e) {
                        e.preventDefault();
                        menuDropdown.style.display = 'none';
                        loginModal.style.display = 'flex';
                    });
                }

                // Open signup modal
                if (openSignupModal) {
                    openSignupModal.addEventListener('click', function (e) {
                        e.preventDefault();
                        menuDropdown.style.display = 'none';
                        signupModal.style.display = 'flex';
                    });
                }

                // Close modals
                if (closeLoginModal) {
                    closeLoginModal.addEventListener('click', function () {
                        loginModal.style.display = 'none';
                    });
                }
                if (closeSignupModal) {
                    closeSignupModal.addEventListener('click', function () {
                        signupModal.style.display = 'none';
                    });
                }

                // Close modals on overlay click
                if (loginModal) {
                    loginModal.addEventListener('click', function (e) {
                        if (e.target.classList.contains('go-modal__overlay')) {
                            loginModal.style.display = 'none';
                        }
                    });
                }
                if (signupModal) {
                    signupModal.addEventListener('click', function (e) {
                        if (e.target.classList.contains('go-modal__overlay')) {
                            signupModal.style.display = 'none';
                        }
                    });
                }

                // Switch between modals
                if (switchToSignup) {
                    switchToSignup.addEventListener('click', function (e) {
                        e.preventDefault();
                        loginModal.style.display = 'none';
                        signupModal.style.display = 'flex';
                    });
                }
                if (switchToLogin) {
                    switchToLogin.addEventListener('click', function (e) {
                        e.preventDefault();
                        signupModal.style.display = 'none';
                        loginModal.style.display = 'flex';
                    });
                }

                // Toggle password visibility
                const toggleModalPassword = document.getElementById('toggleModalPassword');
                if (toggleModalPassword) {
                    toggleModalPassword.addEventListener('click', function () {
                        const passwordField = document.getElementById('modalPassword');
                        const eyeIcon = toggleModalPassword.querySelector('.eye-icon');
                        const eyeOffIcon = toggleModalPassword.querySelector('.eye-off-icon');
                        if (passwordField.type === 'password') {
                            passwordField.type = 'text';
                            eyeIcon.style.display = 'none';
                            eyeOffIcon.style.display = 'block';
                        } else {
                            passwordField.type = 'password';
                            eyeIcon.style.display = 'block';
                            eyeOffIcon.style.display = 'none';
                        }
                    });
                }

                const toggleModalSignupPassword = document.getElementById('toggleModalSignupPassword');
                if (toggleModalSignupPassword) {
                    toggleModalSignupPassword.addEventListener('click', function () {
                        const passwordField = document.getElementById('modalSignupPassword');
                        const eyeIcon = toggleModalSignupPassword.querySelector('.eye-icon');
                        const eyeOffIcon = toggleModalSignupPassword.querySelector('.eye-off-icon');
                        if (passwordField.type === 'password') {
                            passwordField.type = 'text';
                            eyeIcon.style.display = 'none';
                            eyeOffIcon.style.display = 'block';
                        } else {
                            passwordField.type = 'password';
                            eyeIcon.style.display = 'block';
                            eyeOffIcon.style.display = 'none';
                        }
                    });
                }

                // Role selection
                const roleOptions = document.querySelectorAll('.go-role-option');
                roleOptions.forEach(option => {
                    option.addEventListener('click', function () {
                        roleOptions.forEach(opt => opt.classList.remove('selected'));
                        this.classList.add('selected');
                        this.querySelector('input[type="radio"]').checked = true;
                    });
                });

                // Login form submission
                const loginModalForm = document.getElementById('loginModalForm');
                if (loginModalForm) {
                    loginModalForm.addEventListener('submit', function (e) {
                        e.preventDefault();
                        const btnText = document.getElementById('loginModalBtnText');
                        const btnLoader = document.getElementById('loginModalBtnLoader');
                        const loginBtn = document.getElementById('loginModalBtn');
                        const alertDiv = document.getElementById('loginAlertMessage');

                        loginBtn.disabled = true;
                        btnText.style.display = 'none';
                        btnLoader.style.display = 'inline-flex';

                        const formData = new FormData(this);
                        fetch('<?php echo $base_path; ?>actions/login_action.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    alertDiv.className = 'alert-success';
                                    alertDiv.textContent = result.message;
                                    alertDiv.style.display = 'block';
                                    setTimeout(function () {
                                        // Redirect based on user role
                                        // Role 1 = Admin, Role 2 = Customer, Role 3 = Venue Owner
                                        if (result.user && result.user.role == 1) {
                                            window.location.href = '<?php echo $base_path; ?>admin/dashboard.php';
                                        } else {
                                            window.location.href = '<?php echo $base_path; ?>index.php';
                                        }
                                    }, 1000);
                                } else {
                                    alertDiv.className = 'alert-danger';
                                    alertDiv.textContent = result.message;
                                    alertDiv.style.display = 'block';
                                    loginBtn.disabled = false;
                                    btnText.style.display = 'inline';
                                    btnLoader.style.display = 'none';
                                }
                            })
                            .catch(error => {
                                alertDiv.className = 'alert-danger';
                                alertDiv.textContent = 'An error occurred. Please try again.';
                                alertDiv.style.display = 'block';
                                loginBtn.disabled = false;
                                btnText.style.display = 'inline';
                                btnLoader.style.display = 'none';
                            });
                    });
                }

                // Signup form submission
                const signupModalForm = document.getElementById('signupModalForm');
                if (signupModalForm) {
                    signupModalForm.addEventListener('submit', function (e) {
                        e.preventDefault();
                        const password = document.getElementById('modalSignupPassword').value;
                        if (password.length < 8) {
                            const alertDiv = document.getElementById('signupAlertMessage');
                            alertDiv.className = 'alert-danger';
                            alertDiv.textContent = 'Password must be at least 8 characters long';
                            alertDiv.style.display = 'block';
                            return;
                        }

                        const btnText = document.getElementById('signupModalBtnText');
                        const btnLoader = document.getElementById('signupModalBtnLoader');
                        const signupBtn = document.getElementById('signupModalBtn');
                        const alertDiv = document.getElementById('signupAlertMessage');

                        signupBtn.disabled = true;
                        btnText.style.display = 'none';
                        btnLoader.style.display = 'inline-flex';

                        const formData = new FormData(this);
                        fetch('<?php echo $base_path; ?>actions/register_customer_action.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    alertDiv.className = 'alert-success';
                                    alertDiv.textContent = result.message;
                                    alertDiv.style.display = 'block';
                                    setTimeout(function () {
                                        window.location.href = '<?php echo $base_path; ?>public/login.php';
                                    }, 1500);
                                } else {
                                    alertDiv.className = 'alert-danger';
                                    alertDiv.textContent = result.message;
                                    alertDiv.style.display = 'block';
                                    signupBtn.disabled = false;
                                    btnText.style.display = 'inline';
                                    btnLoader.style.display = 'none';
                                }
                            })
                            .catch(error => {
                                alertDiv.className = 'alert-danger';
                                alertDiv.textContent = 'An error occurred. Please try again.';
                                alertDiv.style.display = 'block';
                                signupBtn.disabled = false;
                                btnText.style.display = 'inline';
                                btnLoader.style.display = 'none';
                            });
                    });
                }
            })();

            // Notifications functionality
            <?php if (is_logged_in()): ?>
            (function() {
                const notificationsBtn = document.getElementById('notificationsBtn');
                const notificationsDropdown = document.getElementById('notificationsDropdown');
                const notificationsList = document.getElementById('notificationsList');
                const notificationBadge = document.getElementById('notificationBadge');
                const markAllReadBtn = document.getElementById('markAllReadBtn');
                let notifications = [];
                let unreadCount = 0;

                // Toggle notifications dropdown
                if (notificationsBtn && notificationsDropdown) {
                    notificationsBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const isVisible = notificationsDropdown.style.display !== 'none';
                        if (isVisible) {
                            notificationsDropdown.style.display = 'none';
                        } else {
                            notificationsDropdown.style.display = 'block';
                            loadNotifications();
                        }
                    });

                    // Close dropdown when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!notificationsBtn.contains(e.target) && !notificationsDropdown.contains(e.target)) {
                            notificationsDropdown.style.display = 'none';
                        }
                    });
                }

                // Load notifications
                function loadNotifications() {
                    if (!notificationsList) return;
                    
                    notificationsList.innerHTML = '<div class="go-nav__notifications-loading" style="padding: 20px; text-align: center; color: var(--text-secondary);">Loading notifications...</div>';
                    
                    fetch('<?php echo $base_path; ?>actions/get_notifications_action.php?limit=10')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                notifications = data.notifications;
                                unreadCount = data.unread_count;
                                updateNotificationBadge();
                                renderNotifications();
                            } else {
                                notificationsList.innerHTML = '<div class="go-nav__notifications-empty">Failed to load notifications</div>';
                            }
                        })
                        .catch(error => {
                            notificationsList.innerHTML = '<div class="go-nav__notifications-empty">Error loading notifications</div>';
                        });
                }

                // Render notifications
                function renderNotifications() {
                    if (!notificationsList) return;
                    
                    if (notifications.length === 0) {
                        notificationsList.innerHTML = '<div class="go-nav__notifications-empty">No notifications yet</div>';
                        if (markAllReadBtn) markAllReadBtn.style.display = 'none';
                        return;
                    }

                    let html = '';
                    let hasUnread = false;
                    
                    notifications.forEach(notif => {
                        if (!notif.is_read) hasUnread = true;
                        
                        const icon = getNotificationIcon(notif.type);
                        const link = getNotificationLink(notif);
                        const itemClass = notif.is_read ? 'go-nav__notification-item' : 'go-nav__notification-item unread';
                        
                        html += `
                            <div class="${itemClass}" data-id="${notif.id}" data-read="${notif.is_read ? '1' : '0'}">
                                <div class="go-nav__notification-icon">${icon}</div>
                                <div class="go-nav__notification-content">
                                    <div class="go-nav__notification-title">${escapeHtml(notif.title)}</div>
                                    <div class="go-nav__notification-message">${escapeHtml(notif.message)}</div>
                                    <div class="go-nav__notification-time">${notif.time_ago}</div>
                                </div>
                            </div>
                        `;
                    });

                    notificationsList.innerHTML = html;
                    
                    if (markAllReadBtn) {
                        markAllReadBtn.style.display = hasUnread ? 'block' : 'none';
                    }

                    // Add click handlers
                    notificationsList.querySelectorAll('.go-nav__notification-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const notifId = this.getAttribute('data-id');
                            const isRead = this.getAttribute('data-read') === '1';
                            
                            if (!isRead) {
                                markAsRead(notifId);
                            }
                            
                            const notif = notifications.find(n => n.id == notifId);
                            if (notif) {
                                const link = getNotificationLink(notif);
                                if (link) {
                                    window.location.href = link;
                                }
                            }
                        });
                    });
                }

                // Mark notification as read
                function markAsRead(notificationId) {
                    fetch('<?php echo $base_path; ?>actions/mark_notification_read_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'notification_id=' + notificationId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const item = notificationsList.querySelector(`[data-id="${notificationId}"]`);
                            if (item) {
                                item.classList.remove('unread');
                                item.setAttribute('data-read', '1');
                            }
                            unreadCount = data.unread_count;
                            updateNotificationBadge();
                        }
                    })
                    .catch(error => console.error('Error marking notification as read:', error));
                }

                // Mark all as read
                if (markAllReadBtn) {
                    markAllReadBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        fetch('<?php echo $base_path; ?>actions/mark_all_notifications_read_action.php', {
                            method: 'POST'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                notifications.forEach(notif => {
                                    const item = notificationsList.querySelector(`[data-id="${notif.id}"]`);
                                    if (item) {
                                        item.classList.remove('unread');
                                        item.setAttribute('data-read', '1');
                                    }
                                });
                                unreadCount = 0;
                                updateNotificationBadge();
                                markAllReadBtn.style.display = 'none';
                            }
                        })
                        .catch(error => console.error('Error marking all as read:', error));
                    });
                }

                // Update notification badge
                function updateNotificationBadge() {
                    if (notificationBadge) {
                        if (unreadCount > 0) {
                            notificationBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                            notificationBadge.style.display = 'flex';
                        } else {
                            notificationBadge.style.display = 'none';
                        }
                    }
                }

                // Get notification icon
                function getNotificationIcon(type) {
                    const icons = {
                        'booking_request': '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
                        'booking_confirmed': '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
                        'booking_declined': '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
                        'payment_received': '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
                        'review_posted': '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
                        'venue_approved': '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
                        'venue_rejected': '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
                        'system_alert': '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
                    };
                    return icons[type] || icons['system_alert'];
                }

                // Get notification link
                function getNotificationLink(notif) {
                    const basePath = '<?php echo $base_path; ?>';
                    if (notif.related_booking_id) {
                        return basePath + 'public/profile.php?tab=bookings';
                    } else if (notif.related_venue_id) {
                        return basePath + 'public/venue_detail.php?id=' + notif.related_venue_id;
                    } else if (notif.related_review_id) {
                        return basePath + 'public/profile.php?tab=reviews';
                    }
                    return basePath + 'public/profile.php?tab=notifications';
                }

                // Escape HTML
                function escapeHtml(text) {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                }

                // Load notification count on page load
                if (notificationsBtn) {
                    loadNotifications();
                    // Refresh notifications every 30 seconds
                    setInterval(loadNotifications, 30000);
                }
            })();
            <?php endif; ?>
        </script>
        
        <!-- AI Chatbot Widget -->
        <?php 
        if (file_exists(__DIR__ . '/chatbot_widget.php')) {
            $chatbot_base_path = $base_path ?? '';
            require_once(__DIR__ . '/chatbot_widget.php');
        }
        ?>
        <?php
    }
}
?>