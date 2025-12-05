<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
if ( ! class_exists( 'Katamars' ) ) {

class Katamars {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        $this->version = KATAMARS_VERSION;
        $this->plugin_name = 'katamars-coptic-lectionary';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    private function load_dependencies() {
        require_once KATAMARS_PLUGIN_DIR . 'includes/class-katamars-loader.php';
        require_once KATAMARS_PLUGIN_DIR . 'includes/class-katamars-date.php';
        require_once KATAMARS_PLUGIN_DIR . 'includes/class-katamars-query.php';
        require_once KATAMARS_PLUGIN_DIR . 'includes/class-katamars-synaxarium.php';
        require_once KATAMARS_PLUGIN_DIR . 'includes/class-katamars-bible.php';
        require_once KATAMARS_PLUGIN_DIR . 'includes/class-katamars-shortcodes.php';
        require_once KATAMARS_PLUGIN_DIR . 'includes/class-katamars-admin.php';

        $this->loader = new Katamars_Loader();
    }
    private function define_admin_hooks() {
        $plugin_admin = new Katamars_Admin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'check_plugin_conflicts' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $plugin_shortcodes = new Katamars_Shortcodes();
        $this->loader->add_action( 'init', $plugin_shortcodes, 'register_shortcodes' );

        // SEO Hooks
        $this->loader->add_filter( 'query_vars', $plugin_shortcodes, 'add_query_vars' );
        $this->loader->add_action( 'init', $plugin_shortcodes, 'add_rewrite_rules' );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}

} // End if class_exists check
