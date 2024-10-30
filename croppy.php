<?php
/**
 * Plugin Name: Croppy - AI assisted image cropper
 * Plugin URI: https://croppy.at
 * Version: 1.0.2
 * Author: SEADEV Studios GmbH
 * Author URI: https://seadev-studios.com
 * Text Domain: croppy-ai-assisted-image-cropper
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.0
 * Tested up to: 6.6.2
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
**/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add menu item to left sidebar
function croppy_add_menu_item() {
    // Add menu page with icon
    add_menu_page(
        'Croppy',              // Page title
        'Croppy',              // Menu title
        'manage_options',      // Capability required to access the page
        'croppy-settings',     // Menu slug
        'croppy_settings_page',// Callback function to render the page
        'dashicons-format-image',
        10                      // Position in the menu
    );
}

$croppy_configs = include('config.php');
$croppy_upload_dir = wp_upload_dir();
$croppy_dirname = $croppy_upload_dir['basedir'] . '/croppy-ai-assisted-image-cropper';

// Callback function to render the settings page
function croppy_settings_page() {
    global $croppy_configs;
    $croppy_link_uid = get_option('croppy_link_uid');
    $croppy_token = get_option('croppy_token');
    $croppy_tenant = get_option('croppy_tenant');

    // Get the URL of the logo image
    $logo_url = plugins_url('assets/logo.png', __FILE__);
    $cropper_route = $croppy_configs['CROPPER'];

    if (!empty($croppy_link_uid) && !empty($croppy_token) && croppy_validate_token($croppy_link_uid, $croppy_token)) {
        $server_domain = base64_encode(site_url());
        $iframe_url = $cropper_route . '/interface?uuid=' . urlencode($croppy_link_uid) . '&token=' . urlencode($croppy_token) . '&tenant=' . urlencode($croppy_tenant) . '&server_domain=' . urlencode($server_domain);
        ?>
        <div class="croppy-wrap">
            <h1 class="croppy-header">
                <img class="croppy-logo" src="<?php echo esc_url($logo_url); ?>" alt="Croppy Logo">
                <strong><?php echo esc_html(get_admin_page_title()); ?></strong>
                <button class="croppy-logout"><?php esc_html_e('Logout', 'croppy-ai-assisted-image-cropper'); ?></button>
            </h1>
            <div class="croppy-card" style="margin-top: 16px">
                <p class="croppy-header-3"><?php esc_html_e('Successfully connected', 'croppy-ai-assisted-image-cropper') ?></p>
                <p><?php esc_html_e('Everything went perfectly fine, to access your personal cropper, click the button below!', 'croppy-ai-assisted-image-cropper'); ?><br/> <br />
                <a href="<?php echo esc_url($iframe_url); ?>" class="croppy-button" target="_blank"><?php esc_html_e('Open Croppy', 'croppy-ai-assisted-image-cropper'); ?></a>
            </div>
        </div>
        <?php
    } else {
        // Get the currently logged-in user's email address
        $current_user = wp_get_current_user();
        $default_email = $current_user->user_email;

        // Extract domain from email address
        $email_parts = explode('@', $default_email);
        $domain = isset($email_parts[1]) ? $email_parts[1] : '';

        ?>
        <div class="croppy-wrap">
            <h1 class="croppy-header">
                <img class="croppy-logo" src="<?php echo esc_url($logo_url); ?>" alt="Croppy Logo">
                <strong><?php echo esc_html(get_admin_page_title()); ?></strong>
            </h1>
            <div class="croppy-card" style="margin-top: 16px">
                <h2><?php esc_html_e('Croppy', 'croppy-ai-assisted-image-cropper'); ?></h2>
                <p><?php esc_html_e('This Plugin will help you to connect your WordPress Website with Croppy.', 'croppy-ai-assisted-image-cropper'); ?><br/>
                <br/>
                <?php esc_html_e('Steps you need to do before clicking finish:', 'croppy-ai-assisted-image-cropper'); ?></br>
                1. <?php esc_html_e('Create a new user with the rights to create and manage media', 'croppy-ai-assisted-image-cropper'); ?></br>
                2. <?php esc_html_e('Create an application password for this user', 'croppy-ai-assisted-image-cropper'); ?></br>
                3. <?php esc_html_e('Copy the email address of the user and paste it into the form below', 'croppy-ai-assisted-image-cropper'); ?></br>
                4. <?php esc_html_e('Copy the application password and paste it into the form below', 'croppy-ai-assisted-image-cropper'); ?></br>
                <br/>
                <?php esc_html_e('By clicking on finish it will do the following:', 'croppy-ai-assisted-image-cropper'); ?></br>
                1. <?php esc_html_e('Create a new user on our systems with the user mail and password that you provided', 'croppy-ai-assisted-image-cropper'); ?></br>
                2. <?php esc_html_e('Redirect you to the Croppy interface', 'croppy-ai-assisted-image-cropper'); ?></br>
                3. <?php esc_html_e('Set you up to let you crop', 'croppy-ai-assisted-image-cropper'); ?></br>
                <br/>
                <?php esc_html_e('Please do not modify the users password, as that is used to authenticate with the JSON RPC as a fallback method', 'croppy-ai-assisted-image-cropper'); ?></br>
                <?php esc_html_e('If you have any questions or need help, please reach out to us on', 'croppy-ai-assisted-image-cropper'); ?>
                <a href="https://discord.gg/KQEEu7vb" target="_blank"><?php esc_html_e('Discord', 'croppy-ai-assisted-image-cropper'); ?></a></br>
                </p>
                <p>
                    <form id="croppy-initialize-form" style="margin-left: 1rem; width: 100%;">
                        <label for="email"><?php esc_html_e('Email Address:', 'croppy-ai-assisted-image-cropper'); ?></label>
                        <input type="email" id="email" name="email" value="croppy@<?php echo esc_attr($domain); ?>" required style="min-width: 320px"><br/>
                        <label for="password"><?php esc_html_e('Application Password:', 'croppy-ai-assisted-image-cropper'); ?></label>
                        <input type="password" id="password" name="password" value="" required style="min-width: 320px">
                        <button class="croppy-button" type="submit" id="configure-button"><?php esc_html_e('Configure', 'croppy-ai-assisted-image-cropper'); ?></button>
                    </form>
                </p>
            </div>
        </div>
        <div>
            <h3><?php esc_html_e('Issues with security related plugins', 'croppy-ai-assisted-image-cropper'); ?></h3>
            <p>
                <p>
                    <?php esc_html_e('Please note that certain security-related plugins, such as WPSecurity or Remove XMLRPC Pingback Ping, may interfere with the functionality of this plugin. These plugins often disable or restrict the use of application passwords and the WordPress JSON-RPC API, which are essential for the proper operation of Croppy.', 'croppy-ai-assisted-image-cropper'); ?>
                </p>
                <p>
                    <?php esc_html_e('To find out more, check out the', 'croppy-ai-assisted-image-cropper'); ?>
                    <a href="https://seadev-studios.atlassian.net/wiki/spaces/CD/pages/2960687366/Connecting+to+Wordpress" target="_blank">
                        <?php esc_html_e('WordPress documentation', 'croppy-ai-assisted-image-cropper'); ?>
                    </a>
                    <?php esc_html_e('or visit the download page of the plugin', 'croppy-ai-assisted-image-cropper'); ?>
                </p>
            </p>
        </div>
        <?php
    }
}

