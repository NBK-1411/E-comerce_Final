<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/booking_controller.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/payment_controller.php');
require_once(__DIR__ . '/../includes/site_nav.php');

// Check if logged in
require_login();

// Get booking ID
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if (!$booking_id) {
    header('Location: profile.php');
    exit();
}

// Get booking details
$booking = get_booking_by_id_ctr($booking_id);
if (!$booking || $booking['user_id'] != $_SESSION['customer_id']) {
    header('Location: profile.php');
    exit();
}

// Get venue details
$venue = get_venue_by_id_ctr($booking['venue_id']);
if (!$venue) {
    header('Location: profile.php');
    exit();
}

// Get payment details
$payments = get_payments_by_booking_ctr($booking_id);
$payment = !empty($payments) ? $payments[0] : null;

// Generate QR reference if not exists
$qr_reference = $booking['qr_reference'] ?? 'QR_' . strtoupper(uniqid());

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

// Parse venue photos
$photos = json_decode($venue['photos_json'] ?? '[]', true);
if (empty($photos)) {
    $venue_image = '../images/portfolio/01.jpg';
} else {
    $photo_path = $photos[0];
    // If path starts with uploads/venues/, add ../ prefix
    if (strpos($photo_path, 'uploads/venues/') === 0) {
        $venue_image = '../' . $photo_path;
    } elseif (strpos($photo_path, '../') === 0) {
        // Already has ../ prefix, use as is
        $venue_image = $photo_path;
    } else {
        // Fallback: assume it's a relative path from uploads/venues/
        $venue_image = '../uploads/venues/' . basename($photo_path);
    }
}

