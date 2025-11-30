<?php
require_once(__DIR__ . '/settings/core.php');
require_once(__DIR__ . '/controllers/venue_controller.php');
require_once(__DIR__ . '/controllers/category_controller.php');
require_once(__DIR__ . '/controllers/activity_controller.php');
require_once(__DIR__ . '/includes/site_nav.php');

$featured_venues = get_all_approved_venues_ctr();
if ($featured_venues === false) {
    $featured_venues = [];
}
$featured_venues = array_slice($featured_venues, 0, 8);

$categories = get_all_categories_ctr();
if ($categories === false) {
    $categories = [];
}

function gooutside_slug($text)
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function gooutside_vibes($cat)
{
    $map = [
        'Event Spaces' => ['Luxury', 'Corporate', 'Elegant'],
        'Clubs & Lounges' => ['Nightlife', 'DJ', 'VIP'],
        'Restaurants' => ['Foodie', 'Social', 'Casual'],
        'Studios' => ['Creative', 'Culture', 'Curated'],
        'Short Stays' => ['Retreat', 'Nature', 'Cozy'],
        'Beach' => ['Sunset', 'Party', 'Coastline'],
    ];
    return $map[$cat] ?? ['Verified', 'Community'];
}

$discover_filters = [
    ['id' => 'all', 'label' => 'All'],
    ['id' => 'lounge', 'label' => 'Lounges'],
    ['id' => 'beach', 'label' => 'Beach'],
    ['id' => 'event', 'label' => 'Event Spaces'],
    ['id' => 'club', 'label' => 'Clubs'],
    ['id' => 'studio', 'label' => 'Studios'],
    ['id' => 'restaurant', 'label' => 'Restaurants'],
    ['id' => 'short-stay', 'label' => 'Short Stays'],
];

// Activity category filters (matching Vercel app)
$activity_filters = [
    ['id' => 'all', 'label' => 'All'],
    ['id' => 'workshop', 'label' => 'Workshops'],
    ['id' => 'food', 'label' => 'Food Tours'],
    ['id' => 'adventure', 'label' => 'Adventure'],
    ['id' => 'concert', 'label' => 'Concerts'],
    ['id' => 'sports', 'label' => 'Sports'],
    ['id' => 'festival', 'label' => 'Festivals'],
];

// Map activity types to filter categories
// This maps database activity_type values to the filter categories
function map_activity_type_to_filter($activity_type) {
    $mapping = [
        'workshop' => 'workshop',
        'class' => 'workshop', // Classes are also workshops
        'tour' => 'adventure', // Tours are adventures
        'food' => 'food',
        'adventure' => 'adventure',
        'concert' => 'concert',
        'sports' => 'sports',
        'festival' => 'festival',
        'popup' => 'food', // Pop-ups often include food
        'game_night' => 'sports', // Game nights are sports/activities
        'meetup' => 'adventure', // Meetups can be adventures
        'nightlife' => 'concert', // Nightlife includes concerts
    ];
    return $mapping[strtolower($activity_type)] ?? 'all';
}

$featured_activities = search_activities_ctr([]);
if ($featured_activities === false) {
    $featured_activities = [];
}
$featured_activities = array_slice($featured_activities, 0, 8);

$book_confidence = [
    ['icon' => 'fa-star', 'title' => 'Verified Reviews', 'desc' => 'Only guests who attended can leave feedback.'],
    ['icon' => 'fa-shield-heart', 'title' => 'Secure Payments', 'desc' => 'Paystack escrow protects every deposit.'],
    ['icon' => 'fa-id-card', 'title' => 'Host Verification', 'desc' => 'All hosts verify identity with Ghana Card.'],
];

