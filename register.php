<?php
// Start session for CSRF token and flash messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/config.php'; // Should define $conn and $base_url

// Define constants
define('MIN_PASSWORD_LENGTH', 8);
// Regex for validating names (letters, spaces, hyphens, apostrophes)
define('NAME_VALIDATION_REGEX', "/^[a-zA-Z\s'-]+$/");


$errors = [];
$success = '';
$form_data = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
];

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = 'Invalid CSRF token. Please try again.'; // General error
    } else {
        // 2. Sanitize and get form values
        // Trim and collapse multiple spaces for names
        $form_data['first_name'] = trim(preg_replace('/\s+/', ' ', $_POST['first_name'] ?? ''));
        $form_data['last_name'] = trim(preg_replace('/\s+/', ' ', $_POST['last_name'] ?? ''));
        $form_data['email'] = trim($_POST['email'] ?? '');
        $form_data['phone'] = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $agree_terms = isset($_POST['agree_terms']);

        // 3. Server-side Validations

        // First Name
        if (empty($form_data['first_name'])) {
            $errors['first_name'] = 'First name is required.';
        } elseif (!preg_match(NAME_VALIDATION_REGEX, $form_data['first_name'])) {
            $errors['first_name'] = 'First name can only contain letters, spaces, hyphens, and apostrophes.';
        } elseif (strlen($form_data['first_name']) > 50) {
            $errors['first_name'] = 'First name cannot exceed 50 characters.';
        }

        // Last Name
        if (empty($form_data['last_name'])) {
            $errors['last_name'] = 'Last name is required.';
        } elseif (!preg_match(NAME_VALIDATION_REGEX, $form_data['last_name'])) {
            $errors['last_name'] = 'Last name can only contain letters, spaces, hyphens, and apostrophes.';
        } elseif (strlen($form_data['last_name']) > 50) {
            $errors['last_name'] = 'Last name cannot exceed 50 characters.';
        }


        if (empty($form_data['email'])) {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format provided.';
        } elseif (strlen($form_data['email']) > 100) {
            $errors['email'] = 'Email cannot exceed 100 characters.';
        } else {
            $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check_email->bind_param("s", $form_data['email']);
            $stmt_check_email->execute();
            $stmt_check_email->store_result();
            if ($stmt_check_email->num_rows > 0) {
                $errors['email'] = 'This email address is already registered. Please use a different email, or login';
            }
            $stmt_check_email->close();
        }

        if (!empty($form_data['phone'])) {
            $sanitized_phone = preg_replace('/\D/', '', $form_data['phone']);
            if (!preg_match('/^[0-9]{10,15}$/', $sanitized_phone)) {
                $errors['phone'] = 'Phone number must be 10 to 15 digits (after removing any symbols).';
            } else {
                $form_data['phone'] = $sanitized_phone;
            }
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < MIN_PASSWORD_LENGTH) {
            $errors['password'] = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters long.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Password needs at least one uppercase letter.';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors['password'] = 'Password needs at least one lowercase letter.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Password needs at least one number.';
        } elseif (!preg_match('/[\W_]/', $password)) { // \W is non-word, _ is underscore
            $errors['password'] = 'Password needs at least one special character (e.g., !@#$%^&*).';
        }

        if (empty($password_confirm)) {
            $errors['password_confirm'] = 'Please confirm your password.';
        } elseif ($password !== $password_confirm) {
            $errors['password_confirm'] = 'Passwords do not match. Please re-enter.';
        }

        if (!$agree_terms) {
            $errors['agree_terms'] = 'You must agree to the Terms of Use and Privacy Policy to sign up.';
        }

        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
            $phone_to_insert = !empty($form_data['phone']) ? $form_data['phone'] : null;
            $stmt->bind_param("sssss", $form_data['first_name'], $form_data['last_name'], $form_data['email'], $phone_to_insert, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Account created successfully! You can now log in.";
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header("Location: login.php");
                exit();
            } else {
                if ($conn->errno == 1062) {
                    $errors[] = "An account with this email address already exists."; // General error
                } else {
                    error_log("DB Error on signup: " . $stmt->error); // Log detailed error for admin
                    $errors[] = "We encountered an issue creating your account. Please try again later."; // General error
                }
            }
            $stmt->close();
        }
    }
}

