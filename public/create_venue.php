<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/category_controller.php');
require_once(__DIR__ . '/../includes/site_nav.php');

// Check if logged in and is venue owner
require_login();

// Get all categories for the form
$categories = get_all_categories_ctr();
if ($categories === false)
    $categories = [];

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];
?>
<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Venue - Go Outside</title>
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
            --background: #0a0a0a;
            --foreground: #ffffff;
            --muted: #9b9ba1;
            --border: rgba(39, 39, 42, 0.7);
            --accent: #FF6B35;
            --card: #1a1a1a;
}

        [data-theme="light"] {
            --background: #ffffff;
            --foreground: #0a0a0a;
            --muted: #737373;
            --border: rgba(229, 229, 229, 0.8);
            --accent: #FF6B35;
            --card: #ffffff;
        }

        body {
            background: var(--background);
            color: var(--foreground);
            transition: background-color 0.3s ease, color 0.3s ease;
}

        .leaflet-container {
            background: var(--card) !important;
}

        .leaflet-popup-content-wrapper,
        .leaflet-popup-tip {
            background: var(--background);
            color: var(--foreground);
}

        .leaflet-control-zoom a {
            background-color: var(--card) !important;
            color: var(--foreground) !important;
            border-bottom: 1px solid var(--border) !important;
}
</style>
</head>

<body>
<?php render_site_nav(['base_path' => '../']); ?>

    <div class="min-h-screen" style="background-color: var(--background);">
        <!-- Header -->
        <header class="sticky top-0 z-50 border-b backdrop-blur"
            style="border-color: var(--border); background-color: var(--background); opacity: 0.95;">
            <div class="container flex h-16 items-center justify-between px-4">
                <div class="flex items-center gap-4">
                    <a href="owner_dashboard.php"
                        class="flex h-8 w-8 items-center justify-center rounded-lg transition"
                        style="color: var(--foreground);"
                        onmouseover="this.style.backgroundColor='var(--card)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 19-7-7 7-7" />
                            <path d="M19 12H5" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-lg font-semibold" style="color: var(--foreground);">List Your Venue</h1>
                        <p class="text-xs" style="color: var(--muted);" id="stepIndicator">Step <span id="currentStep">1</span> of
                            <span id="totalSteps">4</span></p>
                    </div>
                </div>
                <a href="owner_dashboard.php"
                    class="rounded-lg border px-3 py-1.5 text-sm font-medium transition"
                    style="border-color: var(--border); background-color: transparent; color: var(--foreground);"
                    onmouseover="this.style.backgroundColor='var(--card)'"
                    onmouseout="this.style.backgroundColor='transparent'">
                    Cancel
                </a>
            </div>
        </header>

        <!-- Progress Bar -->
        <div class="border-b" style="border-color: var(--border);">
            <div class="container px-4">
                <div class="flex">
                    <div class="h-1 flex-1" id="progress-1" style="background-color: var(--accent);"></div>
                    <div class="ml-1 h-1 flex-1" id="progress-2" style="background-color: var(--border);"></div>
                    <div class="ml-1 h-1 flex-1" id="progress-3" style="background-color: var(--border);"></div>
                    <div class="ml-1 h-1 flex-1" id="progress-4" style="background-color: var(--border);"></div>
                </div>
    </div>
