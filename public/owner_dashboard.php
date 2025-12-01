<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/booking_controller.php');
require_once(__DIR__ . '/../controllers/payment_controller.php');
require_once(__DIR__ . '/../controllers/review_controller.php');
require_once(__DIR__ . '/../includes/site_nav.php');

// Check if logged in
require_login();

$owner_id = $_SESSION['customer_id'];
$owner_name = $_SESSION['customer_name'];

// Get owner's venues
$my_venues = get_venues_by_owner_ctr($owner_id);
if ($my_venues === false) $my_venues = [];

// Get bookings for owner's venues
$my_bookings = get_bookings_by_owner_ctr($owner_id);
if ($my_bookings === false) $my_bookings = [];

// Get payments for revenue calculation
$my_payments = get_payments_by_venue_owner_ctr($owner_id);
if ($my_payments === false) $my_payments = [];

// Calculate stats
$total_venues = count($my_venues);
$approved_venues = count(array_filter($my_venues, function($v) { return $v['status'] == 'approved'; }));
$pending_venues = count(array_filter($my_venues, function($v) { return $v['status'] == 'pending'; }));
$total_bookings = count($my_bookings);
$pending_bookings = count(array_filter($my_bookings, function($b) { return $b['status'] == 'requested' || $b['status'] == 'pending'; }));
$confirmed_bookings = count(array_filter($my_bookings, function($b) { return $b['status'] == 'confirmed'; }));

// Calculate total revenue (from completed payments)
$total_revenue = 0;
foreach ($my_payments as $payment) {
    if ($payment['status'] === 'completed') {
        $total_revenue += floatval($payment['amount']);
    }
}

// Calculate average rating
$total_rating = 0;
$total_reviews = 0;
foreach ($my_venues as $venue) {
    $venue_reviews = get_reviews_by_venue_ctr($venue['venue_id'], true);
    if ($venue_reviews && is_array($venue_reviews)) {
        foreach ($venue_reviews as $review) {
            $total_rating += intval($review['rating']);
            $total_reviews++;
        }
    }
}
$average_rating = $total_reviews > 0 ? round($total_rating / $total_reviews, 1) : 0;

// Recent bookings (pending first)
$pending_bookings_list = array_filter($my_bookings, function($b) { return $b['status'] == 'requested' || $b['status'] == 'pending'; });
$recent_bookings = array_slice(array_merge($pending_bookings_list, $my_bookings), 0, 5);

// Get tab from URL
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Host Dashboard - Go Outside</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
* {
    font-family: 'Inter', 'Segoe UI', sans-serif;
}
:root {
    /* Dark theme (default) */
    --bg-primary: #0a0a0a;
    --bg-secondary: #1a1a1a;
    --bg-card: #1a1a1a;
    --text-primary: #ffffff;
    --text-secondary: #9b9ba1;
    --text-muted: #9b9ba1;
    --border-color: rgba(39, 39, 42, 0.7);
    --accent: #FF6B35;
    --accent-hover: #ff8c66;
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
}

body {
    background-color: var(--bg-primary);
    color: var(--text-primary);
    transition: background-color 0.3s ease, color 0.3s ease;
}
</style>
</head>

