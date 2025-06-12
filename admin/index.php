<?php
// admin_dashboard.php - Main admin panel page
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php"); // Assuming admin login is separate
    exit;
}
include_once('../config/config.php'); // Path to your main config
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Punjab Classified</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Custom Admin Styles -->
    <link rel="stylesheet" href="./style.css">
</head>

<body>

    <div class="admin-wrapper d-flex">
        <!-- Sidebar -->
        <aside class="sidebar bg-dark text-white p-3" id="adminSidebar">
            <div class="sidebar-header d-flex align-items-center mb-4">
                <i class="bi bi-speedometer2 fs-2 me-2"></i>
                <h3 class="fs-5 mb-0">Admin Panel</h3>
            </div>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="#" data-page="dashboard" class="nav-link text-white active tab-link">
                        <i class="bi bi-grid-1x2-fill me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" data-page="category" class="nav-link text-white tab-link">
                        <i class="bi bi-tags-fill me-2"></i> Ad Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" data-page="sub-cat" class="nav-link text-white tab-link">
                        <i class="bi bi-tag-fill me-2"></i> Ad Subcategories
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" data-page="ads" class="nav-link text-white tab-link">
                        <i class="bi bi-megaphone-fill me-2"></i> Manage Ads
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" data-page="blog-cat" class="nav-link text-white tab-link">
                        <i class="bi bi-bookmarks-fill me-2"></i> Blog Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" data-page="blog-posts" class="nav-link text-white tab-link">
                        <i class="bi bi-file-post-fill me-2"></i> Blog Posts
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" data-page="users" class="nav-link text-white tab-link">
                        <i class="bi bi-people-fill me-2"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" data-page="settings" class="nav-link text-white tab-link">
                        <i class="bi bi-gear-fill me-2"></i> Site Settings
                    </a>
                </li>
            </ul>
            <hr class="text-secondary">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                    id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <strong><?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
                </ul>
            </div>
        </aside>
        <!-- /Sidebar -->

        <!-- Main Content Area -->
        <div class="main-content flex-grow-1 p-0">
            <!-- Top Navbar for Mobile Toggle and Page Title -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom d-lg-none sticky-top">
                <div class="container-fluid">
                    <button class="btn btn-outline-secondary me-2" type="button" id="sidebarToggleMobile">
                        <i class="bi bi-list"></i>
                    </button>
                    <span class="navbar-brand mb-0 h1" id="pageTitleMobile">Dashboard</span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
                </div>
            </nav>
            <!-- /Top Navbar -->

            <!-- Content loaded by AJAX -->
            <main id="content-area" class="p-4">

                <div class="initial-load-content">
                    <h2>Welcome, Admin!</h2>
                    <p>Select an option from the sidebar to get started.</p>
                </div>
            </main>
            <!-- /Content -->

        </div>
        <!-- /Main Content Area -->
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>
    <!-- Custom Admin JS -->
    <script src="./script.js"></script>
</body>

</html>