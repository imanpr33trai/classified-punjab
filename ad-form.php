<?php
// ad-form.php - This file handles ad creation by users.
// PHP is the server-side language processing this script.
// Ensure this file has a .php extension.

// Start session if not already started. Essential for user auth, CSRF, and flash messages.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "config/config.php"; // Contains $conn and $base_url

// --- Form Processing Logic ---
$errors = []; // Array to hold validation errors
$form_data = []; // Array to hold submitted data for sticky forms

// --- Check if user is logged in ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: " . $base_url . "login.php?message=login_required");
    exit;
}

// --- Pre-populate user details ---
$default_user_name = $_SESSION['user_name'] ?? '';
$default_user_email = $_SESSION['user_email'] ?? '';

// --- Generate CSRF token ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_save'])) {
    // 1. Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors['csrf'] = 'Invalid request. Please try submitting the form again.';
    } else {
        // 2. Sanitize and Collect Form Data
        $form_data['category'] = trim($_POST['adcategory'] ?? '');
        $form_data['subcategory'] = trim($_POST['adsubcategory'] ?? '');
        $form_data['other'] = trim($_POST['adothercat'] ?? '');
        $form_data['adTitle'] = trim($_POST['adTitlemytit'] ?? '');
        $form_data['askingPrice'] = trim($_POST['askingPriceforad'] ?? '');
        $form_data['description'] = trim($_POST['descriptionforad'] ?? '');
        $form_data['name'] = trim($_POST['adusername'] ?? $default_user_name);
        $form_data['organization'] = trim($_POST['adorganizationuser'] ?? '');
        $form_data['email'] = trim($_POST['ademailuser'] ?? $default_user_email);
        $form_data['phone'] = trim($_POST['adphoneuser'] ?? '');
        $form_data['location'] = trim($_POST['adlocationuser'] ?? '');
        $form_data['city'] = trim($_POST['adcityuser'] ?? '');
        $form_data['postalCode'] = trim($_POST['adpostalCodeuser'] ?? '');
        $form_data['expireIn'] = trim($_POST['adexpireingin'] ?? '');
        $form_data['code'] = trim($_POST['adtypecode'] ?? '');
        $form_data['platform'] = trim($_POST['adplatformuser'] ?? '');
        $form_data['link'] = trim($_POST['adlinkuser'] ?? '');

        // 3. Server-Side Validations (Same detailed validations as previous response)
        if (empty($form_data['category']))
            $errors['category'] = 'Category is required.';
        if (!empty($form_data['category'])) { // Only validate subcategory if category is chosen
            $stmt_check_sub = $conn->prepare("SELECT COUNT(*) as count FROM ad_subcategories WHERE category_id = ? AND status = 'live'");
            $stmt_check_sub->bind_param("i", $form_data['category']);
            $stmt_check_sub->execute();
            $result_sub = $stmt_check_sub->get_result()->fetch_assoc();
            if ($result_sub['count'] > 0 && empty($form_data['subcategory'])) {
                $errors['subcategory'] = 'Subcategory is required for the selected category.';
            }
            $stmt_check_sub->close();
        }
        if (empty($form_data['adTitle']))
            $errors['adTitle'] = 'Ad Title is required.';
        elseif (strlen($form_data['adTitle']) < 5)
            $errors['adTitle'] = 'Ad Title must be at least 5 characters.';
        elseif (strlen($form_data['adTitle']) > 100)
            $errors['adTitle'] = 'Ad Title cannot exceed 100 characters.';
        if (!empty($form_data['askingPrice']) && !is_numeric($form_data['askingPrice']))
            $errors['askingPrice'] = 'Asking price must be a number.';
        elseif (!empty($form_data['askingPrice']) && $form_data['askingPrice'] < 0)
            $errors['askingPrice'] = 'Asking price cannot be negative.';
        if (empty($form_data['description']))
            $errors['description'] = 'Description is required.';
        elseif (strlen($form_data['description']) > 2000)
            $errors['description'] = 'Description cannot exceed 2000 characters.';
        if (empty($form_data['name']))
            $errors['name'] = 'Your Name is required.';
        elseif (!preg_match("/^[a-zA-Z\s'-]+$/", $form_data['name']))
            $errors['name'] = 'Name can only contain letters, spaces, hyphens, and apostrophes.';
        if (empty($form_data['email']))
            $errors['email'] = 'Email is required.';
        elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL))
            $errors['email'] = 'Invalid email format.';
        if (!empty($form_data['phone']) && !preg_match('/^[0-9\s\+\-\(\)]{7,20}$/', $form_data['phone']))
            $errors['phone'] = 'Invalid phone number format.';
        if (empty($form_data['city']))
            $errors['city'] = 'City, town, or neighborhood is required.';
        if (empty($form_data['postalCode']))
            $errors['postalCode'] = 'Postal code is required.';
        elseif (!preg_match('/^[a-zA-Z0-9\s-]{3,10}$/', $form_data['postalCode']))
            $errors['postalCode'] = 'Invalid postal code format.';
        if (empty($form_data['expireIn']))
            $errors['expireIn'] = 'Expiration duration is required.';
        if (!empty($form_data['link']) && !filter_var($form_data['link'], FILTER_VALIDATE_URL))
            $errors['link'] = 'Platform link must be a valid URL.';

        // 4. Handle File Upload (Image) - Same detailed logic as previous response
        $imageFileName = null;
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['picture']['tmp_name'];
            $originalFileName = basename($_FILES['picture']['name']);
            $fileSize = $_FILES['picture']['size'];
            $fileType = $_FILES['picture']['type'];
            $fileNameCmps = explode(".", $originalFileName);
            $fileExtension = strtolower(end($fileNameCmps));
            $newFileName = uniqid('adimg_', true) . '.' . $fileExtension;
            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB

            if (in_array($fileExtension, $allowedfileExtensions)) {
                if ($fileSize <= $maxFileSize) {
                    $uploadFileDir = 'assets/uploads/ads_form/';
                    if (!is_dir($uploadFileDir))
                        mkdir($uploadFileDir, 0755, true);
                    $dest_path = $uploadFileDir . $newFileName;
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $imageFileName = $newFileName;
                    } else {
                        $errors['picture'] = 'Error moving uploaded file.';
                        error_log("File upload error: Could not move " . $fileTmpPath . " to " . $dest_path);
                    }
                } else {
                    $errors['picture'] = 'File exceeds maximum size of 5MB.';
                }
            } else {
                $errors['picture'] = 'Invalid file type. Allowed: ' . implode(', ', $allowedfileExtensions) . '.';
            }
        } elseif (isset($_FILES['picture']) && $_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadErrors = [ /* ... error messages ... */]; // As before
            $errorCode = $_FILES['picture']['error'];
            $errors['picture'] = $uploadErrors[$errorCode] ?? "Unknown file upload error.";
        }

        // 5. If No Errors, Insert into DB - Same detailed logic as previous response
        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO ad_form (category_id, subcategory_id, other_category, ad_title, asking_price, description, user_name, organisation, email, phone, location, city_town_neighbourhood, postal_code, expires_in, verification_code, image, platforms, platform_links, user_id_fk) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $askingPriceDb = !empty($form_data['askingPrice']) ? (float) $form_data['askingPrice'] : null;
            $userIdFk = $_SESSION['user_id'];
            $stmt->bind_param("iisssisssssssissssi", $form_data['category'], $form_data['subcategory'], $form_data['other'], $form_data['adTitle'], $askingPriceDb, $form_data['description'], $form_data['name'], $form_data['organization'], $form_data['email'], $form_data['phone'], $form_data['location'], $form_data['city'], $form_data['postalCode'], $form_data['expireIn'], $form_data['code'], $imageFileName, $form_data['platform'], $form_data['link'], $userIdFk);
            if ($stmt->execute()) {
                $_SESSION['form_success_message'] = "Your ad has been created successfully!";
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header("Location: ad-form.php?status=success");
                exit;
            } else {
                $errors['db_error'] = "Error creating ad: " . $stmt->error;
                error_log("Ad form DB error: " . $stmt->error);
            }
            $stmt->close();
        }
    }
}

