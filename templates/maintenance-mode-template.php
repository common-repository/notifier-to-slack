<?php
/* Template Name: Maintenance Mode */

// Include necessary header
include 'header-maintenance.php';
$schedules_int = get_option('wpnts_schedules_maintenannotice_settings');
$schedules_interval = json_decode($schedules_int);

// Check the value of show_maintenance_contact_form
$show_maintenance_contact_form = $schedules_interval->show_maintenance_contact_form ?? false;
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <title><?php bloginfo('name'); ?> - Maintenance Mode</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<style>
    /* Your existing CSS styles */
    body {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
        font-family: Arial, sans-serif;
    }

    #maintenance-mode-container {
        text-align: center;
        width: 100%;
        max-width: 500px;
        padding: 20px;
        box-sizing: border-box;
    }

    h1 {
        font-size: 2em;
        color: #333;
    }

    p {
        font-size: 1.2em;
        color: #666;
    }

    form {
        margin-top: 20px;
        width: 100%;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        text-align: left;
        color: #666;
    }

    input[type="email"],
    textarea {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    input[type="submit"] {
        background-color: #4caf50;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
    }

    input[type="submit"]:hover {
        background-color: #45a049;
    }

    .message {
        margin-top: 15px;
        padding: 10px;
        border-radius: 4px;
    }

    .success {
        background-color: #d4edda;
        color: #155724;
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>

<div id="maintenance-mode-container">
    <h1><?php esc_html_e('Site is Under Maintenance', 'wpnts'); ?></h1>
    <p><?php esc_html_e('We are currently performing maintenance on our website. Please check back shortly.', 'wpnts'); ?></p>

    <?php if ($show_maintenance_contact_form): ?>
        <form id="maintenance-query-form" method="post">
            <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>">
            <input type="email" name="user_email" id="user_email" required placeholder="<?php esc_attr_e('Your Email', 'wpnts'); ?>">
            <textarea name="user_message" id="user_message" rows="4" required placeholder="<?php esc_attr_e('Your Message', 'wpnts'); ?>"></textarea>
            <input type="submit" name="submit_query" value="<?php esc_attr_e('Submit Query', 'wpnts'); ?>">
        </form>

        <div id="form-messages"></div>
    <?php endif; ?>
</div>

<?php wp_footer(); ?>
</body>
</html>
