<?php
// include_once('config/config.php'); // always load this first
include_once('partials/header.php');
?>
<!-- hero section -->
<!-- hero section -->
<section class="hero-sec d-flex justify-content-center align-items-center">
    <div class="container">
        <div class="row">
            <div class="">
                <h1 class="playfair-medium text-white fos-90 text-md-center"><span
                        class="playfair-medium hide-text">Free</span> Classified Ads</h1>
                <h6 class="fos-16 text-md-center text-white mt-4 mb-md-3 mb-5">8,096,606 listings across 5,921 sites
                </h6>
                <div
                    class="d-flex justify-content-center head-searching-content align-items-md-center align-items-start flex-column flex-md-row">
                    <input type="text" id="keyword" placeholder="Enter keyword">
                    <input type="text" id="location" placeholder="City or Postal Code">
                    <!-- <button type="button" class="theme-btn d-flex" id="search-btn"> -->
                    <a href="#" id="search-btn" class="theme-btn d-flex">


                        <img src="<?php echo $base_url; ?>assets/images/search-icon.svg" alt="" class="me-2">Search
                    </a>
                    <!-- </button> -->
                </div>
                <!-- Loader (hidden initially) -->
                <div id="loader" style="display:none;" class="text-center my-3">
                    <img src="assets/images/loader.gif" alt="loading..." width="40">
                </div>

                <!-- Results will be injected here -->
                <div id="search-results" class="mt-4"></div>

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
                <h2 class="playfair-medium text-center fos-40 mb-4">Post Your Free Classified Ads</h2>
                <p class="color-p text-center fos-14">Post your free classified ads on biggest and leading leading
                    cross-category classifieds platform. Advertise for free in India & worldwide. Post free ads & create
                    free business listing.
                    <br>
                    <br>
                    Find the latest classified ads for flats, jobs, cars, motorbikes, furniture, tools, personals
                    services and more for sale in India.
                </p>
            </div>
        </div>
        <div class="row">
            <div class="section-1-advertisements mt-54 d-flex justify-content-center gap-3">
                <div class="image-1 image-secs"><img src="<?php echo $base_url; ?>assets/images/test-image.jpg" alt=""
                        class="w-100"></div>
                <div class="image-2 image-secs d-none d-md-block"><img
                        src="<?php echo $base_url; ?>assets/images/test-image-2.jpg" alt="" class="w-100"></div>
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
            <div class="all-cats d-flex flex-wrap justify-content-center">
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
            <div
                class="d-flex justify-content-between posts-search-full-con gap-2 flex-sm-wrap flex-xxl-nowrap align-items-md-center">
                <h3 class="playfair-medium fos-40 text-md-center text-sm-center text-center w-100">All Classified Ads
                </h3>
                <div class="d-flex posts-search-con lower-post-search ">
                    <select name="catsPosts" id="catsPosts" class="posts-search ">
                        <option value="1">All Categories</option>
                        <?php
                        include_once('config/config.php'); // Adjust path if needed
                        
                        $result = $conn->query("SELECT id, name FROM ad_categories WHERE status = 'live' ORDER BY name ASC");
                        while ($row = $result->fetch_assoc()):
                            ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endwhile; ?>
                    </select>


                    <span class="line-head mx-2">|</span>
                    <div class="search-posts-input-cont d-flex">
                        <img src="<?php echo $base_url; ?>assets/images/black-search.svg" alt="">
                        <input type="text" name="postsInput" id="" placeholder="Search">
                        <img src="<?php echo $base_url; ?>assets/images/black-microphone.svg" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- section-3 search -->
<!-- section-3 search -->





