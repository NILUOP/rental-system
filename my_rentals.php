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
        h2 {
            margin-bottom: 30px;
            color: #333;
        }
        .rentals-grid {
            display: grid;
            gap: 20px;
        }
        .rental-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .rental-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .rental-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .rental-title {
            flex: 1;
        }
        .item-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .owner-name {
            color: #888;
            font-size: 14px;
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }
        .status-ongoing {
            background: #fff3cd;
            color: #856404;
        }
        .status-returned {
            background: #d4edda;
            color: #155724;
        }
        .rental-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .detail-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .detail-label {
            color: #666;
            font-size: 13px;
            margin-bottom: 5px;
        }
        .detail-value {
            font-weight: bold;
            color: #333;
            font-size: 16px;
        }
        .overdue {
            color: #dc3545 !important;
        }
        .cost-summary {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cost-label {
            color: #666;
        }
        .cost-value {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }
        .penalty-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .return-btn {
            display: inline-block;
            padding: 12px 25px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
            transition: background 0.3s;
        }
        .return-btn:hover {
            background: #218838;
        }
        .no-rentals {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            color: #999;
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
                            <?php if ($rental['ACTUAL_RETURN_DATE']): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Return Date</div>
                                    <div class="detail-value"><?php echo date('M d, Y', strtotime($rental['ACTUAL_RETURN_DATE'])); ?></div>
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