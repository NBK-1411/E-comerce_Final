<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/booking_controller.php');
require_once(__DIR__ . '/../controllers/customer_controller.php');
require_once(__DIR__ . '/../controllers/review_controller.php');
require_once(__DIR__ . '/../controllers/payment_controller.php');

// Check if admin
require_admin();

// Get statistics
$all_venues = get_all_venues_admin_ctr();
$all_bookings = get_all_bookings_ctr();
$all_customers = get_all_customers_ctr();
$all_reviews = get_all_reviews_ctr();
$all_payments = get_all_payments_ctr();

// Calculate metrics
$total_venues = is_array($all_venues) ? count($all_venues) : 0;
$approved_venues = is_array($all_venues) ? count(array_filter($all_venues, function($v) { return $v['status'] == 'approved'; })) : 0;

$total_bookings = is_array($all_bookings) ? count($all_bookings) : 0;
$confirmed_bookings = is_array($all_bookings) ? count(array_filter($all_bookings, function($b) { return $b['status'] == 'confirmed'; })) : 0;

$total_customers = is_array($all_customers) ? count($all_customers) : 0;
$venue_owners = is_array($all_customers) ? count(array_filter($all_customers, function($c) { return $c['user_role'] == 3; })) : 0;

$total_reviews = is_array($all_reviews) ? count($all_reviews) : 0;
$approved_reviews = is_array($all_reviews) ? count(array_filter($all_reviews, function($r) { return $r['moderation_status'] == 'approved'; })) : 0;

$total_revenue = 0;
if (is_array($all_payments)) {
    foreach ($all_payments as $payment) {
        if ($payment['payment_status'] == 'paid') {
            $total_revenue += $payment['amount'];
        }
    }
}

