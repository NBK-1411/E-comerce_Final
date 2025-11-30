<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../settings/db_cred.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/review_controller.php');
require_once(__DIR__ . '/../includes/site_nav.php');

$venue_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$venue_id) {
    header('Location: search.php');
    exit();
}

$venue = get_venue_by_id_ctr($venue_id);
if (!$venue) {
    header('Location: search.php');
    exit();
}

// Get reviews and rating stats
$reviews = get_reviews_by_venue_ctr($venue_id, true);
$rating_stats = get_venue_rating_stats_ctr($venue_id);

if ($reviews === false)
    $reviews = [];
if ($rating_stats === false) {
    $rating_stats = ['total_reviews' => 0, 'avg_rating' => 0];
}

// Parse photos
$photos = json_decode($venue['photos_json'] ?? '[]', true);
if (empty($photos)) {
    $photos = ['../images/portfolio/01.jpg'];
} else {
    $photos = array_map(function ($photo) {
        if (strpos($photo, 'uploads/venues/') === 0) {
            return '../' . $photo;
        }
        return $photo;
    }, $photos);
}

// Parse amenities
$amenities = json_decode($venue['amenities_json'] ?? '[]', true);
if (!is_array($amenities))
    $amenities = [];

// Get vibes/tags from database
$vibes = [];
try {
    require_once(__DIR__ . '/../settings/db_class.php');
    $db = new db_connection();
    $vibes_sql = "SELECT vt.tag_name FROM venue_tag_relation vtr 
                  JOIN venue_tags vt ON vtr.tag_id = vt.tag_id 
                  WHERE vtr.venue_id = ?";
    $vibes_result = $db->read($vibes_sql, [$venue_id], 'i');
    if ($vibes_result && is_array($vibes_result)) {
        $vibes = array_column($vibes_result, 'tag_name');
    }
} catch (Exception $e) {
    // If query fails, use fallback
    $vibes = [];
}

// Fallback to category-based vibes if no tags found
if (empty($vibes)) {
    $vibe_map = [
        'Event Spaces' => ['Luxury', 'Corporate', 'Elegant'],
        'Clubs & Lounges' => ['Nightlife', 'DJ', 'VIP'],
        'Restaurants' => ['Foodie', 'Social', 'Casual'],
        'Studios' => ['Creative', 'Culture', 'Curated'],
        'Short Stays' => ['Retreat', 'Nature', 'Cozy'],
        'Beach' => ['Sunset', 'Party', 'Coastline'],
    ];
    $vibes = $vibe_map[$venue['cat_name']] ?? ['Verified', 'Community'];
}

$rules = json_decode($venue['rules_json'] ?? '{}', true);

// Parse GPS coordinates
$lat = null;
$lng = null;
if (!empty($venue['gps_code']) && strpos($venue['gps_code'], ',') !== false) {
    list($lat, $lng) = explode(',', $venue['gps_code']);
    $lat = trim($lat);
    $lng = trim($lng);
    if (is_numeric($lat) && is_numeric($lng)) {
        $lat = floatval($lat);
        $lng = floatval($lng);
    } else {
        $lat = null;
        $lng = null;
    }
}

// Cancellation policy labels
$cancellation_labels = [
    'flex' => ['label' => 'Flexible', 'desc' => 'Full refund up to 24 hours before'],
    'standard' => ['label' => 'Standard', 'desc' => '50% refund up to 48 hours before'],
    'strict' => ['label' => 'Strict', 'desc' => 'No refund after booking'],
];
$cancellation_policy = $venue['cancellation_policy'] ?? 'standard';
$cancellation_info = $cancellation_labels[$cancellation_policy] ?? $cancellation_labels['standard'];

// Host info
$host_name = $venue['owner_name'] ?? 'Host';
$host_contact = $venue['owner_contact'] ?? '';
?>
<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($venue['title']); ?> - Go Outside</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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

.venue-map-container {
    width: 100%;
    height: 400px;
            border-radius: 8px;
    overflow: hidden;
            border: 1px solid rgba(39, 39, 42, 0.7);
}

.venue-map-container .leaflet-container {
            background: var(--bg-card);
    height: 100% !important;
    width: 100% !important;
}
</style>
</head>