</div>

        <main class="container max-w-2xl px-4 py-8">
        <!-- Alert Message -->
            <div id="alertMessage" class="mb-6"></div>

        <form id="createVenueForm">
                <!-- Step 1: Basic Information -->
                <div id="step-1" class="step-content">
                    <div class="rounded-xl border p-6 shadow-sm"
                        style="border-color: var(--border); background-color: var(--card);">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold" style="color: var(--foreground);">Basic Information</h3>
                            <p class="text-sm" style="color: var(--muted);">Tell us about your venue</p>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Venue Name *</label>
                                <input type="text" name="title" id="venueName"
                                    class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                               placeholder="e.g., The Garden Hall" required>
                                    <style>
                                        #venueName::placeholder {
                                            color: var(--muted);
                                        }
                                    </style>
                    </div>
                    
                            <div>
                                <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Category *</label>
                                <select name="cat_id" id="venueCategory"
                                    class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                    required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['cat_id']; ?>">
                                    <?php echo htmlspecialchars($category['cat_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                            <div>
                                <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Booking Type *</label>
                                <select name="booking_type" id="bookingType"
                                    class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                    required>
                                    <option value="rent">Rent (Exclusive Use)</option>
                                    <option value="reservation">Reservation (Non-exclusive)</option>
                                    <option value="ticket">Ticketed Event</option>
                                </select>
                                <p class="mt-1 text-xs" style="color: var(--muted);">
                                    <strong>Rent:</strong> Guests book the entire venue. Payment required.<br>
                                    <strong>Reservation:</strong> Guests reserve a spot (e.g., table). No upfront
                                    payment.<br>
                                    <strong>Ticket:</strong> Guests buy tickets for an event.
                                </p>
                    </div>
                    
                            <div>
                                <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Description *</label>
                                <textarea name="description" id="venueDescription" rows="4"
                                    class="flex w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                    placeholder="Describe your venue - what makes it special?" required></textarea>
                                <style>
                                    #venueDescription::placeholder {
                                        color: var(--muted);
                                    }
                                </style>
                                <p class="mt-1 text-xs" style="color: var(--muted);">
                                    Pro tip: Mention what guests will experience and what makes your venue unique
                                </p>
            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Capacity</label>
                                <input type="number" name="capacity" id="venueCapacity"
                                    class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                    placeholder="Maximum number of guests">
                                <style>
                                    #venueCapacity::placeholder {
                                        color: var(--muted);
                                    }
                                </style>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 2: Location -->
                <div id="step-2" class="step-content hidden">
                    <div class="rounded-xl border p-6 shadow-sm"
                        style="border-color: var(--border); background-color: var(--card);">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold" style="color: var(--foreground);">Location</h3>
                            <p class="text-sm" style="color: var(--muted);">Set your venue's location</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Address *</label>
                                <div class="relative">
                                    <input type="text" id="addressSearch"
                                        class="flex h-10 w-full rounded-lg border px-3 py-2 pr-24 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                        style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                        placeholder="Search for an address or location">
                                    <style>
                                        #addressSearch::placeholder {
                                            color: var(--muted);
                                        }
                                    </style>
                                    <button type="button" onclick="searchAddress()"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg px-3 py-1.5 text-xs font-medium transition"
                                        style="background-color: var(--accent); color: white;"
                                        onmouseover="this.style.backgroundColor='#ff8c66'"
                                        onmouseout="this.style.backgroundColor='var(--accent)'">
                                        Search
                                    </button>
                                </div>
                                <input type="text" name="location_text" id="location_text"
                                    class="mt-2 flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                    placeholder="e.g., 123 Main Street, Accra" required>
                                <style>
                                    #location_text::placeholder {
                                        color: var(--muted);
                                    }
                                </style>
                                <p class="mt-1 text-xs" style="color: var(--muted);">Enter the full address or search and select from
                                    map</p>
                            </div>

                            <div
                                class="rounded-lg border p-3 text-sm"
                                style="border-color: var(--border); background-color: var(--background); color: var(--muted);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mb-1 inline h-4 w-4"
                                    style="color: var(--accent);"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="16" x2="12" y2="12" />
                                    <line x1="12" y1="8" x2="12.01" y2="8" />
                                </svg>
                                <strong style="color: var(--foreground);">How to set location:</strong> Search for an address above, or
                                click directly on the map below to set your venue's exact location.
                </div>
                
                <!-- Interactive Map Picker -->
                            <div id="locationMapPicker"
                                class="h-96 w-full overflow-hidden rounded-lg border"
                                style="border-color: var(--border);"></div>
                
                            <!-- Coordinates Display -->
                <input type="hidden" name="lat" id="selectedLat" value="">
                <input type="hidden" name="lng" id="selectedLng" value="">
                
                            <div id="coordinatesDisplay"
                                class="hidden rounded-lg border border-green-500/30 bg-green-500/10 p-3 text-sm text-green-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mb-1 inline h-4 w-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                    Location set: <strong id="displayCoords"></strong>
                </div>
            </div>
                    </div>
                </div>

                <!-- Step 3: Pricing & Amenities -->
                <div id="step-3" class="step-content hidden">
                    <div class="space-y-6">
                        <div class="rounded-xl border p-6 shadow-sm"
                            style="border-color: var(--border); background-color: var(--card);">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold" style="color: var(--foreground);">Pricing</h3>
                                <p class="text-sm" style="color: var(--muted);">Set your pricing structure</p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Price per Hour (GH₵)
                                        *</label>
                                    <input type="number" name="price_per_hour" id="venuePrice" step="0.01"
                                        class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                        style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                        placeholder="0.00" required>
                                    <style>
                                        #venuePrice::placeholder {
                                            color: var(--muted);
                                        }
                                    </style>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Minimum Booking
                                        Hours</label>
                                    <input type="number" name="min_booking_hours" id="minHours" value="1" min="1"
                                        class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                        style="border-color: var(--border); background-color: var(--background); color: var(--foreground);">
                                    <style>
                                        #minHours::placeholder {
                                            color: var(--muted);
                                        }
                                    </style>
                    </div>
                </div>
            </div>

                        <div class="rounded-xl border p-6 shadow-sm"
                            style="border-color: var(--border); background-color: var(--card);">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold" style="color: var(--foreground);">Amenities</h3>
                                <p class="text-sm" style="color: var(--muted);">Select the amenities your venue offers</p>
                            </div>
                
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label
                                    class="flex items-center gap-3 rounded-lg border p-3 cursor-pointer transition"
                                    style="border-color: var(--border); background-color: var(--background);"
                                    onmouseover="this.style.borderColor='var(--accent)'"
                                    onmouseout="this.style.borderColor='var(--border)'">
                                    <input type="checkbox" name="amenities[]" value="WiFi"
                                        class="h-4 w-4 rounded"
                                        style="border-color: var(--border); background-color: var(--background); accent-color: var(--accent);">
                                    <span class="text-sm" style="color: var(--foreground);">WiFi</span>
                    </label>
                                <label
                                    class="flex items-center gap-3 rounded-lg border p-3 cursor-pointer transition"
                                    style="border-color: var(--border); background-color: var(--background);"
                                    onmouseover="this.style.borderColor='var(--accent)'"
                                    onmouseout="this.style.borderColor='var(--border)'">
                                    <input type="checkbox" name="amenities[]" value="Parking"
                                        class="h-4 w-4 rounded"
                                        style="border-color: var(--border); background-color: var(--background); accent-color: var(--accent);">
                                    <span class="text-sm" style="color: var(--foreground);">Parking</span>
                    </label>
                                <label
                                    class="flex items-center gap-3 rounded-lg border p-3 cursor-pointer transition"
                                    style="border-color: var(--border); background-color: var(--background);"
                                    onmouseover="this.style.borderColor='var(--accent)'"
                                    onmouseout="this.style.borderColor='var(--border)'">
                                    <input type="checkbox" name="amenities[]" value="Air Conditioning"
                                        class="h-4 w-4 rounded"
                                        style="border-color: var(--border); background-color: var(--background); accent-color: var(--accent);">
                                    <span class="text-sm" style="color: var(--foreground);">Air Conditioning</span>
                    </label>
                                <label
                                    class="flex items-center gap-3 rounded-lg border p-3 cursor-pointer transition"
                                    style="border-color: var(--border); background-color: var(--background);"
                                    onmouseover="this.style.borderColor='var(--accent)'"
                                    onmouseout="this.style.borderColor='var(--border)'">
                                    <input type="checkbox" name="amenities[]" value="Sound System"
                                        class="h-4 w-4 rounded"
                                        style="border-color: var(--border); background-color: var(--background); accent-color: var(--accent);">
                                    <span class="text-sm" style="color: var(--foreground);">Sound System</span>
                    </label>
                                <label
                                    class="flex items-center gap-3 rounded-lg border p-3 cursor-pointer transition"
                                    style="border-color: var(--border); background-color: var(--background);"
                                    onmouseover="this.style.borderColor='var(--accent)'"
                                    onmouseout="this.style.borderColor='var(--border)'">
                                    <input type="checkbox" name="amenities[]" value="Catering"
                                        class="h-4 w-4 rounded"
                                        style="border-color: var(--border); background-color: var(--background); accent-color: var(--accent);">
                                    <span class="text-sm" style="color: var(--foreground);">Catering Available</span>
                    </label>
                                <label
                                    class="flex items-center gap-3 rounded-lg border p-3 cursor-pointer transition"
                                    style="border-color: var(--border); background-color: var(--background);"
                                    onmouseover="this.style.borderColor='var(--accent)'"
                                    onmouseout="this.style.borderColor='var(--border)'">
                                    <input type="checkbox" name="amenities[]" value="Projector"
                                        class="h-4 w-4 rounded"
                                        style="border-color: var(--border); background-color: var(--background); accent-color: var(--accent);">
                                    <span class="text-sm" style="color: var(--foreground);">Projector/Screen</span>
                    </label>
                </div>
            </div>
                    </div>
                </div>

                <!-- Step 4: Photos & Review -->
                <div id="step-4" class="step-content hidden">
                    <div class="space-y-6">
                        <div class="rounded-xl border p-6 shadow-sm"
                            style="border-color: var(--border); background-color: var(--card);">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold" style="color: var(--foreground);">Photos</h3>
                                <p class="text-sm" style="color: var(--muted);">Add photos to showcase your venue</p>
                            </div>

                            <div onclick="document.getElementById('photoUpload').click()"
                                class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed p-8 transition"
                                style="border-color: var(--border); background-color: var(--background);"
                                onmouseover="this.style.borderColor='var(--accent)'; this.style.backgroundColor='var(--card)'"
                                onmouseout="this.style.borderColor='var(--border)'; this.style.backgroundColor='var(--background)'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-12 w-12"
                                    style="color: var(--accent);"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <polyline points="21 15 16 10 5 21" />
                                </svg>
                                <h4 class="mb-1 text-base font-medium" style="color: var(--foreground);">Upload Venue Photos</h4>
                                <p class="text-sm" style="color: var(--muted);">Click to browse or drag and drop</p>
                </div>
                <input type="file" id="photoUpload" name="photos[]" multiple accept="image/*" 
                                class="hidden">
                            <div id="photoPreview" class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3"></div>
                            <p class="mt-4 text-sm" style="color: var(--muted);">
                                Tip: Venues with high-quality photos get 2x more bookings
                            </p>
                        </div>

                        <div class="rounded-xl border p-6 shadow-sm"
                            style="border-color: var(--border); background-color: var(--card);">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold" style="color: var(--foreground);">Review Your Listing</h3>
                                <p class="text-sm" style="color: var(--muted);">Make sure everything looks good before publishing</p>
                            </div>

                            <div class="rounded-lg border p-4"
                                style="border-color: var(--border); background-color: var(--background);">
                                <div class="mb-4 flex items-start justify-between">
                                    <div>
                                        <h3 class="font-semibold" id="reviewName" style="color: var(--foreground);">Your Venue Name</h3>
                                        <p class="text-sm" id="reviewCategory" style="color: var(--muted);">Category • Location</p>
                                    </div>
                                    <span
                                        class="inline-flex items-center rounded border px-2.5 py-1 text-xs font-semibold"
                                        style="border-color: var(--border); background-color: transparent; color: var(--muted);">
                                        Draft
                                    </span>
            </div>

                                <div class="my-4 h-px" style="background-color: var(--border);"></div>

                                <div class="grid gap-4 text-sm sm:grid-cols-2">
                                    <div>
                                        <span style="color: var(--muted);">Price:</span>
                                        <p class="font-medium" id="reviewPrice" style="color: var(--foreground);">GH₵0 per hour</p>
                                    </div>
                                    <div>
                                        <span style="color: var(--muted);">Capacity:</span>
                                        <p class="font-medium" id="reviewCapacity" style="color: var(--foreground);">Not set</p>
                                    </div>
                                    <div>
                                        <span style="color: var(--muted);">Min Hours:</span>
                                        <p class="font-medium" id="reviewMinHours" style="color: var(--foreground);">1 hour</p>
                                    </div>
                                    <div>
                                        <span style="color: var(--muted);">Location:</span>
                                        <p class="font-medium" id="reviewLocation" style="color: var(--foreground);">Not set</p>
                                    </div>
                                </div>
                    </div>
                    
                            <div class="mt-6 rounded-lg border p-4"
                                style="border-color: rgba(255, 107, 53, 0.2); background-color: rgba(255, 107, 53, 0.05);">
                                <div class="flex gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                                        style="color: var(--accent);"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10" />
                                        <line x1="12" y1="16" x2="12" y2="12" />
                                        <line x1="12" y1="8" x2="12.01" y2="8" />
                                    </svg>
                                    <div class="text-sm">
                                        <p class="font-medium" style="color: var(--foreground);">What happens next?</p>
                                        <p style="color: var(--muted);">
                                            Your venue will be reviewed by our team within 24 hours. Once approved, it
                                            will be visible to guests on Go Outside.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information (Hidden, included in form) -->
                <div class="hidden">
                    <input type="tel" name="contact_phone" id="contactPhone" placeholder="+233 XX XXX XXXX">
                    <input type="email" name="contact_email" id="contactEmail" placeholder="venue@example.com">
            </div>

                <!-- Navigation Buttons -->
                <div class="mt-8 flex justify-between">
                    <button type="button" id="backBtn" onclick="previousStep()"
                        class="hidden inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md border px-4 py-2 text-sm font-medium shadow-sm transition-all disabled:pointer-events-none disabled:opacity-50 outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B35]/50 focus-visible:ring-offset-2"
                        style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                        onmouseover="this.style.backgroundColor='var(--card)'"
                        onmouseout="this.style.backgroundColor='var(--background)'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 19-7-7 7-7" />
                            <path d="M19 12H5" />
                        </svg>
                        Back
                    </button>
                    <div id="backBtnPlaceholder"></div>

                    <button type="button" id="nextBtn" onclick="nextStep()"
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md px-4 py-2 text-sm font-medium transition-colors disabled:pointer-events-none disabled:opacity-50 outline-none focus-visible:ring-2 focus-visible:ring-[#FF6B35]/50 focus-visible:ring-offset-2"
                        style="background-color: var(--accent); color: white;"
                        onmouseover="this.style.backgroundColor='#ff8c66'"
                        onmouseout="this.style.backgroundColor='var(--accent)'">
                        <span id="nextBtnText">Continue</span>
                        <svg xmlns="http://www.w3.org/2000/svg" id="nextBtnIcon"
                            class="h-4 w-4 shrink-0 pointer-events-none" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14" />
                            <path d="m12 5 7 7-7 7" />
                        </svg>
                </button>
            </div>
        </form>
        </main>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
        let currentStep = 1;
        const totalSteps = 4;

        // Update step indicator and progress bar
        function updateStepIndicator() {
            document.getElementById('currentStep').textContent = currentStep;
            document.getElementById('totalSteps').textContent = totalSteps;

            // Update progress bars
            for (let i = 1; i <= totalSteps; i++) {
                const progressBar = document.getElementById('progress-' + i);
                if (i < currentStep) {
                    progressBar.style.backgroundColor = 'var(--accent)';
                } else if (i === currentStep) {
                    progressBar.style.backgroundColor = 'var(--accent)';
                } else {
                    progressBar.style.backgroundColor = 'var(--border)';
                }
            }

            // Show/hide steps
            for (let i = 1; i <= totalSteps; i++) {
                const stepContent = document.getElementById('step-' + i);
                if (i === currentStep) {
                    stepContent.classList.remove('hidden');
                } else {
                    stepContent.classList.add('hidden');
                }
            }

            // Update navigation buttons
            const backBtn = document.getElementById('backBtn');
            const nextBtn = document.getElementById('nextBtn');

            if (currentStep > 1) {
                backBtn.classList.remove('hidden');
                document.getElementById('backBtnPlaceholder').classList.add('hidden');
            } else {
                backBtn.classList.add('hidden');
                document.getElementById('backBtnPlaceholder').classList.remove('hidden');
            }

            const nextBtnText = document.getElementById('nextBtnText');
            const nextBtnIcon = document.getElementById('nextBtnIcon');

            if (currentStep < totalSteps) {
                // Continue button - text first, icon after
                nextBtnText.textContent = 'Continue';
                nextBtnIcon.innerHTML = '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>';
                nextBtnIcon.style.order = '2'; // Icon after text
                nextBtnText.style.order = '1'; // Text first
                nextBtn.setAttribute('onclick', 'nextStep()');
                nextBtn.type = 'button';
                nextBtn.disabled = false;
                nextBtn.className = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md px-4 py-2 text-sm font-medium transition-colors disabled:pointer-events-none disabled:opacity-50 outline-none focus-visible:ring-2 focus-visible:ring-offset-2';
                nextBtn.style.backgroundColor = 'var(--accent)';
                nextBtn.style.color = '#ffffff';
                nextBtn.onmouseover = function() { this.style.backgroundColor = 'var(--accent-hover)'; };
                nextBtn.onmouseout = function() { this.style.backgroundColor = 'var(--accent)'; };
            } else {
                // Submit button - icon first, text after
                nextBtnText.textContent = 'Submit for Review';
                nextBtnIcon.innerHTML = '<polyline points="20 6 9 17 4 12"/>';
                nextBtnIcon.style.order = '1'; // Icon first
                nextBtnText.style.order = '2'; // Text after
                nextBtn.setAttribute('onclick', 'submitForm(event)');
                nextBtn.type = 'button';
                nextBtn.disabled = false;
                nextBtn.className = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md px-4 py-2 text-sm font-medium transition-colors disabled:pointer-events-none disabled:opacity-50 outline-none focus-visible:ring-2 focus-visible:ring-offset-2';
                nextBtn.style.backgroundColor = 'var(--accent)';
                nextBtn.style.color = '#ffffff';
                nextBtn.onmouseover = function() { this.style.backgroundColor = 'var(--accent-hover)'; };
                nextBtn.onmouseout = function() { this.style.backgroundColor = 'var(--accent)'; };
            }

            // Update review section if on step 4
            if (currentStep === 4) {
                updateReviewSection();
            }
        }

        function nextStep() {
            // Validate current step
            if (currentStep === 1) {
                if (!document.getElementById('venueName').value || !document.getElementById('venueCategory').value || !document.getElementById('venueDescription').value) {
                    showAlert('Please fill in all required fields', 'danger');
                    return;
                }
            } else if (currentStep === 2) {
                if (!document.getElementById('location_text').value) {
                    showAlert('Please set a location', 'danger');
                    return;
                }
            } else if (currentStep === 3) {
                if (!document.getElementById('venuePrice').value) {
                    showAlert('Please set a price', 'danger');
                    return;
                }
            }

            if (currentStep < totalSteps) {
                currentStep++;
                updateStepIndicator();

                // Initialize map on step 2
                if (currentStep === 2 && !locationMap) {
                    initializeMap();
                }
            }
        }

        function previousStep() {
            if (currentStep > 1) {
                currentStep--;
                updateStepIndicator();
            }
        }

        function updateReviewSection() {
            const name = document.getElementById('venueName').value || 'Your Venue Name';
            const category = document.getElementById('venueCategory');
            const categoryText = category.options[category.selectedIndex].text || 'Category';
            const location = document.getElementById('location_text').value || 'Not set';
            const price = document.getElementById('venuePrice').value || '0';
            const capacity = document.getElementById('venueCapacity').value || 'Not set';
            const minHours = document.getElementById('minHours').value || '1';

            document.getElementById('reviewName').textContent = name;
            document.getElementById('reviewCategory').textContent = categoryText + ' • ' + location;
            document.getElementById('reviewPrice').textContent = 'GH₵' + parseFloat(price).toFixed(2) + ' per hour';
            document.getElementById('reviewCapacity').textContent = capacity === 'Not set' ? 'Not set' : capacity + ' guests';
            document.getElementById('reviewMinHours').textContent = minHours + ' hour' + (parseInt(minHours) > 1 ? 's' : '');
            document.getElementById('reviewLocation').textContent = location;
        }

// Photo preview
        $('#photoUpload').on('change', function () {
    const files = this.files;
    const preview = $('#photoPreview');
    preview.empty();
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        
                reader.onload = function (e) {
            const img = $('<div>').css({
                'background-image': 'url(' + e.target.result + ')',
                'background-size': 'cover',
                'background-position': 'center',
                'height': '150px',
                'border-radius': '8px',
                        'border': '2px solid',
                        'border-color': 'var(--border)'
            });
            preview.append(img);
        };
        
        reader.readAsDataURL(file);
    }
});