function croppy_initialize_function() {
    $errors = new WP_ERROR();
    // Check if the request came from an authorized source
    if (!check_ajax_referer('croppy_ajax_nonce', 'nonce', false)) {
        croppy_log_message('[CONFIG] Nonce check failed');
        wp_send_json_error('[CONFIG] Nonce check failed');
        return;
    }

    // Get the email address from the submitted form data
    if (!isset($_POST['email'])) {
        croppy_log_message('[INPUT] Invalid email address - empty');
        $errors->add('user-input', 'mail_incorrect');
    }
    $email = sanitize_email(wp_unslash($_POST['email']));
    if (!$email) {
        croppy_log_message('[INPUT] Invalid email address');
        $errors->add('user-input', 'mail_incorrect');
    }
    if (!isset($_POST['password'])) {
        croppy_log_message('[INPUT] Invalid application password - empty');
        $errors->add('user-input', 'application_password_incorrect');
    }
    $application_password = sanitize_text_field(wp_unslash($_POST['password']));
    if (!$application_password) {
        croppy_log_message('[INPUT] Invalid application password');
        $errors->add('user-input', 'application_password_incorrect');
    }
    $application_password = str_replace(' ', '', $application_password);

    if (count($errors->get_error_codes())) {
        wp_send_json_error(array('errors' => $errors->get_error_messages()));
        return;
    }
    // instead of using the current user, check if there already is a croppy user, if not create a user specifically for the plugin, the user has to has the rights to create posts
    $croppy_user = get_user_by('login', 'croppy');
    $generated_pw = wp_generate_password();

    croppy_get_admin_panel_url($email, $croppy_user->ID, $generated_pw, $application_password);
}

