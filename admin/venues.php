<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/customer_controller.php');
require_once(__DIR__ . '/../controllers/review_controller.php');

// Check if admin
require_admin();

// Get all venues
$all_venues = get_all_venues_admin_ctr();
if ($all_venues === false) $all_venues = [];

// Get pending counts
$pending_venues = is_array($all_venues) ? count(array_filter($all_venues, function($v) { return $v['status'] == 'pending'; })) : 0;
$pending_reviews = get_pending_reviews_ctr();
$pending_review_count = is_array($pending_reviews) ? count($pending_reviews) : 0;

// Get user info for header
$user = get_user();
if (!$user || !is_array($user)) {
    $user = ['name' => 'Admin', 'email' => ''];
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Venue Management - Admin - Go Outside</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    border: '#1b1b1e',
                    background: '#0a0a0a',
                    foreground: '#fafafa',
                    card: '#09090b',
                    'card-foreground': '#fafafa',
                    muted: '#27272a',
                    'muted-foreground': '#a1a1aa',
                }
            }
        }
    }
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
* { font-family: 'Inter', 'Segoe UI', sans-serif; }
:root {
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
}

[data-theme="light"] {
    --background: oklch(0.99 0 0);
    --foreground: oklch(0.12 0.01 50);
    --card: oklch(0.99 0 0);
    --card-foreground: oklch(0.12 0.01 50);
    --primary: oklch(0.55 0.18 45);
    --primary-foreground: oklch(0.99 0 0);
    --secondary: oklch(0.96 0 0);
    --muted: oklch(0.96 0 0);
    --muted-foreground: oklch(0.45 0.02 80);
    --accent: oklch(0.55 0.18 45);
    --destructive: oklch(0.5 0.2 25);
    --destructive-foreground: oklch(0.99 0 0);
    --border: oklch(0.9 0 0);
    --input: oklch(0.96 0 0);
    --ring: oklch(0.55 0.18 45);
}

body { background: var(--background); color: var(--foreground); transition: background-color 0.3s ease, color 0.3s ease; }
.border-border { border-color: var(--border) !important; }
.border-input { border-color: var(--border) !important; }
</style>
</head>

<body class="min-h-screen bg-background">
    <!-- Admin Header - Matching site navigation style -->
    <header class="go-nav" style="position: fixed; top: 0; left: 0; right: 0; z-index: 50; font-family: 'Inter', 'Segoe UI', sans-serif; color: var(--foreground); background: var(--card); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;">
        <div class="go-nav__container" style="max-width: 1280px; margin: 0 auto; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div class="flex items-center gap-4">
                <a href="../index.php" class="go-nav__logo" style="font-weight: 700; letter-spacing: 0.08em; font-size: 18px; color: var(--foreground); text-decoration: none;">
                    GO OUTSIDE
                </a>
                <span class="hidden rounded-md px-2.5 py-0.5 text-xs font-semibold uppercase sm:inline-flex" style="background: var(--destructive); color: var(--destructive-foreground);">
                    ADMIN
                </span>
    </div>

            <div class="go-nav__icons" style="display: flex; gap: 8px; align-items: center;">
                <button id="themeToggle" class="go-nav__icon relative" aria-label="Toggle theme" style="width: 40px; height: 40px; min-width: 40px; min-height: 40px; border: 0; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; color: var(--muted-foreground); cursor: pointer; transition: all 0.2s ease; background: transparent; padding: 0; margin: 0; box-sizing: border-box; outline: none;" onmouseover="this.style.background='var(--secondary)'" onmouseout="this.style.background='transparent'">
                    <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                    <svg id="sunIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"/>
                        <path d="M12 2v2"/>
                        <path d="M12 20v2"/>
                        <path d="m4.93 4.93 1.41 1.41"/>
                        <path d="m17.66 17.66 1.41 1.41"/>
                        <path d="M2 12h2"/>
                        <path d="M20 12h2"/>
                        <path d="m6.34 17.66-1.41 1.41"/>
                        <path d="m19.07 4.93-1.41 1.41"/>
                    </svg>
                </button>
                <a href="dashboard.php" class="go-nav__icon relative" aria-label="Dashboard" style="width: 40px; height: 40px; min-width: 40px; min-height: 40px; border: 0; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; color: var(--muted-foreground); cursor: pointer; transition: all 0.2s ease; background: transparent; padding: 0; margin: 0; box-sizing: border-box; outline: none; text-decoration: none;" onmouseover="this.style.background='var(--secondary)'" onmouseout="this.style.background='transparent'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </a>
                <div class="relative">
                    <button class="go-nav__menu-btn" aria-label="Menu" style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border: 1px solid rgba(212, 212, 216, 0.2); border-radius: 999px; background: rgba(250, 250, 250, 0.08); color: #d4d4d8; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; outline: none;">
                        <span><?php echo htmlspecialchars($user['name'] ?? 'Admin'); ?></span>
                        <span class="go-nav__menu-avatar" style="width: 20px; height: 20px; border-radius: 50%; background: #d4d4d8; display: inline-flex; align-items: center; justify-content: center; overflow: hidden;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#1f1f1f" stroke-width="2" style="stroke: #1f1f1f; fill: none;">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                    </button>
                </div>
    </div>
