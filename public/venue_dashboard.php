<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/booking_controller.php');
require_once(__DIR__ . '/../controllers/payment_controller.php');
require_once(__DIR__ . '/../controllers/review_controller.php');
require_once(__DIR__ . '/../includes/site_nav.php');

// Require venue owner or admin
require_venue_owner();

$owner_id = get_user_id();

// Get venues owned by this user
$my_venues = get_venues_by_owner_ctr($owner_id);
if ($my_venues === false) $my_venues = [];

// Get bookings for all venues owned by this user
$all_bookings = get_bookings_by_owner_ctr($owner_id);
if ($all_bookings === false) $all_bookings = [];

// Get payments for revenue calculation
$my_payments = get_payments_by_venue_owner_ctr($owner_id);
if ($my_payments === false) $my_payments = [];

// Calculate stats
$total_venues = count($my_venues);
$pending_bookings = array_filter($all_bookings, function($b) { return $b['status'] == 'requested' || $b['status'] == 'pending'; });
$confirmed_bookings = array_filter($all_bookings, function($b) { return $b['status'] == 'confirmed'; });
$completed_bookings = array_filter($all_bookings, function($b) { return $b['status'] == 'completed'; });

// Calculate total revenue from completed payments
$total_revenue = 0;
foreach ($my_payments as $payment) {
    if ($payment['status'] === 'completed') {
        $total_revenue += floatval($payment['amount']);
    }
}

// Get tab from URL
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'venues';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Venue Dashboard - Go Outside</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
* {
    font-family: 'Inter', 'Segoe UI', sans-serif;
}
:root {
    --background: #0a0a0a;
    --foreground: #ffffff;
    --muted: #9b9ba1;
    --border: rgba(39, 39, 42, 0.7);
    --accent: #FF6B35;
    --card: #1a1a1a;
}
body {
    background: var(--background);
    color: var(--foreground);
}
</style>
</head>

<body>
<?php render_site_nav(['base_path' => '../']); ?>

