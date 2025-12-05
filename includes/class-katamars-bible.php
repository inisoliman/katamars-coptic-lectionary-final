<?php

/**
 * The Bible Browser functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Katamars_Coptic_Lectionary
 * @subpackage Katamars_Coptic_Lectionary/includes
 * @author     Bishoy A. <bishoy_a@hotmail.com>
 */
class Katamars_Bible {

    /**
     * Render the Bible browser.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML content.
     */
    /**
     * Render the Bible browser.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML content.
     */
    public function render_bible_browser( $atts ) {
        $atts = shortcode_atts( array(
            'lang' => 'ar',
        ), $atts );

        // Handle Language Param
        if ( isset( $_GET['lang'] ) && in_array( $_GET['lang'], array( 'ar', 'en' ) ) ) {
            $atts['lang'] = sanitize_text_field( $_GET['lang'] );
        }

        $book = get_query_var( 'katamars_bible_book' );
        $chapter = get_query_var( 'katamars_bible_chapter' );

        // 1. Books Menu (Home)
        if ( empty( $book ) ) {
            return $this->render_books_menu( $atts['lang'] );
        }

        // 2. Chapters Menu (Book selected, no chapter)
        if ( empty( $chapter ) ) {
            return $this->render_chapters_menu( $book, $atts['lang'] );
        }

        // 3. Reading View (Book and Chapter selected)
        return $this->display_chapter( $book, $chapter, $atts['lang'] );
    }

