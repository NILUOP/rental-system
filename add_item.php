<?php
// add_item.php
require_once 'config.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $iname = sanitize($_POST['iname']);
    $category = sanitize($_POST['category']);
    $rent_per_day = floatval($_POST['rent_per_day']);
    $penalty_per_day = floatval($_POST['penalty_per_day']);
    $user_id = $_SESSION['user_id'];
    
    if (empty($iname) || empty($category) || $rent_per_day <= 0 || $penalty_per_day <= 0) {
        $error = "All fields are required and prices must be greater than 0!";
    } else {
        $query = "INSERT INTO ITEMS (INAME, UID, CATEGORY, RENT_PER_DAY, PENALTY_PER_DAY, IS_AVAILABLE) 
                  VALUES ('$iname', $user_id, '$category', $rent_per_day, $penalty_per_day, 1)";
        
        if ($conn->query($query)) {
            $success = "Item added successfully!";
        } else {
            $error = "Failed to add item: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item - Rental System</title>
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
            <h2>Add New Item</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="iname" required placeholder="e.g., Canon EOS 5D Camera">
                </div>
                
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Cameras">Cameras</option>
                        <option value="Tools">Tools</option>
                        <option value="Sports">Sports Equipment</option>
                        <option value="Furniture">Furniture</option>
                        <option value="Vehicles">Vehicles</option>
                        <option value="Books">Books</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Rent Per Day (₹) *</label>
                    <input type="number" name="rent_per_day" step="0.01" min="0.01" required placeholder="e.g., 500.00">
                </div>
                
                <div class="form-group">
                    <label>Penalty Per Day (₹) *</label>
                    <input type="number" name="penalty_per_day" step="0.01" min="0.01" required placeholder="e.g., 100.00">
                    <small style="color: #666; font-size: 12px;">Charged for late returns</small>
                </div>
                
                <button type="submit">Add Item</button>
            </form>
        </div>
    </div>
</body>
</html>