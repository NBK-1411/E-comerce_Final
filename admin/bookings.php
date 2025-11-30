<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/booking_controller.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/review_controller.php');

// Check if admin
require_admin();

// Get all bookings
$all_bookings = get_all_bookings_ctr();
if ($all_bookings === false) $all_bookings = [];

// Get pending counts for sidebar
$all_venues = get_all_venues_admin_ctr();
$pending_venues = is_array($all_venues) ? count(array_filter($all_venues, function($v) { return $v['status'] == 'pending'; })) : 0;
$pending_reviews = get_pending_reviews_ctr();
$pending_review_count = is_array($pending_reviews) ? count($pending_reviews) : 0;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bookings Management - Admin - Go Outside</title>
<link rel="stylesheet" href="../css/bootstrap.min.css">
<link rel="stylesheet" href="../css/main.css"> 
<link rel="stylesheet" href="../css/responsive.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<style>
.admin-header {
    background: #1e1e1e;
    border-bottom: 1px solid #3a3a3a;
    padding: 20px 0;
}
.content-section {
    background: #1e1e1e;
    border: 1px solid #3a3a3a;
    border-radius: 10px;
    padding: 25px;
    margin-bottom: 20px;
}
.booking-table {
    width: 100%;
    border-collapse: collapse;
}
.booking-table th {
    background: #010101;
    color: #ff5518;
    padding: 15px;
    text-align: left;
    border-bottom: 2px solid #3a3a3a;
}
.booking-table td {
    padding: 15px;
    border-bottom: 1px solid #3a3a3a;
    color: #bcbcbc;
}
.booking-table tr:hover {
    background: #010101;
}
.status-badge {
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
}
.status-pending {
    background: #5d4a1f;
    color: #ffd700;
}
.status-confirmed {
    background: #2d5016;
    color: #90ee90;
}
.status-cancelled {
    background: #5d1616;
    color: #ffcccb;
}
.status-completed {
    background: #1e3a5f;
    color: #87ceeb;
}
.filter-bar {
    background: #010101;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 9999;
    overflow-y: auto;
}
.modal-content {
    max-width: 700px;
    margin: 50px auto;
    background: #1e1e1e;
    border: 1px solid #3a3a3a;
    border-radius: 10px;
    padding: 30px;
}
</style>
</head>

<body>
<!-- Navigation -->
<header id="header" style="background: #010101; position: relative;">
    <div class="header-content clearfix" style="padding: 20px 0;"> 
        <span class="logo"><a href="../index.php">GO<b>OUTSIDE</b></a></span>
        <nav class="navigation" role="navigation">
            <ul class="primary-nav">
                <li><a href="../index.php">Home</a></li>
                <li><a href="dashboard.php">Admin</a></li>
                <li><a href="../actions/logout_action.php">Logout</a></li>
            </ul>
        </nav>
        <a href="#" class="nav-toggle">Menu<span></span></a> 
    </div>
</header>

<div class="admin-header">
    <div class="container">
        <h2 style="color: #fff; margin: 0;">
            <i class="fa fa-calendar"></i> Bookings Management
        </h2>
        <p style="color: #bcbcbc; margin-top: 5px;">View and manage all venue bookings</p>
    </div>
</div>

<section class="section" style="background: #010101; padding: 40px 0;">
    <div class="container">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-3">
                <?php include 'includes/sidebar.php'; ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
        <div class="content-section">
            <!-- Alert Message -->
            <div id="alertMessage" style="display: none; margin-bottom: 20px;"></div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="row">
                    <div class="col-md-4">
                        <select id="statusFilter" class="form-control" style="background: #1e1e1e; border: 1px solid #3a3a3a; color: #fff;">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search bookings..." 
                               style="background: #1e1e1e; border: 1px solid #3a3a3a; color: #fff;">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-large" style="width: 100%;" onclick="filterBookings()">
                            <i class="fa fa-filter"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bookings Table -->
            <div style="overflow-x: auto;">
                <table class="booking-table" id="bookingsTable">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Venue</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_bookings)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #3a3a3a;">
                                    <i class="fa fa-calendar" style="font-size: 48px; display: block; margin-bottom: 10px;"></i>
                                    No bookings found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_bookings as $booking): ?>
                            <tr data-status="<?php echo $booking['status']; ?>">
                                <td>
                                    <strong style="color: #fff;">#<?php echo $booking['booking_id']; ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['venue_title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                <td><?php echo $booking['start_time'] . ' - ' . $booking['end_time']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-small" style="padding: 5px 10px; font-size: 12px; margin-right: 5px;"
                                            onclick="viewBooking(<?php echo $booking['booking_id']; ?>)">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <?php if ($booking['status'] == 'requested'): ?>
                                        <button class="btn btn-small" style="padding: 5px 10px; font-size: 12px; background: #2d5016; margin-right: 5px;"
                                                onclick="updateBookingStatus(<?php echo $booking['booking_id']; ?>, 'confirmed')">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button class="btn btn-small" style="padding: 5px 10px; font-size: 12px; background: #5d1616;"
                                                onclick="updateBookingStatus(<?php echo $booking['booking_id']; ?>, 'cancelled')">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
            </div>
        </div>
    </div>
