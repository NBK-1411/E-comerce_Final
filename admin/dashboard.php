<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../settings/db_class.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/booking_controller.php');
require_once(__DIR__ . '/../controllers/customer_controller.php');
require_once(__DIR__ . '/../controllers/review_controller.php');
require_once(__DIR__ . '/../controllers/payment_controller.php');
require_once(__DIR__ . '/../controllers/category_controller.php');

// Check if admin
// Check if admin
require_admin();

// Get user data
$user = get_user();

// Get statistics with error handling
try {
$all_venues = get_all_venues_admin_ctr();
$all_bookings = get_all_bookings_ctr();
$all_customers = get_all_customers_ctr();
    $all_payments = get_all_payments_ctr();
$pending_reviews = get_pending_reviews_ctr();

    // Ensure all are arrays (functions might return false on error)
    if (!is_array($all_venues))
        $all_venues = [];
    if (!is_array($all_bookings))
        $all_bookings = [];
    if (!is_array($all_customers))
        $all_customers = [];
    if (!is_array($all_payments))
        $all_payments = [];
    if (!is_array($pending_reviews))
        $pending_reviews = [];
} catch (Exception $e) {
    // If there's an error, set defaults
    $all_venues = [];
    $all_bookings = [];
    $all_customers = [];
    $all_payments = [];
    $pending_reviews = [];
    error_log("Admin dashboard error: " . $e->getMessage());
}

// Calculate stats
$total_venues = is_array($all_venues) ? count($all_venues) : 0;
$pending_venues = is_array($all_venues) ? count(array_filter($all_venues, function ($v) {
    return isset($v['status']) && $v['status'] == 'pending';
})) : 0;
$total_bookings = is_array($all_bookings) ? count($all_bookings) : 0;
$total_customers = is_array($all_customers) ? count($all_customers) : 0;
$total_hosts = is_array($all_customers) ? count(array_filter($all_customers, function ($c) {
    return isset($c['user_role']) && $c['user_role'] == 3;
})) : 0;
$pending_review_count = is_array($pending_reviews) ? count($pending_reviews) : 0;

// Calculate platform revenue (sum of completed payments)
$platform_revenue = 0;
if (is_array($all_payments)) {
    $completed_payments = array_filter($all_payments, function ($p) {
        return isset($p['status']) && $p['status'] == 'completed';
    });
    $platform_revenue = array_sum(array_column($completed_payments, 'amount'));
}

// Get verified hosts count (hosts with verified status in customer table)
$db = new db_connection();
$verified_hosts_sql = "SELECT COUNT(DISTINCT c.customer_id) as count FROM customer c 
                       WHERE c.user_role = 3 AND c.verified = 1";
$verified_hosts_result = $db->read($verified_hosts_sql);
$verified_hosts_count = $verified_hosts_result && !empty($verified_hosts_result) ? intval($verified_hosts_result[0]['count']) : 0;
$host_verification_rate = $total_hosts > 0 ? round(($verified_hosts_count / $total_hosts) * 100) : 0;

// Get average rating
$avg_rating_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                    FROM review WHERE moderation_status = 'approved'";
$rating_result = $db->read($avg_rating_sql);
$avg_rating = $rating_result && !empty($rating_result) && isset($rating_result[0]['avg_rating']) ? round(floatval($rating_result[0]['avg_rating']), 1) : 0;
$total_reviews = $rating_result && !empty($rating_result) && isset($rating_result[0]['total_reviews']) ? intval($rating_result[0]['total_reviews']) : 0;

// Recent activities
$recent_bookings = [];
$recent_venues = [];
if (is_array($all_bookings) && !empty($all_bookings)) {
    $recent_bookings = array_slice(array_reverse($all_bookings), 0, 6);
}
if (is_array($all_venues) && !empty($all_venues)) {
    $recent_venues = array_slice(array_reverse($all_venues), 0, 5);
}

// Format recent activity
$recent_activities = [];
if (is_array($recent_bookings)) {
    foreach ($recent_bookings as $booking) {
        if (is_array($booking)) {
            $recent_activities[] = [
                'type' => 'booking',
                'description' => 'New booking at ' . htmlspecialchars($booking['venue_title'] ?? 'venue'),
                'timestamp' => time_ago($booking['created_at'] ?? ''),
                'user' => htmlspecialchars($booking['customer_name'] ?? 'User')
            ];
        }
    }
}
if (is_array($recent_venues)) {
    foreach ($recent_venues as $venue) {
        if (is_array($venue) && isset($venue['status']) && $venue['status'] == 'pending') {
            $recent_activities[] = [
                'type' => 'verification',
                'description' => 'Venue verification requested',
                'timestamp' => time_ago($venue['created_at'] ?? ''),
                'user' => htmlspecialchars($venue['title'] ?? 'Venue')
            ];
        }
    }
}

// Helper function for time ago
function time_ago($datetime)
{
    if (empty($datetime))
        return 'Just now';
    $time = time() - strtotime($datetime);
    if ($time < 60)
        return 'Just now';
    if ($time < 3600)
        return floor($time / 60) . ' mins ago';
    if ($time < 86400)
        return floor($time / 3600) . ' hours ago';
    if ($time < 604800)
        return floor($time / 86400) . ' days ago';
    return date('M d, Y', strtotime($datetime));
}

