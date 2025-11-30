<?php
require_once(__DIR__ . '/../settings/core.php');
require_once(__DIR__ . '/../controllers/customer_controller.php');
require_once(__DIR__ . '/../controllers/venue_controller.php');
require_once(__DIR__ . '/../controllers/review_controller.php');

// Check if admin
require_admin();

// Get all users
$all_users = get_all_customers_ctr();
if ($all_users === false) $all_users = [];

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
<title>User Management - Admin - Go Outside</title>
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
.user-table {
    width: 100%;
    border-collapse: collapse;
}
.user-table th {
    background: #010101;
    color: #ff5518;
    padding: 15px;
    text-align: left;
    border-bottom: 2px solid #3a3a3a;
}
.user-table td {
    padding: 15px;
    border-bottom: 1px solid #3a3a3a;
    color: #bcbcbc;
}
.user-table tr:hover {
    background: #010101;
}
.role-badge {
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
}
.role-admin {
    background: #ff5518;
    color: #fff;
}
.role-customer {
    background: #2d5016;
    color: #90ee90;
}
.role-owner {
    background: #5d4a1f;
    color: #ffd700;
}
.verified-badge {
    color: #90ee90;
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
            <i class="fa fa-users"></i> User Management
        </h2>
        <p style="color: #bcbcbc; margin-top: 5px;">Manage all registered users</p>
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
            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="row">
                    <div class="col-md-4">
                        <select id="roleFilter" class="form-control" style="background: #1e1e1e; border: 1px solid #3a3a3a; color: #fff;">
                            <option value="">All Roles</option>
                            <option value="1">Admins</option>
                            <option value="2">Customers</option>
                            <option value="3">Venue Owners</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search users..." 
                               style="background: #1e1e1e; border: 1px solid #3a3a3a; color: #fff;">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-large" style="width: 100%;" onclick="filterUsers()">
                            <i class="fa fa-filter"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alert Message -->
            <div id="alertMessage" style="display: none; margin-bottom: 20px;"></div>

            <!-- Users Table -->
            <div style="overflow-x: auto;">
                <table class="user-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Location</th>
                            <th>Role</th>
                            <th>Verified</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_users)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #3a3a3a;">
                                    <i class="fa fa-users" style="font-size: 48px; display: block; margin-bottom: 10px;"></i>
                                    No users found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_users as $user): 
                                $role_class = $user['user_role'] == 1 ? 'admin' : ($user['user_role'] == 3 ? 'owner' : 'customer');
                                $role_name = $user['user_role'] == 1 ? 'Admin' : ($user['user_role'] == 3 ? 'Venue Owner' : 'Customer');
                            ?>
                            <tr data-role="<?php echo $user['user_role']; ?>">
                                <td>
                                    <strong style="color: #fff;"><?php echo htmlspecialchars($user['customer_name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($user['customer_email']); ?></td>
                                <td><?php echo htmlspecialchars($user['customer_city'] . ', ' . $user['customer_country']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $role_class; ?>">
                                        <?php echo $role_name; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['verified']): ?>
                                        <i class="fa fa-check-circle verified-badge"></i> Yes
                                    <?php else: ?>
                                        <i class="fa fa-times-circle" style="color: #777;"></i> No
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-small" style="padding: 5px 10px; font-size: 12px; margin-right: 5px;"
                                            onclick='viewUser(<?php echo json_encode($user); ?>)'>
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                    <?php if ($user['user_role'] != 1): // Don't show delete for admins ?>
                                        <button class="btn btn-small" style="padding: 5px 10px; font-size: 12px; background: #5d1616;"
                                                onclick="deleteUser(<?php echo $user['customer_id']; ?>, '<?php echo htmlspecialchars($user['customer_name'], ENT_QUOTES); ?>')">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    <?php else: ?>
                                        <span style="color: #777; font-size: 11px; font-style: italic;">
                                            <i class="fa fa-shield"></i> Protected
                                        </span>
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

<!-- User Details Modal -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="color: #fff; margin: 0; font-size: 24px; font-weight: 700;">
                <i class="fa fa-user"></i> User Details
            </h3>
            <button onclick="closeUserModal()" style="background: none; border: none; color: #bcbcbc; font-size: 24px; cursor: pointer;">
                <i class="fa fa-times"></i>
            </button>
        </div>
        
        <div id="userDetails"></div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script>
// Show alert message
function showAlert(message, type) {
    const alertDiv = $('#alertMessage');
    alertDiv.removeClass('alert-success alert-danger');
    alertDiv.css({
        'padding': '15px',
        'border-radius': '8px',
        'background': type === 'success' ? '#2d5016' : '#5d1616',
        'color': type === 'success' ? '#90ee90' : '#ffcccb',
        'border': type === 'success' ? '1px solid #4caf50' : '1px solid #f44336'
    });
    alertDiv.html(message);
    alertDiv.show();
    
    $('html, body').animate({ scrollTop: 0 }, 'slow');
    
    setTimeout(function() {
        alertDiv.fadeOut();
    }, 5000);
}

// Delete user
function deleteUser(userId, userName) {
    if (!confirm('Are you sure you want to delete user "' + userName + '"?\n\nThis action cannot be undone and will remove:\n- User account\n- All their bookings\n- All their reviews\n- All their venues (if venue owner)')) {
        return;
    }
    
    // Double confirmation for safety
    if (!confirm('FINAL CONFIRMATION: Delete "' + userName + '" permanently?')) {
        return;
    }
    
    $.ajax({
        url: '../actions/user_delete_action.php',
        type: 'POST',
        data: {
            user_id: userId
        },
        dataType: 'json',
        success: function(result) {
            if (result.success) {
                showAlert('<i class="fa fa-check-circle"></i> ' + result.message, 'success');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                showAlert('<i class="fa fa-exclamation-circle"></i> ' + result.message, 'danger');
            }
        },
        error: function() {
            showAlert('<i class="fa fa-exclamation-circle"></i> An error occurred. Please try again.', 'danger');
        }
    });
}

// View user details
function viewUser(user) {
    const roleClass = user.user_role == 1 ? 'role-admin' : (user.user_role == 3 ? 'role-owner' : 'role-customer');
    const roleName = user.user_role == 1 ? 'Admin' : (user.user_role == 3 ? 'Venue Owner' : 'Customer');
    const verifiedIcon = user.verified ? '<i class="fa fa-check-circle" style="color: #90ee90;"></i> Verified' : '<i class="fa fa-times-circle" style="color: #777;"></i> Not Verified';
    
    $('#userDetails').html(`
        <div style="background: #010101; padding: 25px; border-radius: 8px; margin-bottom: 20px;">
            <div style="text-align: center; margin-bottom: 25px;">
                <div style="width: 100px; height: 100px; border-radius: 50%; background: #3a3a3a; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <i class="fa fa-user" style="font-size: 48px; color: #bcbcbc;"></i>
                </div>
                <h4 style="color: #fff; margin-bottom: 10px;">${user.customer_name}</h4>
                <span class="role-badge ${roleClass}">${roleName}</span>
            </div>
            
            <div style="color: #bcbcbc; line-height: 2.2;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                    <div>
                        <p><strong style="color: #ff5518;">Email:</strong></p>
                        <p style="margin-top: -10px;">${user.customer_email}</p>
                    </div>
                    <div>
                        <p><strong style="color: #ff5518;">Contact:</strong></p>
                        <p style="margin-top: -10px;">${user.customer_contact || 'N/A'}</p>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                    <div>
                        <p><strong style="color: #ff5518;">City:</strong></p>
                        <p style="margin-top: -10px;">${user.customer_city}</p>
                    </div>
                    <div>
                        <p><strong style="color: #ff5518;">Country:</strong></p>
                        <p style="margin-top: -10px;">${user.customer_country}</p>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                    <div>
                        <p><strong style="color: #ff5518;">Status:</strong></p>
                        <p style="margin-top: -10px;">${verifiedIcon}</p>
                    </div>
                    <div>
                        <p><strong style="color: #ff5518;">Joined:</strong></p>
                        <p style="margin-top: -10px;">${new Date(user.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</p>
                    </div>
                </div>
                
                ${user.customer_image ? `
                    <div style="margin-top: 20px;">
                        <p><strong style="color: #ff5518;">Profile Image:</strong></p>
                        <img src="../${user.customer_image}" alt="Profile" style="max-width: 150px; border-radius: 8px; margin-top: 10px;">
                    </div>
                ` : ''}
            </div>
        </div>
        
        <div style="text-align: right;">
            <button onclick="closeUserModal()" class="btn btn-large" style="background: #3a3a3a;">
                Close
            </button>
        </div>
    `);
    
    $('#userModal').fadeIn();
}

// Close user modal
function closeUserModal() {
    $('#userModal').fadeOut();
}

// Filter users
function filterUsers() {
    const roleFilter = $('#roleFilter').val();
    const searchText = $('#searchInput').val().toLowerCase();
    
    $('#usersTable tbody tr').each(function() {
        const row = $(this);
        const role = row.data('role');
        const text = row.text().toLowerCase();
        
        let showRow = true;
        
        if (roleFilter && role != roleFilter) {
            showRow = false;
        }
        
        if (searchText && !text.includes(searchText)) {
            showRow = false;
        }
        
        row.toggle(showRow);
    });
}

// Real-time search
$('#searchInput').on('keyup', filterUsers);
$('#roleFilter').on('change', filterUsers);
</script>
</body>
</html>

