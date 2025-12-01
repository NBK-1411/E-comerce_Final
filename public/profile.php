<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/booking_controller.php');
require_once(__DIR__ . '/../controllers/customer_controller.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/rsvp_controller.php');
require_once(__DIR__ . '/../controllers/notification_controller.php');
require_once(__DIR__ . '/../includes/site_nav.php');

// Require login
require_login();

$user_id = get_user_id();
$user = get_customer_by_id_ctr($user_id);

// Get upcoming and past bookings
$upcoming_bookings = get_upcoming_bookings_ctr($user_id);
$past_bookings = get_past_bookings_ctr($user_id);

if ($upcoming_bookings === false)
    $upcoming_bookings = [];
if ($past_bookings === false)
    $past_bookings = [];

// Get all bookings for calendar
$all_bookings = array_merge($upcoming_bookings, $past_bookings);

// Get user's RSVPs
$user_rsvps = get_user_rsvps_ctr($user_id);
if ($user_rsvps === false) {
    $user_rsvps = [];
}

// Separate upcoming and past RSVPs
$upcoming_rsvps = [];
$past_rsvps = [];
$now = date('Y-m-d H:i:s');
foreach ($user_rsvps as $rsvp) {
    if (!empty($rsvp['start_at']) && $rsvp['start_at'] >= $now) {
        $upcoming_rsvps[] = $rsvp;
    } else {
        $past_rsvps[] = $rsvp;
    }
}

// Get notifications if on notifications tab
$notifications = [];
$unread_count = 0;
if (isset($_GET['tab']) && $_GET['tab'] === 'notifications') {
    $notifications = get_user_notifications_ctr($user_id, 50, false);
    if ($notifications === false) {
        $notifications = [];
    }
    $unread_count = get_unread_notification_count_ctr($user_id);
}

// Get tab from URL
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'bookings';