<div class="min-h-screen bg-[#0a0a0a]">
    <!-- Dashboard Header -->
    <header class="sticky top-0 z-50 border-b border-[rgba(39,39,42,0.7)] bg-[#0a0a0a]/95 backdrop-blur">
        <div class="container flex h-16 items-center justify-between px-4">
            <div class="flex items-center gap-4">
                <a href="../index.php" class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#FF6B35]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <span class="font-bold text-white">Go Outside</span>
                </a>
                <span class="hidden rounded border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] px-2.5 py-1 text-xs font-medium text-[#9b9ba1] sm:inline-flex">
                    Venue Analytics
                </span>
            </div>

            <div class="flex items-center gap-2">
                <a href="owner_dashboard.php" class="inline-flex items-center justify-center rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#1a1a1a]">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </header>

    <main class="container px-4 py-6" style="padding-top: 0;">
        <!-- Welcome Section -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-white">Venue Analytics</h1>
            <p class="text-[#9b9ba1]">Manage your venues and track performance</p>
        </div>

        <!-- Stats Grid -->
        <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] p-6 shadow-sm">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-[#9b9ba1]">My Venues</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#9b9ba1]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-white"><?php echo $total_venues; ?></div>
                <p class="text-xs text-[#9b9ba1]">Total venues listed</p>
            </div>
            
            <div class="rounded-xl border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] p-6 shadow-sm">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-[#9b9ba1]">Pending Requests</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#9b9ba1]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-white"><?php echo count($pending_bookings); ?></div>
                <p class="text-xs text-[#9b9ba1]">Requires your attention</p>
            </div>
            
            <div class="rounded-xl border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] p-6 shadow-sm">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-[#9b9ba1]">Confirmed Bookings</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#9b9ba1]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-white"><?php echo count($confirmed_bookings); ?></div>
                <p class="text-xs text-[#9b9ba1]">Active bookings</p>
            </div>
            
            <div class="rounded-xl border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] p-6 shadow-sm">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-sm font-medium text-[#9b9ba1]">Total Revenue</h3>
                    <span class="text-sm font-medium text-[#9b9ba1]">₵</span>
                </div>
                <div class="text-2xl font-bold text-white">GH₵<?php echo number_format($total_revenue, 0); ?></div>
                <p class="text-xs text-[#9b9ba1]">From completed bookings</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="create_venue.php" class="flex h-auto flex-col items-center gap-2 rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent p-4 text-center transition hover:bg-[#1a1a1a]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span class="text-sm font-medium text-white">Add New Venue</span>
            </a>
            <button onclick="showTab('bookings')" class="flex h-auto flex-col items-center gap-2 rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent p-4 text-center transition hover:bg-[#1a1a1a]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <span class="text-sm font-medium text-white">View Bookings</span>
            </button>
            <button onclick="showTab('reviews')" class="flex h-auto flex-col items-center gap-2 rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent p-4 text-center transition hover:bg-[#1a1a1a]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
                <span class="text-sm font-medium text-white">View Reviews</span>
            </button>
            <a href="owner_dashboard.php" class="flex h-auto flex-col items-center gap-2 rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent p-4 text-center transition hover:bg-[#1a1a1a]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <line x1="9" y1="3" x2="9" y2="21"/>
                </svg>
                <span class="text-sm font-medium text-white">View Analytics</span>
            </a>
        </div>

        <!-- Main Tabs -->
        <div class="mb-6 flex gap-1 overflow-x-auto rounded-lg bg-[#1a1a1a] p-1">
            <button onclick="showTab('venues')" 
                    class="tab-btn shrink-0 rounded-md px-4 py-2 text-sm font-medium transition-all <?php echo $active_tab === 'venues' ? 'bg-[#0a0a0a] text-white shadow-sm' : 'text-[#9b9ba1] hover:text-white'; ?>"
                    data-tab="venues">
                My Venues (<?php echo $total_venues; ?>)
                </button>
            <button onclick="showTab('bookings')" 
                    class="tab-btn shrink-0 rounded-md px-4 py-2 text-sm font-medium transition-all <?php echo $active_tab === 'bookings' ? 'bg-[#0a0a0a] text-white shadow-sm' : 'text-[#9b9ba1] hover:text-white'; ?>"
                    data-tab="bookings">
                Booking Requests
                    <?php if (count($pending_bookings) > 0): ?>
                <span class="ml-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white">
                            <?php echo count($pending_bookings); ?>
                        </span>
                    <?php endif; ?>
                </button>
            <button onclick="showTab('reviews')" 
                    class="tab-btn shrink-0 rounded-md px-4 py-2 text-sm font-medium transition-all <?php echo $active_tab === 'reviews' ? 'bg-[#0a0a0a] text-white shadow-sm' : 'text-[#9b9ba1] hover:text-white'; ?>"
                    data-tab="reviews">
                Reviews
                </button>
            </div>

        <!-- Tab Content -->
            <!-- My Venues Tab -->
        <div id="tab-venues" class="tab-content space-y-4 <?php echo $active_tab === 'venues' ? '' : 'hidden'; ?>">
                <?php if (empty($my_venues)): ?>
                <div class="flex flex-col items-center justify-center rounded-xl border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] py-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-4 h-12 w-12 text-[#9b9ba1]/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                    </svg>
                    <h3 class="mb-1 text-lg font-medium text-white">No Venues Yet</h3>
                    <p class="mb-4 text-sm text-[#9b9ba1]">Start by adding your first venue</p>
                    <a href="create_venue.php" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#FF6B35] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#ff8c66]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Add Your First Venue
                        </a>
                    </div>
                <?php else: ?>
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
                        
                        // Get venue stats
                        $venue_stats = get_venue_stats_ctr($venue['venue_id']);
                        $venue_bookings = get_venue_bookings_ctr($venue['venue_id']);
                    $venue_reviews = get_reviews_by_venue_ctr($venue['venue_id'], true);
                    $venue_review_count = $venue_reviews && is_array($venue_reviews) ? count($venue_reviews) : 0;
                ?>
                <div class="flex gap-4 rounded-lg border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] p-4 transition hover:border-[#FF6B35]">
                    <div class="relative h-32 w-40 shrink-0 overflow-hidden rounded-lg md:h-40 md:w-48">
                        <img src="<?php echo htmlspecialchars($venue_image); ?>" 
                                     alt="<?php echo htmlspecialchars($venue['title']); ?>"
                             class="h-full w-full object-cover">
                            </div>
                    <div class="flex flex-1 flex-col">
                        <div class="mb-2 flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="mb-1 text-lg font-semibold text-white">
                                    <?php echo htmlspecialchars($venue['title']); ?>
                                </h4>
                                <p class="mb-1 flex items-center gap-1 text-sm text-[#9b9ba1]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                                    </svg>
                                    <?php echo htmlspecialchars($venue['cat_name']); ?>
                                </p>
                                <p class="mb-2 flex items-center gap-1 text-sm text-[#9b9ba1]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    <?php echo htmlspecialchars($venue['location_text']); ?>
                                </p>
                                <span class="inline-flex items-center rounded border px-2.5 py-1 text-xs font-semibold <?php 
                                    echo $venue['status'] === 'approved' ? 'border-green-500/30 bg-green-500/10 text-green-500' : 
                                        ($venue['status'] === 'pending' ? 'border-yellow-500/30 bg-yellow-500/10 text-yellow-500' : 
                                        'border-red-500/30 bg-red-500/10 text-red-500'); 
                                ?>">
                                    <?php echo ucfirst($venue['status']); ?>
                                </span>
                            </div>
                            <div class="text-right">
                                <div class="mb-1 flex items-center justify-end gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 fill-amber-400 text-amber-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    <span class="text-2xl font-bold text-white">
                                        <?php echo $venue_stats ? number_format($venue_stats['avg_rating'], 1) : '0.0'; ?>
                                    </span>
                                    </div>
                                <div class="text-sm text-[#9b9ba1]">
                                    <?php echo $venue_review_count; ?> reviews
                                    </div>
                                <?php if ($venue_bookings && is_array($venue_bookings)): ?>
                                <div class="mt-2 text-sm text-[#9b9ba1]">
                                    <?php echo count($venue_bookings); ?> bookings
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-auto flex gap-2">
                            <a href="edit_venue.php?id=<?php echo $venue['venue_id']; ?>" 
                               class="flex-1 rounded-lg bg-[#FF6B35] px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-[#ff8c66]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 inline h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Edit
                                </a>
                            <a href="venue_detail.php?id=<?php echo $venue['venue_id']; ?>" 
                               class="flex-1 rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-[#0a0a0a]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 inline h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                View
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Bookings Tab -->
        <div id="tab-bookings" class="tab-content space-y-4 <?php echo $active_tab === 'bookings' ? '' : 'hidden'; ?>">
                <?php if (empty($all_bookings)): ?>
                <div class="flex flex-col items-center justify-center rounded-xl border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] py-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-4 h-12 w-12 text-[#9b9ba1]/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    </svg>
                    <h3 class="mb-1 text-lg font-medium text-white">No Bookings Yet</h3>
                    <p class="text-sm text-[#9b9ba1]">Booking requests will appear here</p>
                    </div>
                <?php else: ?>
                <?php foreach ($all_bookings as $booking): 
                    $booking_payments = get_payments_by_booking_ctr($booking['booking_id']);
                    $payment = !empty($booking_payments) ? $booking_payments[0] : null;
                    $total_amount = $payment ? floatval($payment['amount']) : floatval($booking['total_amount']);
                ?>
                <div class="rounded-lg border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] p-4">
                    <div class="mb-4 flex items-start justify-between">
                            <div>
                            <h4 class="mb-1 text-lg font-semibold text-white">
                                    <?php echo htmlspecialchars($booking['customer_name']); ?>
                                </h4>
                            <p class="mb-2 flex items-center gap-1 text-sm text-[#9b9ba1]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                                </svg>
                                <?php echo htmlspecialchars($booking['venue_title']); ?>
                            </p>
                            <div class="flex flex-wrap gap-4 text-sm text-[#9b9ba1]">
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?>
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
                                <span class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="1" x2="12" y2="23"/>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                    </svg>
                                    GH₵<?php echo number_format($total_amount, 2); ?>
                                </span>
                            </div>
                            <?php if (!empty($booking['special_requests'])): ?>
                            <p class="mt-2 rounded-lg bg-[#0a0a0a] p-2 text-sm text-[#9b9ba1]">"<?php echo htmlspecialchars($booking['special_requests']); ?>"</p>
                            <?php endif; ?>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="inline-flex items-center gap-1 rounded border px-2.5 py-1 text-xs font-semibold <?php 
                                echo $booking['status'] === 'confirmed' ? 'border-green-500/30 bg-green-500/10 text-green-500' : 
                                    ($booking['status'] === 'requested' || $booking['status'] === 'pending' ? 'border-yellow-500/30 bg-yellow-500/10 text-yellow-500' : 
                                    ($booking['status'] === 'completed' ? 'border-[rgba(39,39,42,0.7)] bg-transparent text-[#9b9ba1]' : 'border-red-500/30 bg-red-500/10 text-red-500')); 
                            ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                            </div>
                    <div class="flex gap-2">
                        <a href="mailto:<?php echo htmlspecialchars($booking['customer_email'] ?? ''); ?>" 
                           class="flex-1 rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent px-4 py-2 text-center text-sm font-medium text-white transition hover:bg-[#0a0a0a]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 inline h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                            Contact Customer
                            </a>
                        <?php if ($booking['status'] == 'requested' || $booking['status'] == 'pending'): ?>
                        <button onclick="acceptBooking(<?php echo $booking['booking_id']; ?>)" 
                                class="flex-1 rounded-lg bg-green-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 inline h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Approve
                                </button>
                        <button onclick="declineBooking(<?php echo $booking['booking_id']; ?>)" 
                                class="flex-1 rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 inline h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                            Decline
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Reviews Tab -->
        <div id="tab-reviews" class="tab-content space-y-4 <?php echo $active_tab === 'reviews' ? '' : 'hidden'; ?>">
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
                    <h3 class="mb-1 text-lg font-medium text-white">No Reviews Yet</h3>
                    <p class="text-sm text-[#9b9ba1]">Reviews for your venues will appear here</p>
                </div>
            <?php else: ?>
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
            <?php endif; ?>
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
        btn.classList.remove('bg-[#0a0a0a]', 'text-white', 'shadow-sm');
        btn.classList.add('text-[#9b9ba1]');
    });
    
    document.getElementById('tab-' + tabName).classList.remove('hidden');
    document.querySelector(`[data-tab="${tabName}"]`).classList.remove('text-[#9b9ba1]');
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('bg-[#0a0a0a]', 'text-white', 'shadow-sm');
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
</script>
</body>
</html>