$happening_soon = get_upcoming_activities_ctr(4);
if ($happening_soon === false) {
    $happening_soon = [];
}
?>
<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="description" content="Go Outside - Discover and book verified venues in Ghana">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Go Outside - Discover Verified Venues & Experiences in Ghana</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        background: 'var(--bg-primary)',
                        surface: 'var(--bg-secondary)',
                        card: 'var(--bg-card)',
                        accent: '#FF6B35',
                        border: 'var(--border-color)',
                        muted: 'var(--text-muted)',
                        foreground: 'var(--text-primary)',
                        'muted-foreground': 'var(--text-secondary)'
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui']
                    }
                }
            }
        }
    </script>
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
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .gradient {
            background: radial-gradient(circle at top, rgba(255, 107, 53, 0.35), transparent 60%), radial-gradient(circle at 20% 20%, rgba(224, 70, 143, 0.2), transparent 45%);
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
            }

        /* Tab switcher - selected state with darker background */
        .discover-tab-btn.shadow-sm {
            background-color: rgba(10, 10, 10, 0.95) !important;
            color: var(--text-primary) !important;
        }

        [data-theme="light"] .discover-tab-btn.shadow-sm {
            background-color: rgba(229, 229, 229, 0.95) !important;
            }

        /* Theme-aware text colors */
        .text-foreground {
            color: var(--text-primary);
            }

        .text-muted-foreground {
            color: var(--text-secondary);
            }

        /* Theme-aware backgrounds */
        .bg-primary {
            background-color: var(--bg-primary);
        }

        .bg-secondary {
            background-color: var(--bg-secondary);
              }

        .bg-card {
            background-color: var(--bg-card);
        }

        /* Theme-aware borders */
        .border-theme {
            border-color: var(--border-color);
            }
          </style>
</head>

<body class="bg-background" style="background-color: var(--bg-primary); color: var(--text-primary);">
    <?php render_site_nav(['is_home' => true, 'base_path' => '']); ?>

    <main class="space-y-16 pb-20">
        <!-- Hero -->
        <section class="relative overflow-hidden px-4 pt-20 pb-8 md:pt-24 md:pb-12">
            <div class="gradient absolute inset-0 opacity-80"></div>
            <div class="relative mx-auto max-w-4xl text-center">
                <p class="mb-2 text-sm font-medium text-muted">Akwaaba! Welcome to</p>
                <h1 class="mb-3 text-3xl font-bold leading-tight tracking-tight md:text-5xl">Go Outside</h1>
                <p class="mx-auto mb-6 max-w-lg text-base text-muted md:mb-8 md:text-lg">
                    Discover and book amazing venues and experiences across Ghana. Find your next adventure, verified
                    and trusted.
                </p>

                <!-- Search Bar -->
                <div class="mx-auto max-w-2xl">
                    <form action="public/search.php" method="GET"
                        class="relative flex items-center rounded-full bg-card p-2 shadow-lg transition-all focus-within:ring-2 focus-within:ring-accent/50">
                        <!-- Search Icon -->
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="h-5 w-5 md:h-6 md:w-6" style="color: var(--text-muted);">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>
              </div>

                        <!-- Search Input -->
                        <input type="text" name="q" placeholder="Search venues, activities..."
                            class="w-full rounded-full bg-transparent py-3 pl-12 pr-24 text-base placeholder:text-muted focus:outline-none md:py-4 md:pl-14 md:pr-28 md:text-lg"
                            style="color: var(--text-primary);">

                        <!-- Filters Button (Optional) -->
                        <button type="button" onclick="window.location.href='public/search.php?open_filters=true'"
                            class="absolute right-16 flex h-8 w-8 items-center justify-center rounded-full transition-colors hover:bg-secondary md:right-20 md:h-10 md:w-10"
                            aria-label="Filters">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="h-4 w-4 md:h-5 md:w-5" style="color: var(--text-muted);">
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

                        <!-- Search Button -->
                        <button type="submit"
                            class="absolute right-2 flex items-center justify-center rounded-full bg-accent px-6 py-2.5 font-semibold text-white transition hover:bg-accent-hover md:px-8 md:py-3">
                            <span class="hidden md:inline">Search</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="h-5 w-5 md:hidden">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>
                        </button>
            </form>
          </div>
        </div>
    </div>
  </div>
