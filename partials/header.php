<?php
include_once(__DIR__ . '/../config/config.php');
session_start(); // Ensure session is started
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <!-- <link rel="stylesheet" href="/assets/css/style.css"> -->


    <title>Punjab Classified</title>
</head>

<body>

    <header>


        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="<?= $base_url ?>"><img
                        src="<?php echo $base_url; ?>assets/images/pnb-logo-full.svg" alt="" /></a>

                <div class="d-none gap-4 d-md-flex align-items-center justify-content-end w-100">
                    <!-- <button type="button" class="theme-btn">Articles</button> -->
                    <a href="<?= ARTICLES_URL ?>" class="theme-btn text-decoration-none">Articles</a>
                    <div class="head-search-area d-flex align-items-center ">
                        <select name="cats" id="cats" class="head-drop">
                            <option value="all">All Categories</option>
                            <?php
                            include_once('config/config.php'); // Adjust path if needed
                            
                            $result = $conn->query("SELECT id, name FROM ad_categories WHERE status = 'live' ORDER BY name ASC");
                            while ($row = $result->fetch_assoc()):
                                ?>
                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                            <?php endwhile; ?>
                        </select>

                        <span class="line-head mx-2">|</span>
                        <div class="head-input">
                            <img src="<?php echo $base_url; ?>assets/images/search-icon.svg" alt="">
                            <input type="text" placeholder="Search">
                            <img src="<?php echo $base_url; ?>assets/images/microphone.svg" alt="">
                        </div>
                        <!-- Search Results Container -->
                        <div id="search-results" class="search-results-box d-none">
                            <!-- Results will be loaded here dynamically -->
                        </div>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- User is logged in -->
                        <a href="<?php echo $base_url; ?>logout.php" class="ms-3 text-white text-decoration-none"><img
                                src="<?php echo $base_url; ?>assets/images/user.svg" alt="" class="me-2">Logout</a>
                    <?php else: ?>
                        <!-- Not logged in -->
                        <a href="<?php echo $base_url; ?>login.php" class="text-white text-decoration-none">
                            <img src="<?php echo $base_url; ?>assets/images/user.svg" alt="" class="me-2">Login
                        </a>
                    <?php endif; ?>

                    <!-- <button type="button" class="theme-btn">+ Post Ad</button> -->
                    <a href="<?= POST_AD_URL ?>" class="theme-btn text-decoration-none">+ Post Ad</a>
                </div>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText"
                    aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                    <span><img src="<?php echo $base_url; ?>assets/images/humbergar.svg" alt=""></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarText">
                    <ul class="navbar-nav d-flex d-md-none ">
                        <li class="nav-item">
                            <a class="nav-link text-5xl " href="#">Articles</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Post Ads</a>
                        </li>
                        <li class="nav-item">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a class="nav-link text-3xl" href="<?php echo $base_url; ?>logout.php">Logout</a>
                            <?php else: ?>
                                <a class="nav-link" href="<?php echo $base_url; ?>login.php">Login</a>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>