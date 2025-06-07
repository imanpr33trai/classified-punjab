<?php
// login.php - This file handles user login. PHP is the server-side language processing this script.
// It's crucial that this file has a .php extension to be processed by the PHP interpreter on the server.

// Start session if not already started. Essential for CSRF tokens and user login state.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/config.php'; // Contains $conn (database connection) and $base_url

$errors = []; // Array to hold validation errors
$form_data = [ // Array to hold form data for sticky fields
    'email' => '',
];

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors['csrf'] = 'Invalid request. Please try submitting the form again.';
    } else {
        // 2. Sanitize and get form values
        $form_data['email'] = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? ''; // Don't trim password initially
        $accepted_terms = isset($_POST['accept-login']);

        // 3. Basic Validations
        if (empty($form_data['email'])) {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format provided.';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        }

        // Note: Requiring "accept terms" on login is unusual.
        // Terms are typically agreed upon at signup.
        // If kept, it implies re-acceptance on every login.
        if (!$accepted_terms) {
            $errors['accept-login'] = 'You must agree to the Terms and Policy to log in.';
        }

        // 4. If no basic validation errors, attempt login
        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM users WHERE email = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $form_data['email']);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user['password'])) {
                        // Password is correct, regenerate session ID for security
                        session_regenerate_id(true);

                        // Store user information in session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8');
                        $_SESSION['user_email'] = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');
                        $_SESSION['user_image'] = isset($user['image']) ? htmlspecialchars($user['image'], ENT_QUOTES, 'UTF-8') : null; // Handle if image is not set

                        // Regenerate CSRF token after successful login
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                        // Redirect to a protected page (e.g., dashboard or index)
                        header("Location: index.php"); // Or user-dashboard.php
                        exit;
                    } else {
                        $errors['password'] = "Incorrect password. Please try again.";
                    }
                } else {
                    $errors['email'] = "Email not found. Please check your email or sign up.";
                }
                $stmt->close();
            } else {
                // Database query preparation failed
                error_log("Login page: Failed to prepare statement - " . $conn->error);
                $errors['db_error'] = "An unexpected error occurred. Please try again later.";
            }
        }
    }
}

// Helper functions (can be moved to a separate utility file if used across multiple pages)
function oldValue(string $field_name, array $form_data_array, string $default = ''): string
{
    // Prioritize $form_data, then $_POST, then default. Ensures sticky values.
    return htmlspecialchars($form_data_array[$field_name] ?? $_POST[$field_name] ?? $default, ENT_QUOTES, 'UTF-8');
}

function displayError(string $field_name, array $errors_array): string
{
    if (isset($errors_array[$field_name])) {
        return '<div class="invalid-feedback d-block" id="' . $field_name . '-error">' . htmlspecialchars($errors_array[$field_name]) . '</div>';
    }
    return '';
}

?>
<?php include_once('partials/header.php'); // Ensure header includes Bootstrap CSS, viewport meta tag, AND Bootstrap Icons CSS ?>