</section>

<!-- Booking Details Modal -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="color: #fff; margin: 0; font-size: 24px; font-weight: 700;">
                <i class="fa fa-calendar"></i> Booking Details
            </h3>
            <button onclick="closeBookingModal()" style="background: none; border: none; color: #bcbcbc; font-size: 24px; cursor: pointer;">
                <i class="fa fa-times"></i>
            </button>
        </div>
        
        <div id="bookingDetails">
            <div style="text-align: center; padding: 40px; color: #bcbcbc;">
                <i class="fa fa-spinner fa-spin" style="font-size: 48px;"></i>
                <p style="margin-top: 15px;">Loading...</p>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script>
// Show alert message
function showAlert(message, type) {
    const alertDiv = $('#alertMessage');
    alertDiv.css({
        'padding': '15px',
        'border-radius': '8px',
        'margin-bottom': '20px',
        'background': type === 'success' ? '#2d5016' : '#5d1616',
        'color': type === 'success' ? '#90ee90' : '#ffcccb',
        'border': type === 'success' ? '1px solid #4caf50' : '1px solid #f44336'
    });
    alertDiv.html('<i class="fa fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message);
    alertDiv.show();
    
    setTimeout(function() {
        alertDiv.fadeOut();
    }, 5000);
}

// View booking details
function viewBooking(bookingId) {
    $('#bookingModal').fadeIn();
    $('#bookingDetails').html('<div style="text-align: center; padding: 40px; color: #bcbcbc;"><i class="fa fa-spinner fa-spin" style="font-size: 48px;"></i><p style="margin-top: 15px;">Loading...</p></div>');
    
    $.ajax({
        url: '../actions/booking_get_details_action.php?booking_id=' + bookingId,
        type: 'GET',
        dataType: 'json',
        success: function(result) {
            if (result.success) {
                const booking = result.data;
                const statusClass = booking.status === 'confirmed' ? 'status-confirmed' : 
                                  booking.status === 'cancelled' ? 'status-cancelled' : 
                                  booking.status === 'completed' ? 'status-completed' : 'status-pending';
                
                $('#bookingDetails').html(`
                    <div style="background: #010101; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4 style="color: #fff; margin: 0;">Booking #${booking.booking_id}</h4>
                            <span class="status-badge ${statusClass}">${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}</span>
                        </div>
                        
                        <div style="color: #bcbcbc; line-height: 2;">
                            <p><strong style="color: #fff;">Customer:</strong> ${booking.customer_name}</p>
                            <p><strong style="color: #fff;">Venue:</strong> ${booking.venue_title}</p>
                            <p><strong style="color: #fff;">Date:</strong> ${new Date(booking.booking_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</p>
                            <p><strong style="color: #fff;">Time:</strong> ${booking.start_time} - ${booking.end_time}</p>
                            <p><strong style="color: #fff;">Guests:</strong> ${booking.guest_count || 'N/A'}</p>
                            <p><strong style="color: #fff;">Total Amount:</strong> GH₵${parseFloat(booking.total_amount).toFixed(2)}</p>
                            ${booking.special_requests ? `<p><strong style="color: #fff;">Special Requests:</strong> ${booking.special_requests}</p>` : ''}
                            <p><strong style="color: #fff;">Booked on:</strong> ${new Date(booking.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                        </div>
                    </div>
                    
                    <div style="text-align: right;">
                        <button onclick="closeBookingModal()" class="btn btn-large" style="background: #3a3a3a;">
                            Close
                        </button>
                    </div>
                `);
            } else {
                $('#bookingDetails').html('<div style="text-align: center; padding: 40px; color: #ffcccb;"><i class="fa fa-exclamation-circle" style="font-size: 48px;"></i><p style="margin-top: 15px;">' + result.message + '</p></div>');
            }
        },
        error: function() {
            $('#bookingDetails').html('<div style="text-align: center; padding: 40px; color: #ffcccb;"><i class="fa fa-exclamation-circle" style="font-size: 48px;"></i><p style="margin-top: 15px;">Failed to load booking details</p></div>');
        }
    });
}

// Close booking modal
function closeBookingModal() {
    $('#bookingModal').fadeOut();
}

// Update booking status
function updateBookingStatus(bookingId, status) {
    const action = status === 'confirmed' ? 'confirm' : 'cancel';
    if (!confirm('Are you sure you want to ' + action + ' this booking?')) {
        return;
    }
    
    $.ajax({
        url: '../actions/booking_update_status_action.php',
        type: 'POST',
        data: {
            booking_id: bookingId,
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
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });
}

// Filter bookings
function filterBookings() {
    const statusFilter = $('#statusFilter').val().toLowerCase();
    const searchText = $('#searchInput').val().toLowerCase();
    
    $('#bookingsTable tbody tr').each(function() {
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
$('#searchInput').on('keyup', filterBookings);
$('#statusFilter').on('change', filterBookings);
</script>
</body>
</html>

