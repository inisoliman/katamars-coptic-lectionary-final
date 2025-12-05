<?php
/**
 * Fired during plugin activation.
 *
 * This class handles the database migration from the SQL dump.
 */
if ( ! class_exists( 'Katamars_Activator' ) ) {

class Katamars_Activator {

    /**
     * Plugin activation logic.
     *
     * Checks if the required tables exist, and if not, imports them from the SQL dump.
     */
    public static function activate() {
        global $wpdb;

        // Define table names with WordPress prefix
        $tables = array(
            'bible_ar',
            'bible_en',
            'gr_days',
            'gr_lent',
            'gr_nineveh',
            'gr_pentecost',
            'gr_sundays'
        );

        $all_tables_exist = true;
        foreach ( $tables as $table ) {
            $table_name = $wpdb->prefix . 'katamars_' . $table;
            if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
                $all_tables_exist = false;
                break;
            }
        }

        // If tables don't exist, import from SQL dump
        if ( ! $all_tables_exist ) {
            self::import_database();
        }
    }

    /**
     * Import database tables from the SQL dump file.
     *
     * This function reads the SQL dump and executes the statements with improved error handling.
     */
    private static function import_database() {
        global $wpdb;

        $sql_file = KATAMARS_PLUGIN_DIR . 'data/u626751827_katamars.sql';

        if ( ! file_exists( $sql_file ) ) {
            error_log( 'Katamars: SQL dump file not found at ' . $sql_file );
            return false;
        }

        error_log( 'Katamars: Starting database import from ' . $sql_file );

        // Read the SQL file
        $sql_content = file_get_contents( $sql_file );

        if ( $sql_content === false ) {
            error_log( 'Katamars: Failed to read SQL dump file' );
            return false;
        }

        error_log( 'Katamars: SQL file size: ' . strlen( $sql_content ) . ' bytes' );

        // Use regex for more robust table name replacement
        $table_names = array(
            'bible_ar',
            'bible_en',
            'gr_days',
            'gr_lent',
            'gr_nineveh',
            'gr_pentecost',
            'gr_sundays'
        );

        foreach ( $table_names as $table ) {
            $new_table = $wpdb->prefix . 'katamars_' . $table;
            // Replace table names in CREATE, INSERT, ALTER, and other statements
            $sql_content = preg_replace(
                '/\b' . preg_quote( $table, '/' ) . '\b/',
                $new_table,
                $sql_content
            );
        }

        // Remove MySQL-specific commands that WordPress doesn't need
        $sql_content = preg_replace( '/^USE `.*?`;/m', '', $sql_content );
        $sql_content = preg_replace( '/^SET .*?;/m', '', $sql_content );
        $sql_content = preg_replace( '/^\/\*!.*?\*\/;/ms', '', $sql_content );
        
        // Remove LOCK/UNLOCK TABLE statements
        $sql_content = preg_replace( '/^LOCK TABLES.*?;/m', '', $sql_content );
        $sql_content = preg_replace( '/^UNLOCK TABLES.*?;/m', '', $sql_content );

        // Split into individual statements using improved parser
        $statements = self::split_sql_statements( $sql_content );

        error_log( 'Katamars: Found ' . count( $statements ) . ' SQL statements to execute' );

        // Suppress errors temporarily to handle them manually
        $wpdb->suppress_errors( true );
        $wpdb->show_errors( false );

        // Execute each statement
        $success_count = 0;
        $error_count = 0;
        $skipped_count = 0;

        foreach ( $statements as $index => $statement ) {
            $statement = trim( $statement );
            
            // Skip empty statements
            if ( empty( $statement ) ) {
                $skipped_count++;
                continue;
            }

            // Skip comments
            if ( preg_match( '/^(--|\/\*|#)/', $statement ) ) {
                $skipped_count++;
                continue;
            }

            // Execute the statement
            $result = $wpdb->query( $statement );

            if ( $result === false ) {
                $error_count++;
                $error_msg = $wpdb->last_error;
                
                // Log detailed error information
                error_log( sprintf(
                    'Katamars: SQL Error [Statement %d] - %s',
                    $index + 1,
                    $error_msg
                ) );
                
                // Log first 500 chars of failed statement
                $preview = substr( $statement, 0, 500 );
                if ( strlen( $statement ) > 500 ) {
                    $preview .= '... (truncated)';
                }
                error_log( 'Katamars: Failed statement: ' . $preview );
                
                // If it's a CREATE TABLE error, it might be critical
                if ( stripos( $statement, 'CREATE TABLE' ) !== false ) {
                    error_log( 'Katamars: CRITICAL - Failed to create table!' );
                }
            } else {
                $success_count++;
                
                // Log progress for CREATE and major INSERT statements
                if ( stripos( $statement, 'CREATE TABLE' ) !== false ) {
                    preg_match( '/CREATE TABLE\s+`?(\w+)`?/i', $statement, $matches );
                    if ( isset( $matches[1] ) ) {
                        error_log( 'Katamars: Successfully created table: ' . $matches[1] );
                    }
                }
            }
        }

        // Re-enable error display
        $wpdb->suppress_errors( false );
        $wpdb->show_errors( true );

        error_log( sprintf(
            'Katamars: Database import completed. Success: %d, Errors: %d, Skipped: %d',
            $success_count,
            $error_count,
            $skipped_count
        ) );

        // Verify tables were created
        $tables_created = 0;
        foreach ( $table_names as $table ) {
            $table_name = $wpdb->prefix . 'katamars_' . $table;
            if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
                $tables_created++;
                $row_count = $wpdb->get_var( "SELECT COUNT(*) FROM `$table_name`" );
                error_log( "Katamars: Table $table_name created with $row_count rows" );
            } else {
                error_log( "Katamars: WARNING - Table $table_name was NOT created!" );
            }
        }

        error_log( "Katamars: Tables created: $tables_created out of " . count( $table_names ) );

        return $error_count === 0 && $tables_created === count( $table_names );
    }

    /**
     * Split SQL content into individual statements.
     *
     * Improved parser that handles multi-line statements, string literals, and comments.
     *
     * @param string $sql The SQL content.
     * @return array Array of SQL statements.
     */
    private static function split_sql_statements( $sql ) {
        $statements = array();
        $current_statement = '';
        $in_string = false;
        $string_char = '';
        $in_comment = false;
        $comment_type = '';
        $length = strlen( $sql );

        for ( $i = 0; $i < $length; $i++ ) {
            $char = $sql[ $i ];
            $next_char = ( $i + 1 < $length ) ? $sql[ $i + 1 ] : '';
            $prev_char = ( $i > 0 ) ? $sql[ $i - 1 ] : '';

            // Handle multi-line comments /* ... */
            if ( ! $in_string && ! $in_comment && $char === '/' && $next_char === '*' ) {
                $in_comment = true;
                $comment_type = 'multi';
                $i++; // Skip the *
                continue;
            }

            if ( $in_comment && $comment_type === 'multi' && $char === '*' && $next_char === '/' ) {
                $in_comment = false;
                $comment_type = '';
                $i++; // Skip the /
                continue;
            }

            // Handle single-line comments -- and #
            if ( ! $in_string && ! $in_comment && ( 
                ( $char === '-' && $next_char === '-' ) || 
                $char === '#' 
            ) ) {
                // Skip until end of line
                while ( $i < $length && $sql[ $i ] !== "\n" && $sql[ $i ] !== "\r" ) {
                    $i++;
                }
                continue;
            }

            // Skip if we're in a comment
            if ( $in_comment ) {
                continue;
            }

            // Handle string literals with proper escape handling
            if ( ( $char === '"' || $char === "'" ) && $prev_char !== '\\' ) {
                if ( ! $in_string ) {
                    $in_string = true;
                    $string_char = $char;
                } elseif ( $char === $string_char ) {
                    // Check if it's an escaped quote (doubled quote like '' or "")
                    if ( $next_char === $char ) {
                        // It's an escaped quote, include both and skip next
                        $current_statement .= $char . $next_char;
                        $i++;
                        continue;
                    }
                    $in_string = false;
                }
            }

            $current_statement .= $char;

            // Check for statement terminator (semicolon not in string)
            if ( $char === ';' && ! $in_string ) {
                $trimmed = trim( $current_statement );
                if ( ! empty( $trimmed ) ) {
                    $statements[] = $trimmed;
                }
                $current_statement = '';
            }
        }

        // Add any remaining statement
        $trimmed = trim( $current_statement );
        if ( ! empty( $trimmed ) ) {
            $statements[] = $trimmed;
        }

        return $statements;
    }
}

} // End if class_exists check