$is_confirmed = $booking['status'] === 'confirmed';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Confirmation - Go Outside</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
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
            height: 300px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
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

    <main class="px-4 py-8" style="padding-top: 80px;">
        <div class="container mx-auto max-w-lg">
            <!-- Success Header -->
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-500/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </div>
                <h1 class="mb-2 text-2xl font-bold" style="color: var(--text-primary);">
                    <?php echo $is_confirmed ? "Booking Confirmed!" : "Booking Requested!"; ?>
                </h1>
                <p style="color: var(--text-secondary);">
                    <?php
                    if ($is_confirmed) {
                        echo "Your booking has been confirmed. See you there!";
                    } else {
                        echo "The host will confirm your booking within 24 hours.";
                    }
                    ?>
                </p>
            </div>

            <!-- Booking Card -->
            <div class="mb-6 overflow-hidden rounded-xl border shadow-sm"
                style="border-color: var(--border-color); background-color: var(--bg-card);">
                <!-- Venue Preview -->
                <div class="relative h-40 w-full">
                    <img src="<?php echo htmlspecialchars($venue_image); ?>"
                        alt="<?php echo htmlspecialchars($venue['title']); ?>" class="h-full w-full object-cover">
                    <span
                        class="absolute left-3 top-3 inline-flex items-center gap-1 rounded border border-green-500/30 bg-green-500/10 px-2.5 py-1 text-xs font-semibold text-green-500">
                        <?php if ($is_confirmed): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            Confirmed
                        <?php else: ?>
                            Pending Confirmation
                        <?php endif; ?>
                    </span>
                </div>

                <div class="p-4">
                    <h2 class="mb-1 text-lg font-semibold" style="color: var(--text-primary);"><?php echo htmlspecialchars($venue['title']); ?>
                    </h2>
                    <div class="mb-4 flex items-center gap-2 text-sm" style="color: var(--text-secondary);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <?php echo htmlspecialchars($venue['location_text']); ?>
                    </div>

                    <!-- QR Code -->
                    <div class="mb-4 flex justify-center rounded-lg bg-white p-6">
                        <canvas id="qrCode"></canvas>
                    </div>

                    <p class="mb-4 text-center text-xs" style="color: var(--text-secondary);">
                        Show this QR code at the venue for verification
                    </p>

                    <!-- Booking Details -->
                    <div class="space-y-3 rounded-lg p-4 text-sm" style="background-color: var(--bg-primary);">
                        <div class="flex items-center justify-between">
                            <span style="color: var(--text-secondary);">Booking ID</span>
                            <div class="flex items-center gap-2">
                                <span
                                    class="font-mono font-medium" style="color: var(--text-primary);"><?php echo 'BK' . str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></span>
                                <button onclick="copyBookingId()"
                                    class="flex h-6 w-6 items-center justify-center rounded transition-colors"
                                    style="color: var(--text-secondary);"
                                    onmouseover="this.style.backgroundColor='var(--bg-card)'"
                                    onmouseout="this.style.backgroundColor='transparent'"
                                    id="copyBtn">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" id="copyIcon">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span style="color: var(--text-secondary);">Date</span>
                            <span class="font-medium" style="color: var(--text-primary);">
                                <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span style="color: var(--text-secondary);">Time</span>
                            <span class="font-medium" style="color: var(--text-primary);">
                                <?php echo date('g:i A', strtotime($booking['start_time'])); ?> -
                                <?php echo date('g:i A', strtotime($booking['end_time'])); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span style="color: var(--text-secondary);">Guests</span>
                            <span class="font-medium" style="color: var(--text-primary);"><?php echo $booking['guest_count']; ?></span>
                        </div>
                        <?php if ($payment): ?>
                        <div class="flex items-center justify-between">
                                <span style="color: var(--text-secondary);">Amount Paid</span>
                                <span
                                    class="font-medium" style="color: var(--text-primary);">GH₵<?php echo number_format($payment['amount'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex items-center justify-between">
                            <span style="color: var(--text-secondary);">Status</span>
                            <span
                                class="inline-flex items-center gap-1 rounded border border-green-500/30 bg-transparent px-2 py-1 text-xs font-semibold text-green-500">
                                <?php if ($is_confirmed): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                <?php endif; ?>
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Map -->
            <?php if ($lat && $lng): ?>
            <div class="mb-6 overflow-hidden rounded-xl border shadow-sm"
                style="border-color: var(--border-color); background-color: var(--bg-card);">
                <div class="p-4">
                    <h2 class="mb-3 text-lg font-semibold" style="color: var(--text-primary);">Location</h2>
                    <div class="mb-4 text-sm" style="color: var(--text-secondary);">
                        <div class="mb-2 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <?php echo htmlspecialchars($venue['location_text']); ?>
                        </div>
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
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        Get Directions
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="space-y-3">
                <a href="profile.php"
                    class="flex w-full items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-semibold transition"
                    style="background-color: var(--accent); color: #ffffff;"
                    onmouseover="this.style.backgroundColor='var(--accent-hover)'" onmouseout="this.style.backgroundColor='var(--accent)'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    View My Bookings
                </a>

                <div class="flex gap-3">
                    <a href="../index.php"
                        class="flex flex-1 items-center justify-center gap-2 rounded-lg border bg-transparent px-4 py-3 text-sm font-medium transition"
                        style="border-color: var(--border-color); color: var(--text-primary);"
                        onmouseover="this.style.backgroundColor='var(--bg-card)'"
                        onmouseout="this.style.backgroundColor='transparent'"
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polygon
                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                        </svg>
                        Explore More
                    </a>
                    <button onclick="shareBooking()"
                        class="flex flex-1 items-center justify-center gap-2 rounded-lg border bg-transparent px-4 py-3 text-sm font-medium transition"
                        style="border-color: var(--border-color); color: var(--text-primary);"
                        onmouseover="this.style.backgroundColor='var(--bg-card)'"
                        onmouseout="this.style.backgroundColor='transparent'"
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                            <polyline points="16 6 12 2 8 6" />
                            <line x1="12" y1="2" x2="12" y2="15" />
                        </svg>
                        Share
                    </button>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="mt-8 rounded-lg border p-4"
                style="border-color: var(--border-color); background-color: var(--bg-card);">
                <h3 class="mb-3 font-semibold" style="color: var(--text-primary);">What happens next?</h3>
                <div class="space-y-3">
                    <?php if ($is_confirmed): ?>
                        <div class="flex gap-3">
                            <div
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-medium"
                                style="background-color: var(--accent); color: #ffffff;">
                                1</div>
                            <p class="text-sm" style="color: var(--text-secondary);">You'll receive a confirmation email with all the details</p>
                        </div>
                        <div class="flex gap-3">
                            <div
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-medium"
                                style="background-color: var(--accent); color: #ffffff;">
                                2</div>
                            <p class="text-sm" style="color: var(--text-secondary);">Add the event to your calendar so you don't forget</p>
                        </div>
                        <div class="flex gap-3">
                            <div
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-medium"
                                style="background-color: var(--accent); color: #ffffff;">
                                3</div>
                            <p class="text-sm" style="color: var(--text-secondary);">Show your QR code at the venue for check-in</p>
                        </div>
                    <?php elseif (($venue['booking_type'] ?? 'rent') === 'reservation'): ?>
                        <div class="flex gap-3">
                            <div
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-medium"
                                style="background-color: var(--accent); color: #ffffff;">
                                1</div>
                            <p class="text-sm" style="color: var(--text-secondary);">The host will review your reservation request</p>
                        </div>
                        <div class="flex gap-3">
                            <div
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-medium"
                                style="background-color: var(--accent); color: #ffffff;">
                                2</div>
                            <p class="text-sm" style="color: var(--text-secondary);">You'll receive a notification once confirmed</p>
                        </div>
                        <div class="flex gap-3">
                            <div
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-medium"
                                style="background-color: var(--accent); color: #ffffff;">
                                3</div>
                            <p class="text-sm" style="color: var(--text-secondary);">Show your QR code at the venue upon arrival</p>
                        </div>
                    <?php else: ?>
                        <div class="flex gap-3">
                            <div
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-medium"
                                style="background-color: var(--accent); color: #ffffff;">
                                1</div>
                            <p class="text-sm" style="color: var(--text-secondary);">The host will review and confirm within 24 hours</p>
                        </div>
                        <div class="flex gap-3">
                            <div
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-medium"
                                style="background-color: var(--accent); color: #ffffff;">
                                2</div>
                            <p class="text-sm" style="color: var(--text-secondary);">You'll receive a notification once confirmed</p>
                        </div>
                        <div class="flex gap-3">
                            <div
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-medium"
                                style="background-color: var(--accent); color: #ffffff;">
                                3</div>
                            <p class="text-sm" style="color: var(--text-secondary);">If not confirmed, your deposit is fully refunded</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cancellation Info -->
            <div class="mt-4 rounded-lg bg-[rgba(39,39,42,0.3)] p-4 text-center">
                <p class="text-sm" style="color: var(--text-secondary);">
                    Need to cancel? <a href="profile.php" class="hover:underline" style="color: var(--accent);">Manage your booking</a>
                </p>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Generate QR Code
        const qrData = JSON.stringify({
            bookingId: <?php echo $booking_id; ?>,
            venueId: <?php echo $venue['venue_id']; ?>,
            qrReference: '<?php echo $qr_reference; ?>',
            verified: true
        });

        QRCode.toCanvas(document.getElementById('qrCode'), qrData, {
            width: 160,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        }, function (error) {
            if (error) console.error(error);
        });

        // Copy Booking ID
        let copied = false;
        function copyBookingId() {
            const bookingId = '<?php echo 'BK' . str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?>';
            navigator.clipboard.writeText(bookingId).then(() => {
                copied = true;
                const copyBtn = document.getElementById('copyBtn');
                const copyIcon = document.getElementById('copyIcon');
                if (copyIcon) {
                    copyIcon.innerHTML = '<polyline points="20 6 9 17 4 12"/>';
                    copyIcon.classList.add('text-green-500');
                }
                setTimeout(() => {
                    copied = false;
                    if (copyIcon) {
                        copyIcon.innerHTML = '<rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>';
                        copyIcon.classList.remove('text-green-500');
                    }
                }, 2000);
            });
        }

        // Share Booking
        function shareBooking() {
            if (navigator.share) {
                navigator.share({
                    title: 'My Booking - <?php echo htmlspecialchars($venue['title']); ?>',
                    text: 'Check out my booking at <?php echo htmlspecialchars($venue['title']); ?>',
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Booking link copied to clipboard!');
                });
            }
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