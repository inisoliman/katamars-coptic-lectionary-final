<?php
/**
 * Synaxarium Content Handler.
 *
 * This class handles the file-based Synaxarium content.
 */
if ( ! class_exists( 'Katamars_Synaxarium' ) ) {

class Katamars_Synaxarium {

    /**
     * Get Synaxarium content for a specific Coptic date.
     *
     * @param array $coptic_date Coptic date information.
     * @param string $lang Language (ar or en).
     * @return string Synaxarium content.
     */
    public function get_synaxarium( $coptic_date, $lang = 'ar' ) {
        $month = $coptic_date['coptic_month'];
        $day = $coptic_date['coptic_day'];

        // Map month numbers to directory names
        $month_names = array(
            1  => 'Tut',
            2  => 'Babah',
            3  => 'Hatur',
            4  => 'Kiyahk',
            5  => 'Tubah',
            6  => 'Amshir',
            7  => 'Baramhat',
            8  => 'Baramudah',
            9  => 'Bashans',
            10 => 'Baunah',
            11 => 'Abib',
            12 => 'Misra',
            13 => 'Nasie'
        );

        $month_dir = isset( $month_names[ $month ] ) ? $month_names[ $month ] : '';

        // Construct the file path
        $file_path = KATAMARS_PLUGIN_DIR . 'data/synax-text/' . $lang . '/the_files/' . $month_dir . '/day' . $day . '.php';

        if ( ! file_exists( $file_path ) ) {
            return '';
        }

        // Initialize variables expected by the included file to prevent warnings
        $arstay = ''; 
        $text_ar_1 = ''; $text_ar_2 = ''; $text_ar_3 = ''; $text_ar_4 = ''; $text_ar_5 = '';
        $text_en_1 = ''; $text_en_2 = ''; $text_en_3 = ''; $text_en_4 = ''; $text_en_5 = '';
        // Generic variables used in some files
        $text_1 = ''; $text_2 = ''; $text_3 = ''; $text_3m = ''; $text_4 = ''; $text_5 = '';
        $ar_text = ''; // Fix for undefined variable warning

        // Define _ALONE constant to bypass "Restricted access" check in data files
        if ( ! defined( '_ALONE' ) ) {
            define( '_ALONE', true );
        }

        // Capture the output of the included file
        ob_start();
        include $file_path;
        $content = ob_get_clean();

        return $content;
    }

    /**
     * Check if Synaxarium file exists for a date.
     *
     * @param int $month Coptic month.
     * @param int $day Coptic day.
     * @param string $lang Language.
     * @return bool True if file exists.
     */
    public function synaxarium_exists( $month, $day, $lang = 'ar' ) {
        // Map month numbers to directory names
        $month_names = array(
            1  => 'Tut',
            2  => 'Babah',
            3  => 'Hatur',
            4  => 'Kiyahk',
            5  => 'Tubah',
            6  => 'Amshir',
            7  => 'Baramhat',
            8  => 'Baramudah',
            9  => 'Bashans',
            10 => 'Baunah',
            11 => 'Abib',
            12 => 'Misra',
            13 => 'Nasie'
        );

        $month_dir = isset( $month_names[ $month ] ) ? $month_names[ $month ] : '';
        
        $file_path = KATAMARS_PLUGIN_DIR . 'data/synax-text/' . $lang . '/the_files/' . $month_dir . '/day' . $day . '.php';
        return file_exists( $file_path );
    }
}

} // End if class_exists check
