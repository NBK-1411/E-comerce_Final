<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/review_controller.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');

// Check if admin
require_admin();

// Get all reviews
$all_reviews = get_all_reviews_ctr();
if ($all_reviews === false) $all_reviews = [];

// Get pending counts
$all_venues = get_all_venues_admin_ctr();
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
<title>Review Moderation - Admin - Go Outside</title>
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
            <h1 class="text-2xl font-bold text-foreground">Review Moderation</h1>
            <p class="text-muted-foreground">Moderate and manage venue reviews</p>
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
                            <option value="flagged">Flagged</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Alert Message -->
        <div id="alertMessage" class="mb-4 hidden rounded-md p-3 text-sm" role="alert"></div>

            <!-- Reviews List -->
        <div id="reviewsList" class="space-y-4">
                <?php if (empty($all_reviews)): ?>
                <div class="flex flex-col items-center justify-center rounded-lg border border-border bg-card p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-4 text-muted-foreground opacity-50">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    <h3 class="mb-1 text-lg font-semibold text-foreground">No Reviews</h3>
                    <p class="text-sm text-muted-foreground">Reviews will appear here as they are submitted</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($all_reviews as $review): ?>
                <div class="review-card rounded-lg border border-border bg-card p-6" data-status="<?php echo $review['moderation_status']; ?>">
                    <div class="mb-4 flex items-start justify-between">
                        <div class="flex-1">
                            <div class="mb-2 flex items-center gap-2">
                                <strong class="text-base font-semibold text-foreground">
                                    <?php echo htmlspecialchars($review['customer_name']); ?>
                                </strong>
                                <?php if ($review['is_verified']): ?>
                                    <span class="inline-flex items-center rounded-full border border-emerald-500/30 bg-emerald-950/20 px-2 py-0.5 text-xs font-semibold text-emerald-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                            <polyline points="22 4 12 14.01 9 11.01"/>
                                        </svg>
                                        Verified Attendee
                                    </span>
                                <?php endif; ?>
                                </div>
                            <div class="text-sm text-muted-foreground">
                                Reviewed <strong class="text-accent"><?php echo htmlspecialchars($review['venue_title']); ?></strong>
                            </div>
                        </div>
                        <div class="ml-4 text-right">
                            <div class="mb-2 flex items-center justify-end gap-1 text-yellow-400">
                                    <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                    </svg>
                                    <?php endfor; ?>
                                </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                <?php
                                $status = $review['moderation_status'];
                                if ($status == 'pending') echo 'border-orange-500/30 bg-orange-950/20 text-orange-500';
                                elseif ($status == 'approved') echo 'border-emerald-500/30 bg-emerald-950/20 text-emerald-500';
                                elseif ($status == 'rejected') echo 'border-red-500/30 bg-red-950/20 text-red-500';
                                else echo 'border-destructive/30 bg-destructive/20 text-destructive';
                                ?>">
                                <?php echo ucfirst($status); ?>
                                </span>
                            </div>
                        </div>
                        
                    <p class="mb-4 text-sm leading-relaxed text-muted-foreground">
                            <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                        </p>
                        
                        <?php if ($review['report_count'] > 0): ?>
                    <div class="mb-4 rounded-md border border-red-500/30 bg-red-950/20 p-3">
                        <div class="flex items-center gap-2 text-sm text-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                                <path d="M12 9v4"/>
                                <path d="M12 17h.01"/>
                            </svg>
                            <span>This review has been reported <?php echo $review['report_count']; ?> time(s)</span>
                        </div>
                        </div>
                        <?php endif; ?>
                        
                    <div class="flex items-center justify-between border-t border-border pt-4">
                        <small class="text-xs text-muted-foreground">
                                <?php echo date('M d, Y - g:i A', strtotime($review['created_at'])); ?>
                            </small>
                            <?php if ($review['moderation_status'] == 'pending' || $review['moderation_status'] == 'flagged'): ?>
                            <div class="flex gap-2">
                                <button onclick="updateReviewStatus(<?php echo $review['review_id']; ?>, 'approved')"
                                        class="inline-flex items-center justify-center rounded-md bg-emerald-500 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-emerald-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    Approve
                                </button>
                                <button onclick="updateReviewStatus(<?php echo $review['review_id']; ?>, 'rejected')"
                                        class="inline-flex items-center justify-center rounded-md bg-red-500 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                    Reject
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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

// Update review status
function updateReviewStatus(reviewId, status) {
    if (!confirm('Are you sure you want to ' + status + ' this review?')) {
        return;
    }
    
    // Disable buttons during request
    const buttons = document.querySelectorAll('button[onclick*="updateReviewStatus"]');
    buttons.forEach(btn => btn.disabled = true);
    
    $.ajax({
        url: '../actions/review_update_status_action.php',
        type: 'POST',
        data: {
            review_id: reviewId,
            status: status
        },
        dataType: 'json',
        success: function(result) {
            if (result && result.success) {
                showAlert(result.message || 'Review status updated successfully', 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showAlert(result && result.message ? result.message : 'Failed to update review status', 'danger');
                buttons.forEach(btn => btn.disabled = false);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            console.error('Response:', xhr.responseText);
            let errorMsg = 'An error occurred. Please try again.';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) {
                    errorMsg = response.message;
                }
            } catch (e) {
                // If response is not JSON, use default message
            }
            showAlert(errorMsg, 'danger');
            buttons.forEach(btn => btn.disabled = false);
        }
    });
}

// Filter reviews
function filterReviews() {
    const statusFilter = $('#statusFilter').val().toLowerCase();
    
    $('.review-card').each(function() {
        const card = $(this);
        const status = card.data('status');
        
        if (!statusFilter || status === statusFilter) {
            card.show();
        } else {
            card.hide();
        }
    });
}

// Real-time filter
$('#statusFilter').on('change', filterReviews);

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
