<?php 
// terms.php - Located in D:\XAMPP\htdocs\nexera_africa\

// 1. Include the header for session and language management
include('includes/header.php');
?>

<div style="padding: 40px 20px; max-width: 900px; margin: 0 auto; background-color: #ffffff; font-family: sans-serif; line-height: 1.8; color: #333;">
    
    <h1 style="color: #222; border-bottom: 3px solid #ff9800; padding-bottom: 15px; font-size: 2.2rem;">
        <?php echo $lang['terms_conditions_title'] ?? 'Terms and Conditions'; ?>
    </h1>

    <p style="font-style: italic; color: #666; margin-bottom: 30px;">
        Last Updated: April 10, 2026
    </p>

    <section style="margin-bottom: 30px;">
        <h2 style="color: #e65100; font-size: 1.5rem; margin-bottom: 10px;">
            1. <?php echo $lang['acceptance_terms'] ?? 'Acceptance of Terms'; ?>
        </h2>
        <p>
            By using <strong>NEXERA AFRICA</strong>, you agree to be bound by these terms and conditions. 
            All transactions on this platform must exclusively use <strong>Pi coin</strong> as the method of payment. 
            The required exchange rate for all goods and services is the <strong>Global Consensus Value (GCV)</strong> set at <strong>1 Pi = 314,159 USD</strong>.
        </p>
    </section>

    <section style="margin-bottom: 30px;">
        <h2 style="color: #e65100; font-size: 1.5rem; margin-bottom: 10px;">
            2. <?php echo $lang['product_listing'] ?? 'Products Listing'; ?>
        </h2>
        <p>
            All products and services listed on the marketplace are provided by <strong>verified sellers</strong>. 
            Sellers are strictly required to clarify the following details for every listing:
        </p>
        <ul style="padding-left: 25px; list-style-type: square;">
            <li>Accurate GCV-based pricing.</li>
            <li>Specific quality standards and item conditions.</li>
            <li>Real-time stock and inventory availability.</li>
        </ul>
    </section>

    <section style="margin-bottom: 30px;">
        <h2 style="color: #e65100; font-size: 1.5rem; margin-bottom: 10px;">
            3. <?php echo $lang['licensing_certification'] ?? 'Licensing and Certification'; ?>
        </h2>
        <p>
            To ensure the security and integrity of the NEXERA AFRICA ecosystem, all sellers must undergo a verification and certification process. 
            Access to sell on this platform is only granted after the administration has approved the seller's credentials and business legitimacy.
        </p>
    </section>

    <section style="margin-bottom: 30px; padding: 25px; background-color: #fffaf0; border-radius: 10px; border: 1px dashed #ff9800;">
        <h2 style="color: #e65100; font-size: 1.5rem; margin-bottom: 15px;">
            4. <?php echo $lang['contact_support'] ?? 'Contact and Support'; ?>
        </h2>
        <p>Our support team is available <strong>24/7</strong> to assist you with transactions, seller verification, or technical issues.</p>
        
        <div style="margin-top: 15px;">
            <strong>Email:</strong> <a href="mailto:nexeraafrica@gmail.com" style="color: #ff9800;">nexeraafrica@gmail.com</a><br>
            <strong>WhatsApp & Call:</strong> <a href="tel:+250790088860" style="color: #ff9800;">+250 790 088 860</a>
        </div>
    </section>

    <div style="margin-top: 40px; text-align: center; border-top: 1px solid #eee; padding-top: 20px;">
        <a href="index.php" style="background: #ff9800; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
            <?php echo $lang['i_agree'] ?? 'I Agree and Continue'; ?>
        </a>
    </div>

</div>

<?php 
// Include the footer to close the HTML tags properly
include(__DIR__ . '/includes/footer.php'); 
?>