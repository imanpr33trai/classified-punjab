<?php
include 'config/config.php'; // Ensure this path is correct

$keyword = isset($_POST['keyword']) ? mysqli_real_escape_string($conn, $_POST['keyword']) : '';
$location = isset($_POST['location']) ? mysqli_real_escape_string($conn, $_POST['location']) : '';

$results = [];

// Query for ad_form table
$ad_query = "SELECT 'ad' AS source, id, ad_title AS title, description, created_at 
             FROM ad_form 
             WHERE (ad_title LIKE '%$keyword%' OR description LIKE '%$keyword%' OR phone LIKE '%$keyword%' OR email LIKE '%$keyword%' OR location LIKE '%$keyword%' OR city_town_neighbourhood LIKE '%$keyword%' OR organisation LIKE '%$keyword%')";

if (!empty($location)) {
    $ad_query .= " AND (postal_code LIKE '%$location%' OR city_town_neighbourhood LIKE '%$location%')";
}

// Query for blog_posts table
$blog_query = "SELECT 'blog' AS source, id, title, description, created_at 
               FROM blog_posts 
               WHERE (title LIKE '%$keyword%' OR description LIKE '%$keyword%' OR phone LIKE '%$keyword%' OR email LIKE '%$keyword%' OR author_name LIKE '%$keyword%')";

if (!empty($location)) {
    $blog_query .= " AND (title LIKE '%$location%' OR category_id LIKE '%$location%')";
}

// Execute queries
$ad_result = mysqli_query($conn, $ad_query);
$blog_result = mysqli_query($conn, $blog_query);

// Fetch results
if ($ad_result) {
    while ($row = mysqli_fetch_assoc($ad_result)) {
        $results[] = $row;
    }
}

if ($blog_result) {
    while ($row = mysqli_fetch_assoc($blog_result)) {
        $results[] = $row;
    }
}

// Display results
if (empty($results)) {
    echo "<p>No results found.</p>";
} else {
    foreach ($results as $item) {
        $title = htmlspecialchars($item['title']);
        $description = htmlspecialchars(substr($item['description'], 0, 100)) . '...';
        $date = htmlspecialchars($item['created_at']);
        $id = $item['id'];
        $link = ($item['source'] === 'ad') ? "single-ad.php?id=$id" : "single-article.php?id=$id";

        echo "<div class='result-item'>";
        echo "<a href='$link'><h3>$title</h3></a>";
        echo "<p>$description</p>";
        echo "<small>Posted on: $date</small>";
        echo "</div>";
    }
}
?>