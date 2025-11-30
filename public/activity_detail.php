<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../settings/db_cred.php');
require_once(__DIR__ . '/../controllers/activity_controller.php');
require_once(__DIR__ . '/../includes/site_nav.php');

$activity_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$activity_id) {
    header('Location: search.php?type=activity');
    exit();
}

$activity = get_activity_by_id_ctr($activity_id);
if (!$activity) {
    header('Location: search.php?type=activity');
    exit();
}

// Parse photos
$photos = json_decode($activity['photos_json'] ?? '[]', true);
if (empty($photos)) {
    $photos = ['../images/portfolio/01.jpg'];
} else {
    $photos = array_map(function ($photo) {
        if (strpos($photo, 'uploads/activities/') === 0) {
            return '../' . $photo;
        } elseif (strpos($photo, 'uploads/') === 0) {
            return '../' . $photo;
        }
        return $photo;
    }, $photos);
}

// Parse GPS coordinates
$lat = null;
$lng = null;
if (!empty($activity['gps_code']) && strpos($activity['gps_code'], ',') !== false) {
    list($lat, $lng) = explode(',', $activity['gps_code']);
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

// Format date and time
$start_date = !empty($activity['start_at']) ? date('F j, Y', strtotime($activity['start_at'])) : '';
$start_time = !empty($activity['start_at']) ? date('g:i A', strtotime($activity['start_at'])) : '';
$end_date = !empty($activity['end_at']) ? date('F j, Y', strtotime($activity['end_at'])) : '';
$end_time = !empty($activity['end_at']) ? date('g:i A', strtotime($activity['end_at'])) : '';

$activity_type_display = ucfirst(str_replace('_', ' ', $activity['activity_type'] ?? 'Activity'));
$price_display = isset($activity['is_free']) && $activity['is_free'] ? 'FREE' : 'GH₵' . number_format($activity['price_min'] ?? 0, 0);
?>
<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($activity['title']); ?> - Go Outside</title>
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

.activity-map-container {
    width: 100%;
    height: 400px;
            border-radius: 8px;
    overflow: hidden;
            border: 1px solid rgba(39, 39, 42, 0.7);
}

.activity-map-container .leaflet-container {
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
                    alt="<?php echo htmlspecialchars($activity['title']); ?>" id="mainImage"
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
                    <a href="search.php?type=activity"
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
                    <button onclick="shareActivity()"
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
                                <?php echo htmlspecialchars($activity_type_display); ?>
                            </span>
                        </div>
                        <h1 class="mb-2 text-2xl font-bold md:text-3xl" style="color: var(--text-primary);">
                            <?php echo htmlspecialchars($activity['title']); ?>
                        </h1>
                        <div class="flex flex-wrap items-center gap-3 text-sm" style="color: var(--text-secondary);">
                            <?php if ($start_date): ?>
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    <?php echo $start_date; ?>
                                    <?php if ($start_time): ?>
                                        <span>at <?php echo $start_time; ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <?php echo htmlspecialchars($activity['location_text'] ?? 'Location TBA'); ?>
                            </div>
                            <?php if (isset($activity['capacity']) && $activity['capacity']): ?>
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                    <?php echo $activity['capacity']; ?> spots available
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Description -->
                    <?php if (!empty($activity['description'])): ?>
                        <div class="mb-6">
                            <h2 class="mb-2 text-lg font-semibold" style="color: var(--text-primary);">About</h2>
                            <p class="leading-relaxed" style="color: var(--text-secondary);">
                                <?php echo nl2br(htmlspecialchars($activity['description'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Location Map -->
                    <?php if ($lat && $lng): ?>
                        <div class="mb-6">
                            <h2 class="mb-3 text-lg font-semibold" style="color: var(--text-primary);">Location</h2>
                            <div id="activityMap" class="activity-map-container"></div>
                        </div>
                    <?php endif; ?>

                    <!-- Host Info -->
                    <?php if (!empty($activity['host_name'])): ?>
                        <div class="mb-6">
                            <h2 class="mb-3 text-lg font-semibold" style="color: var(--text-primary);">Hosted by</h2>
                            <div class="flex items-center gap-3 rounded-lg p-4" style="background-color: var(--bg-card);">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full" style="background-color: var(--accent);">
                                    <span class="text-lg font-semibold text-white">
                                        <?php echo strtoupper(substr($activity['host_name'], 0, 1)); ?>
                                    </span>
                                </div>
                                <div>
                                    <div class="font-semibold" style="color: var(--text-primary);">
                                        <?php echo htmlspecialchars($activity['host_name']); ?>
                                    </div>
                                    <?php if (!empty($activity['host_email'])): ?>
                                        <div class="text-sm" style="color: var(--text-secondary);">
                                            <?php echo htmlspecialchars($activity['host_email']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="md:col-span-1">
                    <div class="sticky top-24 rounded-xl border p-6" style="border-color: var(--border-color); background-color: var(--bg-card);">
                        <div class="mb-4">
                            <div class="mb-2 text-2xl font-bold" style="color: var(--text-primary);">
                                <?php echo $price_display; ?>
                            </div>
                            <?php if (!isset($activity['is_free']) || !$activity['is_free']): ?>
                                <div class="text-sm" style="color: var(--text-secondary);">per person</div>
                            <?php endif; ?>
                        </div>

                        <?php if ($start_date): ?>
                            <div class="mb-4 rounded-lg border p-3" style="border-color: var(--border-color); background-color: var(--bg-secondary);">
                                <div class="mb-1 text-xs font-medium" style="color: var(--text-secondary);">Date & Time</div>
                                <div class="font-semibold" style="color: var(--text-primary);">
                                    <?php echo $start_date; ?>
                                </div>
                                <?php if ($start_time): ?>
                                    <div class="text-sm" style="color: var(--text-secondary);">
                                        <?php echo $start_time; ?>
                                        <?php if ($end_time && $end_time !== $start_time): ?>
                                            - <?php echo $end_time; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($activity['capacity']) && $activity['capacity']): ?>
                            <div class="mb-4 rounded-lg border p-3" style="border-color: var(--border-color); background-color: var(--bg-secondary);">
                                <div class="mb-1 text-xs font-medium" style="color: var(--text-secondary);">Capacity</div>
                                <div class="font-semibold" style="color: var(--text-primary);">
                                    <?php echo $activity['capacity']; ?> spots available
                                </div>
                            </div>
                        <?php endif; ?>

                        <button onclick="alert('RSVP functionality coming soon!')"
                            class="w-full rounded-lg px-4 py-3 text-center font-semibold transition"
                            style="background-color: var(--accent); color: #ffffff;"
                            onmouseover="this.style.backgroundColor='var(--accent-hover)'" onmouseout="this.style.backgroundColor='var(--accent)'">
                            RSVP Now
                        </button>

                        <div class="mt-4 text-center text-xs" style="color: var(--text-secondary);">
                            Free cancellation up to 24 hours before
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Image gallery
        const photos = <?php echo json_encode($photos); ?>;
        let currentImageIndex = 0;

        function setCurrentImage(index) {
            currentImageIndex = index;
            const mainImage = document.getElementById('mainImage');
            if (mainImage) {
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

        function shareActivity() {
            if (navigator.share) {
                navigator.share({
                    title: <?php echo json_encode($activity['title']); ?>,
                    text: <?php echo json_encode($activity['description'] ?? ''); ?>,
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(window.location.href);
                alert('Link copied to clipboard!');
            }
        }

        // Initialize map
        <?php if ($lat && $lng): ?>
            const activityLat = <?php echo $lat; ?>;
            const activityLng = <?php echo $lng; ?>;
            const activityName = <?php echo json_encode($activity['title']); ?>;
            const activityAddress = <?php echo json_encode($activity['location_text']); ?>;

            function initActivityMap() {
                const mapContainer = document.getElementById('activityMap');
                if (!mapContainer) return;

                if (mapContainer.offsetWidth === 0 || mapContainer.offsetHeight === 0) {
                    setTimeout(initActivityMap, 200);
                    return;
                }

                const map = L.map('activityMap').setView([activityLat, activityLng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(map);

                const orangeIcon = L.icon({
                    iconUrl: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="48" viewBox="0 0 32 48"><path fill="%23FF6B35" d="M16 0C7.163 0 0 7.163 0 16c0 11.5 16 32 16 32s16-20.5 16-32C32 7.163 24.837 0 16 0zm0 22c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z"/></svg>',
                    iconSize: [32, 48],
                    iconAnchor: [16, 48],
                });

                const marker = L.marker([activityLat, activityLng], { icon: orangeIcon }).addTo(map);
                marker.bindPopup(
                    '<div style="color: #010101; font-weight: 600; margin-bottom: 5px;">' + activityName + '</div>' +
                    '<div style="color: #777; font-size: 13px;">' + activityAddress + '</div>'
                ).openPopup();

                setTimeout(() => map.invalidateSize(), 100);
            }

            setTimeout(initActivityMap, 100);
        <?php endif; ?>
    </script>
</body>

</html>

