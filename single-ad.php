<?php
include_once('config/config.php'); // always load this first
include_once('partials/header.php');
?>
<?php
include 'config/config.php'; // adjust path if needed

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Ad not found.";
    exit;
}

$ad_id = intval($_GET['id']);
$query = $conn->query("SELECT * FROM ad_form WHERE id = $ad_id");

if ($query->num_rows === 0) {
    echo "Ad not found.";
    exit;
}

$ad = $query->fetch_assoc();

// Image fallback
$ad_image = !empty($ad['image']) ? $base_url . 'assets/uploads/ads_form/' . $ad['image'] : $base_url . 'assets/images/test-image-2.jpg';




// review logics
$ad_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user = null;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user['id'] = $user_id; // Store ID as well
}
?>
<!-- Breadcrump -->
<!-- Breadcrump -->
<section class="breadcrump">
    <div class="container">
        <div class="row">
            <div class="d-flex gap-2">
                <a href="#" class="text-decoration-none breadcrump-links breadcrump-link-1">Home >></a>
                <a href="#" class="text-decoration-none breadcrump-links breadcrump-link-2">Post Ad</a>
            </div>
        </div>
    </div>
</section>
<!-- Breadcrump -->
<!-- Breadcrump -->

<!-- article details -->
<!-- article details -->
<section class="single-article-details pb-100 mb-50">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="user-infos d-flex align-items-center mb-30 gap-3 d-block d-lg-none">
                    <img src="<?= $base_url ?>assets/images/userimage.png" alt="">
                    <h1 class="fos-20 poppins-regular"><?= htmlspecialchars($ad['user_name']) ?></h1>
                </div>

                <h1 class=" single-post-title fos-24 poppins-regular mb-20 d-block d-lg-none">
                    <?= htmlspecialchars($ad['ad_title']) ?></h1>

                <img src="<?= $ad_image ?>" class="w-100 mb-60 post-image-single" alt="">


                <div class="loc d-flex mb-40 d-block d-lg-none">
                    <img src="<?php echo $base_url; ?>assets/images/location-black.svg" alt="" class="me-3">
                    <h1 class="fos-16 poppins-regular m-0"><?= htmlspecialchars($ad['location']) ?></h1>
                </div>


                <div class="price-div mb-40 d-block d-lg-none">
                    <h1 class="fos-40 poppins-medium mb-40 color-pink">$<?= htmlspecialchars($ad['asking_price']) ?>
                    </h1>
                </div>

                <div class="contact-btn-div mb-50 d-block d-lg-none">
                    <a href="#" class="theme-btn text-decoration-none mb-50">Contact Us</a>
                </div>





                <div class="details-ad">
                    <h1 class="single-ad-email fos-16 poppins-bold">📧 <?= htmlspecialchars($ad['email']) ?></h1>
                    <h1 class="single-ad-plateform fos-16 poppins-bold"><span
                            class="poppins-regular"><?= ucfirst($ad['platforms']) ?>:</span>
                        <?= htmlspecialchars($ad['user_name']) ?>
                    </h1>
                    <h1 class="single-ad-whatsapp fos-16 poppins-bold"><span class="poppins-regular"> WhatsApp
                            No:</span> <?= htmlspecialchars($ad['phone']) ?>
                    </h1>
                    <hr>
                    <div class="services-single-ad">
                        <!-- <h1 class="fos-16 poppins-regular">Services I provide:</h1>
                        <h1 class="fos-16 poppins-regular">• User Interface Design</h1>
                        <h1 class="fos-16 poppins-regular">• UX Research and UX Design</h1>
                        <h1 class="fos-16 poppins-regular">• Website & Mobile Design</h1>
                        <h1 class="fos-16 poppins-regular">• Interaction Design</h1>
                        <h1 class="fos-16 poppins-regular">• Mobile App Design</h1> -->
                        <h1 class="fos-16 poppins-regular"><?= nl2br(htmlspecialchars($ad['description'])) ?></h1>
                    </div>
                    <hr>
                    <h1 class="single-ad-portfolio fos-16 poppins-bold"><span class="poppins-regular"> Follow My other
                            portfolio:</span> <?= htmlspecialchars($ad['organisation']) ?>
                    </h1>
                    <h1 class="single-ad-social fos-16 poppins-bold mb-30"><span class="poppins-regular">My Social Media
                            Accounts:</span><?= htmlspecialchars($ad['platforms']) ?>
                    </h1>
                    <h1 class="single-ad-platform-link fos-16 poppins-bold">Link: <span
                            class="poppins-regular color-pink">
                            <?= htmlspecialchars($ad['platforms_links']) ?> </span>
                    </h1>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="user-infos d-lg-flex align-items-center mb-30 gap-3 d-none">
                    <img src="<?php echo $base_url; ?>assets/images/userimage.png" class="" alt="">
                    <h1 class="fos-20 poppins-regular">User Name</h1>
                </div>
                <h1 class="fos-40 poppins-regular mb-40 d-none d-lg-block"><?= htmlspecialchars($ad['ad_title']) ?></h1>
                <div class="loc d-lg-flex mb-40 d-none">
                    <img src="<?php echo $base_url; ?>assets/images/location-black.svg" alt="" class="me-3">
                    <h1 class="fos-16 poppins-regular m-0"><?= htmlspecialchars($ad['location']) ?></h1>
                </div>
                <div class="price-div mb-40 d-none d-lg-block">
                    <h1 class="fos-40 poppins-medium mb-40 color-pink">$<?= htmlspecialchars($ad['asking_price']) ?>
                    </h1>
                </div>
                <div class="contact-btn-div mb-50 d-none d-lg-block">
                    <a href="#" class="theme-btn text-decoration-none mb-50">Contact Us</a>
                </div>


                <!-- review system -->
                <!-- review system -->

                <?php if (!$user): ?>
                <div class="text-center mt-5">
                    <p>You must be logged in to write a review.</p>
                    <a href="login.php" class="theme-btn">Login to Continue</a>
                </div>
                <?php else: ?>
                <div class="mb-3">
                    <h4 class="poppins-medium">Welcome,
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h4>
                </div>

                <!-- Review Form -->
                <div class="reviews-col">
                    <div class="reviews-col-text mb-4">
                        <h1 class="poppins-medium fos-20">How would you rate the overall user experience of our App?
                        </h1>
                        <h1 class="fos-14 poppins-regular mb-4">Do you find the app easy to use?</h1>

                        <!-- Star Rating -->
                        <div class="review-stars d-flex justify-content-between mb-4" id="starContainer"
                            style="gap: 10px;">
                            <input type="hidden" name="rating" id="ratingInput" value="0">
                            <i class="fa fa-star-o star" data-value="1"></i>
                            <i class="fa fa-star-o star" data-value="2"></i>
                            <i class="fa fa-star-o star" data-value="3"></i>
                            <i class="fa fa-star-o star" data-value="4"></i>
                            <i class="fa fa-star-o star" data-value="5"></i>
                        </div>

                        <!-- Review Form -->
                        <form action="" method="POST" id="reviewForm">
                            <input type="hidden" name="ad_id" value="<?php echo $ad_id; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="rating" id="ratingHidden" value="0">

                            <div class="review-comment">
                                <textarea name="comment" class="w-100 mb-4" rows="5" required></textarea>
                                <button type="submit" class="theme-btn">Submit</button>
                                <a href="#" class="theme-btn text-decoration-none">Cancel</a>
                            </div>
                        </form>
                        <div id="reviewMsg" class="mt-2 text-success"></div>

                    </div>
                </div>
                <?php endif; ?>

                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const stars = document.querySelectorAll(".star");
                    const ratingInput = document.getElementById("ratingHidden");

                    stars.forEach((star, idx) => {
                        star.addEventListener("click", () => {
                            const rating = parseInt(star.getAttribute("data-value"));
                            ratingInput.value = rating;

                            stars.forEach((s, i) => {
                                if (i < rating) {
                                    s.classList.remove("fa-star-o");
                                    s.classList.add("fa-star");
                                    s.style.color = "#FFA500";
                                } else {
                                    s.classList.remove("fa-star");
                                    s.classList.add("fa-star-o");
                                    s.style.color = "#000";
                                }
                            });
                        });
                    });

                    // AJAX form submit
                    const reviewForm = document.getElementById("reviewForm");
                    const msgBox = document.getElementById("reviewMsg");

                    if (!reviewForm || !msgBox) return; // skip if not on this page

                    reviewForm.addEventListener("submit", function(e) {
                        e.preventDefault();
                        const formData = new FormData(reviewForm);

                        fetch("ajax/submit_review_ajax.php", {
                                method: "POST",
                                body: formData
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    msgBox.textContent = "Review submitted successfully!";
                                    msgBox.style.color = "green";
                                    reviewForm.reset();
                                    ratingInput.value = 0;
                                    stars.forEach(star => {
                                        star.classList.remove("fa-star");
                                        star.classList.add("fa-star-o");
                                        star.style.color = "#000";
                                    });
                                } else {
                                    msgBox.textContent = data.message || "Submission failed.";
                                    msgBox.style.color = "red";
                                }
                            })
                            .catch(err => {
                                msgBox.textContent = "Network error. Try again.";
                                msgBox.style.color = "red";
                            });
                    });
                });
                </script>




                <!-- review system -->
                <!-- review system -->

            </div>
        </div>
    </div>
