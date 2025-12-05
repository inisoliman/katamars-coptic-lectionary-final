<?php
/**
 * Katamars Daily Readings Widget
 * 
 * Displays today's readings with pop-up verse display
 */

if ( ! class_exists( 'Katamars_Readings_Widget' ) ) {

class Katamars_Readings_Widget extends WP_Widget {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'katamars_readings_widget',
            'قراءات اليوم - Katamars',
            array( 'description' => 'عرض قراءات اليوم مع إمكانية عرض الآيات' )
        );
    }

    /**
     * Front-end display of widget
     */
    public function widget( $args, $instance ) {
        echo $args['before_widget'];

        // Get today's readings
        $readings_data = $this->get_today_readings();

        if ( empty( $readings_data ) ) {
            echo '<p>لا توجد قراءات لهذا اليوم</p>';
            echo $args['after_widget'];
            return;
        }

        ?>
        <div class="katamars-widget-readings">
            <?php if ( ! empty( $readings_data['title'] ) ) : ?>
                <h3 class="widget-title"><?php echo esc_html( $readings_data['title'] ); ?></h3>
            <?php endif; ?>

            <div class="readings-container">
                
                <?php if ( ! empty( $readings_data['vespers'] ) ) : ?>
                    <div class="reading-section">
                        <h4 class="section-title">🌙 صلاة العشية</h4>
                        <ul class="reading-list">
                            <?php echo $this->render_reading_items( $readings_data['vespers'] ); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $readings_data['matins'] ) ) : ?>
                    <div class="reading-section">
                        <h4 class="section-title">🌅 صلاة باكر</h4>
                        <ul class="reading-list">
                            <?php echo $this->render_reading_items( $readings_data['matins'] ); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $readings_data['liturgy'] ) ) : ?>
                    <div class="reading-section">
                        <h4 class="section-title">✝️ القداس الإلهي</h4>
                        <ul class="reading-list">
                            <?php echo $this->render_reading_items( $readings_data['liturgy'] ); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $readings_data['synaxarium'] ) ) : ?>
                    <div class="reading-section synaxarium">
                        <h4 class="section-title">📜 السنكسار</h4>
                        <div class="synaxarium-content">
                            <?php echo wp_kses_post( $readings_data['synaxarium'] ); ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Pop-up Modal -->
        <div id="katamars-verse-modal" class="katamars-modal" style="display:none;">
            <div class="modal-overlay"></div>
            <div class="modal-content">
                <button class="modal-close">&times;</button>
                <div class="modal-header">
                    <h3 id="modal-title"></h3>
                </div>
                <div class="modal-body" id="modal-verses">
                    <div class="loading">جاري التحميل...</div>
                </div>
                <div class="modal-footer">
                    <button id="modal-copy-btn" class="btn-copy">📋 نسخ</button>
                    <a id="modal-chapter-link" href="#" class="btn-chapter" target="_blank">📖 اقرأ الإصحاح كاملاً</a>
                </div>
            </div>
        </div>

        <?php
        echo $args['after_widget'];
    }

    /**
     * Render reading items as clickable links
     */
    private function render_reading_items( $readings ) {
        $output = '';
        
        foreach ( $readings as $reading ) {
            $reference = $reading['reference'];
            $label = $reading['label'];
            
            // Translate reference to Arabic
            $arabic_reference = $this->translate_reference( $reference );
            
            $output .= sprintf(
                '<li><a href="#" class="reading-link" data-reference="%s" data-label="%s">%s</a></li>',
                esc_attr( $reference ),
                esc_attr( $label ),
                esc_html( $label . ': ' . $arabic_reference )
            );
        }
        
        return $output;
    }

    /**
     * Translate English book names to Arabic
     */
    private function translate_reference( $reference ) {
        $book_translations = array(
            'Genesis' => 'التكوين',
            'Exodus' => 'الخروج',
            'Leviticus' => 'اللاويين',
            'Numbers' => 'العدد',
            'Deuteronomy' => 'التثنية',
            'Joshua' => 'يشوع',
            'Judges' => 'القضاة',
            'Ruth' => 'راعوث',
            '1 Samuel' => '1 صموئيل',
            '2 Samuel' => '2 صموئيل',
            '1 Kings' => '1 ملوك',
            '2 Kings' => '2 ملوك',
            '1 Chronicles' => '1 أخبار',
            '2 Chronicles' => '2 أخبار',
            'Ezra' => 'عزرا',
            'Nehemiah' => 'نحميا',
            'Esther' => 'أستير',
            'Job' => 'أيوب',
            'Psalms' => 'المزامير',
            'Proverbs' => 'الأمثال',
            'Ecclesiastes' => 'الجامعة',
            'Song of Solomon' => 'نشيد الأنشاد',
            'Isaiah' => 'إشعياء',
            'Jeremiah' => 'إرميا',
            'Lamentations' => 'مراثي إرميا',
            'Ezekiel' => 'حزقيال',
            'Daniel' => 'دانيال',
            'Hosea' => 'هوشع',
            'Joel' => 'يوئيل',
            'Amos' => 'عاموس',
            'Obadiah' => 'عوبديا',
            'Jonah' => 'يونان',
            'Micah' => 'ميخا',
            'Nahum' => 'ناحوم',
            'Habakkuk' => 'حبقوق',
            'Zephaniah' => 'صفنيا',
            'Haggai' => 'حجي',
            'Zechariah' => 'زكريا',
            'Malachi' => 'ملاخي',
            'Matthew' => 'متى',
            'Mark' => 'مرقس',
            'Luke' => 'لوقا',
            'John' => 'يوحنا',
            'Acts' => 'أعمال الرسل',
            'Romans' => 'رومية',
            '1 Corinthians' => '1 كورنثوس',
            '2 Corinthians' => '2 كورنثوس',
            'Galatians' => 'غلاطية',
            'Ephesians' => 'أفسس',
            'Philippians' => 'فيلبي',
            'Colossians' => 'كولوسي',
            '1 Thessalonians' => '1 تسالونيكي',
            '2 Thessalonians' => '2 تسالونيكي',
            '1 Timothy' => '1 تيموثاوس',
            '2 Timothy' => '2 تيموثاوس',
            'Titus' => 'تيطس',
            'Philemon' => 'فليمون',
            'Hebrews' => 'العبرانيين',
            'James' => 'يعقوب',
            '1 Peter' => '1 بطرس',
            '2 Peter' => '2 بطرس',
            '1 John' => '1 يوحنا',
            '2 John' => '2 يوحنا',
            '3 John' => '3 يوحنا',
            'Jude' => 'يهوذا',
            'Revelation' => 'الرؤيا'
        );
        
        $translated = $reference;
        foreach ( $book_translations as $english => $arabic ) {
            if ( strpos( $reference, $english ) === 0 ) {
                $translated = str_replace( $english, $arabic, $reference );
                break;
            }
        }
        
        return $translated;
    }

    /**
     * Get today's readings from database
     */
    private function get_today_readings() {
        // Get today's Coptic date
        if ( ! class_exists( 'Katamars_Date' ) ) {
            require_once plugin_dir_path( __FILE__ ) . 'class-katamars-date.php';
        }
        
        $date_calc = new Katamars_Date();
        $coptic_date = $date_calc->get_coptic_date( time() );

        // Get readings using Query class
        if ( ! class_exists( 'Katamars_Query' ) ) {
            require_once plugin_dir_path( __FILE__ ) . 'class-katamars-query.php';
        }
        
        $query = new Katamars_Query();
        $readings = $query->get_readings( $coptic_date, 'ar' );

        if ( empty( $readings ) ) {
            return array();
        }

        // Organize readings by prayer type
        $organized = array(
            'title' => $this->get_reading_title( $coptic_date, $readings ),
            'vespers' => array(),
            'matins' => array(),
            'liturgy' => array(),
            'synaxarium' => ''
        );

        // Vespers
        if ( ! empty( $readings['vespers_psalm'] ) ) {
            $organized['vespers'][] = array(
                'reference' => $readings['vespers_psalm'][0]['reference'],
                'label' => 'مزمور العشية'
            );
        }
        if ( ! empty( $readings['vespers_gospel'] ) ) {
            $organized['vespers'][] = array(
                'reference' => $readings['vespers_gospel'][0]['reference'],
                'label' => 'إنجيل العشية'
            );
        }

        // Matins
        if ( ! empty( $readings['matins_psalm'] ) ) {
            $organized['matins'][] = array(
                'reference' => $readings['matins_psalm'][0]['reference'],
                'label' => 'مزمور باكر'
            );
        }
        if ( ! empty( $readings['matins_gospel'] ) ) {
            $organized['matins'][] = array(
                'reference' => $readings['matins_gospel'][0]['reference'],
                'label' => 'إنجيل باكر'
            );
        }

        // Liturgy
        if ( ! empty( $readings['pauline_epistle'] ) ) {
            $organized['liturgy'][] = array(
                'reference' => $readings['pauline_epistle'][0]['reference'],
                'label' => 'البولس'
            );
        }
        if ( ! empty( $readings['catholic_epistle'] ) ) {
            $organized['liturgy'][] = array(
                'reference' => $readings['catholic_epistle'][0]['reference'],
                'label' => 'الكاثوليكون'
            );
        }
        if ( ! empty( $readings['acts'] ) ) {
            $organized['liturgy'][] = array(
                'reference' => $readings['acts'][0]['reference'],
                'label' => 'الإبركسيس'
            );
        }
        if ( ! empty( $readings['liturgy_psalm'] ) ) {
            $organized['liturgy'][] = array(
                'reference' => $readings['liturgy_psalm'][0]['reference'],
                'label' => 'مزمور القداس'
            );
        }
        if ( ! empty( $readings['liturgy_gospel'] ) ) {
            $organized['liturgy'][] = array(
                'reference' => $readings['liturgy_gospel'][0]['reference'],
                'label' => 'إنجيل القداس'
            );
        }

        // Synaxarium
        $organized['synaxarium'] = $this->get_synaxarium( $coptic_date );

        return $organized;
    }

    /**
     * Get reading title
     */
    private function get_reading_title( $coptic_date, $readings ) {
        $title = 'قراءات اليوم';
        
        if ( ! empty( $readings['day_name'] ) ) {
            $title = $readings['day_name'];
        } elseif ( ! empty( $coptic_date['formatted'] ) ) {
            $title = 'قراءات ' . $coptic_date['formatted'];
        }
        
        return $title;
    }

    /**
     * Get Synaxarium for today
     */
    private function get_synaxarium( $coptic_date ) {
        $month = $coptic_date['coptic_month'];
        $day = $coptic_date['coptic_day'];
        
        // Synaxarium file path
        $synax_file = plugin_dir_path( dirname( __FILE__ ) ) . 'data/synaxarium/' . $month . '/' . $day . '.html';
        
        if ( file_exists( $synax_file ) ) {
            $content = file_get_contents( $synax_file );
            // Extract summary (first 200 chars)
            $summary = wp_trim_words( strip_tags( $content ), 30, '...' );
            return $summary;
        }
        
        return '';
    }

    /**
     * Widget form (admin)
     */
    public function form( $instance ) {
        ?>
        <p>
            <em>هذا الـ Widget يعرض قراءات اليوم الحالي تلقائياً</em>
        </p>
        <?php
    }

    /**
     * Update widget settings
     */
    public function update( $new_instance, $old_instance ) {
        return $new_instance;
    }
}

} // End class_exists check
