<?php
/**
 * Coptic Date Calculation and Liturgical Day Determination.
 *
 * This class ports the logic from common/cal_helper.php and reading/reading_date.php
 */
if ( ! class_exists( 'Katamars_Date' ) ) {

class Katamars_Date {

    /**
     * Coptic month names.
     */
    private $coptic_months = array(
        1  => 'Tut',
        2  => 'Babah',
        3  => 'Hatur',
        4  => 'Kiyahk',
        5  => 'Tubah',
        6  => 'Amshir',
        7  => 'Baramhat',
        8  => 'Barmuda',
        9  => 'Bashans',
        10 => 'Baunah',
        11 => 'Abib',
        12 => 'Mesra',
        13 => 'Al-Nasi'
    );

    /**
     * Get Coptic date from Gregorian timestamp.
     *
     * @param int $timestamp Gregorian timestamp.
     * @return array Coptic date information.
     */
    public function get_coptic_date( $timestamp ) {
        $gregorian_year = (int) date( 'Y', $timestamp );
        $gregorian_month = (int) date( 'n', $timestamp );
        $gregorian_day = (int) date( 'j', $timestamp );

        // Calculate Coptic date
        $coptic_data = $this->gregorian_to_coptic( $gregorian_year, $gregorian_month, $gregorian_day );
        
        // Determine liturgical information
        $liturgical_info = $this->get_liturgical_day( $coptic_data, $timestamp );

        return array_merge( $coptic_data, $liturgical_info );
    }

    /**
     * Convert Gregorian date to Coptic date.
     *
     * @param int $year Gregorian year.
     * @param int $month Gregorian month.
     * @param int $day Gregorian day.
     * @return array Coptic date components.
     */
    private function gregorian_to_coptic( $year, $month, $day ) {
        // Based on original Katamars script logic from reading_cal.php
        // January 1st = Kiyahk 23 or 24 (depending on leap year)
        
        $timestamp = mktime( 0, 0, 0, $month, $day, $year );
        $is_leap = $this->is_gregorian_leap_year( $year );
        
        // Starting point: January 1st of this year
        $jan_1_timestamp = mktime( 0, 0, 0, 1, 1, $year );
        
        // Calculate day of year (0-indexed)
        $day_of_year = (int) date( 'z', $timestamp );
        
        // January 1st corresponds to Kiyahk 23 (or 24 in leap years)
        $coptic_month = 4;  // Kiyahk
        $coptic_day = 23;
        
        if ( $is_leap == 0 ) {
            $coptic_day = 24;  // In non-leap years, Jan 1 = Kiyahk 24
        }
        
        // Coptic year calculation
        $coptic_year = $year - 284;
        
        // Iterate through days
        $g = 1;
        $count = $day_of_year;
        
        if ( $count != $g ) {
            do {
                $coptic_day++;
                $g++;
                
                // Handle month overflow (Coptic months have 30 days except Nasie)
                if ( $coptic_day == 31 ) {
                    $coptic_day = 1;
                    $coptic_month++;
                }
                
                // Handle 13th month (Nasie) - 5 or 6 days
                $next_year_is_leap = $this->is_gregorian_leap_year( $year + 1 );
                $nasie_days = $next_year_is_leap ? 6 : 5;
                
                if ( $coptic_month == 13 && $coptic_day > $nasie_days ) {
                    $coptic_day = 1;
                    $coptic_month = 1;
                    $coptic_year++;
                }
                
            } while ( $g < $count );
        }
        
        return array(
            'coptic_year'  => $coptic_year,
            'coptic_month' => $coptic_month,
            'coptic_day'   => $coptic_day,
            'month_name'   => $this->coptic_months[ $coptic_month ],
            'formatted'    => $coptic_day . ' ' . $this->coptic_months[ $coptic_month ] . ' ' . $coptic_year,
            'gregorian_year' => $year,
            'gregorian_month' => $month,
            'gregorian_day' => $day,
        );
    }

    /**
     * Determine liturgical day information.
     *
     * @param array $coptic_data Coptic date data.
     * @param int $timestamp Gregorian timestamp.
     * @return array Liturgical information.
     */
    private function get_liturgical_day( $coptic_data, $timestamp ) {
        $day_of_week = (int) date( 'w', $timestamp ); // 0 = Sunday
        $is_sunday = ( $day_of_week === 0 );

        // Calculate Easter and special seasons
        $easter_data = $this->calculate_easter( $coptic_data['gregorian_year'] );
        
        $liturgical_info = array(
            'day_of_week' => $day_of_week,
            'is_sunday' => $is_sunday,
            'feast_name' => '',
            'season' => '',
            'is_lent' => false,
            'is_pentecost' => false,
            'lent_week' => 0,
            'pentecost_week' => 0,
        );

        // Determine if in Lent season
        $lent_info = $this->check_lent_season( $timestamp, $easter_data );
        if ( $lent_info['is_lent'] ) {
            $liturgical_info['is_lent'] = true;
            $liturgical_info['lent_week'] = $lent_info['week'];
            $liturgical_info['season'] = 'Lent';
        }

        // Determine if in Pentecost season
        $pentecost_info = $this->check_pentecost_season( $timestamp, $easter_data );
        if ( $pentecost_info['is_pentecost'] ) {
            $liturgical_info['is_pentecost'] = true;
            $liturgical_info['pentecost_week'] = $pentecost_info['week'];
            $liturgical_info['season'] = 'Pentecost';
        }

        // Check for specific feasts
        $feast = $this->check_feast_days( $coptic_data['coptic_month'], $coptic_data['coptic_day'] );
        if ( $feast ) {
            $liturgical_info['feast_name'] = $feast;
        }

        return $liturgical_info;
    }

    /**
     * Calculate Easter date for a given year.
     *
     * @param int $year Gregorian year.
     * @return array Easter date information.
     */
    private function calculate_easter( $year ) {
        // Coptic Easter calculation (using Alexandrian computation)
        $golden_number = ( $year % 19 ) + 1;
        $century = (int) ( $year / 100 ) + 1;
        $solar_correction = (int) ( ( 3 * $century ) / 4 ) - 12;
        $lunar_correction = (int) ( ( 8 * $century + 5 ) / 25 ) - 5;
        $sunday_letter = (int) ( ( 5 * $year ) / 4 ) - $solar_correction - 10;
        
        $epact = ( 11 * $golden_number + 20 + $lunar_correction - $solar_correction ) % 30;
        if ( ( $epact == 25 && $golden_number > 11 ) || $epact == 24 ) {
            $epact++;
        }
        
        $full_moon = 44 - $epact;
        if ( $full_moon < 21 ) {
            $full_moon += 30;
        }
        
        $full_moon = $full_moon + 7 - ( ( $sunday_letter + $full_moon ) % 7 );
        
        if ( $full_moon > 31 ) {
            $easter_month = 4;
            $easter_day = $full_moon - 31;
        } else {
            $easter_month = 3;
            $easter_day = $full_moon;
        }

        $easter_timestamp = mktime( 0, 0, 0, $easter_month, $easter_day, $year );

        return array(
            'month' => $easter_month,
            'day' => $easter_day,
            'timestamp' => $easter_timestamp,
        );
    }

    /**
     * Check if currently in Lent season.
     *
     * @param int $timestamp Current timestamp.
     * @param array $easter_data Easter date data.
     * @return array Lent information.
     */
    private function check_lent_season( $timestamp, $easter_data ) {
        // Lent starts 55 days before Easter (including Holy Week)
        $lent_start = $easter_data['timestamp'] - ( 55 * 86400 );
        $lent_end = $easter_data['timestamp'] - 86400; // Day before Easter

        if ( $timestamp >= $lent_start && $timestamp <= $lent_end ) {
            $days_into_lent = (int) ( ( $timestamp - $lent_start ) / 86400 );
            $week = (int) ( $days_into_lent / 7 ) + 1;
            
            return array(
                'is_lent' => true,
                'week' => $week,
            );
        }

        return array( 'is_lent' => false, 'week' => 0 );
    }

    /**
     * Check if currently in Pentecost season.
     *
     * @param int $timestamp Current timestamp.
     * @param array $easter_data Easter date data.
     * @return array Pentecost information.
     */
    private function check_pentecost_season( $timestamp, $easter_data ) {
        // Pentecost season is 50 days after Easter
        $pentecost_start = $easter_data['timestamp'];
        $pentecost_end = $easter_data['timestamp'] + ( 50 * 86400 );

        if ( $timestamp >= $pentecost_start && $timestamp <= $pentecost_end ) {
            $days_into_pentecost = (int) ( ( $timestamp - $pentecost_start ) / 86400 );
            $week = (int) ( $days_into_pentecost / 7 ) + 1;
            
            return array(
                'is_pentecost' => true,
                'week' => $week,
            );
        }

        return array( 'is_pentecost' => false, 'week' => 0 );
    }

    /**
     * Check for specific feast days.
     *
     * @param int $month Coptic month.
     * @param int $day Coptic day.
     * @return string|false Feast name or false.
     */
    private function check_feast_days( $month, $day ) {
        // Major feasts in the Coptic calendar
        $feasts = array(
            '1-1'   => 'Feast of Nayrouz (Coptic New Year)',
            '3-17'  => 'Feast of the Cross',
            '4-28'  => 'Nativity Fast Begins',
            '4-29'  => 'Annunciation',
            '5-11'  => 'Baptism of Christ',
            '5-12'  => 'Epiphany',
            '7-29'  => 'Annunciation',
        );

        $key = $month . '-' . $day;
        return isset( $feasts[ $key ] ) ? $feasts[ $key ] : false;
    }

    /**
     * Check if a Gregorian year is a leap year.
     *
     * @param int $year Gregorian year.
     * @return bool True if leap year.
     */
    private function is_gregorian_leap_year( $year ) {
        return ( ( $year % 4 == 0 ) && ( $year % 100 != 0 ) ) || ( $year % 400 == 0 );
    }
}

} // End if class_exists check
