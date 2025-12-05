<?php
/**
 * Admin-specific functionality.
 */
if ( ! class_exists( 'Katamars_Admin' ) ) {

class Katamars_Admin {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Check for conflicting plugins and display admin notice.
     */
    public function check_plugin_conflicts() {
        // Check if there are other katamars plugins active
        $active_plugins = get_option( 'active_plugins' );
        $conflicting_plugins = array();
        
        foreach ( $active_plugins as $plugin ) {
            // Check for old katamars-wp plugin or similar
            if ( strpos( $plugin, 'katamars' ) !== false && $plugin !== 'katamars-coptic-lectionary/katamars-coptic-lectionary.php' ) {
                $conflicting_plugins[] = $plugin;
            }
        }
        
        if ( ! empty( $conflicting_plugins ) ) {
            add_action( 'admin_notices', array( $this, 'display_conflict_notice' ) );
        }
    }

    /**
     * Display admin notice about conflicting plugins.
     */
    public function display_conflict_notice() {
        ?>
        <div class="notice notice-error">
            <p><strong><?php _e( 'Katamars Plugin Conflict Detected!', 'katamars-coptic-lectionary' ); ?></strong></p>
            <p><?php _e( 'Another Katamars plugin is active and may cause conflicts. Please deactivate any old Katamars plugins to avoid errors.', 'katamars-coptic-lectionary' ); ?></p>
        </div>
        <?php
    }


    /**
     * Register the administration menu for this plugin.
     */
    public function add_plugin_admin_menu() {
        add_options_page(
            __( 'Katamars Settings', 'katamars-coptic-lectionary' ),
            __( 'Katamars', 'katamars-coptic-lectionary' ),
            'manage_options',
            $this->plugin_name,
            array( $this, 'display_plugin_setup_page' )
        );
    }

    /**
     * Render the settings page for this plugin.
     */
    public function display_plugin_setup_page() {
        ?>
        <div class="wrap">
            <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
            <p><?php _e( 'Katamars Coptic Lectionary Settings', 'katamars-coptic-lectionary' ); ?></p>
            <p><?php _e( 'Use the shortcode [katamars_today] to display today\'s readings on any page or post.', 'katamars-coptic-lectionary' ); ?></p>
            
            <h3><?php _e( 'Database Status', 'katamars-coptic-lectionary' ); ?></h3>
            <?php
            global $wpdb;
            $tables = array(
                'bible_ar',
                'bible_en',
                'gr_days',
                'gr_lent',
                'gr_nineveh',
                'gr_pentecost',
                'gr_sundays'
            );
            
            echo '<ul>';
            foreach ( $tables as $table ) {
                $table_name = $wpdb->prefix . 'katamars_' . $table;
                $exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name;
                $count = $exists ? $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" ) : 0;
                
                echo '<li>';
                echo '<strong>' . esc_html( $table ) . '</strong>: ';
                echo $exists ? 
                    sprintf( __( 'Installed (%d records)', 'katamars-coptic-lectionary' ), $count ) : 
                    __( 'Not installed', 'katamars-coptic-lectionary' );
                echo '</li>';
            }
            echo '</ul>';
            ?>
        </div>
        <?php
    }
}

} // End if class_exists check