<body>
<?php render_site_nav(['base_path' => '../']); ?>

    <main class="pb-24 md:pb-8" style="padding-top: 80px;">
        <!-- Image Gallery -->
        <div class="relative">
            <!-- Main Image -->
            <div class="relative aspect-[4/3] w-full overflow-hidden md:aspect-[21/9]" style="background-color: var(--bg-primary);">
                <img src="<?php echo htmlspecialchars($photos[0] ?? '../images/portfolio/01.jpg'); ?>"
                    alt="<?php echo htmlspecialchars($venue['title']); ?>" id="mainImage"
                    class="h-full w-full object-cover transition-opacity duration-300">

                <!-- Image Navigation Dots -->
                <?php if (count($photos) > 1): ?>
                    <div class="absolute inset-x-0 bottom-4 flex justify-center gap-2">
                        <?php foreach ($photos as $idx => $photo): ?>
                            <button onclick="setCurrentImage(<?php echo $idx; ?>)"
                                class="image-dot h-2 rounded-full transition-all <?php echo $idx === 0 ? 'w-6 bg-white' : 'w-2 bg-white/50'; ?>"
                                data-index="<?php echo $idx; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Top Actions -->
                <div class="absolute left-4 right-4 top-4 flex justify-between">
                    <a href="search.php"
                        class="flex h-10 w-10 items-center justify-center rounded-full backdrop-blur-sm transition-colors"
                        style="background-color: var(--bg-primary); opacity: 0.8;"
                        onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" style="color: var(--text-primary);" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="m12 19-7-7 7-7" />
                            <path d="M19 12H5" />
                        </svg>
                    </a>
                    <div class="flex gap-2">
                        <?php
                        $is_saved = false;
                        if (is_logged_in()) {
                            require_once(__DIR__ . '/../controllers/customer_controller.php');
                            $is_saved = is_venue_saved_ctr(get_user_id(), $venue_id);
                        }
                        ?>
                        <button onclick="toggleSave()"
                            class="flex h-10 w-10 items-center justify-center rounded-full backdrop-blur-sm transition-colors"
                            style="background-color: var(--bg-primary); opacity: 0.8;"
                            onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'"
                            id="saveBtn" data-venue-id="<?php echo $venue_id; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                style="color: <?php echo $is_saved ? '#FF6B35' : 'var(--text-primary)'; ?>;"
                                viewBox="0 0 24 24" fill="<?php echo $is_saved ? 'currentColor' : 'none'; ?>"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                id="heartIcon">
                                <path
                                    d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z" />
                            </svg>
                        </button>
                        <button onclick="shareVenue()"
                            class="flex h-10 w-10 items-center justify-center rounded-full backdrop-blur-sm transition-colors"
                            style="background-color: var(--bg-primary); opacity: 0.8;"
                            onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" style="color: var(--text-primary);" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                                <polyline points="16 6 12 2 8 6" />
                                <line x1="12" y1="2" x2="12" y2="15" />
                            </svg>
                        </button>
                    </div>
                </div>
        </div>
        
            <!-- Thumbnail Strip - Desktop -->
            <?php if (count($photos) > 1): ?>
                <div class="hidden border-b px-4 py-3 md:block" style="border-color: var(--border-color); background-color: var(--bg-card);">
                    <div class="container mx-auto flex gap-2 overflow-x-auto">
                        <?php foreach ($photos as $idx => $photo): ?>
                            <button onclick="setCurrentImage(<?php echo $idx; ?>)"
                                class="thumbnail-btn relative h-16 w-24 shrink-0 overflow-hidden rounded-lg transition-all <?php echo $idx === 0 ? 'ring-2 ring-[#FF6B35]' : ''; ?>"
                                data-index="<?php echo $idx; ?>">
                                <img src="<?php echo htmlspecialchars($photo); ?>" alt="Photo <?php echo $idx + 1; ?>"
                                    class="h-full w-full object-cover">
                            </button>
                        <?php endforeach; ?>
                    </div>
        </div>
        <?php endif; ?>
