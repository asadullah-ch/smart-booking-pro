<?php
/*
* Plugin Name: Smart
* Plugin URI: http://wordpress.org/plugins/smart
* Description: Ye plugin sirf tab chalega jab Amelia active hoga.
* Version: 1.0
* Author: WebShouters
* Author URI: https://www.mysite.com/
* Text Domain: smart
*/

// Security check
if (!defined('ABSPATH')) {
    exit; // Direct access block
}

// Plugin activation: Check if Amelia is active
function smart_plugin_activate() {
    if (!is_plugin_active('ameliabooking/ameliabooking.php')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('Firstly Amelia Plugin Active.');
    }
}
register_activation_hook(__FILE__, 'smart_plugin_activate');

// Amelia deactivate hone par Smart plugin bhi deactivate ho & warning show ho
function smart_check_amelia_deactivation($plugin, $network_deactivating) {
    if ($plugin === 'ameliabooking/ameliabooking.php') {
        deactivate_plugins(plugin_basename(__FILE__));
        set_transient('smart_plugin_deactivated_notice', true, 5);
    }
}
add_action('deactivated_plugin', 'smart_check_amelia_deactivation', 10, 2);

// Admin notice jab Smart plugin deactivate ho jaye
function smart_plugin_deactivation_notice() {
    if (get_transient('smart_plugin_deactivated_notice')) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><strong>Warning:</strong> Smart Plugin deactivate kar diya gaya kyunki Amelia plugin deactivate ho gaya hai.</p>
        </div>
        <?php
        delete_transient('smart_plugin_deactivated_notice');
    }
}
add_action('admin_notices', 'smart_plugin_deactivation_notice');

// HTML Form Function
function smart_custom_form() {
    ob_start(); ?>
    
    <form action="" method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <input type="submit" name="submit_form" value="Submit">
    </form>

    <?php
    return ob_get_clean();
}
add_shortcode('smart_form', 'smart_custom_form');

// ✅ Functionality 1: Admin Dashboard Me Custom Menu Add Karna
function smart_add_admin_menu() {
    add_menu_page(
        'Smart Plugin Settings',
        'Smart Plugin',
        'manage_options',
        'smart-plugin',
        'smart_admin_page',
        'dashicons-admin-generic',
        20
    );
}
add_action('admin_menu', 'smart_add_admin_menu');

function smart_admin_page() {
    ?>
    <div class="wrap">
        <h1>Smart Plugin Settings</h1>
        <p>Yahan tum apni plugin ki settings add kar sakte ho.</p>
    </div>
    <?php
}

// ✅ Functionality 2: Shortcode Jo User Ka Greeting Message Show Karega
function smart_greeting_shortcode() {
    $user = wp_get_current_user();
    $name = ($user->exists()) ? $user->display_name : 'Guest';
    return "<p>Welcome, <strong>$name</strong>!</p>";
}
add_shortcode('smart_greeting', 'smart_greeting_shortcode');
// 1. Dashboard Menu Add Karein
function smart_dashboard_menu() {
    add_menu_page(
        'Smart Theme Settings', 
        'Theme Color', 
        'manage_options', 
        'smart-theme-settings', 
        'smart_settings_page', 
        'dashicons-admin-customizer', 
        20 
    );
}
add_action('admin_menu', 'smart_dashboard_menu');

// 2. Settings Page Function
function smart_settings_page() {
    $color = get_option('smart_theme_color', '#3498db'); // Default color
    ?>
    <div class="wrap">
        <h1>Theme Color Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('smart_settings_group');
            do_settings_sections('smart-theme-settings');
            submit_button();
            ?>
        </form>

        <!-- Live Preview -->
        <h3>Live Preview</h3>
        <div id="preview-box" style="width:100%; height:100px; background:<?php echo esc_attr($color); ?>; color: white; display:flex; align-items:center; justify-content:center;">
            Sample Text
        </div>
    </div>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let colorPicker = document.querySelector('input[name="smart_theme_color"]');
            let previewBox = document.getElementById("preview-box");

            // Function to determine suitable text color
            function getContrastTextColor(hex) {
                hex = hex.replace("#", "");
                let r = parseInt(hex.substring(0, 2), 16);
                let g = parseInt(hex.substring(2, 4), 16);
                let b = parseInt(hex.substring(4, 6), 16);
                let brightness = (r * 299 + g * 587 + b * 114) / 1000;
                return brightness > 128 ? "black" : "white";
            }

            colorPicker.addEventListener("input", function() {
                let textColor = getContrastTextColor(this.value);
                previewBox.style.backgroundColor = this.value;
                previewBox.style.color = textColor;
                document.body.style.backgroundColor = this.value;
                document.body.style.color = textColor;
            });
        });
    </script>
    <?php
}

// 3. Register Settings
function smart_register_settings() {
    register_setting('smart_settings_group', 'smart_theme_color');

    add_settings_section('smart_main_section', 'Customize Your Theme Color', null, 'smart-theme-settings');

    add_settings_field(
        'smart_theme_color_field',
        'Select Theme Color:',
        'smart_theme_color_callback',
        'smart-theme-settings',
        'smart_main_section'
    );
}
add_action('admin_init', 'smart_register_settings');

// 4. Color Selection Field
function smart_theme_color_callback() {
    $color = get_option('smart_theme_color', '#3498db'); // Default color
    echo '<input type="color" name="smart_theme_color" value="' . esc_attr($color) . '">';
}

// 5. Apply Color on Frontend
function smart_apply_theme_color() {
    $color = get_option('smart_theme_color', '#3498db');

    // Contrast detection function in PHP
    function get_contrast_text_color($hex) {
        $hex = str_replace("#", "", $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
        return $brightness > 128 ? "black" : "white";
    }

    $text_color = get_contrast_text_color($color);

    echo "<style>
        body { 
            background-color: $color !important; 
            color: $text_color !important;
        }
    </style>";
}
add_action('wp_head', 'smart_apply_theme_color');