function croppy_get_admin_panel_url($user_email, $user_id, $generated_pw, $application_password) {
    global $croppy_configs;
    // Generate a unique hash for the application password name
    $hash = md5(uniqid(wp_rand(), true));
    $app_password_name = 'Croppy-' . $hash;

    // Create a base64 string of the email, the application password, and the permalink structure
    $rest_route = rest_url();
    $base64_string = base64_encode($user_email . ':' . $application_password . ':' . $generated_pw);

    // Get the nonce
    $nonce = wp_create_nonce('croppy_admin_nonce');

    // Create the admin URL
    $admin_route = $croppy_configs['ADMIN'];
    $croppy_admin_url = $admin_route . '?server_domain=' . urlencode(site_url()) . '&redirect_url=' . urlencode(admin_url('admin.php?page=croppy-settings&nonce='.$nonce)) . '&auth=' . $base64_string . '&rest_route=' . urlencode($rest_route);

    // Return the URL
    echo wp_json_encode(array('url' => $croppy_admin_url));
    wp_die();
}

function croppy_enqueue_custom_style() {
    // Use plugins_url function to include your custom css file
    wp_enqueue_style('croppy-custom-style', plugins_url('css/styling.css', __FILE__), array(), '1.0.0');
    wp_enqueue_script('croppy-custom-script', plugins_url('js/script.js', __FILE__), array('jquery'), '1.0.0', true);
    $nonce = wp_create_nonce('croppy_ajax_nonce');
    croppy_log_message('[ENQUEUE] Enqueueing custom script with nonce: ' . $nonce);
    wp_localize_script('croppy-custom-script', 'croppy_ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => $nonce,
    ));
}

function croppy_validate_token($link_uid, $token) {
    global $croppy_configs;
    $croppy_tenant = get_option('croppy_tenant');
    $api_route = $croppy_configs['API'];

    $response = wp_remote_get($api_route . '/wp-plugin/validateApplicationToken/' . urlencode($token) . '?tenant=' . urlencode($croppy_tenant));

    if (is_wp_error($response)) {
        croppy_log_message('[VALIDATE_TOKEN] Error validating token: ' . $response->get_error_message());
        return false;
    }

    croppy_log_message('[VALIDATE_TOKEN] Success validating token.');
    return $response['response']['code'] === 200;
}

function croppy_log_message($message) {
    global $wp_filesystem;
    global $croppy_dirname;
    if (empty($wp_filesystem)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }
    if ( ! (file_exists( $croppy_dirname ) ) ) {
        wp_mkdir_p( $croppy_dirname );
    }
    $log_file = $croppy_dirname . '/debug.log';
    $current_time = gmdate('Y-m-d H:i:s');
    $wp_filesystem->put_contents($log_file, $current_time . ' - ' . $message . "\n", FS_CHMOD_FILE);
}

function croppy_uninstall() {
    global $croppy_configs;
    $croppy_token = get_option('croppy_token');
    $croppy_tenant = get_option('croppy_tenant');
    $api_route = $croppy_configs['API'];
    croppy_log_message('[UNINSTALL] Uninstalling Croppy plugin with route: ' . $api_route . ' and token: ' . $croppy_token . ' and tenant: ' . $croppy_tenant);

    if (!empty($croppy_token)) {
        $response = wp_remote_request($api_route . '/wp-plugin/deleteApplicationTokenByToken/' . urlencode($croppy_token) . '?tenant=' . urlencode($croppy_tenant), array(
            'method' => 'DELETE'
        ));

        if (is_wp_error($response)) {
            croppy_log_message('[UNINSTALL] Error deleting token: ' . $response->get_error_message());
        }
    }

    // Get the 'croppy' user
    $croppy_user = get_user_by('login', 'croppy');

    // If the 'croppy' user exists, delete it
    if ($croppy_user) {
        require_once(ABSPATH.'wp-admin/includes/user.php');
        wp_delete_user($croppy_user->ID);
    }
}