</section>

        <!-- Discover -->
        <section id="discover" class="px-4 py-6 md:py-10">
            <div class="container mx-auto max-w-6xl">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold md:text-2xl" style="color: var(--text-primary);">Discover</h2>
                    <a href="public/search.php" class="flex items-center gap-1 text-sm text-muted" style="color: var(--text-secondary); transition: color 0.2s;" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">
                        View all
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
  </div>

                <!-- Tab Switcher -->
                <div class="mb-4 flex gap-1 rounded-lg p-1" style="background-color: var(--bg-secondary);">
                    <button data-discover-tab="venues"
                        class="discover-tab-btn flex-1 rounded-md py-2 text-sm font-medium transition-all shadow-sm" style="color: var(--text-primary);">Venues</button>
                    <button data-discover-tab="activities"
                        class="discover-tab-btn flex-1 rounded-md py-2 text-sm font-medium transition-all" style="color: var(--text-secondary); background-color: transparent;" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">Activities</button>
    </div>

                <!-- Category Pills - Venues -->
                <div class="mb-4 flex flex-wrap gap-2 overflow-x-auto scrollbar-hide" id="venue-filters">
                    <?php foreach ($discover_filters as $filter): ?>
                        <button data-category-filter="<?php echo $filter['id']; ?>"
                            class="discover-filter shrink-0 rounded-full border border-border bg-surface px-4 py-2 text-sm font-medium transition-all" style="color: var(--text-secondary); background-color: var(--bg-secondary); border-color: var(--border-color);" onmouseover="this.style.color='var(--text-primary)'; this.style.backgroundColor='var(--bg-secondary)'" onmouseout="this.style.color='var(--text-secondary)'; this.style.backgroundColor='var(--bg-secondary)'">
                            <?php echo $filter['label']; ?>
                        </button>
      <?php endforeach; ?>
    </div>

                <!-- Category Pills - Activities -->
                <div class="mb-4 hidden flex flex-wrap gap-2 overflow-x-auto scrollbar-hide" id="activity-filters">
                    <?php foreach ($activity_filters as $filter): ?>
                        <button data-activity-filter="<?php echo $filter['id']; ?>"
                            class="activity-filter-btn shrink-0 rounded-full border border-border bg-surface px-4 py-2 text-sm font-medium transition-all" style="color: var(--text-secondary); background-color: var(--bg-secondary); border-color: var(--border-color);" onmouseover="this.style.color='var(--text-primary)'; this.style.backgroundColor='var(--bg-secondary)'" onmouseout="this.style.color='var(--text-secondary)'; this.style.backgroundColor='var(--bg-secondary)'">
                            <?php echo $filter['label']; ?>
                        </button>
                    <?php endforeach; ?>
  </div>

                <!-- Items Grid -->
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" id="discover-venues">
                    <?php if (!empty($featured_venues)): ?>
                        <?php foreach ($featured_venues as $venue):
                            $photos = json_decode($venue['photos_json'] ?? '[]', true);
                            $photo = $photos[0] ?? 'images/portfolio/01.jpg';
                            if (strpos($photo, '../') === 0) {
                                $photo = substr($photo, 3);
                            }
                            $vibes = gooutside_vibes($venue['cat_name'] ?? '');
                            $cat_slug = gooutside_slug($venue['cat_name'] ?? '');
                            ?>
                            <a href="public/venue_detail.php?id=<?php echo $venue['venue_id']; ?>"
                                class="discover-card group relative flex flex-col overflow-hidden rounded-xl bg-card transition-all hover:shadow-lg"
                                data-category="<?php echo $cat_slug ?: 'all'; ?>">
                                <div class="relative aspect-[4/3] overflow-hidden bg-muted">
                                    <img src="<?php echo htmlspecialchars($photo); ?>"
                                        alt="<?php echo htmlspecialchars($venue['title']); ?>"
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
<?php
                                    $is_saved = false;
                                    if (is_logged_in()) {
                                        require_once(__DIR__ . '/controllers/customer_controller.php');
                                        $is_saved = is_venue_saved_ctr(get_user_id(), $venue['venue_id']);
                                    }
                                    ?>
                                    <button
                                        class="save-venue-btn absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full backdrop-blur-sm transition-colors"
                                        style="background-color: var(--bg-primary); opacity: 0.8;"
                                        onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'"
                                        data-venue-id="<?php echo $venue['venue_id']; ?>"
                                        data-saved="<?php echo $is_saved ? 'true' : 'false'; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="<?php echo $is_saved ? 'currentColor' : 'none'; ?>"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="h-4 w-4" style="color: <?php echo $is_saved ? '#FF6B35' : 'var(--text-primary)'; ?>;">
                                            <path
                                                d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex flex-1 flex-col p-3">
                                    <div class="mb-1 flex items-center gap-2 text-xs text-muted">
                                        <span
                                            class="font-medium" style="color: var(--text-secondary);"><?php echo htmlspecialchars($venue['cat_name']); ?></span>
                                        <span>•</span>
                                        <span class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" class="h-3 w-3">
                                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                                <circle cx="12" cy="10" r="3" />
                                            </svg>
                                            <?php echo htmlspecialchars($venue['location_text']); ?>
                                        </span>
                                    </div>
                                    <h3
                                        class="mb-1 line-clamp-1 text-base font-semibold transition-colors group-hover:text-accent" style="color: var(--text-primary);">
                                        <?php echo htmlspecialchars($venue['title']); ?>
                                    </h3>
                                    <div class="mt-1 flex items-center gap-1.5 text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="h-3.5 w-3.5 text-amber-400">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                        <span class="font-medium">4.8</span>
                                        <span class="text-muted">(234 reviews)</span>
                                        <span
                                            class="ml-auto flex items-center gap-1 rounded border border-accent/30 px-1.5 py-0 text-xs text-accent">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" class="h-3 w-3">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                                <polyline points="22 4 12 14.01 9 11.01" />
                                            </svg>
                                            Verified
                                        </span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        <?php foreach (array_slice($vibes, 0, 3) as $vibe): ?>
                                            <span
                                                class="rounded border border-border bg-surface px-2 py-0.5 text-xs font-normal text-muted"><?php echo $vibe; ?></span>
                                        <?php endforeach; ?>
    </div>
                                    <div class="mt-auto flex items-end justify-between pt-2">
          <div>
                                            <span
                                                class="text-lg font-bold" style="color: var(--text-primary);">GH₵<?php echo number_format($venue['price_min'], 0); ?></span>
                                            <span class="text-sm text-muted"> per event</span>
          </div>
        </div>
      </div>
                            </a>
      <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full flex flex-col items-center justify-center py-12 text-center">
                            <div class="mb-4 rounded-full bg-muted p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="h-8 w-8 text-muted">
                                    <circle cx="11" cy="11" r="8" />
                                    <path d="m21 21-4.3-4.3" />
                                </svg>
    </div>
                            <h3 class="mb-1 font-semibold" style="color: var(--text-primary);">No venues yet.</h3>
                            <p class="text-sm text-muted">Try selecting a different category</p>
    </div>
                    <?php endif; ?>
    </div>
    
                <div class="mt-4 hidden grid gap-4 sm:grid-cols-2 lg:grid-cols-3" id="discover-activities">
                    <?php if (!empty($featured_activities)): ?>
                        <?php foreach ($featured_activities as $activity):
                            $photos = json_decode($activity['photos_json'] ?? '[]', true);
                            $photo = $photos[0] ?? 'images/portfolio/01.jpg';
                            if (strpos($photo, '../') === 0) {
                                $photo = substr($photo, 3);
                            }
                            $date_display = date('j M', strtotime($activity['start_at'] ?? $activity['created_at']));
                            $activity_type_display = ucfirst(str_replace('_', ' ', $activity['activity_type'] ?? 'Activity'));
                            $price_display = $activity['is_free'] ? 'FREE' : 'GH₵' . number_format($activity['price_min'], 0);
                            ?>
                            <a href="public/search.php?type=activities&id=<?php echo $activity['activity_id']; ?>"
                                class="discover-card group relative flex flex-col overflow-hidden rounded-xl bg-card transition-all hover:shadow-lg"
                                data-activity-type="<?php echo strtolower($activity['activity_type'] ?? 'other'); ?>">
                                <div class="relative aspect-[4/3] overflow-hidden bg-muted">
                                    <img src="<?php echo htmlspecialchars($photo); ?>"
                                        alt="<?php echo htmlspecialchars($activity['title']); ?>"
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    <span
                                        class="absolute bottom-2 left-2 rounded border border-border bg-background/90 px-2 py-1 text-xs backdrop-blur-sm">
                                        <?php echo $date_display; ?>
              </span>
                                </div>
                                <div class="flex flex-1 flex-col p-3">
                                    <div class="mb-1 flex items-center gap-2 text-xs text-muted">
                                        <span
                                            class="font-medium" style="color: var(--text-secondary);"><?php echo htmlspecialchars($activity_type_display); ?></span>
                                        <span>•</span>
                                        <span class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" class="h-3 w-3">
                                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                                <circle cx="12" cy="10" r="3" />
                                            </svg>
                                            <?php echo htmlspecialchars($activity['location_text']); ?>
              </span>
            </div>
                                    <h3
                                        class="mb-1 line-clamp-1 text-base font-semibold transition-colors group-hover:text-accent" style="color: var(--text-primary);">
                                        <?php echo htmlspecialchars($activity['title']); ?>
                                    </h3>
                                    <div class="mt-auto flex items-end justify-between pt-2">
                                        <div>
                                            <span
                                                class="text-lg font-bold" style="color: var(--text-primary);"><?php echo $price_display; ?></span>
                                            <?php if (!$activity['is_free']): ?>
                                                <span class="text-sm text-muted"> per person</span>
                                            <?php endif; ?>
          </div>
                                        <?php if ($activity['capacity']): ?>
                                            <span
                                                class="rounded border border-border bg-surface px-2 py-1 text-xs text-muted"><?php echo $activity['capacity']; ?>
                                                spots</span>
                                        <?php endif; ?>
        </div>
      </div>
                            </a>
      <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full flex flex-col items-center justify-center py-12 text-center">
                            <div class="mb-4 rounded-full bg-muted p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="h-8 w-8 text-muted">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                            </div>
                            <h3 class="mb-1 font-semibold" style="color: var(--text-primary);">No activities yet.</h3>
                            <p class="text-sm text-muted">Check back later for upcoming events</p>
    </div>
    <?php endif; ?>
    </div>
  </div>
