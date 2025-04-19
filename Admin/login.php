<?php
// login.php - Admin login page
require_once 'config.php';

$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, username, password, full_name, role FROM admins WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();
                
                // Check if username exists, if yes then verify password
                if ($stmt->num_rows == 1) {
                    // Bind result variables
                    $stmt->bind_result($id, $username, $hashed_password, $full_name, $role);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["full_name"] = $full_name;
                            $_SESSION["role"] = $role;
                            
                            // Update last login
                            $update = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                            $update->bind_param("i", $id);
                            $update->execute();
                            $update->close();
                            
                            // Log action
                            logAction($id, "Login", "Admin logged in successfully");
                            
                            // Redirect user to dashboard
                            header("location: dashboard.php");
                        } else {
                            // Password is not valid
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    // Username doesn't exist
                    $login_err = "Invalid username or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
    
    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - eSports Tournament</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#8a2be2',
                        secondary: '#00ffff',
                        dark: '#121212',
                        light: '#f0f0f0',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-dark text-light font-sans bg-cover bg-fixed" style="background-image: linear-gradient(rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.85)), url('https://cdnjs.cloudflare.com/ajax/placeholder/1920/1080');">

    <div class="max-w-md mx-auto my-20 px-8 py-10 bg-opacity-80 bg-[#191923] shadow-lg rounded-lg border border-primary">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-secondary">
                <i class="fas fa-gamepad mr-2"></i> eSports Tournament
            </h1>
            <p class="text-light text-lg mt-2">Admin Panel</p>
        </div>
        
        <?php if (!empty($login_err)) : ?>
            <div class="bg-red-500 bg-opacity-20 border border-red-500 text-red-100 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $login_err; ?>
            </div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-6">
                <label for="username" class="block mb-2 text-secondary font-semibold">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-user text-secondary"></i>
                    </span>
                    <input type="text" name="username" id="username" class="w-full pl-10 pr-3 py-3 bg-black bg-opacity-50 border border-primary rounded-lg text-light focus:outline-none focus:ring focus:ring-secondary focus:border-secondary" 
                        value="<?php echo $username; ?>" placeholder="Enter username">
                </div>
                <span class="text-red-400 text-sm"><?php echo $username_err; ?></span>
            </div>
            
            <div class="mb-6">
                <label for="password" class="block mb-2 text-secondary font-semibold">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-lock text-secondary"></i>
                    </span>
                    <input type="password" name="password" id="password" class="w-full pl-10 pr-3 py-3 bg-black bg-opacity-50 border border-primary rounded-lg text-light focus:outline-none focus:ring focus:ring-secondary focus:border-secondary" 
                        placeholder="Enter password">
                </div>
                <span class="text-red-400 text-sm"><?php echo $password_err; ?></span>
            </div>
            
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-primary to-secondary text-dark font-bold uppercase tracking-wider rounded transition-all hover:shadow-lg hover:-translate-y-1 active:translate-y-0 focus:outline-none">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </button>
        </form>
        
        <div class="mt-6 text-center text-gray-400 text-sm">
            <p>© 2025 eSports Tournament Admin Panel</p>
        </div>
    </div>

</body>
</html>