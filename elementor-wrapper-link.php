<?php
/**
 * Plugin Name: Elementor Wrapper Link (Section, Column, Container)
 * Description: Adds a clickable wrapper (section/column/container) link for Elementor elements. Supports dynamic tags (ACF/JetEngine).
 * Version: 1.5
 * Author: Abe Prangishvili
 * Text Domain: elementor-wrapper-link
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Plugin constants
if ( ! defined( 'EWL_PLUGIN_FILE' ) ) {
    define( 'EWL_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'EWL_PLUGIN_DIR' ) ) {
    define( 'EWL_PLUGIN_DIR', plugin_dir_path( EWL_PLUGIN_FILE ) );
}
if ( ! defined( 'EWL_PLUGIN_URL' ) ) {
    define( 'EWL_PLUGIN_URL', plugin_dir_url( EWL_PLUGIN_FILE ) );
}

// Load main class
require_once EWL_PLUGIN_DIR . 'includes/class-elementor-wrapper-link.php';

// Instantiate
function ewl_run_plugin() {
    return new Elementor_Wrapper_Link_Plugin();
}

// Safe bootstrap only when Elementor is present
add_action( 'plugins_loaded', function() {
    // load translations
    load_plugin_textdomain( 'elementor-wrapper-link', false, dirname( plugin_basename( EWL_PLUGIN_FILE ) ) . '/languages' );

    // Defer instantiation to after Elementor is initialized
    if ( defined( 'ELEMENTOR_VERSION' ) || class_exists( '\Elementor\Plugin' ) ) {
        ewl_run_plugin();
    } else {
        // Show admin notice if needed (only in admin)
        add_action( 'admin_notices', function() {
            if ( current_user_can( 'activate_plugins' ) ) {
                echo '<div class="error"><p>' . esc_html__( 'Elementor Wrapper Link requires Elementor to be installed and activated.', 'elementor-wrapper-link' ) . '</p></div>';
            }
        } );
    }
}, 20 );
