<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/activity_controller.php');
require_once(__DIR__ . '/../controllers/category_controller.php');
require_once(__DIR__ . '/../includes/site_nav.php');

// Get filter parameters
$filters = [];
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
if (isset($_GET['location']) && !empty($_GET['location'])) {
    $filters['location'] = trim($_GET['location']);
}
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $filters['category'] = intval($_GET['category']);
}
if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $filters['min_price'] = floatval($_GET['min_price']);
}
if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $filters['max_price'] = floatval($_GET['max_price']);
}
if (isset($_GET['min_capacity']) && !empty($_GET['min_capacity'])) {
    $filters['min_capacity'] = intval($_GET['min_capacity']);
}
if (isset($_GET['guests']) && !empty($_GET['guests'])) {
    $filters['min_capacity'] = intval($_GET['guests']);
}
if (isset($_GET['verified']) && $_GET['verified'] == '1') {
    $filters['is_verified'] = true;
}
$type = isset($_GET['type']) ? $_GET['type'] : 'venue'; // Default to venue
$filters['type'] = $type;

// Pass date to venue detail URL
$date_param = isset($_GET['date']) ? '&date=' . urlencode($_GET['date']) : '';
$guests_param = isset($_GET['guests']) ? '&guests=' . urlencode($_GET['guests']) : '';
$url_params = $date_param . $guests_param;

// Get recurrence filter if set
$recurrence_filter = isset($_GET['recurrence']) ? trim($_GET['recurrence']) : '';
if (!empty($recurrence_filter) && in_array($recurrence_filter, ['none', 'recurring'])) {
    $filters['recurrence_type'] = $recurrence_filter;
}

// Get results based on type
$results = [];
if ($type === 'activity') {
    // Get activities based on filters
    $results = search_activities_ctr($filters);
    if ($results === false) {
        $results = [];
    }
    
    // Filter by search query if provided
    if (!empty($search_query)) {
        $results = array_filter($results, function ($activity) use ($search_query) {
            $query = strtolower($search_query);
            return (
                strpos(strtolower($activity['title']), $query) !== false ||
                strpos(strtolower($activity['location_text']), $query) !== false ||
                strpos(strtolower($activity['activity_type']), $query) !== false ||
                strpos(strtolower($activity['description'] ?? ''), $query) !== false
            );
        });
    }
} else {
    // Get venues based on filters
    $results = search_venues_ctr($filters);
    if ($results === false) {
        $results = [];
    }
    
    // Filter by search query if provided
    if (!empty($search_query)) {
        $results = array_filter($results, function ($venue) use ($search_query) {
            $query = strtolower($search_query);
            return (
                strpos(strtolower($venue['title']), $query) !== false ||
                strpos(strtolower($venue['location_text']), $query) !== false ||
                strpos(strtolower($venue['cat_name']), $query) !== false ||
                strpos(strtolower($venue['description']), $query) !== false
            );
        });
    }
}

// For backward compatibility, keep $venues variable
$venues = $results;

// Get all categories for filter
$categories = get_all_categories_ctr();
if ($categories === false) {
    $categories = [];
}

// Prepare category data for pills
$category_pills = [];
if ($type === 'activity') {
    // Activity category filters (matching index page)
    $activity_filters = [
        ['id' => 'all', 'label' => 'All'],
        ['id' => 'workshop', 'label' => 'Workshops'],
        ['id' => 'food', 'label' => 'Food Tours'],
        ['id' => 'adventure', 'label' => 'Adventure'],
        ['id' => 'concert', 'label' => 'Concerts'],
        ['id' => 'sports', 'label' => 'Sports'],
        ['id' => 'festival', 'label' => 'Festivals'],
    ];
    foreach ($activity_filters as $filter) {
        $category_pills[] = [
            'id' => $filter['id'],
            'name' => $filter['label'],
            'icon' => 'grid'
        ];
    }
} else {
    // Venue categories
    foreach ($categories as $cat) {
        $category_pills[] = [
            'id' => strtolower(str_replace(' ', '-', $cat['cat_name'])),
            'name' => $cat['cat_name'],
            'icon' => 'grid'
        ];
    }
}
?>
<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search - Go Outside</title>
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

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
}

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
}
</style>
</head>

