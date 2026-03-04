# Qassem Archive Core — Plugin Documentation

**Version:** 1.0.0 (Phase 1 — MVP)  
**WordPress:** 6.0+  
**PHP:** 8.0+  
**RTL:** عربي كامل

---

## شجرة الملفات

```
qassem-archive-core/
├── qassem-archive-core.php          ← نقطة الدخول الرئيسية
├── includes/
│   ├── class-plugin.php             ← Orchestrator (Singleton)
│   ├── class-installer.php          ← Activation/Deactivation + DB tables
│   ├── class-post-types.php         ← CPT: qa_evidence, qa_event, qa_location
│   ├── class-taxonomies.php         ← Taxonomies: qa_topic, qa_tag
│   ├── class-meta-fields.php        ← Schema للحقول + register_post_meta()
│   ├── class-rest-api.php           ← REST API endpoints
│   ├── class-seeder.php             ← بيانات تجريبية
│   └── Admin/
│       ├── class-meta-boxes.php     ← Meta Boxes + Tabbed UI
│       ├── class-settings.php       ← Settings Page (R2, AI, Map)
│       ├── class-columns.php        ← Custom Admin Columns
│       └── class-assets.php         ← CSS/JS Enqueue
├── admin/
│   ├── css/admin.css                ← Admin styles (RTL)
│   └── js/admin.js                  ← Tabs + AI button + Map preview
└── languages/
    └── qassem-archive.pot           ← (للترجمة لاحقًا)
```

---

## التثبيت

1. ارفع مجلد `qassem-archive-core` إلى `/wp-content/plugins/`
2. فعّل Plugin من لوحة WordPress
3. ستجد قائمة **Qassem Archive** في الشريط الجانبي

---

## Custom Post Types

| Post Type | Slug | الوصف |
|---|---|---|
| `qa_evidence` | `/evidence` | الأدلة — الوحدة الأساسية |
| `qa_event` | `/events` | الأحداث التاريخية |
| `qa_location` | `/location` | المواقع الجغرافية |

---

## Taxonomies

| Taxonomy | النوع | الوصف |
|---|---|---|
| `qa_topic` | Hierarchical | المواضيع (مثل تصنيف) |
| `qa_tag` | Non-hierarchical | الوسوم الحرة |

---

## حقول Evidence (Meta Fields)

### قسم البيانات الأساسية
| الحقل | النوع | القيم |
|---|---|---|
| `qa_evidence_type` | select | video / photo / document / testimony |
| `qa_event_date` | date | YYYY-MM-DD |
| `qa_event_date_precision` | select | exact / month / year / unknown |
| `qa_location_id` | relation | ID لـ qa_location |
| `qa_event_id` | relation | ID لـ qa_event |
| `qa_source_type` | select | eyewitness / activist / media / organization / archive |
| `qa_source_url` | url | رابط المصدر الأصلي |
| `qa_language` | select | ar / en / fr |

### قسم التحقق
| الحقل | النوع | القيم |
|---|---|---|
| `qa_verification_level` | select | unverified / possible / probable / verified |
| `qa_verification_notes` | textarea | ملاحظات نصية |
| `qa_verification_by` | auto | user_id (يُملأ تلقائيًا) |
| `qa_verification_date` | auto | datetime (يُملأ تلقائيًا) |

### قسم الوسائط
| الحقل | النوع | الوصف |
|---|---|---|
| `qa_media_storage` | select | r2 / external / wp |
| `qa_media_url` | url | رابط الملف |
| `qa_thumb_url` | url | الصورة المصغرة |
| `qa_file_size` | text | حجم الملف |
| `qa_file_duration` | text | مدة الفيديو |

### قسم AI (للمرحلة الثالثة)
| الحقل | الوصف |
|---|---|
| `qa_ai_status` | idle / processing / ready / error |
| `qa_ai_summary` | ملخص تلقائي |
| `qa_ai_tags` | JSON: كلمات مفتاحية |
| `qa_ai_transcript` | نص تفريغ الصوت |
| `qa_ai_ocr_text` | نص OCR |
| `qa_ai_entities` | JSON: أماكن وتواريخ |

---

## REST API Endpoints

### GET /wp-json/qassem/v1/evidence
فلاتر متاحة:
- `evidence_type` — نوع الدليل
- `location_id` — الموقع
- `event_id` — الحدث
- `verification_level` — درجة التحقق
- `source_type` — نوع المصدر
- `date_from` / `date_to` — نطاق التاريخ (YYYY-MM-DD)
- `topic` — موضوع (slug)
- `search` — بحث نصي
- `per_page` / `page` — pagination

**مثال:**
```
GET /wp-json/qassem/v1/evidence?evidence_type=video&location_id=5&date_from=2011-01-01
```

### GET /wp-json/qassem/v1/map-points
نقاط الخريطة — كل دليل له موقع جغرافي.  
**الاستجابة:** `{ points: [{id, title, lat, lng, evidence_type, ...}] }`

### GET /wp-json/qassem/v1/timeline
أدلة مرتبة زمنيًا.  
**Parameters:** `year_from`, `year_to`, `location_id`, `event_id`

### GET /wp-json/qassem/v1/locations
قائمة كل المواقع.

