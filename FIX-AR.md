# إصلاح مشكلة التفعيل - Katamars Plugin

## المشكلة التي تم حلها

كانت المشكلة: `Cannot declare class Katamars_Shortcodes, because the name is already in use`

**السبب**: كان WordPress يحمل ملفات الإضافة مرتين أثناء التفعيل، مما يسبب إعادة تعريف الثوابت والفئات.

## الحل المطبق

تم إضافة حماية لجميع الملفات:

### 1. حماية الثوابت (Constants)
```php
if ( ! defined( 'KATAMARS_VERSION' ) ) {
    define( 'KATAMARS_VERSION', '1.0.0' );
}
```

### 2. حماية الفئات (Classes)
```php
if ( ! class_exists( 'Katamars_Shortcodes' ) ) {
    class Katamars_Shortcodes {
        // ...
    }
}
```

## الملفات التي تم تحديثها

✅ `katamars-coptic-lectionary.php` - الملف الرئيسي
✅ `class-katamars.php` - الفئة الأساسية
✅ `class-katamars-loader.php` - مدير الخطافات
✅ `class-katamars-activator.php` - التفعيل
✅ `class-katamars-deactivator.php` - إلغاء التفعيل
✅ `class-katamars-admin.php` - صفحة الإعدادات
✅ `class-katamars-date.php` - حسابات التاريخ القبطي
✅ `class-katamars-query.php` - استعلامات قاعدة البيانات
✅ `class-katamars-shortcodes.php` - الشورت كود
✅ `class-katamars-synaxarium.php` - السنكسار

## خطوات التفعيل الآن

1. **تأكد من نسخ الملفات المطلوبة**:
   ```
   ✓ u626751827_katamars.sql → data/
   ✓ synax-text/ → data/
   ```

2. **إلغاء تفعيل الإضافة** (إذا كانت مفعلة):
   - اذهب إلى الإضافات
   - إلغاء تفعيل "Katamars Coptic Lectionary"

3. **إعادة التفعيل**:
   - اضغط "تفعيل"
   - انتظر 30-60 ثانية لاستيراد قاعدة البيانات

4. **التحقق من التثبيت**:
   - اذهب إلى الإعدادات → Katamars
   - تأكد من ظهور جميع الجداول السبعة

## ما يجب أن تراه

عند نجاح التثبيت:
```
✓ bible_ar: Installed (~35,686 records)
✓ bible_en: Installed (~35,749 records)
✓ gr_days: Installed (367 records)
✓ gr_lent: Installed (54 records)
✓ gr_nineveh: Installed (5 records)
✓ gr_pentecost: Installed (51 records)
✓ gr_sundays: Installed (68 records)
```

## استخدام الشورت كود

بعد التفعيل الناجح:

```
[katamars_today]           // عربي (افتراضي)
[katamars_today lang="en"] // إنجليزي
```

## إذا استمرت المشاكل

1. تحقق من سجل أخطاء WordPress: `wp-content/debug.log`
2. تأكد من وجود ملف SQL في المكان الصحيح
3. تأكد من صلاحيات الملفات (755 للمجلدات، 644 للملفات)
4. جرب إعادة رفع ملفات الإضافة

---

**ملاحظة**: الخطأ الذي رأيته حول `wp_odse_ai_cache` ليس من إضافة Katamars، بل من إضافة SEO أخرى مثبتة لديك.