<!-- section test posts  -->
<!-- section test posts  -->
<!-- section test posts  -->
<section class="section-4">
    <div class="container">
        <!-- First 8 Ads -->
        <div class="row ads-list">
            <?php
            $query = $conn->query("SELECT * FROM ad_form ORDER BY id DESC LIMIT 8");
            while ($ad = $query->fetch_assoc()):
                $img = !empty($ad['image']) ? $base_url . 'assets/uploads/ads_form/' . $ad['image'] : $base_url . 'assets/images/test-img.png';
                $price = htmlspecialchars($ad['asking_price']);
                $title = htmlspecialchars($ad['ad_title']);
                $location = htmlspecialchars($ad['location']);
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="card position-relative h-100">
                        <div class="ad-tag poppins-regular">Ad</div>
                        <div class="card-img-ad">
                            <a href="single-ad.php?id=<?= $ad['id'] ?>">
                                <img src="<?= $img ?>" class="" alt="">
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="card-price-det">
                                <h5 class="poppins-bold price-post">$<?= $price ?></h5>
                                <a href="#" class="wish-heart">
                                    <img src="<?= $base_url ?>assets/images/single-ad/heart-icon.svg" alt="">
                                </a>
                            </div>
                            <a href="single-ad.php?id=<?= $ad['id'] ?>">
                                <p class="Post-title fos-16 poppins-regular"><?= $title ?></p>
                            </a>
                            <hr>
                            <div class="d-flex align-items-start poppins-regular fos-14">
                                <img src="<?= $base_url ?>assets/images/location-black.svg" alt="" class="me-2">
                                <h6 class="fos-14"><?= $location ?></h6>
                            </div>
                        </div>
                        <button class="position-absolute top-0 end-0 bg-white border-0 m-2 rounded-circle shadow-sm p-1">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Advertisement Images -->
        <div class="row">
            <div class="section-1-advertisements mt-3 mb-5 d-flex justify-content-center gap-3">
                <div class="image-1 image-secs">
                    <img src="<?= $base_url ?>assets/images/test-image.jpg" alt="" class="w-100">
                </div>
                <div class="image-2 image-secs d-none d-md-block">
                    <img src="<?= $base_url ?>assets/images/test-image-2.jpg" alt="" class="w-100">
                </div>
            </div>
        </div>

        <!-- Next 8 Ads -->
        <div class="row ads-list">
            <?php
            $query = $conn->query("SELECT * FROM ad_form ORDER BY id DESC LIMIT 8 OFFSET 8");
            while ($ad = $query->fetch_assoc()):
                $img = !empty($ad['image']) ? $base_url . 'assets/uploads/ads_form/' . $ad['image'] : $base_url . 'assets/images/test-img.png';
                $price = htmlspecialchars($ad['asking_price']);
                $title = htmlspecialchars($ad['ad_title']);
                $location = htmlspecialchars($ad['location']);
                ?>
                <div class="col-12 col-sm-6 col-md-4  col-lg-3 mb-4">
                    <div class="card position-relative h-100">
                        <div class="ad-tag poppins-regular">Ad</div>
                        <div class="card-img-ad">
                            <a href="single-ad.php?id=<?= $ad['id'] ?>">
                                <img src="<?= $img ?>" class="" alt="">
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="card-price-det">
                                <h5 class="poppins-bold price-post">$<?= $price ?></h5>
                                <a href="#" class="wish-heart">
                                    <img src="<?= $base_url ?>assets/images/single-ad/heart-icon.svg" alt="">
                                </a>
                            </div>
                            <a href="single-ad.php?id=<?= $ad['id'] ?>">
                                <p class="Post-title fos-16 poppins-regular"><?= $title ?></p>
                            </a>
                            <hr>
                            <div class="d-flex align-items-start poppins-regular fos-14">
                                <img src="<?= $base_url ?>assets/images/location-black.svg" alt="" class="me-2">
                                <small><?= $location ?></small>
                            </div>
                        </div>
                        <button class="position-absolute top-0 end-0 bg-white border-0 m-2 rounded-circle shadow-sm p-1">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>


<!-- section test posts  -->
<!-- section test posts  -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#search-btn').on('click', function (e) {
        e.preventDefault();
        var keyword = $('#keyword').val().trim();
        var location = $('#location').val().trim();

        $('#loader').show();
        $('#search-results').empty();

        $.ajax({
            url: 'search-handler.php',
            type: 'POST',
            data: {
                keyword: keyword,
                location: location
            },
            success: function (response) {
                $('#loader').hide();
                $('#search-results').html(response);
            },
            error: function () {
                $('#loader').hide();
                $('#search-results').html('<p>Error fetching results.</p>');
            }
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