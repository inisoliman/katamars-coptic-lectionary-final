# Katamars Coptic Lectionary Plugin

A WordPress plugin to display daily Coptic Orthodox readings based on the Coptic calendar.

## Features

- **Automatic Coptic Date Calculation**: Converts Gregorian dates to Coptic calendar dates
- **Liturgical Season Detection**: Automatically detects Lent, Pentecost, and other special seasons
- **Daily Readings**: Displays appropriate readings for each day based on the Coptic liturgical calendar
- **Synaxarium Integration**: Includes daily Synaxarium readings
- **Multi-language Support**: Arabic and English (extensible)
- **SEO-Friendly**: Optimized for search engines
- **Responsive Design**: Mobile-friendly interface with RTL support for Arabic

## Installation

1. Copy the `katamars-coptic-lectionary` folder to your WordPress `wp-content/plugins/` directory
2. Copy the SQL dump file `u626751827_katamars.sql` to the `data/` folder within the plugin
3. Copy the `synax-text/` directory from the original Katamars script to the `data/` folder
4. Activate the plugin through the WordPress admin panel
5. The plugin will automatically import the database tables on first activation

## Usage

### Shortcode

Display today's readings on any page or post using the shortcode:

```
[katamars_today]
```

With language parameter:

```
[katamars_today lang="en"]
```

### Database Tables

The plugin creates the following tables (with `wp_katamars_` prefix):

- `bible_ar` - Arabic Bible text
- `bible_en` - English Bible text
- `gr_days` - Daily readings calendar
- `gr_lent` - Lent season readings
- `gr_nineveh` - Nineveh fast readings
- `gr_pentecost` - Pentecost season readings
- `gr_sundays` - Sunday readings

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher

## File Structure

```
katamars-coptic-lectionary/
├── katamars-coptic-lectionary.php (Main plugin file)
├── includes/
│   ├── class-katamars.php (Core plugin class)
│   ├── class-katamars-activator.php (Database migration)
│   ├── class-katamars-deactivator.php (Cleanup)
│   ├── class-katamars-loader.php (Hook manager)
│   ├── class-katamars-date.php (Coptic date logic)
│   ├── class-katamars-query.php (Database queries)
│   ├── class-katamars-synaxarium.php (Synaxarium handler)
│   ├── class-katamars-shortcodes.php (Shortcode rendering)
│   └── class-katamars-admin.php (Admin interface)
├── assets/
│   └── css/
│       └── style.css (Frontend styles)
└── data/
    ├── u626751827_katamars.sql (Database dump - USER MUST COPY)
    └── synax-text/ (Synaxarium files - USER MUST COPY)
```

## License

GPL-2.0+

## Credits

Based on the original Katamars PHP script.