<body>
<div class="min-h-screen" style="background-color: var(--bg-primary);">
    <!-- Host Header -->
    <header class="go-nav" style="position: fixed; top: 0; left: 0; right: 0; z-index: 50; font-family: 'Inter', 'Segoe UI', sans-serif; background: var(--bg-primary); opacity: 0.95; backdrop-filter: blur(14px); border-bottom: 1px solid var(--border-color); transition: background-color 0.3s ease, border-color 0.3s ease;">
        <div class="go-nav__container" style="max-width: 1280px; margin: 0 auto; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between;">
            <div class="flex items-center gap-4">
                <a href="../index.php" class="flex items-center gap-2" style="text-decoration: none;">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg"
                        style="background-color: var(--accent);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" style="color: #ffffff;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <span class="font-bold" style="color: var(--text-primary); font-size: 18px; letter-spacing: 0.08em;">Go Outside</span>
                </a>
                <span class="hidden rounded border px-2.5 py-1 text-xs font-medium sm:inline-flex"
                    style="border-color: var(--border-color); background-color: var(--bg-card); color: var(--text-secondary);">
                    Host Mode
                </span>
            </div>

            <div class="go-nav__icons" style="display: flex; gap: 8px; align-items: center;">
                <!-- Theme Toggle -->
                <button id="themeToggle" class="go-nav__icon" aria-label="Toggle theme"
                    style="width: 40px; height: 40px; min-width: 40px; min-height: 40px; border: 0; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; color: var(--text-secondary); cursor: pointer; transition: all 0.2s ease; background: transparent; padding: 0; margin: 0; box-sizing: border-box; outline: none;"
                    onmouseover="this.style.background='var(--bg-card)'"
                    onmouseout="this.style.background='transparent'">
                    <!-- Moon icon (for dark mode - shown when light mode is active) -->
                    <svg id="moonIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                    </svg>
                    <!-- Sun icon (for light mode - shown when dark mode is active) -->
                    <svg id="sunIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                
                <!-- Notifications -->
                <button class="go-nav__icon relative" aria-label="Notifications"
                    style="width: 40px; height: 40px; min-width: 40px; min-height: 40px; border: 0; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; color: var(--text-secondary); cursor: pointer; transition: all 0.2s ease; background: transparent; padding: 0; margin: 0; box-sizing: border-box; outline: none;"
                    onmouseover="this.style.background='var(--bg-card)'"
                    onmouseout="this.style.background='transparent'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                    <?php if ($pending_bookings > 0): ?>
                    <span class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full text-xs font-semibold"
                        style="background: #ef4444; color: #ffffff;">
                        <?php echo $pending_bookings; ?>
                    </span>
                    <?php endif; ?>
                </button>
                
                <!-- Menu Button with Profile -->
                <div class="relative ml-2">
                    <button onclick="toggleProfileMenu()"
                        class="flex h-9 w-9 items-center justify-center rounded-full border text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background"
                        style="border-color: var(--border-color); background-color: var(--bg-card); color: var(--text-primary);"
                        onmouseover="this.style.backgroundColor='var(--bg-secondary)'"
                        onmouseout="this.style.backgroundColor='var(--bg-card)'">
                        <?php
                        $initials = 'H';
                        if (!empty($owner_name)) {
                            $parts = explode(' ', $owner_name);
                            if (count($parts) >= 2) {
                                $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
                            } else {
                                $initials = strtoupper(substr($parts[0], 0, 2));
                            }
                        }
                        echo htmlspecialchars($initials);
                        ?>
                    </button>
                    <!-- Dropdown Menu -->
                    <div id="profileMenu"
                        class="absolute right-0 mt-2 w-56 origin-top-right rounded-md border p-1 shadow-md outline-none hidden"
                        style="z-index: 100; border-color: var(--border-color); background-color: var(--bg-card); color: var(--text-primary);">
                        <div class="px-2 py-1.5 text-sm font-semibold">
                            <?php echo htmlspecialchars($owner_name); ?>
                            <div class="text-xs font-normal" style="color: var(--text-secondary);">
                                Host Account
    </div>