    /**
     * Render the main menu of Bible books.
     */
    private function render_books_menu( $lang ) {
        // Arabic Book Names Mapping
        $books_ar = array(
            'العهد القديم' => array(
                'Genesis' => 'التكوين', 'Exodus' => 'الخروج', 'Leviticus' => 'اللاويين', 'Numbers' => 'العدد', 'Deuteronomy' => 'التثنية',
                'Joshua' => 'يشوع', 'Judges' => 'القضاة', 'Ruth' => 'راعوث', '1 Samuel' => 'صموئيل الأول', '2 Samuel' => 'صموئيل الثاني',
                '1 Kings' => 'ملوك الأول', '2 Kings' => 'ملوك الثاني', '1 Chronicles' => 'أخبار الأيام الأول', '2 Chronicles' => 'أخبار الأيام الثاني',
                'Ezra' => 'عزرا', 'Nehemiah' => 'نحميا', 'Esther' => 'أستير', 'Job' => 'أيوب', 'Psalms' => 'المزامير', 'Proverbs' => 'الأمثال',
                'Ecclesiastes' => 'الجامعة', 'Song of Solomon' => 'نشيد الأنشاد', 'Isaiah' => 'إشعياء', 'Jeremiah' => 'إرميا', 
                'Lamentations' => 'مراثي إرميا', 'Ezekiel' => 'حزقيال', 'Daniel' => 'دانيال', 'Hosea' => 'هوشع', 'Joel' => 'يوئيل', 
                'Amos' => 'عاموس', 'Obadiah' => 'عوبديا', 'Jonah' => 'يونان', 'Micah' => 'ميخا', 'Nahum' => 'ناحوم', 'Habakkuk' => 'حبقوق', 
                'Zephaniah' => 'صفنيا', 'Haggai' => 'حجي', 'Zechariah' => 'زكريا', 'Malachi' => 'ملاخي'
            ),
            'العهد الجديد' => array(
                'Matthew' => 'متى', 'Mark' => 'مرقس', 'Luke' => 'لوقا', 'John' => 'يوحنا', 'Acts' => 'أعمال الرسل',
                'Romans' => 'رومية', '1 Corinthians' => 'كورنثوس الأولى', '2 Corinthians' => 'كورنثوس الثانية', 'Galatians' => 'غلاطية',
                'Ephesians' => 'أفسس', 'Philippians' => 'فيلبي', 'Colossians' => 'كولوسي', '1 Thessalonians' => 'تسالونيكي الأولى',
                '2 Thessalonians' => 'تسالونيكي الثانية', '1 Timothy' => 'تيموثاوس الأولى', '2 Timothy' => 'تيموثاوس الثانية',
                'Titus' => 'تيطس', 'Philemon' => 'فليمون', 'Hebrews' => 'العبرانيين', 'James' => 'يعقوب', '1 Peter' => 'بطرس الأولى',
                '2 Peter' => 'بطرس الثانية', '1 John' => 'يوحنا الأولى', '2 John' => 'يوحنا الثانية', '3 John' => 'يوحنا الثالثة',
                'Jude' => 'يهوذا', 'Revelation' => 'رؤيا يوحنا'
            )
        );

        ob_start();
        ?>
        <div class="katamars-bible-menu" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
            <div class="katamars-controls" style="justify-content: flex-end; margin-bottom: 10px;">
                <button id="katamars-lang-btn" class="katamars-btn action-btn">
                    <span class="icon">🌐</span> <?php echo ($lang == 'ar') ? 'English' : 'عربي'; ?>
                </button>
            </div>
            <h2><?php echo ($lang == 'ar') ? 'الكتاب المقدس' : 'Holy Bible'; ?></h2>
            <?php foreach ($books_ar as $testament => $book_list) : ?>
                <h3 class="testament-title"><?php echo ($lang == 'ar') ? $testament : (($testament == 'العهد القديم') ? 'Old Testament' : 'New Testament'); ?></h3>
                <ul class="katamars-book-list">
                    <?php foreach ($book_list as $slug => $book_name) : ?>
                        <li>
                            <a href="<?php echo esc_url( home_url( '/katamars_bible/' . sanitize_title( $slug ) ) ); ?>">
                                <span class="book-icon">📖</span>
                                <span class="book-name"><?php echo ($lang == 'ar') ? $book_name : $slug; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render the list of chapters for a specific book.
     */
    private function render_chapters_menu( $book_slug, $lang ) {
        global $wpdb;
        $book_name = $this->slug_to_book_name( $book_slug );
        $table_name = ($lang == 'ar') ? $wpdb->prefix . 'katamars_bible_ar' : $wpdb->prefix . 'katamars_bible_en';

        // Get Max Chapter
        $max_chapter = $wpdb->get_var( $wpdb->prepare(
            "SELECT MAX(Chapter) FROM $table_name WHERE Book_Name = %s",
            $book_name
        ) );

        // Get Arabic Name for display
        $arabic_name = $this->get_arabic_book_name($book_slug);
        $display_name = ($lang == 'ar' && $arabic_name) ? $arabic_name : $book_name;

        ob_start();
        ?>
        <div class="katamars-bible-chapters" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
            <div class="katamars-bible-nav">
                <a href="<?php echo esc_url( home_url( '/katamars_bible/' ) ); ?>">
                    <span class="icon">🏠</span> <?php echo ($lang == 'ar') ? 'الرئيسية' : 'Home'; ?>
                </a>
                <span class="separator">/</span>
                <span class="current"><?php echo esc_html( $display_name ); ?></span>
            </div>

            <h2 class="chapter-list-title"><?php echo ($lang == 'ar') ? 'اختر الإصحاح' : 'Select Chapter'; ?></h2>

            <div class="katamars-chapter-grid">
                <?php for ( $i = 1; $i <= $max_chapter; $i++ ) : ?>
                    <a href="<?php echo esc_url( home_url( '/katamars_bible/' . $book_slug . '/' . $i ) ); ?>" class="chapter-item">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Display a specific chapter.
     */
    private function display_chapter( $book_slug, $chapter, $lang ) {
        // ... (Existing code)
        global $wpdb;
        
        $book_name = $this->slug_to_book_name( $book_slug );
        $table_name = ($lang == 'ar') ? $wpdb->prefix . 'katamars_bible_ar' : $wpdb->prefix . 'katamars_bible_en';

        // Query Verses
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE Book_Name = %s AND Chapter = %d ORDER BY Verse ASC",
            $book_name,
            $chapter
        );
        $verses = $wpdb->get_results( $sql );

        // Get Arabic Name for display
        $arabic_name = $this->get_arabic_book_name($book_slug);
        $display_name = ($lang == 'ar' && $arabic_name) ? $arabic_name : $book_name;

        // Prepare Text for TTS (Plain text)
        $tts_text = "";
        foreach($verses as $v) {
            $tts_text .= $v->Text . " ";
        }

        ob_start();
        
        // SEO: Add Schema.org Structured Data
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "Article",
            "headline" => $display_name . " " . $chapter,
            "articleBody" => substr(strip_tags($tts_text), 0, 200) . "...",
            "author" => [
                "@type" => "Organization",
                "name" => "القطمارس - Katamars"
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => "القطمارس",
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => home_url('/wp-content/uploads/logo.png')
                ]
            ],
            "datePublished" => date('c'),
            "inLanguage" => $lang == 'ar' ? 'ar' : 'en',
            "mainEntityOfPage" => get_permalink()
        ];
        ?>
        
