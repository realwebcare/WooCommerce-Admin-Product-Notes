<?php
// Add a meta box for Admin Note
function realwebcare_add_admin_note_meta_box() {
    add_meta_box(
        'rwc_admin_note', // ID of the meta box
        __('Admin Note', 'porto'), // Title of the meta box
        'realwebcare_display_admin_note_meta_box', // Callback function to display the content
        'product', // Post type where this meta box will be shown
        'side', // Context ('side', 'normal', 'advanced')
        'high' // Priority
    );
}
add_action('add_meta_boxes', 'realwebcare_add_admin_note_meta_box');

// Display the content of the Admin Note meta box
function realwebcare_display_admin_note_meta_box($post) {
    // Retrieve the existing admin note
    $admin_note = get_post_meta($post->ID, '_rwc_product_note', true);

    // Display the WP Editor for admin note with adjusted textarea size
    $settings = array(
        'textarea_name' => 'rwc_product_note', // The name of the textarea
        'textarea_rows' => 10, // Number of rows (height of textarea)
        'editor_height' => 150, // Height of the editor in pixels (optional)
    );

    // Display the WP Editor for admin note
    wp_editor($admin_note, 'rwc_product_note', $settings);

    // Add nonce field for security
    wp_nonce_field('save_rwc_product_note', 'realwebcare_product_note_nonce');
}

// Save the Admin Note when the product is saved
function realwebcare_save_admin_note_meta_box($post_id) {
    // Check if the current user is an administrator
    if (!current_user_can('administrator')) {
        return; // Exit if the user is not an admin
    }

    // Verify nonce for security
    if (!isset($_POST['realwebcare_product_note_nonce']) || !wp_verify_nonce($_POST['realwebcare_product_note_nonce'], 'save_rwc_product_note')) {
        return; // Exit if nonce verification fails
    }

    // Save the admin note
    if (isset($_POST['rwc_product_note'])) {
        update_post_meta($post_id, '_rwc_product_note', wp_kses_post($_POST['rwc_product_note'])); // Update the admin note
    }
}
add_action('save_post', 'realwebcare_save_admin_note_meta_box');

// Display the Admin Note tab on the front-end for admin users only
function realwebcare_admin_only_product_tab($tabs) {
    // Check if the current user is an administrator
    if (current_user_can('administrator')) { // Only add tab if the user is an admin
        $tabs['admin_note_tab'] = array(
            'title'    => __('Admin Note', 'porto'), // Title of the tab
            'priority' => 60, // Tab priority
            'callback' => 'realwebcare_admin_note_tab_content', // Callback function to display the tab content
        );
    }
    return $tabs; // Return the modified tabs array
}
add_filter('woocommerce_product_tabs', 'realwebcare_admin_only_product_tab');

// Display the admin note content on the front-end
function realwebcare_admin_note_tab_content() {
    global $post;

    // Retrieve the existing admin note
    $admin_note = get_post_meta($post->ID, '_rwc_product_note', true);

    // Display the admin note content if available
    if (!empty($admin_note)) {
        echo wpautop($admin_note); // Output the note with automatic formatting
    } else {
        echo __('No admin note added for this product.', 'porto'); // Message if no note is found
    }
}
