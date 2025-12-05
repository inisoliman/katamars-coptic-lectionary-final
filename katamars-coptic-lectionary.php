<?php
/**
 * Plugin Name: Katamars Coptic Lectionary
 * Plugin URI: https://github.com/yourusername/katamars-coptic-lectionary
 * Description: A WordPress plugin to display daily Coptic Orthodox readings based on the Coptic calendar.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: katamars-coptic-lectionary
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 */
if ( ! defined( 'KATAMARS_VERSION' ) ) {
    define( 'KATAMARS_VERSION', '1.1.0' );
}
if ( ! defined( 'KATAMARS_PLUGIN_DIR' ) ) {
    define( 'KATAMARS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'KATAMARS_PLUGIN_URL' ) ) {
    define( 'KATAMARS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * The code that runs during plugin activation.
 */
function activate_katamars() {
    require_once KATAMARS_PLUGIN_DIR . 'includes/class-katamars-activator.php';
    Katamars_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_katamars() {
    require_once KATAMARS_PLUGIN_DIR . 'includes/class-katamars-deactivator.php';
    Katamars_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_katamars' );
register_deactivation_hook( __FILE__, 'deactivate_katamars' );

/**
 * The core plugin class.
 */
require KATAMARS_PLUGIN_DIR . 'includes/class-katamars.php';

/**
 * Load Widget Class
 */
require KATAMARS_PLUGIN_DIR . 'includes/class-katamars-widget.php';

/**
 * Register Widget
 */
function katamars_register_widget() {
    register_widget( 'Katamars_Readings_Widget' );
}
add_action( 'widgets_init', 'katamars_register_widget' );

/**
 * AJAX Handler for Getting Verses
 */
function katamars_ajax_get_verses() {
    check_ajax_referer( 'katamars_widget_nonce', 'nonce' );
    
    $reference = isset( $_POST['reference'] ) ? sanitize_text_field( $_POST['reference'] ) : '';
    
    if ( empty( $reference ) ) {
        wp_send_json_error( array( 'message' => 'No reference provided' ) );
    }
    
    // Load Query class
    require_once KATAMARS_PLUGIN_DIR . 'includes/class-katamars-query.php';
    $query = new Katamars_Query();
    
    // Get verses text
    $verses_html = $query->get_reading_text( $reference, 'ar' );
    
    if ( empty( $verses_html ) ) {
        wp_send_json_error( array( 'message' => 'No verses found' ) );
    }
    
    wp_send_json_success( array( 'html' => $verses_html ) );
}
add_action( 'wp_ajax_katamars_get_verses', 'katamars_ajax_get_verses' );
add_action( 'wp_ajax_nopriv_katamars_get_verses', 'katamars_ajax_get_verses' );

/**
 * Enqueue Widget Assets
 */
function katamars_enqueue_widget_assets() {
    if ( is_active_widget( false, false, 'katamars_readings_widget' ) ) {
        wp_enqueue_style( 'katamars-widget-css', KATAMARS_PLUGIN_URL . 'assets/css/katamars-widget.css', array(), '1.0.2' );
        wp_enqueue_script( 'katamars-widget-js', KATAMARS_PLUGIN_URL . 'assets/js/katamars-widget.js', array(), '1.0.0', true );
        
        wp_localize_script( 'katamars-widget-js', 'katamarWidget', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'katamars_widget_nonce' ),
            'homeUrl' => home_url()
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'katamars_enqueue_widget_assets' );

/**
 * Begins execution of the plugin.
 */
function run_katamars() {
    $plugin = new Katamars();
    $plugin->run();
}
run_katamars();