</section>

        <!-- Book with confidence -->
        <section class="border-y border-border bg-surface/30 px-4 py-8 md:py-12">
            <div class="container mx-auto max-w-6xl">
                <div class="mb-6 text-center">
                    <h2 class="mb-2 text-xl font-bold md:text-2xl" style="color: var(--text-primary);">Book with Confidence</h2>
                    <p class="text-sm text-muted md:text-base">Your safety and trust are our priority</p>
    </div>
                <div class="grid gap-6 md:grid-cols-3">
                    <?php foreach ($book_confidence as $item): ?>
                        <div class="flex flex-col items-center rounded-xl bg-card p-6 text-center shadow-sm">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-accent/10">
                                <?php if ($item['icon'] === 'fa-star'): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="h-6 w-6 text-accent">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                        <polyline points="22 4 12 14.01 9 11.01" />
                                    </svg>
                                <?php elseif ($item['icon'] === 'fa-shield-heart'): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="h-6 w-6 text-accent">
                                        <path
                                            d="M20 13c0 5-3.5 7.5-7.66 8.94a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                                    </svg>
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="h-6 w-6 text-accent">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                <?php endif; ?>
            </div>
                            <h3 class="mb-2 font-semibold" style="color: var(--text-primary);"><?php echo $item['title']; ?></h3>
                            <p class="text-sm text-muted"><?php echo $item['desc']; ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

        <!-- Happening soon -->
        <section id="happening" class="px-4 py-6 md:py-10">
            <div class="container mx-auto max-w-6xl">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold md:text-2xl" style="color: var(--text-primary);">Happening Soon</h2>
                        <p class="text-sm text-muted">Don't miss out on these experiences</p>
        </div>
                    <a href="public/search.php?type=activities"
                        class="flex items-center gap-1 text-sm text-muted" style="color: var(--text-secondary); transition: color 0.2s;" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">
                        View all
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
        </div>
                <div
                    class="scrollbar-hide -mx-4 flex gap-4 overflow-x-auto px-4 md:mx-0 md:grid md:grid-cols-2 md:overflow-visible md:px-0 lg:grid-cols-4">
                    <?php if (empty($happening_soon)): ?>
                        <div class="col-span-full flex flex-col items-center justify-center py-12 text-center">
                            <div class="mb-4 rounded-full bg-muted p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="h-8 w-8 text-muted">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
      </div>
                            <h3 class="mb-1 font-semibold" style="color: var(--text-primary);">No upcoming events.</h3>
                            <p class="text-sm text-muted">Check back later for new activities</p>
        </div>
                    <?php else: ?>
                        <?php foreach ($happening_soon as $event):
                            $photos = json_decode($event['photos_json'] ?? '[]', true);
                            $photo = $photos[0] ?? 'images/portfolio/01.jpg';
                            if (strpos($photo, '../') === 0) {
                                $photo = substr($photo, 3);
                            }
                            $date_display = date('j M', strtotime($event['start_at'] ?? $event['created_at']));
                            $activity_type_display = ucfirst(str_replace('_', ' ', $event['activity_type'] ?? 'Activity'));
                            $price_display = $event['is_free'] ? 'FREE' : 'GH₵' . number_format($event['price_min'], 0);
                            ?>
                            <a href="public/search.php?type=activities&id=<?php echo $event['activity_id']; ?>"
                                class="group relative flex w-72 shrink-0 flex-col overflow-hidden rounded-xl bg-card transition-all hover:shadow-lg md:w-auto">
                                <div class="relative aspect-[4/3] overflow-hidden bg-muted">
                                    <img src="<?php echo htmlspecialchars($photo); ?>"
                                        alt="<?php echo htmlspecialchars($event['title']); ?>"
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    <span
                                        class="absolute bottom-2 left-2 flex items-center gap-1 rounded border border-border bg-background/90 px-2 py-1 text-xs backdrop-blur-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="h-3 w-3">
                                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg>
                                        <?php echo $date_display; ?>
                                    </span>
        </div>
                                <div class="flex flex-1 flex-col p-3">
                                    <div class="mb-1 flex items-center gap-2 text-xs text-muted">
                                        <span
                                            class="font-medium" style="color: var(--text-secondary);"><?php echo htmlspecialchars($activity_type_display); ?></span>
                                        <span>•</span>
                                        <span class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" class="h-3 w-3">
                                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                                <circle cx="12" cy="10" r="3" />
                                            </svg>
                                            <?php echo htmlspecialchars($event['location_text']); ?>
                                        </span>
        </div>
                                    <h3
                                        class="mb-1 line-clamp-1 text-base font-semibold transition-colors group-hover:text-accent" style="color: var(--text-primary);">
                                        <?php echo htmlspecialchars($event['title']); ?>
                                    </h3>
                                    <div class="mt-auto flex items-end justify-between pt-2">
                                        <div>
                                            <span
                                                class="text-lg font-bold" style="color: var(--text-primary);"><?php echo $price_display; ?></span>
                                            <?php if (!$event['is_free']): ?>
                                                <span class="text-sm text-muted"> per person</span>
                                            <?php endif; ?>
      </div>
                                        <?php if ($event['capacity']): ?>
                                            <span
                                                class="rounded border border-border bg-surface px-2 py-1 text-xs text-muted"><?php echo $event['capacity']; ?>
                                                spots</span>
                                        <?php endif; ?>
    </div>
  </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
    </div>
  </div>
