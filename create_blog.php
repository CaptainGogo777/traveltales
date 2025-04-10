<?php
include 'connect.php';

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $summary = $_POST['summary'];
    $content = $_POST['content'];
    $country_id = $_POST['country_id'];
    $date = date("Y-m-d");

    $sql = "INSERT INTO blogs (title, author, summary, content, country_id, date_published) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssis", $title, $author, $summary, $content, $country_id, $date);

    if ($stmt->execute()) {
        $message = "Blog successfully created!";
        header("Location: blog destination.html");
        exit;
    } else {
        $message = "Error: " . $stmt->error;
    }
}

// Get country list
$countryQuery = "SELECT country_id, country_name FROM countries ORDER BY country_name ASC";
$countryResult = $conn->query($countryQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Create Blog | Travel Tales</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    :root {
      --primary: #ff6347;
      --text: #222;
      --muted: #555;
      --bg: #f9f9f9;
      --font-serif: 'Playfair Display', serif;
      --font-sans: 'Inter', sans-serif;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: var(--font-sans);
      background: var(--bg);
      color: var(--text);
      padding-top: 90px;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    .navbar {
      background: rgba(255, 255, 255, 0.96);
      backdrop-filter: blur(10px);
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 999;
      display: flex;
      justify-content: center;
    }

    .navbar .container {
      max-width: 1280px;
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
    }

    .logo {
      font-family: var(--font-serif);
      font-size: 24px;
      color: var(--primary);
      font-weight: 700;
    }

    .nav-links a {
      margin-left: 20px;
      color: var(--text);
      font-weight: 500;
      transition: 0.3s;
    }

    .nav-links a:hover {
      color: var(--primary);
    }

    h2 {
      text-align: center;
      font-family: var(--font-serif);
      font-size: 2rem;
      margin: 2rem 0 1rem;
      color: var(--primary);
    }

    .message {
      text-align: center;
      color: green;
      font-weight: 500;
    }

    form {
      background: #fff;
      padding: 2rem;
      max-width: 600px;
      margin: auto;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    label {
      display: block;
      margin-top: 1.2rem;
      font-weight: 500;
    }

    input[type="text"],
    textarea,
    select {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      margin-top: 0.5rem;
      font-size: 1rem;
    }

    textarea {
      min-height: 120px;
    }

    button {
      margin-top: 2rem;
      padding: 0.75rem 1.5rem;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      font-size: 1rem;
      transition: background 0.3s;
    }

    button:hover {
      background: #e5573f;
    }

    .footer {
      background: #111;
      color: #bbb;
      text-align: center;
      padding: 2rem 1rem;
      font-size: 0.9rem;
      margin-top: 3rem;
    }

    @media (max-width: 600px) {
      .navbar .container {
        padding: 1rem;
      }

      .nav-links a {
        margin-left: 12px;
        font-size: 14px;
      }
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar">
    <div class="container">
      <a href="homepage.html" class="logo">Travel Tales</a>
      <div class="nav-links">
        <a href="homepage.html">Home</a>
        <a href="about.html">About</a>
        <a href="blog destination.html">Blog</a>
        <a href="Destination.html">Destinations</a>
      </div>
    </div>
  </nav>

  <!-- Page Content -->
  <h2>Create a New Blog</h2>
  <?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <form method="POST" action="">
    <label for="title">Blog Title</label>
    <input type="text" name="title" id="title" required>

    <label for="author">Author</label>
    <input type="text" name="author" id="author" required>

    <label for="summary">Summary</label>
    <textarea name="summary" id="summary" required></textarea>

    <label for="content">Full Content</label>
    <textarea name="content" id="content" required></textarea>

    <label for="country_id">Country</label>
    <select name="country_id" id="country_id" required>
      <option value="">-- Select Country --</option>
      <?php while($row = $countryResult->fetch_assoc()): ?>
        <option value="<?= $row['country_id'] ?>"><?= htmlspecialchars($row['country_name']) ?></option>
      <?php endwhile; ?>
    </select>

    <button type="submit">Publish Blog</button>
  </form>

  <!-- Footer -->
  <footer class="footer">
    <p>&copy; 2025 Travel Tales | All Rights Reserved</p>
  </footer>

  <script>lucide.createIcons();</script>
</body>
</html>
