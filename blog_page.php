<?php
include 'connect.php';

$searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : null;
$countryName = isset($_GET['country']) ? $_GET['country'] : null;

if ($countryName) {
    $sqlCountry = "SELECT country_id FROM countries WHERE country_name = ?";
    $stmtCountry = $conn->prepare($sqlCountry);
    $stmtCountry->bind_param("s", $countryName);
    $stmtCountry->execute();
    $resultCountry = $stmtCountry->get_result();

    if ($resultCountry->num_rows === 0) {
        echo "Country not found.";
        exit;
    }

    $row = $resultCountry->fetch_assoc();
    $countryId = $row['country_id'];

    if ($searchTerm) {
        $sqlBlogs = "SELECT b.blog_id, b.title, b.author, b.date_published, b.summary, b.content, 
                    c.country_name, u.username, u.fullname, b.user_id
                    FROM blogs b 
                    JOIN countries c ON b.country_id = c.country_id 
                    LEFT JOIN users u ON b.user_id = u.id
                    WHERE b.country_id = ? AND (b.title LIKE ? OR b.author LIKE ?)
                    ORDER BY b.date_published DESC";
        $stmtBlogs = $conn->prepare($sqlBlogs);
        $stmtBlogs->bind_param("iss", $countryId, $searchTerm, $searchTerm);
    } else {
        $sqlBlogs = "SELECT b.blog_id, b.title, b.author, b.date_published, b.summary, b.content, 
                    c.country_name, u.username, u.fullname, b.user_id
                    FROM blogs b 
                    JOIN countries c ON b.country_id = c.country_id 
                    LEFT JOIN users u ON b.user_id = u.id
                    WHERE b.country_id = ? 
                    ORDER BY RAND() LIMIT 5";
        $stmtBlogs = $conn->prepare($sqlBlogs);
        $stmtBlogs->bind_param("i", $countryId);
    }
} elseif ($searchTerm) {
    $sqlBlogs = "SELECT b.blog_id, b.title, b.author, b.date_published, b.summary, b.content, 
                c.country_name, u.username, u.fullname, b.user_id
                FROM blogs b 
                JOIN countries c ON b.country_id = c.country_id 
                LEFT JOIN users u ON b.user_id = u.id
                WHERE b.title LIKE ? OR b.author LIKE ? OR c.country_name LIKE ?
                ORDER BY b.date_published DESC";
    $stmtBlogs = $conn->prepare($sqlBlogs);
    $stmtBlogs->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
} else {
    echo "No country or search term provided.";
    exit;
}

$stmtBlogs->execute();
$blogs_result = $stmtBlogs->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($countryName) ?> Blogs | Travel Tales</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    :root {
      --primary: #ff6347;
      --text: #222;
      --muted: #555;
      --bg: #fff;
      --gray: #f5f5f5;
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
      line-height: 1.6;
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

    .page-header {
      text-align: center;
      padding: 20px;
    }

    .page-header h2 {
      font-family: var(--font-serif);
      font-size: 2rem;
      color: var(--primary);
      margin-bottom: 0.5rem;
    }

    .page-header p {
      color: var(--muted);
    }

    .blog-container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 0 2rem 3rem;
    }

    .blog {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
      padding: 1.5rem;
      margin-bottom: 2rem;
      transition: 0.3s;
    }

    .blog:hover {
      transform: translateY(-5px);
    }

    .blog h3 {
      color: var(--primary);
      font-size: 1.5rem;
      margin-bottom: 0.3rem;
    }

    .blog span {
      font-size: 0.9rem;
      color: #888;
    }

    .blog p {
      margin: 0.7rem 0;
      color: var(--text);
    }

    .full-content {
      display: none;
    }

    .blog a {
      color: #0077cc;
      font-weight: 500;
      cursor: pointer;
      font-size: 0.95rem;
    }

    .blog a:hover {
      text-decoration: underline;
    }

    .footer {
      background: #111;
      color: #bbb;
      text-align: center;
      padding: 2rem 1rem;
      font-size: 0.9rem;
    }

    @media (max-width: 768px) {
      .nav-links {
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
        <a href="blog destination.html">Blogs</a>
        <a href="Destination.html">Destinations</a>
      </div>
    </div>
  </nav>

  <!-- Page Header -->
  <div class="page-header">
    <h2><?= htmlspecialchars($countryName ?? 'Travel') ?> Blogs</h2>
    <p>Read stories and local experiences from our vibrant community.</p>
  </div>

  <!-- Blog Cards -->
  <div class="blog-container">
    <?php if ($blogs_result && $blogs_result->num_rows > 0): ?>
      <?php while ($blog = $blogs_result->fetch_assoc()): ?>
        <div class="blog">
          <h3><?= htmlspecialchars($blog['title']) ?></h3>
          <span>
            <?php if (!empty($blog['username'])): ?>
              By <strong><?= htmlspecialchars($blog['username']) ?></strong> 
              (<?= htmlspecialchars($blog['author']) ?>)
            <?php else: ?>
              By <?= htmlspecialchars($blog['author']) ?>
            <?php endif; ?>
            | <?= $blog['date_published'] ?>
          </span>
          <p><?= htmlspecialchars($blog['summary']) ?></p>
          <div class="full-content"><?= nl2br(htmlspecialchars($blog['content'])) ?></div>
          <a onclick="toggleFullContent(this)">Read More</a>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="text-align:center; color:#888;">No blogs found.</p>
    <?php endif; ?>
  </div>

  <!-- Footer -->
  <footer class="footer">
    <p>&copy; 2025 Travel Tales. All Rights Reserved.</p>
  </footer>

  <script>
    lucide.createIcons();
    function toggleFullContent(link) {
      const content = link.previousElementSibling;
      const isVisible = content.style.display === "block";
      content.style.display = isVisible ? "none" : "block";
      link.textContent = isVisible ? "Read More" : "Read Less";
    }
  </script>
</body>
</html>