        <!-- SEO: Structured Data -->
        <script type="application/ld+json">
        <?php echo json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
        </script>
        
        <!-- SEO: Article Wrapper -->
        <article class="katamars-bible-reader" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>" itemscope itemtype="https://schema.org/Article">
            <meta itemprop="headline" content="<?php echo esc_attr($display_name . ' ' . $chapter); ?>">
            <meta itemprop="inLanguage" content="<?php echo $lang == 'ar' ? 'ar' : 'en'; ?>">
            
            <!-- Navigation Breadcrumb -->
            <div class="katamars-bible-nav">
                <a href="<?php echo esc_url( home_url( '/katamars_bible/' ) ); ?>">
                    <span class="icon">🏠</span>
                </a>
                <span class="separator">/</span>
                <a href="<?php echo esc_url( home_url( '/katamars_bible/' . $book_slug ) ); ?>">
                    <?php echo esc_html( $display_name ); ?>
                </a>
                <span class="separator">/</span>
                <span class="current"><?php echo ($lang == 'ar') ? 'إصحاح ' . $chapter : 'Chapter ' . $chapter; ?></span>
            </div>

            <!-- Toolbar -->
            <div class="katamars-reader-toolbar">
                <div class="toolbar-group">
                    <button id="font-decrease" class="tool-btn" data-tooltip="تصغير الخط">A-</button>
                    <span class="font-size-display"><?php echo $lang == 'ar' ? '22px' : '22px'; ?></span>
                    <button id="font-increase" class="tool-btn" data-tooltip="تكبير الخط">A+</button>
                </div>
                <div class="toolbar-group">
                    <button id="share-btn" class="tool-btn" data-tooltip="<?php echo $lang == 'ar' ? 'مشاركة' : 'Share'; ?>">📤</button>
                    <button id="katamars-lang-btn" class="tool-btn" title="<?php echo $lang == 'ar' ? 'English' : 'عربي'; ?>" style="width:auto; padding:0 15px; border-radius:20px;">
                        <span class="icon">🌐</span> <?php echo ($lang == 'ar') ? 'En' : 'عربي'; ?>
                    </button>
                    <button id="bible-audio-btn" class="tool-btn audio-btn">
                        <span class="icon">🔊</span> <?php echo ($lang == 'ar') ? 'استماع' : 'Listen'; ?>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="katamars-chapter-content" id="bible-text-content" itemprop="articleBody">
                <h1 class="chapter-heading" itemprop="name"><?php echo esc_html( $display_name ) . ' ' . $chapter; ?></h1>
                <?php if ( $verses ) : ?>
                    <?php foreach ( $verses as $verse ) : ?>
                        <span class="katamars-verse" data-verse="<?php echo $verse->Verse; ?>">
                            <span class="verse-number"><?php echo $verse->Verse; ?></span>
                            <span class="verse-text"><?php echo $verse->Text; ?></span>
                        </span>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p><?php echo ($lang == 'ar') ? 'لم يتم العثور على محتوى.' : 'No content found.'; ?></p>
                <?php endif; ?>
            </div>

            <!-- Footer Navigation -->
            <!-- Enhanced Chapter Navigation -->
            <div class="katamars-chapter-nav">
                <?php if ( $chapter > 1 ) : ?>
                    <a href="<?php echo esc_url( home_url( '/katamars_bible/' . $book_slug . '/' . ($chapter - 1) ) ); ?>" class="nav-btn prev">
                        <span class="nav-icon">◀</span>
                        <span class="nav-text"><?php echo ($lang == 'ar') ? 'السابق' : 'Previous'; ?></span>
                    </a>
                <?php else : ?>
                    <span class="nav-btn prev disabled">
                        <span class="nav-icon">◀</span>
                        <span class="nav-text"><?php echo ($lang == 'ar') ? 'السابق' : 'Previous'; ?></span>
                    </span>
                <?php endif; ?>