// Show alert message
function showAlert(message, type) {
    const alertDiv = $('#alertMessage');
            alertDiv.removeClass('hidden');
            alertDiv.removeClass('bg-green-500/10 border-green-500/30 text-green-500 bg-red-500/10 border-red-500/30 text-red-500');

            if (type === 'success') {
                alertDiv.addClass('rounded-lg border border-green-500/30 bg-green-500/10 p-4 text-sm text-green-500');
            } else {
                alertDiv.addClass('rounded-lg border border-red-500/30 bg-red-500/10 p-4 text-red-500');
            }

            alertDiv.html('<svg xmlns="http://www.w3.org/2000/svg" class="mr-2 inline h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                (type === 'success' ? '<polyline points="20 6 9 17 4 12"/>' : '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>') +
                '</svg>' + message);
    alertDiv.show();
    
    $('html, body').animate({ scrollTop: 0 }, 'slow');
    
            setTimeout(function () {
        alertDiv.fadeOut();
    }, 5000);
}

// Form submission
        function submitForm(e) {
    e.preventDefault();
    
            const formData = new FormData(document.getElementById('createVenueForm'));
            const submitBtn = $('#nextBtn');
    
    // Disable button
            submitBtn.prop('disabled', true).html('<svg class="mr-2 inline h-4 w-4 animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Submitting...');
    
    $.ajax({
        url: '../actions/venue_create_action.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
                success: function (result) {
            if (result.success) {
                showAlert(result.message, 'success');
                        setTimeout(function () {
                    window.location.href = 'owner_dashboard.php';
                }, 2000);
            } else {
                showAlert(result.message, 'danger');
                        submitBtn.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" class="mr-2 inline h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Submit for Review');
            }
        },
                error: function () {
            showAlert('An error occurred. Please try again.', 'danger');
                    submitBtn.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" class="mr-2 inline h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Submit for Review');
        }
    });
        }

