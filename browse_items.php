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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .navbar {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .navbar h1 {
            font-size: 24px;
        }
        .navbar-right {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .navbar a:hover {
            background: rgba(255,255,255,0.2);
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .filter-row {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .filter-row input,
        .filter-row select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .filter-row input {
            flex: 1;
        }
        .filter-row select {
            min-width: 200px;
        }
        .filter-row button {
            padding: 10px 25px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .filter-row button:hover {
            background: #5568d3;
        }
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .item-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .item-header {
            margin-bottom: 15px;
        }
        .item-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .owner-name {
            color: #888;
            font-size: 14px;
        }
        .category-badge {
            display: inline-block;
            background: #e8eaf6;
            color: #667eea;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            margin-top: 10px;
        }
        .item-details {
            margin: 15px 0;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }
        .price-label {
            color: #888;
            font-size: 14px;
        }
        .price-value {
            font-weight: bold;
            color: #667eea;
            font-size: 16px;
        }
        .rent-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
            transition: background 0.3s;
        }
        .rent-btn:hover {
            background: #218838;
        }
        .no-items {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            color: #999;
        }
        h2 {
            margin-bottom: 20px;
        }
    </style>
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