                <a href="<?php echo esc_url( home_url( '/katamars_bible/' . $book_slug ) ); ?>" class="nav-btn list">
                    <span class="nav-icon">📑</span>
                    <span class="nav-text"><?php echo ($lang == 'ar') ? 'فهرس الإصحاحات' : 'Chapters Index'; ?></span>
                </a>

                <a href="<?php echo esc_url( home_url( '/katamars_bible/' . $book_slug . '/' . ($chapter + 1) ) ); ?>" class="nav-btn next">
                    <span class="nav-text"><?php echo ($lang == 'ar') ? 'التالي' : 'Next'; ?></span>
                    <span class="nav-icon">▶</span>
                </a>
            </div>
        </article>
        <?php
        return ob_get_clean();
    }

    /**
     * Helper to convert slug to Book Name in DB.
     */
    private function slug_to_book_name( $slug ) {
        $name = str_replace( '-', ' ', $slug );
        return ucwords( $name );
    }

    /**
     * Helper to get Arabic name from slug.
     */
    public static function get_arabic_book_name( $slug ) {
        $map = array(
            'Genesis' => 'التكوين', 'Exodus' => 'الخروج', 'Leviticus' => 'اللاويين', 'Numbers' => 'العدد', 'Deuteronomy' => 'التثنية',
            'Joshua' => 'يشوع', 'Judges' => 'القضاة', 'Ruth' => 'راعوث', '1 Samuel' => 'صموئيل الأول', '2 Samuel' => 'صموئيل الثاني',
            '1 Kings' => 'ملوك الأول', '2 Kings' => 'ملوك الثاني', '1 Chronicles' => 'أخبار الأيام الأول', '2 Chronicles' => 'أخبار الأيام الثاني',
            'Ezra' => 'عزرا', 'Nehemiah' => 'نحميا', 'Esther' => 'أستير', 'Job' => 'أيوب', 'Psalms' => 'المزامير', 'Proverbs' => 'الأمثال',
            'Ecclesiastes' => 'الجامعة', 'Song of Solomon' => 'نشيد الأنشاد', 'Isaiah' => 'إشعياء', 'Jeremiah' => 'إرميا', 
            'Lamentations' => 'مراثي إرميا', 'Ezekiel' => 'حزقيال', 'Daniel' => 'دانيال', 'Hosea' => 'هوشع', 'Joel' => 'يوئيل', 
            'Amos' => 'عاموس', 'Obadiah' => 'عوبديا', 'Jonah' => 'يونان', 'Micah' => 'ميخا', 'Nahum' => 'ناحوم', 'Habakkuk' => 'حبقوق', 
            'Zephaniah' => 'صفنيا', 'Haggai' => 'حجي', 'Zechariah' => 'زكريا', 'Malachi' => 'ملاخي',
            'Matthew' => 'متى', 'Mark' => 'مرقس', 'Luke' => 'لوقا', 'John' => 'يوحنا', 'Acts' => 'أعمال الرسل',
            'Romans' => 'رومية', '1 Corinthians' => 'كورنثوس الأولى', '2 Corinthians' => 'كورنثوس الثانية', 'Galatians' => 'غلاطية',
            'Ephesians' => 'أفسس', 'Philippians' => 'فيلبي', 'Colossians' => 'كولوسي', '1 Thessalonians' => 'تسالونيكي الأولى',
            '2 Thessalonians' => 'تسالونيكي الثانية', '1 Timothy' => 'تيموثاوس الأولى', '2 Timothy' => 'تيموثاوس الثانية',
            'Titus' => 'تيطس', 'Philemon' => 'فليمون', 'Hebrews' => 'العبرانيين', 'James' => 'يعقوب', '1 Peter' => 'بطرس الأولى',
            '2 Peter' => 'بطرس الثانية', '1 John' => 'يوحنا الأولى', '2 John' => 'يوحنا الثانية', '3 John' => 'يوحنا الثالثة',
            'Jude' => 'يهوذا', 'Revelation' => 'رؤيا يوحنا'
        );
        // We need to handle the slug to name conversion here too if static
        $name = str_replace( '-', ' ', $slug );
        $english_name = ucwords( $name );
        
        return isset($map[$english_name]) ? $map[$english_name] : false;
    }
}
