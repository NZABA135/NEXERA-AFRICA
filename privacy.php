<?php 
// privacy.php - Located in D:\XAMPP\htdocs\nexera_africa\

// 1. Include the header for session management and language loading
include('includes/header.php');
?>

<div style="padding: 40px 20px; max-width: 900px; margin: 0 auto; background-color: #ffffff; font-family: sans-serif; line-height: 1.6; color: #333;">
    
    <h1 style="color: #222; border-bottom: 2px solid #ff9800; padding-bottom: 10px;">
        <?php echo $lang['privacy_policy_title'] ?? 'Privacy Policy'; ?>
    </h1>

    <p style="font-style: italic; color: #666;">
        Last Updated: April 10, 2026
    </p>

    <section style="margin-top: 30px;">
        <h2 style="color: #e65100;"><?php echo $lang['pp_intro_title'] ?? '1. Introduction'; ?></h2>
        <p>
            Welcome to <strong>NEXERA AFRICA</strong>. This Privacy Policy explains how we collect, use, and protect your information as a Pi-powered digital infrastructure platform for property, e-commerce, and services.
        </p>
    </section>

    <section style="margin-top: 20px;">
        <h2 style="color: #e65100;"><?php echo $lang['pp_data_title'] ?? '2. Data We Collect'; ?></h2>
        <ul style="padding-left: 20px;">
            <li><strong>Account Data:</strong> Information stored via sessions to manage your language preferences and login status.</li>
            <li><strong>Transaction Data:</strong> Details regarding Pi Network transactions and marketplace activity.</li>
            <li><strong>Communication Data:</strong> Logs of transaction-based communication within the platform.</li>
        </ul>
    </section>

    <section style="margin-top: 20px;">
        <h2 style="color: #e65100;"><?php echo $lang['pp_pi_title'] ?? '3. Pi Network & GCV Compliance'; ?></h2>
        <p>
            NEXERA AFRICA facilitates payments using the Pi cryptocurrency. 
            <strong>Note:</strong> We accept GCV (314,159 USD) as the standard PI price within our ecosystem. 
            We do not store your private keys; all blockchain interactions are handled through the Pi SDK.
        </p>
    </section>

    <section style="margin-top: 20px;">
        <h2 style="color: #e65100;"><?php echo $lang['pp_usage_title'] ?? '4. How We Use Information'; ?></h2>
        <p>Your data is used solely to:</p>
        <ul style="padding-left: 20px;">
            <li>Provide digital infrastructure services for property and e-commerce.</li>
            <li>Verify transactions and secure your user experience.</li>
            <li>Improve our language localization features.</li>
        </ul>
    </section>

    <section style="margin-top: 20px;">
        <h2 style="color: #e65100;"><?php echo $lang['pp_security_title'] ?? '5. Security'; ?></h2>
        <p>
            We implement encryption and secure session handling to protect your data. However, as no platform is 100% secure, we encourage users to protect their account credentials.
        </p>
    </section>

    <div style="margin-top: 40px; padding: 20px; background-color: #fffaf0; border-left: 5px solid #ff9800;">
        <p style="margin: 0;">
            <strong><?php echo $lang['questions_contact'] ?? 'Questions?'; ?></strong> 
            If you have concerns regarding your data, please contact the NEXERA AFRICA administration.
        </p>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <a href="index.php" style="color: #ff9800; text-decoration: none; font-weight: bold;">
            &larr; <?php echo $lang['back_home'] ?? 'Back to Home'; ?>
        </a>
    </div>

</div>

<?php 
// Include the footer to close the HTML tags
include(__DIR__ . '/includes/footer.php'); 
?>