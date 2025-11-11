<?php
// browse_items.php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get filter parameters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$query = "SELECT I.*, U.UNAME as OWNER_NAME 
          FROM ITEMS I 
          JOIN USERS U ON I.UID = U.UID 
          WHERE I.IS_AVAILABLE = 1 AND I.UID != $user_id";

if (!empty($category)) {
    $query .= " AND I.CATEGORY = '$category'";
}

if (!empty($search)) {
    $query .= " AND I.INAME LIKE '%$search%'";
}

$query .= " ORDER BY I.IID DESC";
$result = $conn->query($query);

// Get categories for filter
$cat_query = "SELECT DISTINCT CATEGORY FROM ITEMS ORDER BY CATEGORY";
$cat_result = $conn->query($cat_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Items - Rental System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <h1>🏠 Rental System</h1>
        <div class="navbar-right">
            <a href="dashboard.php">Dashboard</a>
            <a href="browse_items.php">Browse Items</a>
            <a href="my_items.php">My Items</a>
            <a href="my_rentals.php">My Rentals</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Browse Available Items</h2>
        
        <div class="filters">
            <form method="GET" action="">
                <div class="filter-row">
                    <input type="text" name="search" placeholder="Search items..." value="<?php echo $search; ?>">
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php while ($cat = $cat_result->fetch_assoc()): ?>
                            <option value="<?php echo $cat['CATEGORY']; ?>" 
                                <?php echo $category == $cat['CATEGORY'] ? 'selected' : ''; ?>>
                                <?php echo $cat['CATEGORY']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit">🔍 Search</button>
                    <a href="browse_items.php" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;">Clear</a>
                </div>
            </form>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="items-grid">
                <?php while ($item = $result->fetch_assoc()): ?>
                    <div class="item-card">
                        <div class="item-header">
                            <div class="item-name"><?php echo $item['INAME']; ?></div>
                            <div class="owner-name">Owner: <?php echo $item['OWNER_NAME']; ?></div>
                            <span class="category-badge"><?php echo $item['CATEGORY']; ?></span>
                        </div>
                        
                        <div class="item-details">
                            <div class="price-row">
                                <span class="price-label">Rent per Day:</span>
                                <span class="price-value">₹<?php echo number_format($item['RENT_PER_DAY'], 2); ?></span>
                            </div>
                            <div class="price-row">
                                <span class="price-label">Late Penalty/Day:</span>
                                <span class="price-value">₹<?php echo number_format($item['PENALTY_PER_DAY'], 2); ?></span>
                            </div>
                        </div>
                        
                        <a href="rent_item.php?id=<?php echo $item['IID']; ?>" class="rent-btn">📦 Rent This Item</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-items">
                <h3>No items found</h3>
                <p>Try adjusting your search or filter criteria</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>