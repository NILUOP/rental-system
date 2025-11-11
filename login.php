<?php
// login.php
require_once 'config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uname = sanitize($_POST['uname']);
    $upassword = $_POST['upassword'];
    
    if (empty($uname) || empty($upassword)) {
        $error = "Please enter both username and password!";
    } else {
        $query = "SELECT * FROM USERS WHERE UNAME = '$uname'";
        $result = $conn->query($query);
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($upassword, $user['UPASSWORD'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['UID'];
                $_SESSION['username'] = $user['UNAME'];
                $_SESSION['email'] = $user['UEMAIL'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password!";
            }
        } else {
            $error = "Invalid username or password!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rental System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="uname" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="upassword" required>
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>