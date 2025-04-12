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
        echo json_encode(["error" => "Country not found"]);
        exit;
    }

    $row = $resultCountry->fetch_assoc();
    $countryId = $row['country_id'];

    // Search within selected country
    if ($searchTerm) {
        $sqlBlogs = "SELECT b.title, b.author, b.date_published, b.summary, b.content, c.country_name 
                     FROM blogs b 
                     JOIN countries c ON b.country_id = c.country_id 
                     WHERE b.country_id = ? AND (b.title LIKE ? OR b.author LIKE ?)
                     ORDER BY b.date_published DESC";
        $stmtBlogs = $conn->prepare($sqlBlogs);
        $stmtBlogs->bind_param("iss", $countryId, $searchTerm, $searchTerm);
    } else {
        $sqlBlogs = "SELECT b.title, b.author, b.date_published, b.summary, b.content, c.country_name 
                     FROM blogs b 
                     JOIN countries c ON b.country_id = c.country_id 
                     WHERE b.country_id = ? 
                     ORDER BY RAND() LIMIT 5";
        $stmtBlogs = $conn->prepare($sqlBlogs);
        $stmtBlogs->bind_param("i", $countryId);
    }
} elseif ($searchTerm) {
    // Global search without specific country
    $sqlBlogs = "SELECT b.title, b.author, b.date_published, b.summary, b.content, c.country_name 
                 FROM blogs b 
                 JOIN countries c ON b.country_id = c.country_id 
                 WHERE b.title LIKE ? OR b.author LIKE ? OR c.country_name LIKE ?
                 ORDER BY b.date_published DESC";
    $stmtBlogs = $conn->prepare($sqlBlogs);
    $stmtBlogs->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
} else {
    echo json_encode(["error" => "No country or search term provided"]);
    exit;
}

$stmtBlogs->execute();
$blogs_result = $stmtBlogs->get_result();

$blogs = [];
while ($blog = $blogs_result->fetch_assoc()) {
    $blogs[] = $blog;
}

echo json_encode($blogs);

$stmtBlogs->close();
$conn->close();
?>