### POST /wp-json/qassem/v1/evidence/{id}/run-ai
تشغيل تحليل AI (يتطلب صلاحية edit_posts).  
**Phase 3** ستُفعّل المنطق الفعلي.

---

## صفحة الإعدادات

**القائمة:** Qassem Archive → الإعدادات

### R2 Settings
- Account ID
- Access Key ID  
- Secret Access Key
- Bucket Name
- Public URL

### AI Settings
- Provider (OpenAI / Anthropic)
- API Key
- Model

### Map Settings
- Default Latitude/Longitude (بانياس: 35.1667, 35.9333)
- Default Zoom Level

---

## البيانات التجريبية (Seed)

**طريقة 1 — WP-CLI:**
```bash
wp eval "QA\Seeder::run();"
```

**طريقة 2 — URL (Admin فقط):**
1. احصل على nonce:
```php
echo wp_create_nonce('qa_seed');
```
2. زر:
```
/wp-admin/?qa_seed=1&qa_seed_nonce=YOUR_NONCE
```

**ما يُنشئه Seed:**
- 3 مواقع (بانياس، طرطوس، البلد القديم)
- 2 أحداث
- 4 أدلة (فيديو، صورة، شهادة، وثيقة)
- مواضيع ووسوم

---

## جدول قاعدة البيانات

**`wp_qa_audit_log`** — سجل التعديلات
```sql
id, post_id, user_id, action, field_name, old_value, new_value, created_at
```

---

## Workflow المستخدمين

| الدور | الصلاحية |
|---|---|
| Contributor | إضافة دليل (Draft) |
| Editor | مراجعة + اعتماد + تعديل التحقق |
| Admin | كل الإعدادات + الحذف |

---

## المراحل القادمة

- **Phase 2:** واجهة Front-end + خريطة Leaflet + Timeline
- **Phase 3:** AI Analysis (OCR + Transcript + Summary) + R2 Upload
- **Phase 4:** خط زمني تفاعلي + بحث متقدم + i18n عربي/إنجليزي/فرنسي

---

## ملاحظات الأمان

- كل حقول AI نتائجها "اقتراحات" تحتاج اعتماد بشري
- ممنوع تمامًا: Face Recognition / تحديد هويات أشخاص
- سجل مراجعة (Audit Log) لكل تعديل على حقول التحقق
- Nonce verification على كل AJAX و form save
- Capability checks على كل REST endpoint حساس

---

## Phase 3 — Map (Leaflet) + Timeline Widget

### الشورت كود الرئيسي

```
[qa_map_and_timeline]
```

#### الخصائص المتاحة

| الخاصية | النوع | الافتراضي | الوصف |
|---|---|---|---|
| `title` | نص | — | عنوان الودجت |
| `subtitle` | نص | — | نص توضيحي |
| `map_height` | CSS | `650px` | ارتفاع خريطة Leaflet |
| `year_from` | رقم | `2000` | بداية الخط الزمني |
| `year_to` | رقم | السنة الحالية | نهاية الخط الزمني |
| `event_id` | ID | `0` | تصفية مسبقة حسب حدث |

#### أمثلة

```
[qa_map_and_timeline
    title="أرشيف بانياس — الخريطة والخط الزمني"
    subtitle="استكشف الأدلة الموثقة جغرافياً وزمنياً"
    map_height="700px"
]

[qa_map_and_timeline
    title="أحداث 2011"
    year_from="2011"
    year_to="2011"
]
```

### الملفات الجديدة (Phase 3)

| الملف | الوصف |
|---|---|
| `assets/js/map-timeline.js` | JS كامل للخريطة + الخط الزمني (853 سطر) |
| `assets/css/map-timeline.css` | CSS مستقل للودجت (RTL — Dark theme) |
| `templates/evidence/map-timeline-embed.php` | Template للشورت كود |
| `includes/Frontend/class-templates.php` | محدّث بـ shortcode + enqueue |

### المكتبات المُضافة

- **Leaflet 1.9.4** — `leaflet@1.9.4` (CDN)
- **MarkerCluster 1.5.3** — `leaflet.markercluster@1.5.3` (CDN)
- **Timeline** — مُبنى من الصفر (lightweight JS، بدون مكتبات خارجية)

### المزايا التقنية

- **Sync ثنائي الاتجاه**: النقر على الخريطة يُبرز العنصر في الخط الزمني، والعكس
- **Marker clustering** مع أيقونات مخصصة حسب الحجم (sm/md/lg)
- **Popup cards** بثيم داكن RTL: صورة مصغرة + تاريخ + رابط
- **Zoom timeline**: تبديل بين عرض الأشهر / الأرباع / السنوات
- **Navigation**: أزرار تقديم / تأخير الفترة الزمنية
- **Year ruler**: شريط سنوات علوي يُبرز السنوات التي تحتوي بيانات
- **URL state**: الفلاتر تُحفظ في URL params قابلة للمشاركة
- **Mobile**: تبويب map/timeline على الشاشات الصغيرة
- **ResizeObserver**: إعادة حساب حجم الخريطة عند تغيير حجم الحاوية
- **Transient caching**: API responses مخزنة في الـ transients 5 دقائق
