<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../includes/site_nav.php');

// Check if logged in
require_login();

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];

// Get user's venues (optional - for linking activity to venue)
$my_venues = get_venues_by_owner_ctr($customer_id);
if ($my_venues === false) $my_venues = [];

// Activity types
$activity_types = [
    'workshop' => 'Workshop',
    'class' => 'Class',
    'tour' => 'Tour',
    'food' => 'Food Experience',
    'adventure' => 'Adventure',
    'concert' => 'Concert',
    'sports' => 'Sports',
    'festival' => 'Festival',
    'popup' => 'Pop-up Event',
    'game_night' => 'Game Night',
    'meetup' => 'Meetup',
    'nightlife' => 'Nightlife'
];
?>
<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Activity - Go Outside</title>
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
                        <h1 class="text-lg font-semibold" style="color: var(--foreground);">Create Activity</h1>
                        <p class="text-xs" style="color: var(--muted);">List a new activity for guests to RSVP</p>
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

        <main class="container max-w-2xl px-4 py-8">
            <!-- Alert Message -->
            <div id="alertMessage" class="mb-6"></div>

            <form id="createActivityForm" enctype="multipart/form-data">
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div class="rounded-xl border p-6 shadow-sm"
                        style="border-color: var(--border); background-color: var(--card);">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold" style="color: var(--foreground);">Basic Information</h3>
                            <p class="text-sm" style="color: var(--muted);">Tell us about your activity</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Activity Title *</label>
                                <input type="text" name="title" id="activityTitle" required
                                    class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                    placeholder="e.g., Accra Food Tour">
                            </div>
                            
                            <div>
                                <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Activity Type *</label>
                                <select name="activity_type" id="activityType" required
                                    class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);">
                                    <option value="">Select activity type</option>
                                    <?php foreach ($activity_types as $key => $label): ?>
                                        <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Description *</label>
                                <textarea name="description" id="activityDescription" rows="4" required
                                    class="flex w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                    placeholder="Describe your activity - what will participants experience?"></textarea>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Link to Venue (Optional)</label>
                                <select name="venue_id" id="venueId"
                                    class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);">
                                    <option value="">No venue (standalone activity)</option>
                                    <?php foreach ($my_venues as $venue): ?>
                                        <option value="<?php echo $venue['venue_id']; ?>">
                                            <?php echo htmlspecialchars($venue['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Type -->
                    <div class="rounded-xl border p-6 shadow-sm"
                        style="border-color: var(--border); background-color: var(--card);">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold" style="color: var(--foreground);">Activity Type</h3>
                            <p class="text-sm" style="color: var(--muted);">Is this a one-time or recurring activity?</p>
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-center gap-3 rounded-lg border p-4 cursor-pointer transition"
                                style="border-color: var(--border); background-color: var(--background);"
                                onmouseover="this.style.borderColor='var(--accent)'"
                                onmouseout="this.style.borderColor='var(--border)'">
                                <input type="radio" name="recurrence_type" value="none" checked
                                    class="h-4 w-4"
                                    style="accent-color: var(--accent);">
                                <div>
                                    <div class="font-medium" style="color: var(--foreground);">One-Time Activity</div>
                                    <div class="text-xs" style="color: var(--muted);">A single event that happens once</div>
                                </div>
                            </label>
                            
                            <label class="flex items-center gap-3 rounded-lg border p-4 cursor-pointer transition"
                                style="border-color: var(--border); background-color: var(--background);"
                                onmouseover="this.style.borderColor='var(--accent)'"
                                onmouseout="this.style.borderColor='var(--border)'">
                                <input type="radio" name="recurrence_type" value="recurring"
                                    class="h-4 w-4"
                                    style="accent-color: var(--accent);">
                                <div>
                                    <div class="font-medium" style="color: var(--foreground);">Recurring Activity</div>
                                    <div class="text-xs" style="color: var(--muted);">Happens regularly (e.g., weekly classes, monthly meetups)</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Date & Time -->
                    <div class="rounded-xl border p-6 shadow-sm"
                        style="border-color: var(--border); background-color: var(--card);">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold" style="color: var(--foreground);">Date & Time</h3>
                            <p class="text-sm" id="dateTimeDescription" style="color: var(--muted);">When will your activity take place?</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium" id="startDateLabel" style="color: var(--foreground);">Event Date *</label>
                                <input type="date" name="start_date" id="startDate" required
                                    class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                    min="<?php echo date('Y-m-d'); ?>">
                                <p class="mt-1 text-xs" id="startDateHelp" style="color: var(--muted);">The date when your activity will occur</p>
                            </div>
                            
                            <div>
                                <label class="mb-2 block text-sm font-medium" id="startTimeLabel" style="color: var(--foreground);">Event Time *</label>
                                <input type="time" name="start_time" id="startTime" required
                                    class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);">
                                <p class="mt-1 text-xs" id="startTimeHelp" style="color: var(--muted);">The time when your activity starts</p>
                            </div>
                            
                            <div id="endDateContainer">
                                <label class="mb-2 block text-sm font-medium" id="endDateLabel" style="color: var(--foreground);">End Date (Optional)</label>
                                <input type="date" name="end_date" id="endDate"
                                    class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                    min="<?php echo date('Y-m-d'); ?>">
                                <p class="mt-1 text-xs" id="endDateHelp" style="color: var(--muted);">When the activity ends (for one-time events)</p>
                            </div>
                            
                            <div id="endTimeContainer">
                                <label class="mb-2 block text-sm font-medium" id="endTimeLabel" style="color: var(--foreground);">End Time (Optional)</label>
                                <input type="time" name="end_time" id="endTime"
                                    class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);">
                                <p class="mt-1 text-xs" id="endTimeHelp" style="color: var(--muted);">The time when your activity ends</p>
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="rounded-xl border p-6 shadow-sm"
                        style="border-color: var(--border); background-color: var(--card);">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold" style="color: var(--foreground);">Location</h3>
                            <p class="text-sm" style="color: var(--muted);">Where will your activity take place?</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Address *</label>
                                <div class="relative">
                                    <input type="text" id="addressSearch"
                                        class="flex h-10 w-full rounded-lg border px-3 py-2 pr-24 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                        style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                        placeholder="Search for an address or location">
                                    <button type="button" onclick="searchAddress()"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg px-3 py-1.5 text-xs font-medium transition address-search-btn"
                                        style="background-color: var(--accent); color: white;"
                                        onmouseover="this.style.backgroundColor='#ff8c66'"
                                        onmouseout="this.style.backgroundColor='var(--accent)'">
                                        Search
                                    </button>
                                </div>
                                <input type="text" name="location_text" id="locationText" required
                                    class="mt-2 flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                    placeholder="e.g., 123 Main Street, Accra">
                                <p class="mt-1 text-xs" style="color: var(--muted);">Enter the full address or search and select from map</p>
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
                                click directly on the map below to set your activity's exact location.
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

                    <!-- Pricing & Capacity -->
                    <div class="rounded-xl border p-6 shadow-sm"
                        style="border-color: var(--border); background-color: var(--card);">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold" style="color: var(--foreground);">Pricing & Capacity</h3>
                            <p class="text-sm" style="color: var(--muted);">Set pricing and participant limits</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 flex items-center gap-2">
                                    <input type="checkbox" name="is_free" id="isFree" value="1"
                                        class="h-4 w-4 rounded border"
                                        style="border-color: var(--border); background-color: var(--background);"
                                        onchange="togglePricing()">
                                    <span class="text-sm font-medium" style="color: var(--foreground);">This activity is free</span>
                                </label>
                            </div>

                            <div id="pricingFields">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Price (GH₵) *</label>
                                        <input type="number" name="price_min" id="priceMin" step="0.01" min="0"
                                            class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                            style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                            placeholder="0.00">
                                    </div>
                                    
                                    <div>
                                        <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Max Price (GH₵) (Optional)</label>
                                        <input type="number" name="price_max" id="priceMax" step="0.01" min="0"
                                            class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                            style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                            placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Capacity (Optional)</label>
                                <input type="number" name="capacity" id="capacity" min="1"
                                    class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                    style="border-color: var(--border); background-color: var(--background); color: var(--foreground);"
                                    placeholder="Maximum number of participants">
                            </div>
                        </div>
                    </div>

                    <!-- Photos -->
                    <div class="rounded-xl border p-6 shadow-sm"
                        style="border-color: var(--border); background-color: var(--card);">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold" style="color: var(--foreground);">Photos</h3>
                            <p class="text-sm" style="color: var(--muted);">Upload photos of your activity</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium" style="color: var(--foreground);">Activity Photos</label>
                            <input type="file" name="photos[]" id="photos" multiple accept="image/*"
                                class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:border-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20"
                                style="border-color: var(--border); background-color: var(--background); color: var(--foreground);">
                            <p class="mt-1 text-xs" style="color: var(--muted);">You can select multiple images</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <button type="submit" id="submitBtn"
                            class="flex-1 rounded-lg px-6 py-3 text-sm font-semibold transition"
                            style="background-color: var(--accent); color: #ffffff;"
                            onmouseover="this.style.backgroundColor='#ff8c66'"
                            onmouseout="this.style.backgroundColor='var(--accent)'">
                            <span id="submitBtnText">Create Activity</span>
                            <span id="submitBtnLoader" style="display: none;">
                                <svg class="inline h-4 w-4 animate-spin" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Creating...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function togglePricing() {
            const isFree = document.getElementById('isFree').checked;
            const pricingFields = document.getElementById('pricingFields');
            const priceMin = document.getElementById('priceMin');
            
            if (isFree) {
                pricingFields.style.display = 'none';
                priceMin.removeAttribute('required');
            } else {
                pricingFields.style.display = 'block';
                priceMin.setAttribute('required', 'required');
            }
        }

        // Initialize
        togglePricing();

        // Recurrence Type Change Handler
        function updateDateTimeLabels() {
            const recurrenceType = document.querySelector('input[name="recurrence_type"]:checked')?.value || 'none';
            const isRecurring = recurrenceType === 'recurring';
            
            // Update labels
            const startDateLabel = document.getElementById('startDateLabel');
            const startTimeLabel = document.getElementById('startTimeLabel');
            const endDateLabel = document.getElementById('endDateLabel');
            const endTimeLabel = document.getElementById('endTimeLabel');
            const dateTimeDescription = document.getElementById('dateTimeDescription');
            const startDateHelp = document.getElementById('startDateHelp');
            const startTimeHelp = document.getElementById('startTimeHelp');
            const endDateHelp = document.getElementById('endDateHelp');
            const endTimeHelp = document.getElementById('endTimeHelp');
            
            if (isRecurring) {
                // Recurring activity labels
                startDateLabel.textContent = 'First Occurrence Date *';
                startTimeLabel.textContent = 'First Occurrence Time *';
                endDateLabel.textContent = 'Last Occurrence Date (Optional)';
                endTimeLabel.textContent = 'Last Occurrence Time (Optional)';
                dateTimeDescription.textContent = 'When does your recurring activity start?';
                startDateHelp.textContent = 'The date of the first occurrence';
                startTimeHelp.textContent = 'The time of the first occurrence';
                endDateHelp.textContent = 'When the recurring series ends (leave empty if ongoing)';
                endTimeHelp.textContent = 'The time of the last occurrence';
            } else {
                // One-time activity labels
                startDateLabel.textContent = 'Event Date *';
                startTimeLabel.textContent = 'Event Time *';
                endDateLabel.textContent = 'End Date (Optional)';
                endTimeLabel.textContent = 'End Time (Optional)';
                dateTimeDescription.textContent = 'When will your activity take place?';
                startDateHelp.textContent = 'The date when your activity will occur';
                startTimeHelp.textContent = 'The time when your activity starts';
                endDateHelp.textContent = 'When the activity ends (for one-time events)';
                endTimeHelp.textContent = 'The time when your activity ends';
            }
        }
        
        // Listen for recurrence type changes
        document.querySelectorAll('input[name="recurrence_type"]').forEach(radio => {
            radio.addEventListener('change', updateDateTimeLabels);
        });
        
        // Initialize labels on page load
        updateDateTimeLabels();

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
                        $('#locationText').val(result.display_name || query);
                        
                        const zoom = result.importance && result.importance > 0.7 ? 17 : 16;
                        locationMap.setView([lat, lng], zoom);
                        
                        $('.address-search-btn').html('Search').prop('disabled', false);
                    } else {
                        $('.address-search-btn').html('Search').prop('disabled', false);
                        alert('Location not found. You can click directly on the map to set your activity location.');
                    }
                },
                error: function () {
                    $('.address-search-btn').html('Search').prop('disabled', false);
                    alert('Search failed. You can click directly on the map to set your activity location.');
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
            locationMarker.bindPopup('Activity Location').openPopup();
            
            $('#displayCoords').text(lat.toFixed(6) + ', ' + lng.toFixed(6));
            $('#coordinatesDisplay').removeClass('hidden');
        }

        // Initialize map on page load
        $(document).ready(function () {
            initializeMap();
        });

        // Form submission
        $('#createActivityForm').on('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = document.getElementById('submitBtnText');
            const submitBtnLoader = document.getElementById('submitBtnLoader');
            const alertDiv = document.getElementById('alertMessage');
            
            // Ensure map coordinates are included
            if (selectedLat && selectedLng) {
                $('#selectedLat').val(selectedLat);
                $('#selectedLng').val(selectedLng);
            }
            
            submitBtn.disabled = true;
            submitBtnText.style.display = 'none';
            submitBtnLoader.style.display = 'inline';
            alertDiv.innerHTML = '';
            alertDiv.className = '';
            
            const formData = new FormData(this);
            
            // Add map coordinates if available
            if (selectedLat && selectedLng) {
                formData.append('lat', selectedLat);
                formData.append('lng', selectedLng);
            }
            
            $.ajax({
                url: '../actions/activity_create_action.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alertDiv.className = 'rounded-lg border p-4 text-sm';
                        alertDiv.style.backgroundColor = 'rgba(34, 197, 94, 0.1)';
                        alertDiv.style.borderColor = 'rgba(34, 197, 94, 0.3)';
                        alertDiv.style.color = '#4ade80';
                        alertDiv.textContent = response.message;
                        
                        setTimeout(function() {
                            window.location.href = 'owner_dashboard.php';
                        }, 2000);
                    } else {
                        alertDiv.className = 'rounded-lg border p-4 text-sm';
                        alertDiv.style.backgroundColor = 'rgba(239, 68, 68, 0.1)';
                        alertDiv.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                        alertDiv.style.color = '#f87171';
                        alertDiv.textContent = response.message || 'Failed to create activity. Please try again.';
                        
                        submitBtn.disabled = false;
                        submitBtnText.style.display = 'inline';
                        submitBtnLoader.style.display = 'none';
                    }
                },
                error: function() {
                    alertDiv.className = 'rounded-lg border p-4 text-sm';
                    alertDiv.style.backgroundColor = 'rgba(239, 68, 68, 0.1)';
                    alertDiv.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                    alertDiv.style.color = '#f87171';
                    alertDiv.textContent = 'An error occurred. Please try again.';
                    
                    submitBtn.disabled = false;
                    submitBtnText.style.display = 'inline';
                    submitBtnLoader.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>

