<?php
// register.php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uname = sanitize($_POST['uname']);
    $uemail = sanitize($_POST['uemail']);
    $upassword = $_POST['upassword'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($uname) || empty($uemail) || empty($upassword)) {
        $error = "All fields are required!";
    } elseif ($upassword !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($upassword) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        // Check if username or email already exists
        $check_query = "SELECT * FROM USERS WHERE UNAME = '$uname' OR UEMAIL = '$uemail'";
        $result = $conn->query($check_query);
        
        if ($result->num_rows > 0) {
            $error = "Username or Email already exists!";
        } else {
            // Hash password
            $hashed_password = password_hash($upassword, PASSWORD_DEFAULT);
            
            // Insert user
            $insert_query = "INSERT INTO USERS (UNAME, UEMAIL, UPASSWORD) VALUES ('$uname', '$uemail', '$hashed_password')";
            
            if ($conn->query($insert_query)) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Rental System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2 class="main-form-group">Register</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="main-form-group">
                <div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="uname" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="uemail" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="upassword" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit">Register</button>
                </div>
            </div>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>