</div>

        <!-- Content -->
        <div class="container mx-auto px-4">
            <div class="grid gap-8 py-6 md:grid-cols-3 md:py-8">
                <!-- Main Content -->
                <div class="md:col-span-2">
                    <!-- Header -->
                    <div class="mb-6">
                        <div class="mb-2 flex items-center gap-2">
                            <span
                                class="rounded border px-3 py-1 text-sm font-medium"
                                style="border-color: var(--border-color); background-color: var(--bg-card); color: var(--text-primary);">
                                <?php echo htmlspecialchars($venue['cat_name']); ?>
                            </span>
                            <?php if (isset($venue['verified']) && $venue['verified']): ?>
                                <span
                                    class="flex items-center gap-1 rounded border border-green-500/30 bg-transparent px-2 py-1 text-xs text-green-500">
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
                        <h1 class="mb-2 text-2xl font-bold md:text-3xl" style="color: var(--text-primary);">
                            <?php echo htmlspecialchars($venue['title']); ?>
                        </h1>
                        <div class="flex flex-wrap items-center gap-3 text-sm" style="color: var(--text-secondary);">
                            <div class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <?php echo htmlspecialchars($venue['location_text']); ?>
                                <?php if (!empty($venue['gps_code'])): ?>
                                    <span class="text-xs">(<?php echo htmlspecialchars($venue['gps_code']); ?>)</span>
                                <?php endif; ?>
                    </div>
                            <?php if ($rating_stats['total_reviews'] > 0): ?>
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 fill-amber-400 text-amber-400"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                    </svg>
                                    <span
                                        class="font-medium" style="color: var(--text-primary);"><?php echo number_format($rating_stats['avg_rating'], 1); ?></span>
                                    <span>(<?php echo $rating_stats['total_reviews']; ?> reviews)</span>
            </div>
                            <?php endif; ?>
                            <?php if ($venue['capacity']): ?>
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                    Up to <?php echo $venue['capacity']; ?> guests
        </div>
                            <?php endif; ?>
    </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h2 class="mb-2 text-lg font-semibold" style="color: var(--text-primary);">About</h2>
                        <p class="leading-relaxed" style="color: var(--text-secondary);">
                        <?php echo nl2br(htmlspecialchars($venue['description'])); ?>
                    </p>
                </div>

                    <!-- Vibes -->
                    <?php if (!empty($vibes)): ?>
                        <div class="mb-6">
                            <h2 class="mb-3 text-lg font-semibold" style="color: var(--text-primary);">Vibes</h2>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($vibes as $vibe): ?>
                                    <span
                                        class="rounded border px-3 py-1 text-sm"
                                        style="border-color: var(--border-color); background-color: var(--bg-card); color: var(--text-primary);">
                                        <?php echo htmlspecialchars($vibe); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                    <!-- Amenities -->
                    <?php if (!empty($amenities) || $venue['parking_transport'] || $venue['accessibility_info']): ?>
                        <div class="mb-6">
                            <h2 class="mb-3 text-lg font-semibold" style="color: var(--text-primary);">Amenities</h2>
                            <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                                <?php foreach ($amenities as $amenity): ?>
                                    <div class="flex items-center gap-2 text-sm" style="color: var(--text-secondary);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        <?php echo htmlspecialchars($amenity); ?>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($venue['parking_transport'] && strpos(strtolower($venue['parking_transport']), 'parking') !== false): ?>
                                    <div class="flex items-center gap-2 text-sm" style="color: var(--text-secondary);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1" />
                                            <polygon points="12 15 17 21 7 21 12 15" />
                                        </svg>
                                        Parking Available
                        </div>
                        <?php endif; ?>
                                <?php if ($venue['accessibility_info']): ?>
                                    <div class="flex items-center gap-2 text-sm" style="color: var(--text-secondary);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M12 16v-4" />
                                            <path d="M12 8h.01" />
                                        </svg>
                                        Accessible
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                    <!-- House Rules -->
                    <?php if (!empty($rules)): ?>
                        <div class="mb-6">
                            <h2 class="mb-3 text-lg font-semibold" style="color: var(--text-primary);">House Rules</h2>
                            <div class="space-y-2 rounded-lg p-4" style="background-color: var(--bg-card);">
                                <?php if (isset($rules['age_limit'])): ?>
                                    <div class="flex items-center gap-2 text-sm" style="color: var(--text-secondary);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                            <circle cx="9" cy="7" r="4" />
                                        </svg>
                                        Minimum age: <?php echo htmlspecialchars($rules['age_limit']); ?>+
                    </div>
                    <?php endif; ?>
                                <?php if (isset($rules['dress_code'])): ?>
                                    <div class="flex items-center gap-2 text-sm" style="color: var(--text-secondary);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M12 16v-4" />
                                            <path d="M12 8h.01" />
                                        </svg>
                                        Dress code: <?php echo htmlspecialchars($rules['dress_code']); ?>
                    </div>
                    <?php endif; ?>
                                <?php if (isset($rules['noise_curfew'])): ?>
                                    <div class="flex items-center gap-2 text-sm" style="color: var(--text-secondary);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M9 18V5a4.5 4.5 0 0 1 9 0v13" />
                                            <path d="M6 9h12" />
                                            <path d="M6 15h12" />
                                        </svg>
                                        <?php echo htmlspecialchars($rules['noise_curfew']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                </div>
                <?php endif; ?>

                <!-- Location & Map -->
                    <?php if ($lat && $lng): ?>
                        <div class="mb-6">
                            <h2 class="mb-3 text-lg font-semibold" style="color: var(--text-primary);">Location</h2>
                            <div class="mb-4 text-sm" style="color: var(--text-secondary);">
                                <div class="mb-2 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    <strong
                                        style="color: var(--text-primary);"><?php echo htmlspecialchars($venue['location_text']); ?></strong>
                        </div>
                        <?php if (!empty($venue['gps_code'])): ?>
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M12 2v20M2 12h20" />
                                        </svg>
                            GPS: <?php echo htmlspecialchars($venue['gps_code']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div id="venueMap" class="venue-map-container"></div>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($lat . ',' . $lng); ?>" 
                       target="_blank" 
                                class="mt-4 inline-flex items-center gap-2 rounded-lg border bg-transparent px-4 py-2 text-sm font-medium transition"
                                style="border-color: var(--border-color); color: var(--text-primary);"
                                onmouseover="this.style.backgroundColor='var(--bg-card)'"
                                onmouseout="this.style.backgroundColor='transparent'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2v20M2 12h20" />
                                </svg>
                                Get Directions
                            </a>
                    </div>
                    <?php endif; ?>

                    <!-- Reviews -->
                    <div class="mb-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold" style="color: var(--text-primary);">Reviews (<?php echo count($reviews); ?>)</h2>
                        <?php if (is_logged_in()): ?>
                                <button onclick="openReviewModal()"
                                    class="rounded-lg border px-3 py-1.5 text-sm font-medium transition"
                                    style="border-color: var(--border-color); background-color: transparent; color: var(--text-primary);"
                                    onmouseover="this.style.backgroundColor='var(--bg-card)'"
                                    onmouseout="this.style.backgroundColor='transparent'">
                                    Write Review
                            </button>
                        <?php endif; ?>
                        </div>
                    <?php if (empty($reviews)): ?>
                            <div class="rounded-lg border p-8 text-center"
                                style="border-color: var(--border-color); background-color: var(--bg-card);">
                                <p style="color: var(--text-secondary);">No reviews yet. Be the first to review this venue!</p>
                            </div>
                    <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach (array_slice($reviews, 0, 3) as $review): ?>
                                    <div class="rounded-lg border p-4"
                                        style="border-color: var(--border-color); background-color: var(--bg-card);">
                                        <div class="mb-3 flex items-start justify-between">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="flex h-10 w-10 items-center justify-center rounded-full"
                                                    style="background-color: var(--bg-primary); color: var(--text-secondary);">
                                                    <span
                                                        class="text-lg font-semibold"><?php echo strtoupper(substr($review['customer_name'], 0, 1)); ?></span>
                                </div>
                                        <div>
                                                    <div class="flex items-center gap-1">
                                                        <span
                                                            class="font-medium" style="color: var(--text-primary);"><?php echo htmlspecialchars($review['customer_name']); ?></span>
                                            <?php if ($review['is_verified']): ?>
                                                            <span
                                                                class="flex items-center gap-1 rounded border border-green-500/30 bg-transparent px-1.5 py-0 text-xs text-green-500">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                    <polyline points="20 6 9 17 4 12" />
                                                                </svg>
                                                                Verified Attendee
                                                </span>
                                            <?php endif; ?>
                                            </div>
                                                    <p class="text-xs" style="color: var(--text-secondary);">
                                                        <?php echo date('d M Y', strtotime($review['created_at'])); ?>
                                                    </p>
                                        </div>
                                        </div>
                                    </div>
                                        <div class="mb-2 flex items-center gap-1">
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4 <?php echo $i < $review['rating'] ? 'fill-amber-400 text-amber-400' : ''; ?>"
                                                    style="<?php echo $i < $review['rating'] ? '' : 'color: var(--text-secondary); opacity: 0.3;'; ?>"
                                                    viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="text-sm leading-relaxed" style="color: var(--text-secondary);">
                                        <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                    </p>
                        </div>
                        <?php endforeach; ?>
                            </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Booking Sidebar -->
                <div class="md:col-span-1">
                    <div class="sticky top-20 space-y-4">
                        <!-- Price Card -->
                        <div class="rounded-xl border p-6 shadow-sm"
                            style="border-color: var(--border-color); background-color: var(--bg-card);">
                            <div class="mb-4">
                                <span
                                    class="text-2xl font-bold" style="color: var(--text-primary);">GH₵<?php echo number_format($venue['price_min'], 0); ?></span>
                                <span style="color: var(--text-secondary);"> per hour</span>
                    </div>

                            <form action="booking.php" method="GET" id="bookingWidgetForm">
                                <input type="hidden" name="id" value="<?php echo $venue_id; ?>">
                        
                                <div class="space-y-3 mb-4">
                                    <div>
                                        <label class="block text-xs font-medium mb-1" style="color: var(--text-secondary);">Date</label>
                                        <input type="date" name="date" id="widgetDate"
                                            class="w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none"
                                            style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-primary);"
                                            min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-medium mb-1" style="color: var(--text-secondary);">Start</label>
                                            <input type="time" name="start" id="widgetStart"
                                                class="w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none"
                                                style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-primary);"
                                                required>
                        </div>
                                        <div>
                                            <label class="block text-xs font-medium mb-1" style="color: var(--text-secondary);">End</label>
                                            <input type="time" name="end" id="widgetEnd"
                                                class="w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none"
                                                style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-primary);"
                                                required>
                        </div>
                        </div>
                                    <div>
                                        <label class="block text-xs font-medium mb-1" style="color: var(--text-secondary);">Guests</label>
                                        <select name="guests" id="widgetGuests"
                                            class="w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none"
                                            style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-primary);">
                                            <?php for ($i = 1; $i <= min(20, $venue['capacity']); $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?>
                                                    guest<?php echo $i > 1 ? 's' : ''; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                        </div>
                        
                                <?php if (($venue['booking_type'] ?? 'rent') === 'rent'): ?>
                                    <div id="priceCalculation"
                                        class="hidden mb-4 space-y-2 border-t pt-3"
                                        style="border-color: var(--border-color);">
                                        <div class="flex justify-between text-sm" style="color: var(--text-secondary);">
                                            <span>GH₵<?php echo number_format($venue['price_min'], 0); ?> x <span
                                                    id="calcHours">0</span> hrs</span>
                                            <span>GH₵<span id="calcBase">0</span></span>
                            </div>
                                        <div class="flex justify-between text-sm" style="color: var(--text-secondary);">
                                            <span>Service fee</span>
                                            <span>GH₵<span id="calcFee">0</span></span>
                            </div>
                                        <div
                                            class="flex justify-between font-bold pt-2 border-t"
                                            style="border-color: var(--border-color); color: var(--text-primary);">
                                            <span>Total</span>
                                            <span>GH₵<span id="calcTotal">0</span></span>
                        </div>
                                    </div>
                                <?php endif; ?>
                        
                        <?php if (is_logged_in()): ?>
                                    <button type="submit"
                                        class="w-full rounded-lg px-4 py-3 text-sm font-semibold transition"
                                        style="background-color: var(--accent); color: white;"
                                        onmouseover="this.style.backgroundColor='var(--accent-hover)'"
                                        onmouseout="this.style.backgroundColor='var(--accent)'">
                                        <?php echo ($venue['booking_type'] ?? 'rent') === 'reservation' ? 'Make Reservation' : 'Request to Book'; ?>
                            </button>
                        <?php else: ?>
                                    <a href="login.php"
                                        class="flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-semibold transition"
                                        style="background-color: var(--accent); color: white;"
                                        onmouseover="this.style.backgroundColor='var(--accent-hover)'"
                                        onmouseout="this.style.backgroundColor='var(--accent)'">
                                        Login to
                                        <?php echo ($venue['booking_type'] ?? 'rent') === 'reservation' ? 'Reserve' : 'Book'; ?>
                            </a>
                        <?php endif; ?>
                    </form>
                    
                            <?php if (($venue['booking_type'] ?? 'rent') === 'rent'): ?>
                                <p class="mt-3 text-center text-xs" style="color: var(--text-secondary);">
                                    You won't be charged yet
                                </p>
                            <?php else: ?>
                                <p class="mt-3 text-center text-xs" style="color: var(--text-secondary);">
                                    No payment required
                                </p>
                            <?php endif; ?>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const form = document.getElementById('bookingWidgetForm');
                                const dateInput = document.getElementById('widgetDate');
                                const startInput = document.getElementById('widgetStart');
                                const endInput = document.getElementById('widgetEnd');
                                const priceCalc = document.getElementById('priceCalculation');

                                const hourlyRate = <?php echo $venue['price_min']; ?>;
                                const isReservation = <?php echo json_encode(($venue['booking_type'] ?? 'rent') === 'reservation'); ?>;

                                function calculatePrice() {
                                    if (isReservation) return; // Skip calculation for reservations

                                    if (startInput.value && endInput.value) {
                                        const start = new Date('2000-01-01 ' + startInput.value);
                                        const end = new Date('2000-01-01 ' + endInput.value);
                                        let diff = (end - start) / (1000 * 60 * 60);

                                        if (diff < 0) diff += 24; // Handle overnight

                                        if (diff > 0) {
                                            const base = Math.round(diff * hourlyRate);
                                            const fee = Math.round(base * 0.05);
                                            const total = base + fee;

                                            document.getElementById('calcHours').textContent = diff.toFixed(1);
                                            document.getElementById('calcBase').textContent = base.toLocaleString();
                                            document.getElementById('calcFee').textContent = fee.toLocaleString();
                                            document.getElementById('calcTotal').textContent = total.toLocaleString();

                                            priceCalc.classList.remove('hidden');
                                        } else {
                                            priceCalc.classList.add('hidden');
                                        }
                                    }
                                }

                                startInput.addEventListener('change', calculatePrice);
                                endInput.addEventListener('change', calculatePrice);
                            });
                        </script>

                        <!-- Host Card -->
                        <div class="rounded-xl border p-6 shadow-sm"
                            style="border-color: var(--border-color); background-color: var(--bg-card);">
                            <h3 class="mb-4 font-semibold" style="color: var(--text-primary);">Hosted by</h3>
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full"
                                    style="background-color: var(--bg-primary); color: var(--text-primary);">
                                    <span
                                        class="text-lg font-semibold"><?php echo strtoupper(substr($host_name, 0, 1)); ?></span>
                        </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-1">
                                        <span
                                            class="font-medium" style="color: var(--text-primary);"><?php echo htmlspecialchars($host_name); ?></span>
                        </div>
                                    <p class="text-sm" style="color: var(--text-secondary);">Host</p>
                    </div>
                </div>
                            <?php if ($host_contact): ?>
                                <button
                                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition"
                                    style="border-color: var(--border-color); background-color: transparent; color: var(--text-primary);"
                                    onmouseover="this.style.backgroundColor='var(--bg-card)'"
                                    onmouseout="this.style.backgroundColor='transparent'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                    </svg>
                                    Contact Host
                                </button>
                            <?php endif; ?>
            </div>

                        <!-- Safety -->
                        <div class="rounded-xl border border-green-500/20 bg-green-500/5 p-4">
                            <div class="flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 text-green-500"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                </svg>
                                <div>
                                    <h4 class="font-medium" style="color: var(--text-primary);">Secure Booking</h4>
                                    <p class="text-sm" style="color: var(--text-secondary);">
                                        Your deposit is held in escrow until your booking is confirmed.
                                    </p>
        </div>
    </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Sticky Footer -->
        <div class="fixed bottom-0 left-0 right-0 border-t p-4 md:hidden"
            style="border-color: var(--border-color); background-color: var(--bg-card);">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <span
                        class="text-lg font-bold" style="color: var(--text-primary);">GH₵<?php echo number_format($venue['price_min'], 0); ?></span>
                    <?php if ($venue['price_min'] != $venue['price_max']): ?>
                        <span style="color: var(--text-primary);"> - GH₵<?php echo number_format($venue['price_max'], 0); ?></span>
                    <?php endif; ?>
                    <span class="text-sm" style="color: var(--text-secondary);"> per event</span>
        </div>
                <?php if (is_logged_in()): ?>
                    <a href="booking.php?id=<?php echo $venue_id; ?>"
                        class="rounded-lg px-6 py-2.5 text-sm font-semibold transition"
                        style="background-color: var(--accent); color: white;"
                        onmouseover="this.style.backgroundColor='var(--accent-hover)'"
                        onmouseout="this.style.backgroundColor='var(--accent)'">
                        Request to Book
                    </a>
                <?php else: ?>
                    <a href="login.php"
                        class="rounded-lg px-6 py-2.5 text-sm font-semibold transition"
                        style="background-color: var(--accent); color: white;"
                        onmouseover="this.style.backgroundColor='var(--accent-hover)'"
                        onmouseout="this.style.backgroundColor='var(--accent)'">
                        Login to Book
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Review Modal -->
    <div id="reviewModal" class="fixed inset-0 z-50 hidden items-center justify-center backdrop-blur-sm"
        style="background-color: rgba(0, 0, 0, 0.7);">
        <div class="relative w-full max-w-md rounded-lg border p-6 shadow-xl"
            style="border-color: var(--border-color); background-color: var(--bg-card);">
            <button onclick="closeReviewModal()" class="absolute right-4 top-4 transition"
                style="color: var(--text-secondary);"
                onmouseover="this.style.color='var(--text-primary)'"
                onmouseout="this.style.color='var(--text-secondary)'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
            <h3 class="mb-6 text-lg font-semibold" style="color: var(--text-primary);">Write a Review</h3>
            <div id="reviewAlertMessage" class="hidden mb-4 p-3 rounded-lg text-sm"></div>
        <form id="reviewForm">
            <input type="hidden" name="venue_id" value="<?php echo $venue_id; ?>">
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium" style="color: var(--text-primary);">Your Rating *</label>
                    <div id="starRating" class="text-3xl cursor-pointer">
                        <span class="star-rating hover:text-amber-400" data-rating="1" style="color: var(--text-secondary);">★</span>
                        <span class="star-rating hover:text-amber-400" data-rating="2" style="color: var(--text-secondary);">★</span>
                        <span class="star-rating hover:text-amber-400" data-rating="3" style="color: var(--text-secondary);">★</span>
                        <span class="star-rating hover:text-amber-400" data-rating="4" style="color: var(--text-secondary);">★</span>
                        <span class="star-rating hover:text-amber-400" data-rating="5" style="color: var(--text-secondary);">★</span>
                </div>
                <input type="hidden" name="rating" id="ratingValue" value="0" required>
            </div>
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium" style="color: var(--text-primary);">Your Review *</label>
                    <textarea name="review_text" rows="6" placeholder="Share your experience with this venue..."
                        class="w-full rounded-lg border px-3 py-2 focus:border-[#FF6B35] focus:outline-none"
                        style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-primary);"
                          required></textarea>
                    <style>
                        textarea::placeholder {
                            color: var(--text-secondary);
                        }
                    </style>
            </div>
                <button type="submit" id="submitReviewBtn"
                    class="w-full rounded-lg px-4 py-2 text-sm font-semibold transition"
                    style="background-color: var(--accent); color: white;"
                    onmouseover="this.style.backgroundColor='var(--accent-hover)'"
                    onmouseout="this.style.backgroundColor='var(--accent)'">
                    Submit Review
            </button>
        </form>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
        let currentImageIndex = 0;
        const photos = <?php echo json_encode($photos); ?>;

        function setCurrentImage(index) {
            currentImageIndex = index;
            const mainImage = document.getElementById('mainImage');
            if (mainImage && photos[index]) {
                mainImage.src = photos[index];
            }

            // Update dots
            document.querySelectorAll('.image-dot').forEach((dot, idx) => {
                if (idx === index) {
                    dot.classList.remove('w-2', 'bg-white/50');
                    dot.classList.add('w-6', 'bg-white');
                } else {
                    dot.classList.remove('w-6', 'bg-white');
                    dot.classList.add('w-2', 'bg-white/50');
                }
    });
    
            // Update thumbnails
            document.querySelectorAll('.thumbnail-btn').forEach((btn, idx) => {
                if (idx === index) {
                    btn.classList.add('ring-2', 'ring-[#FF6B35]');
                } else {
                    btn.classList.remove('ring-2', 'ring-[#FF6B35]');
                }
            });
        }

        let isSaved = <?php echo $is_saved ? 'true' : 'false'; ?>;
        
        function toggleSave() {
            const venueId = document.getElementById('saveBtn').dataset.venueId;
            const action = isSaved ? 'unsave' : 'save';
            const heartIcon = document.getElementById('heartIcon');
            
            // Optimistic UI update
            isSaved = !isSaved;
            if (heartIcon) {
                if (isSaved) {
                    heartIcon.setAttribute('fill', 'currentColor');
                    heartIcon.style.color = '#FF6B35';
                } else {
                    heartIcon.setAttribute('fill', 'none');
                    heartIcon.style.color = 'var(--text-primary)';
                }
            }

            // Send request
            fetch('../actions/toggle_saved_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    venue_id: venueId,
                    action: action
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'error') {
                    // Revert UI on error
                    isSaved = !isSaved;
                    if (heartIcon) {
                        if (isSaved) {
                            heartIcon.setAttribute('fill', 'currentColor');
                            heartIcon.style.color = '#FF6B35';
                        } else {
                            heartIcon.setAttribute('fill', 'none');
                            heartIcon.style.color = 'var(--text-primary)';
            }
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
                isSaved = !isSaved;
                if (heartIcon) {
                    if (isSaved) {
                        heartIcon.setAttribute('fill', 'currentColor');
                        heartIcon.style.color = '#FF6B35';
                    } else {
                        heartIcon.setAttribute('fill', 'none');
                        heartIcon.style.color = 'var(--text-primary)';
    }
                }
            });
    }

        function shareVenue() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo htmlspecialchars($venue['title']); ?>',
                    text: '<?php echo htmlspecialchars($venue['description']); ?>',
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(window.location.href);
                alert('Link copied to clipboard!');
            }
}

        // Review Modal