function oldValue(string $field_name, array $form_data_array, string $default = ''): string
{
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
<?php
include_once('partials/header.php');
?>
<style>
    /* ... (previous responsive styles remain the same) ... */
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

        .row.mb-3>div[class*="col-md-6"]:first-child {
            margin-bottom: 1rem;
        }

        .account-main .login-sec-2 {
            padding-left: 15px;
            padding-right: 15px;
        }
    }

    /* Basic Password Strength Indicator Styles */
    #passwordStrength {
        font-size: 0.875em;
        margin-top: 0.25rem;
    }

    .strength-weak {
        color: #dc3545;
        /* Bootstrap danger color */
    }

    .strength-medium {
        color: #ffc107;
        /* Bootstrap warning color */
    }

    .strength-strong {
        color: #198754;
        /* Bootstrap success color */
    }

    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear,
    input[type="password"]::-webkit-contacts-auto-fill-button,
    input[type="password"]::-webkit-credentials-auto-fill-button,
    input[type="password"]::-webkit-clear-button,
    input[type="password"]::-webkit-password-toggle-button {
        display: none !important;
    }
</style>

<section class="account-main">
    <div class="container">
        <div class="row justify-content-center">
            <div class="inner-section d-flex flex-lg-row flex-column col-12">
                <div class="col-lg-7 col-md-12">
                    <img src="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>assets/images/login-2.jpg"
                        alt="Sign Up Visual" class="w-100 h-100" style="object-fit: cover;" />
                </div>
                <div class="col-lg-5 col-md-12 login-sec-2 p-4 p-lg-5">
                    <h1 class="fos-32 poppins-medium responsive-h1">Sign up now</h1>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    // Display general errors (not field-specific, or if you prefer a general list)
                    $general_errors = array_filter($errors, function ($key) {
                        return is_numeric($key); // Filter out associative keys which are field-specific
                    }, ARRAY_FILTER_USE_KEY);

                    if (!empty($general_errors)):
                        ?>
                        <div class="alert alert-danger" role="alert">
                            <?php foreach ($general_errors as $err)
                                echo "<p class='mb-0'>" . htmlspecialchars($err, ENT_QUOTES, 'UTF-8') . "</p>"; ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST"
                        class="text-start mt-4" id="registerForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="firstnameid" class="form-label">First name*</label>
                                <input type="text" name="first_name"
                                    class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>"
                                    id="firstnameid" placeholder="Your First Name"
                                    value="<?php echo oldValue('first_name', $form_data); ?>" required maxlength="50"
                                    pattern="[a-zA-Z\s'-]+" title="Only letters, spaces, hyphens, apostrophes."
                                    aria-describedby="firstnameid-error" />
                                <?php echo displayError('first_name', $errors); ?>
                            </div>
                            <div class="col-md-6">
                                <label for="lastnameid" class="form-label">Last name*</label>
                                <input type="text" name="last_name"
                                    class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>"
                                    id="lastnameid" placeholder="Your Last Name"
                                    value="<?php echo oldValue('last_name', $form_data); ?>" required maxlength="50"
                                    pattern="[a-zA-Z\s'-]+" title="Only letters, spaces, hyphens, apostrophes."
                                    aria-describedby="lastnameid-error" />
                                <?php echo displayError('last_name', $errors); ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="emailid" class="form-label">Email address*</label>
                            <input type="email" name="email"
                                class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                                id="emailid" placeholder="your.email@example.com"
                                value="<?php echo oldValue('email', $form_data); ?>" required maxlength="100"
                                aria-describedby="emailid-error" />
                            <?php echo displayError('email', $errors); ?>
                        </div>
                        <div class="mb-3">
                            <label for="phonenumbid" class="form-label">Phone Number (Optional)</label>
                            <input type="tel" name="phone"
                                class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>"
                                id="phonenumbid" placeholder="e.g., +1 (555) 123-4567"
                                value="<?php echo oldValue('phone', $form_data); ?>"
                                aria-describedby="phonenumbid-error" />
                            <?php echo displayError('phone', $errors); ?>
                        </div>

                        <div class="mb-3">
                            <label for="passwordid" class="form-label">Password*</label>
                            <div class="input-group">
                                <input type="password" name="password" id="passwordid"
                                    class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                                    placeholder="Create a Strong Password" required
                                    aria-describedby="passwordHelpBlock passwordStrength passwordid-error" />
                                <button class="btn btn-outline-secondary btn-toggle-password" type="button"
                                    id="togglePasswordBtn" aria-label="Show password">
                                    <i class="bi bi-eye-slash" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <div id="passwordHelpBlock" class="form-text">
                                <?php echo MIN_PASSWORD_LENGTH; ?>+ characters, with letters, numbers & symbols.
                            </div>
                            <div id="passwordStrength"></div>
                            <?php echo displayError('password', $errors); ?>
                        </div>

                        <div class="mb-3">
                            <label for="passwordconfirmid" class="form-label">Confirm Password*</label>
                            <div class="input-group">
                                <input type="password" name="password_confirm" id="passwordconfirmid"
                                    class="form-control <?php echo isset($errors['password_confirm']) ? 'is-invalid' : ''; ?>"
                                    placeholder="Re-enter Your Password" required
                                    aria-describedby="passwordconfirmid-error" />
                                <button class="btn btn-outline-secondary btn-toggle-password" type="button"
                                    id="togglePasswordConfirmBtn" aria-label="Show password">
                                    <i class="bi bi-eye-slash" id="togglePasswordConfirmIcon"></i>
                                </button>
                            </div>
                            <?php echo displayError('password_confirm', $errors); ?>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="agree_terms"
                                class="form-check-input <?php echo isset($errors['agree_terms']) ? 'is-invalid' : ''; ?>"
                                required id="accept-terms" <?php echo (isset($_POST['agree_terms']) || oldValue('agree_terms', $form_data) === 'on') ? 'checked' : ''; ?>
                                aria-describedby="agree_terms-error" />
                            <label class="form-check-label" for="accept-terms">I agree to <a href="/terms-of-use.php"
                                    target="_blank" rel="noopener noreferrer">Terms of use</a> and <a
                                    href="/privacy-policy.php" target="_blank" rel="noopener noreferrer">Privacy
                                    Policy</a>*</label>
                            <?php echo displayError('agree_terms', $errors); ?>
                        </div>
                        <div class="d-grid gap-2 mb-4">
                            <button type="submit"
                                class="theme-btn text-decoration-none text-center btn btn-primary btn-lg">Sign
                                Up</button>
                        </div>
                    </form>
                    <p class="text-center">Already have an account? <a href="login.php">Log In</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include_once('partials/footer.php');
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Password Toggle Functionality (from previous response) ---
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
        setupPasswordToggle('passwordconfirmid', 'togglePasswordConfirmBtn', 'togglePasswordConfirmIcon');

        // --- Basic Password Strength Indicator ---
        const passwordField = document.getElementById('passwordid');
        const strengthIndicator = document.getElementById('passwordStrength');

        if (passwordField && strengthIndicator) {
            passwordField.addEventListener('input', function () {
                const password = this.value;
                let strength = 0;
                let feedbackText = '';
                let feedbackClass = '';

                if (password.length >= <?php echo MIN_PASSWORD_LENGTH; ?>) strength++;
                if (password.match(/[a-z]/)) strength++;
                if (password.match(/[A-Z]/)) strength++;
                if (password.match(/[0-9]/)) strength++;
                if (password.match(/[\W_]/)) strength++; // Special character
                if (password.length >= 15) strength++; // Bonus for longer passwords

                switch (strength) {
                    case 0:
                    case 1:
                    case 2:
                        feedbackText = 'Weak';
                        feedbackClass = 'strength-weak';
                        break;
                    case 3:
                    case 4:
                        feedbackText = 'Medium';
                        feedbackClass = 'strength-medium';
                        break;
                    case 5:
                        feedbackText = 'Strong';
                        feedbackClass = 'strength-strong';
                        break;
                    // case :
                    //     feedbackText = 'Strong';
                    //     feedbackClass = 'strength-strong';

                    default:
                        feedbackText = '';
                        feedbackClass = '';
                }
                strengthIndicator.textContent = password.length > 0 ? `Strength: ${feedbackText}` : '';
                strengthIndicator.className = password.length > 0 ? feedbackClass : '';


            });
        }

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