</div>
    </header>
    <style>
    .go-nav__icon:hover { color: #fafafa; }
    .go-nav__menu-btn:hover {
        border-color: rgba(212, 212, 216, 0.3);
        color: #fafafa;
        background: rgba(250, 250, 250, 0.12);
    }
    </style>

    <main class="container mx-auto px-4 py-6" style="padding-top: 80px;">
        <!-- Welcome Section -->
        <div class="mb-6">
            <div class="mb-4 flex items-center gap-4">
                <a href="dashboard.php" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-secondary hover:text-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                        <path d="m12 19-7-7 7-7"/>
                        <path d="M19 12H5"/>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
            <h1 class="text-2xl font-bold text-foreground">Venue Management</h1>
            <p class="text-muted-foreground">Review and manage all venue listings</p>
            </div>

            <!-- Filter Bar -->
        <div class="mb-6 rounded-lg border border-border bg-card p-4">
            <div class="flex flex-col gap-4 sm:flex-row">
                <div class="flex-1">
                    <select id="statusFilter" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:ring-offset-background">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                <div class="flex-1">
                    <input type="text" id="searchInput" placeholder="Search venues..." 
                           class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:ring-offset-background">
                    </div>
                </div>
            </div>

            <!-- Alert Message -->
        <div id="alertMessage" class="mb-4 hidden rounded-md p-3 text-sm" role="alert"></div>

            <!-- Venues Table -->
        <div class="overflow-x-auto rounded-lg border border-border bg-card">
            <table class="w-full" id="venuesTable">
                    <thead>
                    <tr class="border-b border-border">
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Venue Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Owner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                <tbody class="divide-y divide-border">
                        <?php if (empty($all_venues)): ?>
                            <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-4 text-muted-foreground opacity-50">
                                        <rect width="16" height="20" x="4" y="2" rx="2" ry="2"/>
                                        <path d="M9 22v-4h6v4"/>
                                        <path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/>
                                        <path d="M12 10h.01"/><path d="M12 14h.01"/>
                                        <path d="M16 10h.01"/><path d="M16 14h.01"/>
                                        <path d="M8 10h.01"/><path d="M8 14h.01"/>
                                    </svg>
                                    <p class="text-sm font-medium text-foreground">No venues found</p>
                                </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_venues as $venue): ?>
                        <tr data-status="<?php echo $venue['status']; ?>" class="hover:bg-muted/30 transition-colors">
                            <td class="whitespace-nowrap px-6 py-4">
                                <strong class="text-sm font-semibold text-foreground"><?php echo htmlspecialchars($venue['title']); ?></strong>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-muted-foreground">
                                <?php echo htmlspecialchars($venue['owner_name']); ?>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-muted-foreground">
                                <?php echo htmlspecialchars($venue['cat_name']); ?>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-muted-foreground">
                                <?php echo htmlspecialchars($venue['location_text']); ?>
                                </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    <?php
                                    $status = $venue['status'];
                                    if ($status == 'pending') echo 'border-orange-500/30 bg-orange-950/20 text-orange-500';
                                    elseif ($status == 'approved') echo 'border-emerald-500/30 bg-emerald-950/20 text-emerald-500';
                                    else echo 'border-red-500/30 bg-red-950/20 text-red-500';
                                    ?>">
                                    <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-muted-foreground">
                                <?php echo date('M d, Y', strtotime($venue['created_at'])); ?>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex flex-col gap-2">
                                        <a href="../public/venue_detail.php?id=<?php echo $venue['venue_id']; ?>" 
                                       class="inline-flex items-center justify-center rounded-md border border-input bg-background px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-secondary hover:text-foreground">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        View
                                        </a>
                                        <select onchange="changeVenueStatus(<?php echo $venue['venue_id']; ?>, this.value)" 
                                            class="flex h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:ring-offset-background"
                                            style="appearance: none; background-image: url('data:image/svg+xml;utf8,<svg fill=\"%23FF6B35\" height=\"20\" viewBox=\"0 0 24 24\" width=\"20\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M7 10l5 5 5-5z\"/></svg>'); background-repeat: no-repeat; background-position: right 4px center; background-size: 16px; padding-right: 24px;">
                                        <option value="">Change Status</option>
                                            <option value="pending" <?php echo $venue['status'] == 'pending' ? 'disabled' : ''; ?>>
                                            Pending
                                            </option>
                                            <option value="approved" <?php echo $venue['status'] == 'approved' ? 'disabled' : ''; ?>>
                                            Approved
                                            </option>
                                            <option value="rejected" <?php echo $venue['status'] == 'rejected' ? 'disabled' : ''; ?>>
                                            Rejected
                                            </option>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
    </main>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