<!-- Add this to your main CSS file or in a <style> tag in the <head> -->
<style>
    /* Responsive heading font size (same as signup) */
    .responsive-h1.fos-32 {
        font-size: 32px;
    }

    @media (max-width: 991.98px) {
        .responsive-h1.fos-32 {
            font-size: 28px;
        }

        .account-main .inner-section {
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .account-main .login-sec-2 {
            padding-top: 20px;
        }
    }

    @media (max-width: 767.98px) {
        .responsive-h1.fos-32 {
            font-size: 24px;
        }

        .account-main .inner-section .col-lg-7 img {
            max-height: 300px;
            object-fit: cover;
        }
    }

    @media (max-width: 575.98px) {
        .responsive-h1.fos-32 {
            font-size: 22px;
        }

        .account-main .login-sec-2 {
            padding-left: 15px;
            padding-right: 15px;
        }
    }

    /* Custom divider style if not defined elsewhere */
    .divider {
        flex-grow: 1;
        height: 1px;
        background-color: #dee2e6;
        /* Bootstrap's default border color */
    }

    /* Ensure theme-btn has some base styling if it's custom */
    .theme-btn {
        /* Example: Add padding, border-radius, color as per your theme */
        /* padding: 0.5rem 1rem; */
        /* border-radius: 0.25rem; */
    }
</style>

<section class="account-main">
    <div class="container">
        <div class="row justify-content-center">
            <div class="inner-section d-flex flex-lg-row flex-column col-12">
                <div class="col-lg-7 col-md-12">
                    <img src="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>assets/images/login-2.jpg"
                        alt="Login Visual" class="w-100 h-100" style="object-fit: cover;" />
                </div>
                <div class="col-lg-5 col-md-12 login-sec-2 p-4 p-lg-5">
                    <div class="text-center">
                        <h1 class="fos-32 poppins-medium responsive-h1">Sign In</h1>
                        <h6 class="fos-16">Welcome back, enter your details below.</h6>
                    </div>

                    <?php
                    // Display general errors (like CSRF or DB error)
                    $general_errors = array_filter($errors, fn($key) => in_array($key, ['csrf', 'db_error']), ARRAY_FILTER_USE_KEY);
                    if (!empty($general_errors)):
                        ?>
                        <div class="alert alert-danger mt-3" role="alert">
                            <?php foreach ($general_errors as $err_key => $err_msg)
                                echo "<p class='mb-0'>" . htmlspecialchars($err_msg, ENT_QUOTES, 'UTF-8') . "</p>"; ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST"
                        class="text-start mt-4">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <div class="mb-3">
                            <label for="emailid" class="form-label">Email address</label>
                            <input type="email" name="email" id="emailid"
                                class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                                placeholder="Enter Your Registered Email"
                                value="<?php echo oldValue('email', $form_data); ?>" required
                                aria-describedby="emailid-error" />
                            <?php echo displayError('email', $errors); ?>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="passwordid" class="form-label">Password</label>

                            </div>
                            <div class="input-group">
                                <input type="password" name="password" id="passwordid"
                                    class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                                    placeholder="Enter Your Password" required aria-describedby="passwordid-error" />
                                <button class="btn btn-outline-secondary btn-toggle-password" type="button"
                                    id="togglePasswordBtn" aria-label="Show password">
                                    <i class="bi bi-eye-slash" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <?php echo displayError('password', $errors); ?>
                        </div>

                        <div class="mb-3 text-end">
                            <a href="forgot-password.php" class="poppins-medium color-pink text-decoration-none">Forgot
                                Password?</a>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="accept-login" id="accept-login"
                                class="form-check-input <?php echo isset($errors['accept-login']) ? 'is-invalid' : ''; ?>"
                                <?php echo (isset($_POST['accept-login'])) ? 'checked' : ''; ?>
                                aria-describedby="accept-login-error" />
                            <label class="form-check-label" for="accept-login">By logging in, I agree to the <a
                                    href="/terms-of-use.php" target="_blank" rel="noopener noreferrer">Terms of Use</a>
                                and <a href="/privacy-policy.php" target="_blank" rel="noopener noreferrer">Privacy
                                    Policy</a></label>
                            <?php echo displayError('accept-login', $errors); ?>
                        </div>

                        <div class="d-grid gap-2 mb-4">
                            <button type="submit" class="w-100 theme-btn btn btn-primary btn-lg text-center">Log
                                in</button>
                        </div>

                        <div class="mb-4 d-flex align-items-center gap-2">
                            <div class="divider"></div>
                            <h6 class="m-0 text-muted">OR</h6>
                            <div class="divider"></div>
                        </div>

                        <div class="mb-4 d-grid gap-2">
                            <a href="#" class="w-100 theme-btn btn btn-outline-dark text-decoration-none text-center">

                                <i class="bi bi-google me-2"></i>Continue with Google
                            </a>
                        </div>



                        <div class="text-center mt-4">
                            <p class="fos-16 mb-0">Don’t have an account?
                                <a href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>register.php"
                                    class="color-pink text-decoration-none fw-bold">Sign up</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once('partials/footer.php'); ?>

<!-- JavaScript for Password Toggle and Focus on First Error -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Password Toggle Functionality ---
        function setupPasswordToggle(inputId, buttonId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleButton = document.getElementById(buttonId);
            const toggleIcon = document.getElementById(iconId);

            if (passwordInput && toggleButton && toggleIcon) {
                toggleButton.addEventListener('click', function () {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        toggleIcon.classList.remove('bi-eye-slash');
                        toggleIcon.classList.add('bi-eye');
                        this.setAttribute('aria-label', 'Hide password');
                    } else {
                        passwordInput.type = 'password';
                        toggleIcon.classList.remove('bi-eye');
                        toggleIcon.classList.add('bi-eye-slash');
                        this.setAttribute('aria-label', 'Show password');
                    }
                });
            }
        }
        setupPasswordToggle('passwordid', 'togglePasswordBtn', 'togglePasswordIcon');

        // --- Focus on First Invalid Field ---
        <?php if (!empty($errors)): ?>
            const firstErrorField = document.querySelector('.is-invalid');
            if (firstErrorField) {
                firstErrorField.focus();
                // Optional: Scroll to the error field if it's off-screen
                // firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        <?php endif; ?>
    });
</script>

</body>

</html>