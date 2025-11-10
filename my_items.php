<?php
// my_items.php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Handle delete
if (isset($_GET['delete'])) {
    $item_id = intval($_GET['delete']);
    
    // Check if item is currently rented
    $check_query = "SELECT * FROM RENTALS WHERE IID = $item_id AND STATUS = 'ONGOING'";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        $error = "Cannot delete item while it's being rented!";
    } else {
        $delete_query = "DELETE FROM ITEMS WHERE IID = $item_id AND UID = $user_id";
        if ($conn->query($delete_query)) {
            $success = "Item deleted successfully!";
        }
    }
}

// Get user's items
$query = "SELECT * FROM ITEMS WHERE UID = $user_id ORDER BY IID DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Items - Rental System</title>
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
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .add-btn {
            background: #667eea;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .add-btn:hover {
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
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        .item-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .category-badge {
            background: #e8eaf6;
            color: #667eea;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }
        .item-details {
            margin: 15px 0;
            color: #666;
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
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        .available {
            background: #d4edda;
            color: #155724;
        }
        .rented {
            background: #fff3cd;
            color: #856404;
        }
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn {
            flex: 1;
            padding: 8px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: opacity 0.3s;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .btn-edit {
            background: #667eea;
            color: white;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background: #efe;
            color: #3c3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .no-items {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            color: #999;
        }
        .no-items h3 {
            margin-bottom: 20px;
            color: #666;
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
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="header">
            <h2>My Items</h2>
            <a href="add_item.php" class="add-btn">➕ Add New Item</a>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="items-grid">
                <?php while ($item = $result->fetch_assoc()): ?>
                    <div class="item-card">
                        <div class="item-header">
                            <div class="item-name"><?php echo $item['INAME']; ?></div>
                            <div class="category-badge"><?php echo $item['CATEGORY']; ?></div>
                        </div>
                        
                        <div class="item-details">
                            <div class="price-row">
                                <span class="price-label">Rent/Day:</span>
                                <span class="price-value">₹<?php echo number_format($item['RENT_PER_DAY'], 2); ?></span>
                            </div>
                            <div class="price-row">
                                <span class="price-label">Penalty/Day:</span>
                                <span class="price-value">₹<?php echo number_format($item['PENALTY_PER_DAY'], 2); ?></span>
                            </div>
                        </div>
                        
                        <span class="status <?php echo $item['IS_AVAILABLE'] ? 'available' : 'rented'; ?>">
                            <?php echo $item['IS_AVAILABLE'] ? '✓ Available' : '✗ Rented Out'; ?>
                        </span>
                        
                        <div class="actions">
                            <a href="edit_item.php?id=<?php echo $item['IID']; ?>" class="btn btn-edit">Edit</a>
                            <a href="my_items.php?delete=<?php echo $item['IID']; ?>" 
                               class="btn btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-items">
                <h3>No items yet</h3>
                <p>Start by adding your first item to rent out!</p>
                <br>
                <a href="add_item.php" class="add-btn">Add Your First Item</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>