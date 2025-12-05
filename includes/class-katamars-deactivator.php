<?php
/**
 * Fired during plugin deactivation.
 */
if ( ! class_exists( 'Katamars_Deactivator' ) ) {

class Katamars_Deactivator {

    /**
     * Plugin deactivation logic.
     *
     * Currently does nothing, but can be extended for cleanup tasks.
     */
    public static function deactivate() {
        // Cleanup tasks if needed
    }
}

} // End if class_exists check
