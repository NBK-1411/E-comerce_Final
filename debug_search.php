<?php
/**
 * Debug Search - Check what venues are available and test search
 */
require_once(__DIR__ . '/settings/core.php');
require_once(__DIR__ . '/controllers/venue_controller.php');
require_once(__DIR__ . '/controllers/category_controller.php');

echo "<h2>Search Debug Tool</h2>";
echo "<style>body{background:#1e1e1e;color:#fff;font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #3a3a3a;padding:10px;text-align:left;} th{background:#ff5518;color:#fff;} tr:nth-child(even){background:#2a2a2a;} .success{color:#90ee90;} .warning{color:#ffa500;} .error{color:#ff5555;}</style>";

// 1. Check all approved venues
echo "<h3>1. All Approved Venues in Database:</h3>";
$all_approved = get_all_approved_venues_ctr();
if ($all_approved && count($all_approved) > 0) {
    echo "<p class='success'>✓ Found " . count($all_approved) . " approved venue(s)</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Title</th><th>Category</th><th>Location</th><th>GPS Code</th><th>Status</th></tr>";
    foreach ($all_approved as $venue) {
        echo "<tr>";
        echo "<td>" . $venue['venue_id'] . "</td>";
        echo "<td>" . htmlspecialchars($venue['title']) . "</td>";
        echo "<td>" . htmlspecialchars($venue['cat_name'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($venue['location_text']) . "</td>";
        echo "<td>" . htmlspecialchars($venue['gps_code']) . "</td>";
        echo "<td>" . htmlspecialchars($venue['status']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>✗ No approved venues found in database!</p>";
    echo "<p>This means:</p>";
    echo "<ul>";
    echo "<li>Either no venues have been added yet</li>";
    echo "<li>Or existing venues haven't been approved by admin</li>";
    echo "</ul>";
}

// 2. Test search with empty filters (should return all approved)
echo "<h3>2. Test Search with Empty Filters:</h3>";
$search_empty = search_venues_ctr([]);
if ($search_empty && count($search_empty) > 0) {
    echo "<p class='success'>✓ Search with no filters returns " . count($search_empty) . " venue(s)</p>";
} else {
    echo "<p class='error'>✗ Search with no filters returns nothing</p>";
}

// 3. Test search with location
if ($all_approved && count($all_approved) > 0) {
    echo "<h3>3. Test Location Search:</h3>";
    $test_location = $all_approved[0]['location_text'];
    $search_word = explode(' ', $test_location)[0]; // Get first word
    
    echo "<p>Testing search for: '<strong>" . htmlspecialchars($search_word) . "</strong>'</p>";
    
    $search_location = search_venues_ctr(['location' => $search_word]);
    if ($search_location && count($search_location) > 0) {
        echo "<p class='success'>✓ Found " . count($search_location) . " venue(s)</p>";
    } else {
        echo "<p class='warning'>⚠ Location search returned no results</p>";
        echo "<p>Try searching for these locations:</p>";
        echo "<ul>";
        foreach (array_slice($all_approved, 0, 5) as $v) {
            echo "<li>" . htmlspecialchars($v['location_text']) . "</li>";
        }
        echo "</ul>";
    }
}

// 4. Check categories
echo "<h3>4. Available Categories:</h3>";
$categories = get_all_categories_ctr();
if ($categories && count($categories) > 0) {
    echo "<p class='success'>✓ Found " . count($categories) . " categorie(s)</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Venues Count</th></tr>";
    foreach ($categories as $cat) {
        $cat_venues = search_venues_ctr(['category' => $cat['cat_id']]);
        $count = $cat_venues ? count($cat_venues) : 0;
        echo "<tr>";
        echo "<td>" . $cat['cat_id'] . "</td>";
        echo "<td>" . htmlspecialchars($cat['cat_name']) . "</td>";
        echo "<td>" . $count . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>✗ No categories found</p>";
}

// 5. Recommendations
echo "<h3>5. Recommendations:</h3>";
if (!$all_approved || count($all_approved) == 0) {
    echo "<div style='background:#3a3a3a;padding:15px;border-radius:5px;'>";
    echo "<p class='warning'><strong>No approved venues found!</strong></p>";
    echo "<p>To fix this:</p>";
    echo "<ol>";
    echo "<li>Login as a venue owner and create a venue</li>";
    echo "<li>Login as admin at <a href='admin/dashboard.php' style='color:#ff5518;'>admin/dashboard.php</a></li>";
    echo "<li>Go to 'Venues' section</li>";
    echo "<li>Approve the pending venues</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background:#2a4a2a;padding:15px;border-radius:5px;'>";
    echo "<p class='success'><strong>Search is working correctly!</strong></p>";
    echo "<p>When searching:</p>";
    echo "<ul>";
    echo "<li>Make sure to search for partial location names (e.g., 'Accra', 'East', 'Legon')</li>";
    echo "<li>Location search is case-insensitive and searches in both location text and GPS code</li>";
    echo "<li>Leave category as 'All Categories' for broader results</li>";
    echo "<li>If no results, try leaving the location field empty to see all venues</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr style='margin:30px 0;border-color:#3a3a3a;'>";
echo "<p style='text-align:center;'><a href='index.php' style='color:#ff5518;'>← Back to Homepage</a> | <a href='public/search.php' style='color:#ff5518;'>Go to Search Page →</a></p>";
?>