</div>
                        <div class="h-px my-1" style="background-color: var(--border-color);"></div>
                        <a href="profile.php" class="relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-muted hover:text-foreground block w-full text-left"
                            style="color: var(--text-primary);"
                            onmouseover="this.style.backgroundColor='var(--bg-secondary)'"
                            onmouseout="this.style.backgroundColor='transparent'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Profile
                        </a>
                        <a href="profile.php?tab=collections" class="relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-muted hover:text-foreground block w-full text-left"
                            style="color: var(--text-primary);"
                            onmouseover="this.style.backgroundColor='var(--bg-secondary)'"
                            onmouseout="this.style.backgroundColor='transparent'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                            </svg>
                            Saved
                        </a>
                        <a href="profile.php?tab=bookings" class="relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-muted hover:text-foreground block w-full text-left"
                            style="color: var(--text-primary);"
                            onmouseover="this.style.backgroundColor='var(--bg-secondary)'"
                            onmouseout="this.style.backgroundColor='transparent'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            My Bookings
                        </a>
                        <div class="h-px my-1" style="background-color: var(--border-color);"></div>
                        <a href="owner_dashboard.php" class="relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-muted hover:text-foreground block w-full text-left"
                            style="color: var(--text-primary);"
                            onmouseover="this.style.backgroundColor='var(--bg-secondary)'"
                            onmouseout="this.style.backgroundColor='transparent'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                <polyline points="9 22 9 12 15 12 15 22" />
                            </svg>
                            Host Dashboard
                        </a>
                        <?php if (is_admin()): ?>
                        <a href="../admin/dashboard.php" class="relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-muted hover:text-foreground block w-full text-left"
                            style="color: var(--text-primary);"
                            onmouseover="this.style.backgroundColor='var(--bg-secondary)'"
                            onmouseout="this.style.backgroundColor='transparent'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            </svg>
                            Admin
                        </a>
                        <?php endif; ?>
                        <div class="h-px my-1" style="background-color: var(--border-color);"></div>
                        <a href="profile.php" class="relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-muted hover:text-foreground block w-full text-left"
                            style="color: var(--text-primary);"
                            onmouseover="this.style.backgroundColor='var(--bg-secondary)'"
                            onmouseout="this.style.backgroundColor='transparent'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                <circle cx="12" cy="12" r="3" />
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
                            </svg>
                            Settings
                        </a>
                        <a href="../actions/logout_action.php" class="relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-muted hover:text-foreground block w-full text-left"
                            style="color: #ef4444;"
                            onmouseover="this.style.backgroundColor='var(--bg-secondary)'"
                            onmouseout="this.style.backgroundColor='transparent'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" y1="12" x2="9" y2="12" />
                            </svg>
                            Log out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="container px-4 py-6" style="padding-top: 80px;">
        <!-- Welcome Section -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold" style="color: var(--text-primary);">Welcome back, <?php echo htmlspecialchars($owner_name); ?></h1>
            <p style="color: var(--text-secondary);">Here's what's happening with your venues today</p>
        </div>

        <!-- Stats Grid -->
        <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border p-6 shadow-sm"
                style="border-color: var(--border-color); background-color: var(--bg-card);">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium" style="color: var(--text-secondary);">Total Bookings</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" style="color: var(--text-secondary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    </div>
                <div class="text-2xl font-bold" style="color: var(--text-primary);"><?php echo $total_bookings; ?></div>
                <p class="text-xs" style="color: var(--text-secondary);">All time bookings</p>
            </div>
            
            <div class="rounded-xl border p-6 shadow-sm"
                style="border-color: var(--border-color); background-color: var(--bg-card);">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium" style="color: var(--text-secondary);">Pending Requests</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" style="color: var(--text-secondary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="text-2xl font-bold" style="color: var(--text-primary);"><?php echo $pending_bookings; ?></div>
                <p class="text-xs" style="color: var(--text-secondary);">Requires your attention</p>
            </div>
            
            <div class="rounded-xl border p-6 shadow-sm"
                style="border-color: var(--border-color); background-color: var(--bg-card);">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium" style="color: var(--text-secondary);">Total Revenue</h3>
                    <span class="text-sm font-medium" style="color: var(--text-secondary);">₵</span>
                </div>
                <div class="text-2xl font-bold" style="color: var(--text-primary);">GH₵<?php echo number_format($total_revenue, 0); ?></div>
                <p class="text-xs" style="color: var(--text-secondary);">From completed bookings</p>
            </div>
            
            <div class="rounded-xl border p-6 shadow-sm"
                style="border-color: var(--border-color); background-color: var(--bg-card);">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium" style="color: var(--text-secondary);">Average Rating</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" style="color: var(--text-secondary);" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold" style="color: var(--text-primary);"><?php echo $average_rating; ?></span>
                    <span style="color: var(--text-secondary);">/5</span>
                </div>
                <p class="text-xs" style="color: var(--text-secondary);">Based on <?php echo $total_reviews; ?> reviews</p>
            </div>
        </div>

        <!-- Main Tabs -->
        <div class="mb-6 flex gap-1 overflow-x-auto rounded-lg p-1" style="background-color: var(--bg-card);">
            <button onclick="showTab('overview')" 
                    class="tab-btn shrink-0 rounded-md px-4 py-2 text-sm font-medium transition-all <?php echo $active_tab === 'overview' ? 'shadow-sm' : ''; ?>"
                    style="<?php echo $active_tab === 'overview' ? 'background-color: var(--bg-primary); color: var(--text-primary);' : 'color: var(--text-secondary);'; ?>"
                    onmouseover="<?php echo $active_tab !== 'overview' ? "this.style.color='var(--text-primary)'" : ''; ?>"
                    onmouseout="<?php echo $active_tab !== 'overview' ? "this.style.color='var(--text-secondary)'" : ''; ?>"
                    data-tab="overview">
                Overview
            </button>
            <button onclick="showTab('bookings')" 
                    class="tab-btn shrink-0 rounded-md px-4 py-2 text-sm font-medium transition-all <?php echo $active_tab === 'bookings' ? 'bg-[#0a0a0a] text-white shadow-sm' : 'text-[#9b9ba1] hover:text-white'; ?>"
                    data-tab="bookings">
                Bookings
                <?php if ($pending_bookings > 0): ?>
                <span class="ml-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white">
                    <?php echo $pending_bookings; ?>
                </span>
                <?php endif; ?>
            </button>
            <button onclick="showTab('venues')" 
                    class="tab-btn shrink-0 rounded-md px-4 py-2 text-sm font-medium transition-all <?php echo $active_tab === 'venues' ? 'bg-[#0a0a0a] text-white shadow-sm' : 'text-[#9b9ba1] hover:text-white'; ?>"
                    data-tab="venues">
                My Venues
            </button>
            <button onclick="showTab('reviews')" 
                    class="tab-btn shrink-0 rounded-md px-4 py-2 text-sm font-medium transition-all <?php echo $active_tab === 'reviews' ? 'bg-[#0a0a0a] text-white shadow-sm' : 'text-[#9b9ba1] hover:text-white'; ?>"
                    data-tab="reviews">
                Reviews
            </button>
            <button onclick="showTab('payouts')" 
                    class="tab-btn shrink-0 rounded-md px-4 py-2 text-sm font-medium transition-all <?php echo $active_tab === 'payouts' ? 'bg-[#0a0a0a] text-white shadow-sm' : 'text-[#9b9ba1] hover:text-white'; ?>"
                    data-tab="payouts">
                Payouts
            </button>
        </div>

        <!-- Tab Content -->
        <!-- Overview Tab -->
        <div id="tab-overview" class="tab-content space-y-6 <?php echo $active_tab === 'overview' ? '' : 'hidden'; ?>">
        <!-- Quick Actions -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="create_venue.php" class="flex h-auto flex-col items-center gap-2 rounded-lg border bg-transparent p-4 text-center transition"
                    style="border-color: var(--border-color); color: var(--text-primary);"
                    onmouseover="this.style.backgroundColor='var(--bg-card)'"
                    onmouseout="this.style.backgroundColor='transparent'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" style="color: var(--text-primary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                        <line x1="9" y1="22" x2="15" y2="22"/>
                        <line x1="12" y1="18" x2="12" y2="18"/>
                    </svg>
                    <span class="text-sm font-medium" style="color: var(--text-primary);">Add New Venue</span>
                </a>
                <a href="create_activity.php" class="flex h-auto flex-col items-center gap-2 rounded-lg border bg-transparent p-4 text-center transition"
                    style="border-color: var(--border-color); color: var(--text-primary);"
                    onmouseover="this.style.backgroundColor='var(--bg-card)'"
                    onmouseout="this.style.backgroundColor='transparent'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" style="color: var(--text-primary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                    </svg>
                    <span class="text-sm font-medium" style="color: var(--text-primary);">Create Activity</span>
                </a>
                <button onclick="showTab('bookings')" class="flex h-auto flex-col items-center gap-2 rounded-lg border bg-transparent p-4 text-center transition"
                    style="border-color: var(--border-color); color: var(--text-primary);"
                    onmouseover="this.style.backgroundColor='var(--bg-card)'"
                    onmouseout="this.style.backgroundColor='transparent'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" style="color: var(--text-primary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span class="text-sm font-medium" style="color: var(--text-primary);">View Bookings</span>
                </button>
                <button onclick="showTab('payouts')" class="flex h-auto flex-col items-center gap-2 rounded-lg border bg-transparent p-4 text-center transition"
                    style="border-color: var(--border-color); color: var(--text-primary);"
                    onmouseover="this.style.backgroundColor='var(--bg-card)'"
                    onmouseout="this.style.backgroundColor='transparent'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" style="color: var(--text-primary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                    <span class="text-sm font-medium" style="color: var(--text-primary);">Request Payout</span>
                </button>
                <a href="venue_dashboard.php" class="flex h-auto flex-col items-center gap-2 rounded-lg border bg-transparent p-4 text-center transition"
                    style="border-color: var(--border-color); color: var(--text-primary);"
                    onmouseover="this.style.backgroundColor='var(--bg-card)'"
                    onmouseout="this.style.backgroundColor='transparent'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" style="color: var(--text-primary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="9" y1="3" x2="9" y2="21"/>
                    </svg>
                    <span class="text-sm font-medium" style="color: var(--text-primary);">Venue Analytics</span>
                    </a>
                </div>

            <!-- Pending Bookings Preview -->
            <?php if ($pending_bookings > 0): ?>
            <div class="rounded-xl border p-6 shadow-sm"
                style="border-color: var(--border-color); background-color: var(--bg-card);">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold" style="color: var(--text-primary);">Pending Requests</h3>
                    <button onclick="showTab('bookings')" class="text-sm hover:underline"
                        style="color: var(--accent);">
                        View All
                    </button>
                </div>
                <div class="space-y-4">
                    <?php foreach (array_slice($pending_bookings_list, 0, 3) as $booking): ?>
                    <div class="flex items-center justify-between rounded-lg border p-4"
                        style="border-color: var(--border-color);">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full"
                                style="background-color: var(--bg-primary); color: var(--text-primary);">
                                <span class="text-lg font-semibold"><?php echo strtoupper(substr($booking['customer_name'], 0, 1)); ?></span>
                            </div>
                            <div>
                                <p class="font-medium" style="color: var(--text-primary);"><?php echo htmlspecialchars($booking['customer_name']); ?></p>
                                <p class="text-sm" style="color: var(--text-secondary);">
                                    <?php echo htmlspecialchars($booking['venue_title']); ?> • <?php echo $booking['guest_count']; ?> guests
                                </p>
                                <p class="text-sm" style="color: var(--text-secondary);">
                                    <?php echo date('d M', strtotime($booking['booking_date'])); ?> at <?php echo date('g:i A', strtotime($booking['start_time'])); ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="declineBooking(<?php echo $booking['booking_id']; ?>)" 
                                    class="rounded-lg border bg-transparent px-3 py-1.5 text-sm font-medium transition"
                                    style="border-color: var(--border-color); color: var(--text-primary);"
                                    onmouseover="this.style.backgroundColor='var(--bg-primary)'"
                                    onmouseout="this.style.backgroundColor='transparent'">
                                Decline
                            </button>
                            <button onclick="acceptBooking(<?php echo $booking['booking_id']; ?>)" 
                                    class="rounded-lg px-3 py-1.5 text-sm font-semibold transition"
                                    style="background-color: var(--accent); color: #ffffff;"
                                    onmouseover="this.style.backgroundColor='var(--accent-hover)'" onmouseout="this.style.backgroundColor='var(--accent)'">
                                Accept
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Venue Performance -->
            <div class="rounded-xl border p-6 shadow-sm"
                style="border-color: var(--border-color); background-color: var(--bg-card);">
                <h3 class="mb-4 text-lg font-semibold" style="color: var(--text-primary);">Venue Performance</h3>
                <div class="space-y-4">
                    <?php if (empty($my_venues)): ?>
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mb-4 h-12 w-12" style="color: var(--text-secondary); opacity: 0.5;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                            </svg>
                            <p style="color: var(--text-secondary);">No venues yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_slice($my_venues, 0, 5) as $venue): 
                            $photos = json_decode($venue['photos_json'] ?? '[]', true);
                            if (!empty($photos)) {
                                $first_photo = $photos[0];
                                // Handle path: if it starts with uploads/venues/, add ../ prefix
                                if (strpos($first_photo, 'uploads/venues/') === 0) {
                                    $venue_image = '../' . $first_photo;
                                } elseif (strpos($first_photo, '../') === 0) {
                                    // Already has ../ prefix
                                    $venue_image = $first_photo;
                                } else {
                                    // Add ../ prefix for other paths
                                    $venue_image = '../' . $first_photo;
                                }
                            } else {
                                $venue_image = '../images/portfolio/01.jpg';
                            }
                            $venue_reviews = get_reviews_by_venue_ctr($venue['venue_id'], true);
                            $venue_rating = 0;
                            $venue_review_count = 0;
                            if ($venue_reviews && is_array($venue_reviews)) {
                                $venue_review_count = count($venue_reviews);
                                $total_rating = array_sum(array_column($venue_reviews, 'rating'));
                                $venue_rating = $venue_review_count > 0 ? round($total_rating / $venue_review_count, 1) : 0;
                            }
                        ?>
                        <div class="flex items-center gap-4 rounded-lg border p-4"
                            style="border-color: var(--border-color);">
                            <div class="relative h-16 w-24 overflow-hidden rounded-lg">
                                <img src="<?php echo htmlspecialchars($venue_image); ?>" alt="<?php echo htmlspecialchars($venue['title']); ?>" 
                                     class="h-full w-full object-cover">
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-medium" style="color: var(--text-primary);"><?php echo htmlspecialchars($venue['title']); ?></h3>
                                    <?php if ($venue['verified']): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm" style="color: var(--text-secondary);"><?php echo htmlspecialchars($venue['cat_name']); ?></p>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 fill-amber-400 text-amber-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    <span class="font-medium text-white"><?php echo $venue_rating; ?></span>
                                </div>
                                <p class="text-sm text-[#9b9ba1]"><?php echo $venue_review_count; ?> reviews</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Bookings Tab -->
        <div id="tab-bookings" class="tab-content space-y-6 <?php echo $active_tab === 'bookings' ? '' : 'hidden'; ?>">
            <!-- Filter Tabs -->
            <div class="flex gap-2 overflow-x-auto pb-2">
                <button onclick="filterBookings('all')" 
                        class="filter-btn shrink-0 rounded-lg border border-[rgba(39,39,42,0.7)] bg-[#FF6B35] px-3 py-1.5 text-sm font-medium text-white transition"
                        data-filter="all">
                    All
                </button>
                <button onclick="filterBookings('pending')" 
                        class="filter-btn shrink-0 rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#1a1a1a]"
                        data-filter="pending">
                    Pending
                    <?php if ($pending_bookings > 0): ?>
                    <span class="ml-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white">
                        <?php echo $pending_bookings; ?>
                    </span>
                    <?php endif; ?>
                </button>
                <button onclick="filterBookings('confirmed')" 
                        class="filter-btn shrink-0 rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#1a1a1a]"
                        data-filter="confirmed">
                    Confirmed
                </button>
                <button onclick="filterBookings('completed')" 
                        class="filter-btn shrink-0 rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#1a1a1a]"
                        data-filter="completed">
                    Completed
                </button>
                <button onclick="filterBookings('cancelled')" 
                        class="filter-btn shrink-0 rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#1a1a1a]"
                        data-filter="cancelled">
                    Cancelled
                </button>
            </div>

            <!-- Bookings List -->
            <div id="bookingsList" class="space-y-4">
                <?php if (empty($my_bookings)): ?>
                    <div class="flex flex-col items-center justify-center rounded-xl border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] py-12 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mb-4 h-12 w-12 text-[#9b9ba1]/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        </svg>
                        <p class="text-[#9b9ba1]">No bookings found</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($my_bookings as $booking): 
                        $booking_payments = get_payments_by_booking_ctr($booking['booking_id']);
                        $payment = !empty($booking_payments) ? $booking_payments[0] : null;
                        $total_amount = $payment ? floatval($payment['amount']) : floatval($booking['total_amount']);
                    ?>
                    <div class="booking-item rounded-xl border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] p-4" 
                         data-booking-status="<?php echo $booking['status']; ?>">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <!-- Guest Info -->
                            <div class="flex items-start gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#0a0a0a] text-white">
                                    <span class="text-lg font-semibold"><?php echo strtoupper(substr($booking['customer_name'], 0, 1)); ?></span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-medium text-white"><?php echo htmlspecialchars($booking['customer_name']); ?></h3>
                                        <span class="inline-flex items-center gap-1 rounded border px-2 py-1 text-xs font-semibold <?php 
                                            echo $booking['status'] === 'confirmed' ? 'border-green-500/30 text-green-500' : 
                                                ($booking['status'] === 'pending' || $booking['status'] === 'requested' ? 'border-yellow-500/30 text-yellow-500' : 
                                                ($booking['status'] === 'completed' ? 'border-[rgba(39,39,42,0.7)] text-[#9b9ba1]' : 'border-red-500/30 text-red-500')); 
                                        ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-[#9b9ba1]"><?php echo htmlspecialchars($booking['venue_title']); ?></p>
                                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-[#9b9ba1]">
                                        <span class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                                <line x1="16" y1="2" x2="16" y2="6"/>
                                                <line x1="8" y1="2" x2="8" y2="6"/>
                                                <line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                            <?php echo date('d M Y', strtotime($booking['booking_date'])); ?>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"/>
                                                <polyline points="12 6 12 12 16 14"/>
                                            </svg>
                                            <?php echo date('g:i A', strtotime($booking['start_time'])); ?>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                            </svg>
                                            <?php echo $booking['guest_count']; ?> guests
                                        </span>
                                    </div>
                                    <?php if (!empty($booking['special_requests'])): ?>
                                    <p class="mt-2 rounded-lg bg-[#0a0a0a] p-2 text-sm text-[#9b9ba1]">"<?php echo htmlspecialchars($booking['special_requests']); ?>"</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Amount & Actions -->
                            <div class="flex flex-col items-end gap-3">
                                <div class="text-right">
                                    <p class="text-lg font-bold text-white">GH₵<?php echo number_format($total_amount, 2); ?></p>
                                    <?php if ($payment): ?>
                                    <span class="inline-flex items-center gap-1 rounded border border-blue-500/30 bg-transparent px-2 py-1 text-xs font-semibold text-blue-500">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($booking['status'] === 'requested' || $booking['status'] === 'pending'): ?>
                                <div class="flex gap-2">
                                    <button onclick="declineBooking(<?php echo $booking['booking_id']; ?>)" 
                                            class="rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#0a0a0a]">
                                        Decline
                                    </button>
                                    <button onclick="acceptBooking(<?php echo $booking['booking_id']; ?>)" 
                                            class="rounded-lg bg-[#FF6B35] px-3 py-1.5 text-sm font-semibold text-white transition hover:bg-[#ff8c66]">
                                        Accept
                                    </button>
                                </div>
                                <?php elseif ($booking['status'] === 'confirmed'): ?>
                                <div class="flex gap-2">
                                    <button class="rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#0a0a0a]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 inline h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                        </svg>
                                        Call
                                    </button>
                                    <button class="rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#0a0a0a]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 inline h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                        </svg>
                                        Message
                                    </button>
                                </div>
                                <?php elseif ($booking['status'] === 'completed'): ?>
                                <button class="rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#1a1a1a]">
                                    View Details
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Venues Tab -->
        <div id="tab-venues" class="tab-content space-y-6 <?php echo $active_tab === 'venues' ? '' : 'hidden'; ?>">
            <div class="flex justify-end">
                <a href="create_venue.php" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#FF6B35] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#ff8c66]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Add New Venue
                </a>
            </div>

            <!-- Venues Grid -->
            <?php if (empty($my_venues)): ?>
                <div class="flex flex-col items-center justify-center rounded-xl border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] py-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-4 h-12 w-12 text-[#9b9ba1]/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                    </svg>
                    <h3 class="mb-1 text-lg font-medium text-white">No venues yet</h3>
                    <p class="mb-4 text-sm text-[#9b9ba1]">Add your first venue to start receiving bookings</p>
                    <a href="create_venue.php" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#FF6B35] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#ff8c66]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Add Your First Venue
                    </a>
                </div>
            <?php else: ?>
                <div class="grid gap-6 md:grid-cols-2">
                    <?php foreach ($my_venues as $venue): 
                        $photos = json_decode($venue['photos_json'] ?? '[]', true);
                        if (!empty($photos)) {
                            $first_photo = $photos[0];
                            // Handle path: if it starts with uploads/venues/, add ../ prefix
                            if (strpos($first_photo, 'uploads/venues/') === 0) {
                                $venue_image = '../' . $first_photo;
                            } elseif (strpos($first_photo, '../') === 0) {
                                // Already has ../ prefix
                                $venue_image = $first_photo;
                            } else {
                                // Add ../ prefix for other paths
                                $venue_image = '../' . $first_photo;
                            }
                        } else {
                            $venue_image = '../images/portfolio/01.jpg';
                        }
                        $venue_reviews = get_reviews_by_venue_ctr($venue['venue_id'], true);
                        $venue_rating = 0;
                        $venue_review_count = 0;
                        if ($venue_reviews && is_array($venue_reviews)) {
                            $venue_review_count = count($venue_reviews);
                            $total_rating = array_sum(array_column($venue_reviews, 'rating'));
                            $venue_rating = $venue_review_count > 0 ? round($total_rating / $venue_review_count, 1) : 0;
                        }
                    ?>
                    <div class="overflow-hidden rounded-xl border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] shadow-sm">
                        <div class="relative aspect-video">
                            <img src="<?php echo htmlspecialchars($venue_image); ?>" alt="<?php echo htmlspecialchars($venue['title']); ?>" 
                                 class="h-full w-full object-cover">
                            <div class="absolute right-2 top-2 flex gap-2">
                                <?php if ($venue['verified']): ?>
                                <span class="inline-flex items-center gap-1 rounded border border-green-500/30 bg-green-500/10 px-2.5 py-1 text-xs font-semibold text-green-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    Verified
                                </span>
                                <?php endif; ?>
                                <?php if ($venue['is_featured'] ?? false): ?>
                                <span class="inline-flex items-center rounded border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] px-2.5 py-1 text-xs font-semibold text-white">
                                    Featured
                            </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="mb-4 flex items-start justify-between">
                        <div>
                                    <h3 class="text-lg font-semibold text-white"><?php echo htmlspecialchars($venue['title']); ?></h3>
                                    <p class="text-sm" style="color: var(--text-secondary);"><?php echo htmlspecialchars($venue['cat_name']); ?></p>
                                    <p class="text-sm text-[#9b9ba1]"><?php echo htmlspecialchars($venue['location_text']); ?></p>
                                </div>
                                <button class="flex h-8 w-8 items-center justify-center rounded text-[#9b9ba1] hover:bg-[#0a0a0a]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="1"/>
                                        <circle cx="19" cy="12" r="1"/>
                                        <circle cx="5" cy="12" r="1"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="mb-4 flex items-center justify-between">
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 fill-amber-400 text-amber-400" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                        <?php echo $venue_rating; ?>
                                    </span>
                                    <span class="text-[#9b9ba1]"><?php echo $venue_review_count; ?> reviews</span>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded border border-[rgba(39,39,42,0.7)] bg-[#0a0a0a] px-2.5 py-1 text-xs font-semibold text-white">
                                    <?php echo ucfirst($venue['status']); ?>
                                </span>
                            </div>

                            <div class="flex gap-2">
                            <a href="venue_detail.php?id=<?php echo $venue['venue_id']; ?>" 
                                   class="flex flex-1 items-center justify-center gap-2 rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent px-3 py-2 text-sm font-medium text-white transition hover:bg-[#0a0a0a]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    Preview
                                </a>
                                <a href="edit_venue.php?id=<?php echo $venue['venue_id']; ?>" 
                                   class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-[#FF6B35] px-3 py-2 text-sm font-semibold text-white transition hover:bg-[#ff8c66]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    Edit
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Reviews Tab -->
        <div id="tab-reviews" class="tab-content space-y-6 <?php echo $active_tab === 'reviews' ? '' : 'hidden'; ?>">
            <?php
            // Get all reviews for owner's venues
            $all_reviews = [];
            foreach ($my_venues as $venue) {
                $venue_reviews = get_reviews_by_venue_ctr($venue['venue_id'], true);
                if ($venue_reviews && is_array($venue_reviews)) {
                    foreach ($venue_reviews as $review) {
                        $review['venue_title'] = $venue['title'];
                        $all_reviews[] = $review;
                    }
                }
            }
            usort($all_reviews, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            ?>
            <?php if (empty($all_reviews)): ?>
                <div class="flex flex-col items-center justify-center rounded-xl border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] py-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-4 h-12 w-12 text-[#9b9ba1]/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    <p class="text-[#9b9ba1]">No reviews yet</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($all_reviews as $review): ?>
                    <div class="rounded-lg border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] p-4">
                        <div class="mb-2 flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#0a0a0a] text-white">
                                    <span class="text-lg font-semibold"><?php echo strtoupper(substr($review['customer_name'], 0, 1)); ?></span>
                                </div>
                                <div>
                                    <p class="font-medium text-white"><?php echo htmlspecialchars($review['customer_name']); ?></p>
                                    <p class="text-sm text-[#9b9ba1]"><?php echo htmlspecialchars($review['venue_title']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 <?php echo $i < $review['rating'] ? 'fill-amber-400 text-amber-400' : 'text-[#9b9ba1]/30'; ?>" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="text-sm text-[#9b9ba1]"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                        <p class="mt-2 text-xs text-[#9b9ba1]"><?php echo date('d M Y', strtotime($review['created_at'])); ?></p>
                </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Payouts Tab -->
        <div id="tab-payouts" class="tab-content space-y-6 <?php echo $active_tab === 'payouts' ? '' : 'hidden'; ?>">
            <div class="rounded-xl border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-white">Payouts</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between rounded-lg border border-[rgba(39,39,42,0.7)] bg-[#0a0a0a] p-4">
                    <div>
                            <p class="font-medium text-white">Available Balance</p>
                            <p class="text-sm text-[#9b9ba1]">Ready for withdrawal</p>
                        </div>
                        <p class="text-2xl font-bold text-white">GH₵<?php echo number_format($total_revenue, 2); ?></p>
                    </div>
                    <button class="w-full rounded-lg bg-[#FF6B35] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#ff8c66]">
                        Request Payout
                    </button>
                    <p class="text-center text-xs text-[#9b9ba1]">Payouts are processed within 3-5 business days</p>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Decline Booking Modal -->
<div id="declineModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm">
    <div class="relative w-full max-w-md rounded-lg border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] p-6 shadow-xl">
        <button onclick="closeDeclineModal()" class="absolute right-4 top-4 text-[#9b9ba1] hover:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
        <h3 class="mb-2 text-lg font-semibold text-white">Decline Booking Request</h3>
        <p class="mb-4 text-sm text-[#9b9ba1]">Let the guest know why you can't accommodate their request. Their payment will be refunded immediately.</p>
        <textarea id="declineReason" rows="4" 
                  class="mb-4 flex w-full rounded-lg border border-[rgba(39,39,42,0.7)] bg-[#0a0a0a] px-3 py-2 text-sm text-white placeholder:text-[#9b9ba1] focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                  placeholder="e.g., We're fully booked for that date..."></textarea>
        <div class="flex gap-3">
            <button onclick="closeDeclineModal()" 
                    class="flex-1 rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent px-4 py-2 text-sm font-medium text-white transition hover:bg-[#0a0a0a]">
                Cancel
            </button>
            <button onclick="confirmDecline()" 
                    class="flex-1 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600">
                Decline & Refund
            </button>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
let currentBookingId = null;

// Tab switching
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.style.backgroundColor = '';
        btn.style.color = 'var(--text-secondary)';
        btn.classList.remove('shadow-sm');
    });
    
    document.getElementById('tab-' + tabName).classList.remove('hidden');
    const activeBtn = document.querySelector(`[data-tab="${tabName}"]`);
    activeBtn.style.backgroundColor = 'var(--bg-primary)';
    activeBtn.style.color = 'var(--text-primary)';
    activeBtn.classList.add('shadow-sm');
}

// Filter bookings
function filterBookings(filter) {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.backgroundColor = 'transparent';
        btn.style.color = 'var(--text-primary)';
    });
    const activeFilterBtn = document.querySelector(`[data-filter="${filter}"]`);
    activeFilterBtn.classList.add('active');
    activeFilterBtn.style.backgroundColor = 'var(--accent)';
    activeFilterBtn.style.color = '#ffffff';
    
    document.querySelectorAll('.booking-item').forEach(item => {
        if (filter === 'all') {
            item.style.display = 'flex';
        } else {
            const status = item.dataset.bookingStatus;
            item.style.display = (status === filter) ? 'flex' : 'none';
        }
    });
}

// Accept Booking
function acceptBooking(bookingId) {
    if (!confirm('Accept this booking request?')) return;
    
    $.ajax({
        url: '../actions/booking_update_status_action.php',
        type: 'POST',
        data: { booking_id: bookingId, status: 'confirmed' },
        dataType: 'json',
        success: function(result) {
            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Failed to accept booking');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
        }
    });
}

// Decline Booking
function declineBooking(bookingId) {
    currentBookingId = bookingId;
    document.getElementById('declineModal').classList.remove('hidden');
    document.getElementById('declineModal').classList.add('flex');
}

function closeDeclineModal() {
    document.getElementById('declineModal').classList.add('hidden');
    document.getElementById('declineModal').classList.remove('flex');
    document.getElementById('declineReason').value = '';
    currentBookingId = null;
}

function confirmDecline() {
    if (!currentBookingId) return;
    
    const reason = document.getElementById('declineReason').value.trim();
    
    $.ajax({
        url: '../actions/booking_update_status_action.php',
        type: 'POST',
        data: { 
            booking_id: currentBookingId, 
            status: 'cancelled',
            reason: reason || 'Booking declined by host'
        },
        dataType: 'json',
        success: function(result) {
            if (result.success) {
                closeDeclineModal();
                location.reload();
            } else {
                alert(result.message || 'Failed to decline booking');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
        }
    });
}

// Profile Menu Toggle
function toggleProfileMenu() {
    const menu = document.getElementById('profileMenu');
    menu.classList.toggle('hidden');
}

// Close profile menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('profileMenu');
    const button = event.target.closest('button[onclick="toggleProfileMenu()"]');
    if (!button && menu && !menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
    }
});

// Theme Toggle Functionality
(function() {
    const themeToggle = document.getElementById('themeToggle');
    const moonIcon = document.getElementById('moonIcon');
    const sunIcon = document.getElementById('sunIcon');
    const html = document.documentElement;

    // Load saved theme preference
    const savedTheme = localStorage.getItem('theme') || 'dark';
    if (savedTheme === 'light') {
        html.setAttribute('data-theme', 'light');
        moonIcon.style.display = 'block';
        sunIcon.style.display = 'none';
    } else {
        html.setAttribute('data-theme', 'dark');
        moonIcon.style.display = 'none';
        sunIcon.style.display = 'block';
    }

    themeToggle.addEventListener('click', function() {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);

        // Update icon
        if (newTheme === 'light') {
            moonIcon.style.display = 'block';
            sunIcon.style.display = 'none';
        } else {
            moonIcon.style.display = 'none';
            sunIcon.style.display = 'block';
        }
    });
})();
</script>
</body>
</html>
