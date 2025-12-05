<?php
/**
 * Database Query Handler for Readings.
 *
 * This class ports the logic from reading/reading_func.php and reading/disp_books.php
 */
if ( ! class_exists( 'Katamars_Query' ) ) {

class Katamars_Query {

    /**
     * Get readings for a specific Coptic date.
     *
     * @param array $coptic_date Coptic date information.
     * @param string $lang Language (ar or en).
     * @return array Readings data.
     */
    public function get_readings( $coptic_date, $lang = 'ar' ) {
        global $wpdb;

        $readings = array();

        // Determine which table to query based on season
        if ( $coptic_date['is_lent'] ) {
            $readings = $this->get_lent_readings( $coptic_date, $lang );
        } elseif ( $coptic_date['is_pentecost'] ) {
            $readings = $this->get_pentecost_readings( $coptic_date, $lang );
        } else {
            // Check for Sunday
            $day_of_week = $coptic_date['day_of_week']; // 0 = Sunday
            
            error_log( sprintf( 'Katamars Query: Checking readings for date %s, Day of Week: %d', $coptic_date['formatted'], $day_of_week ) );

            if ( $day_of_week == 0 ) {
                error_log( 'Katamars Query: It is a Sunday. Attempting to fetch Sunday readings.' );
                $readings = $this->get_sunday_readings( $coptic_date, $lang );
                if ( ! empty( $readings ) ) {
                    error_log( 'Katamars Query: Sunday readings found.' );
                } else {
                    error_log( 'Katamars Query: Sunday readings NOT found. Falling back to daily.' );
                }
            }

            // If not Sunday or no Sunday readings found, try daily readings
            if ( empty( $readings ) ) {
                $readings = $this->get_daily_readings( $coptic_date, $lang );
            }
        }

        // Fetch actual text for each reading reference
        foreach ( $readings as $type => &$reading_refs ) {
            if ( is_array( $reading_refs ) ) {
                foreach ( $reading_refs as &$ref ) {
                    if ( ! empty( $ref['reference'] ) ) {
                        $ref['text'] = $this->get_reading_text( $ref['reference'], $lang );
                    }
                }
            }
        }

        return $readings;
    }

    /**
     * Get daily readings from gr_days table.
     *
     * @param array $coptic_date Coptic date information.
     * @param string $lang Language.
     * @return array Readings.
     */
    private function get_daily_readings( $coptic_date, $lang ) {
        global $wpdb;

        $table = $wpdb->prefix . 'katamars_gr_days';
        $month = $coptic_date['coptic_month'];
        $day = $coptic_date['coptic_day'];

        // Debug logging
        error_log( sprintf( 
            'Katamars Daily Readings Query: Month=%d, Day=%d, Coptic Date=%s',
            $month,
            $day,
            $coptic_date['formatted']
        ) );

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE Month_Number = %d AND Day = %d",
            $month,
            $day
        ), ARRAY_A );

        if ( ! $row ) {
            error_log( sprintf(
                'Katamars: No daily readings found for Month=%d, Day=%d. Last SQL Error: %s',
                $month,
                $day,
                $wpdb->last_error
            ) );
            return array();
        }

        // Check for 'Other' column redirection (Recursive fallback)
        $attempts = 0;
        while ( ! empty( $row['Other'] ) && $attempts < 3 ) {
            $other_ref = $row['Other'];
            
            // Check if it starts with a number (valid reference)
            if ( is_numeric( substr( $other_ref, 0, 1 ) ) ) {
                error_log( sprintf( 'Katamars: Redirecting readings from %d/%d to %s', $month, $day, $other_ref ) );
                
                $parts = explode( '/', $other_ref );
                if ( count( $parts ) >= 2 ) {
                    $new_month = intval( $parts[0] );
                    $new_day = intval( $parts[1] );
                    
                    $row = $wpdb->get_row( $wpdb->prepare(
                        "SELECT * FROM $table WHERE Month_Number = %d AND Day = %d",
                        $new_month,
                        $new_day
                    ), ARRAY_A );
                    
                    if ( ! $row ) {
                        error_log( sprintf( 'Katamars: Redirect failed to %d/%d', $new_month, $new_day ) );
                        break;
                    }
                    
                    // Update current month/day for logging if needed
                    $month = $new_month;
                    $day = $new_day;
                }
            } else {
                break; 
            }
            $attempts++;
        }

        error_log( 'Katamars: Daily readings found successfully' );
        return $this->parse_reading_row( $row );
    }

    /**
     * Get Sunday readings from gr_sundays table.
     *
     * @param array $coptic_date Coptic date information.
     * @param string $lang Language.
     * @return array Readings.
     */
    private function get_sunday_readings( $coptic_date, $lang ) {
        global $wpdb;

        $table = $wpdb->prefix . 'katamars_gr_sundays';
        $month = $coptic_date['coptic_month'];
        $day = $coptic_date['coptic_day'];

        // Calculate which Sunday of the month this is (1st, 2nd, 3rd, 4th, or 5th)
        // Use the Gregorian date from $coptic_date to find the Sunday number
        $sunday_number = $this->calculate_sunday_number_simple( 
            $month, 
            $day, 
            $coptic_date['coptic_year'],
            $coptic_date['gregorian_year'],
            $coptic_date['gregorian_month'],
            $coptic_date['gregorian_day']
        );
        
        error_log( sprintf( 'Katamars Query: Sunday Calculation: Month=%d, Day=%d, Calculated Sunday Number=%d', $month, $day, $sunday_number ) );

        if ( $sunday_number === 0 ) {
            // Not a valid Sunday, fallback to daily readings
            return array();
        }

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE Month_Number = %d AND Day = %d",
            $month,
            $sunday_number  // Use Sunday number (1-5) mapped to Day column
        ), ARRAY_A );

        if ( ! $row ) {
            error_log( sprintf( 'Katamars Query: No Sunday readings found for Month=%d, Day=%d', $month, $sunday_number ) );
            return array();
        }

        return $this->parse_reading_row( $row );
    }

    /**
     * Calculate which Sunday of the month a given Coptic day is (simplified version).
     *
     * @param int $coptic_month Coptic month.
     * @param int $coptic_day Coptic day.
     * @param int $coptic_year Coptic year.
     * @param int $greg_year Gregorian year of this date.
     * @param int $greg_month Gregorian month of this date.
     * @param int $greg_day Gregorian day of this date.
     * @return int Sunday number (1-5), or 0 if not applicable.
     */
    private function calculate_sunday_number_simple( $coptic_month, $coptic_day, $coptic_year, $greg_year, $greg_month, $greg_day ) {
        // Count Sundays in this Coptic month up to this day
        // We'll use Katamars_Date to properly convert each day
        
        $date_calc = new Katamars_Date();
        $sunday_count = 0;
        
        // Check each day from 1 to $coptic_day
        for ( $d = 1; $d <= $coptic_day; $d++ ) {
            // We need to find the Gregorian equivalent of this Coptic date
            // The challenge: we need to go backwards from the current Gregorian date
            
            // Calculate how many days back from current date
            $days_back = $coptic_day - $d;
            
            // Get the timestamp for that many days back
            $check_timestamp = mktime( 0, 0, 0, $greg_month, $greg_day - $days_back, $greg_year );
            
            // Check if it's a Sunday
            $day_of_week = (int) date( 'w', $check_timestamp );
            
            if ( $day_of_week === 0 ) { // Sunday
                $sunday_count++;
            }
        }
        
        return $sunday_count;
    }

    /**
     * Get Lent readings from gr_lent table.
     *
     * @param array $coptic_date Coptic date information.
     * @param string $lang Language.
     * @return array Readings.
     */
    private function get_lent_readings( $coptic_date, $lang ) {
        global $wpdb;

        $table = $wpdb->prefix . 'katamars_gr_lent';
        $week = $coptic_date['lent_week'];
        $day_of_week = $coptic_date['day_of_week'];

        // Map day of week to day name
        $day_names = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
        $day_name = $day_names[ $day_of_week ];

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE Week = %d AND Day = %s",
            $week,
            $day_name
        ), ARRAY_A );

        if ( ! $row ) {
            return array();
        }

        return $this->parse_reading_row( $row );
    }

    /**
     * Get Pentecost readings from gr_pentecost table.
     *
     * @param array $coptic_date Coptic date information.
     * @param string $lang Language.
     * @return array Readings.
     */
    private function get_pentecost_readings( $coptic_date, $lang ) {
        global $wpdb;

        $table = $wpdb->prefix . 'katamars_gr_pentecost';
        $week = $coptic_date['pentecost_week'];
        $day_of_week = $coptic_date['day_of_week'];

        $day_names = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
        $day_name = $day_names[ $day_of_week ];

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE Week = %d AND Day = %s",
            $week,
            $day_name
        ), ARRAY_A );

        if ( ! $row ) {
            return array();
        }

        return $this->parse_reading_row( $row );
    }

    /**
     * Parse a reading row from the database.
     *
     * @param array $row Database row.
     * @return array Parsed readings.
     */
    private function parse_reading_row( $row ) {
        $readings = array();

        // Debug logging for raw row
        error_log( 'Katamars Parse Row: Raw Row Keys: ' . implode( ', ', array_keys( $row ) ) );
        
        // Add Metadata
        if ( ! empty( $row['DayName'] ) ) $readings['day_name'] = $row['DayName'];
        if ( ! empty( $row['Season'] ) ) $readings['season'] = $row['Season'];
        if ( ! empty( $row['Day_Tune'] ) ) $readings['day_tune'] = $row['Day_Tune'];

        // Map database columns to reading types
        $reading_types = array(
            'V_Psalm_Ref'   => 'vespers_psalm',
            'V_Gospel_Ref'  => 'vespers_gospel',
            'M_Psalm_Ref'   => 'matins_psalm',
            'M_Gospel_Ref'  => 'matins_gospel',
            'P_Gospel_Ref'  => 'pauline_epistle',
            'C_Gospel_Ref'  => 'catholic_epistle',
            'X_Gospel_Ref'  => 'acts',
            'L_Psalm_Ref'   => 'liturgy_psalm',
            'L_Gospel_Ref'  => 'liturgy_gospel',
            'Prophecy'      => 'prophecy',
        );

        foreach ( $reading_types as $column => $type ) {
            // Check if column exists and has value
            $value = isset( $row[ $column ] ) ? $row[ $column ] : null;
            
            // Debug logging for specific columns to check content
            if ( $column === 'V_Psalm_Ref' || $column === 'M_Gospel_Ref' ) {
                error_log( sprintf( 'Katamars Parse Row: Column %s, Value: "%s", Type: %s', $column, $value, gettype($value) ) );
            }

            if ( ! empty( $value ) && $value !== 'NULL' ) {
                $readings[ $type ] = array(
                    array(
                        'reference' => $value,
                        'text' => '',
                    )
                );
            }
        }

        return $readings;
    }

    /**
     * Get actual text for a reading reference.
     *
     * @param string $reference Reading reference (e.g., "Psalm 1:1-5").
     * @param string $lang Language.
     * @return string Reading text.
     */
    public function get_reading_text( $reference, $lang ) {
        global $wpdb;

        if ( ! isset( $wpdb ) ) {
            error_log( 'Katamars Error: $wpdb is not set in get_reading_text' );
            return 'Error: Database connection missing';
        }

        // Handle Compound References (e.g., "Book 1:1-5*@+Book 2:1-3")
        if ( preg_match( '/[\*@\+]+/', $reference ) ) {
            $sub_refs = preg_split( '/[\*@\+]+/', $reference );
            $full_text = '';
            foreach ( $sub_refs as $sub_ref ) {
                $sub_ref = trim( $sub_ref );
                if ( ! empty( $sub_ref ) ) {
                    $full_text .= $this->get_reading_text( $sub_ref, $lang ) . ' ';
                }
            }
            return trim( $full_text );
        }

        $table = $wpdb->prefix . ( $lang === 'ar' ? 'katamars_bible_ar' : 'katamars_bible_en' );
        
        // Parse reference (Book Chapter:Verse-Verse)
        // Example: "Psalms 1:1-5" or "John 1:1"
        
        $parts = explode( ' ', $reference );
        $ref_part = array_pop( $parts ); // Last part is usually Chapter:Verses
        $book_name = implode( ' ', $parts ); // Rest is book name
        
        // Handle Chapter:Verses
        $chapter_verse = explode( ':', $ref_part );
        $chapter = isset( $chapter_verse[0] ) ? intval( $chapter_verse[0] ) : 0;
        $verses = isset( $chapter_verse[1] ) ? $chapter_verse[1] : '';
        
        // Handle Commas (e.g., "10,14,15" or "1-2,5")
        if ( strpos( $verses, ',' ) !== false ) {
            $v_segments = explode( ',', $verses );
            $full_text = '';
            foreach ( $v_segments as $segment ) {
                $segment = trim( $segment );
                if ( empty( $segment ) ) continue;
                
                // Reconstruct reference for this segment
                $segment_ref = "$book_name $chapter:$segment";
                $full_text .= $this->get_reading_text( $segment_ref, $lang ) . ' ';
            }
            return trim( $full_text );
        }
        
        // Handle Verse Range (Start-End)
        $verse_start = 0;
        $verse_end = 0;
        
        if ( strpos( $verses, '-' ) !== false ) {
            $v_parts = explode( '-', $verses );
            $verse_start = intval( $v_parts[0] );
            $verse_end = intval( $v_parts[1] );
        } else {
            $verse_start = intval( $verses );
            $verse_end = $verse_start;
        }

        // Query the database using Book_Name
        $query = $wpdb->prepare(
            "SELECT Verse, Text FROM $table WHERE Book_Name = %s AND Chapter = %d AND Verse >= %d AND Verse <= %d ORDER BY Verse ASC",
            $book_name,
            $chapter,
            $verse_start,
            $verse_end
        );
        
        $results = $wpdb->get_results( $query, ARRAY_A );
        
        if ( ! $results ) {
            // Log error if no results found to help debugging
            error_log( sprintf( 
                'Katamars: No text found for reference "%s" (Book: "%s", Chapter: %d, Verses: %d-%d) in table %s', 
                $reference, $book_name, $chapter, $verse_start, $verse_end, $table 
            ) );
            return ''; 
        }
        
        $formatted_text = '';
        foreach ( $results as $row ) {
            $verse_num = $row['Verse'];
            $text = $row['Text'];
            $formatted_text .= sprintf( 
                '<span class="katamars-verse" data-verse="%d"><span class="verse-number">%d</span> %s</span> ', 
                $verse_num, 
                $verse_num, 
                $text 
            );
        }
        
        return trim( $formatted_text );
    }

}

} // End if class_exists check
