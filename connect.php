<?php
//  Connect to DB first so other files can use it
$conn = new mysqli('localhost', 'root', '', 'travel_tales_db');
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Only run registration logic if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fullname'])) {
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        echo "<script>
            alert('Passwords do not match!');
            window.history.back();
        </script>";
        //echo "<p>Please go <a href='javascript:history.back()'>back</a> and try again.</p>";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email or username already exists
    $check_stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $check_stmt->bind_param("ss", $email, $username);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>
            alert('Email or username already exists! Please try again with different credentials.');
            window.history.back();
        </script>";
        //echo "<p>Please go <a href='javascript:history.back()'>back</a> and try again with a different email or username.</p>";
        $check_stmt->close();
        $conn->close();
        exit();
    }

    $check_stmt->close();

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, phone, username, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullname, $email, $phone, $username, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>
            alert('Registration successful! Redirecting to login page...');
            window.location.href = 'login.html';
        </script>";
        //echo "<p>Redirecting you to <a href='login.html'>Login</a>...</p>";
        //header("refresh:3;url=login.html");
    } else {
        echo "<script>
            alert('Registration failed: " . addslashes($stmt->error) . "');
            window.history.back();
        </script>";
        //echo "<p>Error: " . $stmt->error . "</p>";
        //echo "<p>Please go <a href='javascript:history.back()'>back</a> and try again.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>
