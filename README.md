# Market Intelligence Platform

منصة الاستخبارات التسويقية - نظام داخلي لمراقبة السمعة الرقمية وتحليل المحتوى على منصة X (Twitter)

---

## نظرة عامة

منصة داخلية تساعد فريق التسويق والاستراتيجية على:
- مراقبة ما يُنشر عن الجهة أو العلامة التجارية على X
- تحليل السمعة الرقمية ورصد الهجوم أو التصعيد
- تحليل الكلمات المفتاحية والهاشتاقات والمواضيع
- اكتشاف الأزمات المحتملة والهجمات المنسقة
- إصدار تقارير تحليلية وملخصات تنفيذية

## المتطلبات

- **PHP 7.4+** (8.x موصى به)
- **MySQL 5.7+** أو MariaDB 10.3+
- **Apache** مع mod_rewrite
- **إضافات PHP**: pdo_mysql, curl, json, mbstring
- **حساب Apify** مع API Token
- **حساب OpenAI** مع API Key

## التركيب على Hostinger

### 1. رفع الملفات

ارفع جميع ملفات المشروع إلى مجلد الموقع على Hostinger:
```
/home/uXXXXXXX/domains/yourdomain.com/public_html/
```

أو إذا كنت تستخدم subdomain:
```
/home/uXXXXXXX/domains/yourdomain.com/subdomains/mi-platform/
```

### 2. إعداد قاعدة البيانات

1. أنشئ قاعدة بيانات MySQL من لوحة تحكم Hostinger
2. افتح phpMyAdmin واستورد ملف `database/schema.sql`
3. عدّل ملف `config/env.php` بمعلومات الاتصال:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'uXXXXXXX_mi_platform');
define('DB_USER', 'uXXXXXXX_user');
define('DB_PASS', 'your_secure_password');
```

### 3. إعداد المفاتيح

عدّل `config/env.php` وأضف:

```php
define('APIFY_API_TOKEN', 'apify_api_xxxxxxxxxxxx');
define('OPENAI_API_KEY', 'sk-xxxxxxxxxxxx');
```

أو يمكنك إدخالها لاحقًا من صفحة الإعدادات في المنصة.

### 4. إعداد .htaccess

**الخيار أ: إذا كان document root يشير إلى مجلد المشروع مباشرة**

استخدم ملف `.htaccess` الموجود في الجذر - يعيد التوجيه إلى `/public/index.php`

**الخيار ب: إذا كان document root يشير إلى `/public` (موصى به للـ VPS)**

استخدم ملف `.htaccess` داخل `/public`

### 5. تأمين الملفات

تأكد من أن المجلدات التالية غير قابلة للوصول من الويب:
- `/config/`
- `/app/`
- `/storage/`
- `/database/`

ملف `.htaccess` الرئيسي يتضمن حماية لهذه المجلدات.

### 6. تسجيل الدخول

افتح المتصفح على عنوان المنصة:
```
https://yourdomain.com/login
```

بيانات الدخول الافتراضية:
- **اسم المستخدم**: admin
- **كلمة المرور**: Admin@123456

⚠️ **غيّر كلمة المرور فورًا بعد أول تسجيل دخول!**

## إعداد Cron Jobs

من لوحة تحكم Hostinger > Cron Jobs:

### جلب البيانات (كل 6 ساعات)
```
0 */6 * * * /usr/bin/php /home/uXXXXXXX/domains/yourdomain.com/public_html/cron/collect.php
```

### تحليل AI (كل 6 ساعات - بعد الجلب بـ 30 دقيقة)
```
30 */6 * * * /usr/bin/php /home/uXXXXXXX/domains/yourdomain.com/public_html/cron/analyze.php
```

### التقارير اليومية (كل يوم الساعة 8 صباحًا)
```
0 8 * * * /usr/bin/php /home/uXXXXXXX/domains/yourdomain.com/public_html/cron/reports.php
```

## هيكل المشروع

```
SM-Platform/
├── .htaccess                  # Apache rewrite rules
├── .gitignore
├── config/
│   ├── config.php             # الإعدادات الأساسية
│   └── env.php                # إعدادات البيئة (لا تُرفع لـ Git)
├── public/
│   ├── .htaccess              # Rewrite rules للـ public
│   ├── index.php              # نقطة الدخول الرئيسية
│   └── assets/
│       ├── css/
│       │   └── style.css      # ملف الأنماط الرئيسي
│       └── js/
│           └── app.js         # JavaScript الرئيسي
├── app/
│   ├── core/
│   │   ├── Database.php       # اتصال قاعدة البيانات
│   │   ├── BaseModel.php      # النموذج الأساسي
│   │   ├── BaseController.php # المتحكم الأساسي
│   │   ├── Auth.php           # المصادقة والجلسات
│   │   ├── Router.php         # نظام التوجيه
│   │   └── Helpers.php        # دوال مساعدة
│   ├── services/
│   │   ├── ApifyService.php   # تكامل Apify API
│   │   └── OpenAIService.php  # تكامل OpenAI API
│   ├── models/
│   │   ├── UserModel.php
│   │   ├── ProjectModel.php
│   │   ├── PostModel.php
│   │   ├── CollectionRunModel.php
│   │   ├── AnalysisModel.php
│   │   ├── AlertModel.php
│   │   └── SummaryModel.php
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── ProjectController.php
│   │   ├── CollectionController.php
│   │   ├── PostController.php
│   │   ├── AnalysisController.php
│   │   ├── AlertController.php
│   │   ├── ReportController.php
│   │   └── SettingsController.php
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   ├── auth/
│   │   │   └── login.php
│   │   ├── dashboard/
│   │   │   └── index.php
│   │   ├── projects/
│   │   │   ├── index.php
│   │   │   └── form.php
│   │   ├── collection/
│   │   │   └── index.php
│   │   ├── posts/
│   │   │   └── index.php
│   │   ├── analysis/
│   │   │   ├── index.php
│   │   │   └── summary.php
│   │   ├── alerts/
│   │   │   └── index.php
│   │   ├── reports/
│   │   │   ├── index.php
│   │   │   ├── view.php
│   │   │   └── export.php
│   │   ├── settings/
│   │   │   ├── index.php
│   │   │   └── health.php
│   │   └── errors/
│   │       └── 404.php
│   └── prompts/
│       ├── post_analysis.txt
│       ├── executive_summary.txt
│       ├── crisis_detection.txt
│       └── campaign_insights.txt
├── database/
│   └── schema.sql             # هيكل قاعدة البيانات
├── cron/
│   ├── collect.php            # جلب بيانات مجدول
│   ├── analyze.php            # تحليل AI مجدول
│   └── reports.php            # تقارير يومية مجدولة
└── storage/
    └── logs/                  # سجلات النظام