// Helper function for time ago
function time_ago($datetime) {
    if (empty($datetime)) return 'Just now';
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time / 60) . ' min' . (floor($time / 60) > 1 ? 's' : '') . ' ago';
    if ($time < 86400) return floor($time / 3600) . ' hour' . (floor($time / 3600) > 1 ? 's' : '') . ' ago';
    if ($time < 604800) return floor($time / 86400) . ' day' . (floor($time / 86400) > 1 ? 's' : '') . ' ago';
    return date('M d, Y', strtotime($datetime));
}
?>
<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Profile - Go Outside</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
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

        /* FullCalendar Theme-Aware Styles */
        .fc {
            color: var(--text-primary);
        }

        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: var(--border-color);
        }

        .fc-theme-standard .fc-scrollgrid {
            border-color: var(--border-color);
        }

        .fc-theme-standard .fc-col-header-cell {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }

        .fc-theme-standard .fc-daygrid-day {
            background-color: var(--bg-card);
        }

        .fc-theme-standard .fc-daygrid-day.fc-day-today {
            background-color: var(--bg-secondary);
        }

        .fc-theme-standard .fc-button {
            background-color: var(--bg-secondary);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        .fc-theme-standard .fc-button:hover {
            background-color: var(--bg-card);
        }

        .fc-theme-standard .fc-button-active {
            background-color: var(--accent);
            border-color: var(--accent);
            color: #ffffff;
        }

        .fc-theme-standard .fc-toolbar-title {
            color: var(--text-primary);
        }

        .fc-daygrid-event {
    cursor: pointer;
}
</style>
</head>

<body>
<?php render_site_nav(['base_path' => '../']); ?>

    <main class="px-4 py-6" style="padding-top: 80px;">
        <div class="container mx-auto max-w-4xl">
            <!-- Profile Header -->
            <div class="mb-6 flex flex-col items-center gap-4 md:flex-row md:items-start">
                <div
                    class="flex h-24 w-24 items-center justify-center rounded-full border-4 text-2xl font-semibold shadow-lg md:h-28 md:w-28"
                    style="border-color: var(--bg-primary); background-color: var(--bg-card); color: var(--text-primary);">
                    <?php echo strtoupper(substr($user['customer_name'], 0, 1)); ?>
</div>

                <div class="flex-1 text-center md:text-left">
                    <div class="mb-1 flex items-center justify-center gap-2 md:justify-start">
                        <h1 class="text-2xl font-bold" style="color: var(--text-primary);">
                            <?php echo htmlspecialchars($user['customer_name']); ?></h1>
                    </div>
                    
                    <p class="mb-2 flex items-center justify-center gap-1 text-sm md:justify-start" style="color: var(--text-secondary);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <?php echo htmlspecialchars($user['customer_city']); ?>,
                        <?php echo htmlspecialchars($user['customer_country']); ?>
                    </p>

                    <div class="flex items-center justify-center gap-4 text-sm md:justify-start">
                        <div>
                            <span class="font-semibold" style="color: var(--text-primary);"><?php echo count($upcoming_bookings); ?></span>
                            <span class="ml-1" style="color: var(--text-secondary);">Upcoming</span>
                    </div>
                        <div>
                            <span class="font-semibold" style="color: var(--text-primary);"><?php echo count($past_bookings); ?></span>
                            <span class="ml-1" style="color: var(--text-secondary);">Past</span>
                    </div>
                    </div>
                    </div>
                    
                <div class="flex gap-2">
                    <button onclick="openEditModal()"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border bg-transparent px-3 py-1.5 text-sm font-medium transition"
                        style="border-color: var(--border-color); color: var(--text-primary);"
                        onmouseover="this.style.backgroundColor='var(--bg-card)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        Edit Profile
                    </button>
                </div>
                </div>

            <!-- Member Info -->
            <div
                class="mb-6 flex items-center justify-center gap-6 rounded-lg p-4 text-sm md:justify-start"
                style="background-color: var(--bg-card);">
                <div class="flex items-center gap-2" style="color: var(--text-secondary);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                        </div>
                        </div>

                <!-- Tabs -->
            <div class="mb-6 grid w-full grid-cols-5 gap-1 rounded-lg p-1" style="background-color: var(--bg-card);">
                <button onclick="showTab('bookings')"
                    class="tab-btn flex items-center justify-center gap-1 rounded-md px-3 py-2 text-xs font-medium transition-all md:text-sm <?php echo $active_tab === 'bookings' ? 'shadow-sm' : ''; ?>"
                    style="<?php echo $active_tab === 'bookings' ? 'background-color: var(--bg-primary); color: var(--text-primary);' : 'color: var(--text-secondary);'; ?>"
                    onmouseover="<?php echo $active_tab !== 'bookings' ? "this.style.color='var(--text-primary)'" : ''; ?>"
                    onmouseout="<?php echo $active_tab !== 'bookings' ? "this.style.color='var(--text-secondary)'" : ''; ?>"
                    data-tab="bookings">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    <span class="hidden md:inline">Bookings</span>
                </button>
                <button onclick="showTab('rsvps')"
                    class="tab-btn flex items-center justify-center gap-1 rounded-md px-3 py-2 text-xs font-medium transition-all md:text-sm <?php echo $active_tab === 'rsvps' ? 'bg-[#0a0a0a] text-white shadow-sm' : 'text-[#9b9ba1] hover:text-white'; ?>"
                    data-tab="rsvps">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <span class="hidden md:inline">RSVPs</span>
                </button>
                <button onclick="showTab('collections')"
                    class="tab-btn flex items-center justify-center gap-1 rounded-md px-3 py-2 text-xs font-medium transition-all md:text-sm <?php echo $active_tab === 'collections' ? 'bg-[#0a0a0a] text-white shadow-sm' : 'text-[#9b9ba1] hover:text-white'; ?>"
                    data-tab="collections">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21l-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z" />
                    </svg>
                    <span class="hidden md:inline">Collections</span>
                </button>
                <button onclick="showTab('posts')"
                    class="tab-btn flex items-center justify-center gap-1 rounded-md px-3 py-2 text-xs font-medium transition-all md:text-sm <?php echo $active_tab === 'posts' ? 'bg-[#0a0a0a] text-white shadow-sm' : 'text-[#9b9ba1] hover:text-white'; ?>"
                    data-tab="posts">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <circle cx="8.5" cy="8.5" r="1.5" />
                        <polyline points="21 15 16 10 5 21" />
                    </svg>
                    <span class="hidden md:inline">Posts</span>
                </button>
                <button onclick="showTab('notifications')"
                    class="tab-btn flex items-center justify-center gap-1 rounded-md px-3 py-2 text-xs font-medium transition-all md:text-sm <?php echo $active_tab === 'notifications' ? 'shadow-sm' : ''; ?>"
                    style="<?php echo $active_tab === 'notifications' ? 'background-color: var(--bg-primary); color: var(--text-primary);' : 'color: var(--text-secondary);'; ?>"
                    onmouseover="<?php echo $active_tab !== 'notifications' ? "this.style.color='var(--text-primary)'" : ''; ?>"
                    onmouseout="<?php echo $active_tab !== 'notifications' ? "this.style.color='var(--text-secondary)'" : ''; ?>"
                    data-tab="notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                    <span class="hidden md:inline">Notifications</span>
                    <?php if ($unread_count > 0): ?>
                        <span class="ml-1 flex h-5 w-5 items-center justify-center rounded-full text-xs font-semibold" style="background-color: var(--accent); color: #ffffff;">
                            <?php echo $unread_count; ?>
                        </span>
                            <?php endif; ?>
                </button>
                        </div>

            <!-- Tab Content -->
            <!-- Bookings Tab -->
            <div id="tab-bookings" class="tab-content <?php echo $active_tab === 'bookings' ? '' : 'hidden'; ?>">
                <!-- View Toggle and Filter Pills -->
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <!-- View Toggle -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium" style="color: var(--text-secondary);">View:</span>
                        <div class="flex rounded-lg border" style="border-color: var(--border-color); background-color: var(--bg-secondary);">
                            <button onclick="switchView('list')" id="view-list-btn"
                                class="view-toggle-btn rounded-l-lg px-3 py-1.5 text-xs font-medium transition"
                                style="background-color: var(--bg-card); color: var(--text-primary);">
                                List
                            </button>
                            <button onclick="switchView('calendar')" id="view-calendar-btn"
                                class="view-toggle-btn rounded-r-lg px-3 py-1.5 text-xs font-medium transition"
                                style="color: var(--text-secondary);"
                                onmouseover="this.style.color='var(--text-primary)'"
                                onmouseout="this.style.color='var(--text-secondary)'">
                                Calendar
                            </button>
                    </div>
                </div>
                    
                    <!-- Filter Pills -->
                    <div class="flex gap-2">
                        <button onclick="filterBookings('all')"
                            class="filter-btn rounded-lg border bg-transparent px-3 py-1.5 text-xs font-medium transition active"
                            style="border-color: var(--border-color); color: var(--text-primary); background-color: var(--bg-card);"
                            onmouseover="this.style.backgroundColor='var(--bg-card)'"
                            onmouseout="this.style.backgroundColor='var(--bg-card)'"
                            data-filter="all">
                            All
                            </button>
                        <button onclick="filterBookings('upcoming')"
                            class="filter-btn rounded-lg border bg-transparent px-3 py-1.5 text-xs font-medium transition"
                            style="border-color: var(--border-color); color: var(--text-primary);"
                            onmouseover="this.style.backgroundColor='var(--bg-card)'"
                            onmouseout="this.style.backgroundColor='transparent'"
                            data-filter="upcoming">
                            Upcoming
                            </button>
                        <button onclick="filterBookings('past')"
                            class="filter-btn rounded-lg border bg-transparent px-3 py-1.5 text-xs font-medium transition"
                            style="border-color: var(--border-color); color: var(--text-primary);"
                            onmouseover="this.style.backgroundColor='var(--bg-card)'"
                            onmouseout="this.style.backgroundColor='transparent'"
                            data-filter="past">
                            Past
                            </button>
                    </div>
            </div>

                <!-- Bookings List View -->
                <div id="bookingsList" class="space-y-4">
                    <?php if (empty($upcoming_bookings) && empty($past_bookings)): ?>
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="mb-4 rounded-full p-4" style="background-color: var(--bg-card);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" style="color: var(--text-secondary);" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                            </div>
                            <h3 class="mb-1 font-semibold" style="color: var(--text-primary);">No bookings yet</h3>
                            <p class="mb-4 text-sm" style="color: var(--text-secondary);">Start exploring and book your first venue</p>
                            <a href="search.php"
                                class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold transition"
                                style="background-color: var(--accent); color: #ffffff;"
                                onmouseover="this.style.backgroundColor='var(--accent-hover)'" onmouseout="this.style.backgroundColor='var(--accent)'">
                                Discover Venues
                            </a>
                            </div>
                        <?php else: ?>
                        <?php foreach (array_merge($upcoming_bookings, $past_bookings) as $booking):
                            $venue = get_venue_by_id_ctr($booking['venue_id']);
                            $photos = json_decode($venue['photos_json'] ?? '[]', true);
                            $venue_image = !empty($photos) ? '../' . $photos[0] : '../images/portfolio/01.jpg';
                            if (strpos($venue_image, 'uploads/venues/') !== false && strpos($venue_image, '../') === false) {
                                $venue_image = '../' . $venue_image;
                            }
                            $booking_date = strtotime($booking['booking_date'] . ' ' . $booking['start_time']);
                            $is_upcoming = $booking_date > time();
                            $status_class = [
                                'confirmed' => 'border',
                                'requested' => 'border',
                                'completed' => 'border',
                                'cancelled' => 'border',
                            ];
                            $status_style = [
                                'confirmed' => 'background-color: var(--accent); color: #ffffff; border-color: var(--accent);',
                                'requested' => 'background-color: var(--bg-card); color: var(--text-secondary); border-color: var(--border-color);',
                                'completed' => 'background-color: transparent; color: var(--text-secondary); border-color: var(--border-color);',
                                'cancelled' => 'background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.3);',
                            ];
                            $status_icon = [
                                'confirmed' => '<polyline points="20 6 9 17 4 12"/>',
                                'requested' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
                                'completed' => '<polyline points="20 6 9 17 4 12"/>',
                                'cancelled' => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
                            ];
                            ?>
                            <div class="booking-card flex gap-4 rounded-lg border p-4"
                                style="border-color: var(--border-color); background-color: var(--bg-card);"
                                data-booking-type="<?php echo $is_upcoming ? 'upcoming' : 'past'; ?>">
                                <!-- Image -->
                                <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-lg md:h-28 md:w-36">
                                    <img src="<?php echo htmlspecialchars($venue_image); ?>"
                                        alt="<?php echo htmlspecialchars($booking['venue_title']); ?>"
                                        class="h-full w-full object-cover">
                            </div>

                                <!-- Content -->
                                <div class="flex flex-1 flex-col">
                                    <div class="mb-1 flex items-start justify-between">
                                    <div>
                                            <h3 class="font-semibold line-clamp-1" style="color: var(--text-primary);">
                                                <?php echo htmlspecialchars($booking['venue_title']); ?></h3>
                                            <p class="flex items-center gap-1 text-xs" style="color: var(--text-secondary);">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                                    <circle cx="12" cy="10" r="3" />
                                                </svg>
                                                <?php echo htmlspecialchars($booking['location_text']); ?>
                                        </p>
                            </div>
                                        <span
                                            class="inline-flex items-center gap-1 rounded border px-2 py-1 text-xs font-semibold"
                                            style="<?php echo $status_style[$booking['status']] ?? $status_style['requested']; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <?php echo $status_icon[$booking['status']] ?? $status_icon['requested']; ?>
                                            </svg>
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                            </div>
                                
                                    <div class="mt-2 flex flex-wrap gap-3 text-sm text-[#9b9ba1]">
                                        <div class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                                <line x1="16" y1="2" x2="16" y2="6" />
                                                <line x1="8" y1="2" x2="8" y2="6" />
                                                <line x1="3" y1="10" x2="21" y2="10" />
                                            </svg>
                                            <?php echo date('d M Y', strtotime($booking['booking_date'])); ?>
                            </div>
                                        <div class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                                <circle cx="9" cy="7" r="4" />
                                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                            </svg>
                                            <?php echo $booking['guest_count']; ?> guests
                        </div>
                    </div>
                                
                                    <div class="mt-auto flex items-center gap-2 pt-2">
                                        <?php if ($booking['status'] === 'confirmed'): ?>
                                            <button onclick="showQRCode(<?php echo $booking['booking_id']; ?>)"
                                                class="inline-flex items-center justify-center gap-2 rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent px-3 py-1.5 text-xs font-medium text-white transition hover:bg-[#0a0a0a]">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                                    <path d="M8 7h8M8 12h8M8 17h8" />
                                                </svg>
                                                Show QR
                                            </button>
                            <?php endif; ?>
                                        <a href="venue_detail.php?id=<?php echo $booking['venue_id']; ?>"
                                            class="inline-flex items-center justify-center rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-xs font-medium text-white transition hover:bg-[#1a1a1a]">
                                            View Details
                                        </a>
                                        <?php if ($booking['status'] === 'requested'): ?>
                                            <button onclick="cancelBooking(<?php echo $booking['booking_id']; ?>)"
                                                class="inline-flex items-center justify-center rounded-lg border border-transparent bg-transparent px-3 py-1.5 text-xs font-medium text-red-500 transition hover:bg-red-500/10">
                                                Cancel
                        </button>
                                    <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                <!-- Calendar View -->
                <div id="bookingsCalendar" class="hidden">
                    <div id="calendar" style="background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 16px;"></div>
                </div>
            </div>

            <!-- RSVPs Tab -->
            <div id="tab-rsvps" class="tab-content <?php echo $active_tab === 'rsvps' ? '' : 'hidden'; ?>">
                <?php if (empty($user_rsvps)): ?>
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="mb-4 rounded-full bg-[#1a1a1a] p-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#9b9ba1]" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </div>
                        <h3 class="mb-1 font-semibold text-white">No RSVPs yet</h3>
                        <p class="mb-4 text-sm text-[#9b9ba1]">RSVPs for activities will appear here</p>
                        <a href="search.php?type=activity"
                            class="rounded-lg border bg-transparent px-4 py-2 text-sm font-medium transition"
                            style="border-color: var(--border-color); color: var(--text-primary);"
                            onmouseover="this.style.backgroundColor='var(--bg-card)'"
                            onmouseout="this.style.backgroundColor='transparent'">
                            Browse Activities
                        </a>
                            </div>
                        <?php else: ?>
                    <!-- Upcoming RSVPs -->
                    <?php if (!empty($upcoming_rsvps)): ?>
                        <div class="mb-6">
                            <h3 class="mb-4 text-lg font-semibold" style="color: var(--text-primary);">Upcoming</h3>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <?php foreach ($upcoming_rsvps as $rsvp):
                                    $photos = json_decode($rsvp['photos_json'] ?? '[]', true);
                                    $photo = !empty($photos) ? $photos[0] : '../images/portfolio/01.jpg';
                                    if (strpos($photo, 'uploads/') === 0) {
                                        $photo = '../' . $photo;
                                    }
                                    $date_display = !empty($rsvp['start_at']) ? date('M j, Y', strtotime($rsvp['start_at'])) : '';
                                    $time_display = !empty($rsvp['start_at']) ? date('g:i A', strtotime($rsvp['start_at'])) : '';
                                    $price_display = isset($rsvp['is_free']) && $rsvp['is_free'] ? 'FREE' : 'GH₵' . number_format($rsvp['price_min'] ?? 0, 0);
                                ?>
                                    <a href="activity_detail.php?id=<?php echo $rsvp['activity_id']; ?>"
                                        class="group relative flex flex-col overflow-hidden rounded-xl transition-all hover:shadow-lg"
                                        style="background-color: var(--bg-card);">
                                        <div class="relative aspect-[4/3] overflow-hidden" style="background-color: var(--bg-primary);">
                                            <img src="<?php echo htmlspecialchars($photo); ?>"
                                                alt="<?php echo htmlspecialchars($rsvp['activity_title']); ?>"
                                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                            <?php if ($date_display): ?>
                                                <span class="absolute bottom-2 left-2 rounded border backdrop-blur-sm px-2 py-1 text-xs"
                                                    style="border-color: var(--border-color); background-color: var(--bg-primary); opacity: 0.9; color: var(--text-primary);">
                                                    <?php echo $date_display; ?>
                                                </span>
                                            <?php endif; ?>
                                            <span class="absolute top-2 right-2 rounded border px-2 py-1 text-xs font-medium"
                                                style="border-color: var(--accent); background-color: var(--accent); color: #ffffff; opacity: 0.9;">
                                                <?php echo ucfirst($rsvp['status']); ?>
                                            </span>
                                        </div>
                                        <div class="flex flex-1 flex-col p-3">
                                            <h3 class="line-clamp-1 text-base font-semibold transition-colors group-hover:text-[#FF6B35]"
                                                style="color: var(--text-primary);">
                                                <?php echo htmlspecialchars($rsvp['activity_title']); ?>
                                            </h3>
                                            <div class="mt-1 flex items-center gap-2 text-xs" style="color: var(--text-secondary);">
                                                <?php if ($time_display): ?>
                                                    <span><?php echo $time_display; ?></span>
                                                    <span>•</span>
                                                <?php endif; ?>
                                                <span><?php echo htmlspecialchars($rsvp['location_text'] ?? ''); ?></span>
                                            </div>
                                            <div class="mt-auto flex items-end justify-between pt-2">
                                    <div>
                                                    <span class="text-lg font-bold" style="color: var(--text-primary);"><?php echo $price_display; ?></span>
                                    </div>
                                                <?php if ($rsvp['guest_count'] > 1): ?>
                                                    <span class="rounded border px-2 py-1 text-xs"
                                                        style="border-color: var(--border-color); background-color: var(--bg-secondary); color: var(--text-secondary);">
                                                        <?php echo $rsvp['guest_count']; ?> guests
                                    </span>
                                                <?php endif; ?>
                                </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Past RSVPs -->
                    <?php if (!empty($past_rsvps)): ?>
                                    <div>
                            <h3 class="mb-4 text-lg font-semibold" style="color: var(--text-primary);">Past</h3>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <?php foreach ($past_rsvps as $rsvp):
                                    $photos = json_decode($rsvp['photos_json'] ?? '[]', true);
                                    $photo = !empty($photos) ? $photos[0] : '../images/portfolio/01.jpg';
                                    if (strpos($photo, 'uploads/') === 0) {
                                        $photo = '../' . $photo;
                                    }
                                    $date_display = !empty($rsvp['start_at']) ? date('M j, Y', strtotime($rsvp['start_at'])) : '';
                                    $price_display = isset($rsvp['is_free']) && $rsvp['is_free'] ? 'FREE' : 'GH₵' . number_format($rsvp['price_min'] ?? 0, 0);
                                ?>
                                    <a href="activity_detail.php?id=<?php echo $rsvp['activity_id']; ?>"
                                        class="group relative flex flex-col overflow-hidden rounded-xl transition-all hover:shadow-lg opacity-75"
                                        style="background-color: var(--bg-card);">
                                        <div class="relative aspect-[4/3] overflow-hidden" style="background-color: var(--bg-primary);">
                                            <img src="<?php echo htmlspecialchars($photo); ?>"
                                                alt="<?php echo htmlspecialchars($rsvp['activity_title']); ?>"
                                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    </div>
                                        <div class="flex flex-1 flex-col p-3">
                                            <h3 class="line-clamp-1 text-base font-semibold transition-colors group-hover:text-[#FF6B35]"
                                                style="color: var(--text-primary);">
                                                <?php echo htmlspecialchars($rsvp['activity_title']); ?>
                                            </h3>
                                            <div class="mt-1 text-xs" style="color: var(--text-secondary);">
                                                <?php echo $date_display; ?>
                                    </div>
                                    </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                                </div>
                                
            <!-- Collections Tab -->
            <div id="tab-collections" class="tab-content <?php echo $active_tab === 'collections' ? '' : 'hidden'; ?>">
                                    <?php 
                $saved_venues = get_saved_venues_ctr($user_id);
                if ($saved_venues === false)
                    $saved_venues = [];
                
                $saved_activities = get_saved_activities_ctr($user_id);
                if ($saved_activities === false)
                    $saved_activities = [];
                ?>

                <?php if (empty($saved_venues) && empty($saved_activities)): ?>
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="mb-4 rounded-full bg-[#1a1a1a] p-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#9b9ba1]" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M19 21l-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z" />
                            </svg>
                        </div>
                        <h3 class="mb-1 font-semibold text-white">No collections yet</h3>
                        <p class="mb-4 text-sm text-[#9b9ba1]">Save venues and activities to collections to organize your favorites</p>
                        <a href="search.php"
                            class="inline-flex items-center justify-center rounded-lg bg-[#FF6B35] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#ff8c66]">
                            Discover Venues & Activities
                        </a>
                                </div>
                        <?php else: ?>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <!-- Saved Venues -->
                        <?php foreach ($saved_venues as $venue):
                            $photos = json_decode($venue['photos_json'] ?? '[]', true);
                            $photo = $photos[0] ?? 'images/portfolio/01.jpg';
                            if (strpos($photo, '../') === 0) {
                                $photo = substr($photo, 3);
                            }
                            // Adjust path for profile page (which is in public/)
                            if (strpos($photo, 'uploads/') === 0) {
                                $photo = '../' . $photo;
                            } elseif (strpos($photo, 'images/') === 0) {
                                $photo = '../' . $photo;
                            }
                            ?>
                            <a href="venue_detail.php?id=<?php echo $venue['venue_id']; ?>"
                                class="group relative flex flex-col overflow-hidden rounded-xl bg-[#1a1a1a] transition-all hover:shadow-lg">
                                <div class="relative aspect-[4/3] overflow-hidden bg-[#27272a]">
                                    <img src="<?php echo htmlspecialchars($photo); ?>"
                                        alt="<?php echo htmlspecialchars($venue['title']); ?>"
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    <div class="absolute right-2 top-2 rounded-full bg-black/50 p-1.5 backdrop-blur-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#FF6B35]"
                                            viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M19 21l-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z" />
                                        </svg>
                            </div>
                                </div>
                                <div class="flex flex-1 flex-col p-3">
                                    <div class="mb-1 flex items-center gap-2 text-xs text-[#9b9ba1]">
                                        <span
                                            class="font-medium text-white/80"><?php echo htmlspecialchars($venue['cat_name']); ?></span>
                                        <span>•</span>
                                        <span class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                                <circle cx="12" cy="10" r="3" />
                                            </svg>
                                            <?php echo htmlspecialchars($venue['location_text']); ?>
                                        </span>
                                    </div>
                                    <h3
                                        class="mb-1 line-clamp-1 text-base font-semibold text-white transition-colors group-hover:text-[#FF6B35]">
                                        <?php echo htmlspecialchars($venue['title']); ?></h3>
                                    <div class="mt-auto flex items-end justify-between pt-2">
                                    <div>
                                            <span
                                                class="text-lg font-bold text-white">GH₵<?php echo number_format($venue['price_min'], 0); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        <!-- Saved Activities -->
                        <?php foreach ($saved_activities as $activity):
                            $photos = json_decode($activity['photos_json'] ?? '[]', true);
                            $photo = !empty($photos) ? $photos[0] : 'images/portfolio/01.jpg';
                            if (strpos($photo, '../') === 0) {
                                $photo = substr($photo, 3);
                            }
                            if (strpos($photo, 'uploads/') === 0) {
                                $photo = '../' . $photo;
                            } elseif (strpos($photo, 'images/') === 0) {
                                $photo = '../' . $photo;
                            }
                            $date_display = !empty($activity['start_at']) ? date('M j, Y', strtotime($activity['start_at'])) : '';
                            $price_display = isset($activity['is_free']) && $activity['is_free'] ? 'FREE' : 'GH₵' . number_format($activity['price_min'] ?? 0, 0);
                            ?>
                            <a href="activity_detail.php?id=<?php echo $activity['activity_id']; ?>"
                                class="group relative flex flex-col overflow-hidden rounded-xl bg-[#1a1a1a] transition-all hover:shadow-lg">
                                <div class="relative aspect-[4/3] overflow-hidden bg-[#27272a]">
                                    <img src="<?php echo htmlspecialchars($photo); ?>"
                                        alt="<?php echo htmlspecialchars($activity['title']); ?>"
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    <div class="absolute right-2 top-2 rounded-full bg-black/50 p-1.5 backdrop-blur-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#FF6B35]"
                                            viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M19 21l-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z" />
                                        </svg>
                                    </div>
                                    <?php if ($date_display): ?>
                                        <span class="absolute bottom-2 left-2 rounded border backdrop-blur-sm px-2 py-1 text-xs bg-black/50 text-white">
                                            <?php echo $date_display; ?>
                                        </span>
                        <?php endif; ?>
                    </div>
                                <div class="flex flex-1 flex-col p-3">
                                    <div class="mb-1 flex items-center gap-2 text-xs text-[#9b9ba1]">
                                        <span class="font-medium text-white/80">Activity</span>
                                        <?php if (!empty($activity['location_text'])): ?>
                                            <span>•</span>
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                                    <circle cx="12" cy="10" r="3" />
                                                </svg>
                                                <?php echo htmlspecialchars($activity['location_text']); ?>
                                            </span>
                                        <?php endif; ?>
                            </div>
                                    <h3 class="mb-1 line-clamp-1 text-base font-semibold text-white transition-colors group-hover:text-[#FF6B35]">
                                        <?php echo htmlspecialchars($activity['title']); ?>
                                    </h3>
                                    <div class="mt-auto flex items-end justify-between pt-2">
                                    <div>
                                            <span class="text-lg font-bold text-white"><?php echo $price_display; ?></span>
                                    </div>
                                </div>
                            </div>
                            </a>
                            <?php endforeach; ?>
                    </div>
                        <?php endif; ?>
                    </div>

            <!-- Posts Tab (Placeholder) -->
            <div id="tab-posts" class="tab-content <?php echo $active_tab === 'posts' ? '' : 'hidden'; ?>">
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="mb-4 rounded-full bg-[#1a1a1a] p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#9b9ba1]" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <polyline points="21 15 16 10 5 21" />
                        </svg>
                        </div>
                    <h3 class="mb-1 font-semibold text-white">No posts yet</h3>
                    <p class="mb-4 text-sm text-[#9b9ba1]">Share your experiences and photos from venues</p>
                        </div>
                    </div>

            <!-- Notifications Tab -->
            <div id="tab-notifications" class="tab-content <?php echo $active_tab === 'notifications' ? '' : 'hidden'; ?>">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold" style="color: var(--text-primary);">All Notifications</h2>
                    <?php if ($unread_count > 0): ?>
                        <button onclick="markAllAsRead()" 
                            class="rounded-lg border px-3 py-1.5 text-xs font-medium transition"
                            style="border-color: var(--border-color); color: var(--accent);"
                            onmouseover="this.style.backgroundColor='var(--bg-card)'"
                            onmouseout="this.style.backgroundColor='transparent'">
                            Mark all as read
                        </button>
                    <?php endif; ?>
                </div>

                <div class="space-y-3">
                    <?php if (empty($notifications)): ?>
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="mb-4 rounded-full p-4" style="background-color: var(--bg-card);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" style="color: var(--text-secondary);" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                </svg>
            </div>
                            <h3 class="mb-1 font-semibold" style="color: var(--text-primary);">No notifications yet</h3>
                            <p class="mb-4 text-sm" style="color: var(--text-secondary);">You'll see notifications about bookings, reviews, and more here</p>
        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): 
                            // Get icon based on notification type
                            $icon_svg = '';
                            $icon_color = 'var(--text-secondary)';
                            switch ($notif['notification_type']) {
                                case 'booking_request':
                                case 'booking_confirmed':
                                case 'booking_declined':
                                case 'booking_cancelled':
                                    $icon_svg = '<rect x="3" y="4" width="18" height="18" rx="2" ry="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="3" y1="10" x2="21" y2="10" />';
                                    $icon_color = 'var(--accent)';
                                    break;
                                case 'payment_received':
                                    $icon_svg = '<line x1="12" y1="2" x2="12" y2="22" /><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />';
                                    $icon_color = '#4CAF50';
                                    break;
                                case 'review_posted':
                                case 'review_approved':
                                    $icon_svg = '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />';
                                    $icon_color = '#FFD700';
                                    break;
                                case 'venue_approved':
                                    $icon_svg = '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" /><polyline points="22 4 12 14.01 9 11.01" />';
                                    $icon_color = '#4CAF50';
                                    break;
                                case 'venue_rejected':
                                    $icon_svg = '<circle cx="12" cy="12" r="10" /><line x1="15" y1="9" x2="9" y2="15" /><line x1="9" y1="9" x2="15" y2="15" />';
                                    $icon_color = '#ef4444';
                                    break;
                                default:
                                    $icon_svg = '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />';
                                    $icon_color = 'var(--text-secondary)';
                            }
                            $is_unread = !$notif['is_read'];
                            ?>
                            <div class="notification-item flex gap-3 rounded-lg border p-4 transition cursor-pointer <?php echo $is_unread ? 'border-l-4' : ''; ?>"
                                style="border-color: <?php echo $is_unread ? 'var(--accent)' : 'var(--border-color)'; ?>; background-color: var(--bg-card); border-left-color: <?php echo $is_unread ? 'var(--accent)' : 'var(--border-color)'; ?>;"
                                onmouseover="this.style.backgroundColor='var(--bg-secondary)'"
                                onmouseout="this.style.backgroundColor='var(--bg-card)'"
                                onclick="markAsRead(<?php echo $notif['notification_id']; ?>, this, <?php echo $notif['related_booking_id'] ? $notif['related_booking_id'] : 'null'; ?>, <?php echo $notif['related_venue_id'] ? $notif['related_venue_id'] : 'null'; ?>)"
                                data-read="<?php echo $notif['is_read'] ? '1' : '0'; ?>">
                                <!-- Icon -->
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full" style="background-color: <?php echo $is_unread ? 'var(--accent)' : 'var(--bg-secondary)'; ?>; opacity: <?php echo $is_unread ? '0.2' : '1'; ?>;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                                        stroke="<?php echo $icon_color; ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <?php echo $icon_svg; ?>
                                    </svg>
    </div>
                                
                                <!-- Content -->
                                <div class="flex-1">
                                    <div class="mb-1 flex items-start justify-between">
                                        <h3 class="font-semibold" style="color: var(--text-primary);">
                                            <?php echo htmlspecialchars($notif['title']); ?>
                                            <?php if ($is_unread): ?>
                                                <span class="ml-2 inline-block h-2 w-2 rounded-full" style="background-color: var(--accent);"></span>
                                            <?php endif; ?>
            </h3>
                                        <span class="text-xs" style="color: var(--text-muted);">
                                            <?php echo time_ago($notif['created_at']); ?>
                                        </span>
                                    </div>
                                    <p class="text-sm" style="color: var(--text-secondary);">
                                        <?php echo htmlspecialchars($notif['message']); ?>
                                    </p>
                                    <?php if ($notif['related_venue_id']): ?>
                                        <p class="mt-1 text-xs" style="color: var(--text-muted);">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="inline h-3 w-3" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                                <circle cx="12" cy="10" r="3" />
                                            </svg>
                                            <?php echo htmlspecialchars($notif['venue_title'] ?? 'Venue'); ?>
                                        </p>
                                    <?php endif; ?>
                        </div>
                    </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- QR Code Modal -->
    <div id="qrModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm">
        <div class="relative w-full max-w-sm rounded-lg border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] p-6 shadow-xl">
            <button onclick="closeQRModal()" class="absolute right-4 top-4 text-[#9b9ba1] hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
            <h3 class="mb-2 text-lg font-semibold text-white">Booking QR Code</h3>
            <p class="mb-4 text-sm text-[#9b9ba1]">Show this at the venue for verification</p>
            <div class="mb-4 flex justify-center rounded-lg bg-white p-6">
                <canvas id="qrCanvas"></canvas>
            </div>
            <p class="text-center text-sm text-[#9b9ba1]" id="qrBookingId"></p>
        </div>
        </div>
        