// Show alert message
function showAlert(message, type) {
    const alertDiv = $('#alertMessage');
    alertDiv.removeClass('hidden');
    alertDiv.css({
        'background': type === 'success' ? 'oklch(0.3 0.15 145)' : 'oklch(0.3 0.2 25)',
        'color': type === 'success' ? 'oklch(0.85 0.1 145)' : 'oklch(0.85 0.1 25)',
        'border-color': type === 'success' ? 'oklch(0.4 0.15 145)' : 'oklch(0.4 0.2 25)'
    });
    alertDiv.html(message);
    alertDiv.show();
    
    setTimeout(function() {
        alertDiv.fadeOut();
    }, 5000);
}

// Change venue status from dropdown
function changeVenueStatus(venueId, status) {
    if (!status) return; // If "Change Status" option selected, do nothing
    
    const statusText = status.charAt(0).toUpperCase() + status.slice(1);
    if (!confirm('Are you sure you want to change this venue status to "' + statusText + '"?')) {
        // Reset dropdown to default
        event.target.value = '';
        return;
    }
    
    $.ajax({
        url: '../actions/venue_update_status_action.php',
        type: 'POST',
        data: {
            venue_id: venueId,
            status: status
        },
        dataType: 'json',
        success: function(result) {
            if (result.success) {
                showAlert(result.message, 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showAlert(result.message, 'danger');
                event.target.value = ''; // Reset dropdown
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'danger');
            event.target.value = ''; // Reset dropdown
        }
    });
}

// Filter venues
function filterVenues() {
    const statusFilter = $('#statusFilter').val().toLowerCase();
    const searchText = $('#searchInput').val().toLowerCase();
    
    $('#venuesTable tbody tr').each(function() {
        const row = $(this);
        const status = row.data('status');
        const text = row.text().toLowerCase();
        
        let showRow = true;
        
        if (statusFilter && status !== statusFilter) {
            showRow = false;
        }
        
        if (searchText && !text.includes(searchText)) {
            showRow = false;
        }
        
        row.toggle(showRow);
    });
}

// Real-time search
$('#searchInput').on('keyup', filterVenues);
$('#statusFilter').on('change', filterVenues);

// Theme toggle functionality
(function() {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const sunIcon = document.getElementById('sunIcon');
    const html = document.documentElement;
    
    // Get saved theme or default to dark
    const savedTheme = localStorage.getItem('theme') || 'dark';
    html.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);
    
    themeToggle.addEventListener('click', function() {
        const currentTheme = html.getAttribute('data-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });
    
    function updateThemeIcon(theme) {
        if (theme === 'light') {
            themeIcon.style.display = 'block';
            sunIcon.style.display = 'none';
        } else {
            themeIcon.style.display = 'none';
            sunIcon.style.display = 'block';
        }
    }
})();
</script>
</body>
</html>
