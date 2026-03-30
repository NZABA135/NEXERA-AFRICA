<?php 
// index.php - Located in D:\XAMPP\htdocs\nexera_africa\

// 1. Include the header which handles session and language loading
include 'includes/header.php'; 
?>

<div style="padding: 40px 20px; text-align: center; background-color: #ffffff;">
    <h1 style="color: #222; font-size: 2.5rem; margin-bottom: 20px;">
        <?php echo $lang['welcome_nexera'] ?? 'Welcome to NEXERA AFRICA'; ?>
    </h1>

    <p style="max-width: 800px; margin: 0 auto 30px auto; line-height: 1.8; font-size: 1.1rem; color: #555;">
        <?php 
            echo $lang['infrastructure_desc'] ?? 'NEXERA is a Pi-powered digital infrastructure platform for property, e-commerce, and services, with transaction-based communication.'; 
        ?>
    </p>
    
    <div style="margin-top: 30px; padding: 30px; border: 3px solid #ff9800; display: inline-block; border-radius: 15px; background-color: #fffaf0;">
        <h2 style="margin: 0 0 10px 0; color: #e65100; font-size: 1.8rem;">
            <?php echo $lang['gcv_accepted'] ?? 'GCV Accepted'; ?>
        </h2>
        <p style="font-size: 1.3rem; color: #2e7d32; font-weight: bold; margin: 0;">
            <?php echo $lang['gcv_value'] ?? 'We accept GCV (314,159 USD) as PI price.'; ?>
        </p>
    </div>

    <div style="margin-top: 40px;">
        <a href="marketplace.php" style="background: #ff9800; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">
            <?php echo $lang['marketplace'] ?? 'Marketplace'; ?>
        </a>
    </div>
</div>

<?php 
// Include the footer to close the <main> and <body> tags
include 'includes/footer.php'; 
?>