```

## كيفية الاستخدام

### 1. إضافة جهة للمراقبة
1. اذهب إلى **الجهات والمشاريع** > **إضافة جهة جديدة**
2. أدخل اسم الجهة والكلمات المفتاحية والهاشتاقات والحسابات
3. أضف كلمات الأزمة والمنافسين

### 2. جلب البيانات
1. اذهب إلى **جلب البيانات**
2. اختر الجهة وعدد المنشورات والنافذة الزمنية
3. اضغط **تشغيل** لبدء الجلب من Apify

### 3. تحليل AI
1. اذهب إلى **تحليل الذكاء الاصطناعي**
2. اختر الجهة واضغط **تشغيل التحليل**
3. أو أنشئ **ملخص تنفيذي** أو **فحص أزمة**

### 4. مراجعة التنبيهات
- التنبيهات تُولد تلقائيًا عند اكتشاف:
  - ارتفاع نسبة السلبية
  - هجوم محتمل
  - تكرار شكاوى
  - كلمات أزمة

### 5. التقارير
- أنشئ تقارير يومية أو أسبوعية أو مخصصة
- يمكن طباعة/تصدير التقارير كـ HTML

## ملاحظات مهمة

- **تكلفة API**: استخدم `gpt-4o-mini` للتحليل الاقتصادي، و `gpt-4o` للجودة الأعلى
- **Batching**: النظام يحلل المنشورات على شكل دفعات لتقليل التكلفة
- **التخزين المؤقت**: النتائج المحللة لا تُعاد تحليلها إلا عند الطلب
- **التوسع**: النظام مصمم لدعم منصات إضافية (Instagram, TikTok) مستقبلًا
- **اللغة**: الواجهة عربية مع دعم تحليل المحتوى العربي والإنجليزي

## الأمان

- حماية الجلسات مع CSRF tokens
- Prepared statements لجميع استعلامات SQL
- تخزين مفاتيح API في ملف منفصل خارج Git
- حماية المجلدات الحساسة من الوصول المباشر
- تشفير كلمات المرور بـ bcrypt
- تسجيل الأخطاء والعمليات

## الترخيص

مشروع داخلي - جميع الحقوق محفوظة