<body>
<?php render_site_nav(['base_path' => '../']); ?>

    <main class="px-4 py-4 md:py-6" style="padding-top: 80px;">
        <div class="container mx-auto max-w-7xl">
            <!-- Search Bar -->
            <div class="mb-4">
                <form method="GET" action=""
                    class="relative flex w-full items-center rounded-lg transition-all" style="background-color: var(--bg-card);">
                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 h-5 w-5"
                        style="color: var(--text-secondary);"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>"
                        placeholder="Search by name, location, or vibe..."
                        class="w-full bg-transparent py-3 pl-12 pr-24 text-sm focus:outline-none"
                        style="color: var(--text-primary);" placeholder-style="color: var(--text-secondary);">
                    <div class="absolute right-2 flex items-center gap-2">
                        <button type="button" id="openFilters"
                            class="flex h-8 w-8 items-center justify-center rounded-full transition-colors"
                            style="color: var(--text-secondary);" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="4" y1="21" x2="4" y2="14" />
                                <line x1="4" y1="10" x2="4" y2="3" />
                                <line x1="12" y1="21" x2="12" y2="12" />
                                <line x1="12" y1="8" x2="12" y2="3" />
                                <line x1="20" y1="21" x2="20" y2="16" />
                                <line x1="20" y1="12" x2="20" y2="3" />
                                <line x1="1" y1="14" x2="7" y2="14" />
                                <line x1="9" y1="8" x2="15" y2="8" />
                                <line x1="17" y1="16" x2="23" y2="16" />
                            </svg>
                        </button>
                        <button type="submit"
                            class="rounded-full px-4 py-1.5 text-sm font-semibold transition"
                            style="background-color: var(--accent); color: #ffffff;" onmouseover="this.style.backgroundColor='var(--accent-hover)'" onmouseout="this.style.backgroundColor='var(--accent)'">
                            Search
                        </button>
    </div>
                </form>