<!-- Edit Profile Modal -->
    <div id="editProfileModal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm">
        <div class="relative w-full max-w-md rounded-lg border border-[rgba(39,39,42,0.7)] bg-[#1a1a1a] p-6 shadow-xl">
            <button onclick="closeEditModal()" class="absolute right-4 top-4 text-[#9b9ba1] hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
            <h3 class="mb-6 text-lg font-semibold text-white">Edit Profile</h3>
            <div id="profileAlertMessage" class="hidden mb-4 p-3 rounded-lg text-sm"></div>
        <form id="editProfileForm">
                <div class="mb-4">
                    <label for="edit_name" class="mb-2 block text-sm font-medium text-white">Full Name *</label>
                    <input type="text" id="edit_name" name="name"
                        class="flex h-10 w-full rounded-lg border border-[rgba(39,39,42,0.7)] bg-[#0a0a0a] px-3 py-2 text-sm text-white placeholder:text-[#9b9ba1] focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                       value="<?php echo htmlspecialchars($user['customer_name']); ?>" required>
            </div>
                <div class="mb-4">
                    <label for="edit_email" class="mb-2 block text-sm font-medium text-white">Email Address</label>
                    <input type="email" id="edit_email"
                        class="flex h-10 w-full rounded-lg border border-[rgba(39,39,42,0.7)] bg-[#0a0a0a] px-3 py-2 text-sm text-white opacity-60 cursor-not-allowed"
                        value="<?php echo htmlspecialchars($user['customer_email']); ?>" disabled>
                    <small class="mt-1 block text-xs text-[#9b9ba1]">Email cannot be changed</small>
            </div>
                <div class="mb-4 grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit_country" class="mb-2 block text-sm font-medium text-white">Country *</label>
                        <input type="text" id="edit_country" name="country"
                            class="flex h-10 w-full rounded-lg border border-[rgba(39,39,42,0.7)] bg-[#0a0a0a] px-3 py-2 text-sm text-white placeholder:text-[#9b9ba1] focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                               value="<?php echo htmlspecialchars($user['customer_country']); ?>" required>
                    </div>
                    <div>
                        <label for="edit_city" class="mb-2 block text-sm font-medium text-white">City *</label>
                        <input type="text" id="edit_city" name="city"
                            class="flex h-10 w-full rounded-lg border border-[rgba(39,39,42,0.7)] bg-[#0a0a0a] px-3 py-2 text-sm text-white placeholder:text-[#9b9ba1] focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                               value="<?php echo htmlspecialchars($user['customer_city']); ?>" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="edit_contact" class="mb-2 block text-sm font-medium text-white">Phone Number *</label>
                    <input type="tel" id="edit_contact" name="contact"
                        class="flex h-10 w-full rounded-lg border border-[rgba(39,39,42,0.7)] bg-[#0a0a0a] px-3 py-2 text-sm text-white placeholder:text-[#9b9ba1] focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                       value="<?php echo htmlspecialchars($user['customer_contact']); ?>" required>
            </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeEditModal()"
                        class="flex-1 rounded-lg border border-[rgba(39,39,42,0.7)] bg-transparent px-4 py-2 text-sm font-medium text-white transition hover:bg-[#0a0a0a]">
                        Cancel
                </button>
                    <button type="submit" id="submitProfileBtn"
                        class="flex-1 rounded-lg bg-[#FF6B35] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#ff8c66]">
                        Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