</section>

        <!-- CTA -->
        <section class="px-4 py-12 md:py-16">
            <div class="mx-auto max-w-4xl">
                <div
                    class="relative overflow-hidden rounded-2xl border border-[rgba(39,39,42,0.7)] bg-gradient-to-br from-[#FF6B35] via-[#ff8c66] to-[#ff5518] p-8 text-center md:p-12">
                    <div class="relative z-10">
                        <h2 class="text-2xl font-bold md:text-3xl" style="color: var(--text-primary);">Ready to list your venue?</h2>
                        <p class="mx-auto mt-3 max-w-2xl text-sm md:text-base" style="color: var(--text-primary); opacity: 0.9;">
                            Join Go Outside and reach thousands of verified guests. Manage bookings, payments and
                            reviews in one place.
                        </p>
                        <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                            <a href="public/register.php"
                                class="inline-flex items-center justify-center rounded-lg px-6 py-3 text-sm font-semibold transition-all hover:shadow-lg" style="background-color: var(--text-primary); color: var(--bg-primary);" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                Become a host
                            </a>
                            <a href="public/login.php"
                                class="inline-flex items-center justify-center rounded-lg border-2 px-6 py-3 text-sm font-semibold backdrop-blur-sm transition-all" style="border-color: rgba(255, 255, 255, 0.3); background-color: rgba(255, 255, 255, 0.1); color: var(--text-primary);" onmouseover="this.style.backgroundColor='rgba(255, 255, 255, 0.2)'; this.style.borderColor='rgba(255, 255, 255, 0.5)'" onmouseout="this.style.backgroundColor='rgba(255, 255, 255, 0.1)'; this.style.borderColor='rgba(255, 255, 255, 0.3)'">
                                Sign in
                            </a>
        </div>
      </div>
    </div>
  </div>
