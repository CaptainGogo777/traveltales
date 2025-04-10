<?php
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "travel_tales_db");
    if ($conn->connect_error) {
        die("Failed to connect: " . $conn->connect_error);
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify the password (if using password_hash in registration)
            if (password_verify($password, $user['password'])) {
                // Start session and store user info if needed
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                echo "<h2 style='font-family: Arial, sans-serif'>Login Successful!</h2>";
                echo "<p>Welcome, " . $user['fullname'] . "!</p>";
                echo "<p>Redirecting you to <a href='homepage.html'>Home</a>...</p>";
                // Redirect to homepage after a short delay
                header("refresh:3;url=homepage.html");
                exit();
            } else {
                echo "<h2>Invalid Email or Password</h2>";
                echo "<p>Please go <a href='javascript:history.back()'>back</a> and try again.</p>";
            }
        } else {
            echo "<h2>Invalid Email or Password</h2>";
            echo "<p>Please go <a href='javascript:history.back()'>back</a> and try again.</p>";
        }
        
        $stmt->close();
        $conn->close();
    }
?>