// Map Picker Functionality
let locationMap;
let locationMarker;
let selectedLat = null;
let selectedLng = null;

// Initialize map (default to Accra, Ghana)
        function initializeMap() {
            if (locationMap) return; // Already initialized

    locationMap = L.map('locationMapPicker', {
        zoomControl: true,
        scrollWheelZoom: true
            }).setView([5.6037, -0.1870], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(locationMap);
    
    var orangeIcon = L.icon({
                iconUrl: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="48" viewBox="0 0 32 48"><path fill="%23FF6B35" d="M16 0C7.163 0 0 7.163 0 16c0 11.5 16 32 16 32s16-20.5 16-32C32 7.163 24.837 0 16 0zm0 22c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z"/></svg>',
        iconSize: [32, 48],
        iconAnchor: [16, 48],
        popupAnchor: [0, -48]
    });
    
            locationMap.on('click', function (e) {
        setLocation(e.latlng.lat, e.latlng.lng);
    });
    
            $('#addressSearch').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            searchAddress();
        }
    });
        }

        // Search for address
function searchAddress() {
    const query = $('#addressSearch').val().trim();
    
    if (!query) {
        alert('Please enter an address to search');
        return;
    }
    
            $('.address-search-btn').html('<svg class="inline h-4 w-4 animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Searching...').prop('disabled', true);
    
    $.ajax({
        url: '../actions/geocode_address_action.php',
        method: 'GET',
                data: { q: query },
        dataType: 'json',
                timeout: 20000,
                success: function (response) {
            if (response.success && response.results && response.results.length > 0) {
                const result = response.results[0];
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);
                
                if (isNaN(lat) || isNaN(lng)) {
                            $('.address-search-btn').html('Search').prop('disabled', false);
                    alert('Invalid coordinates returned. Please try a different search or click on the map.');
                    return;
                }
                
                setLocation(lat, lng);
                        $('#location_text').val(result.display_name || query);
                
                const zoom = result.importance && result.importance > 0.7 ? 17 : 16;
                locationMap.setView([lat, lng], zoom);
                
                        $('.address-search-btn').html('Search').prop('disabled', false);
            } else {
                        $('.address-search-btn').html('Search').prop('disabled', false);
                        alert('Location not found. You can click directly on the map to set your venue location.');
            }
        },
                error: function () {
                    $('.address-search-btn').html('Search').prop('disabled', false);
                    alert('Search failed. You can click directly on the map to set your venue location.');
        }
    });
}