// Tab switching
function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-[#0a0a0a]', 'text-white', 'shadow-sm');
                btn.style.backgroundColor = '';
                btn.style.color = '';
            });
    
            document.getElementById('tab-' + tabName).classList.remove('hidden');
            const activeBtn = document.querySelector(`[data-tab="${tabName}"]`);
            if (activeBtn) {
                activeBtn.style.backgroundColor = 'var(--bg-primary)';
                activeBtn.style.color = 'var(--text-primary)';
                activeBtn.classList.add('shadow-sm');
    }
        }

        // View switching (List/Calendar)
        let currentView = 'list';
        let calendarInstance = null;

        function switchView(view) {
            currentView = view;
            const listView = document.getElementById('bookingsList');
            const calendarView = document.getElementById('bookingsCalendar');
            const listBtn = document.getElementById('view-list-btn');
            const calendarBtn = document.getElementById('view-calendar-btn');

            if (view === 'list') {
                listView.classList.remove('hidden');
                calendarView.classList.add('hidden');
                listBtn.style.backgroundColor = 'var(--bg-card)';
                listBtn.style.color = 'var(--text-primary)';
                calendarBtn.style.backgroundColor = 'transparent';
                calendarBtn.style.color = 'var(--text-secondary)';
            } else {
                listView.classList.add('hidden');
                calendarView.classList.remove('hidden');
                calendarBtn.style.backgroundColor = 'var(--bg-card)';
                calendarBtn.style.color = 'var(--text-primary)';
                listBtn.style.backgroundColor = 'transparent';
                listBtn.style.color = 'var(--text-secondary)';
                
                // Initialize calendar if not already done
                if (!calendarInstance) {
                    initializeCalendar();
                }
            }
        }

        // Initialize FullCalendar
        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');
            
            // Prepare bookings data for calendar
            const bookingsData = [
                <?php 
                foreach ($all_bookings as $booking): 
                    $venue = get_venue_by_id_ctr($booking['venue_id']);
                    $booking_date = $booking['booking_date'];
                    $start_time = $booking['start_time'];
                    $end_time = $booking['end_time'];
                    $is_upcoming = strtotime($booking_date . ' ' . $start_time) > time();
                    $status = $booking['status'];
                    
                    // Determine color based on status
                    $color = '#FF6B35'; // default accent
                    if ($status === 'cancelled') $color = '#ef4444';
                    else if ($status === 'completed') $color = '#9b9ba1';
                    else if ($status === 'requested') $color = '#9b9ba1';
                ?>
                {
                    id: <?php echo $booking['booking_id']; ?>,
                    title: '<?php echo addslashes($booking['venue_title']); ?>',
                    start: '<?php echo $booking_date . 'T' . $start_time; ?>',
                    end: '<?php echo $booking_date . 'T' . $end_time; ?>',
                    backgroundColor: '<?php echo $color; ?>',
                    borderColor: '<?php echo $color; ?>',
                    textColor: '#ffffff',
                    extendedProps: {
                        bookingId: <?php echo $booking['booking_id']; ?>,
                        venueId: <?php echo $booking['venue_id']; ?>,
                        status: '<?php echo $status; ?>',
                        guestCount: <?php echo $booking['guest_count']; ?>,
                        totalAmount: <?php echo $booking['total_amount']; ?>
                    }
                },
                <?php endforeach; ?>
                <?php 
                // Add RSVPs to calendar
                foreach ($user_rsvps as $rsvp): 
                    if (empty($rsvp['start_at'])) continue;
                    $start_datetime = date('Y-m-d\TH:i:s', strtotime($rsvp['start_at']));
                    $end_datetime = !empty($rsvp['end_at']) ? date('Y-m-d\TH:i:s', strtotime($rsvp['end_at'])) : date('Y-m-d\TH:i:s', strtotime($rsvp['start_at'] . ' +2 hours'));
                    $rsvp_color = $rsvp['status'] === 'going' ? '#FF6B35' : '#9b9ba1';
                ?>
                {
                    id: 'rsvp_<?php echo $rsvp['rsvp_id']; ?>',
                    title: '<?php echo addslashes($rsvp['activity_title']); ?>',
                    start: '<?php echo $start_datetime; ?>',
                    end: '<?php echo $end_datetime; ?>',
                    backgroundColor: '<?php echo $rsvp_color; ?>',
                    borderColor: '<?php echo $rsvp_color; ?>',
                    textColor: '#ffffff',
                    extendedProps: {
                        rsvpId: <?php echo $rsvp['rsvp_id']; ?>,
                        activityId: <?php echo $rsvp['activity_id']; ?>,
                        status: '<?php echo $rsvp['status']; ?>',
                        guestCount: <?php echo $rsvp['guest_count']; ?>,
                        type: 'rsvp'
                    }
                },
                <?php endforeach; ?>
            ];

            calendarInstance = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listWeek'
                },
                events: bookingsData,
                eventClick: function(info) {
                    // Navigate to booking detail or activity detail
                    if (info.event.extendedProps.type === 'rsvp') {
                        window.location.href = 'activity_detail.php?id=' + info.event.extendedProps.activityId;
                    } else {
                        window.location.href = 'booking_confirmation.php?booking_id=' + info.event.extendedProps.bookingId;
                    }
                },
                eventContent: function(arg) {
                    // Custom event rendering
                    if (arg.event.extendedProps.type === 'rsvp') {
                        const statusBadge = arg.event.extendedProps.status === 'going' ? '✓' : '★';
                        return {
                            html: '<div style="padding: 2px 4px; font-size: 11px; font-weight: 500;">' + 
                                  statusBadge + ' ' + arg.event.title + 
                                  '</div>'
                        };
                    } else {
                        const status = arg.event.extendedProps.status;
                        const statusBadge = status === 'confirmed' ? '✓' : status === 'requested' ? '⏳' : status === 'cancelled' ? '✕' : '✓';
                        return {
                            html: '<div style="padding: 2px 4px; font-size: 11px; font-weight: 500;">' + 
                                  statusBadge + ' ' + arg.event.title + 
                                  '</div>'
                        };
                    }
                },
                height: 'auto',
                themeSystem: 'standard',
                // Theme-aware colors
                eventBackgroundColor: 'var(--accent)',
                eventBorderColor: 'var(--accent)',
                // Custom CSS variables for theme
                eventDidMount: function(info) {
                    // Apply theme-aware styling
                    const isLight = document.documentElement.getAttribute('data-theme') === 'light';
                    if (isLight) {
                        info.el.style.color = '#ffffff';
                    }
                }
            });

            calendarInstance.render();
            
            // Update calendar colors when theme changes
            const themeObserver = new MutationObserver(function(mutations) {
                calendarInstance.render();
            });
            themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
        }

        // Filter bookings
        function filterBookings(filter) {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
                btn.style.backgroundColor = 'transparent';
                btn.style.borderColor = 'var(--border-color)';
            });
            const activeFilterBtn = document.querySelector(`[data-filter="${filter}"]`);
            activeFilterBtn.classList.add('active');
            activeFilterBtn.style.backgroundColor = 'var(--accent)';
            activeFilterBtn.style.borderColor = 'var(--accent)';
            activeFilterBtn.style.color = '#ffffff';

            document.querySelectorAll('.booking-card').forEach(card => {
                if (filter === 'all') {
                    card.style.display = 'flex';
                } else {
                    const type = card.dataset.bookingType;
                    card.style.display = (type === filter) ? 'flex' : 'none';
                }
            });
        }

        // QR Code Modal
        function showQRCode(bookingId) {
            const qrData = JSON.stringify({
                bookingId: bookingId,
                verified: true
            });

            document.getElementById('qrBookingId').textContent = 'Booking ID: BK' + String(bookingId).padStart(6, '0');
            document.getElementById('qrModal').classList.remove('hidden');
            document.getElementById('qrModal').classList.add('flex');

            QRCode.toCanvas(document.getElementById('qrCanvas'), qrData, {
                width: 180,
                margin: 2,
                color: { dark: '#000000', light: '#FFFFFF' }
            });
        }

        function closeQRModal() {
            document.getElementById('qrModal').classList.add('hidden');
            document.getElementById('qrModal').classList.remove('flex');
}

        // Edit Profile Modal