// Recent activities
$recent_bookings = is_array($all_bookings) ? array_slice($all_bookings, 0, 10) : [];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reports & Analytics - Admin - Go Outside</title>
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
.stat-card {
    background: #010101;
    border: 1px solid #3a3a3a;
    border-radius: 10px;
    padding: 25px;
    text-align: center;
    margin-bottom: 20px;
}
.stat-icon {
    font-size: 40px;
    color: #ff5518;
    margin-bottom: 15px;
}
.stat-value {
    font-size: 32px;
    color: #fff;
    font-weight: 700;
    margin-bottom: 5px;
}
.stat-label {
    color: #bcbcbc;
    font-size: 14px;
}
.section-title {
    color: #ff5518;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 20px;
}
.metric-row {
    display: flex;
    justify-content: space-between;
    padding: 15px 0;
    border-bottom: 1px solid #3a3a3a;
}
.metric-label {
    color: #bcbcbc;
    font-size: 14px;
}
.metric-value {
    color: #fff;
    font-weight: 600;
    font-size: 16px;
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
            <i class="fa fa-chart-bar"></i> Reports & Analytics
        </h2>
        <p style="color: #bcbcbc; margin-top: 5px;">Platform statistics and insights</p>
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
        <!-- Summary Stats -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa fa-building"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_venues; ?></div>
                    <div class="stat-label">Total Venues</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa fa-calendar"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_bookings; ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_customers; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa fa-money"></i>
                    </div>
                    <div class="stat-value">GH₵<?php echo number_format($total_revenue, 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Platform Metrics -->
            <div class="col-md-6">
                <div class="content-section">
                    <h3 class="section-title">
                        <i class="fa fa-chart-line"></i> Platform Metrics
                    </h3>
                    
                    <div class="metric-row">
                        <span class="metric-label">Approved Venues</span>
                        <span class="metric-value"><?php echo $approved_venues; ?> / <?php echo $total_venues; ?></span>
                    </div>
                    
                    <div class="metric-row">
                        <span class="metric-label">Confirmed Bookings</span>
                        <span class="metric-value"><?php echo $confirmed_bookings; ?> / <?php echo $total_bookings; ?></span>
                    </div>
                    
                    <div class="metric-row">
                        <span class="metric-label">Venue Owners</span>
                        <span class="metric-value"><?php echo $venue_owners; ?></span>
                    </div>
                    
                    <div class="metric-row">
                        <span class="metric-label">Regular Customers</span>
                        <span class="metric-value"><?php echo $total_customers - $venue_owners; ?></span>
                    </div>
                    
                    <div class="metric-row" style="border-bottom: none;">
                        <span class="metric-label">Approved Reviews</span>
                        <span class="metric-value"><?php echo $approved_reviews; ?> / <?php echo $total_reviews; ?></span>
                    </div>
                </div>
            </div>

            <!-- Growth Indicators -->
            <div class="col-md-6">
                <div class="content-section">
                    <h3 class="section-title">
                        <i class="fa fa-trending-up"></i> Growth Indicators
                    </h3>
                    
                    <div class="metric-row">
                        <span class="metric-label">Average Rating</span>
                        <span class="metric-value" style="color: #ffa500;">
                            <?php 
                            if ($total_reviews > 0 && is_array($all_reviews)) {
                                $total_rating = 0;
                                foreach ($all_reviews as $review) {
                                    $total_rating += $review['rating'];
                                }
                                echo number_format($total_rating / $total_reviews, 1);
                            } else {
                                echo 'N/A';
                            }
                            ?> ★
                        </span>
                    </div>
                    
                    <div class="metric-row">
                        <span class="metric-label">Booking Conversion Rate</span>
                        <span class="metric-value">
                            <?php 
                            if ($total_bookings > 0) {
                                echo number_format(($confirmed_bookings / $total_bookings) * 100, 1);
                            } else {
                                echo '0';
                            }
                            ?>%
                        </span>
                    </div>
                    
                    <div class="metric-row">
                        <span class="metric-label">Venue Approval Rate</span>
                        <span class="metric-value">
                            <?php 
                            if ($total_venues > 0) {
                                echo number_format(($approved_venues / $total_venues) * 100, 1);
                            } else {
                                echo '0';
                            }
                            ?>%
                        </span>
                    </div>
                    
                    <div class="metric-row" style="border-bottom: none;">
                        <span class="metric-label">Average Booking Value</span>
                        <span class="metric-value">
                            GH₵<?php 
                            if ($confirmed_bookings > 0 && $total_revenue > 0) {
                                echo number_format($total_revenue / $confirmed_bookings, 2);
                            } else {
                                echo '0.00';
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings Activity -->
        <div class="content-section">
            <h3 class="section-title">
                <i class="fa fa-clock"></i> Recent Bookings Activity
            </h3>
            
            <?php if (empty($recent_bookings)): ?>
                <p style="color: #bcbcbc; text-align: center; padding: 20px;">No bookings yet</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #010101; border-bottom: 2px solid #3a3a3a;">
                                <th style="padding: 12px; text-align: left; color: #ff5518;">Date</th>
                                <th style="padding: 12px; text-align: left; color: #ff5518;">Customer</th>
                                <th style="padding: 12px; text-align: left; color: #ff5518;">Venue</th>
                                <th style="padding: 12px; text-align: left; color: #ff5518;">Status</th>
                                <th style="padding: 12px; text-align: right; color: #ff5518;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $booking): ?>
                            <tr style="border-bottom: 1px solid #3a3a3a;">
                                <td style="padding: 12px; color: #bcbcbc;">
                                    <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?>
                                </td>
                                <td style="padding: 12px; color: #bcbcbc;">
                                    <?php echo htmlspecialchars($booking['customer_name']); ?>
                                </td>
                                <td style="padding: 12px; color: #fff;">
                                    <?php echo htmlspecialchars($booking['venue_title']); ?>
                                </td>
                                <td style="padding: 12px;">
                                    <span style="padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: 600;
                                          background: <?php echo $booking['status'] == 'confirmed' ? '#2d5016' : '#5d4a1f'; ?>;
                                          color: <?php echo $booking['status'] == 'confirmed' ? '#90ee90' : '#ffd700'; ?>;">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; color: #fff; text-align: right;">
                                    GH₵<?php echo number_format($booking['total_amount'], 2); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
            </div>
        </div>
    </div>
</section>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
</body>
</html>

