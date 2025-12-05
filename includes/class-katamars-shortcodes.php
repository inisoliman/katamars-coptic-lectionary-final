<?php
/**
 * Shortcode functionality.
 */
if ( ! class_exists( 'Katamars_Shortcodes' ) ) {
    
class Katamars_Shortcodes {

    /**
     * Register shortcodes.
     */
    public function register_shortcodes() {
        add_shortcode( 'katamars_today', array( $this, 'render_today_readings' ) );
        add_shortcode( 'katamars_synaxarium', array( $this, 'render_synaxarium_only' ) );
        add_shortcode( 'katamars_bible', array( $this, 'render_bible_browser' ) );
        add_action( 'wp_head', array( $this, 'add_seo_meta_tags' ) );

        // Enqueue Scripts and Styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // SEO: Dynamic Page Titles
        add_filter( 'pre_get_document_title', array( $this, 'katamars_dynamic_title' ), 999 );
        add_filter( 'rank_math/frontend/title', array( $this, 'katamars_dynamic_title' ), 999 );
    }

    /**
     * Enqueue styles and scripts.
     */
    public function enqueue_assets() {
        // Enqueue Core Styles (Always loaded)
        wp_enqueue_style( 'katamars-core-css', KATAMARS_PLUGIN_URL . 'assets/css/katamars-core.css', array(), '2.0.3' );
        wp_enqueue_script( 'katamars-core-js', KATAMARS_PLUGIN_URL . 'assets/js/katamars-core.js', array( 'jquery' ), '2.0.3', true );

        // Conditional Loading
        global $post;
        if ( is_a( $post, 'WP_Post' ) ) {
            // Check for Bible Shortcode
            if ( has_shortcode( $post->post_content, 'katamars_bible' ) || get_query_var( 'katamars_bible_book' ) ) {
                wp_enqueue_style( 'katamars-bible-css', KATAMARS_PLUGIN_URL . 'assets/css/katamars-bible.css', array( 'katamars-core-css' ), '2.0.4' );
                wp_enqueue_script( 'katamars-bible-js', KATAMARS_PLUGIN_URL . 'assets/js/katamars-bible.js', array( 'katamars-core-js' ), '2.0.4', true );
            }

            // Check for Readings Shortcode
            if ( has_shortcode( $post->post_content, 'katamars_today' ) ) {
                wp_enqueue_style( 'katamars-readings-css', KATAMARS_PLUGIN_URL . 'assets/css/katamars-readings.css', array( 'katamars-core-css' ), '1.2.0' );
                wp_enqueue_script( 'katamars-readings-js', KATAMARS_PLUGIN_URL . 'assets/js/katamars-readings.js', array( 'katamars-core-js' ), '1.2.0', true );
            }
        }
    }

    /**
     * Helper to get current language from URL or Shortcode.
     */
    private function get_current_lang( $atts ) {
        if ( isset( $_GET['lang'] ) && in_array( $_GET['lang'], array( 'ar', 'en' ) ) ) {
            return sanitize_text_field( $_GET['lang'] );
        }
        return isset( $atts['lang'] ) ? $atts['lang'] : 'ar';
    }

    /**
     * Get the target timestamp based on request or current time.
     */
    private function get_target_timestamp() {
        $current_timestamp = current_time( 'timestamp' );
        
        // Check query var (Rewrite Rules)
        if ( get_query_var( 'katamars_date' ) ) {
            $selected_date = sanitize_text_field( get_query_var( 'katamars_date' ) );
            $current_timestamp = strtotime( $selected_date );
        }
        // Check GET parameter (Fallback or direct access)
        elseif ( isset( $_GET['katamars_date'] ) && ! empty( $_GET['katamars_date'] ) ) {
            $selected_date = sanitize_text_field( $_GET['katamars_date'] );
            $current_timestamp = strtotime( $selected_date );
        }
        
        return $current_timestamp;
    }

    /**
     * Add SEO Meta Tags to wp_head.
     */
    public function add_seo_meta_tags() {
        // Only add if we are on a page/post (basic check)
        if ( ! is_singular() ) {
            return;
        }

        // Bible Browser SEO
        $bible_book = get_query_var( 'katamars_bible_book' );
        if ( ! empty( $bible_book ) ) {
            $chapter = get_query_var( 'katamars_bible_chapter' );
            
            // Get Arabic Name
            $arabic_name = Katamars_Bible::get_arabic_book_name($bible_book);
            $book_name = $arabic_name ? $arabic_name : ucwords( str_replace( '-', ' ', $bible_book ) );
            
            if ( ! empty( $chapter ) ) {
                $title = "$book_name $chapter - الكتاب المقدس - القطمارس";
                $description = "قراءة الإصحاح $chapter من سفر $book_name. النص الكامل مع التفسير والاستماع.";
            } else {
                $title = "سفر $book_name - الكتاب المقدس - القطمارس";
                $description = "تصفح إصحاحات سفر $book_name من الكتاب المقدس. قراءة واستماع وتفسير.";
            }
            
            echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
            echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
            echo '<meta property="og:description" content="' . esc_attr( $description ) . '" />' . "\n";
            echo '<meta property="og:type" content="book" />' . "\n";
            echo '<meta property="og:url" content="' . esc_url( get_permalink() ) . '" />' . "\n";
            
        // Hook into Rank Math for Canonical and OG URL
        add_filter( 'rank_math/frontend/canonical', array( $this, 'katamars_seo_url' ) );
        add_filter( 'rank_math/opengraph/url', array( $this, 'katamars_seo_url' ) );
    }
    }

    /**
     * Filter Rank Math Canonical and OG URL.
     */
    public function katamars_seo_url( $url ) {
        // Bible Browser
        if ( get_query_var( 'katamars_bible_book' ) ) {
            $book = get_query_var( 'katamars_bible_book' );
            $chapter = get_query_var( 'katamars_bible_chapter' );
            $new_url = home_url( '/katamars_bible/' . $book );
            if ( $chapter ) {
                $new_url .= '/' . $chapter;
            }
            return $new_url;
        }

        // Daily Readings
        if ( isset( $_GET['katamars_date'] ) ) {
            $date = sanitize_text_field( $_GET['katamars_date'] );
            return home_url( '/readings/' . $date );
        }
        
        // Check rewrite rule match
        if ( get_query_var( 'katamars_date' ) ) {
             return home_url( '/readings/' . get_query_var( 'katamars_date' ) );
        }

        return $url;
    }

    /**
     * Generate Dynamic SEO Title.
     */
    public function katamars_dynamic_title( $title ) {
        // ... (Keep existing logic, but ensure it runs)
        // 1. Bible Browser Title
        $book_slug = get_query_var( 'katamars_bible_book' );
        $chapter = get_query_var( 'katamars_bible_chapter' );
        
        if ( $book_slug ) {
            $lang = isset( $_GET['lang'] ) ? sanitize_text_field( $_GET['lang'] ) : 'ar';
            $book_name = Katamars_Bible::get_arabic_book_name( $book_slug );
            
            if ( ! $book_name ) {
                $book_name = ucwords( str_replace( '-', ' ', $book_slug ) );
            }

            if ( $chapter ) {
                return ($lang == 'ar') 
                    ? "$book_name - إصحاح $chapter - الكتاب المقدس" 
                    : "$book_name - Chapter $chapter - Holy Bible";
            } else {
                return ($lang == 'ar') 
                    ? "$book_name - الكتاب المقدس" 
                    : "$book_name - Holy Bible";
            }
        }

        // 2. Daily Readings Title
        // Check both GET param and Query Var
        $date_str = '';
        if ( isset( $_GET['katamars_date'] ) ) {
            $date_str = sanitize_text_field( $_GET['katamars_date'] );
        } elseif ( get_query_var( 'katamars_date' ) ) {
            $date_str = get_query_var( 'katamars_date' );
        }

        if ( $date_str ) {
            $lang = isset( $_GET['lang'] ) ? sanitize_text_field( $_GET['lang'] ) : 'ar';
            
            if ( ! class_exists( 'Katamars_Date' ) ) {
                require_once KATAMARS_PLUGIN_DIR . 'includes/class-katamars-date.php';
            }
            $date_calculator = new Katamars_Date();
            $coptic_date = $date_calculator->get_coptic_date( strtotime( $date_str ) );
            
            // Format Coptic Date String
            $month_names_ar = array(
                1 => 'توت', 2 => 'بابة', 3 => 'هاتور', 4 => 'كيهك', 5 => 'طوبة', 6 => 'أمشير',
                7 => 'برمهات', 8 => 'برمودة', 9 => 'بشنس', 10 => 'بؤونة', 11 => 'أبيب', 12 => 'مسرى', 13 => 'النسيء'
            );
            $coptic_formatted = $coptic_date['coptic_day'] . ' ' . $month_names_ar[ $coptic_date['coptic_month'] ] . ' ' . $coptic_date['coptic_year'];

            if ( $lang == 'ar' ) {
                return "قطمارس " . $coptic_formatted . " - " . date( 'd F Y', strtotime( $date_str ) );
            } else {
                return "Katamars " . $coptic_formatted . " - " . date( 'd F Y', strtotime( $date_str ) );
            }
        }

        return $title;
    }

    /**
     * Get Liturgical Intro and Conclusion.
     */
    private function get_liturgical_text( $type, $reference, $is_arabic ) {
        if ( ! $is_arabic ) {
            return array( 'intro' => '', 'conclusion' => '' );
        }

        $book_name = explode( ' ', $reference )[0]; // Rough extraction
        // Refine book name extraction for Arabic mapping
        if ( strpos( $reference, 'Matthew' ) !== false ) $evangelist = 'متى';
        elseif ( strpos( $reference, 'Mark' ) !== false ) $evangelist = 'مرقس';
        elseif ( strpos( $reference, 'Luke' ) !== false ) $evangelist = 'لوقا';
        elseif ( strpos( $reference, 'John' ) !== false ) $evangelist = 'يوحنا';
        else $evangelist = '';

        $intro = '';
        $conclusion = '';

        switch ( $type ) {
            case 'vespers_psalm':
            case 'matins_psalm':
            case 'liturgy_psalm':
                $intro = 'من مزامير وتراتيل أبينا داود النبي . بركاته علينا، آمين.';
                $conclusion = 'طوبى لمن يأتي باسم الرب. ربنا والهنا ، ومخلصنا وملكنا جميعاً ، يسوع المسيح ، ابن الله الحي الذي له المجد الدائم إلى الأبد. آمين.';
                break;

            case 'vespers_gospel':
            case 'matins_gospel':
            case 'liturgy_gospel':
                $intro = "قفوا بخوف أمام الله، وانصتوا لسماع الإنجيل المقدس.\nفصل شريف من بشارة معلمنا $evangelist الإنجيلي.\nبركته تكون مع جميعنا، آمين.";
                $conclusion = 'والمجد لله دائماً.';
                break;

            case 'pauline_epistle':
                $intro = "بولس، عبد يسوع المسيح، المدعوّ رسولاً، المُفرَز لإنجيل الله.\nالبولس، فصل من رسالة القديس بولس الرسول إلى $book_name.\nبركته تكون مع جميعنا، آمين.";
                $conclusion = 'نعمة ربنا يسوع المسيح فلتكن معكم ومعي، يا آبائي وأخوتي، آمين.';
                break;

            case 'catholic_epistle':
                $intro = "الكاثوليكون، فصل من رسالة معلمنا $book_name.\nبركته تكون مع جميعنا، آمين.";
                $conclusion = 'لا تحبوا العالم ولا الأشياء التي في العالم؛ لأن العالم يمضي وشهوته. أما الذي يصنع إرادة الله فيثبت إلى الأبد. آمين.';
                break;

            case 'acts':
                $intro = "الإبركسيس، فصل من اعمال آبائنأ الرسل الأطهار الحواريين المشمولين بنعمة الروح القدس،،\nبركتهم تكون معنا. آمين.";
                $conclusion = 'لم تزل كلمة الرب تنمو وتعتز وتثبت في كنيسة الله المقدسة. آمين.';
                break;
        }

        return array( 'intro' => nl2br( $intro ), 'conclusion' => $conclusion );
    }

    /**
     * Render today's readings shortcode.
     */
    public function render_today_readings( $atts ) {
        $atts = shortcode_atts( array(
            'lang' => 'ar', // Default fallback
        ), $atts );

        // Determine Language
        $lang = $this->get_current_lang( $atts );
        $is_arabic = ( $lang === 'ar' );
        $dir = $is_arabic ? 'rtl' : 'ltr';

        $current_timestamp = $this->get_target_timestamp();

        // Get Coptic date
        $date_calculator = new Katamars_Date();
        $coptic_date = $date_calculator->get_coptic_date( $current_timestamp );
        
        // Format date based on language
        if ( $is_arabic ) {
            $month_names_ar = array(
                1 => 'توت', 2 => 'بابة', 3 => 'هاتور', 4 => 'كيهك', 5 => 'طوبة', 6 => 'أمشير',
                7 => 'برمهات', 8 => 'برمودة', 9 => 'بشنس', 10 => 'بؤونة', 11 => 'أبيب', 12 => 'مسرى', 13 => 'النسيء'
            );
            $coptic_date_str = $coptic_date['coptic_day'] . ' ' . $month_names_ar[ $coptic_date['coptic_month'] ] . ' ' . $coptic_date['coptic_year'];
            
            // Arabic Gregorian Date
            $months_ar = array(
                'January' => 'يناير', 'February' => 'فبراير', 'March' => 'مارس', 'April' => 'أبريل',
                'May' => 'مايو', 'June' => 'يونيو', 'July' => 'يوليو', 'August' => 'أغسطس',
                'September' => 'سبتمبر', 'October' => 'أكتوبر', 'November' => 'نوفمبر', 'December' => 'ديسمبر'
            );
            $day_names_ar = array(
                'Sunday' => 'الأحد', 'Monday' => 'الاثنين', 'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء',
                'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت'
            );
            $day_name = $day_names_ar[ date( 'l', $current_timestamp ) ];
            $greg_date_str = $day_name . ' ' . date( 'j', $current_timestamp ) . ' ' . $months_ar[ date( 'F', $current_timestamp ) ] . ' ' . date( 'Y', $current_timestamp );
        } else {
            $coptic_date_str = $coptic_date['formatted'];
            $greg_date_str = date( 'l, F j, Y', $current_timestamp );
        }

        // Get readings
        $query = new Katamars_Query();
        $readings = $query->get_readings( $coptic_date, $lang );
        
        // Get Synaxarium
        $synaxarium = new Katamars_Synaxarium();
        $synax_content = $synaxarium->get_synaxarium( $coptic_date, $lang );

        // Translations
        $translations = array(
            'Daily Readings' => 'القراءات اليومية',
            'Synaxarium' => 'السنكسار',
            'vespers_psalm' => 'مزمور العشية',
            'vespers_gospel' => 'إنجيل العشية',
            'matins_psalm' => 'مزمور باكر',
            'matins_gospel' => 'إنجيل باكر',
            'pauline_epistle' => 'البولس',
            'catholic_epistle' => 'الكاثوليكون',
            'acts' => 'الإبركسيس',
            'liturgy_psalm' => 'مزمور القداس',
            'liturgy_gospel' => 'إنجيل القداس',
            'prophecy' => 'النبوة',
        );

        // Book Name Translations
        $book_translations = array(
            'Psalms' => 'المزامير',
            'Matthew' => 'إنجيل متى',
            'Mark' => 'إنجيل مرقس',
            'Luke' => 'إنجيل لوقا',
            'John' => 'إنجيل يوحنا',
            'Acts' => 'أعمال الرسل',
            'Romans' => 'رومية',
            '1 Corinthians' => 'كورنثوس الأولى',
            '2 Corinthians' => 'كورنثوس الثانية',
            'Galatians' => 'غلاطية',
            'Ephesians' => 'أفسس',
            'Philippians' => 'فيلبي',
            'Colossians' => 'كولوسي',
            '1 Thessalonians' => 'تسالونيكي الأولى',
            '2 Thessalonians' => 'تسالونيكي الثانية',
            '1 Timothy' => 'تيموثاوس الأولى',
            '2 Timothy' => 'تيموثاوس الثانية',
            'Titus' => 'تيطس',
            'Philemon' => 'فليمون',
            'Hebrews' => 'العبرانيين',
            'James' => 'يعقوب',
            '1 Peter' => 'بطرس الأولى',
            '2 Peter' => 'بطرس الثانية',
            '1 John' => 'يوحنا الأولى',
            '2 John' => 'يوحنا الثانية',
            '3 John' => 'يوحنا الثالثة',
            'Jude' => 'يهوذا',
            'Revelation' => 'سفر الرؤيا',
            'Genesis' => 'التكوين',
            'Exodus' => 'الخروج',
            'Leviticus' => 'اللاويين',
            'Numbers' => 'العدد',
            'Deuteronomy' => 'التثنية',
            'Isaiah' => 'إشعياء',
            'Jeremiah' => 'إرميا',
            'Ezekiel' => 'حزقيال',
            'Daniel' => 'دانيال',
            'Joel' => 'يوئيل',
            'Amos' => 'عاموس',
            'Jonah' => 'يونان',
            'Micah' => 'ميخا',
            'Zechariah' => 'زكريا',
            'Malachi' => 'ملاخي',
            'Proverbs' => 'الأمثال',
            'Wisdom' => 'الحكمة',
            'Sirach' => 'يشوع بن سيراخ',
            'Job' => 'أيوب',
        );

        // JSON-LD Schema
        $schema_data = array(
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => "القراءات اليومية القبطية - $coptic_date_str",
            'description' => "قراءات القطمارس للكنيسة القبطية الأرثوذكسية ليوم $coptic_date_str.",
            'datePublished' => date( 'Y-m-d', $current_timestamp ),
            'inLanguage' => $is_arabic ? 'ar' : 'en',
            'mainEntity' => array(
                '@type' => 'Article',
                'headline' => "قراءات $coptic_date_str",
                'articleBody' => 'Daily readings content including Psalms, Gospels, and Synaxarium.',
            )
        );

        // Build output
        ob_start();
        ?>
        <!-- JSON-LD Schema for AI and SEO -->
        <script type="application/ld+json">
        <?php echo json_encode( $schema_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ); ?>
        </script>

        <!-- Floating Navigation -->
        <div class="katamars-floating-nav">
            <a href="#section-vespers" class="katamars-nav-item" title="<?php echo $is_arabic ? 'العشية' : 'Vespers'; ?>">
                <span class="icon">🌅</span>
                <span class="nav-tooltip"><?php echo $is_arabic ? 'العشية' : 'Vespers'; ?></span>
            </a>
            <a href="#section-matins" class="katamars-nav-item" title="<?php echo $is_arabic ? 'باكر' : 'Matins'; ?>">
                <span class="icon">🌄</span>
                <span class="nav-tooltip"><?php echo $is_arabic ? 'باكر' : 'Matins'; ?></span>
            </a>
            <a href="#section-liturgy" class="katamars-nav-item" title="<?php echo $is_arabic ? 'القداس' : 'Liturgy'; ?>">
                <span class="icon">✝️</span>
                <span class="nav-tooltip"><?php echo $is_arabic ? 'القداس' : 'Liturgy'; ?></span>
            </a>
            <?php if ( ! empty( $synax_content ) ) : ?>
            <a href="#section-synaxarium" class="katamars-nav-item" title="<?php echo $is_arabic ? 'السنكسار' : 'Synaxarium'; ?>">
                <span class="icon">📖</span>
                <span class="nav-tooltip"><?php echo $is_arabic ? 'السنكسار' : 'Synaxarium'; ?></span>
            </a>
            <?php endif; ?>
        </div>

        <div class="katamars-container" dir="<?php echo esc_attr( $dir ); ?>" lang="<?php echo esc_attr( $lang ); ?>">
            
            <!-- Date Picker Form -->
            <!-- Date Picker Form -->
            <!-- Date Picker Form -->
            <form method="get" class="katamars-date-form" onsubmit="event.preventDefault(); var date = document.getElementById('katamars_date').value; if(date) { var url = '<?php echo home_url('/readings/'); ?>' + date; <?php if($lang !== 'ar') echo "url += '?lang=$lang';"; ?> window.location.href = url; }">
                <label for="katamars_date"><?php echo $is_arabic ? 'اختر تاريخ:' : 'Select Date:'; ?></label>
                <input type="date" id="katamars_date" name="katamars_date" value="<?php echo date( 'Y-m-d', $current_timestamp ); ?>">
                <button type="submit"><?php echo $is_arabic ? 'عرض' : 'Show'; ?></button>
            </form>

            <?php
            // Navigation Dates
            $prev_date = date( 'Y-m-d', strtotime( '-1 day', $current_timestamp ) );
            $next_date = date( 'Y-m-d', strtotime( '+1 day', $current_timestamp ) );
            
            // SEO Friendly URLs
            $prev_url = home_url( '/readings/' . $prev_date );
            $next_url = home_url( '/readings/' . $next_date );
            
            if ( $lang !== 'ar' ) {
                $prev_url = add_query_arg( 'lang', $lang, $prev_url );
                $next_url = add_query_arg( 'lang', $lang, $next_url );
            }
            ?>

            <!-- Controls -->
            <div class="katamars-controls">
                <a href="<?php echo esc_url( $prev_url ); ?>" class="katamars-btn katamars-nav-link"><?php echo $is_arabic ? 'اليوم السابق' : 'Previous Day'; ?></a>
                
                <button id="katamars-audio-btn" class="katamars-btn action-btn">
                    <span class="icon">🔊</span> <?php echo $is_arabic ? 'استماع' : 'Listen'; ?>
                </button>

                <button id="katamars-copy-all-btn" class="katamars-btn action-btn">
                    <span class="icon">📋</span> <?php echo $is_arabic ? 'نسخ الكل' : 'Copy All'; ?>
                </button>

                <button id="katamars-lang-btn" class="katamars-btn action-btn">
                    <span class="icon">🌐</span> <?php echo $is_arabic ? 'English' : 'عربي'; ?>
                </button>
                
                <a href="<?php echo esc_url( $next_url ); ?>" class="katamars-btn katamars-nav-link"><?php echo $is_arabic ? 'اليوم التالي' : 'Next Day'; ?></a>
            </div>

            <div class="katamars-header">
                <h2><?php echo esc_html( $greg_date_str ); ?></h2>
                <p class="katamars-gregorian"><?php echo esc_html( $coptic_date_str ); ?></p>
            </div>

            <?php if ( ! empty( $coptic_date['feast_name'] ) ) : ?>
                <div class="katamars-feast">
                    <h3><?php echo esc_html( $coptic_date['feast_name'] ); ?></h3>
                </div>
            <?php endif; ?>

            <div class="katamars-readings">
                
                <?php 
                // Extract metadata
                $day_name = isset( $readings['day_name'] ) ? $readings['day_name'] : '';
                $season = isset( $readings['season'] ) ? $readings['season'] : '';
                $day_tune = isset( $readings['day_tune'] ) ? $readings['day_tune'] : '';

                // Display Occasion/Feast Name
                if ( ! empty( $day_name ) ) {
                    echo '<div class="katamars-occasion" style="text-align:center; margin-bottom:20px;">';
                    echo '<h3 style="color:#8b0000;">' . esc_html( $day_name ) . '</h3>';
                    if ( ! empty( $season ) ) {
                        echo '<span class="katamars-season" style="font-weight:bold;">' . esc_html( $season ) . '</span>';
                    }
                    if ( ! empty( $day_tune ) ) {
                        echo ' | <span class="katamars-tune">' . esc_html( $day_tune ) . '</span>';
                    }
                    echo '</div>';
                }

                $current_service = '';
                foreach ( $readings as $reading_type => $reading_data ) : 
                    // Skip metadata keys
                    if ( in_array( $reading_type, array( 'day_name', 'season', 'day_tune' ) ) ) {
                        continue;
                    }

                    if ( empty( $reading_data ) ) continue;

                    // Determine Service Header and ID
                    $service_header = '';
                    $section_id = '';
                    if ( strpos( $reading_type, 'vespers' ) !== false ) {
                        $service_header = 'العشية';
                        $section_id = 'section-vespers';
                    } elseif ( strpos( $reading_type, 'matins' ) !== false ) {
                        $service_header = 'باكر';
                        $section_id = 'section-matins';
                    } elseif ( in_array( $reading_type, array( 'pauline_epistle', 'catholic_epistle', 'acts', 'liturgy_psalm', 'liturgy_gospel' ) ) ) {
                        $service_header = 'القداس الإلهي';
                        $section_id = 'section-liturgy';
                    }

                    // Show Service Header if changed
                    if ( $service_header && $service_header !== $current_service ) {
                        // Close previous section div if needed (not strictly needed as we use classes)
                        // Actually, we need to wrap sections for IDs to work well with scrollspy? 
                        // Or just add ID to the first element of the section.
                        // Let's add ID to the header or a wrapper.
                        
                        // To avoid complex nesting logic in loop, we'll just add an anchor div
                        if ( $section_id ) {
                            echo '<div id="' . esc_attr( $section_id ) . '" class="katamars-section-anchor"></div>';
                        }
                        
                        if ( $is_arabic ) {
                            echo '<h3 class="katamars-service-header">' . esc_html( $service_header ) . '</h3>';
                        } else {
                             // English Headers
                             $en_header = ($service_header == 'العشية') ? 'Vespers' : (($service_header == 'باكر') ? 'Matins' : 'Divine Liturgy');
                             echo '<h3 class="katamars-service-header">' . esc_html( $en_header ) . '</h3>';
                        }
                        $current_service = $service_header;
                    }
                ?>
                    <div class="katamars-reading-section">
                        <h4>
                            <?php 
                            if ( $is_arabic && isset( $translations[ $reading_type ] ) ) {
                                echo esc_html( $translations[ $reading_type ] );
                            } else {
                                echo esc_html( ucfirst( str_replace( '_', ' ', $reading_type ) ) );
                            }
                            ?>
                        </h4>
                        
                        <?php foreach ( $reading_data as $reading ) : 
                            $liturgy = $this->get_liturgical_text( $reading_type, $reading['reference'], $is_arabic );
                        ?>
                            <div class="katamars-reading">
                                <?php if ( $liturgy['intro'] ) : ?>
                                    <div class="katamars-intro"><?php echo $liturgy['intro']; ?></div>
                                <?php endif; ?>

                                <p class="katamars-reference">
                                    <?php 
                                    $ref_display = $reading['reference'];
                                    if ( $is_arabic ) {
                                        foreach ( $book_translations as $en => $ar ) {
                                            $ref_display = str_replace( $en, $ar, $ref_display );
                                        }
                                    }
                                    // Link the reference
                                    echo $this->link_bible_reference_string( $reading['reference'], $ref_display, $is_arabic ); 
                                    ?>
                                </p>
                                <div class="katamars-text"><?php echo wp_kses_post( $reading['text'] ); ?></div>
                                
                                <?php if ( $liturgy['conclusion'] ) : ?>
                                    <div class="katamars-conclusion"><?php echo esc_html( $liturgy['conclusion'] ); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ( ! empty( $synax_content ) ) : ?>
                <div id="section-synaxarium" class="katamars-synaxarium">
                    <h3><?php echo $is_arabic ? $translations['Synaxarium'] : 'Synaxarium'; ?></h3>
                    <div class="katamars-synax-content">
                        <?php echo wp_kses_post( $synax_content ); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Helper to link a reference string to the Bible Browser.
     * 
     * @param string $ref_raw The raw reference (e.g. "Psalms 40:9,2")
     * @param string $ref_display The display text (e.g. "المزامير 40:9,2")
     * @param bool $is_arabic
     * @return string HTML link
     */
    private function link_bible_reference_string( $ref_raw, $ref_display, $is_arabic ) {
        // Parse Book and Chapter
        // Formats: "Book Chapter:Verse-Verse" or "1 Book Chapter:Verse"
        
        // 1. Extract Book Name
        // We need a list of English book names to match against $ref_raw
        $book_slug = '';
        $chapter = '';
        
        // Simple regex to capture Book and Chapter
        // Matches "1 John 3:..." or "John 3:..."
        if ( preg_match( '/^((?:\d\s)?[a-zA-Z\s]+)\s(\d+):/', $ref_raw, $matches ) ) {
            $book_name_en = trim( $matches[1] );
            $chapter = $matches[2];
            $book_slug = sanitize_title( $book_name_en );
        }
        
        if ( $book_slug && $chapter ) {
            $url = home_url( '/katamars_bible/' . $book_slug . '/' . $chapter );
            // Add lang param if needed
            if ( ! $is_arabic ) {
                $url = add_query_arg( 'lang', 'en', $url );
            }
            
            return '<a href="' . esc_url( $url ) . '" class="katamars-ref-link" title="' . ( $is_arabic ? 'اقرأ الإصحاح كاملاً' : 'Read full chapter' ) . '">' . esc_html( $ref_display ) . ' <span class="icon">🔗</span></a>';
        }
        
        return esc_html( $ref_display );
    }


    /**
     * Render Synaxarium only shortcode.
     */
    public function render_synaxarium_only( $atts ) {
        $atts = shortcode_atts( array(
            'lang' => 'ar', // Default to Arabic
        ), $atts );

        $is_arabic = ( $atts['lang'] === 'ar' );
        $dir = $is_arabic ? 'rtl' : 'ltr';

        $current_timestamp = $this->get_target_timestamp();

        // Get Coptic date
        $date_calculator = new Katamars_Date();
        $coptic_date = $date_calculator->get_coptic_date( $current_timestamp );
        
        // Format date based on language
        if ( $is_arabic ) {
            $month_names_ar = array(
                1 => 'توت', 2 => 'بابة', 3 => 'هاتور', 4 => 'كيهك', 5 => 'طوبة', 6 => 'أمشير',
                7 => 'برمهات', 8 => 'برمودة', 9 => 'بشنس', 10 => 'بؤونة', 11 => 'أبيب', 12 => 'مسرى', 13 => 'النسيء'
            );
            $coptic_date_str = $coptic_date['coptic_day'] . ' ' . $month_names_ar[ $coptic_date['coptic_month'] ] . ' ' . $coptic_date['coptic_year'];
            
            // Arabic Gregorian Date
            $months_ar = array(
                'January' => 'يناير', 'February' => 'فبراير', 'March' => 'مارس', 'April' => 'أبريل',
                'May' => 'مايو', 'June' => 'يونيو', 'July' => 'يوليو', 'August' => 'أغسطس',
                'September' => 'سبتمبر', 'October' => 'أكتوبر', 'November' => 'نوفمبر', 'December' => 'ديسمبر'
            );
            $day_names_ar = array(
                'Sunday' => 'الأحد', 'Monday' => 'الاثنين', 'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء',
                'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت'
            );
            $day_name = $day_names_ar[ date( 'l', $current_timestamp ) ];
            $greg_date_str = $day_name . ' ' . date( 'j', $current_timestamp ) . ' ' . $months_ar[ date( 'F', $current_timestamp ) ] . ' ' . date( 'Y', $current_timestamp );
        } else {
            $coptic_date_str = $coptic_date['formatted'];
            $greg_date_str = date( 'l, F j, Y', $current_timestamp );
        }

        // Get Synaxarium
        $synaxarium = new Katamars_Synaxarium();
        $synax_content = $synaxarium->get_synaxarium( $coptic_date, $atts['lang'] );

        // Build output
        ob_start();
        ?>
        <div class="katamars-container" dir="<?php echo esc_attr( $dir ); ?>" lang="<?php echo esc_attr( $atts['lang'] ); ?>">
            
            <!-- Date Picker Form -->
            <form method="get" class="katamars-date-form" onsubmit="event.preventDefault(); var date = document.getElementById('katamars_date').value; if(date) { window.location.href = '?katamars_date=' + date; }">
                <label for="katamars_date"><?php echo $is_arabic ? 'اختر تاريخ:' : 'Select Date:'; ?></label>
                <input type="date" id="katamars_date" name="katamars_date" value="<?php echo date( 'Y-m-d', $current_timestamp ); ?>">
                <button type="submit"><?php echo $is_arabic ? 'عرض' : 'Show'; ?></button>
            </form>

            <div class="katamars-header">
                <h2><?php echo $is_arabic ? 'السنكسار' : 'Synaxarium'; ?></h2>
                <p class="katamars-gregorian"><?php echo esc_html( $coptic_date_str ); ?></p>
            </div>

            <?php if ( ! empty( $synax_content ) ) : ?>
                <div class="katamars-synaxarium">
                    <div class="katamars-synax-content">
                        <?php echo wp_kses_post( $synax_content ); ?>
                    </div>
                </div>
            <?php else : ?>
                <p><?php echo $is_arabic ? 'لا يوجد محتوى للسنكسار لهذا اليوم.' : 'No Synaxarium content for this day.'; ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Bible browser shortcode.
     */
    public function render_bible_browser( $atts ) {
        $bible = new Katamars_Bible();
        return $bible->render_bible_browser( $atts );
    }

    /**
     * Add query variables.
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'katamars_date';
        $vars[] = 'katamars_bible_book';
        $vars[] = 'katamars_bible_chapter';
        return $vars;
    }

    /**
     * Add rewrite rules for friendly URLs.
     */
    public function add_rewrite_rules() {
        // Rule for Readings: readings/YYYY-MM-DD
        add_rewrite_rule(
            '^readings/([0-9]{4}-[0-9]{2}-[0-9]{2})/?$',
            'index.php?pagename=readings&katamars_date=$matches[1]',
            'top'
        );

        // Rule for Bible: katamars_bible/Book/Chapter
        add_rewrite_rule(
            '^katamars_bible/([^/]+)/?([0-9]+)?/?$',
            'index.php?pagename=katamars_bible&katamars_bible_book=$matches[1]&katamars_bible_chapter=$matches[2]',
            'top'
        );
    }

    /**
     * Dynamic Page Title.
     */
    public function dynamic_page_title( $title ) {
        if ( get_query_var( 'katamars_date' ) ) {
            $date_str = get_query_var( 'katamars_date' );
            $timestamp = strtotime( $date_str );
            
            if ( $timestamp ) {
                $date_calc = new Katamars_Date();
                $coptic_date = $date_calc->get_coptic_date( $timestamp );
                
                // Arabic Month Names
                $month_names_ar = array(
                    1 => 'توت', 2 => 'بابة', 3 => 'هاتور', 4 => 'كيهك', 5 => 'طوبة', 6 => 'أمشير',
                    7 => 'برمهات', 8 => 'برمودة', 9 => 'بشنس', 10 => 'بؤونة', 11 => 'أبيب', 12 => 'مسرى', 13 => 'النسيء'
                );

                // Arabic Day Names
                $day_names_ar = array(
                    'Sunday' => 'الأحد', 'Monday' => 'الاثنين', 'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء',
                    'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت'
                );

                $day_name = $day_names_ar[ date( 'l', $timestamp ) ];
                $greg_date = date( 'd-m-Y', $timestamp );
                $coptic_str = $coptic_date['coptic_day'] . ' ' . $month_names_ar[ $coptic_date['coptic_month'] ] . ' ' . $coptic_date['coptic_year'];
                
                // Format: قراءات يوم الأحد 23-11-2025 قبطي 14 هاتور 1742
                $new_title = "قراءات يوم $day_name $greg_date قبطي $coptic_str";
                
                // If title is an array (wp_title filter sometimes), return string
                return $new_title;
            }
        }
        return $title;
    }

    /**
     * Render Interactive Coptic Calendar.
     */
    private function render_calendar( $current_timestamp, $coptic_date, $is_arabic ) {
        $coptic_month = $coptic_date['coptic_month'];
        $coptic_day = $coptic_date['coptic_day'];
        $coptic_year = $coptic_date['coptic_year'];
        
        // Month Names
        $month_names_ar = array(
            1 => 'توت', 2 => 'بابة', 3 => 'هاتور', 4 => 'كيهك', 5 => 'طوبة', 6 => 'أمشير',
            7 => 'برمهات', 8 => 'برمودة', 9 => 'بشنس', 10 => 'بؤونة', 11 => 'أبيب', 12 => 'مسرى', 13 => 'النسيء'
        );
        $month_names_en = array(
            1 => 'Tut', 2 => 'Babah', 3 => 'Hatur', 4 => 'Kiyahk', 5 => 'Tubah', 6 => 'Amshir',
            7 => 'Baramhat', 8 => 'Barmuda', 9 => 'Bashans', 10 => 'Baunah', 11 => 'Abib', 12 => 'Mesra', 13 => 'Nasie'
        );
        
        $month_name = $is_arabic ? $month_names_ar[ $coptic_month ] : $month_names_en[ $coptic_month ];
        
        // Days in Month
        $days_in_month = 30;
        if ( $coptic_month == 13 ) {
            // Check if next Gregorian year is leap (approximate for Coptic leap year logic here)
            // Better: Check if (Coptic Year + 1) % 4 == 0. Coptic leap year is every 4 years.
            // Coptic year 1743 is followed by leap? 
            // Standard: Remainder of year / 4. If remainder is 3, next is leap.
            $days_in_month = ( $coptic_year % 4 === 3 ) ? 6 : 5;
        }
        
        // Calculate Start of Month Weekday
        // Current Timestamp corresponds to $coptic_day
        // Start Timestamp = Current - ($coptic_day - 1) days
        $start_timestamp = $current_timestamp - ( ( $coptic_day - 1 ) * 86400 );
        $start_weekday = (int) date( 'w', $start_timestamp ); // 0 = Sunday
        
        // Day Names
        $days_header = $is_arabic 
            ? array( 'أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت' )
            : array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );
            
        ob_start();
        ?>
        <div class="katamars-calendar-wrapper">
            <div class="katamars-calendar-header">
                <span class="katamars-calendar-title"><?php echo esc_html( $month_name . ' ' . $coptic_year ); ?></span>
            </div>
            <div class="katamars-calendar-grid">
                <?php foreach ( $days_header as $day_name ) : ?>
                    <div class="katamars-calendar-day-name"><?php echo esc_html( $day_name ); ?></div>
                <?php endforeach; ?>
                
                <?php
                // Empty cells before start
                for ( $i = 0; $i < $start_weekday; $i++ ) {
                    echo '<div class="katamars-calendar-day empty"></div>';
                }
                
                // Days
                for ( $day = 1; $day <= $days_in_month; $day++ ) {
                    $day_timestamp = $start_timestamp + ( ( $day - 1 ) * 86400 );
                    $greg_date = date( 'Y-m-d', $day_timestamp );
                    $is_current = ( $day == $coptic_day );
                    $is_today = ( date( 'Y-m-d' ) == $greg_date );
                    
                    $classes = 'katamars-calendar-day katamars-nav-link';
                    if ( $is_current ) $classes .= ' current-day';
                    if ( $is_today ) $classes .= ' today';
                    
                    // Link to readings page with date
                    $url = home_url( '/readings/' . $greg_date );
                    
                    echo '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $classes ) . '">' . $day . '</a>';
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

} // End Class

} // End if class_exists check
