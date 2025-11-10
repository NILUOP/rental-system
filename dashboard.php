<?php
// dashboard.php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM ITEMS WHERE UID = $user_id) as my_items,
    (SELECT COUNT(*) FROM ITEMS WHERE UID = $user_id AND IS_AVAILABLE = 1) as available_items,
    (SELECT COUNT(*) FROM RENTALS WHERE UID = $user_id AND STATUS = 'ONGOING') as active_rentals,
    (SELECT COALESCE(SUM(TOTAL_COST), 0) FROM RENTALS WHERE UID = $user_id) as total_spent";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get recent rentals
$recent_rentals = "SELECT R.*, I.INAME, U.UNAME as OWNER_NAME 
    FROM RENTALS R 
    JOIN ITEMS I ON R.IID = I.IID 
    JOIN USERS U ON I.UID = U.UID 
    WHERE R.UID = $user_id 
    ORDER BY R.RENT_DATE DESC 
    LIMIT 5";
$rentals_result = $conn->query($recent_rentals);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Rental System</title>
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
        .welcome {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #667eea;
            font-size: 36px;
            margin-bottom: 10px;
        }
        .stat-card p {
            color: #666;
            font-size: 14px;
        }
        .section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f8f8;
            font-weight: bold;
            color: #555;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-ongoing {
            background: #fff3cd;
            color: #856404;
        }
        .badge-returned {
            background: #d4edda;
            color: #155724;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .action-btn {
            display: block;
            padding: 15px;
            background: #667eea;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .action-btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🏠 Rental System</h1>
        <div class="navbar-right">
            <span>Welcome, <?php echo $username; ?></span>
            <a href="dashboard.php">Dashboard</a>
            <a href="browse_items.php">Browse Items</a>
            <a href="my_items.php">My Items</a>
            <a href="my_rentals.php">My Rentals</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="welcome">
            <h2>Welcome back, <?php echo $username; ?>! 👋</h2>
            <p>Here's what's happening with your rentals today.</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3><?php echo $stats['my_items']; ?></h3>
                <p>My Listed Items</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['available_items']; ?></h3>
                <p>Available for Rent</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['active_rentals']; ?></h3>
                <p>Active Rentals</p>
            </div>
            <div class="stat-card">
                <h3>₹<?php echo number_format($stats['total_spent'], 2); ?></h3>
                <p>Total Spent</p>
            </div>
        </div>

        <div class="section">
            <h2>Quick Actions</h2>
            <div class="quick-actions">
                <a href="add_item.php" class="action-btn">➕ Add New Item</a>
                <a href="browse_items.php" class="action-btn">🔍 Browse Items</a>
                <a href="my_rentals.php" class="action-btn">📋 View All Rentals</a>
                <a href="my_items.php" class="action-btn">📦 Manage My Items</a>
            </div>
        </div>

        <div class="section">
            <h2>Recent Rentals</h2>
            <?php if ($rentals_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Owner</th>
                            <th>Rent Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($rental = $rentals_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $rental['INAME']; ?></td>
                                <td><?php echo $rental['OWNER_NAME']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($rental['RENT_DATE'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($rental['DUE_DATE'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($rental['STATUS']); ?>">
                                        <?php echo $rental['STATUS']; ?>
                                    </span>
                                </td>
                                <td>₹<?php echo number_format($rental['TOTAL_COST'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 20px;">No rentals yet. Start browsing items!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>