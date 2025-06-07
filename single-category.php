<?php
include_once('config/config.php'); // always load this first
include_once('partials/header.php');
?>

<!-- hero section -->
<!-- hero section -->
<section class="hero-sec d-flex justify-content-center align-items-center">
    <div class="container">
        <div class="row">
            <div class="">
                <h1 class="playfair-medium text-white fos-90 text-md-center">
                    <span class="playfair-medium hide-text">Free</span> Classified Ads
                </h1>
                <h6 class="fos-16 text-md-center text-white mt-4 mb-md-3 mb-5">
                    8,096,606 listings across 5,921 sites
                </h6>
                <div
                    class="d-flex justify-content-center head-searching-content align-items-md-center align-items-start flex-column flex-md-row">
                    <input type="text" name="keyword" id="keywords" placeholder="keyword" />
                    <input type="text" name="keyword" id="keywords" placeholder="City or Postal Code" />
                    <button type="button" class="theme-btn d-flex">
                        <img src="<?php echo $base_url; ?>assets/images/search-icon.svg" alt="" class="me-2" />Search
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- hero section -->
<!-- hero section -->

<!-- section-1 -->
<!-- section-1 -->
<section class="section-1">
    <div class="container">
        <div class="row">
            <div class="section-1-text">
                <h2 class="playfair-medium text-center fos-40 mb-4">
                    Post Your Free Classified Ads
                </h2>
                <p class="color-p text-center fos-14">
                    Post your free classified ads on biggest and leading leading
                    cross-category classifieds platform. Advertise for free in India &
                    worldwide. Post free ads & create free business listing.
                    <br />
                    <br />
                    Find the latest classified ads for flats, jobs, cars, motorbikes,
                    furniture, tools, personals services and more for sale in India.
                </p>
            </div>
        </div>
        <div class="row">
            <div class="section-1-advertisements mt-54 d-flex gap-3">
                <div class="image-1 image-secs">
                    <img src="<?php echo $base_url; ?>assets/images/test-image.jpg" alt="" class="w-100" />
                </div>
                <div class="image-2 image-secs d-none d-md-block">
                    <img src="<?php echo $base_url; ?>assets/images/test-image-2.jpg" alt="" class="w-100" />
                </div>
            </div>
        </div>
    </div>
</section>
<!-- section-1 -->
<!-- section-1 -->

<!-- section-2 categories -->
<!-- section-2 categories -->
<section class="sec-categories">
    <div class="container">
        <div class="row">
            <div class="all-cats d-flex flex-wrap">
                <?php
                include_once('config/config.php'); // Adjust the path as needed
                $query = $conn->query("SELECT * FROM ad_categories WHERE LOWER(status) = 'live' ORDER BY id DESC");

                while ($cat = $query->fetch_assoc()):
                    // Set default fallback if image is missing
                    $img = !empty($cat['image']) ? $base_url . 'assets/uploads/' . $cat['image'] : $base_url . 'assets/images/cats/default.svg';
                ?>
                <a href="single-category.php?category=<?= $cat['id'] ?>"
                    class="single-cat d-flex flex-column align-items-center text-decoration-none text-dark">
                    <img src="<?= $img ?>" alt="<?= htmlspecialchars($cat['name']) ?>">
                    <h3 class="fos-16"><?= htmlspecialchars($cat['name']) ?></h3>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</section>

<!-- section-2 categories -->
<!-- section-2 categories -->

<!-- section-3 search -->
<!-- section-3 search -->
<section class="sec-search">
    <div class="container">
        <div class="row">
            <div class="d-flex justify-content-between flex-wrap">
                <h3 class="playfair-medium fos-40 text-md-start text-center">
                    All Classified Ads
                </h3>
                <div class="d-flex posts-search-con">
                    <select name="catsPosts" id="catsPosts" class="posts-search">
                        <option value="1">All Categories</option>
                        <option value="1">2</option>
                        <option value="1">3</option>
                        <option value="1">4</option>
                        <option value="1">5</option>
                    </select>
                    <span class="line-head mx-2">|</span>
                    <div class="search-posts-input-cont">
                        <img src="<?php echo $base_url; ?>assets/images/black-search.svg" alt="" />
                        <input type="text" name="postsInput" id="" placeholder="Search" class="" />
                        <img src="<?php echo $base_url; ?>assets/images/black-microphone.svg" alt="" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- section-3 search -->
<!-- section-3 search -->

<!-- section-4 posts -->
<!-- section-4 posts -->
<section class="section-4 pb-100">
<?php
include_once('config/config.php'); // database connection

// Get the selected category ID from URL
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Fetch all subcategories of this category
$subQuery = $conn->query("SELECT * FROM ad_subcategories WHERE category_id = $category_id ORDER BY id ASC");

$subcategories = [];
while ($sub = $subQuery->fetch_assoc()) {
    $subcategories[] = $sub;
}

// Optional: get the first subcategory ID for default active
$default_sub_id = isset($subcategories[0]['id']) ? $subcategories[0]['id'] : 0;
?>
    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4 mb-md-0">
                <div class="sidebar-category">
                    <h1 class="poppins-regular fos-20 mb-30">Subcategory</h1>
                    <div class="col fos-16 subcats-fetched">
                        
                        <?php foreach ($subcategories as $index => $sub): ?>
                    <a href="#"
                       class="<?= $index === 0 ? 'active' : '' ?>"
                       data-subid="<?= $sub['id'] ?>">
                        <?= htmlspecialchars($sub['title']) ?>
                    </a>
                <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-8 col-lg-9 d-flex justify-content-between flex-wrap" id="subcat-posts">
            <div class="w-100 text-center"><p>Loading...</p></div>
            </div>
        </div>
    </div>
</section>
<!-- section-4 posts -->

<script>
document.addEventListener("DOMContentLoaded", function () {
    const subcatLinks = document.querySelectorAll('.subcats-fetched a');
    const postContainer = document.getElementById('subcat-posts');

    function loadPosts(subcatID) {
        postContainer.innerHTML = '<div class="w-100 text-center"><p>Loading...</p></div>';

        fetch('ajax/load-subcat-posts.php?subcat_id=' + subcatID)
            .then(response => response.text())
            .then(html => {
                postContainer.innerHTML = html;
            })
            .catch(err => {
                console.error("Error:", err);
                postContainer.innerHTML = "<p>Error loading posts.</p>";
            });
    }

    // Auto-load the first subcategory
    const firstActive = document.querySelector('.subcats-fetched a.active');
    if (firstActive) {
        loadPosts(firstActive.dataset.subid);
    }

    // On subcategory click
    subcatLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            subcatLinks.forEach(el => el.classList.remove('active'));
            this.classList.add('active');
            const subID = this.dataset.subid;
            loadPosts(subID);
        });
    });
});
</script>



<!-- footer -->
<!-- footer -->
<?php
 include_once('partials/footer.php');
 ?>
<!-- footer -->
<!-- footer -->