// Get user info for header
$user = get_user();
if (!$user || !is_array($user)) {
    $user = ['name' => 'Admin', 'email' => ''];
}

// Get all categories for Categories tab
try {
    $all_categories = get_categories_with_venue_count_ctr();
    if (!is_array($all_categories))
        $all_categories = [];
} catch (Exception $e) {
    $all_categories = [];
    error_log("Error loading categories: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard - Go Outside</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        border: 'var(--border)',
                        background: 'var(--background)',
                        foreground: 'var(--foreground)',
                        card: 'var(--card)',
                        'card-foreground': 'var(--card-foreground)',
                        muted: 'var(--muted)',
                        'muted-foreground': 'var(--muted-foreground)',
                        primary: 'var(--primary)',
                        'primary-foreground': 'var(--primary-foreground)',
                        secondary: 'var(--secondary)',
                        accent: 'var(--accent)',
                        'accent-foreground': 'var(--accent-foreground)',
                        destructive: 'var(--destructive)',
                        'destructive-foreground': 'var(--destructive-foreground)',
                        input: 'var(--input)',
                        ring: 'var(--ring)',
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
        * {
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        :root {
            /* Dark theme (default) - Modern oklch color system for better consistency */
            --background: oklch(0.12 0.01 50);
            --foreground: oklch(0.95 0.01 80);
            --card: oklch(0.16 0.01 50);
            --card-foreground: oklch(0.95 0.01 80);
            --primary: oklch(0.75 0.15 85);
            --primary-foreground: oklch(0.12 0.01 50);
            --secondary: oklch(0.22 0.01 50);
            --muted: oklch(0.22 0.01 50);
            --muted-foreground: oklch(0.65 0.02 80);
            --accent: oklch(0.55 0.18 45);
            --destructive: oklch(0.5 0.2 25);
            --destructive-foreground: oklch(0.95 0.01 80);
            --border: oklch(0.25 0.01 50);
            --input: oklch(0.22 0.01 50);
            --ring: oklch(0.75 0.15 85);
            --radius: 0.75rem;
            
            /* Map to standard theme variables for consistency */
            --bg-primary: oklch(0.12 0.01 50);
            --bg-secondary: oklch(0.16 0.01 50);
            --bg-card: oklch(0.16 0.01 50);
            --text-primary: oklch(0.95 0.01 80);
            --text-secondary: oklch(0.65 0.02 80);
            --text-muted: oklch(0.65 0.02 80);
            --border-color: oklch(0.25 0.01 50);
            --accent-hover: oklch(0.6 0.18 45);
}

        [data-theme="light"] {
            /* Light theme */
            --background: oklch(0.98 0.01 80);
            --foreground: oklch(0.12 0.01 50);
            --card: oklch(1 0 0);
            --card-foreground: oklch(0.12 0.01 50);
            --primary: oklch(0.75 0.15 85);
            --primary-foreground: oklch(0.12 0.01 50);
            --secondary: oklch(0.95 0.01 80);
            --muted: oklch(0.95 0.01 80);
            --muted-foreground: oklch(0.45 0.02 80);
            --accent: oklch(0.55 0.18 45);
            --destructive: oklch(0.5 0.2 25);
            --destructive-foreground: oklch(0.95 0.01 80);
            --border: oklch(0.9 0.01 80);
            --input: oklch(0.95 0.01 80);
            --ring: oklch(0.75 0.15 85);
            
            /* Map to standard theme variables */
            --bg-primary: oklch(0.98 0.01 80);
            --bg-secondary: oklch(0.95 0.01 80);
            --bg-card: oklch(1 0 0);
            --text-primary: oklch(0.12 0.01 50);
            --text-secondary: oklch(0.45 0.02 80);
            --text-muted: oklch(0.45 0.02 80);
            --border-color: oklch(0.9 0.01 80);
            --accent-hover: oklch(0.6 0.18 45);
        }

        body {
            background: var(--background);
            color: var(--foreground);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Override Tailwind's default border color */
        .border-border {
            border-color: var(--border) !important;
        }

        .border-input {
            border-color: var(--border) !important;
        }
        
        /* Map Tailwind utility classes to CSS variables */
        .bg-background {
            background-color: var(--background) !important;
        }
        
        .bg-card {
            background-color: var(--card) !important;
        }
        
        .bg-muted {
            background-color: var(--muted) !important;
        }
        
        .text-foreground {
            color: var(--foreground) !important;
        }
        
        .text-card-foreground {
            color: var(--card-foreground) !important;
        }
        
        .text-muted-foreground {
            color: var(--muted-foreground) !important;
        }
        
        .text-destructive {
            color: var(--destructive) !important;
        }
        
        .hover\:bg-accent:hover {
            background-color: var(--accent) !important;
        }
        
        .hover\:text-accent-foreground:hover {
            color: var(--accent-foreground) !important;
        }
        
        .hover\:bg-muted:hover {
            background-color: var(--muted) !important;
        }
        
        .hover\:text-foreground:hover {
            color: var(--foreground) !important;
        }
}
</style>
</head>

<body class="min-h-screen bg-background">
    <!-- Admin Header - Matching site navigation style -->
    <header class="go-nav"
        style="position: fixed; top: 0; left: 0; right: 0; z-index: 50; font-family: 'Inter', 'Segoe UI', sans-serif; color: var(--foreground); background: var(--background); opacity: 0.95; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); transition: background-color 0.3s ease, color 0.3s ease;">
        <div class="go-nav__container"
            style="max-width: 1280px; margin: 0 auto; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div class="flex items-center gap-4">
                <a href="../index.php" class="go-nav__logo"
                    style="font-weight: 700; letter-spacing: 0.08em; font-size: 18px; color: var(--foreground); text-decoration: none; transition: color 0.3s ease;">
                    GO OUTSIDE
                </a>
                <span class="hidden rounded-md px-2.5 py-0.5 text-xs font-semibold uppercase sm:inline-flex"
                    style="background: var(--destructive); color: var(--destructive-foreground);">
                    ADMIN
                </span>
            </div>

            <div class="go-nav__icons" style="display: flex; gap: 8px; align-items: center;">
                <!-- Theme Toggle -->
                <button id="themeToggle" class="go-nav__icon" aria-label="Toggle theme"
                    style="width: 40px; height: 40px; min-width: 40px; min-height: 40px; border: 0; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; color: var(--muted-foreground); cursor: pointer; transition: all 0.2s ease; background: transparent; padding: 0; margin: 0; box-sizing: border-box; outline: none;"
                    onmouseover="this.style.background='var(--secondary)'"
                    onmouseout="this.style.background='transparent'">
                    <!-- Moon icon (for dark mode - shown when light mode is active) -->
                    <svg id="moonIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                    </svg>
                    <!-- Sun icon (for light mode - shown when dark mode is active) -->
                    <svg id="sunIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                <button class="go-nav__icon relative" aria-label="Notifications"
                    style="width: 40px; height: 40px; min-width: 40px; min-height: 40px; border: 0; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; color: var(--muted-foreground); cursor: pointer; transition: all 0.2s ease; background: transparent; padding: 0; margin: 0; box-sizing: border-box; outline: none;"
                    onmouseover="this.style.background='var(--secondary)'"
                    onmouseout="this.style.background='transparent'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                    </svg>
                    <?php if (($pending_venues + $pending_review_count) > 0): ?>
                        <span
                            class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full text-xs font-semibold"
                            style="background: var(--destructive); color: var(--destructive-foreground);">
                            <?php echo $pending_venues + $pending_review_count; ?>
                        </span>
                    <?php endif; ?>
                </button>
                <div class="relative ml-2">
                    <button onclick="toggleProfileMenu()"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-muted border border-border text-sm font-medium text-foreground transition-colors hover:bg-accent hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background">
                        <?php
                        $initials = 'AD';
                        if (!empty($user['name'])) {
                            $parts = explode(' ', $user['name']);
                            if (count($parts) >= 2) {
                                $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
                            } else {
                                $initials = strtoupper(substr($parts[0], 0, 2));
                            }
                        }
                        echo $initials;
                        ?>
                    </button>
                    <!-- Dropdown Menu -->
                    <div id="profileMenu"
                        class="absolute right-0 mt-2 w-56 origin-top-right rounded-md border border-border bg-card p-1 text-foreground shadow-md outline-none hidden"
                        style="z-index: 100;">
                        <div class="px-2 py-1.5 text-sm font-semibold">
                            <?php echo htmlspecialchars($user['name'] ?? 'Admin'); ?>
                            <div class="text-xs font-normal text-muted-foreground">
                                <?php echo htmlspecialchars($user['email'] ?? 'admin@example.com'); ?></div>
                        </div>
                        <div class="h-px bg-border my-1"></div>
                        <a href="../public/profile.php"
                            class="relative flex cursor-default select-none items-center gap-2.5 rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-muted hover:text-foreground block w-full text-left">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Profile
                        </a>
                        <a href="../public/profile.php?tab=collections"
                            class="relative flex cursor-default select-none items-center gap-2.5 rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-muted hover:text-foreground block w-full text-left">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                            </svg>
                            Saved
                        </a>
                        <a href="../public/profile.php?tab=bookings"
                            class="relative flex cursor-default select-none items-center gap-2.5 rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-muted hover:text-foreground block w-full text-left">
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
                            <div class="h-px bg-border my-1"></div>
                            <a href="../public/owner_dashboard.php"
                                class="relative flex cursor-default select-none items-center gap-2.5 rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-muted hover:text-foreground block w-full text-left">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                    <polyline points="9 22 9 12 15 12 15 22" />
                                </svg>
                                Host Dashboard
                            </a>
                        <?php endif; ?>
                        <?php if (is_admin()): ?>
                            <a href="../admin/dashboard.php"
                                class="relative flex cursor-default select-none items-center gap-2.5 rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-muted hover:text-foreground block w-full text-left">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                </svg>
                                Admin
                            </a>
                        <?php endif; ?>
                        <div class="h-px bg-border my-1"></div>
                        <a href="../public/profile.php"
                            class="relative flex cursor-default select-none items-center gap-2.5 rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-muted hover:text-foreground block w-full text-left">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3" />
                                <path
                                    d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
                            </svg>
                            Settings
                        </a>
                        <a href="../actions/logout_action.php"
                            class="relative flex cursor-default select-none items-center gap-2.5 rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-muted hover:text-foreground text-destructive hover:text-destructive block w-full text-left">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
    <style>
        .go-nav__icon:hover {
            color: var(--foreground);
        }

        .go-nav__menu-btn:hover {
            border-color: var(--border);
            color: var(--foreground);
            background: var(--muted);
        }
        
        .tab-trigger.active {
            background: var(--muted);
        }
    </style>

    <main class="container mx-auto px-4 py-6" style="padding-top: 80px;">
        <!-- Welcome Section -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">Admin Dashboard</h1>
            <p class="text-muted-foreground">Platform moderation and management</p>
        </div>

        <!-- Stats Grid -->
        <div class="mb-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Card: Total Users -->
            <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-row items-center justify-between space-y-0 p-6 pb-2">
                    <h3 class="text-sm font-medium tracking-tight text-muted-foreground">Total Users</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="h-4 w-4 text-muted-foreground">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold"><?php echo number_format($total_customers); ?></div>
                    <p class="text-xs text-muted-foreground">
                        <span class="text-emerald-500">+<?php echo $total_hosts; ?></span> hosts
                    </p>
                </div>
            </div>

            <!-- Card: Total Venues -->
            <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-row items-center justify-between space-y-0 p-6 pb-2">
                    <h3 class="text-sm font-medium tracking-tight text-muted-foreground">Total Venues</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="h-4 w-4 text-muted-foreground">
                        <rect width="16" height="20" x="4" y="2" rx="2" ry="2" />
                        <path d="M9 22v-4h6v4" />
                        <path d="M8 6h.01" />
                        <path d="M16 6h.01" />
                        <path d="M12 6h.01" />
                        <path d="M12 10h.01" />
                        <path d="M12 14h.01" />
                        <path d="M16 10h.01" />
                        <path d="M16 14h.01" />
                        <path d="M8 10h.01" />
                        <path d="M8 14h.01" />
                    </svg>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold"><?php echo number_format($total_venues); ?></div>
                    <p class="text-xs text-muted-foreground"><?php echo $pending_venues; ?> pending verification</p>
                </div>
            </div>

            <!-- Card: Platform Revenue -->
            <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-row items-center justify-between space-y-0 p-6 pb-2">
                    <h3 class="text-sm font-medium tracking-tight text-muted-foreground">Platform Revenue</h3>
                    <span class="text-sm font-medium text-muted-foreground">₵</span>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold">₵<?php echo number_format($platform_revenue, 2); ?></div>
                    <p class="text-xs text-muted-foreground">
                        <span class="text-emerald-500">From bookings</span>
                    </p>
                </div>
            </div>

            <!-- Card: Active Disputes -->
            <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-row items-center justify-between space-y-0 p-6 pb-2">
                    <h3 class="text-sm font-medium tracking-tight text-muted-foreground">Active Disputes</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="h-4 w-4 text-muted-foreground">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                        <path d="M12 9v4" />
                        <path d="M12 17h.01" />
                    </svg>
                </div>
                <div class="p-6 pt-0">
                    <div class="text-2xl font-bold">0</div>
                    <p class="text-xs text-muted-foreground">Requires attention</p>
                </div>
    </div>
</div>

        <!-- Main Tabs -->
        <div class="flex flex-col gap-2">
            <!-- TabsList -->
            <div
                class="inline-flex h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground w-full overflow-x-auto">
                <button onclick="showTab('overview')" id="tab-overview"
                    class="tab-trigger active inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 flex-1">
                    Overview
                </button>
                <button onclick="showTab('verifications')" id="tab-verifications"
                    class="tab-trigger inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 relative flex-1">
                    Verifications
                    <?php if ($pending_venues > 0): ?>
                        <span
                            class="ml-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-orange-500 text-xs text-white">
                            <?php echo $pending_venues; ?>
                        </span>
                    <?php endif; ?>
                </button>
                <button onclick="showTab('reviews')" id="tab-reviews"
                    class="tab-trigger inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 relative flex-1">
                    Flagged Reviews
                    <?php if ($pending_review_count > 0): ?>
                        <span
                            class="ml-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-destructive text-xs text-destructive-foreground">
                            <?php echo $pending_review_count; ?>
                        </span>
                    <?php endif; ?>
                </button>
                <button onclick="showTab('disputes')" id="tab-disputes"
                    class="tab-trigger inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 relative flex-1">
                    Disputes
                    <span
                        class="ml-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-orange-500 text-xs text-white">
                        0
                    </span>
                </button>
                <button onclick="showTab('categories')" id="tab-categories"
                    class="tab-trigger inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 flex-1">
                    Categories
                </button>
            </div>

            <!-- Overview Tab Content -->
            <div id="content-overview" class="flex-1 space-y-6 pt-6">
                <!-- Quick Stats -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Pending Verifications -->
                    <div class="rounded-lg border border-orange-500/20 bg-orange-950/20 p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-500/10">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="text-orange-500">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold"><?php echo $pending_venues; ?></p>
                                <p class="text-sm text-muted-foreground">Pending Verifications</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Flagged Reviews -->
                    <div class="rounded-lg border border-red-500/20 bg-red-950/20 p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="text-red-500">
                                    <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
                                    <line x1="4" x2="4" y1="22" y2="15" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold"><?php echo $pending_review_count; ?></p>
                                <p class="text-sm text-muted-foreground">Flagged Reviews</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Active Disputes -->
                    <div class="rounded-lg border border-orange-500/20 bg-orange-950/20 p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-500/10">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="text-orange-500">
                                    <path
                                        d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                                    <path d="M12 9v4" />
                                    <path d="M12 17h.01" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">0</p>
                                <p class="text-sm text-muted-foreground">Active Disputes</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Bookings -->
                    <div class="rounded-lg border border-emerald-500/20 bg-emerald-950/20 p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/10">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="text-emerald-500">
                                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                                    <polyline points="16 7 22 7 22 13" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold"><?php echo number_format($total_bookings); ?></p>
                                <p class="text-sm text-muted-foreground">Total Bookings</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <button onclick="showTab('verifications')"
                        class="inline-flex flex-col items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-input bg-background px-4 py-6 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="shrink-0">
                            <circle cx="12" cy="12" r="10" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                        <span>Review Verifications</span>
                    </button>
                    <button onclick="showTab('reviews')"
                        class="inline-flex flex-col items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-input bg-background px-4 py-6 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="shrink-0">
                            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" />
                            <line x1="4" x2="4" y1="22" y2="15" />
                        </svg>
                        <span>Moderate Reviews</span>
                    </button>
                    <button onclick="showTab('disputes')"
                        class="inline-flex flex-col items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-input bg-background px-4 py-6 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="shrink-0">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                            <path d="M12 9v4" />
                            <path d="M12 17h.01" />
                        </svg>
                        <span>Handle Disputes</span>
                    </button>
                    <button
                        class="inline-flex flex-col items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-input bg-background px-4 py-6 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="shrink-0">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" x2="12" y1="15" y2="3" />
                        </svg>
                        <span>Export Reports</span>
                    </button>
                </div>

                <!-- Recent Activity -->
                <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-lg font-semibold leading-none tracking-tight">Recent Activity</h3>
                    </div>
                    <div class="p-6 pt-0">
                        <div class="space-y-4">
                            <?php if (empty($recent_activities)): ?>
                                <p class="text-sm text-muted-foreground text-center py-8">No recent activity</p>
                            <?php else: ?>
                                <?php foreach (array_slice($recent_activities, 0, 6) as $activity): ?>
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                                            <?php
                                            $icon_class = 'lucide-calendar';
                                            if ($activity['type'] == 'review')
                                                $icon_class = 'lucide-star';
                                            elseif ($activity['type'] == 'signup')
                                                $icon_class = 'lucide-user';
                                            elseif ($activity['type'] == 'verification')
                                                $icon_class = 'lucide-check-circle-2';
                                            elseif ($activity['type'] == 'dispute')
                                                $icon_class = 'lucide-alert-triangle';
                                            ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" class="lucide <?php echo $icon_class; ?>">
                                                <?php if ($icon_class == 'lucide-calendar'): ?>
                                                    <path d="M8 2v4" />
                                                    <path d="M16 2v4" />
                                                    <rect width="18" height="18" x="3" y="4" rx="2" />
                                                    <path d="M3 10h18" />
                                                <?php elseif ($icon_class == 'lucide-star'): ?>
                                                    <polygon
                                                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                                <?php elseif ($icon_class == 'lucide-user'): ?>
                                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                                    <circle cx="12" cy="7" r="4" />
                                                <?php elseif ($icon_class == 'lucide-check-circle-2'): ?>
                                                    <circle cx="12" cy="12" r="10" />
                                                    <path d="m9 12 2 2 4-4" />
                                                <?php elseif ($icon_class == 'lucide-alert-triangle'): ?>
                                                    <path
                                                        d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                                                    <path d="M12 9v4" />
                                                    <path d="M12 17h.01" />
                                                <?php endif; ?>
                                            </svg>
                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium">
                                                <?php echo htmlspecialchars($activity['description']); ?>
                                            </p>
                                            <?php if (!empty($activity['user'])): ?>
                                                <p class="text-xs text-muted-foreground">by
                                                    <?php echo htmlspecialchars($activity['user']); ?>
                                                </p>
                                            <?php endif; ?>
                        </div>
                                        <span
                                            class="text-xs text-muted-foreground"><?php echo htmlspecialchars($activity['timestamp']); ?></span>
                        </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Platform Health -->
                <div class="grid gap-4 lg:grid-cols-2">
                    <!-- Host Distribution -->
                    <!-- Host Distribution -->
                    <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
                        <div class="flex flex-col space-y-1.5 p-6">
                            <h3 class="text-lg font-semibold leading-none tracking-tight">Host Distribution</h3>
                        </div>
                        <div class="p-6 pt-0">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm">Verified Hosts</span>
                                    <span class="font-medium"><?php echo $verified_hosts_count; ?>
                                        (<?php echo $host_verification_rate; ?>%)</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-secondary">
                                    <div class="h-full bg-emerald-500"
                                        style="width: <?php echo $host_verification_rate; ?>%"></div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm">Pending Verification</span>
                                    <span class="font-medium"><?php echo $total_hosts - $verified_hosts_count; ?>
                                        (<?php echo 100 - $host_verification_rate; ?>%)</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-secondary">
                                    <div class="h-full bg-orange-500"
                                        style="width: <?php echo 100 - $host_verification_rate; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Review Health -->
                    <!-- Review Health -->
                    <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
                        <div class="flex flex-col space-y-1.5 p-6">
                            <h3 class="text-lg font-semibold leading-none tracking-tight">Review Health</h3>
                        </div>
                        <div class="p-6 pt-0">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm">Average Platform Rating</span>
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="currentColor" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="fill-yellow-400 text-yellow-400">
                                            <polygon
                                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                        </svg>
                                        <span class="font-medium"><?php echo $avg_rating; ?></span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm">Total Reviews</span>
                                    <span class="font-medium"><?php echo number_format($total_reviews); ?></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm">Response Rate</span>
                                    <span class="font-medium">78%</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm">Flagged Rate</span>
                                    <span class="font-medium text-emerald-500">0.03%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verifications Tab Content -->
            <div id="content-verifications" class="flex-1 hidden space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">Pending Venue Verifications</h2>
                        <p class="text-sm text-muted-foreground">Review and approve new venue submissions</p>
                    </div>
                    <span
                        class="rounded-md border border-border px-2.5 py-0.5 text-xs font-semibold"><?php echo $pending_venues; ?>
                        pending</span>
                </div>
                <div
                    class="bg-card text-card-foreground flex flex-col gap-6 rounded-xl border border-border py-6 shadow-sm">
                    <div class="px-6">
                        <p class="text-center text-muted-foreground py-8">
                            <a href="venues.php?status=pending" class="text-accent hover:underline">View all pending
                                venues →</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Reviews Tab Content -->
            <div id="content-reviews" class="flex-1 hidden space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">Flagged Reviews</h2>
                        <p class="text-sm text-muted-foreground">Review and moderate flagged content</p>
                    </div>
                    <span
                        class="rounded-md border border-border px-2.5 py-0.5 text-xs font-semibold"><?php echo $pending_review_count; ?>
                        pending</span>
                </div>
                <div
                    class="bg-card text-card-foreground flex flex-col gap-6 rounded-xl border border-border py-6 shadow-sm">
                    <div class="px-6">
                        <p class="text-center text-muted-foreground py-8">
                            <a href="reviews.php?status=pending" class="text-accent hover:underline">View all pending
                                reviews →</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Disputes Tab Content -->
            <div id="content-disputes" class="flex-1 hidden space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">Active Disputes</h2>
                        <p class="text-sm text-muted-foreground">Review and resolve booking disputes</p>
                    </div>
                    <span class="rounded-md border border-border px-2.5 py-0.5 text-xs font-semibold">0 pending</span>
                </div>
                <div
                    class="bg-card text-card-foreground flex flex-col gap-6 rounded-xl border border-border py-6 shadow-sm">
                    <div class="px-6">
                        <p class="text-center text-muted-foreground py-8">No active disputes</p>
                    </div>
                </div>
            </div>

            <!-- Categories Tab Content -->
            <div id="content-categories" class="flex-1 hidden space-y-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">Venue Categories</h2>
                        <p class="text-sm text-muted-foreground">Manage venue categories and classifications</p>
                    </div>
                    <button onclick="openCategoryModal()"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="mr-2">
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg>
                        Add Category
                    </button>
                </div>

                <!-- Categories Grid -->
                <div class="rounded-lg border border-border bg-card p-6">
                    <?php if (empty($all_categories)): ?>
                        <div class="flex flex-col items-center justify-center py-12 text-center text-muted-foreground">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                class="mb-4 opacity-50">
                                <path d="M3 3h18v18H3z" />
                                <path d="M3 9h18" />
                                <path d="M9 21V9" />
                            </svg>
                            <p class="text-lg font-medium mb-1">No categories found</p>
                            <p class="text-sm">Click "Add Category" to create your first category</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                            <?php foreach ($all_categories as $category): ?>
                                <div
                                    class="flex items-center justify-between p-3 border border-border rounded-lg bg-card hover:bg-muted/30 transition-colors">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary flex-shrink-0">
                                            <span class="text-xl">
                                                <?php
                                                // Map icon class to emoji
                                                // Map icon class to emoji
                                                $iconMap = [
                                                    // FontAwesome classes
                                                    'fas fa-home' => '🏠',
                                                    'fas fa-building' => '🏢',
                                                    'fas fa-tree' => '🌳',
                                                    'fas fa-futbol' => '⚽',
                                                    'fas fa-music' => '🎵',
                                                    'fas fa-palette' => '🎨',
                                                    'fas fa-utensils' => '🍴',
                                                    'fas fa-glass-cheers' => '🍷',
                                                    'fas fa-umbrella-beach' => '🏖️',
                                                    'fas fa-mountain' => '⛰️',
                                                    'fas fa-camera' => '📷',
                                                    'fas fa-heart' => '❤️',
                                                    'fas fa-star' => '⭐',
                                                    'fas fa-sun' => '☀️',
                                                    'fas fa-moon' => '🌙',
                                                    // Simple names
                                                    'home' => '🏠',
                                                    'building' => '🏢',
                                                    'tree' => '🌳',
                                                    'futbol' => '⚽',
                                                    'music' => '🎵',
                                                    'palette' => '🎨',
                                                    'utensils' => '🍴',
                                                    'wine' => '🍷',
                                                    'umbrella' => '🏖️',
                                                    'mountain' => '⛰️',
                                                    'camera' => '📷',
                                                    'heart' => '❤️',
                                                    'star' => '⭐',
                                                    'sun' => '☀️',
                                                    'moon' => '🌙',
                                                    'grid' => '▦',
                                                    'party' => '🎉',
                                                    'brush' => '🎨',
                                                    'activity' => '⚡',
                                                    'mapPin' => '📍',
                                                    'coffee' => '☕'
                                                ];
                                                $icon = $category['cat_icon'] ?? '';
                                                // Try exact match, then try removing 'fas fa-' prefix
                                                $displayIcon = $iconMap[$icon] ?? null;
                                                if (!$displayIcon) {
                                                    $simpleIcon = str_replace(['fas fa-', 'fa-'], '', $icon);
                                                    $displayIcon = $iconMap[$simpleIcon] ?? '📌';
                                                }
                                                echo $displayIcon;
                                                ?>
                                    </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-semibold text-foreground truncate">
                                                <?php echo htmlspecialchars($category['cat_name']); ?>
                                            </h3>
                                            <p class="text-xs text-muted-foreground">
                                                <?php echo intval($category['venue_count'] ?? 0); ?> venues
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 flex-shrink-0">
                                        <button onclick='editCategory(<?php echo json_encode($category); ?>)'
                                            class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-muted transition-colors text-muted-foreground hover:text-foreground"
                                            title="Edit category">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                                <path d="m15 5 4 4" />
                                            </svg>
                                        </button>
                                        <button
                                            onclick="deleteCategory(<?php echo $category['cat_id']; ?>, '<?php echo htmlspecialchars($category['cat_name'], ENT_QUOTES); ?>')"
                                            class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-destructive/10 transition-colors text-muted-foreground hover:text-destructive"
                                            title="Delete category">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M3 6h18" />
                                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Activity Categories Section -->
                <div class="flex items-center justify-between mt-8">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">Activity Categories</h2>
                        <p class="text-sm text-muted-foreground">Manage categories for activities and events</p>
                    </div>
                    <button onclick="openCategoryModal('activity')"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="mr-2">
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg>
                        Add Category
                    </button>
                </div>

                <!-- Activity Categories Grid -->
                <div class="rounded-lg border border-border bg-card p-6 mt-6">
                    <div class="flex flex-col items-center justify-center py-12 text-center text-muted-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                            class="mb-4 opacity-50">
                            <path d="M3 3h18v18H3z" />
                            <path d="M3 9h18" />
                            <path d="M9 21V9" />
                        </svg>
                        <p class="text-lg font-medium mb-1">No activity categories yet</p>
                        <p class="text-sm">Click "Add Category" to create activity categories</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('[id^="content-"]').forEach(content => {
                content.classList.add('hidden');
            });

            // Update all tab buttons
            document.querySelectorAll('.tab-trigger').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab content
            document.getElementById('content-' + tabName).classList.remove('hidden');

            // Update selected tab button
            const activeTab = document.getElementById('tab-' + tabName);
            if (activeTab) {
                activeTab.classList.add('active');
            }
        }

        // Initialize with overview tab
        document.addEventListener('DOMContentLoaded', function () {
            showTab('overview');
        });
    </script>
    <style>
        .tab-trigger {
            color: var(--muted-foreground);
            background: transparent;
        }

        .tab-trigger.active {
            color: var(--foreground);
            background: var(--muted);
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.3), 0 1px 2px -1px rgb(0 0 0 / 0.2);
        }

        .tab-trigger:hover:not(.active) {
            color: var(--foreground);
            background: var(--muted);
        }
    </style>

    <!-- Category Modal -->
    <div id="categoryModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4"
        style="background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(4px);"
        onclick="if(event.target === this) closeCategoryModal();">
        <div class="relative w-full max-w-lg rounded-lg border border-border bg-card p-6 shadow-lg"
            onclick="event.stopPropagation();">
            <div class="mb-6 flex items-center justify-between">
                <h3 id="categoryModalTitle" class="text-xl font-semibold text-foreground">Add New Category</h3>
                <button onclick="closeCategoryModal()"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:text-foreground hover:bg-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="categoryForm" class="space-y-4">
                <input type="hidden" id="categoryId">

                <div>
                    <label for="categoryName" class="block text-sm font-medium text-foreground mb-2">Category Name
                        *</label>
                    <input type="text" id="categoryName" name="name" required
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:ring-offset-background"
                        placeholder="e.g., Outdoor, Indoor, Sports">
                </div>

                <div>
                    <label for="categoryDescription"
                        class="block text-sm font-medium text-foreground mb-2">Description</label>
                    <textarea id="categoryDescription" name="description" rows="3"
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:ring-offset-background"
                        placeholder="Brief description of the category"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Icon</label>
                    <input type="hidden" id="categoryIcon" name="icon" value="grid">
                    <div class="grid grid-cols-6 gap-2" id="iconGrid">
                        <?php
                        $availableIcons = [
                            'home' => '🏠',
                            'building' => '🏢',
                            'tree' => '🌳',
                            'futbol' => '⚽',
                            'music' => '🎵',
                            'palette' => '🎨',
                            'utensils' => '🍴',
                            'wine' => '🍷',
                            'umbrella' => '🏖️',
                            'mountain' => '⛰️',
                            'camera' => '📷',
                            'heart' => '❤️',
                            'star' => '⭐',
                            'sun' => '☀️',
                            'moon' => '🌙',
                            'grid' => '▦',
                            'party' => '🎉',
                            'activity' => '⚡',
                            'mapPin' => '📍',
                            'coffee' => '☕'
                        ];
                        foreach ($availableIcons as $key => $emoji):
                            ?>
                            <button type="button" onclick="selectIcon('<?php echo $key; ?>', this)"
                                class="icon-option flex items-center justify-center h-10 w-10 rounded-md border border-input hover:bg-accent hover:text-accent-foreground transition-colors text-lg"
                                data-icon="<?php echo $key; ?>">
                                <?php echo $emoji; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit"
                        class="inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 px-4 py-2 bg-accent text-white hover:bg-accent-hover">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="mr-2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        Save Category
                    </button>
                    <button type="button" onclick="closeCategoryModal()"
                        class="inline-flex flex-1 items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 px-4 py-2 border border-border bg-background hover:bg-secondary hover:text-foreground">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Icon Selection
        function selectIcon(icon, btn) {
            document.getElementById('categoryIcon').value = icon;
            // Remove selected class from all buttons
            document.querySelectorAll('.icon-option').forEach(b => {
                b.classList.remove('bg-primary', 'text-primary-foreground', 'border-primary');
                b.classList.add('border-input');
            });
            // Add selected class to clicked button
            btn.classList.remove('border-input');
            btn.classList.add('bg-primary', 'text-primary-foreground', 'border-primary');
        }

        // Category Modal Functions
        function openCategoryModal(type = 'venue') {
            document.getElementById('categoryModal').classList.remove('hidden');
            document.getElementById('categoryModal').classList.add('flex');
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryId').value = '';

            // Reset icon selection
            document.getElementById('categoryIcon').value = 'grid';
            document.querySelectorAll('.icon-option').forEach(b => {
                b.classList.remove('bg-primary', 'text-primary-foreground', 'border-primary');
                b.classList.add('border-input');
            });

            const typeLabel = type === 'activity' ? 'Activity' : 'Venue';
            document.getElementById('categoryModalTitle').textContent = `Add New ${typeLabel} Category`;
        }

        function closeCategoryModal() {
            document.getElementById('categoryModal').classList.add('hidden');
            document.getElementById('categoryModal').classList.remove('flex');
        }

        function editCategory(category) {
            document.getElementById('categoryId').value = category.cat_id;
            document.getElementById('categoryName').value = category.cat_name;
            document.getElementById('categoryDescription').value = category.cat_description || '';

            // Handle icon selection
            const icon = category.cat_icon || 'grid';
            // Clean icon string if it has fontawesome classes
            const cleanIcon = icon.replace('fas fa-', '').replace('fa-', '');

            document.getElementById('categoryIcon').value = cleanIcon;
            document.getElementById('categoryModalTitle').textContent = 'Edit Category';

            // Update visual selection
            document.querySelectorAll('.icon-option').forEach(b => {
                b.classList.remove('bg-primary', 'text-primary-foreground', 'border-primary');
                b.classList.add('border-input');
                if (b.dataset.icon === cleanIcon || b.dataset.icon === icon) {
                    b.classList.remove('border-input');
                    b.classList.add('bg-primary', 'text-primary-foreground', 'border-primary');
                }
            });

            document.getElementById('categoryModal').classList.remove('hidden');
            document.getElementById('categoryModal').classList.add('flex');
        }

        function deleteCategory(id, name) {
            if (!confirm(`Are you sure you want to delete the category "${name}"? This action cannot be undone.`)) {
                return;
            }

            fetch('../actions/delete_category_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `cat_id=${id}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Category deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete category'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the category');
                });
        }

        // Handle category form submission
        document.getElementById('categoryForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const categoryId = document.getElementById('categoryId').value;
            const formData = new FormData();
            formData.append('cat_name', document.getElementById('categoryName').value);
            formData.append('cat_description', document.getElementById('categoryDescription').value);
            formData.append('cat_icon', document.getElementById('categoryIcon').value);

            const url = categoryId ? '../actions/update_category_action.php' : '../actions/add_category_action.php';
            if (categoryId) {
                formData.append('cat_id', categoryId);
            }

            fetch(url, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(categoryId ? 'Category updated successfully!' : 'Category added successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to save category'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving the category');
                });
        });
        // Profile Menu Functions
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.classList.toggle('hidden');
        }

        // Close menu when clicking outside
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

            if (!themeToggle || !moonIcon || !sunIcon) return;

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