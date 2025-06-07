<?php

include_once('../config/config.php');

// Get the subcategory ID from AJAX request
$subcat_id = isset($_GET['subcat_id']) ? (int)$_GET['subcat_id'] : 0;

if ($subcat_id <= 0) {
    echo "Invalid subcategory ID.";
    exit;
}

// Check DB connection
if (!$conn) {
    die("Database connection failed.");
}

$sql = "SELECT * FROM ad_form WHERE subcategory = $subcat_id AND expires_in = 1 ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo "Query Error: " . mysqli_error($conn);
    exit;
}

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = (int)$row['id'];
        $img = !empty($row['image']) ? $base_url . 'assets/uploads/ads_form/' . $row['image'] : $base_url . 'assets/images/test-img.png';
        $price = htmlspecialchars($row['asking_price']);
        $title = htmlspecialchars($row['ad_title']);
        $location = htmlspecialchars($row['location']);

        echo '
        <div class="col-12 col-md-6 col-lg-4 mb-4 px-md-2">
            <div class="card position-relative">
                <div class="ad-tag poppins-regular">Ad</div>
                <div class="card-img-ad">
                <a href="single-ad.php?id='. $id .'">
<img src="' . $img . '" class="img-fluid" alt="' . $title . '" />
</a>
</div>
<div class="card-body">
    <div class="card-price-det">
        <h5 class="poppins-bold price-post">₹' . $price . '</h5>
    </div>
    <a href="single-ad.php?id='. $id .'">
<p class="Post-title fos-16 poppins-regular">' . $title . '</p>
</a>
<hr />
<div class="d-flex align-items-start poppins-regular fos-14">
    <img src="' . $base_url . 'assets/images/location-black.svg" alt="" class="me-2" />
    <small>' . $location . '</small>
</div>
</div>
</div>
</div>';
}
} else {
echo '<p>No posts found in this subcategory.</p>';
}
?>