</div>

            <!-- Tab Switcher & Filter Button -->
            <div class="mb-4 flex items-center gap-4">
                <div class="flex flex-1 gap-1 rounded-lg p-1" style="background-color: var(--bg-card);">
                    <a href="?type=venue<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>"
                        class="flex-1 rounded-md py-2 text-center text-sm font-medium transition-all <?php echo $type === 'venue' ? 'shadow-sm' : ''; ?>"
                        style="<?php echo $type === 'venue' ? 'background-color: var(--bg-primary); color: var(--text-primary);' : 'color: var(--text-secondary);'; ?>"
                        onmouseover="<?php echo $type !== 'venue' ? "this.style.color='var(--text-primary)'" : ''; ?>"
                        onmouseout="<?php echo $type !== 'venue' ? "this.style.color='var(--text-secondary)'" : ''; ?>">
                        Venues
                    </a>
                    <a href="?type=activity<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>"
                        class="flex-1 rounded-md py-2 text-center text-sm font-medium transition-all <?php echo $type === 'activity' ? 'shadow-sm' : ''; ?>"
                        style="<?php echo $type === 'activity' ? 'background-color: var(--bg-primary); color: var(--text-primary);' : 'color: var(--text-secondary);'; ?>"
                        onmouseover="<?php echo $type !== 'activity' ? "this.style.color='var(--text-primary)'" : ''; ?>"
                        onmouseout="<?php echo $type !== 'activity' ? "this.style.color='var(--text-secondary)'" : ''; ?>">
                        Activities
                    </a>
                            </div>
                        </div>

            <!-- Recurrence Filter (Activities only) -->
            <?php if ($type === 'activity'): ?>
                <div class="mb-4 flex gap-2">
                    <a href="?type=activity<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>"
                        class="rounded-lg border px-3 py-1.5 text-sm font-medium transition <?php echo empty($recurrence_filter) ? 'shadow-sm' : ''; ?>"
                        style="<?php echo empty($recurrence_filter) ? 'background-color: var(--bg-primary); color: var(--text-primary); border-color: var(--accent);' : 'background-color: var(--bg-card); color: var(--text-secondary); border-color: var(--border-color);'; ?>"
                        onmouseover="<?php echo !empty($recurrence_filter) ? "this.style.color='var(--text-primary)'" : ''; ?>"
                        onmouseout="<?php echo !empty($recurrence_filter) ? "this.style.color='var(--text-secondary)'" : ''; ?>">
                        All Activities
                    </a>
                    <a href="?type=activity&recurrence=recurring<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>"
                        class="rounded-lg border px-3 py-1.5 text-sm font-medium transition <?php echo $recurrence_filter === 'recurring' ? 'shadow-sm' : ''; ?>"
                        style="<?php echo $recurrence_filter === 'recurring' ? 'background-color: var(--bg-primary); color: var(--text-primary); border-color: var(--accent);' : 'background-color: var(--bg-card); color: var(--text-secondary); border-color: var(--border-color);'; ?>"
                        onmouseover="<?php echo $recurrence_filter !== 'recurring' ? "this.style.color='var(--text-primary)'" : ''; ?>"
                        onmouseout="<?php echo $recurrence_filter !== 'recurring' ? "this.style.color='var(--text-secondary)'" : ''; ?>">
                        Recurring Only
                    </a>
                    <a href="?type=activity&recurrence=none<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>"
                        class="rounded-lg border px-3 py-1.5 text-sm font-medium transition <?php echo $recurrence_filter === 'none' ? 'shadow-sm' : ''; ?>"
                        style="<?php echo $recurrence_filter === 'none' ? 'background-color: var(--bg-primary); color: var(--text-primary); border-color: var(--accent);' : 'background-color: var(--bg-card); color: var(--text-secondary); border-color: var(--border-color);'; ?>"
                        onmouseover="<?php echo $recurrence_filter !== 'none' ? "this.style.color='var(--text-primary)'" : ''; ?>"
                        onmouseout="<?php echo $recurrence_filter !== 'none' ? "this.style.color='var(--text-secondary)'" : ''; ?>">
                        One-Time Only
                    </a>
                </div>
            <?php endif; ?>

            <!-- Category Pills -->
            <div class="relative mb-4">
                <div class="scrollbar-hide flex gap-2 overflow-x-auto px-1 py-2 md:px-8" id="categoryPills">
                    <button
                        class="category-pill flex shrink-0 items-center gap-2 rounded-full px-4 py-2 text-sm font-medium shadow-md transition-all"
                        style="background-color: var(--accent); color: #ffffff;"
                        data-category="all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="7" height="7" x="3" y="3" rx="1" />
                            <rect width="7" height="7" x="14" y="3" rx="1" />
                            <rect width="7" height="7" x="14" y="14" rx="1" />
                            <rect width="7" height="7" x="3" y="14" rx="1" />
                        </svg>
                        All
                    </button>
                    <?php foreach ($category_pills as $pill): ?>
                        <button
                            class="category-pill flex shrink-0 items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition-all"
                            style="background-color: var(--bg-card); color: var(--text-secondary);"
                            onmouseover="this.style.backgroundColor='var(--bg-card)'; this.style.opacity='0.8'"
                            onmouseout="this.style.backgroundColor='var(--bg-card)'; this.style.opacity='1'"
                            data-category="<?php echo $pill['id']; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="7" height="7" x="3" y="3" rx="1" />
                                <rect width="7" height="7" x="14" y="3" rx="1" />
                                <rect width="7" height="7" x="14" y="14" rx="1" />
                                <rect width="7" height="7" x="3" y="14" rx="1" />
                            </svg>
                            <?php echo htmlspecialchars($pill['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Results Count -->
            <div class="mb-4 flex items-center justify-between text-sm" style="color: var(--text-secondary);">
                <span><?php echo count($results); ?> results found</span>
                <?php if (!empty($search_query)): ?>
                    <span>Searching for "<?php echo htmlspecialchars($search_query); ?>"</span>
                <?php endif; ?>
            </div>

            <!-- Results Grid -->
                <?php if (empty($results)): ?>
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="mb-4 rounded-full p-4" style="background-color: var(--bg-card);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" style="color: var(--text-secondary);" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                    </div>
                    <h3 class="mb-1 font-semibold" style="color: var(--text-primary);">No <?php echo $type === 'activity' ? 'activities' : 'venues'; ?> found</h3>
                    <p class="mb-4 text-sm" style="color: var(--text-secondary);">Try adjusting your filters or search query</p>
                    <a href="search.php?type=<?php echo htmlspecialchars($type); ?>"
                        class="rounded-lg border bg-transparent px-4 py-2 text-sm font-medium transition"
                        style="border-color: var(--border-color); color: var(--text-primary);"
                        onmouseover="this.style.backgroundColor='var(--bg-card)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                        Clear all filters
                    </a>
                    </div>
                <?php else: ?>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <?php if ($type === 'activity'): ?>
                            <?php foreach ($results as $activity):
                                $photos = json_decode($activity['photos_json'] ?? '[]', true);
                                $first_photo = !empty($photos) ? $photos[0] : '../images/portfolio/01.jpg';
                                if (strpos($first_photo, 'uploads/activities/') === 0) {
                                    $first_photo = '../' . $first_photo;
                                } elseif (strpos($first_photo, '../') !== 0 && strpos($first_photo, 'uploads/') === 0) {
                                    $first_photo = '../' . $first_photo;
                                }
                                $date_display = !empty($activity['start_at']) ? date('j M', strtotime($activity['start_at'])) : '';
                                $activity_type_display = ucfirst(str_replace('_', ' ', $activity['activity_type'] ?? 'Activity'));
                                $price_display = isset($activity['is_free']) && $activity['is_free'] ? 'FREE' : 'GH₵' . number_format($activity['price_min'] ?? 0, 0);
                                $is_recurring = isset($activity['recurrence_type']) && $activity['recurrence_type'] === 'recurring';
                            ?>
                            <a href="activity_detail.php?id=<?php echo $activity['activity_id']; ?>"
                                data-activity-type="<?php echo strtolower($activity['activity_type'] ?? 'other'); ?>"
                                class="activity-card group relative flex flex-col overflow-hidden rounded-xl transition-all hover:shadow-lg"
                                style="background-color: var(--bg-card);">
                                <!-- Image -->
                                <div class="relative aspect-[4/3] overflow-hidden" style="background-color: var(--bg-primary);">
                                    <img src="<?php echo htmlspecialchars($first_photo); ?>"
                                        alt="<?php echo htmlspecialchars($activity['title']); ?>"
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    
                                    <!-- Save Button -->
                                    <?php
                                    $is_saved = false;
                                    if (is_logged_in()) {
                                        require_once(__DIR__ . '/../controllers/customer_controller.php');
                                        $is_saved = is_activity_saved_ctr(get_user_id(), $activity['activity_id']);
                                    }
                                    ?>
                                    <button
                                        class="save-activity-btn absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full backdrop-blur-sm transition-colors"
                                        style="background-color: var(--bg-primary); opacity: 0.8;"
                                        onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'"
                                        data-activity-id="<?php echo $activity['activity_id']; ?>"
                                        data-saved="<?php echo $is_saved ? 'true' : 'false'; ?>" onclick="event.preventDefault();">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            style="color: <?php echo $is_saved ? '#FF6B35' : 'var(--text-primary)'; ?>;"
                                            viewBox="0 0 24 24" fill="<?php echo $is_saved ? 'currentColor' : 'none'; ?>"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z" />
                                        </svg>
                                    </button>
                                    
                                    <?php if ($date_display): ?>
                                        <span class="absolute bottom-2 left-2 rounded border backdrop-blur-sm px-2 py-1 text-xs"
                                            style="border-color: var(--border-color); background-color: var(--bg-primary); opacity: 0.9; color: var(--text-primary);">
                                            <?php echo $date_display; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Content -->
                                <div class="flex flex-1 flex-col p-3">
                                    <!-- Activity Type & Location -->
                                    <div class="mb-1 flex items-center gap-2 text-xs" style="color: var(--text-secondary);">
                                        <span class="font-medium" style="color: var(--text-secondary); opacity: 0.8;"><?php echo htmlspecialchars($activity_type_display); ?></span>
                                        <?php if ($is_recurring): ?>
                                            <span class="flex items-center gap-1 rounded border border-blue-500/30 bg-transparent px-1.5 py-0.5 text-[10px] text-blue-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                                </svg>
                                                RECURRING
                                            </span>
                                        <?php endif; ?>
                                        <span>•</span>
                                        <span class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                                <circle cx="12" cy="10" r="3" />
                                            </svg>
                                            <?php echo htmlspecialchars($activity['location_text'] ?? ''); ?>
                                        </span>
                                    </div>

                                    <!-- Title -->
                                    <h3 class="line-clamp-1 text-base font-semibold transition-colors group-hover:text-[#FF6B35]"
                                        style="color: var(--text-primary);">
                                        <?php echo htmlspecialchars($activity['title']); ?>
                                    </h3>

                                    <!-- Price & Spots -->
                                    <div class="mt-auto flex items-end justify-between pt-2">
                                        <div>
                                            <span class="text-lg font-bold" style="color: var(--text-primary);"><?php echo $price_display; ?></span>
                                            <?php if (!isset($activity['is_free']) || !$activity['is_free']): ?>
                                                <span class="text-sm" style="color: var(--text-secondary);"> per person</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (isset($activity['capacity']) && $activity['capacity']): ?>
                                            <span class="rounded border px-2 py-1 text-xs"
                                                style="border-color: var(--border-color); background-color: var(--bg-secondary); color: var(--text-secondary);">
                                                <?php echo $activity['capacity']; ?> spots
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($results as $venue): 
                            $photos = json_decode($venue['photos_json'] ?? '[]', true);
                        $first_photo = !empty($photos) ? $photos[0] : '../images/portfolio/01.jpg';
                                if (strpos($first_photo, 'uploads/venues/') === 0) {
                                    $first_photo = '../' . $first_photo;
                                }
                        $avg_rating = isset($venue['avg_rating']) ? number_format($venue['avg_rating'], 1) : '0.0';
                        $review_count = isset($venue['review_count']) ? $venue['review_count'] : 0;
                        $price_min = number_format($venue['price_min'], 0);
                        $price_max = number_format($venue['price_max'], 0);
                        ?>
                        <a href="venue_detail.php?id=<?php echo $venue['venue_id']; ?><?php echo $url_params; ?>"
                            class="group relative flex flex-col overflow-hidden rounded-xl transition-all hover:shadow-lg"
                            style="background-color: var(--bg-card);">
                            <!-- Image -->
                            <div class="relative aspect-[4/3] overflow-hidden" style="background-color: var(--bg-primary);">
                                <img src="<?php echo htmlspecialchars($first_photo); ?>"
                                    alt="<?php echo htmlspecialchars($venue['title']); ?>"
                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                
                                <!-- Save Button -->
                                <?php
                                $is_saved = false;
                                if (is_logged_in()) {
                                    require_once(__DIR__ . '/../controllers/customer_controller.php');
                                    $is_saved = is_venue_saved_ctr(get_user_id(), $venue['venue_id']);
                                }
                                ?>
                                <button
                                    class="save-venue-btn absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full backdrop-blur-sm transition-colors"
                                    style="background-color: var(--bg-primary); opacity: 0.8;"
                                    onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'"
                                    data-venue-id="<?php echo $venue['venue_id']; ?>"
                                    data-saved="<?php echo $is_saved ? 'true' : 'false'; ?>" onclick="event.preventDefault();">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4"
                                        style="color: <?php echo $is_saved ? '#FF6B35' : 'var(--text-primary)'; ?>;"
                                        viewBox="0 0 24 24" fill="<?php echo $is_saved ? 'currentColor' : 'none'; ?>"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z" />
                                    </svg>
                                </button>

                                <!-- Featured Badge -->
                                <?php if (isset($venue['featured']) && $venue['featured']): ?>
                                    <span
                                        class="absolute left-2 top-2 flex items-center gap-1 rounded border border-transparent px-2 py-1 text-xs font-medium"
                                        style="background-color: var(--accent); color: #ffffff;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z" />
                                        </svg>
                                        Featured
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Content -->
                            <div class="flex flex-1 flex-col p-3">
                                <!-- Category & Location -->
                                <div class="mb-1 flex items-center gap-2 text-xs" style="color: var(--text-secondary);">
                                    <span
                                        class="font-medium" style="color: var(--text-secondary); opacity: 0.8;"><?php echo htmlspecialchars($venue['cat_name']); ?></span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                            <circle cx="12" cy="10" r="3" />
                                        </svg>
                                        <?php echo htmlspecialchars($venue['location_text']); ?>
                                    </span>
                                        </div>

                                <!-- Title -->
                                <h3
                                    class="line-clamp-1 text-base font-semibold transition-colors group-hover:text-[#FF6B35]"
                                    style="color: var(--text-primary);">
                                    <?php echo htmlspecialchars($venue['title']); ?>
                                </h3>

                                <!-- Rating & Reviews -->
                                <div class="mt-1 flex items-center gap-1.5 text-sm">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-3.5 w-3.5 fill-amber-400 text-amber-400" viewBox="0 0 24 24"
                                            fill="currentColor">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                        <span class="font-medium"><?php echo $avg_rating; ?></span>
                                    </div>
                                    <span style="color: var(--text-secondary);">(<?php echo $review_count; ?> reviews)</span>
                                    <?php if (isset($venue['verified']) && $venue['verified']): ?>
                                        <span
                                            class="ml-auto flex items-center gap-1 rounded border border-green-500/30 bg-transparent px-1.5 py-0 text-xs text-green-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                                <polyline points="22 4 12 14.01 9 11.01" />
                                            </svg>
                                            Verified
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Price -->
                                <div class="mt-auto flex items-end justify-between pt-2">
                                    <div>
                                        <span class="text-lg font-bold" style="color: var(--text-primary);">GH₵<?php echo $price_min; ?></span>
                                        <?php if ($price_min != $price_max): ?>
                                            <span style="color: var(--text-primary);"> - GH₵<?php echo $price_max; ?></span>
                                        <?php endif; ?>
                                        <span class="text-sm" style="color: var(--text-secondary);"> per event</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
    </main>

    <!-- Filters Modal -->
    <div id="filtersModal" class="fixed inset-0 z-50 hidden items-center justify-center backdrop-blur-sm"
        style="background-color: rgba(0, 0, 0, 0.7);">
        <div class="relative w-full max-w-md rounded-lg border p-6 shadow-xl"
            style="border-color: var(--border-color); background-color: var(--bg-card);">
            <button id="closeFilters" class="absolute right-4 top-4 transition-colors"
                style="color: var(--text-secondary);" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
            <h3 class="mb-6 text-lg font-semibold" style="color: var(--text-primary);">Filters</h3>
            <form method="GET" action="" class="space-y-6">
                <?php if (!empty($search_query)): ?>
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                <?php endif; ?>

                <!-- Price Range -->
                <div>
                    <label class="mb-4 block text-sm font-medium" style="color: var(--text-primary);">
                        Price Range: GH₵<span id="minPriceDisplay"><?php echo $filters['min_price'] ?? 0; ?></span> -
                        GH₵<span id="maxPriceDisplay"><?php echo $filters['max_price'] ?? 5000; ?></span>
                    </label>
                    <div class="flex gap-4">
                        <input type="number" name="min_price" id="minPrice"
                            value="<?php echo $filters['min_price'] ?? 0; ?>" min="0" max="5000"
                            class="w-full rounded-lg border px-3 py-2 focus:outline-none"
                            style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-primary);"
                            onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border-color)'"
                        <input type="number" name="max_price" id="maxPrice"
                            value="<?php echo $filters['max_price'] ?? 5000; ?>" min="0" max="5000"
                            class="w-full rounded-lg border px-3 py-2 focus:outline-none"
                            style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-primary);"
                            onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border-color)'"
                    </div>
                </div>

                <!-- Verified Only -->
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="verified" name="verified" value="1" <?php echo (isset($filters['is_verified']) && $filters['is_verified']) ? 'checked' : ''; ?>
                        class="h-4 w-4 rounded focus:ring-[#FF6B35]"
                        style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--accent);">
                    <label for="verified" class="text-sm font-normal" style="color: var(--text-primary);">Show verified only</label>
                </div>

                <!-- Location -->
                <div>
                    <label class="mb-2 block text-sm font-medium" style="color: var(--text-primary);">Location</label>
                    <input type="text" name="location"
                        value="<?php echo htmlspecialchars($filters['location'] ?? ''); ?>" placeholder="e.g., Accra"
                        class="w-full rounded-lg border px-3 py-2 focus:outline-none"
                        style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-primary);"
                        placeholder-style="color: var(--text-secondary);"
                        onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border-color)'"
                </div>

                <!-- Category -->
                <div>
                    <label class="mb-2 block text-sm font-medium" style="color: var(--text-primary);">Category</label>
                    <select name="category"
                        class="w-full rounded-lg border px-3 py-2 focus:outline-none"
                        style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-primary);"
                        onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border-color)'">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['cat_id']; ?>" <?php echo (isset($filters['category']) && $filters['category'] == $cat['cat_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['cat_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Min Capacity -->
                <div>
                    <label class="mb-2 block text-sm font-medium" style="color: var(--text-primary);">Min Capacity</label>
                    <input type="number" name="min_capacity" value="<?php echo $filters['min_capacity'] ?? ''; ?>"
                        placeholder="e.g., 50"
                        class="w-full rounded-lg border px-3 py-2 focus:outline-none"
                        style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-primary);"
                        placeholder-style="color: var(--text-secondary);"
                        onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border-color)'"
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                        class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition"
                        style="background-color: var(--accent); color: #ffffff;"
                        onmouseover="this.style.backgroundColor='var(--accent-hover)'" onmouseout="this.style.backgroundColor='var(--accent)'">
                        Apply Filters
                    </button>
                    <a href="search.php<?php echo !empty($search_query) ? '?q=' . urlencode($search_query) : ''; ?>"
                        class="flex-1 rounded-lg border bg-transparent px-4 py-2 text-center text-sm font-medium transition"
                        style="border-color: var(--border-color); color: var(--text-primary);"
                        onmouseover="this.style.backgroundColor='var(--bg-card)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Filters Modal
        const openFilters = document.getElementById('openFilters');
        const closeFilters = document.getElementById('closeFilters');
        const filtersModal = document.getElementById('filtersModal');

        openFilters?.addEventListener('click', () => {
            filtersModal?.classList.remove('hidden');
            filtersModal?.classList.add('flex');
        });

        closeFilters?.addEventListener('click', () => {
            filtersModal?.classList.add('hidden');
            filtersModal?.classList.remove('flex');
        });

        filtersModal?.addEventListener('click', (e) => {
            if (e.target === filtersModal) {
                filtersModal.classList.add('hidden');
                filtersModal.classList.remove('flex');
            }
        });

        // Price display updates
        const minPrice = document.getElementById('minPrice');
        const maxPrice = document.getElementById('maxPrice');
        const minPriceDisplay = document.getElementById('minPriceDisplay');
        const maxPriceDisplay = document.getElementById('maxPriceDisplay');

        minPrice?.addEventListener('input', () => {
            if (minPriceDisplay) minPriceDisplay.textContent = minPrice.value;
        });

        maxPrice?.addEventListener('input', () => {
            if (maxPriceDisplay) maxPriceDisplay.textContent = maxPrice.value;
        });

        // Category pills
        const categoryPills = document.querySelectorAll('.category-pill');
        const currentType = '<?php echo $type; ?>';
        
        categoryPills.forEach(pill => {
            pill.addEventListener('click', () => {
                categoryPills.forEach(p => {
                    p.style.backgroundColor = 'var(--bg-card)';
                    p.style.color = 'var(--text-secondary)';
                    p.classList.remove('shadow-md');
                });
                pill.style.backgroundColor = 'var(--accent)';
                pill.style.color = '#ffffff';
                pill.classList.add('shadow-md');

                const category = pill.dataset.category;
                if (category !== 'all') {
                    if (currentType === 'activity') {
                        // Filter activities by category (client-side for now)
                        const activityCards = document.querySelectorAll('a[href*="type=activity"]');
                        activityCards.forEach(card => {
                            const typeText = card.querySelector('span.font-medium')?.textContent?.toLowerCase() || '';
                            const categorySlug = category.replace('-', ' ');
                            // Check if activity type matches category
                            if (typeText.includes(categorySlug) || 
                                (category === 'workshop' && (typeText.includes('workshop') || typeText.includes('class'))) ||
                                (category === 'food' && (typeText.includes('food') || typeText.includes('popup'))) ||
                                (category === 'adventure' && (typeText.includes('adventure') || typeText.includes('tour') || typeText.includes('meetup'))) ||
                                (category === 'sports' && (typeText.includes('sports') || typeText.includes('game'))) ||
                                (category === 'concert' && (typeText.includes('concert') || typeText.includes('nightlife')))) {
                                card.style.display = '';
                            } else {
                                card.style.display = 'none';
                            }
                        });
                    } else {
                        // Filter venues by category (client-side for now)
                        const venueCards = document.querySelectorAll('a[href*="venue_detail"]');
                        venueCards.forEach(card => {
                            const categoryText = card.querySelector('span.font-medium')?.textContent?.toLowerCase() || '';
                            if (categoryText.includes(category.replace('-', ' '))) {
                                card.style.display = '';
                            } else {
                                card.style.display = 'none';
                            }
                        });
                    }
                } else {
                    // Show all
                    if (currentType === 'activity') {
                        document.querySelectorAll('a[href*="type=activity"]').forEach(card => {
                            card.style.display = '';
                        });
                    } else {
                        document.querySelectorAll('a[href*="venue_detail"]').forEach(card => {
                            card.style.display = '';
                        });
                    }
                }
            });
        });

        // Save Venue/Activity Functionality
        document.addEventListener('click', function(e) {
            const saveVenueBtn = e.target.closest('.save-venue-btn');
            const saveActivityBtn = e.target.closest('.save-activity-btn');
            const saveBtn = saveVenueBtn || saveActivityBtn;
            const isActivity = !!saveActivityBtn;
            
            if (saveBtn) {
                e.preventDefault();
                e.stopPropagation(); // Prevent card click

                const itemId = isActivity ? saveBtn.dataset.activityId : saveBtn.dataset.venueId;
                const isSaved = saveBtn.dataset.saved === 'true';
                const action = isSaved ? 'unsave' : 'save';
                const icon = saveBtn.querySelector('svg');

                // Optimistic UI update
                if (action === 'save') {
                    saveBtn.dataset.saved = 'true';
                    icon.setAttribute('fill', 'currentColor');
                    icon.style.color = '#FF6B35';
                } else {
                    saveBtn.dataset.saved = 'false';
                    icon.setAttribute('fill', 'none');
                    icon.style.color = 'var(--text-primary)';
                }

                // Send request
                const requestBody = isActivity ? {
                    activity_id: itemId,
                    item_type: 'activity',
                    action: action
                } : {
                    venue_id: itemId,
                    item_type: 'venue',
                    action: action
                };
                
                fetch('../actions/toggle_saved_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(requestBody)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'error') {
                        // Revert UI on error
                        if (action === 'save') {
                            saveBtn.dataset.saved = 'false';
                            icon.setAttribute('fill', 'none');
                            icon.classList.remove('text-[#FF6B35]');
                            icon.classList.add('text-white');
                        } else {
                            saveBtn.dataset.saved = 'true';
                            icon.setAttribute('fill', 'currentColor');
                            icon.classList.add('text-[#FF6B35]');
                            icon.classList.remove('text-white');
                        }
                        
                        if (data.message.includes('login')) {
                            window.location.href = 'login.php';
                        } else {
                            alert(data.message);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Revert UI on error
                    if (action === 'save') {
                        saveBtn.dataset.saved = 'false';
                        icon.setAttribute('fill', 'none');
                        icon.classList.remove('text-[#FF6B35]');
                        icon.classList.add('text-white');
                    } else {
                        saveBtn.dataset.saved = 'true';
                        icon.setAttribute('fill', 'currentColor');
                        icon.classList.add('text-[#FF6B35]');
                        icon.classList.remove('text-white');
                    }
                });
            }
        });
    </script>
</body>

</html>