function openReviewModal() {
            document.getElementById('reviewModal').classList.remove('hidden');
            document.getElementById('reviewModal').classList.add('flex');
}

function closeReviewModal() {
            document.getElementById('reviewModal').classList.add('hidden');
            document.getElementById('reviewModal').classList.remove('flex');
}

let selectedRating = 0;
        document.querySelectorAll('.star-rating').forEach(star => {
            star.addEventListener('mouseenter', function () {
                const rating = parseInt(this.dataset.rating);
        highlightStars(rating);
            });
            star.addEventListener('mouseleave', function () {
        highlightStars(selectedRating);
            });
            star.addEventListener('click', function () {
                selectedRating = parseInt(this.dataset.rating);
                document.getElementById('ratingValue').value = selectedRating;
    highlightStars(selectedRating);
            });
});

function highlightStars(rating) {
            document.querySelectorAll('.star-rating').forEach((star, idx) => {
                if (idx < rating) {
                    star.style.color = '#fbbf24'; // amber-400
        } else {
                    star.style.color = 'var(--text-secondary)';
        }
    });
}

// Review form submission
        document.getElementById('reviewForm').addEventListener('submit', function (e) {
    e.preventDefault();
            const rating = document.getElementById('ratingValue').value;
    if (rating == 0) {
        showReviewAlert('Please select a rating', 'danger');
        return;
    }
    
            const submitBtn = document.getElementById('submitReviewBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
    
            const formData = new FormData(this);
            fetch('../actions/review_add_action.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(result => {
            if (result.success) {
                showReviewAlert(result.message, 'success');
                        setTimeout(() => location.reload(), 2000);
            } else {
                showReviewAlert(result.message, 'danger');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Submit Review';
            }
                })
                .catch(error => {
            showReviewAlert('An error occurred. Please try again.', 'danger');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Review';
    });
});

        function showReviewAlert(message, type) {
            const alertDiv = document.getElementById('reviewAlertMessage');
            alertDiv.className = type === 'success'
                ? 'mb-4 rounded-lg bg-green-500/10 border border-green-500/30 p-3 text-sm text-green-500'
                : 'mb-4 rounded-lg bg-red-500/10 border border-red-500/30 p-3 text-sm text-red-500';
            alertDiv.textContent = message;
            alertDiv.classList.remove('hidden');
        }

        // Initialize map
        <?php if ($lat && $lng): ?>
            if (typeof L !== 'undefined') {
                const venueLat = <?php echo $lat; ?>;
                const venueLng = <?php echo $lng; ?>;
                const venueName = <?php echo json_encode($venue['title']); ?>;
                const venueAddress = <?php echo json_encode($venue['location_text']); ?>;

                function initVenueMap() {
                    const mapContainer = document.getElementById('venueMap');
                    if (!mapContainer) return;

                    if (mapContainer.offsetWidth === 0 || mapContainer.offsetHeight === 0) {
                        setTimeout(initVenueMap, 200);
                        return;
                    }

                    const map = L.map('venueMap').setView([venueLat, venueLng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(map);

                    const orangeIcon = L.icon({
                        iconUrl: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="48" viewBox="0 0 32 48"><path fill="%23FF6B35" d="M16 0C7.163 0 0 7.163 0 16c0 11.5 16 32 16 32s16-20.5 16-32C32 7.163 24.837 0 16 0zm0 22c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z"/></svg>',
                        iconSize: [32, 48],
                        iconAnchor: [16, 48],
                        popupAnchor: [0, -48]
                    });

                    const marker = L.marker([venueLat, venueLng], { icon: orangeIcon }).addTo(map);
                    marker.bindPopup(
                        '<div style="color: #010101; font-weight: 600; margin-bottom: 5px;">' + venueName + '</div>' +
                        '<div style="color: #777; font-size: 13px;">' + venueAddress + '</div>'
                    ).openPopup();

                    setTimeout(() => map.invalidateSize(), 100);
                }

                setTimeout(initVenueMap, 100);
            }
        <?php endif; ?>
</script>
</body>

</html>