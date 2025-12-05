# Katamars Plugin - Quick Installation Guide

## IMPORTANT: Before Activation

You MUST copy two items to the plugin's `data/` directory:

### 1. SQL Database Dump
```
FROM: kata/katamars/u626751827_katamars.sql
TO:   wp-content/plugins/katamars-coptic-lectionary/data/u626751827_katamars.sql
```

### 2. Synaxarium Text Files
```
FROM: kata/katamars/synax-text/
TO:   wp-content/plugins/katamars-coptic-lectionary/data/synax-text/
```

## Installation Steps

1. **Copy the plugin folder** to your WordPress installation:
   ```
   kata/katamars/katamars-coptic-lectionary/
   → wp-content/plugins/katamars-coptic-lectionary/
   ```

2. **Copy the data files** (see above - CRITICAL!)

3. **Activate the plugin**:
   - Go to WordPress Admin → Plugins
   - Find "Katamars Coptic Lectionary"
   - Click "Activate"
   - Wait 30-60 seconds for database import

4. **Verify installation**:
   - Go to Settings → Katamars
   - Check all 7 tables show "Installed" with record counts:
     - bible_ar: ~35,686 records
     - bible_en: ~35,749 records
     - gr_days: 367 records
     - gr_lent: 54 records
     - gr_nineveh: 5 records
     - gr_pentecost: 51 records
     - gr_sundays: 68 records

5. **Test the shortcode**:
   - Create a new page
   - Add: `[katamars_today]`
   - Publish and view

## Troubleshooting

### Database tables not created?
- Check that `u626751827_katamars.sql` is in the `data/` folder
- Check WordPress error logs
- Try deactivating and reactivating the plugin

### Synaxarium not showing?
- Verify `synax-text/` folder is in `data/` directory
- Check folder structure: `data/synax-text/ar/the_files/1/day1.php` etc.

### Readings not displaying?
- Verify database tables were imported successfully
- Check Settings → Katamars for table status
- Try a different date to rule out missing data

## Usage

### Basic Shortcode
```
[katamars_today]
```

### With Language Parameter
```
[katamars_today lang="en"]  // English
[katamars_today lang="ar"]  // Arabic (default)
```

## Support

Check the full walkthrough documentation for detailed technical information.
