<?php
// Bootstrap the application — session, timezone, and DB connection
require_once 'core/core.php';

// ── Route to the appropriate customer view ────────────────────────────────
if (isset($_GET['pro_id'])) {
    // Single product details page
    require_once 'views/single_product.php';
} elseif (isset($_GET['user_query'])) {
    // Search results page
    require_once 'views/search_results.php';
} else {
    // Homepage: featured products, or filtered by ?cat=N or ?brand=N
    require_once 'views/home.php';
}