</section>
    </main>

    <footer class="border-t border-border px-4 py-6 text-center text-xs text-muted">
        <p>© <?php echo date('Y'); ?> Go Outside Ghana. Crafted for the community.</p>
</footer>

    <script>
        const tabButtons = document.querySelectorAll('.discover-tab-btn');
        const venuesPanel = document.getElementById('discover-venues');
        const activitiesPanel = document.getElementById('discover-activities');
        const filterButtons = document.querySelectorAll('.discover-filter');
        const discoverCards = document.querySelectorAll('.discover-card');
        let currentTab = 'venues'; // Track current tab
        
        // Map activity types to filter categories
        function mapActivityTypeToFilter(activityType) {
            const mapping = {
                'workshop': 'workshop',
                'class': 'workshop',
                'tour': 'adventure',
                'food': 'food',
                'adventure': 'adventure',
                'concert': 'concert',
                'sports': 'sports',
                'festival': 'festival',
                'popup': 'food',
                'game_night': 'sports',
                'meetup': 'adventure',
                'nightlife': 'concert',
            };
            return mapping[activityType?.toLowerCase()] || 'all';
        }

        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                tabButtons.forEach(b => {
                    b.classList.remove('shadow-sm');
                    b.style.backgroundColor = 'transparent';
                    b.style.color = 'var(--text-secondary)';
                });
                // Use a darker background for selected tab (better contrast)
                const isLight = document.documentElement.getAttribute('data-theme') === 'light';
                btn.style.backgroundColor = isLight ? 'rgba(229, 229, 229, 0.95)' : 'rgba(10, 10, 10, 0.95)';
                btn.style.color = 'var(--text-primary)';
                btn.classList.add('shadow-sm');
                const tab = btn.dataset.discoverTab;
                currentTab = tab;
                
                // Show/hide panels
                if (tab === 'activities') {
                    venuesPanel.classList.add('hidden');
                    activitiesPanel.classList.remove('hidden');
                    document.getElementById('venue-filters').classList.add('hidden');
                    document.getElementById('activity-filters').classList.remove('hidden');
                } else {
                    activitiesPanel.classList.add('hidden');
                    venuesPanel.classList.remove('hidden');
                    document.getElementById('venue-filters').classList.remove('hidden');
                    document.getElementById('activity-filters').classList.add('hidden');
                }
                
                // Reset filters when switching tabs
                filterButtons.forEach(b => {
                    b.style.backgroundColor = 'var(--bg-secondary)';
                    b.style.color = 'var(--text-secondary)';
                    b.style.borderColor = 'var(--border-color)';
                });
                document.querySelectorAll('.activity-filter-btn').forEach(b => {
                    b.style.backgroundColor = 'var(--bg-secondary)';
                    b.style.color = 'var(--text-secondary)';
                    b.style.borderColor = 'var(--border-color)';
                });
                // Show all cards when switching
                discoverCards.forEach(card => card.classList.remove('hidden'));
                updateActivityCards();
                activityCards.forEach(card => card.classList.remove('hidden'));
            });
        });
        
        // Map filter IDs to category slugs
        function mapFilterToCategory(filterId) {
            const mapping = {
                'all': 'all',
                'lounge': 'clubs-lounges',
                'beach': 'beach',
                'event': 'event-spaces',
                'club': 'clubs-lounges',
                'studio': 'studios',
                'restaurant': 'restaurants',
                'short-stay': 'short-stays'
            };
            return mapping[filterId] || filterId;
        }

        // Venue filter buttons
        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Only filter if we're on venues tab
                if (currentTab !== 'venues') return;
                
                filterButtons.forEach(b => {
                    b.style.backgroundColor = 'var(--bg-secondary)';
                    b.style.color = 'var(--text-secondary)';
                    b.style.borderColor = 'var(--border-color)';
                });
                button.style.backgroundColor = '#FF6B35';
                button.style.color = '#ffffff';
                button.style.borderColor = '#FF6B35';
                const filter = button.dataset.categoryFilter;
                const categorySlug = mapFilterToCategory(filter);
                
                discoverCards.forEach(card => {
                    if (filter === 'all' || card.dataset.category === categorySlug) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                });
            });
        });

        // Activity filter buttons
        const activityFilterButtons = document.querySelectorAll('.activity-filter-btn');
        let activityCards = document.querySelectorAll('.discover-card[data-activity-type]');
        
        // Update activity cards when tab is switched (in case they're loaded dynamically)
        function updateActivityCards() {
            activityCards = document.querySelectorAll('.discover-card[data-activity-type]');
        }
        
        activityFilterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Only filter if we're on activities tab
                if (currentTab !== 'activities') return;
                
                activityFilterButtons.forEach(b => {
                    b.style.backgroundColor = 'var(--bg-secondary)';
                    b.style.color = 'var(--text-secondary)';
                    b.style.borderColor = 'var(--border-color)';
                });
                button.style.backgroundColor = '#FF6B35';
                button.style.color = '#ffffff';
                button.style.borderColor = '#FF6B35';
                const filter = button.dataset.activityFilter;
                
                // Update activity cards list
                updateActivityCards();
                
                activityCards.forEach(card => {
                    const activityType = card.dataset.activityType;
                    const mappedCategory = mapActivityTypeToFilter(activityType);
                    
                    if (filter === 'all' || mappedCategory === filter) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                });
            });
        });


        // Save Venue Functionality
        document.addEventListener('click', function (e) {
            const saveBtn = e.target.closest('.save-venue-btn');
            if (saveBtn) {
                e.preventDefault();
                e.stopPropagation(); // Prevent card click

                const venueId = saveBtn.dataset.venueId;
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
                fetch('actions/toggle_saved_action.php', {
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
                            if (action === 'save') {
                                saveBtn.dataset.saved = 'false';
                                icon.setAttribute('fill', 'none');
                                icon.style.color = 'var(--text-primary)';
                            } else {
                                saveBtn.dataset.saved = 'true';
                                icon.setAttribute('fill', 'currentColor');
                                icon.style.color = '#FF6B35';
                            }

                            if (data.message.includes('login')) {
                                window.location.href = 'public/login.php';
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
                            icon.style.color = 'var(--text-primary)';
                        } else {
                            saveBtn.dataset.saved = 'true';
                            icon.setAttribute('fill', 'currentColor');
                            icon.style.color = '#FF6B35';
                        }
                    });
            }
        });
    </script>
</body>

</html>