<?php
session_start();
include_once('config/config.php'); // always load this first
include_once('partials/header.php');
if (isset($_POST['btn_save_2'])) {


    $blogtitle = $_POST['blogtitle'];
    $blogusername = $_POST['blogusernamess'];
    $blogcategory = $_POST['categoryblog'];
    $blogdiscription = $_POST['Descriptionusrblog'];
    $blogemail = $_POST['ademailuserssblog'];
    $blogmob = $_POST['adsusermobblog'];
    $blogplatform = $_POST['Platformblog'];
    $blogplatlink = $_POST['PlatformLinkblog'];

    // Handle file upload
    $imageNames = [];
    $uploadDir = 'assets/uploads/blog_form/';
    
    if (isset($_FILES['pictures']) && !empty($_FILES['pictures']['name'][0])) {
        foreach ($_FILES['pictures']['tmp_name'] as $key => $tmpName) {
            $error = $_FILES['pictures']['error'][$key];
            $originalName = $_FILES['pictures']['name'][$key];
    
            if ($error === UPLOAD_ERR_OK) {
                $fileExt = pathinfo($originalName, PATHINFO_EXTENSION);
                $newFileName = uniqid() . '.' . $fileExt;
                $uploadPath = $uploadDir . $newFileName;
    
                if (move_uploaded_file($tmpName, $uploadPath)) {
                    $imageNames[] = $newFileName;
                }
            }
        }
    }
    
    $encodedImages = json_encode($imageNames); // ✅ Convert to JSON before insert
    

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO blog_posts (
        title, author_name, category_id, description, email, phone, image, 
        platform, platform_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssissssss", 
    $blogtitle, $blogusername, $blogcategory, $blogdiscription, 
    $blogemail, $blogmob, $encodedImages, $blogplatform, $blogplatlink
);
 
    if ($stmt->execute()) {
           // After successful processing:
     $_SESSION['form_success'] = true;
     // Redirect back to the same page to prevent resubmission on refresh
    header("Location: Blog-form.php");
    exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    

$conn->close();
}
?>
<style>
#multi-preview-area span {
    font-size: 18px;
    line-height: 1;
}
</style>

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
<?php
session_start();
?>

<?php if (!isset($_SESSION['user_id'])): ?>
    <div class="text-center mt-5">
        <p>You must be logged in to post.</p>
        <a href="<?php echo $base_url; ?>login.php" class="theme-btn">Login to Continue</a>
    </div>
<?php else: ?>
  <!-- form section -->
<!-- form section -->
<section class="form-section pb-100">
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="fos-40 playfair-medium mb-30">Create Your Free ads</h1>
                <form action="" method="POST" enctype="multipart/form-data">
                    <!-- form row-->
                    <div class="row">
                        <div class="col-lg-12 col-sm-12 d-flex flex-column mb-30">
                            <label for="blogtitle">Article Title*</label>
                            <input type="text" name="blogtitle" id="blogtitles" />
                        </div>

                        <div class="col-lg-6 col-sm-12 d-flex flex-column mb-30">
                            <label for="blogusrname">Your Name*</label>
                            <input type="text" name="blogusernamess" id="blogusrname" placeholder="" />
                        </div>

                        <div class="col-lg-6 col-sm-12 d-flex flex-column mb-30">
                            <label for="categoryOfblog">Category*</label>
                            <select name="categoryblog" id="categoryOfblog">
                            <?php
                                $result = mysqli_query($conn, "SELECT id, name FROM blog_categories");
                                echo '<option value="">Select Category</option>';
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-lg-12 col-sm-12 d-flex flex-column mb-30">
                            <label for="descriptionsblogs">Description*</label>
                            <textarea name="Descriptionusrblog" id="descriptionsblogs" rows="8"></textarea>
                            <span class="text-end fos-12 mt-2">0/200</span>
                        </div>

                        <div class="col-lg-6 col-sm-12 d-flex flex-column mb-30">
                            <label for="ademailuserblog">Email*</label>
                            <input type="text" name="ademailuserssblog" id="ademailuserblog" placeholder="" />
                        </div>

                        <div class="col-lg-6 col-sm-12 d-flex flex-column mb-30">
                            <label for="teluserblog">Phone</label>
                            <input type="text" name="adsusermobblog" id="teluserblog" placeholder="" />
                        </div>

                        <div class="col-lg-6 col-sm-12 d-flex flex-column mb-30 file-upload-main">
                            <label for="imageaduser">Image</label>
                            <input type="file" name="pictures[]" id="previewimagesblogs" class="file-uploads" multiple />


                            <div class="upload-image-placeholder-area">
                                <img src="<?php echo $base_url; ?>assets/images/upload-place.png" alt="" />
                                <p class="poppins-medium">
                                    Drag and drop an image, or
                                    <span class="color-pink">Browse</span>
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-6 col-sm-12 d-flex flex-column mb-30 file-upload-main">
                            <label for="previewimagesblogs">Preview Images</label>
                            <div id="multi-preview-area" class="d-flex flex-wrap gap-3"></div>
                        </div>

                        <div class="col-lg-2 col-sm-12 d-flex flex-column mb-30">
                            <label for="Platformaduserblogs">Platform</label>
                            <input type="text" name="Platformblog" id="Platformaduserblogs" />
                        </div>

                        <div class="col-lg-10 col-sm-12 d-flex flex-column mb-30">
                            <label for="PlatformLinkaduserblog">Link</label>
                            <input type="text" name="PlatformLinkblog" id="PlatformLinkaduserblog" />
                        </div>

                        <div class="col-lg-12 col-sm-12 d-flex flex-column mb-50">
                            <a href="#" class="color-pink poppins-medium">Add More</a>
                        </div>

                        <div class="col-lg-12 col-sm-12 mb-30 text-center text-md-start">
                        <button type="submit" name="btn_save_2" id="btn_save" class="theme-btn">Post This Ad</button>
                        </div>
                    </div>
                    <!-- form row -->
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('previewimagesblogs').addEventListener('change', function (e) {
    const previewContainer = document.getElementById('multi-preview-area');
    previewContainer.innerHTML = ''; // Clear previous previews

    const files = e.target.files;
    if (!files.length) return;

    Array.from(files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function (event) {
            const previewDiv = document.createElement('div');
            previewDiv.classList.add('position-relative');
            previewDiv.style.width = '100px';
            previewDiv.style.height = '100px';

            const img = document.createElement('img');
            img.src = event.target.result;
            img.classList.add('img-fluid', 'rounded');
            img.style.objectFit = 'cover';
            img.style.width = '100%';
            img.style.height = '100%';

            const removeBtn = document.createElement('span');
            removeBtn.innerHTML = '&times;';
            removeBtn.classList.add('position-absolute', 'top-0', 'end-0', 'bg-danger', 'text-white', 'p-1', 'rounded-circle');
            removeBtn.style.cursor = 'pointer';

            removeBtn.onclick = () => {
                previewDiv.remove();
                // Optional: Clear file from input (reset input if all removed)
                if (previewContainer.children.length === 1) {
                    e.target.value = '';
                }
            };

            previewDiv.appendChild(img);
            previewDiv.appendChild(removeBtn);
            previewContainer.appendChild(previewDiv);
        };
        reader.readAsDataURL(file);
    });
});
</script>

<!-- form section -->
<!-- form section -->
<?php endif; ?>


<!-- footer -->
<!-- footer -->
<?php
 include_once('partials/footer.php');
 ?>
<!-- footer -->
<!-- footer -->