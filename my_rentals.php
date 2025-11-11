<?php
// my_rentals.php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user's rentals
$query = "SELECT R.*, I.INAME, I.PENALTY_PER_DAY, U.UNAME as OWNER_NAME 
          FROM RENTALS R 
          JOIN ITEMS I ON R.IID = I.IID 
          JOIN USERS U ON I.UID = U.UID 
          WHERE R.UID = $user_id 
          ORDER BY R.STATUS ASC, R.RENT_DATE DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rentals - Rental System</title>
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
        <h2>My Rentals</h2>

        <?php if ($result->num_rows > 0): ?>
            <div class="rentals-grid">
                <?php while ($rental = $result->fetch_assoc()): 
                    $today = new DateTime();
                    $due_date = new DateTime($rental['DUE_DATE']);
                    $is_overdue = ($today > $due_date && $rental['STATUS'] == 'ONGOING');
                    $days_overdue = $is_overdue ? $today->diff($due_date)->days : 0;
                ?>
                    <div class="rental-card">
                        <div class="rental-header">
                            <div class="rental-title">
                                <div class="item-name"><?php echo $rental['INAME']; ?></div>
                                <div class="owner-name">👤 Owner: <?php echo $rental['OWNER_NAME']; ?></div>
                            </div>
                            <span class="status-badge status-<?php echo strtolower($rental['STATUS']); ?>">
                                <?php echo $rental['STATUS']; ?>
                            </span>
                        </div>

                        <div class="rental-details">
                            <div class="detail-item">
                                <div class="detail-label">Rent Date</div>
                                <div class="detail-value"><?php echo date('M d, Y', strtotime($rental['RENT_DATE'])); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Due Date</div>
                                <div class="detail-value <?php echo $is_overdue ? 'overdue' : ''; ?>">
                                    <?php echo date('M d, Y', strtotime($rental['DUE_DATE'])); ?>
                                </div>
                            </div>
                            <?php if ($rental['RETURN_DATE']): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Return Date</div>
                                    <div class="detail-value"><?php echo date('M d, Y', strtotime($rental['RETURN_DATE'])); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($is_overdue): ?>
                            <div class="penalty-warning">
                                ⚠ <strong>Overdue by <?php echo $days_overdue; ?> days!</strong><br>
                                Penalty: ₹<?php echo number_format($days_overdue * $rental['PENALTY_PER_DAY'], 2); ?>
                            </div>
                        <?php endif; ?>

                        <div class="cost-summary">
                            <div>
                                <div class="cost-label">Total Cost</div>
                                <?php if ($rental['PENALTY'] > 0): ?>
                                    <div style="font-size: 12px; color: #dc3545;">
                                        (includes ₹<?php echo number_format($rental['PENALTY'], 2); ?> penalty)
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="cost-value">₹<?php echo number_format($rental['TOTAL_COST'] + $rental['PENALTY'], 2); ?></div>
                        </div>

                        <?php if ($rental['STATUS'] == 'ONGOING'): ?>
                            <a href="return_item.php?id=<?php echo $rental['RID']; ?>" class="return-btn">
                                ↩ Return Item
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-rentals">
                <h3>No rentals yet</h3>
                <p>Browse items to start renting!</p>
                <br>
                <a href="browse_items.php" style="display: inline-block; padding: 12px 25px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;">Browse Items</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>