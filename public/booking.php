<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../settings/db_cred.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../includes/site_nav.php');

// Check if logged in
require_login();

// Get venue ID
$venue_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($venue_id == 0) {
    header('Location: search.php');
    exit();
}

// Get venue details
$venue = get_venue_details_ctr($venue_id);

if (!$venue) {
    header('Location: search.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];

// Calculate deposit percentage
$deposit_percentage = $venue['deposit_percentage'] ?? 30;
$price_min = floatval($venue['price_min']);
$price_max = floatval($venue['price_max']);
$price_avg = ($price_min + $price_max) / 2;

// Parse photos
$photos = json_decode($venue['photos_json'] ?? '[]', true);
if (empty($photos)) {
    $venue_image = '../images/portfolio/01.jpg';
} else {
    $photo_path = $photos[0];
    // Remove any existing ../ or ./
    $photo_path = ltrim($photo_path, './');
    $photo_path = preg_replace('#^\.\./#', '', $photo_path); // Remove leading ../
    
    // Ensure path starts with uploads/ or images/
    if (strpos($photo_path, 'uploads/') === 0 || strpos($photo_path, 'images/') === 0) {
        $venue_image = '../' . $photo_path;
    } else {
        // Default: assume it's relative to project root
        $venue_image = '../' . $photo_path;
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
?>
<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Book Venue - <?php echo htmlspecialchars($venue['title']); ?> - Go Outside</title>
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
</style>
</head>

<body>
<?php render_site_nav(['base_path' => '../']); ?>

    <main class="px-4 py-6" style="padding-top: 80px;">
        <div class="container mx-auto max-w-3xl">
            <!-- Back Button -->
            <a href="venue_detail.php?id=<?php echo $venue_id; ?>"
                class="mb-4 inline-flex items-center gap-2 text-sm transition-colors"
                style="color: var(--text-secondary);" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7" />
                    <path d="M19 12H5" />
                </svg>
                Back to listing
            </a>

            <!-- Progress Steps -->
            <div class="mb-6">
                <div class="flex items-center">
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center flex-1">
                        <div class="flex items-center w-full">
                            <div id="step1"
                                class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium shrink-0"
                                style="background-color: var(--accent); color: #ffffff;">
                                1
                            </div>
                            <div id="step1Line" class="flex-1 h-1 mx-2 rounded-full" style="background-color: rgba(39,39,42,0.7);"></div>
                        </div>
                        <span class="mt-2 text-xs text-center" style="color: var(--text-secondary);">Details</span>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="flex flex-col items-center flex-1">
                        <div class="flex items-center w-full">
                            <div id="step2"
                                class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium shrink-0"
                                style="background-color: var(--bg-card); color: var(--text-secondary);">
                                2
                            </div>
                            <div id="step2Line" class="flex-1 h-1 mx-2 rounded-full" style="background-color: rgba(39,39,42,0.7);"></div>
                        </div>
                        <span class="mt-2 text-xs text-center" style="color: var(--text-secondary);">Payment</span>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="flex flex-col items-center flex-1">
                        <div class="flex items-center w-full">
                            <div id="step3"
                                class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium shrink-0"
                                style="background-color: var(--bg-card); color: var(--text-secondary);">
                                3
                            </div>
                        </div>
                        <span class="mt-2 text-xs text-center" style="color: var(--text-secondary);">Confirm</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                <!-- Main Content -->
                <div class="flex-1 min-w-0">
                    <!-- Step 1: Details -->
                    <div id="stepDetails" class="step-content space-y-6">
                        <h2 class="text-xl font-bold" style="color: var(--text-primary);">Booking Details</h2>

                        <!-- Date Selection -->
                        <div>
                            <label class="mb-2 block text-sm font-medium" style="color: var(--text-primary);">Select Date *</label>
                            <input type="date" id="bookingDate" name="booking_date"
                                class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2"
                                style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-primary);"
                                placeholder-style="color: var(--text-secondary);"
                                onfocus="this.style.borderColor='var(--accent)'; this.style.boxShadow='0 0 0 2px rgba(255, 107, 53, 0.2)'"
                                onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'"
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                        <!-- Time Selection -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium" style="color: var(--text-primary);">Start Time *</label>
                                <input type="time" id="startTime" name="start_time"
                                    class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2"
                                style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-primary);"
                                placeholder-style="color: var(--text-secondary);"
                                onfocus="this.style.borderColor='var(--accent)'; this.style.boxShadow='0 0 0 2px rgba(255, 107, 53, 0.2)'"
                                onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'"
                                    required>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium" style="color: var(--text-primary);">End Time *</label>
                                <input type="time" id="endTime" name="end_time"
                                    class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2"
                                style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-primary);"
                                placeholder-style="color: var(--text-secondary);"
                                onfocus="this.style.borderColor='var(--accent)'; this.style.boxShadow='0 0 0 2px rgba(255, 107, 53, 0.2)'"
                                onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'"
                                    required>
                            </div>
                        </div>

                        <!-- Guest Count -->
                        <div>
                            <label class="mb-2 block text-sm font-medium" style="color: var(--text-primary);">Number of Guests</label>
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="changeGuests(-1)"
                                    class="flex h-10 w-10 items-center justify-center rounded-lg border bg-transparent transition-colors disabled:opacity-50"
                                    style="border-color: var(--border-color); color: var(--text-primary);"
                                    onmouseover="this.style.backgroundColor='var(--bg-card)'"
                                    onmouseout="this.style.backgroundColor='transparent'"
                                    id="decreaseGuests">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                </button>
                                <span id="guestCountDisplay"
                                    class="w-12 text-center text-lg font-medium" style="color: var(--text-primary);">1</span>
                                <input type="hidden" id="guestCount" name="guest_count" value="1">
                                <button type="button" onclick="changeGuests(1)"
                                    class="flex h-10 w-10 items-center justify-center rounded-lg border bg-transparent transition-colors disabled:opacity-50"
                                    style="border-color: var(--border-color); color: var(--text-primary);"
                                    onmouseover="this.style.backgroundColor='var(--bg-card)'"
                                    onmouseout="this.style.backgroundColor='transparent'"
                                    id="increaseGuests">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1 text-xs" style="color: var(--text-secondary);">Max: <?php echo $venue['capacity']; ?> guests</p>
                        </div>

                        <!-- Special Requests -->
                        <div>
                            <label class="mb-2 block text-sm font-medium" style="color: var(--text-primary);">Message to Host (Optional)</label>
                            <textarea id="specialRequests" name="special_requests" rows="4"
                                class="flex min-h-[100px] w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2"
                                style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-primary);"
                                placeholder-style="color: var(--text-secondary);"
                                onfocus="this.style.borderColor='var(--accent)'; this.style.boxShadow='0 0 0 2px rgba(255, 107, 53, 0.2)'"
                                onblur="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none'"
                                placeholder="Introduce yourself and share any special requests..."></textarea>
                        </div>

                        <button type="button" onclick="goToPayment()" id="continueToPayment"
                            class="w-full rounded-lg px-4 py-3 text-sm font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed"
                            style="background-color: var(--accent); color: #ffffff;"
                            onmouseover="this.style.backgroundColor='var(--accent-hover)'" onmouseout="this.style.backgroundColor='var(--accent)'"
                            disabled>
                            Continue to Payment
                        </button>
                        </div>

                    <!-- Step 2: Payment -->
                    <div id="stepPayment" class="step-content hidden">
                        <div class="space-y-6">
                            <h2 class="text-xl font-bold mb-6" style="color: var(--text-primary);">Payment Method</h2>

                            <!-- Payment Options -->
                            <div>
                                <button type="button" onclick="selectPaymentMethod('paystack')" id="paystackOption"
                                    class="flex w-full items-center gap-4 rounded-lg border p-4 transition-colors"
                                    style="border-color: var(--accent); background-color: rgba(255, 107, 53, 0.05);"
                                    onmouseover="this.style.backgroundColor='rgba(255, 107, 53, 0.1)'"
                                    onmouseout="this.style.backgroundColor='rgba(255, 107, 53, 0.05)'">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                                        style="background-color: var(--accent); color: #ffffff;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2" />
                                            <line x1="1" y1="10" x2="23" y2="10" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 text-left min-w-0">
                                        <p class="font-medium mb-0.5" style="color: var(--text-primary);">Paystack</p>
                                        <p class="text-sm" style="color: var(--text-secondary);">Card, Bank, Mobile Money</p>
                                    </div>
                                    <div class="h-5 w-5 shrink-0 rounded-full border-2 flex items-center justify-center"
                                        style="border-color: var(--accent); background-color: var(--accent);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                            style="color: #ffffff;"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                    </div>
                                </button>
                            </div>

                            <!-- Escrow Notice -->
                            <div class="rounded-lg border p-4"
                                style="border-color: rgba(34, 197, 94, 0.3); background-color: rgba(34, 197, 94, 0.08);">
                                <div class="flex items-start gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 mt-0.5"
                                        style="color: #22c55e;"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium mb-1" style="color: var(--text-primary);">Secure Escrow Payment</h4>
                                        <p class="text-sm leading-relaxed" style="color: var(--text-secondary);">
                                            Your payment is held securely until the host confirms your booking. Full refund
                                            if not confirmed within 24 hours.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Navigation Buttons -->
                            <div class="flex gap-3 pt-2">
                                <button type="button" onclick="goToDetails()"
                                    class="flex-1 rounded-lg border bg-transparent px-4 py-3 text-sm font-medium transition"
                                    style="border-color: var(--border-color); color: var(--text-primary);"
                                    onmouseover="this.style.backgroundColor='var(--bg-card)'"
                                    onmouseout="this.style.backgroundColor='transparent'">
                                    Back
                                </button>
                                <button type="button" onclick="goToConfirm()" id="reviewBooking"
                                    class="flex-1 rounded-lg px-4 py-3 text-sm font-semibold text-white transition"
                                    style="background-color: var(--accent);"
                                    onmouseover="this.style.backgroundColor='var(--accent-hover)'"
                                    onmouseout="this.style.backgroundColor='var(--accent)'">
                                    Review Booking
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Confirm -->
                    <div id="stepConfirm" class="step-content hidden space-y-6">
                        <h2 class="text-xl font-bold" style="color: var(--text-primary);">Review & Confirm</h2>

            <!-- Booking Summary -->
                        <div class="space-y-4 rounded-lg p-4" style="background-color: var(--bg-card);">
                            <div class="flex items-center justify-between">
                                <span style="color: var(--text-secondary);">Date</span>
                                <span class="font-medium" style="color: var(--text-primary);" id="confirmDate">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span style="color: var(--text-secondary);">Time</span>
                                <span class="font-medium" style="color: var(--text-primary);" id="confirmTime">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span style="color: var(--text-secondary);">Guests</span>
                                <span class="font-medium" style="color: var(--text-primary);" id="confirmGuests">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span style="color: var(--text-secondary);">Payment Method</span>
                                <span class="font-medium capitalize" style="color: var(--text-primary);">Paystack</span>
                            </div>
                    </div>

                        <!-- Terms -->
                        <div class="flex items-start space-x-2">
                            <input type="checkbox" id="agreeTerms"
                                class="mt-1 h-4 w-4 rounded focus:ring-[#FF6B35]"
                                style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--accent);">
                            <label for="agreeTerms" class="text-sm leading-relaxed" style="color: var(--text-secondary);">
                                I agree to the <a href="#" class="hover:underline" style="color: var(--accent);">Terms of Service</a>
                                and <a href="#" class="hover:underline" style="color: var(--accent);">Cancellation Policy</a>
                                (<?php echo $cancellation_info['label']; ?>)
                            </label>
                    </div>

                        <div class="flex gap-3">
                            <button type="button" onclick="goToPayment()"
                                class="flex-1 rounded-lg border bg-transparent px-4 py-3 text-sm font-medium transition"
                                style="border-color: var(--border-color); color: var(--text-primary);"
                                onmouseover="this.style.backgroundColor='var(--bg-card)'"
                                onmouseout="this.style.backgroundColor='transparent'"
                                Back
                            </button>
                            <button type="button" onclick="submitBooking()" id="submitBookingBtn"
                                class="flex-1 rounded-lg px-4 py-3 text-sm font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed"
                                style="background-color: var(--accent); color: #ffffff;"
                                onmouseover="this.style.backgroundColor='var(--accent-hover)'" onmouseout="this.style.backgroundColor='var(--accent)'"
                                disabled>
                                Pay GH₵<span id="totalAmountDisplay">0</span>
                            </button>
                        </div>
                    </div>

                    <!-- Alert Message -->
                    <div id="alertMessage" class="mt-4 hidden rounded-lg p-4"></div>
                    </div>

                <!-- Order Summary Sidebar -->
                <div class="w-full lg:w-96 lg:flex-shrink-0">
                    <div class="sticky top-20 rounded-xl border p-6 shadow-sm w-full"
                        style="border-color: var(--border-color); background-color: var(--bg-card);">
                        <!-- Venue Preview -->
                        <div class="mb-5 flex gap-3">
                            <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-lg">
                                <img src="<?php echo htmlspecialchars($venue_image); ?>"
                                    alt="<?php echo htmlspecialchars($venue['title']); ?>"
                                    class="h-full w-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <span
                                    class="mb-2 inline-block rounded border px-2.5 py-1 text-xs font-medium"
                                    style="border-color: var(--border-color); background-color: var(--bg-primary); color: var(--text-secondary);">
                                    <?php echo htmlspecialchars($venue['cat_name']); ?>
                                </span>
                                <h3 class="mb-1 font-semibold text-base leading-tight" style="color: var(--text-primary);">
                                    <?php echo htmlspecialchars($venue['title']); ?>
                                </h3>
                                <div class="flex items-start gap-1.5 text-xs" style="color: var(--text-secondary);">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 mt-0.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    <span class="break-words"><?php echo htmlspecialchars($venue['location_text']); ?></span>
                                </div>
                            </div>
                        </div>

                        <hr class="my-5" style="border-color: var(--border-color);">

                        <!-- Price Breakdown -->
                        <h4 class="mb-4 text-base font-semibold" style="color: var(--text-primary);">Price Details</h4>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3 min-w-0">
                                <span class="flex-shrink-0 text-nowrap" style="color: var(--text-secondary);">Base Price</span>
                                <span class="font-medium text-right whitespace-nowrap flex-shrink-0" style="color: var(--text-primary);">GH₵<span
                                        id="basePriceDisplay"><?php echo number_format($price_avg, 2); ?></span></span>
                            </div>
                            <div class="flex items-center justify-between gap-3 min-w-0">
                                <span class="flex-shrink-0 text-nowrap" style="color: var(--text-secondary);">Deposit (<?php echo $deposit_percentage; ?>%)</span>
                                <span class="font-medium text-right whitespace-nowrap flex-shrink-0" style="color: var(--text-primary);">GH₵<span id="depositDisplay">0.00</span></span>
                            </div>
                            <div class="flex items-center justify-between gap-3 min-w-0">
                                <span class="flex-shrink-0 text-nowrap" style="color: var(--text-secondary);">Service fee</span>
                                <span class="font-medium text-right whitespace-nowrap flex-shrink-0" style="color: var(--text-primary);">GH₵<span id="serviceFeeDisplay">0.00</span></span>
                            </div>
                        </div>

                        <hr class="my-5" style="border-color: var(--border-color);">

                        <div class="flex items-center justify-between gap-3 min-w-0 font-semibold">
                            <span class="text-base flex-shrink-0 text-nowrap" style="color: var(--text-primary);">Due Now</span>
                            <span class="text-xl text-right whitespace-nowrap flex-shrink-0" style="color: var(--accent);">GH₵<span id="totalDueDisplay">0.00</span></span>
                        </div>
                        <p class="mt-3 text-xs leading-relaxed" id="balanceNote" style="display: none; color: var(--text-secondary);">
                            Balance of GH₵<span id="balanceAmount">0</span> due at venue
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
        let currentStep = 1;
        let bookingData = {};
        let paymentMethod = 'paystack';

        const basePrice = <?php echo $price_avg; ?>;
        const depositPercent = <?php echo $deposit_percentage; ?>;
        const maxGuests = <?php echo $venue['capacity']; ?>;

        // Initialize from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('date')) document.getElementById('bookingDate').value = urlParams.get('date');
        if (urlParams.has('start')) document.getElementById('startTime').value = urlParams.get('start');
        if (urlParams.has('end')) document.getElementById('endTime').value = urlParams.get('end');
        if (urlParams.has('guests')) {
            const guests = parseInt(urlParams.get('guests'));
            document.getElementById('guestCount').value = guests;
            document.getElementById('guestCountDisplay').textContent = guests;

            // Update buttons state
            document.getElementById('decreaseGuests').disabled = guests <= 1;
            document.getElementById('increaseGuests').disabled = guests >= maxGuests;
        }

        const isReservation = <?php echo json_encode(($venue['booking_type'] ?? 'rent') === 'reservation'); ?>;

        // Step navigation
        function goToDetails() {
            currentStep = 1;
            updateStepDisplay();
        }

        function goToPayment() {
            const date = document.getElementById('bookingDate').value;
            const startTime = document.getElementById('startTime').value;
            const endTime = document.getElementById('endTime').value;

            if (!date || !startTime || !endTime) {
                showAlert('Please fill in all required fields', 'danger');
                return;
            }

            if (startTime >= endTime) {
                showAlert('End time must be after start time', 'danger');
                return;
            }

            if (isReservation) {
                goToConfirm(); // Skip payment step for reservations
            } else {
                currentStep = 2;
                updateStepDisplay();
            }
        }

        function goToConfirm() {
            currentStep = 3;
            updateBookingSummary();
            updateStepDisplay();
        }

        function updateStepDisplay() {
            document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));

            if (currentStep === 1) {
                document.getElementById('stepDetails').classList.remove('hidden');
            } else if (currentStep === 2) {
                document.getElementById('stepPayment').classList.remove('hidden');
            } else if (currentStep === 3) {
                document.getElementById('stepConfirm').classList.remove('hidden');
            }

            for (let i = 1; i <= 3; i++) {
                const stepEl = document.getElementById('step' + i);
                const lineEl = document.getElementById('step' + i + 'Line');

                if (i < currentStep) {
                    stepEl.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium shrink-0';
                    stepEl.style.backgroundColor = 'var(--accent)';
                    stepEl.style.color = '#ffffff';
                    stepEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
                    if (lineEl) {
                        lineEl.className = 'flex-1 h-1 mx-2 rounded-full';
                        lineEl.style.backgroundColor = 'var(--accent)';
                    }
                } else if (i === currentStep) {
                    stepEl.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium shrink-0';
                    stepEl.style.backgroundColor = 'var(--accent)';
                    stepEl.style.color = '#ffffff';
                    stepEl.textContent = i;
                    if (lineEl) {
                        lineEl.className = 'flex-1 h-1 mx-2 rounded-full';
                        lineEl.style.backgroundColor = 'rgba(39,39,42,0.7)';
                    }
                } else {
                    stepEl.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium shrink-0';
                    stepEl.style.backgroundColor = 'var(--bg-card)';
                    stepEl.style.color = 'var(--text-secondary)';
                    stepEl.textContent = i;
                    if (lineEl) {
                        lineEl.className = 'flex-1 h-1 mx-2 rounded-full';
                        lineEl.style.backgroundColor = 'rgba(39,39,42,0.7)';
                    }
                }
            }

            // Hide payment step in progress bar if reservation
            if (isReservation) {
                const step2Container = document.getElementById('step2').closest('.flex.flex-col');
                if (step2Container) {
                    step2Container.style.display = 'none';
                }
            }
        }

        function changeGuests(delta) {
            const input = document.getElementById('guestCount');
            const display = document.getElementById('guestCountDisplay');
            const current = parseInt(input.value);
            const newValue = Math.max(1, Math.min(maxGuests, current + delta));
            input.value = newValue;
            display.textContent = newValue;

            document.getElementById('decreaseGuests').disabled = newValue <= 1;
            document.getElementById('increaseGuests').disabled = newValue >= maxGuests;

            updatePricing();
        }

        function selectPaymentMethod(method) {
            paymentMethod = method;
        }

        function updatePricing() {
            if (isReservation) {
                // Hide price details for reservations
                document.getElementById('basePriceDisplay').parentElement.parentElement.style.display = 'none';
                document.getElementById('depositDisplay').parentElement.parentElement.style.display = 'none';
                document.getElementById('serviceFeeDisplay').parentElement.parentElement.style.display = 'none';
                document.getElementById('totalDueDisplay').parentElement.parentElement.style.display = 'none';
                document.getElementById('balanceNote').style.display = 'none';

                document.getElementById('continueToPayment').textContent = 'Continue to Confirmation';
                document.getElementById('continueToPayment').disabled = false;

                document.getElementById('submitBookingBtn').textContent = 'Confirm Reservation';
                return;
            }

            const guests = parseInt(document.getElementById('guestCount').value);
            const date = document.getElementById('bookingDate').value;
            const startTime = document.getElementById('startTime').value;
            const endTime = document.getElementById('endTime').value;

            if (date && startTime && endTime) {
        const start = new Date('2000-01-01 ' + startTime);
        const end = new Date('2000-01-01 ' + endTime);
                const hours = (end - start) / (1000 * 60 * 60);

                if (hours > 0) {
                    const totalBase = basePrice * hours;
                    const deposit = Math.round(totalBase * (depositPercent / 100));
                    const serviceFee = Math.round(totalBase * 0.05);
                    const totalDue = deposit + serviceFee;
                    const balance = totalBase - deposit;

                    document.getElementById('basePriceDisplay').textContent = totalBase.toFixed(2);
                    document.getElementById('depositDisplay').textContent = deposit.toFixed(2);
                    document.getElementById('serviceFeeDisplay').textContent = serviceFee.toFixed(2);
                    document.getElementById('totalDueDisplay').textContent = totalDue.toFixed(2);
                    document.getElementById('totalAmountDisplay').textContent = totalDue.toLocaleString();

                    if (balance > 0) {
                        document.getElementById('balanceNote').style.display = 'block';
                        document.getElementById('balanceAmount').textContent = balance.toFixed(2);
        } else {
                        document.getElementById('balanceNote').style.display = 'none';
                    }

                    document.getElementById('continueToPayment').disabled = false;
                }
            }
        }

        function updateBookingSummary() {
            const date = new Date(document.getElementById('bookingDate').value);
            const startTime = document.getElementById('startTime').value;
            const endTime = document.getElementById('endTime').value;
            const guests = document.getElementById('guestCount').value;

            document.getElementById('confirmDate').textContent = date.toLocaleDateString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric'
            });
            document.getElementById('confirmTime').textContent = startTime + ' - ' + endTime;
            document.getElementById('confirmGuests').textContent = guests + ' ' + (guests == 1 ? 'person' : 'people');

            // Hide payment method in summary if reservation
            if (isReservation) {
                const paymentMethodRow = document.getElementById('confirmGuests').parentElement.nextElementSibling;
                if (paymentMethodRow) paymentMethodRow.style.display = 'none';
            }
        }

        function submitBooking() {
            if (!document.getElementById('agreeTerms').checked) {
                showAlert('Please agree to the terms and conditions', 'danger');
        return;
    }
    
            const submitBtn = document.getElementById('submitBookingBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin h-4 w-4 inline-block mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';

            bookingData = {
                venue_id: <?php echo $venue_id; ?>,
                booking_date: document.getElementById('bookingDate').value,
                start_time: document.getElementById('startTime').value,
                end_time: document.getElementById('endTime').value,
                number_of_guests: document.getElementById('guestCount').value,
                special_requirements: document.getElementById('specialRequests').value,
                payment_method: isReservation ? 'reservation' : 'paystack'
            };

            const start = new Date('2000-01-01 ' + bookingData.start_time);
            const end = new Date('2000-01-01 ' + bookingData.end_time);
            const hours = (end - start) / (1000 * 60 * 60);

            let totalAmount = 0;
            let deposit = 0;

            if (!isReservation) {
                const totalBase = basePrice * hours;
                deposit = Math.round(totalBase * (depositPercent / 100));
                const serviceFee = Math.round(totalBase * 0.05);
                totalAmount = deposit + serviceFee;
            }

            bookingData.total_amount = totalAmount;
            bookingData.deposit_amount = deposit;
    
    $.ajax({
        url: '../actions/booking_create_action.php',
        type: 'POST',
                data: bookingData,
                dataType: 'json',
                success: function (result) {
                    if (result.success && result.payment_required && !isReservation) {
                        $.ajax({
                            url: '../actions/payment_init_paystack_action.php',
                            type: 'POST',
                            data: {
                                booking_id: result.booking_id,
                                amount: totalAmount
                            },
        dataType: 'json',
                            success: function (paymentResult) {
                                if (paymentResult.success) {
                                    window.location.href = paymentResult.authorization_url;
                                } else {
                                    showAlert(paymentResult.message || 'Failed to initialize payment', 'danger');
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = 'Pay GH₵' + totalAmount.toLocaleString();
                                }
                            },
                            error: function () {
                                showAlert('Payment initialization failed. Please try again.', 'danger');
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = 'Pay GH₵' + totalAmount.toLocaleString();
                            }
                        });
                    } else if (result.success) {
                        window.location.href = 'booking_confirmation.php?booking_id=' + result.booking_id;
            } else {
                        showAlert(result.message || 'Booking creation failed', 'danger');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = isReservation ? 'Confirm Reservation' : 'Pay GH₵' + totalAmount.toLocaleString();
            }
        },
                error: function () {
            showAlert('An error occurred. Please try again.', 'danger');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = isReservation ? 'Confirm Reservation' : 'Pay GH₵' + totalAmount.toLocaleString();
                }
            });
        }

        function showAlert(message, type) {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.className = 'mt-4 rounded-lg p-4';
            alertDiv.style.background = type === 'success' ? 'rgba(45, 80, 22, 0.1)' : 'rgba(93, 22, 22, 0.1)';
            alertDiv.style.color = type === 'success' ? '#90ee90' : '#ffcccb';
            alertDiv.style.border = type === 'success' ? '1px solid rgba(45, 80, 22, 0.3)' : '1px solid rgba(93, 22, 22, 0.3)';
            alertDiv.innerHTML = message;
            alertDiv.classList.remove('hidden');

            setTimeout(() => {
                alertDiv.classList.add('hidden');
            }, 5000);
        }

        document.getElementById('bookingDate').addEventListener('change', updatePricing);
        document.getElementById('startTime').addEventListener('change', updatePricing);
        document.getElementById('endTime').addEventListener('change', updatePricing);
        document.getElementById('guestCount').addEventListener('change', function () {
            updatePricing();
        });

        document.getElementById('agreeTerms').addEventListener('change', function () {
            document.getElementById('submitBookingBtn').disabled = !this.checked;
        });

        updatePricing();
        changeGuests(0); // Initialize guest buttons
</script>
</body>

</html>