// Set location on map
function setLocation(lat, lng) {
    selectedLat = lat;
    selectedLng = lng;
    
    $('#selectedLat').val(lat);
    $('#selectedLng').val(lng);
    
    if (locationMarker) {
        locationMap.removeLayer(locationMarker);
    }
    
    var orangeIcon = L.icon({
                iconUrl: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="48" viewBox="0 0 32 48"><path fill="%23FF6B35" d="M16 0C7.163 0 0 7.163 0 16c0 11.5 16 32 16 32s16-20.5 16-32C32 7.163 24.837 0 16 0zm0 22c-3.314 0-6-2.686-6-6s2.686-6 6-6 6 2.686 6 6-2.686 6-6 6z"/></svg>',
        iconSize: [32, 48],
        iconAnchor: [16, 48],
        popupAnchor: [0, -48]
    });
    
            locationMarker = L.marker([lat, lng], { icon: orangeIcon }).addTo(locationMap);
    locationMarker.bindPopup('Venue Location').openPopup();
    
    $('#displayCoords').text(lat.toFixed(6) + ', ' + lng.toFixed(6));
            $('#coordinatesDisplay').removeClass('hidden');
}

        // Initialize on page load
        $(document).ready(function () {
            updateStepIndicator();
        });
</script>
</body>

</html>