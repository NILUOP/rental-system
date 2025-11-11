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