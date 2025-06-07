<?php
session_start();


include_once('config/config.php'); // always load this first
include_once('partials/header.php');



$currentUrl = $base_url . 'single-article.php?id=' . $_GET['id'];


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid blog ID.";
    exit;
}

$blogId = intval($_GET['id']);

$query = "SELECT * FROM blog_posts WHERE id = $blogId";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $blog = mysqli_fetch_assoc($result);
    $title = htmlspecialchars($blog['title']);  // Title of the article
    $description = htmlspecialchars($blog['description']);  // Description of the article
    // Decode images JSON and get the first image
    $images = json_decode($blog['image'], true);
    $firstImage = !empty($images[0]) ? $base_url . 'assets/uploads/blog_form/' . $images[0] : $base_url . 'assets/images/test-img.png';
} else {
    echo "Blog not found.";
    exit;
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
<section class="single-article-details pb-100">
    <div class="container">
        <div class="row">
            <div class="col-lg-9 ps-0">
                <div class="col-12 article-top-img mb-30">
                <img src="<?php echo $firstImage; ?>" alt="" class="w-100" />

                <div class="user-info d-flex gap-2 align-items-center justify-content-end">
    <img src="<?php echo $base_url; ?>assets/images/userimage.png" alt="" />
    <h1 class="fos-12 poppins-medium m-0 text-white"><?php echo htmlspecialchars($blog['author_name']); ?></h1>
    <h1 class="fos-12 poppins-medium m-0 text-white">|</h1>
    <h1 class="fos-12 poppins-medium m-0 text-white">
        <?php echo date('jS F, Y', strtotime($blog['created_at'])); ?>
    </h1>
</div>

                </div>
                <div class="col-12">
                    <h1 class="poppins-medium fos-30 mb-20">
                    <?php echo $title; ?>
                    </h1>
                    <p class="poppins-regular mb-30">
                    <?php echo nl2br($description); ?>
                    </p>
                    
                    
                    
                    <div class="col mb-40">
                        <hr />
                    </div>
                    <div class="reviews">
    <h1 class="fos-24">Join the conversation</h1>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <h1 class="not-login fos-16">
            You must be logged in to comment. <a href="<?= $base_url; ?>login.php" class="color-pink">Login Here</a>
        </h1>
    <?php else: ?>
        <form id="commentForm">
            <div class="comment-sec d-flex align-items-start gap-3 mb-3">
                <img src="<?php echo $base_url; ?>assets/images/userimage.png" alt="" />
                <textarea name="comment" id="commentbyallusers" rows="5" class="w-100" required></textarea>
            </div>
            <input type="hidden" name="blog_id" value="<?= $_GET['id'] ?>" />
            <input type="hidden" name="user_name" value="<?= $_SESSION['user_name'] ?>" />
            <button type="submit" class="theme-btn">Submit Comment</button>
        </form>
    <?php endif; ?>
</div>

<!-- Comments will be loaded here -->
<div id="all-comments"></div>


                </div>
            </div>
            <div class="col-lg-3 m-0 pe-0">
            <?php
$currentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Try to get the next article by ID
$nextQuery = "SELECT * FROM blog_posts WHERE id > $currentId ORDER BY id ASC LIMIT 1";
$nextResult = mysqli_query($conn, $nextQuery);

if ($nextResult && mysqli_num_rows($nextResult) > 0) {
    $nextArticle = mysqli_fetch_assoc($nextResult);
} else {
    // No next article found, get the first article (loop back)
    $firstQuery = "SELECT * FROM blog_posts ORDER BY id ASC LIMIT 1";
    $firstResult = mysqli_query($conn, $firstQuery);
    $nextArticle = ($firstResult && mysqli_num_rows($firstResult) > 0) ? mysqli_fetch_assoc($firstResult) : null;
}

if ($nextArticle) {
    // Get first image
    $nextImages = json_decode($nextArticle['image'], true);
    $nextImage = !empty($nextImages[0]) ? $base_url . 'assets/uploads/blog_form/' . $nextImages[0] : $base_url . 'assets/images/test-img.png';

    // Shorten description to 15 words
    $descWords = explode(' ', strip_tags($nextArticle['description']));
    $shortDesc = implode(' ', array_slice($descWords, 0, 15)) . (count($descWords) > 15 ? '...' : '');

    echo '<div class="next-post-sidebar mb-40">';
    echo '    <a href="single-article.php?id=' . $nextArticle['id'] . '">';
    echo '        <img src="' . $nextImage . '" class="img-next-post mb-24" alt="" />';
    echo '        <h1 class="poppins-medium fos-20 mb-20">' . htmlspecialchars($nextArticle['title']) . '</h1>';
    echo '    </a>';
    echo '    <p class="fos-14">' . htmlspecialchars($shortDesc) . '</p>';
    echo '</div>';
}
?>


                <div class="share-with-community mb-50">
                    <h1 class="fos-20 poppins-medium mb-24">Share with your community</h1>
                    <div class="social-share d-flex justify-content-between">
    <a href="https://www.instagram.com/?url=<?= urlencode($currentUrl) ?>" target="_blank">
        <img src="<?= $base_url; ?>assets/images/insta.svg" alt="Share on Instagram" />
    </a>
    <a href="https://twitter.com/intent/tweet?url=<?= urlencode($currentUrl) ?>" target="_blank">
        <img src="<?= $base_url; ?>assets/images/tweetr.svg" alt="Share on Twitter" />
    </a>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($currentUrl) ?>" target="_blank">
        <img src="<?= $base_url; ?>assets/images/facebook.svg" alt="Share on Facebook" />
    </a>
    <a href="https://x.com/intent/post?url=<?= urlencode($currentUrl) ?>" target="_blank">
        <img src="<?= $base_url; ?>assets/images/x.svg" alt="Share on X" />
    </a>
    <!-- WhatsApp -->
<a href="https://wa.me/?text=<?= urlencode($currentUrl) ?>" target="_blank">WhatsApp</a>

<!-- LinkedIn -->
<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($currentUrl) ?>" target="_blank">LinkedIn</a>

</div>

                </div>
                <div class="inthisarticle">
                    <h1 class="fos-30 poppins-medium mb-30">In this article</h1>
                    <ul class="inthisarticlelist">
                        <li class="article-list-item active">
                            <a href="#">10 secret tips for managing a team remotely</a>
                        </li>
                        <li class="article-list-item">
                            <a href="#">10 secret tips for managing a team remotely</a>
                        </li>
                        <li class="article-list-item">
                            <a href="#">10 secret tips for managing a team remotely</a>
                        </li>
                        <li class="article-list-item">
                            <a href="#">10 secret tips for managing a team remotely</a>
                        </li>
                        <li class="article-list-item">
                            <a href="#">10 secret tips for managing a team remotely</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- article details -->
<!-- article details -->

<!-- Related blogs -->
<!-- Related blogs -->
<?php
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch current article
$current_query = "SELECT * FROM blog_posts WHERE id = $article_id LIMIT 1";
$current_result = mysqli_query($conn, $current_query);

if (!$current_result || mysqli_num_rows($current_result) == 0) {
    echo "Article not found.";
    exit;
}

$current_article = mysqli_fetch_assoc($current_result);
$current_category = $current_article['category_id']; // or 'cat_id'

$related_query = "SELECT * FROM blog_posts 
                  WHERE category_id = '$current_category' AND id != $article_id 
                  ORDER BY created_at DESC LIMIT 3";

$related_result = mysqli_query($conn, $related_query);
$relatedImages = json_decode($related['image'], true);
$relatedImage = !empty($relatedImages[0]) ? $base_url . 'assets/uploads/blog_form/' . $relatedImages[0] : $base_url . 'assets/images/test-img.png';
?>

<section class="related-blog">
    <div class="container">
        <div class="row">
            <div class="">
                <h1 class="poppins-medium fos-30 mb-30">Related Blog Post</h1>
                <div class="p-0 d-flex flex-wrap">
                    
                   
                <?php if ($related_result && mysqli_num_rows($related_result) > 0) {
    while ($related = mysqli_fetch_assoc($related_result)) {?>
    <div class="col-12 col-sm-6 col-lg-4 mb-4 px-2">
        <div class="article-card position-relative">
            <div class="card-img-blog">
                <a href="<?= $base_url; ?>single-article.php?id=<?= $related['id']; ?>">
                <img src="<?= $relatedImage ?>" class="img-fluid" alt="">
                </a>
            </div>
            <div class="card-body-blog">
                <h1 class="fos-20 poppins-regular mb-20">
                    <a href="<?= $base_url; ?>single-article.php?id=<?= $related['id']; ?>" class="text-dark text-decoration-none">
                        <?= htmlspecialchars($related['title']); ?>
                    </a>
                </h1>
                <p>
                    <?= substr(strip_tags($related['description']), 0, 100); ?>...
                </p>
            </div>
            <div class="card-foot-blog d-flex align-items-center gap-2">
                <img src="<?= $base_url . $related['user_image']; ?>" alt="" class="user-image-blog">
                <h1 class="fos-12 usernameblog m-0"><?= $related['user_name']; ?></h1>
                <h1 class="fos-12 usernameblog m-0">|</h1>
                <h1 class="fos-12 dateblog m-0"><?= date('jS F, Y', strtotime($related['created_at'])); ?></h1>
            </div>
        </div>
    </div>
<?php   
  }
                }

?>


                    
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Related blogs -->
<!-- Related blogs -->

<!-- Pagination -->
<!-- Pagination -->
<?php
$currentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch current article
$currentQuery = "SELECT * FROM blog_posts WHERE id = $currentId LIMIT 1";
$currentResult = mysqli_query($conn, $currentQuery);

if (!$currentResult || mysqli_num_rows($currentResult) == 0) {
    echo "Article not found.";
    exit;
}

$currentArticle = mysqli_fetch_assoc($currentResult);

// Fetch next article
$nextQuery = "SELECT * FROM blog_posts WHERE id > $currentId ORDER BY id ASC LIMIT 1";
$nextResult = mysqli_query($conn, $nextQuery);
$nextArticle = mysqli_fetch_assoc($nextResult);

// Fetch previous article
$prevQuery = "SELECT * FROM blog_posts WHERE id < $currentId ORDER BY id DESC LIMIT 1";
$prevResult = mysqli_query($conn, $prevQuery);
$prevArticle = mysqli_fetch_assoc($prevResult);
?>

<section class="pagination-sec pb-100">
    <div class="container">
        <div class="row">
            <div class="col d-flex align-items-center justify-content-between">
                <?php if ($prevArticle): ?>
                    <a href="single-article.php?id=<?php echo $prevArticle['id']; ?>" class="pagination-btn">&larr; Previous</a>
                <?php else: ?>
                    <span class="pagination-btn disabled">&larr; Previous</span>
                <?php endif; ?>

                <?php if ($nextArticle): ?>
                    <a href="single-article.php?id=<?php echo $nextArticle['id']; ?>" class="pagination-btn">Next &rarr;</a>
                <?php else: ?>
                    <span class="pagination-btn disabled">Next &rarr;</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Pagination -->
<!-- Pagination -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    const blogId = <?= $_GET['id'] ?>;

    function loadComments() {
        $.post('ajax/fetch_comments.php', { blog_id: blogId }, function(data) {
            $('#all-comments').html(data);
        });
    }

    loadComments(); // Initial load

    $('#commentForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/submit_comment.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.trim() === "success") {
                    $('#commentbyallusers').val('');
                    loadComments();
                } else {
                    alert(response);
                }
            }
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