// Helper functions (same as before)
function oldValue(string $field_name, array $form_data_array, string $default = ''): string
{
    return htmlspecialchars($form_data_array[$field_name] ?? $_POST[$field_name] ?? $default, ENT_QUOTES, 'UTF-8');
}
function displayError(string $field_name, array $errors_array): string
{
    if (isset($errors_array[$field_name])) {
        return '<div class="invalid-feedback d-block" id="' . htmlspecialchars($field_name) . '-error">' . htmlspecialchars($errors_array[$field_name]) . '</div>';
    }
    return '';
}

include "partials/header.php";
?>

<style>
    <?php /* Previous styles remain unchanged */ ?>
    #preview-images span.remove-preview {
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
        background-color: rgba(220, 53, 69, 0.8);
        color: white;
        padding: 2px 6px;
        border-radius: 50%;
        position: absolute;
        top: -5px;
        right: -5px;
        z-index: 10;
    }

    #preview-images .preview-item {
        width: 100px;
        height: 100px;
        position: relative;
    }    <?php /* Previous upload styles remain unchanged */ ?>
    .file-upload-main .upload-image-placeholder-area {
        border: 2px dashed #dee2e6;
        padding: 20px;
        cursor: pointer;
        transition: background-color 0.2s ease-in-out;
    }

    .file-upload-main .upload-image-placeholder-area:hover {
        background-color: #f8f9fa;
    }

    .file-upload-main .file-uploads {
        display: none;
    }    <?php /* New Responsive Styles */ ?>
    @media (max-width: 991.98px) {
        <?php /* Tablet and smaller */ ?>
        .fos-40 {
            font-size: 32px; /* Previous: 40px */
        }
        
        .form-section .col-lg-6,
        .form-section .col-lg-4,
        .form-section .col-lg-12 {
            margin-bottom: 20px; /* Add spacing between stacked columns */
        }

        .file-upload-main .upload-image-placeholder-area {
            min-height: 150px; /* Previous: auto */
        }

        #preview-images {
            gap: 10px; /* Previous: 15px */
            justify-content: center;
        }
    }    @media (max-width: 767.98px) {
        <?php /* Mobile devices */ ?>
        .fos-40 {
            font-size: 28px; /* Previous: 32px */
        }

        .form-control, 
        .form-select {
            height: 48px; /* Previous: 56px */
            padding: 8px 15px; /* Previous: 10px 30px */
        }

        .theme-btn {
            padding: 10px 20px; /* Previous: 13px 33px */
            width: 100%; /* Previous: auto */
        }

        .file-upload-main .upload-image-placeholder-area {
            padding: 15px; /* Previous: 20px */
        }

        #preview-images .preview-item {
            width: 80px; /* Previous: 100px */
            height: 80px; /* Previous: 100px */
        }
    }    @media (max-width: 575.98px) {
        <?php /* Small mobile devices */ ?>
        .fos-40 {
            font-size: 24px; /* Previous: 28px */
        }

        .form-section {
            padding: 30px 15px; /* Previous: no specific padding */
        }

        .form-label {
            font-size: 14px; /* Previous: 16px */
        }

        .file-upload-main .upload-image-placeholder-area img {
            max-height: 40px; /* Previous: not specified */
        }

        #preview-images {
            gap: 8px; /* Previous: 10px */
        }

        #preview-images .preview-item {
            width: 70px; /* Previous: 80px */
            height: 70px; /* Previous: 80px */
        }

        .mb-30 {
            margin-bottom: 20px; /* Previous: 30px */
        }

        .mb-50 {
            margin-bottom: 30px; /* Previous: 50px */
        }
    }
