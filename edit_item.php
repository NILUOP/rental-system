<?php
// edit_item.php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if (!isset($_GET['id'])) {
    header("Location: my_items.php");
    exit();
}

$item_id = intval($_GET['id']);

// Get item details
$query = "SELECT * FROM ITEMS WHERE IID = $item_id AND UID = $user_id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    header("Location: my_items.php");
    exit();
}

$item = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $iname = sanitize($_POST['iname']);
    $category = sanitize($_POST['category']);
    $rent_per_day = floatval($_POST['rent_per_day']);
    $penalty_per_day = floatval($_POST['penalty_per_day']);
    
    if (empty($iname) || empty($category) || $rent_per_day <= 0 || $penalty_per_day <= 0) {
        $error = "All fields are required and prices must be greater than 0!";
    } else {
        $update_query = "UPDATE ITEMS 
                        SET INAME = '$iname', 
                            CATEGORY = '$category', 
                            RENT_PER_DAY = $rent_per_day, 
                            PENALTY_PER_DAY = $penalty_per_day 
                        WHERE IID = $item_id AND UID = $user_id";
        
        if ($conn->query($update_query)) {
            $success = "Item updated successfully!";
            // Refresh item data
            $result = $conn->query($query);
            $item = $result->fetch_assoc();
        } else {
            $error = "Failed to update item: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - Rental System</title>
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
        <a href="my_items.php" class="back-link">← Back to My Items</a>
        
        <div class="form-container">
            <h2>Edit Item</h2>
            
            <?php if ($item['IS_AVAILABLE'] == 0): ?>
                <div class="warning-box">
                    ⚠ <strong>Note:</strong> This item is currently rented out. You can still edit details, but changes won't affect the ongoing rental.
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="iname" value="<?php echo htmlspecialchars($item['INAME']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="Electronics" <?php echo $item['CATEGORY'] == 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                        <option value="Cameras" <?php echo $item['CATEGORY'] == 'Cameras' ? 'selected' : ''; ?>>Cameras</option>
                        <option value="Tools" <?php echo $item['CATEGORY'] == 'Tools' ? 'selected' : ''; ?>>Tools</option>
                        <option value="Sports" <?php echo $item['CATEGORY'] == 'Sports' ? 'selected' : ''; ?>>Sports Equipment</option>
                        <option value="Furniture" <?php echo $item['CATEGORY'] == 'Furniture' ? 'selected' : ''; ?>>Furniture</option>
                        <option value="Vehicles" <?php echo $item['CATEGORY'] == 'Vehicles' ? 'selected' : ''; ?>>Vehicles</option>
                        <option value="Books" <?php echo $item['CATEGORY'] == 'Books' ? 'selected' : ''; ?>>Books</option>
                        <option value="Other" <?php echo $item['CATEGORY'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Rent Per Day (₹) *</label>
                    <input type="number" name="rent_per_day" step="0.01" min="0.01" value="<?php echo $item['RENT_PER_DAY']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Penalty Per Day (₹) *</label>
                    <input type="number" name="penalty_per_day" step="0.01" min="0.01" value="<?php echo $item['PENALTY_PER_DAY']; ?>" required>
                    <small style="color: #666; font-size: 12px;">Charged for late returns</small>
                </div>
                
                <button type="submit">💾 Update Item</button>
            </form>
        </div>
    </div>
</body>
</html>