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
            
            // Verify the password 
            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                echo "<script>
                alert('Welcome, " . addslashes($user['fullname']) . "!');
                window.location.href = 'homepage.html';
            </script>";
                
                //header("refresh:3;url=homepage.html");
                exit();
            } else {
                echo "<script>
                alert('Invalid Email or Password');
                window.history.back();
            </script>";
            }
        } else {
            echo "<script>
            alert('Invalid Email or Password');
            window.history.back();
        </script>";
        }
        
        $stmt->close();
        $conn->close();
    }
?>