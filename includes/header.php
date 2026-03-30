<?php
// includes/header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

// 1. Language Logic - Added 'fr' to the array
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'rw', 'sw', 'fr'])) {
    $_SESSION['lang'] = $_GET['lang'];
    $clean_url = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: $clean_url");
    exit();
}

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

require_once __DIR__ . '/../languages/lang_' . $_SESSION['lang'] . '.php';

$current_page = basename($_SERVER['PHP_SELF']);
$is_vendor_dash = ($current_page === 'dashboard.php' && strpos($_SERVER['PHP_SELF'], '/vendor/') !== false);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXERA AFRICA</title>
    <link rel="stylesheet" href="/nexera_africa/assets/css/style.css">
    <style>
        /* Account Icon & Dropdown Styles */
        .account-wrapper {
            position: relative;
            display: inline-block;
        }

        .user-icon {
            display: inline-block;
            width: 35px;
            height: 35px;
            background: #ff9800;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 35px;
            font-weight: bold;
            cursor: pointer;
            vertical-align: middle;
            border: 2px solid transparent;
            transition: 0.3s;
        }

        .user-icon:hover {
            border-color: #e65100;
            background: #fb8c00;
        }

        .account-dropdown {
            display: none; 
            position: absolute;
            right: 0;
            top: 45px;
            background-color: white;
            min-width: 180px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            border-radius: 8px;
            z-index: 1001;
            overflow: hidden;
            border: 1px solid #ddd;
        }

        .account-dropdown a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-size: 14px;
        }

        .account-dropdown a:hover {
            background-color: #f1f1f1;
        }

        .dropdown-info {
            padding: 12px 16px;
            background: #f9f9f9;
            border-bottom: 1px solid #eee;
            font-size: 13px;
            color: #555;
        }

        .show { display: block !important; }

        #welcome-popup {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2e7d32;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 1000;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
    </style>
</head>
<body>
    <div id="welcome-popup">
        <?php 
            $welcome_msg = ($lang['login_success'] ?? 'Welcome back, %s! Login successful.');
            echo sprintf($welcome_msg, '<strong>' . (isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : '') . '</strong>'); 
        ?>
    </div>

    <header>
        <nav>
            <div class="logo">NEXERA AFRICA</div>
            <ul>
                <li><a href="/nexera_africa/index.php"><?php echo $lang['home']; ?></a></li>

                <?php if (!$is_vendor_dash): ?>
                    <li><a href="/nexera_africa/marketplace.php"><?php echo $lang['marketplace']; ?></a></li>
                    <li><a href="/nexera_africa/category_manage.php?name=Electronics"><?php echo $lang['electronics'] ?? 'Electronics'; ?></a></li>
                    <li><a href="/nexera_africa/category_manage.php?name=Apartments"><?php echo $lang['apartments'] ?? 'Apartments'; ?></a></li>
                    <li><a href="/nexera_africa/category_manage.php?name=Real Estates"><?php echo $lang['real_estates'] ?? 'Real Estates'; ?></a></li>
                    <li><a href="/nexera_africa/category_manage.php?name=Other Services"><?php echo $lang['other_services'] ?? 'Other Services'; ?></a></li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="account-wrapper">
                            <div class="user-icon" onclick="toggleUserMenu()">
                                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                            </div>
                            
                            <div id="userDropdown" class="account-dropdown">
                                <div class="dropdown-info">
                                    <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong><br>
                                    <small><?php echo isset($lang[$_SESSION['role']]) ? $lang[$_SESSION['role']] : ucfirst($_SESSION['role']); ?></small>
                                </div>
                                <a href="/nexera_africa/profile.php"><?php echo $lang['my_profile'] ?? 'My Profile'; ?></a>
                                
                                <?php if (get_user_role($_SESSION['user_id']) === 'admin'): ?>
                                    <a href="/nexera_africa/admin/index.php"><?php echo $lang['dashboard']; ?></a>
                                <?php elseif (get_user_role($_SESSION['user_id']) === 'vendor'): ?>
                                    <a href="/nexera_africa/vendor/dashboard.php"><?php echo $lang['dashboard']; ?></a>
                                <?php endif; ?>
                                
                                <a href="/nexera_africa/logout.php" style="color:red; border-top: 1px solid #eee;"><?php echo $lang['logout']; ?></a>
                            </div>
                        </li>

                    <?php else: ?>
                        <li><a href="/nexera_africa/login.php"><?php echo $lang['login']; ?></a></li>
                        <li><a href="/nexera_africa/register.php"><?php echo $lang['register']; ?></a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <select onchange="window.location.href = '?lang=' + this.value;">
                <option value=""><?php echo $lang['select_language']; ?></option>
                <option value="en" <?php echo $_SESSION['lang'] == 'en' ? 'selected' : ''; ?>><?php echo $lang['english']; ?></option>
                <option value="fr" <?php echo $_SESSION['lang'] == 'fr' ? 'selected' : ''; ?>><?php echo $lang['french'] ?? 'French'; ?></option>
                <option value="rw" <?php echo $_SESSION['lang'] == 'rw' ? 'selected' : ''; ?>><?php echo $lang['kinyarwanda']; ?></option>
                <option value="sw" <?php echo $_SESSION['lang'] == 'sw' ? 'selected' : ''; ?>><?php echo $lang['kiswahili']; ?></option>
            </select>
        </nav>
    </header>

    <script>
        function toggleUserMenu() {
            document.getElementById("userDropdown").classList.toggle("show");
        }

        window.onclick = function(event) {
            if (!event.target.matches('.user-icon')) {
                var dropdowns = document.getElementsByClassName("account-dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }

        <?php if (isset($_SESSION['login_success'])): ?>
            const popup = document.getElementById('welcome-popup');
            popup.style.display = 'block';
            setTimeout(() => {
                popup.style.display = 'none';
            }, 4000);
            <?php unset($_SESSION['login_success']); ?>
        <?php endif; ?>
    </script>
    <main>