function openEditModal() {
            document.getElementById('editProfileModal').classList.remove('hidden');
            document.getElementById('editProfileModal').classList.add('flex');
}

function closeEditModal() {
            document.getElementById('editProfileModal').classList.add('hidden');
            document.getElementById('editProfileModal').classList.remove('flex');
}

        // Cancel Booking
        function cancelBooking(bookingId) {
            if (!confirm('Are you sure you want to cancel this booking?')) return;

            $.ajax({
                url: '../actions/booking_update_status_action.php',
                type: 'POST',
                data: { booking_id: bookingId, status: 'cancelled' },
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        location.reload();
                    } else {
                        alert(result.message || 'Failed to cancel booking');
                    }
                },
                error: function () {
                    alert('An error occurred. Please try again.');
                }
            });
}

        // Profile form submission
        $('#editProfileForm').submit(function (e) {
    e.preventDefault();
    
    const submitBtn = $('#submitProfileBtn');
            submitBtn.prop('disabled', true).text('Updating...');
    
    $.ajax({
        url: '../actions/update_profile_action.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
                success: function (result) {
            if (result.success) {
                showProfileAlert(result.message, 'success');
                        setTimeout(() => location.reload(), 2000);
            } else {
                showProfileAlert(result.message, 'danger');
                        submitBtn.prop('disabled', false).text('Save Changes');
            }
        },
                error: function () {
            showProfileAlert('An error occurred. Please try again.', 'danger');
                    submitBtn.prop('disabled', false).text('Save Changes');
        }
    });
});

        function showProfileAlert(message, type) {
            const alertDiv = $('#profileAlertMessage');
            alertDiv.removeClass('hidden');
            alertDiv.className = 'mb-4 rounded-lg p-3 text-sm ' +
                (type === 'success' ? 'bg-green-500/10 border border-green-500/30 text-green-500' : 'bg-red-500/10 border border-red-500/30 text-red-500');
            alertDiv.text(message);

            setTimeout(() => alertDiv.addClass('hidden'), 5000);
        }

        // Initialize filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.filter-btn').forEach(b => {
                    b.classList.remove('active', 'bg-[#FF6B35]', 'border-[#FF6B35]');
                    b.classList.add('bg-transparent');
                });
                this.classList.add('active', 'bg-[#FF6B35]', 'border-[#FF6B35]');
    });
});

        // Mark notification as read
        function markAsRead(notificationId, element, bookingId, venueId) {
            if (element.dataset.read === '1') {
                // Already read, just navigate if needed
                if (bookingId) {
                    window.location.href = 'profile.php?tab=bookings';
                } else if (venueId) {
                    window.location.href = 'venue_detail.php?id=' + venueId;
                }
                return;
            }

            $.ajax({
                url: '../actions/mark_notification_read_action.php',
                type: 'POST',
                data: { notification_id: notificationId },
                dataType: 'json',
                success: function(result) {
                    if (result.success) {
                        // Update UI
                        element.style.borderLeftColor = 'var(--border-color)';
                        element.style.borderLeftWidth = '1px';
                        element.querySelector('.rounded-full').style.backgroundColor = 'var(--bg-secondary)';
                        element.querySelector('.rounded-full').style.opacity = '1';
                        const dot = element.querySelector('.rounded-full[style*="background-color: var(--accent)"]');
                        if (dot) dot.remove();
                        element.dataset.read = '1';
                        
                        // Update badge count
                        if (result.unread_count !== undefined) {
                            const badge = document.querySelector('[data-tab="notifications"] .rounded-full');
                            if (badge) {
                                if (result.unread_count > 0) {
                                    badge.textContent = result.unread_count;
                                    badge.style.display = 'flex';
                                } else {
                                    badge.style.display = 'none';
                                }
                            }
                        }
                        
                        // Navigate if related item exists
                        if (bookingId) {
                            setTimeout(() => {
                                window.location.href = 'profile.php?tab=bookings';
                            }, 300);
                        } else if (venueId) {
                            setTimeout(() => {
                                window.location.href = 'venue_detail.php?id=' + venueId;
                            }, 300);
                        }
                    }
                },
                error: function() {
                    console.error('Failed to mark notification as read');
                }
            });
        }

        // Mark all notifications as read
        function markAllAsRead() {
            if (!confirm('Mark all notifications as read?')) return;

            $.ajax({
                url: '../actions/mark_all_notifications_read_action.php',
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    if (result.success) {
                        // Update all notification items
                        document.querySelectorAll('.notification-item').forEach(item => {
                            item.style.borderLeftColor = 'var(--border-color)';
                            item.style.borderLeftWidth = '1px';
                            const iconDiv = item.querySelector('.rounded-full');
                            if (iconDiv) {
                                iconDiv.style.backgroundColor = 'var(--bg-secondary)';
                                iconDiv.style.opacity = '1';
                            }
                            const dot = item.querySelector('.rounded-full[style*="background-color: var(--accent)"]');
                            if (dot) dot.remove();
                            item.dataset.read = '1';
                        });
                        
                        // Hide mark all button
                        const markAllBtn = document.querySelector('button[onclick="markAllAsRead()"]');
                        if (markAllBtn) markAllBtn.style.display = 'none';
                        
                        // Update badge
                        const badge = document.querySelector('[data-tab="notifications"] .rounded-full');
                        if (badge) badge.style.display = 'none';
                        
                        location.reload();
                    } else {
                        alert('Failed to mark all notifications as read');
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