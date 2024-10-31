<?php
/* Template Name: Maintenance Mode */

include 'header-maintenance.php';
global $wpdb;

// Define the table name with the WordPress table prefix
$wpnts_maintenance_query = $wpdb->prefix . 'wpnts_maintenance_query';
?>

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
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
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
    }

    input[type="submit"] {
        background-color: #4caf50;
        color: white;
        padding: 10px;
        border: none;
        cursor: pointer;
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
    <h1>Site is Under Maintenance</h1>
    <p>We are currently performing maintenance on our website. Please check back shortly.</p>

    <!-- Optional Subtitle -->
    <!-- <h4>Have urgent Query? Submit the form</h4> -->

    <form id="maintenance-query-form" method="post">
        <?php wp_nonce_field('maintenance_query_nonce', 'maintenance_query_nonce_field'); ?>
        <label for="user_email">Your Email:</label>
        <input type="email" name="user_email" id="user_email" required>

        <label for="user_message">Your Message:</label>
        <textarea name="user_message" id="user_message" rows="4" required></textarea>

        <input type="submit" name="submit_query" value="Submit Query">
    </form>

    <!-- Container for Success and Error Messages -->
    <div id="form-messages"></div>
</div>

<script>
// JavaScript for handling AJAX form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('maintenance-query-form');
    const messagesContainer = document.getElementById('form-messages');

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Clear previous messages
        messagesContainer.innerHTML = '';

        // Prepare form data
        const formData = new FormData(form);
        formData.append('action', 'submit_maintenance_query'); // AJAX action

        // Send AJAX request
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Display success message
                messagesContainer.innerHTML = `<div class="message success">${data.data}</div>`;
                // Optionally, reset the form
                form.reset();
            } else {
                // Display error message
                messagesContainer.innerHTML = `<div class="message error">${data.data}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messagesContainer.innerHTML = `<div class="message error">An unexpected error occurred. Please try again later.</div>`;
        });
    });
});
</script>

<?php
// Include the WordPress footer
wp_footer();
?>
