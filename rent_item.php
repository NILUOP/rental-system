<?php
// rent_item.php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get item ID
if (!isset($_GET['id'])) {
    header("Location: browse_items.php");
    exit();
}

$item_id = intval($_GET['id']);

// Get item details
$query = "SELECT I.*, U.UNAME as OWNER_NAME 
          FROM ITEMS I 
          JOIN USERS U ON I.UID = U.UID 
          WHERE I.IID = $item_id AND I.IS_AVAILABLE = 1 AND I.UID != $user_id";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    header("Location: browse_items.php");
    exit();
}

$item = $result->fetch_assoc();

// Handle rental form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rent_days = intval($_POST['rent_days']);
    
    if ($rent_days < 1) {
        $error = "Rental period must be at least 1 day!";
    } else {
        // Calculate dates and cost
        $rent_date = date('Y-m-d');
        $due_date = date('Y-m-d', strtotime("+$rent_days days"));
        $total_cost = $item['RENT_PER_DAY'] * $rent_days;
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert rental
            $insert_query = "INSERT INTO RENTALS (UID, IID, RENT_DATE, DUE_DATE, TOTAL_COST, STATUS) 
                            VALUES ($user_id, $item_id, '$rent_date', '$due_date', $total_cost, 'ONGOING')";
            $conn->query($insert_query);
            
            // Update item availability
            $update_query = "UPDATE ITEMS SET IS_AVAILABLE = 0 WHERE IID = $item_id";
            $conn->query($update_query);
            
            $conn->commit();
            $success = "Item rented successfully! Check 'My Rentals' to manage it.";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to rent item: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Item - Rental System</title>
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
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
        }
        .rental-container {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .item-info {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .item-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .item-owner {
            color: #888;
            margin-bottom: 15px;
        }
        .category-badge {
            display: inline-block;
            background: #e8eaf6;
            color: #667eea;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 14px;
        }
        .pricing {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }
        .price-label {
            color: #666;
        }
        .price-value {
            font-weight: bold;
            color: #667eea;
            font-size: 18px;
        }
        .form-group {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-bottom: 10px;
            color: #555;
            font-weight: bold;
        }
        input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        #totalCost {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        #totalCost .label {
            color: #666;
            font-size: 14px;
        }
        #totalCost .amount {
            font-size: 32px;
            font-weight: bold;
            color: #28a745;
            margin-top: 5px;
        }
        button {
            width: 100%;
            padding: 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #218838;
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
        .info-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
    <script>
        function calculateTotal() {
            const days = document.getElementById('rent_days').value || 0;
            const rentPerDay = <?php echo $item['RENT_PER_DAY']; ?>;
            const total = days * rentPerDay;
            document.getElementById('totalAmount').textContent = '₹' + total.toFixed(2);
            document.getElementById('dueDate').textContent = getDueDate(days);
        }
        
        function getDueDate(days) {
            const today = new Date();
            today.setDate(today.getDate() + parseInt(days));
            return today.toLocaleDateString('en-IN', { year: 'numeric', month: 'long', day: 'numeric' });
        }
    </script>
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
        <a href="browse_items.php" class="back-link">← Back to Browse</a>
        
        <div class="rental-container">
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success">
                    <?php echo $success; ?>
                    <br><br>
                    <a href="my_rentals.php" style="color: #28a745; font-weight: bold;">View My Rentals →</a>
                </div>
            <?php endif; ?>
            
            <div class="item-info">
                <div class="item-name"><?php echo $item['INAME']; ?></div>
                <div class="item-owner">👤 Owner: <?php echo $item['OWNER_NAME']; ?></div>
                <span class="category-badge"><?php echo $item['CATEGORY']; ?></span>
            </div>
            
            <div class="pricing">
                <div class="price-row">
                    <span class="price-label">Rent per Day:</span>
                    <span class="price-value">₹<?php echo number_format($item['RENT_PER_DAY'], 2); ?></span>
                </div>
                <div class="price-row">
                    <span class="price-label">Late Penalty per Day:</span>
                    <span class="price-value">₹<?php echo number_format($item['PENALTY_PER_DAY'], 2); ?></span>
                </div>
            </div>
            
            <div class="info-box">
                ⚠ <strong>Note:</strong> Please return the item by the due date to avoid penalty charges.
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Number of Days to Rent:</label>
                    <input type="number" id="rent_days" name="rent_days" min="1" value="1" 
                           oninput="calculateTotal()" required>
                </div>
                
                <div id="totalCost">
                    <div class="label">Total Cost</div>
                    <div class="amount" id="totalAmount">₹<?php echo number_format($item['RENT_PER_DAY'], 2); ?></div>
                    <div class="label" style="margin-top: 10px;">Due Date: <span id="dueDate"></span></div>
                </div>
                
                <button type="submit">🎉 Confirm Rental</button>
            </form>
        </div>
    </div>
    
    <script>
        calculateTotal();
    </script>
</body>
</html>