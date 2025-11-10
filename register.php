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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #5568d3;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success {
            background: #efe;
            color: #3c3;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
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
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>