</section>
<!-- article details -->
<!-- article details -->


<!-- review view -->
<!-- review view -->

<?php
include('config/config.php');
// Ensure ad_id is available
$currentAdId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Prepare review query
$reviewStmt = $conn->prepare("
    SELECT r.comment, r.rating, r.created_at, u.first_name, u.last_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.ad_id = ?
    ORDER BY r.created_at DESC
");
$reviewStmt->bind_param("i", $currentAdId);
$reviewStmt->execute();
$reviewResult = $reviewStmt->get_result();
?>

<!-- Reviews Display Section -->
<section class="mt-5 mb-5">
    <h3 class="poppins-medium mb-4">User Reviews</h3>

    <?php if ($reviewResult && $reviewResult->num_rows > 0): ?>
    <?php while ($reviewData = $reviewResult->fetch_assoc()): ?>
    <div class="reviews-col mb-4 p-3 border rounded shadow-sm">
        <div class="reviews-col-text">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="poppins-medium mb-0">
                    <?php echo htmlspecialchars($reviewData['first_name'] . ' ' . $reviewData['last_name']); ?>
                </h5>
                <small class="text-muted">
                    <?php echo date("F j, Y, g:i A", strtotime($reviewData['created_at'])); ?>
                </small>
            </div>

            <!-- Star rating -->
            <div class="review-stars mb-2">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="fa <?php echo ($i <= (int)$reviewData['rating']) ? 'fa-star' : 'fa-star-o'; ?>"
                    style="color: #FFA500;"></i>
                <?php endfor; ?>
            </div>

            <!-- Comment -->
            <p class="poppins-regular mb-0"><?php echo nl2br(htmlspecialchars($reviewData['comment'])); ?></p>
        </div>
    </div>
    <?php endwhile; ?>
    <?php else: ?>
    <p class="text-muted">No reviews yet. Be the first to leave one!</p>
    <?php endif; ?>
</section>



<!-- review view -->
<!-- review view -->
<!-- review view -->






<!-- advertisements -->
<!-- advertisements -->
<section class="mb-50">
    <div class="container">
        <div class="row">
            <div class="section-1-advertisements d-flex gap-3">
                <div class="image-1 image-secs"><img src="<?php echo $base_url; ?>assets/images/test-image.jpg" alt=""
                        class="w-100"></div>
                <div class="image-2 image-secs d-none d-md-block"><img
                        src="<?php echo $base_url; ?>assets/images/test-image-2.jpg" alt="" class="w-100"></div>
            </div>
        </div>
    </div>
</section>
<!-- advertisements -->
<!-- advertisements -->

<!-- related posts -->
<!-- related posts -->
<section>
    <div class="container">
        <div class="row">
            <h1 class="fos-40 playfair-regular text-center text-md-start mb-30">More Related Ads</h1>
            <?php
$current_ad_id = $ad['id'];
$current_category = $ad['category'];

// Fetch up to 8 related ads excluding current one
$related_ads_query = $conn->query("SELECT * FROM ad_form 
    WHERE category = '$current_category' 
    AND id != $current_ad_id 
    ORDER BY created_at DESC 
    LIMIT 8");

if ($related_ads_query && $related_ads_query->num_rows > 0): 
?>
            <div class="row mt-5">
                <h3 class="mb-4">Related Ads</h3>

                <?php while ($related = $related_ads_query->fetch_assoc()): 
        $related_image = !empty($related['image']) 
            ? $base_url . 'assets/uploads/ads_form/' . $related['image'] 
            : $base_url . 'assets/images/test-img.png';
        $related_title = htmlspecialchars($related['ad_title']);
        $related_price = htmlspecialchars($related['asking_price']);
        $related_location = htmlspecialchars($related['location']);
        $related_link = $base_url . "single-ad.php?id=" . $related['id'];
    ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                    <a href="<?= $related_link ?>" class="text-decoration-none text-dark">
                        <div class="card position-relative h-100">
                            <div class="ad-tag poppins-regular">Ad</div>
                            <div class="card-img-ad">
                                <img src="<?= $related_image ?>" class="img-fluid" alt="">
                            </div>
                            <div class="card-body">
                                <div class="card-price-det">
                                    <h5 class="poppins-bold price-post">$<?= $related_price ?></h5>
                                    <a href="#" class="wish-heart">
                                        <img src="<?= $base_url ?>assets/images/single-ad/heart-icon.svg" alt="">
                                    </a>
                                </div>
                                <p class="Post-title fos-16 poppins-regular"><?= $related_title ?></p>
                                <hr>
                                <div class="d-flex align-items-start poppins-regular fos-14">
                                    <img src="<?= $base_url ?>assets/images/location-black.svg" alt="" class="me-2">
                                    <small><?= $related_location ?></small>
                                </div>
                            </div>
                            <button
                                class="position-absolute top-0 end-0 bg-white border-0 m-2 rounded-circle shadow-sm p-1">
                                <i class="bi bi-heart"></i>
                            </button>
                        </div>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>
<!-- related posts -->
<!-- related posts -->











<!-- footer -->
<!-- footer -->
<?php
 include_once('partials/footer.php');
 ?>
<!-- footer -->
<!-- footer -->