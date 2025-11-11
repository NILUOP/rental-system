<?php
// return_item.php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if (!isset($_GET['id'])) {
    header("Location: my_rentals.php");
    exit();
}

$rental_id = intval($_GET['id']);

// Get rental details
$query = "SELECT R.*, I.INAME, I.PENALTY_PER_DAY, U.UNAME as OWNER_NAME 
          FROM RENTALS R 
          JOIN ITEMS I ON R.IID = I.IID 
          JOIN USERS U ON I.UID = U.UID 
          WHERE R.RID = $rental_id AND R.UID = $user_id AND R.STATUS = 'ONGOING'";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    header("Location: my_rentals.php");
    exit();
}

$rental = $result->fetch_assoc();

// Calculate penalty if overdue
$today = new DateTime();
$due_date = new DateTime($rental['DUE_DATE']);
$days_overdue = 0;
$penalty = 0;

if ($today > $due_date) {
    $days_overdue = $today->diff($due_date)->days;
    $penalty = $days_overdue * $rental['PENALTY_PER_DAY'];
}

$final_cost = $rental['TOTAL_COST'] + $penalty;

// Handle return confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $return_date = date('Y-m-d');
    
    $conn->begin_transaction();
    
    try {
        // Update rental
        $update_rental = "UPDATE RENTALS 
                         SET STATUS = 'RETURNED', 
                             RETURN_DATE = '$return_date', 
                             PENALTY = $penalty 
                         WHERE RID = $rental_id";
        $conn->query($update_rental);
        
        // Update item availability
        $update_item = "UPDATE ITEMS SET IS_AVAILABLE = 1 WHERE IID = " . $rental['IID'];
        $conn->query($update_item);
        
        $conn->commit();
        header("Location: my_rentals.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Failed to return item: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Item - Rental System</title>
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
        <a href="my_rentals.php" class="back-link">← Back to My Rentals</a>
        
        <div class="return-container">
            <h2>Return Item</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="item-info">
                <div class="item-name"><?php echo $rental['INAME']; ?></div>
                <div class="owner-name">Owner: <?php echo $rental['OWNER_NAME']; ?></div>
            </div>
            
            <div class="rental-details">
                <div class="detail-row">
                    <span class="detail-label">Rent Date:</span>
                    <span class="detail-value"><?php echo date('M d, Y', strtotime($rental['RENT_DATE'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Due Date:</span>
                    <span class="detail-value"><?php echo date('M d, Y', strtotime($rental['DUE_DATE'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Return Date:</span>
                    <span class="detail-value"><?php echo date('M d, Y'); ?> (Today)</span>
                </div>
            </div>
            
            <?php if ($days_overdue > 0): ?>
                <div class="overdue-warning">
                    <h3>⚠ Item is Overdue!</h3>
                    <p>This item is <strong><?php echo $days_overdue; ?> days overdue</strong>. A late penalty will be applied.</p>
                </div>
            <?php endif; ?>
            
            <div class="cost-breakdown">
                <div class="cost-row">
                    <span>Rental Cost:</span>
                    <span>₹<?php echo number_format($rental['TOTAL_COST'], 2); ?></span>
                </div>
                <?php if ($penalty > 0): ?>
                    <div class="cost-row" style="color: #dc3545;">
                        <span>Late Penalty (<?php echo $days_overdue; ?> days × ₹<?php echo number_format($rental['PENALTY_PER_DAY'], 2); ?>):</span>
                        <span>₹<?php echo number_format($penalty, 2); ?></span>
                    </div>
                <?php endif; ?>
                <div class="cost-total">
                    <div class="cost-row">
                        <span>Total Amount:</span>
                        <span>₹<?php echo number_format($final_cost, 2); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="warning-box">
                <strong>⚠ Please confirm:</strong><br>
                By clicking "Confirm Return", you acknowledge that:
                <ul style="margin: 10px 0 0 20px;">
                    <li>The item has been returned to the owner</li>
                    <li>You agree to pay the total amount of ₹<?php echo number_format($final_cost, 2); ?></li>
                    <li>This action cannot be undone</li>
                </ul>
            </div>
            
            <form method="POST" action="">
                <div class="button-group">
                    <button type="submit" class="btn-confirm">✓ Confirm Return</button>
                    <a href="my_rentals.php" style="flex: 1; padding: 15px; background: #6c757d; color: white; text-align: center; text-decoration: none; border-radius: 5px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>