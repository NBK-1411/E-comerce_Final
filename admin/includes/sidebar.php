<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Get pending counts
$pending_venues = isset($pending_venues) ? $pending_venues : 0;
$pending_review_count = isset($pending_review_count) ? $pending_review_count : 0;
?>
<style>
.admin-sidebar {
    background: #010101;
    border: 1px solid #3a3a3a;
    border-radius: 10px;
    padding: 20px;
    transition: all 0.3s ease;
    position: relative;
}
.admin-sidebar.collapsed {
    width: 80px;
    padding: 20px 10px;
}
.admin-sidebar.collapsed .menu-text,
.admin-sidebar.collapsed .menu-title,
.admin-sidebar.collapsed .alert-badge {
    display: none;
}
.admin-sidebar.collapsed .admin-nav a {
    text-align: center;
    padding: 12px 10px;
}
.sidebar-toggle {
    position: absolute;
    top: 15px;
    right: -15px;
    background: #ff5518;
    color: #fff;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    transition: all 0.3s;
}
.sidebar-toggle:hover {
    background: #ff6a2e;
}
.admin-nav a {
    color: #bcbcbc;
    display: flex;
    align-items: center;
    padding: 12px 15px;
    margin-bottom: 5px;
    border-radius: 5px;
    text-decoration: none;
    transition: all 0.3s;
}
.admin-nav a i {
    min-width: 20px;
    text-align: center;
}
.admin-nav a .menu-text {
    margin-left: 10px;
}
.admin-nav a:hover {
    background: #1e1e1e;
    color: #ff5518;
}
.admin-nav a.active {
    background: #ff5518;
    color: #fff;
}
.alert-badge {
    background: #ff5518;
    color: #fff;
    padding: 3px 10px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
    margin-left: auto;
}
</style>

<div class="admin-sidebar" id="adminSidebar">
    <button class="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
        <i class="fa fa-bars"></i>
    </button>
    
    <h4 class="menu-title" style="color: #fff; margin-bottom: 15px;">
        <i class="fa fa-bars"></i> Admin Menu
    </h4>
    
    <div class="admin-nav">
        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa fa-dashboard"></i>
            <span class="menu-text">Dashboard</span>
        </a>
        
        <a href="venues.php" class="<?php echo $current_page == 'venues.php' ? 'active' : ''; ?>">
            <i class="fa fa-building"></i>
            <span class="menu-text">Venues</span>
            <?php if ($pending_venues > 0): ?>
                <span class="alert-badge"><?php echo $pending_venues; ?></span>
            <?php endif; ?>
        </a>
        
        <a href="category.php" class="<?php echo $current_page == 'category.php' ? 'active' : ''; ?>">
            <i class="fa fa-tags"></i>
            <span class="menu-text">Categories</span>
        </a>
        
        <a href="bookings.php" class="<?php echo $current_page == 'bookings.php' ? 'active' : ''; ?>">
            <i class="fa fa-calendar"></i>
            <span class="menu-text">Bookings</span>
        </a>
        
        <a href="users.php" class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
            <i class="fa fa-users"></i>
            <span class="menu-text">Users</span>
        </a>
        
        <a href="reviews.php" class="<?php echo $current_page == 'reviews.php' ? 'active' : ''; ?>">
            <i class="fa fa-star"></i>
            <span class="menu-text">Reviews</span>
            <?php if ($pending_review_count > 0): ?>
                <span class="alert-badge"><?php echo $pending_review_count; ?></span>
            <?php endif; ?>
        </a>
        
        <a href="reports.php" class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
            <i class="fa fa-chart-bar"></i>
            <span class="menu-text">Reports</span>
        </a>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    sidebar.classList.toggle('collapsed');
    
    // Save state to localStorage
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

// Restore sidebar state on page load
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
    }
});
</script>