function croppy_logout() {
    global $croppy_configs;
    if (!isset($croppy_configs) || !is_array($croppy_configs)) {
        croppy_log_message('[LOGOUT] Error: $croppy_configs is not set or not an array, logout cannot be performed.');
        wp_send_json_error('[LOGOUT] Error: $croppy_configs is not set or not an array');
        return;
    }

    if (!check_ajax_referer('croppy_ajax_nonce', 'nonce', false)) {
        croppy_log_message('[LOGOUT] Nonce check failed');
        wp_send_json_error('[LOGOUT] Nonce check failed');
        return;
    }
    $croppy_token = get_option('croppy_token');
    $croppy_tenant = get_option('croppy_tenant');
    $api_route = $croppy_configs['API'];
    croppy_log_message('[LOGOUT] Logging out with token: ' . $croppy_token . ' and tenant: ' . $croppy_tenant . ' and route: ' . $api_route);

    if (!empty($croppy_token)) {
        $response = wp_remote_request($api_route . '/wp-plugin/deleteApplicationTokenByToken/' . urlencode($croppy_token) . '?tenant=' . urlencode($croppy_tenant), array(
            'method' => 'DELETE'
        ));

        if (is_wp_error($response)) {
            croppy_log_message('[LOGOUT] Error deleting token: ' . $response->get_error_message());
            wp_send_json_error('[LOGOUT] Error deleting token');
            return;
        }
    } else {
        croppy_log_message('[LOGOUT] No token found');
    }

    if (!delete_option('croppy_token')) {
        croppy_log_message('[LOGOUT] Failed to delete croppy_token option');
        wp_send_json_error('[LOGOUT] Failed to delete croppy_token option');
        return;
    }

    // Send the URL of the plugin page to the client-side
    wp_send_json_success(array('redirect_url' => admin_url('admin.php?page=croppy-settings')));
}

function croppy_load_textdomain( $mofile, $domain ) {
    croppy_log_message('[TEXTDOMAIN] Loading text domain: ' . $domain);
	if ( 'croppy-ai-assisted-image-cropper' === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
		$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
        croppy_log_message('[TEXTDOMAIN] Loading text domain: ' . $domain . ' with locale: ' . $locale);
		$mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $domain . '-' . $locale . '.mo';
	}
	return $mofile;
}

add_action('wp_ajax_croppy_logout', 'croppy_logout');
add_action('admin_menu', 'croppy_add_menu_item');
add_action('admin_init', function() {
    croppy_log_message('[INIT] Initializing Croppy plugin');
    load_plugin_textdomain( 'croppy-ai-assisted-image-cropper', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    if (isset($_GET['page']) && $_GET['page'] === 'croppy-settings' && isset($_GET['linkUID']) && isset($_GET['token']) && isset($_GET['tenant'])) {
        // Nonce check
        if (!isset($_GET['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'croppy_admin_nonce')) {
            croppy_log_message('[INIT] Nonce check failed');
            wp_die('Nonce check failed');
        }
        croppy_log_message('[INIT] Setting linkUID, token, and tenant options.');
        $linkUID = sanitize_text_field(wp_unslash($_GET['linkUID']));
        $token = sanitize_text_field(wp_unslash($_GET['token']));
        $tenant = sanitize_text_field(wp_unslash($_GET['tenant']));
        if (!$linkUID || !$token || !$tenant) {
            croppy_log_message('[INIT] Invalid linkUID, token, or tenant');
            return;
        }
        update_option('croppy_link_uid', $linkUID);
        update_option('croppy_token', $token);
        update_option('croppy_tenant', $tenant);
    } else {
        croppy_log_message('[INIT] Not setting linkUID, token, and tenant options.');
    }
});
add_action('admin_enqueue_scripts', 'croppy_enqueue_custom_style');
register_uninstall_hook(__FILE__, 'croppy_uninstall');
add_shortcode('croppy_initialize_form', 'croppy_initialize_form');
add_action('wp_ajax_initialize_croppy', 'croppy_initialize_function');
add_filter( 'load_textdomain_mofile', 'croppy_load_textdomain', 10, 2 );