</style>

<!-- Breadcrumb (Same as your original if it was fine) -->
<section class="breadcrump py-3">
    <div class="container">
        <div class="row">
            <div class="col d-flex gap-2"> <?php /* Retained d-flex gap-2 for original spacing */ ?>
                <a href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>index.php"
                    class="text-decoration-none breadcrump-links breadcrump-link-1">Home »</a>
                <span class="text-decoration-none breadcrump-links breadcrump-link-2">Post Ad</span>
            </div>
        </div>
    </div>
</section>

<!-- Form Section -->
<section class="form-section pb-100"> <?php /* Retained pb-100 */ ?>
    <div class="container">
        <div class="row">
            <div class="col"> <?php /* Original full-width column for the form area */ ?>
                <h1 class="fos-40 playfair-medium mb-30 text-center text-lg-start">Create Your Free ads</h1>

                <?php if (!empty($_SESSION['form_success_message'])): ?>
                    <div id="successPopup" class="alert alert-success text-center mb-30" role="alert">
                        <?php echo htmlspecialchars($_SESSION['form_success_message'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                <?php $general_errors = array_filter($errors, fn($key) => in_array($key, ['csrf', 'db_error']), ARRAY_FILTER_USE_KEY);
                if (!empty($general_errors)): ?>
                    <div class="alert alert-danger mb-30" role="alert">
                        <?php foreach ($general_errors as $err_msg)
                            echo "<p class='mb-0'>" . htmlspecialchars($err_msg, ENT_QUOTES, 'UTF-8') . "</p>"; ?>
                    </div>
                <?php endif; ?>                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST"
                    enctype="multipart/form-data" id="adForm" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="row gy-3"> <?php /* Added gy-3 for consistent vertical spacing between rows */ ?>
                        <?php /* Category */ ?>
                        <div class="col-lg-4 col-sm-12 d-flex flex-column mb-30">
                            <label for="categoryOfads" class="form-label">Category*</label>
                            <select name="adcategory" id="categoryOfads"
                                class="form-select <?php echo isset($errors['category']) ? 'is-invalid' : ''; ?>"
                                required aria-describedby="categoryOfads-error">
                                <option value="">Select Category</option>
                                <?php if ($conn) {
                                    $cats_query = "SELECT id, name FROM ad_categories WHERE status = 'live' ORDER BY name ASC";
                                    $cats_result = $conn->query($cats_query);
                                    if ($cats_result) {
                                        while ($row = $cats_result->fetch_assoc()) {
                                            $selected = (oldValue('category', $form_data) == $row['id']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
                                        }
                                    }
                                } ?>
                            </select>
                            <?php echo displayError('category', $errors); ?>
                        </div>

                        <?php /* Sub Category */ ?>
                        <div class="col-lg-4 col-sm-12 d-flex flex-column mb-30">
                            <label for="subcategoryOfads" class="form-label">Subcategory*</label>
                            <select name="adsubcategory" id="subcategoryOfads"
                                class="form-select <?php echo isset($errors['subcategory']) ? 'is-invalid' : ''; ?>"
                                aria-describedby="subcategoryOfads-error">
                                <option value="">Select Category First</option>
                                <?php if (!empty(oldValue('category', $form_data)) && !empty(oldValue('subcategory', $form_data)) && $conn) {
                                    $stmt_sub_populate = $conn->prepare("SELECT id, name FROM ad_subcategories WHERE category_id = ? AND status = 'live' ORDER BY name ASC");
                                    $cat_id_val = oldValue('category', $form_data); // Assign to var before bind_param
                                    $stmt_sub_populate->bind_param("i", $cat_id_val);
                                    $stmt_sub_populate->execute();
                                    $result_sub_populate = $stmt_sub_populate->get_result();
                                    while ($sub_row = $result_sub_populate->fetch_assoc()) {
                                        $selected_sub = (oldValue('subcategory', $form_data) == $sub_row['id']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($sub_row['id']) . "' $selected_sub>" . htmlspecialchars($sub_row['name']) . "</option>";
                                    }
                                    $stmt_sub_populate->close();
                                } ?>
                            </select>
                            <?php echo displayError('subcategory', $errors); ?>
                        </div>

                        <?php /* Others (for category) - Kept original structure */ ?>
                        <div class="col-lg-4 col-sm-12 d-flex flex-column mb-30" id="otherCategoryWrapperOriginal"
                            style="<?php echo (oldValue('subcategory', $form_data) == 'other_value_placeholder_original' || !empty(oldValue('other', $form_data))) ? '' : 'display:none;'; ?>">
                            <label for="otherCategory" class="form-label">Others*</label>
                            <input type="text" name="adothercat" id="otherCategory"
                                class="form-control <?php echo isset($errors['other']) ? 'is-invalid' : ''; ?>"
                                value="<?php echo oldValue('other', $form_data); ?>"
                                placeholder="Specify if 'Other' selected" aria-describedby="otherCategory-error" />
                            <?php echo displayError('other', $errors); ?>
                        </div>

                        <?php /* Ad Title - Kept original structure */ ?>
                        <div class="col-lg-6 col-sm-12 d-flex flex-column mb-30">
                            <label for="adTitle" class="form-label">Ad Title*</label>
                            <input type="text" name="adTitlemytit" id="adTitle"
                                class="form-control <?php echo isset($errors['adTitle']) ? 'is-invalid' : ''; ?>"
                                value="<?php echo oldValue('adTitle', $form_data); ?>" required minlength="5"
                                maxlength="100" placeholder="e.g., Gently Used Sofa for Sale"
                                aria-describedby="adTitle-error" />
                            <?php echo displayError('adTitle', $errors); ?>
                        </div>

                        <?php /* Asking price - Kept original structure */ ?>
                        <div class="col-lg-6 col-sm-12 d-flex flex-column mb-30">
                            <label for="adPrice" class="form-label">Asking Price (Optional)</label>
                            <input type="number" name="askingPriceforad" id="adPrice"
                                class="form-control <?php echo isset($errors['askingPrice']) ? 'is-invalid' : ''; ?>"
                                value="<?php echo oldValue('askingPrice', $form_data); ?>"
                                placeholder="e.g., 150 or leave blank" step="0.01" min="0"
                                aria-describedby="adPrice-error" />
                            <?php echo displayError('askingPrice', $errors); ?>
                        </div>

                        <?php /* Description - Kept original structure */ ?>
                        <div class="col-lg-12 col-sm-12 d-flex flex-column mb-30">
                            <label for="descriptions" class="form-label">Description*</label>
                            <textarea name="descriptionforad" id="descriptions"
                                class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>"
                                rows="8" required placeholder="Provide details..."
                                aria-describedby="descriptions-error descriptions-char-count"><?php echo oldValue('description', $form_data); ?></textarea>
                            <span id="descriptions-char-count" class="text-end fos-12 mt-2">0/2000</span>
                            <?php echo displayError('description', $errors); ?>
                        </div>

                        <?php /* Your Name - Kept original structure */ ?>
                        <div class="col-lg-6 col-sm-12 d-flex flex-column mb-30">
                            <label for="aduserName" class="form-label">Your Name*</label>
                            <input type="text" name="adusername" id="aduserName"
                                class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                                value="<?php echo oldValue('name', $form_data, $default_user_name); ?>" required
                                placeholder="Your full name" aria-describedby="aduserName-error" />
                            <?php echo displayError('name', $errors); ?>
                        </div>

                        <?php /* Organisation - Kept original structure */ ?>
                        <div class="col-lg-6 col-sm-12 d-flex flex-column mb-30">
                            <label for="aduserOrganisation" class="form-label">Organisation (Optional)</label>
                            <input type="text" name="adorganizationuser" id="aduserOrganisation"
                                class="form-control <?php echo isset($errors['organization']) ? 'is-invalid' : ''; ?>"
                                value="<?php echo oldValue('organization', $form_data); ?>" placeholder="Company name"
                                aria-describedby="aduserOrganisation-error" />
                            <?php echo displayError('organization', $errors); ?>
                        </div>

                        <?php /* Email - Kept original structure */ ?>
                        <div class="col-lg-6 col-sm-12 d-flex flex-column mb-30">
                            <label for="ademailuser" class="form-label">Email*</label>
                            <input type="email" name="ademailuser" id="ademailuser"
                                class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                                value="<?php echo oldValue('email', $form_data, $default_user_email); ?>" required
                                placeholder="your.email@example.com" aria-describedby="ademailuser-error" />
                            <?php echo displayError('email', $errors); ?>
                        </div>

                        <?php /* Phone - Kept original structure */ ?>
                        <div class="col-lg-6 col-sm-12 d-flex flex-column mb-30">
                            <label for="teluserads" class="form-label">Phone (Optional)</label>
                            <input type="tel" name="adphoneuser" id="teluserads"
                                class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>"
                                value="<?php echo oldValue('phone', $form_data); ?>" placeholder="+1-555-123-4567"
                                aria-describedby="teluserads-error" />
                            <?php echo displayError('phone', $errors); ?>
                        </div>

                        <?php /* Location - Kept original structure */ ?>
                        <div class="col-lg-12 col-sm-12 d-flex flex-column mb-30">
                            <label for="adlocuser" class="form-label">Location (Optional)</label>
                            <input type="text" name="adlocationuser" id="adlocuser"
                                class="form-control <?php echo isset($errors['location']) ? 'is-invalid' : ''; ?>"
                                value="<?php echo oldValue('location', $form_data); ?>"
                                placeholder="Street address or general area" aria-describedby="adlocuser-error" />
                            <?php echo displayError('location', $errors); ?>
                        </div>

                        <?php /* City, town, or neighborhood - Kept original structure */ ?>
                        <div class="col-lg-12 col-sm-12 d-flex flex-column mb-30">
                            <label for="adusercityTown" class="form-label">City, town, or neighborhood*</label>
                            <input type="text" name="adcityuser" id="adusercityTown"
                                class="form-control <?php echo isset($errors['city']) ? 'is-invalid' : ''; ?>"
                                value="<?php echo oldValue('city', $form_data); ?>" required
                                placeholder="e.g., Springfield" aria-describedby="adusercityTown-error" />
                            <?php echo displayError('city', $errors); ?>
                        </div>

                        <?php /* Postal code - Kept original structure */ ?>
                        <div class="col-lg-4 col-sm-12 d-flex flex-column mb-30">
                            <label for="adpostalcode" class="form-label">Postal code*</label>
                            <input type="text" name="adpostalCodeuser" id="adpostalcode"
                                class="form-control <?php echo isset($errors['postalCode']) ? 'is-invalid' : ''; ?>"
                                value="<?php echo oldValue('postalCode', $form_data); ?>" required placeholder="A1B 2C3"
                                aria-describedby="adpostalcode-error" />
                            <?php echo displayError('postalCode', $errors); ?>
                        </div>

                        <?php /* Expires In - Kept original structure */ ?>
                        <div class="col-lg-4 col-sm-12 d-flex flex-column mb-30">
                            <label for="expiresindate" class="form-label">Expires In*</label>
                            <select name="adexpireingin" id="expiresindate"
                                class="form-select <?php echo isset($errors['expireIn']) ? 'is-invalid' : ''; ?>"
                                required aria-describedby="expiresindate-error">
                                <option value="">Select Duration</option>
                                <option value="7_days" <?php echo (oldValue('expireIn', $form_data) == '7_days') ? 'selected' : ''; ?>>7 Days</option>
                                <option value="14_days" <?php echo (oldValue('expireIn', $form_data) == '14_days') ? 'selected' : ''; ?>>14 Days</option>
                                <option value="30_days" <?php echo (oldValue('expireIn', $form_data) == '30_days') ? 'selected' : ''; ?>>30 Days</option>
                                <option value="60_days" <?php echo (oldValue('expireIn', $form_data) == '60_days') ? 'selected' : ''; ?>>60 Days</option>
                            </select>
                            <?php echo displayError('expireIn', $errors); ?>
                        </div>

                        <?php /* Please type this code - Kept original structure */ ?>
                        <div class="col-lg-4 col-sm-12 d-flex flex-column mb-30">
                            <label for="pleaseTypeThisCode" class="form-label">Please type this code (if
                                applicable)</label>
                            <input type="text" name="adtypecode" id="pleaseTypeThisCode"
                                class="form-control <?php echo isset($errors['code']) ? 'is-invalid' : ''; ?>"
                                value="<?php echo oldValue('code', $form_data); ?>" placeholder="CAPTCHA"
                                aria-describedby="pleaseTypeThisCode-error" />
                            <?php echo displayError('code', $errors); ?>
                        </div>

                        <?php /* Image Upload - Kept original structure, with internal improvements */ ?>
                        <div class="col-lg-6 col-sm-12 d-flex flex-column mb-30 file-upload-main">
                            <label for="imageaduser" class="form-label">Image (Optional)</label>
                            <input type="file" name="picture" id="imageaduser"
                                class="file-uploads <?php echo isset($errors['picture']) ? 'is-invalid' : ''; ?>"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                aria-describedby="imageaduser-error" />
                            <label for="imageaduser"
                                class="upload-image-placeholder-area text-center d-block p-3 p-md-4 rounded">
                                <img src="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>assets/images/upload-place.png"
                                    alt="Upload icon" class="img-fluid mb-2" style="max-height: 50px;" />
                                <p class="poppins-medium mb-0">
                                    Drag and drop an image, or
                                    <span class="color-pink text-decoration-underline">Browse</span>
                                </p>
                                <small class="form-text text-muted d-block mt-1">Max 5MB. JPG, PNG, GIF, WEBP.</small>
                            </label>
                            <?php echo displayError('picture', $errors); ?>
                        </div>

                        <?php /* Preview Area - Kept original structure */ ?>
                        <div class="col-lg-6 col-sm-12 d-flex flex-column mb-30 file-upload-main">
                            <label class="form-label">Preview Images</label>
                            <div id="preview-images" class="d-flex flex-wrap gap-3 p-2 border rounded"
                                style="min-height: 100px;">
                                <small class="text-muted align-self-center if-no-preview">No image selected</small>
                            </div>
                        </div>

                        <?php /* Platform - Kept original structure */ ?>
                        <div class="col-lg-2 col-sm-12 d-flex flex-column mb-30">
                            <label for="Platformaduser" class="form-label">Platform (Optional)</label>
                            <input type="text" name="adplatformuser" id="Platformaduser" class="form-control"
                                value="<?php echo oldValue('platform', $form_data); ?>" placeholder="e.g., Facebook" />
                        </div>

                        <?php /* Link - Kept original structure */ ?>
                        <div class="col-lg-10 col-sm-12 d-flex flex-column mb-30">
                            <label for="PlatformLinkaduser" class="form-label">Link (Optional)</label>
                            <input type="url" name="adlinkuser" id="PlatformLinkaduser"
                                class="form-control <?php echo isset($errors['link']) ? 'is-invalid' : ''; ?>"
                                value="<?php echo oldValue('link', $form_data); ?>" placeholder="https://..."
                                aria-describedby="PlatformLinkaduser-error" />
                            <?php echo displayError('link', $errors); ?>
                        </div>

                        <?php /* Add More - Kept original structure, though functionality might need more */ ?>
                        <div class="col-lg-12 col-sm-12 d-flex flex-column mb-50">
                            <a href="#" id="addMoreLinks" class="color-pink poppins-medium text-decoration-none"
                                style="display:none;">Add More Links</a> <?php /* Hide if not implemented */ ?>
                        </div>

                        <?php /* Submit Button - Kept original structure */ ?>
                        <div class="col-lg-12 col-sm-12 mb-30 text-center"> <?php /* Centered submit button */ ?>
                            <button type="submit" name="btn_save" id="btn_save"
                                class="theme-btn btn btn-primary btn-lg px-5">Post This Ad</button>
                        </div>
                    </div> <?php /* End .row for form fields */ ?>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Success Popup (if using GET param method) -->
<?php if (isset($_GET['status']) && $_GET['status'] == 'success' && !empty($_SESSION['form_success_message_display'])): ?>
    <div id="temporarySuccessPopup"
        style="display: block; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background: #28a745; color: white; padding: 15px 30px; border-radius: 5px; z-index: 1050; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <?php echo htmlspecialchars($_SESSION['form_success_message_display'], ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <script>
        setTimeout(function () {
            const popup = document.getElementById('temporarySuccessPopup');
            if (popup) {
                popup.style.display = 'none';
                if (window.history.replaceState) { // Clean URL
                    window.history.replaceState(null, null, window.location.pathname);
                }
            }
        }, 3000);
    </script>
    <?php unset($_SESSION['form_success_message_display']); // Crucial: unset after rendering script ?>
<?php elseif (isset($_SESSION['form_success_message'])): // Fallback if JS is disabled or for other popups
        // This is the PHP session success message handling logic that was originally for the non-reloading popup.
        // Kept for reference or if you decide to use it.
        $_SESSION['form_success_message_display'] = $_SESSION['form_success_message']; // Transfer to display var
        unset($_SESSION['form_success_message']); // Clear the original
    ?>
<?php endif; ?>


<?php include_once('partials/footer.php'); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // All JavaScript (Subcategory loading, Image Preview, Char Count, Focus on Error)
    // from the PREVIOUS comprehensive response should be placed here.
    // It was already designed to work with the field IDs.
    // Make sure the DOMContentLoaded wrapper is used.

    document.addEventListener('DOMContentLoaded', function () {

        // --- Dynamic Subcategory Loading ---
        const categorySelect = document.getElementById('categoryOfads');
        const subcategorySelect = document.getElementById('subcategoryOfads');
        const otherCategoryWrapper = document.getElementById('otherCategoryWrapperOriginal'); // Use ID from your original HTML
        const otherCategoryInput = document.getElementById('otherCategory');

        if (categorySelect && subcategorySelect) {
            categorySelect.addEventListener('change', function () {
                const categoryId = this.value;
                subcategorySelect.innerHTML = '<option value="">Loading...</option>';
                if (otherCategoryWrapper) otherCategoryWrapper.style.display = 'none';
                if (otherCategoryInput) otherCategoryInput.value = '';

                if (categoryId) {
                    fetch('get_subcategories.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'category_id=' + encodeURIComponent(categoryId)
                    })
                        .then(response => response.text())
                        .then(data => {
                            subcategorySelect.innerHTML = data;
                            // Check if the auto-selected subcategory is 'other'
                            if (subcategorySelect.options[subcategorySelect.selectedIndex] &&
                                subcategorySelect.options[subcategorySelect.selectedIndex].dataset.isOther === 'true') {
                                if (otherCategoryWrapper) otherCategoryWrapper.style.display = 'block'; // Or 'flex' if it's a flex item
                                if (otherCategoryInput) otherCategoryInput.focus();
                            }
                        })
                        .catch(error => { console.error('Error fetching subcategories:', error); subcategorySelect.innerHTML = '<option value="">Error</option>'; });
                } else {
                    subcategorySelect.innerHTML = '<option value="">Select Category First</option>';
                }
            });

            // Trigger change for pre-selected category on page load (e.g., after validation error)
            if (categorySelect.value && subcategorySelect.querySelectorAll('option').length <= 1) { // if only default option
                // Ensure it doesn't run if PHP already populated subcategories for the selected category
                setTimeout(() => { // Small delay to let PHP potentially populate first
                    if (subcategorySelect.querySelectorAll('option').length <= 1 ||
                        (subcategorySelect.value === "" && categorySelect.value !== "")) {
                        categorySelect.dispatchEvent(new Event('change'));
                    } else if (subcategorySelect.options[subcategorySelect.selectedIndex] &&
                        subcategorySelect.options[subcategorySelect.selectedIndex].dataset.isOther === 'true') {
                        // If PHP pre-selected an "Other" subcategory, show the input field
                        if (otherCategoryWrapper) otherCategoryWrapper.style.display = 'block';
                    }
                }, 100);
            } else if (categorySelect.value && subcategorySelect.value) {
                // If both category and subcategory are pre-selected by PHP (due to sticky form)
                // and the subcategory is 'other', ensure the 'other' input field is visible.
                const selectedSubOption = subcategorySelect.options[subcategorySelect.selectedIndex];
                if (selectedSubOption && selectedSubOption.dataset.isOther === 'true') {
                    if (otherCategoryWrapper) otherCategoryWrapper.style.display = 'block';
                }
            }


            if (subcategorySelect && otherCategoryWrapper) {
                subcategorySelect.addEventListener('change', function () {
                    if (this.options[this.selectedIndex] && this.options[this.selectedIndex].dataset.isOther === 'true') {
                        otherCategoryWrapper.style.display = 'block'; // Or 'flex' if d-flex was on it
                        if (otherCategoryInput) otherCategoryInput.focus();
                    } else {
                        otherCategoryWrapper.style.display = 'none';
                        if (otherCategoryInput) otherCategoryInput.value = '';
                    }
                });
                // Initial check if an "other" subcategory is already selected by PHP
                if (subcategorySelect.options[subcategorySelect.selectedIndex] &&
                    subcategorySelect.options[subcategorySelect.selectedIndex].dataset.isOther === 'true') {
                    otherCategoryWrapper.style.display = 'block';
                }

            }
        }

        // --- Image Preview --- (Same as previous, good version)
        const imageInput = document.getElementById('imageaduser');
        const previewContainer = document.getElementById('preview-images');
        const noPreviewText = previewContainer.querySelector('.if-no-preview');

        if (imageInput && previewContainer) {
            imageInput.addEventListener('change', function (e) { /* ... same image preview logic ... */
                previewContainer.innerHTML = ''; // Clear previous previews
                if (noPreviewText) noPreviewText.style.display = 'none';
                const file = e.target.files[0];
                if (!file) { if (noPreviewText) { previewContainer.appendChild(noPreviewText); noPreviewText.style.display = 'block'; } return; }
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) { alert('Invalid file type...'); imageInput.value = ''; if (noPreviewText) { previewContainer.appendChild(noPreviewText); noPreviewText.style.display = 'block'; } return; }
                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) { alert('File is too large...'); imageInput.value = ''; if (noPreviewText) { previewContainer.appendChild(noPreviewText); noPreviewText.style.display = 'block'; } return; }
                const reader = new FileReader();
                reader.onload = function (event) {
                    const previewItem = document.createElement('div'); previewItem.classList.add('preview-item');
                    const img = document.createElement('img'); img.src = event.target.result; img.classList.add('img-fluid', 'rounded'); img.style.objectFit = 'cover'; img.style.width = '100%'; img.style.height = '100%';
                    const removeBtn = document.createElement('span'); removeBtn.innerHTML = '×'; removeBtn.classList.add('remove-preview');
                    removeBtn.onclick = () => { previewItem.remove(); imageInput.value = ''; if (noPreviewText && previewContainer.children.length === 0) { previewContainer.appendChild(noPreviewText); noPreviewText.style.display = 'block'; } };
                    previewItem.appendChild(img); previewItem.appendChild(removeBtn); previewContainer.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            });
        }

        // --- Description Character Count --- (Same as previous, good version)
        const descriptionTextarea = document.getElementById('descriptions');
        const descriptionCharCount = document.getElementById('descriptions-char-count');
        const maxChars = 2000;
        if (descriptionTextarea && descriptionCharCount) {
            descriptionTextarea.addEventListener('input', function () { /* ... same char count logic ... */
                const currentLength = this.value.length; descriptionCharCount.textContent = `${currentLength}/${maxChars}`;
                if (currentLength > maxChars) descriptionCharCount.classList.add('text-danger'); else descriptionCharCount.classList.remove('text-danger');
            });
            descriptionTextarea.dispatchEvent(new Event('input')); // Initial count
        }

        // --- Focus on First Invalid Field ---
        <?php if (!empty($errors)): ?>
            const firstErrorField = document.querySelector('.is-invalid');
            if (firstErrorField) {
                firstErrorField.focus();
                // firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' }); // Optional scroll
            }
        <?php endif; ?>

    });
</script>
</body>

</html>