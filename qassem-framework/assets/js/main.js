/* QASSEM V5 — WordPress Config Bridge */
var _CFG   = (typeof QASSEM_CONFIG !== 'undefined') ? QASSEM_CONFIG : {};
var _lang  = _CFG.lang  || 'fr';
var _theme = _CFG.defaultTheme || 'dark';
var _email = _CFG.email || 'contact@qassem.io';
var _skin  = _CFG.activeSkin  || 'qassem';
(function(){
  try {
    document.documentElement.setAttribute('data-skin', _skin);
    var saved = localStorage.getItem('qassem-theme');
    if (!saved) document.documentElement.setAttribute('data-theme', _theme);
    var qux = localStorage.getItem('qassem_ux');
    if (qux) {
      var st = JSON.parse(qux);
      if (st.skin)  document.documentElement.setAttribute('data-skin',  st.skin);
      if (st.theme) document.documentElement.setAttribute('data-theme', st.theme);
      if (st.brand) document.documentElement.style.setProperty('--brand', st.brand);
      if (st.gold)  document.documentElement.style.setProperty('--gold',  st.gold);
    }
  } catch(e) {}
})();

  (function(){
    const lb = document.getElementById('lightbox');
    if(!lb) return;
    const lbImg = document.getElementById('lbImg');
    const lbTitle = document.getElementById('lbTitle');
    const lbSub = document.getElementById('lbSub');
    const lbDesc = document.getElementById('lbDesc');

    document.querySelectorAll('.pf-card').forEach(card => {
      card.addEventListener('click', () => {
        const img = card.dataset.img || '';
        const pid = card.dataset.pid || '';
        const lang = document.documentElement.lang || 'fr';
        const dict = getDict(lang);
        const base = getDict('fr');

        const item = (dict.portfolio && dict.portfolio.items && dict.portfolio.items[pid]) 
          ? dict.portfolio.items[pid]
          : ((base.portfolio && base.portfolio.items && base.portfolio.items[pid]) ? base.portfolio.items[pid] : {});

        lbImg.src = img;
        lbImg.alt = (item.title || card.dataset.title || 'Portfolio');
        lbTitle.textContent = item.title || card.dataset.title || '';
        lbSub.textContent = item.sub || card.dataset.sub || '';
        lbDesc.textContent = item.desc || card.dataset.desc || '';

        lb.classList.add('open');
        lb.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
      });
    });

    function closeLB(){
      lb.classList.remove('open');
      lb.setAttribute('aria-hidden', 'true');
      lbImg.src = '';
      document.body.style.overflow = '';
    }

    lb.addEventListener('click', (e) => { if(e.target === lb) closeLB(); });
    const closeBtn = lb.querySelector('.lb-close');
    if(closeBtn) closeBtn.addEventListener('click', closeLB);
    document.addEventListener('keydown', (e) => { if(e.key === 'Escape') closeLB(); });
  })();


/* ============ SECTION ============ */


    const certsData = [
      { id: "itil", cat: "strategy", title: "ITIL 4 Strategic Leader", issuer: "PeopleCert", img: "assets/certificates/itil-strategic.jpg", icon: "fa-shield-halved" },
      { id: "aws", cat: "tech", title: "AWS Cloud Fundamentals", issuer: "Amazon", img: "assets/certificates/aws-cloud.jpg", icon: "fa-aws" },
      { id: "mct", cat: "strategy", title: "Microsoft Certified Trainer", issuer: "Microsoft", img: "assets/certificates/ms-trainer.jpg", icon: "fa-chalkboard-user" },
      { id: "android", cat: "tech", title: "Android Developer", issuer: "Google", img: "assets/certificates/google-android.jpg", icon: "fa-android" },
      { id: "meta", cat: "strategy", title: "Meta Marketing Professional", issuer: "Meta", img: "assets/certificates/meta-marketing.jpg", icon: "fa-meta" },
      { id: "adobe", cat: "tech", title: "Adobe Professional", issuer: "Adobe", img: "assets/certificates/adobe-professional.jpg", icon: "fa-palette" },
      { id: "mos", cat: "tech", title: "MOS Excel Expert", issuer: "Microsoft", img: "assets/certificates/mos-expert.jpg", icon: "fa-file-excel" },
      { id: "hyperv", cat: "tech", title: "Hyper-V Specialist", issuer: "Microsoft", img: "assets/certificates/ms-specialist.jpg", icon: "fa-server" },
      { id: "ai", cat: "strategy", title: "AI Strategy", issuer: "Helsinki", img: "assets/certificates/ai-elements.jpg", icon: "fa-brain" },
      { id: "seo", cat: "tech", title: "SEO Specialist", issuer: "Google", img: "assets/certificates/seo-expert.jpg", icon: "fa-magnifying-glass-chart" },
      { id: "data", cat: "tech", title: "Data Analyst", issuer: "Microsoft", img: "assets/certificates/data-analysis.jpeg", icon: "fa-chart-pie" },
      { id: "adobeA", cat: "tech", title: "Adobe Associate", issuer: "Adobe", img: "assets/certificates/adobe-associate.jpg", icon: "fa-bezier-curve" },
      { id: "mcp", cat: "tech", title: "MS Professional (MCP)", issuer: "Microsoft", img: "assets/certificates/ms-professional.jpg", icon: "fa-microsoft" },
      { id: "asu", cat: "strategy", title: "ASU Mastery", issuer: "ASU", img: "assets/certificates/learning-mastery.jpg", icon: "fa-graduation-cap" },
      { id: "alison_journalism", cat: "media", title: "Diploma in Journalism", issuer: "Alison", img: "", link: "https://alison.com/certification/check/067880a5c8", icon: "fa-newspaper" },

    ];

    const archiveData = [
      { id: "afaaq", url: "https://web.archive.org/web/20180824100503/http://afaaq.org/", icon: "fa-globe" },
      { id: "banias_gateway", url: "https://web.archive.org/web/20121105113943/http://baniasgateway.net/", icon: "fa-landmark" },
      { id: "banias_media", url: "https://web.archive.org/web/20130518052816/http://baniasnews.com/", icon: "fa-newspaper" },
      { id: "al_feda", url: "https://web.archive.org/web/20130526130101/http://www.alfeda.org/", icon: "fa-feather" },
      { id: "kasemadra", url: "https://web.archive.org/web/20170909163607/http://www.kasemadra.com/", icon: "fa-user" },
      { id: "wafaaonline", url: "https://web.archive.org/web/20141125080407/http://www.wafaaonline.com/", icon: "fa-tower-broadcast" }
    ];

    function renderArchive(query = "") {
      const grid = document.getElementById("archive-grid");
      const count = document.getElementById("archive-count");
      if (!grid) return;

      const lang = document.documentElement.lang || "fr";
      const base = translations.fr;
      const dict = getDict(lang);
      const items = (dict.archive && dict.archive.items) ? dict.archive.items : (base.archive && base.archive.items ? base.archive.items : {});

      const q = (query || "").trim().toLowerCase();

      const filtered = archiveData.filter(a => {
        const meta = items[a.id] || {};
        const title = (meta.title || a.id).toLowerCase();
        const desc = (meta.desc || "").toLowerCase();
        const tag = (meta.tag || "").toLowerCase();
        return !q || title.includes(q) || desc.includes(q) || tag.includes(q) || a.url.toLowerCase().includes(q);
      });

      grid.innerHTML = "";
      filtered.forEach((a) => {
        const meta = items[a.id] || {};
        const title = meta.title || a.id;
        const desc = meta.desc || "";
        const tag = meta.tag || "";
        const card = document.createElement("button");
        card.type = "button";
        card.className = "archive-card text-left p-6 md:p-7 w-full";
        card.innerHTML = `
          <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
              <div class="archive-icon"><i class="fa-solid ${a.icon}"></i></div>
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <h4 class="title-font text-xl md:text-2xl font-black italic text-white leading-tight truncate">${title}</h4>
                </div>
                <p class="text-white/55 text-xs md:text-sm leading-relaxed mt-2">${desc}</p>
              </div>
            </div>
            ${tag ? `<span class="archive-badge"><i class="fa-regular fa-clock"></i> ${tag}</span>` : ``}
          </div>
          <div class="mt-5 flex items-center justify-between gap-4">
            <span class="text-[10px] md:text-[11px] font-black uppercase tracking-[0.35em] text-white/30 truncate">${a.url.replace("https://","")}</span>
            <span class="text-rose-400 text-[10px] md:text-[11px] font-black uppercase tracking-[0.35em] flex items-center gap-2">
              <span data-i18n="archive.open">Open</span>
              <i class="fa-solid fa-arrow-right"></i>
            </span>
          </div>
        `;
        card.addEventListener("mousemove", (e) => {
          const r = card.getBoundingClientRect();
          const mx = ((e.clientX - r.left) / r.width) * 100;
          const my = ((e.clientY - r.top) / r.height) * 100;
          card.style.setProperty("--mx", mx + "%");
          card.style.setProperty("--my", my + "%");
        });
        card.addEventListener("click", () => openArchive(a.url));
        grid.appendChild(card);
      });

      if (count) count.textContent = String(filtered.length);
      // ensure dynamic i18n labels update
      applyTranslations(lang);
      // refresh wayback meta badges if present
      applyArchiveMeta();
    }


    const translations = {
      ar: {

        nav: { about: "من أنا", legacy: "الإرث المهني", archive: "الأرشيف الرقمي", certs: "الاعتمادات المهنية", focus: "مجالات الخبرة", portfolio: "معرض الأعمال", contact: "تواصل" },
        tabs: { home: "الرئيسية", profile: "من أنا", legacy: "الإرث", certs: "الاعتمادات", portfolio: "المعرض", contact: "تواصل" },
        ui: { download_cv: "تحميل السيرة الذاتية", results: "النتائج", close: "إغلاق", open_new: "فتح في نافذة جديدة", dark: "داكن", ultra: "فائق" },
        site: { title: "قاسم عدرا | المرونة الرقمية وهندسة نظم المعلومات", desc: "قاسم عدرا — معماري نظم معلومات واستراتيجي في المرونة الرقمية. حوكمة، أمن، سحابة، وأتمتة." },
        hero: { subtitle: "معماري نظم معلومات • مرونة رقمية", description: "رؤية صقلتها التحديات وتحولت إلى حوكمة تقنية ومعماريات مؤسسية قابلة للتوسع.", cta: "استعراض الاعتمادات", contact: "تواصل معي", signal: "الأنظمة متصلة", tag: "هندسة القرار" },
        
about: {
          kicker: "قائد الحوكمة وهندسة القرار",
          title: "المنهج <span class='text-rose-600'>القيادي</span>",
          img_desc: "معماري نظم معلومات واستراتيجي مرونة رقمية",
          bio1: "لم تكن رحلتي في التكنولوجيا مسارًا وظيفيًا تقليديًا، بل تشكّلًا استراتيجيًا صاغته لحظات مفصلية اختبرت معنى القرار تحت الضغط، ومعنى النظام في قلب الفوضى.",
          bio2: "من تأسيس بنى توثيقية تحفظ الحقيقة في ظروف معقدة، إلى تصميم معماريات مؤسسية وفق أعلى المعايير العالمية (ITIL / AWS)، أعيد تعريف النظام بوصفه أداة سيادة واستقرار مؤسسي. منهجي يرتكز على مبدأ غير قابل للتفاوض: المعلومة مسؤولية سيادية، والنظام هو درعها المؤسسي. اليوم أبني معماريات مضادة للهشاشة — أنظمة تصنع القرار، لا تستهلكه.",
          pillar1: "اعتمادات",
          pillar2: "رؤية SI",
          pillar3: "مرونة",
          stat1: "اعتمادات", stat2: "ميديا", stat3: "رؤية SI"
        },
        legacy: {
          title: "إرث <span class='text-rose-600'>رقمي</span>",
          subtitle: "ذاكرة موثّقة للقيادة المدنية والإعلامية والعمل التوثيقي",
          timeline_title: "الخط الزمني للأثر",
          activity_date_label: "تاريخ النشاط:",
          t_bmc_date: "2011 — 2015",
          t_coastal_title: "المكتب الإعلامي في بانياس الساحل",
          t_coastal_date: "2013 — 2014",
          t_coastal_desc: "تنسيق إعلامي محلي وتوثيق مدني ساحلي في مدينة بانياس.",
          t_flash_date: "2011",
          t_flash_title: "شبكة فلاش",
          t_flash_desc: "شبكة إعلامية — تنسيق استراتيجي وإدارة توزيع المحتوى.",
          t_ugarit_title: "شبكة أوغاريت",
          t_ugarit_desc: "شبكة إعلامية — رصد ومراجعة تحريرية وإشراف على النشر.",
          t_shaam_title: "شبكة شام",
          t_shaam_desc: "تغطية ميدانية وتقارير إعلامية في مناطق الأحداث.",
          t_smc_title: "المركز الإعلامي السوري",
          t_smc_desc: "إنتاج إعلامي مؤسسي وأرشفة منظمة للمحتوى.",
          t_refugees_title: "تنسيقية اللاجئين السوريين في لبنان",
          t_refugees_desc: "تنسيق مجتمعي واستجابة إنسانية منظمة لدعم اللاجئين السوريين في لبنان.",
          t_edu_title: "الهيئة التربوية السورية في لبنان",
          t_edu_desc: "تنسيق إعلامي وإدارة استراتيجية للمحتوى والتواصل المؤسسي.",
          t_ugarit_date: "2011",
          t_shaam_date: "2011 — 2013",
          t_smc_date: "2012 — 2013",
          t_refugees_date: "2013 — 2014",
          t_edu_date: "2015 — 2016",
          t1_title: "مركز بانياس الإعلامي", t1_desc: "توثيق الذاكرة الرقمية المحلية.",
          t2_title: "شبكة شام / أوغاريت / فلاش", t2_desc: "تنسيق إعلامي في بيئات معقدة.",
          t3_title: "تنسيقية اللاجئين السوريين في لبنان", t3_desc: "دعم مجتمعي وأثر إنساني.",
          t4_title: "الهيئة التربوية السورية في لبنان", t4_desc: "منسق إعلامي — إدارة المحتوى والتواصل المؤسسي.",
          doc_tag: "عمل توثيقي",
          book_name: "وردة من دم",
          book_hook: "هذا ليس توثيقًا… بل ذاكرة مقاومة.",
          book_desc: "عمل وثائقي عن بانياس: الذاكرة، العدالة، والإنسان.",
          book_long: "أنا قاسم عدرا (أبو دجانة البانياسي). يوثق هذا العمل ذاكرة المدينة وأهلها عبر شهادات وأرشفة منهجية تحوّل الحدث العابر إلى سجل قابل للتحقق. إنه مسار لحماية الحقيقة وتكريم من رحلوا، عبر نظام يحفظ المعنى ويصون الذاكرة من التلاشي."
,
          lead1: 'لم يكن المسار الإعلامي مرحلة عابرة؛ بل كان تدريباً قيادياً على بناء منظومات معلومات وتوثيق في بيئات معقّدة وعالية المخاطر.',
          lead2: 'الهدف كان واضحاً: تحويل الحدث إلى سجل، والفوضى إلى نظام، والذاكرة إلى بنية رقمية مستدامة قابلة للرجوع والتحقق.',
          t_bmc_desc: 'حفظ الذاكرة المدنية المحلية وأرشفتها رقمياً وفق منهجية توثيق منظمة.',
          t_flash_desc: 'شبكة إعلامية — تنسيق استراتيجي وإدارة توزيع المحتوى.',
          t_ugarit_desc: 'شبكة إعلامية — رصد ومراجعة تحريرية وإشراف على النشر.',
          t_shaam_desc: 'تغطية ميدانية وتقارير إعلامية في مناطق الأحداث.',
          t_smc_desc: 'إنتاج إعلامي مؤسسي وأرشفة منظمة للمحتوى.',
          t_refugees_desc: 'تنسيق مجتمعي واستجابة إنسانية منظمة لدعم اللاجئين السوريين في لبنان.',
          t_edu_desc: 'تنسيق إعلامي وإدارة استراتيجية للمحتوى والتواصل المؤسسي.'
},
        archive: {
          title: "الأرشيف <span class='text-rose-600'>الرقمي</span>",
          book_footer_right: "أرشفة / أنظمة",
          book_footer_left: "قاسم عدرا",
          subtitle: "روابط موثّقة من أرشيف الويب — مبادرات إعلامية وأعمال توثيقية",
          search_label: "ابحث داخل الأرشيف",
          results: "نتائج",
          tip_kicker: "معلومة سريعة",
          tip: "اضغط على أي بطاقة لفتحها داخل العارض السينمائي.",
          actions: { open: "فتح الأرشيف", view: "عرض الأرشيف", source: "عرض المصدر" },
          items: {
            baniasnews: { title: "بوابة بانياس الساحلية", tag: "2012", desc: "بوابة محلية لمدينة بانياس — لقطة مؤرشفة." },
            kasem: { title: "مدونة قاسم عدرا", tag: "2017", desc: "المدونة الشخصية — لقطة Wayback مؤرشفة." },
            feda: { title: "جريدة الفداء", tag: "2013", desc: "أرشيف صحفي — لقطة محفوظة." },
            baniasinfo: { title: "مركز بانياس الإعلامي", tag: "2013", desc: "ذاكرة إعلامية موثّقة — لقطة مؤرشفة." },
            afaaq: { title: "مركز آفاق سوريا", tag: "2018", desc: "أخبار ومقالات — لقطة مؤرشفة." },
            wafaa: { title: "شبكة وفاء الإخبارية", tag: "2014", desc: "أرشيف شبكة وفاء — لقطة Wayback محفوظة." }
          }
        },
        focus: {
          title: "مجالات <span class='text-rose-600'>الخبرة</span>",
          items: {
            arch_title: "هندسة الأنظمة", arch_desc: "تصميم معماريات مؤسسية آمنة وقابلة للتوسع.",
            res_title: "المرونة الرقمية", res_desc: "استمرارية الأعمال والتعافي من الكوارث وفق أفضل الممارسات.",
            cloud_title: "الحوسبة السحابية", cloud_desc: "تصميم وتشغيل منصات سحابية بكفاءة وتشغيلية عالية.",
            ai_title: "استراتيجية الذكاء", ai_desc: "إدماج الذكاء الاصطناعي ضمن الحوكمة وعمليات القرار."
          }
        },
        
        portfolio: {
          title: "معرض التصميم <span class='text-rose-600'>الإبداعي</span>",
          lead: "مجموعة مختارة من الأعمال — هويات، شعارات، ورسوم توضيحية.",
          items: {
            almukhl: { title: "Almukhl – المُكحل", sub: "تصميم شعار • علامة إسلامية • 2022", desc: "شعار حديث مستوحى من فكرة البصيرة والوضوح. التكوين الدائري يرمز للإحاطة والكمال، مع رمز قوي مناسب للطباعة والمنصات الرقمية." },
            middle_east: { title: "Middle East Food Trading", sub: "هوية مؤسسية • تجارة غذائية • 2021", desc: "هوية ثنائية اللغة تجمع العربي والإنجليزي، مع رمز يعكس الزراعة والتجارة والثقة والانتشار." },
            sanad: { title: "SANAD", sub: "تصميم شعار • مبادرة تنموية • 2025", desc: "شعار رمزي يعبّر عن الدعم والنمو عبر حركة العناصر وتوازنها، بألوان تعزز فكرة التمكين والتقدم." },
            eva_adam: { title: "Eva & Adam", sub: "رسوم توضيحية • مفهوم فني • 2020", desc: "عمل فني بتكوين متناظر يعكس الازدواجية والتوازن، مع تفاصيل خطية وزخرفية عالية." },
            feda_news: { title: "FedaNews", sub: "هوية إعلامية • تصميم شعار • 2016", desc: "شعار إعلامي معاصر يعتمد على خطوط انسيابية لتجسيد حركة الخبر وسرعة تدفق المعلومات، مناسب للسوشال ميديا." }
          }
        },

      certs: {
          title: "محفظة <span class='text-rose-600'>اعتمادات</span>",
          subtitle: "موثّقة بـ 15 اعتماداً عالمياً",
          filters: { all: "الكل", strategy: "استراتيجية", tech: "تقني", media: "إعلام" },
          no_results_title: "لا نتائج",
          no_results_desc: "جرّب كلمات أخرى (Microsoft, AWS, ITIL)."
        },
        contact: { title: "لنصنع <span class='text-rose-600'>الأثر</span> معاً", desc: "متاح للاستشارات الاستراتيجية، الحوكمة، والتحول الرقمي." },
        footer: { mission: "هندسة المرونة الرقمية والحوكمة المؤسسية وفق معايير عالمية قابلة للقياس.", explore: "استكشف", location_title: "الموقع", location_val: "بانياس، سوريا / فرنسا", status: "الأنظمة متصلة وآمنة", tagline: "المرونة الرقمية • قاسم عدرا", copy: "جميع الحقوق محفوظة 2026 قاسم.", top: "للأعلى" },
        loader: { status: ["جاري التحقق", "تحميل المعمارية", "بدء النظام", "جاهز"], subtext: ["تأمين الروابط...", "رسم خرائط العقد...", "مزامنة البيانات...", "مرحباً بك."] },
        certs_desc: {
          itil: "الريادة في إدارة الخدمات التقنية وصياغة الرؤى الاستراتيجية.", aws: "هندسة السحابة المتقدمة باستخدام حلول أمازون.",
          mct: "نقل المعرفة والتدريب التقني المعتمد من مايكروسوفت.", android: "تطوير تطبيقات الجوال المتقدمة.",
          meta: "احتراف التسويق الرقمي واستراتيجيات Meta.", adobe: "التميز في التصميم الإبداعي والإنتاج البصري.",
          mos: "خبير معتمد في أدوات الإنتاجية المكتبية.", hyperv: "تخصص في تقنيات المحاكاة الافتراضية.",
          ai: "رسم خرائط الطريق لدمج الذكاء الاصطناعي.", seo: "تحسين ظهور المنصات الرقمية وتصدر نتائج البحث.",
          data: "تحليل البيانات الضخمة للقرارات الاستراتيجية.", adobeA: "المعايير الأساسية للتصميم من Adobe.",
          mcp: "الاعتماد المهني من مايكروسوفت لإدارة النظم.", asu: "إتقان استراتيجيات التعلم من ASU.",
          cyber: "حماية الأصول الرقمية وأمن المعلومات."
        }
      },
      en: {

        nav: { about: "Profile", legacy: "Legacy", archive: "Archive", certs: "Credentials", focus: "Expertise", portfolio: "Gallery", contact: "Contact" },
        tabs: { home: "Home", profile: "Profile", legacy: "Legacy", certs: "Certs", portfolio: "Gallery", contact: "Contact" },
        ui: { download_cv: "Download CV", results: "Results", close: "Close", open_new: "Open", dark: "Dark", ultra: "Ultra" },
        site: { title: "QASSEM | Digital Resilience & Enterprise Architecture", desc: "Qassem Adra — enterprise architect and digital resilience strategist. Governance, security, cloud and automation." },
        hero: { subtitle: "Enterprise Architect • Digital Resilience", description: "A vision forged by challenges, transformed into governance.", cta: "View Credentials", contact: "Contact Me", signal: "Systems Online", tag: "Decision Architecture" },
        
about: {
          kicker: "Decision Architecture",
          title: "Strategic <span class='text-rose-600'>Method</span>",
          img_desc: "Information Systems Architect & Digital Resilience Strategist",
          bio1: "My journey in technology was never a conventional career path — it was a strategic formation shaped by defining moments where decisions were made under pressure and order was forged from chaos.",
          bio2: "From documentation infrastructures that safeguarded truth in complex environments to enterprise architectures aligned with global standards (ITIL / AWS), I treat systems as instruments of institutional stability and sovereignty. My methodology is non-negotiable: Information is a sovereign responsibility — and systems are its institutional shield. Today, I design anti-fragile architectures that shape decisions rather than react to them.",
          pillar1: "Certifications",
          pillar2: "360° Vision",
          pillar3: "Resilience",
          stat1: "Certs", stat2: "Media", stat3: "Vision"
        },
        legacy: {
          title: "Digital <span class='text-rose-600'>Legacy</span>",
          subtitle: "Archive of civic and media leadership",
          timeline_title: "Impact Timeline",
          activity_date_label: "Activity:",
          t_bmc_date: "2011 — 2015",
          t_coastal_title: "Banias Coastal Media Office",
          t_coastal_date: "2013 — 2014",
          t_coastal_desc: "Local media coordination and coastal civic documentation in Banias.",
          t_flash_date: "2011",
          t_flash_title: "Flash Network",
          t_flash_desc: "Media network — strategic coordination and content distribution management.",
          t_ugarit_title: "Ugarit Network",
          t_ugarit_desc: "Media network — monitoring, editorial oversight, and publication governance.",
          t_shaam_title: "Shaam Network",
          t_shaam_desc: "Field reporting and frontline media coverage.",
          t_smc_title: "Syrian Media Center",
          t_smc_desc: "Institutional media production and structured content archiving.",
          t_refugees_title: "Syrian Refugees Coordination – Lebanon",
          t_refugees_desc: "Community coordination and structured humanitarian response.",
          t_edu_title: "Syrian Educational Authority – Lebanon",
          t_edu_desc: "Media coordination, strategic content management, and institutional communication.",
          t_ugarit_date: "2011",
          t_shaam_date: "2011 — 2013",
          t_smc_date: "2012 — 2013",
          t_refugees_date: "2013 — 2014",
          t_edu_date: "2015 — 2016",
          t1_title: "Banias Media Center", t1_desc: "Preserving local digital memory.",
          t2_title: "Shaam / Ugarit / Flash", t2_desc: "Media coordination in complex environments.",
          t3_title: "Syrian Refugees Coordination – Lebanon", t3_desc: "Community support and humanitarian impact.",
          t4_title: "Syrian Educational Authority in Lebanon", t4_desc: "Media Coordinator — content management and institutional communication.",
          doc_tag: "Documentary Work",
          book_name: "Rose de Sang",
          book_hook: "This is not documentation — it is a memory of resistance.",
          book_desc: "A documentary work on Banias: memory, justice, and people.",
          book_long: "I am Qassem Adra (Abou Dujana al‑Banayasi). This work preserves the city’s memory through testimonies and disciplined archiving that turns fleeting events into verifiable records. It is a commitment to protect truth and honor those lost — through systems that keep meaning intact and memory alive."
,
          lead1: 'This journey was never about visibility — it was about structure: building documentation systems, information governance, and resilient digital memory under pressure.',
          lead2: 'The objective: turn events into records, chaos into process, and collective memory into sustainable infrastructure.',
          t_bmc_desc: 'Preservation and structured digital archiving of local civic memory.',
          t_flash_desc: 'Media network — strategic coordination and content distribution management.',
          t_ugarit_desc: 'Media network — monitoring, editorial oversight, and publication governance.',
          t_shaam_desc: 'Field reporting and frontline media coverage.',
          t_smc_desc: 'Institutional media production and structured content archiving.',
          t_refugees_desc: 'Community coordination and structured humanitarian response.',
          t_edu_desc: 'Media coordination, strategic content management, and institutional communication.'
},
        archive: {
          title: "Digital <span class='text-rose-600'>Archive</span>",
          book_footer_right: "Archive / Systems",
          book_footer_left: "Qassem Adra",
          subtitle: "Verified web archive links — media initiatives & documentary work",
          search_label: "Search the archive",
          results: "results",
          tip_kicker: "Tip",
          tip: "Tap any card to open it in the cinematic viewer.",
          actions: { open: "Open Archive", view: "View Archive", source: "View Source" },
          items: {
            baniasnews: { title: "Portail Côtier de Banias", tag: "2012", desc: "Portail local de Banias — capture archivée." },
            kasem: { title: "Blog de Qassem Adra", tag: "2017", desc: "Blog personnel — capture Wayback archivée." },
            feda: { title: "Journal Al-Fidaa", tag: "2013", desc: "Archive de presse — capture conservée." },
            baniasinfo: { title: "Centre des médias de Banias", tag: "2013", desc: "Mémoire médiatique documentée — capture archivée." },
            afaaq: { title: "Centre Afaaq Syrie", tag: "2018", desc: "Actualités & articles — capture archivée." },
            wafaa: { title: "Réseau d’actualités Wafaa", tag: "2014", desc: "Archive du réseau Wafaa — capture Wayback conservée." }
          }
        },
        focus: {
          title: "Domains of <span class='text-rose-600'>Expertise</span>",
          items: {
            arch_title: "Enterprise Architecture", arch_desc: "Designing complex enterprise infrastructures.",
            res_title: "Resilience", res_desc: "DR / business continuity strategies.",
            cloud_title: "Cloud Computing", cloud_desc: "Efficient AWS/Azure systems.",
            ai_title: "AI Strategy", ai_desc: "Integrating AI into governance."
          }
        },
        
        portfolio: {
          title: "Creative <span class='text-rose-600'>Portfolio</span>",
          lead: "A curated selection of branding, identity and illustration work.",
          items: {
            almukhl: { title: "Almukhl – Al-Mukhl", sub: "Logo Design • Islamic Brand • 2022", desc: "A modern mark inspired by clarity and insight. The circular construction suggests completeness, with a strong symbol that performs beautifully in print and digital." },
            middle_east: { title: "Middle East Food Trading", sub: "Corporate Identity • Food Trading • 2021", desc: "A bilingual identity (Arabic/English) with a symbol that conveys agriculture, trade, trust and reach." },
            sanad: { title: "SANAD", sub: "Logo Design • Development Initiative • 2025", desc: "A symbolic logo expressing support and growth through balanced forms and an empowering color system." },
            eva_adam: { title: "Eva & Adam", sub: "Illustration • Artistic Concept • 2020", desc: "A symmetrical composition reflecting duality and balance, enriched with refined line work and ornamental detail." },
            feda_news: { title: "FedaNews", sub: "Media Identity • Logo Design • 2016", desc: "A contemporary media logo using flowing strokes to evoke news momentum and fast information flow — optimized for social platforms." }
          }
        },

      certs: {
          title: "Certified <span class='text-rose-600'>Portfolio</span>",
          subtitle: "Validated by 15 pillars of excellence",
          filters: { all: "ALL", strategy: "STRATEGY", tech: "TECH", media: "MEDIA" },
          no_results_title: "No results",
          no_results_desc: "Try another keyword (e.g., Microsoft, AWS, ITIL)."
        },
        contact: { title: "Let’s Create <span class='text-rose-600'>Impact</span>", desc: "Available for strategic consulting and transformation." },
        footer: { mission: "Architecting digital resilience and enterprise governance at global standards.", explore: "EXPLORE", location_title: "LOCATION", location_val: "Banias, Syria / France", status: "Systems Online & Secure", tagline: "Digital Resilience • QASSEM ADRA", copy: "© 2026 QASSEM ADRA.", top: "TOP" },
        loader: { status: ["VERIFYING", "LOADING ARCHITECTURE", "INITIALIZING", "READY"], subtext: ["Securing links...", "Mapping nodes...", "Syncing...", "Welcome."] },
        certs_desc: { itil: "Leadership in ITSM.", aws: "Cloud architecture.", mct: "Certified Trainer.", android: "Android Dev.", meta: "Marketing Professional.", adobe: "Creative Design.", mos: "Office Expert.", hyperv: "Virtualization.", ai: "AI Strategy.", seo: "SEO Mastery.", data: "Data Analyst.", adobeA: "Adobe Associate.", mcp: "MS Professional.", asu: "ASU Mastery.", cyber: "Cybersecurity." }
      },
      fr: {

        nav: { about: "Profil", legacy: "Héritage", archive: "Archive", certs: "Certifications", focus: "Maîtrise", portfolio: "Galerie", contact: "Contact" },
        tabs: { home: "Accueil", profile: "Profil", legacy: "Héritage", certs: "Certifs", portfolio: "Galerie", contact: "Contact" },
        ui: { download_cv: "Télécharger CV", results: "Résultats", close: "Fermer", open_new: "Ouvrir", dark: "Sombre", ultra: "Ultra" },
        site: { title: "QASSEM | Résilience numérique & Architecture SI", desc: "Qassem Adra — Architecte SI & stratège en résilience numérique. Gouvernance, sécurité, cloud et automatisation." },
        hero: { subtitle: "Architecte SI • Résilience Numérique", description: "Une vision forgée par les défis, transformée en gouvernance technologique.", cta: "Voir Certifications", contact: "Contactez-moi", signal: "Systèmes en ligne", tag: "Architecture de Décision" },
        
about: {
          kicker: "Architecture de Décision",
          title: "Méthode <span class='text-rose-600'>Stratégique</span>",
          img_desc: "Architecte SI & Stratège en Résilience Numérique",
          bio1: "Mon parcours technologique n’a jamais été une trajectoire professionnelle conventionnelle, mais une construction stratégique façonnée par des contextes où la décision se prenait sous pression et où l’ordre devait émerger du chaos.",
          bio2: "Des infrastructures de documentation protégeant la vérité aux architectures SI alignées sur les standards internationaux (ITIL / AWS), j’aborde le système comme un instrument de stabilité et de souveraineté institutionnelle. Principe non négociable : l’information est une responsabilité souveraine, et les systèmes en sont le bouclier institutionnel. Aujourd’hui, je conçois des architectures anti-fragiles qui façonnent la décision au lieu de la subir.",
          pillar1: "Certifications",
          pillar2: "Vision 360°",
          pillar3: "Résilience",
          stat1: "Certifs", stat2: "Médias", stat3: "Vision SI"
        },
        legacy: {
          title: "Héritage <span class='text-rose-600'>Digital</span>",
          subtitle: "Archive du Leadership Civil et Média",
          timeline_title: "Chronologie de l'Impact",
          activity_date_label: "Période :",
          t_bmc_date: "2011 — 2015",
          t_coastal_title: "Bureau médiatique – Banias Côte",
          t_coastal_date: "2013 — 2014",
          t_coastal_desc: "Coordination médiatique locale et gestion de contenu en zone côtière.",
          t_flash_date: "2011",
          t_flash_title: "Réseau Flash",
          t_flash_desc: "Réseau médiatique — coordination stratégique et gestion de la diffusion des contenus.",
          t_ugarit_title: "Réseau Ougarit",
          t_ugarit_desc: "Réseau médiatique — suivi éditorial et supervision des publications.",
          t_shaam_title: "Réseau Shaam",
          t_shaam_desc: "Reportage de terrain et couverture médiatique en zones sensibles.",
          t_smc_title: "Centre Médiatique Syrien",
          t_smc_desc: "Production médiatique institutionnelle et archivage structuré.",
          t_refugees_title: "Coordination des réfugiés syriens au Liban",
          t_refugees_desc: "Coordination communautaire et réponse humanitaire organisée.",
          t_edu_title: "Autorité éducative syrienne au Liban",
          t_edu_desc: "Coordination médiatique et gestion stratégique de la communication institutionnelle.",
          t_ugarit_date: "2011",
          t_shaam_date: "2011 — 2013",
          t_smc_date: "2012 — 2013",
          t_refugees_date: "2013 — 2014",
          t_edu_date: "2015 — 2016",
          t1_title: "Banias Media Center", t1_desc: "Préservation de la mémoire numérique locale.",
          t2_title: "Shaam / Ugarit / Flash", t2_desc: "Coordination média dans des environnements complexes.",
          t3_title: "Coordination des réfugiés syriens au Liban", t3_desc: "Soutien communautaire et impact humanitaire.",
          t4_title: "Autorité éducative syrienne au Liban", t4_desc: "Coordinateur média — gestion de contenu et communication institutionnelle.",
          doc_tag: "Travail Documentaire",
          book_name: "Rose de Sang",
          book_hook: "Ce n’est pas un simple document — c’est une mémoire de résistance.",
          book_desc: "Un ouvrage documentaire sur Banias : mémoire, justice et vies humaines.",
          book_long: "Je suis Qassem Adra (Abou Dujana al‑Banayasi). Cet ouvrage préserve la mémoire de la ville à travers des témoignages et une archivage rigoureux qui transforme l’éphémère en traces vérifiables. C’est un engagement à protéger la vérité et à honorer les disparus — par des systèmes qui préservent le sens et maintiennent la mémoire."
,
          lead1: 'Ce parcours n’était pas une simple présence médiatique : il s’agissait de structurer l’information, de documenter, et de bâtir une mémoire numérique résiliente sous contrainte.',
          lead2: 'Objectif : transformer l’événement en trace, le chaos en processus, et la mémoire collective en infrastructure durable.',
          t_bmc_desc: 'Préservation et archivage numérique structuré de la mémoire civique locale.',
          t_flash_desc: 'Réseau médiatique — coordination stratégique et gestion de la diffusion des contenus.',
          t_ugarit_desc: 'Réseau médiatique — suivi, supervision éditoriale et gouvernance des publications.',
          t_shaam_desc: 'Reportage de terrain et couverture médiatique en zones sensibles.',
          t_smc_desc: 'Production médiatique institutionnelle et archivage structuré des contenus.',
          t_refugees_desc: 'Coordination communautaire et réponse humanitaire organisée.',
          t_edu_desc: 'Coordination médiatique, gestion stratégique des contenus et communication institutionnelle.'
},
        archive: {
          title: "Archive <span class='text-rose-600'>Numérique</span>",
          book_footer_right: "Archives / Systèmes",
          book_footer_left: "Qassem Adra",
          subtitle: "Liens vérifiés issus des archives du web — projets médiatiques & travaux documentaires",
          search_label: "Rechercher dans l’archive",
          results: "résultats",
          tip_kicker: "Astuce",
          tip: "Touchez une carte pour l’ouvrir dans le viewer cinématique.",
          actions: { open: "Ouvrir l’archive", view: "Consulter l’archive", source: "Voir la source" },
          items: {
            baniasnews: { title: "Banias Coastal Gateway", tag: "2012", desc: "Local Banias portal — archived snapshot." },
            kasem: { title: "Qassem Adra Blog", tag: "2017", desc: "Personal blog — archived Wayback snapshot." },
            feda: { title: "Al-Fidaa Newspaper", tag: "2013", desc: "Press archive — preserved snapshot." },
            baniasinfo: { title: "Banias Media Center", tag: "2013", desc: "Documented media record — archived snapshot." },
            afaaq: { title: "Afaaq Syria Research Center", tag: "2018", desc: "News & articles — archived snapshot." },
            wafaa: { title: "Wafaa News Network", tag: "2014", desc: "Wafaa network archive — preserved Wayback snapshot." }
          }
        },
        focus: {
          title: "Domaines de <span class='text-rose-600'>Maîtrise</span>",
          items: {
            arch_title: "Architecture SI", arch_desc: "Conception d'infrastructures d'entreprise complexes.",
            res_title: "Résilience", res_desc: "Stratégies de reprise après sinistre.",
            cloud_title: "Cloud Computing", cloud_desc: "Gestion efficace AWS/Azure.",
            ai_title: "Stratégie IA", ai_desc: "Intégration de l'IA en entreprise."
          }
        },
        
        portfolio: {
          title: "Portfolio <span class='text-rose-600'>Créatif</span>",
          lead: "Une sélection soignée de projets d’identité, de branding et d’illustration.",
          items: {
            almukhl: { title: "Almukhl", sub: "Logo • Marque islamique • 2022", desc: "Un symbole moderne inspiré par la clarté et la vision. La géométrie circulaire évoque l’achèvement et s’adapte parfaitement au print et au digital." },
            middle_east: { title: "Middle East Food Trading", sub: "Identité corporate • Trading alimentaire • 2021", desc: "Une identité bilingue (arabe/anglais) avec un signe qui exprime agriculture, commerce, confiance et rayonnement." },
            sanad: { title: "SANAD", sub: "Logo • Initiative de développement • 2025", desc: "Un logo symbolique traduisant le soutien et la croissance via un équilibre des formes et une palette valorisante." },
            eva_adam: { title: "Eva & Adam", sub: "Illustration • Concept artistique • 2020", desc: "Une composition symétrique explorant la dualité et l’équilibre, avec un travail de ligne fin et des détails ornementaux." },
            feda_news: { title: "FedaNews", sub: "Identité média • Logo • 2016", desc: "Un logo média contemporain aux courbes dynamiques, traduisant vitesse et circulation de l’information—idéal pour les réseaux sociaux." }
          }
        },

      certs: {
          title: "Portfolio <span class='text-rose-600'>Certifié</span>",
          subtitle: "Validé par 15 Piliers d'Excellence Mondiale",
          filters: { all: "TOUT", strategy: "STRATÉGIE", tech: "TECH", media: "MÉDIA" },
          no_results_title: "Aucun résultat",
          no_results_desc: "Essayez un autre mot-clé (Microsoft, AWS, ITIL)."
        },
        contact: { title: "Créons un <span class='text-rose-600'>Impact</span>", desc: "Disponible pour conseil stratégique et transformation." },
        footer: { mission: "Architecture de la résilience numérique et gouvernance d'entreprise aux standards mondiaux.", explore: "EXPLORER", location_title: "LOCALISATION", location_val: "Banias, Syrie / France", status: "Systèmes en ligne et sécurisés", tagline: "Résilience Numérique • QASSEM ADRA", copy: "© 2026 QASSEM ADRA.", top: "HAUT" },
        loader: { status: ["VÉRIFICATION", "CHARGEMENT", "INITIALISATION", "PRÊT"], subtext: ["Sécurisation...", "Cartographie...", "Synchronisation...", "Bienvenue."] },
        certs_desc: { itil: "Leadership en ITSM.", aws: "Architecture cloud.", mct: "Transmission d'expertise.", android: "Apps mobiles.", meta: "Marketing Meta.", adobe: "Design créatif.", mos: "Productivité Office.", hyperv: "Virtualisation.", ai: "Stratégie IA.", seo: "Optimisation SEO.", data: "Analyse données.", adobeA: "Design Adobe.", mcp: "Systèmes Microsoft.", asu: "Stratégies ASU.", cyber: "Protection numérique." }
      }
    };

    
    // Icon helper: some cert icons are Brand icons (Font Awesome "fa-brands"), others are Solid ("fa-solid")
    const BRAND_ICON_SET = new Set([
      'fa-aws','fa-android','fa-meta','fa-microsoft','fa-github','fa-linkedin-in','fa-x-twitter','fa-facebook-f','fa-instagram','fa-snapchat'
    ]);
    function iconClass(icon){
      const base = BRAND_ICON_SET.has(icon) ? 'fa-brands' : 'fa-solid';
      return base + ' ' + icon;
    }
    let currentCertIdx = 0;
    let activeFilter = 'all';
    let activeSearch = '';

    function getDict(lang) { return translations[lang] || translations.en || translations.fr; }
    function getValue(path, obj) { return path.split('.').reduce((acc, k) => (acc && acc[k] != null ? acc[k] : null), obj); }

    function init() {
      createMatrixBackground();
      renderCerts();
      setupNav();
      setupMagnetic();
      setupCertSearch();
      setupArchiveSearch();
      renderArchive("");
      // Preloader
      let progress = { value: 0 };
      const tl = gsap.timeline();
      tl.to(progress, {
        value: 100, duration: 2.35, ease: "power3.inOut",
        onUpdate: () => {
          const v = Math.floor(progress.value);
          document.getElementById('loader-bar').style.width = v + "%";
          document.getElementById('loader-num').innerText = v < 10 ? "0" + v : v;

          const lang = document.documentElement.lang || 'fr';
          const dict = getDict(lang);
          const l = dict.loader || getDict('fr').loader;

          if (v < 30) { document.getElementById('loader-status').innerText = l.status[0]; document.getElementById('loader-subtext').innerText = l.subtext[0]; }
          else if (v < 60) { document.getElementById('loader-status').innerText = l.status[1]; document.getElementById('loader-subtext').innerText = l.subtext[1]; }
          else if (v < 90) { document.getElementById('loader-status').innerText = l.status[2]; document.getElementById('loader-subtext').innerText = l.subtext[2]; }
          else { document.getElementById('loader-status').innerText = l.status[3]; document.getElementById('loader-subtext').innerText = l.subtext[3]; }
        }
      })
      .to("#loader", { y: "-100%", duration: 1.05, ease: "expo.inOut" })
      .call(() => {
        gsap.to("#site-content", { opacity: 1, duration: 0.7 });
        gsap.from("#hero-master > *", { opacity:0, y:22, stagger:0.08, duration:1.0, ease:"power4.out" });
      });

      gsap.registerPlugin(ScrollTrigger);
      gsap.utils.toArray('section').forEach(sec => {
        gsap.from(sec, { scrollTrigger: { trigger: sec, start: "top 88%" }, opacity: 0, y: 26, duration: 0.9 });
      });

      initLanguage();
    }

    function applyTranslations(lang) {
      const base = translations.fr;
      const dict = getDict(lang);

      document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        const v = getValue(key, dict) ?? getValue(key, base);
        if (v == null) return;

        // Prefer textContent for safety; allow HTML only when the translation contains markup.
        if (typeof v === 'string' && /<\s*[a-z][\s\S]*>/i.test(v)) el.innerHTML = v;
        else el.textContent = String(v);
      });

      applySiteMeta(lang);
    }


    function applySiteMeta(lang){
      const base = getDict('fr');
      const dict = getDict(lang);
      const title = (dict.site && dict.site.title) || (base.site && base.site.title) || document.title;
      const desc  = (dict.site && dict.site.desc)  || (base.site && base.site.desc)  || '';

      document.title = title;
      const mt = document.getElementById('meta-title');
      if (mt) mt.textContent = title;

      const md = document.querySelector('meta[name="description"]');
      if (md) md.setAttribute('content', desc);

      const ogt = document.querySelector('meta[property="og:title"]');
      if (ogt) ogt.setAttribute('content', title);
      const ogd = document.querySelector('meta[property="og:description"]');
      if (ogd) ogd.setAttribute('content', desc);
    }
    
    function detectBrowserLanguage() {
      const navLang = (navigator.language || navigator.userLanguage || 'fr').toLowerCase();
      if (navLang.startsWith('ar')) return 'ar';
      if (navLang.startsWith('en')) return 'en';
      if (navLang.startsWith('fr')) return 'fr';
      // fallback: try primary subtag (e.g. "fr-fr" already handled by startsWith('fr'))
      return 'fr';
    }

    function initLanguage() {
      try {
        const saved = localStorage.getItem('site_lang');
        if (saved) { setLanguage(saved); return; }
      } catch(e) { /* ignore */ }

      setLanguage(detectBrowserLanguage());
    }

function setLanguage(lang) {
      try { localStorage.setItem('site_lang', lang); } catch(e) { /* ignore */ }
      document.documentElement.lang = lang;
      document.documentElement.dir = (lang === 'ar' ? 'rtl' : 'ltr');
      document.querySelectorAll('#lang-btns button').forEach(b => b.classList.toggle('active-lang', b.dataset.lang === lang));
      applyTranslations(lang);
      renderCerts();
      const a = document.getElementById('archive-search');
      renderArchive(a ? (a.value || "") : "");
          applyArchiveMeta();
    }

    // --- CERTS: filter + search + counter ---
    function setupCertSearch() {
      const input = document.getElementById('cert-search');
      if (!input) return;
      input.addEventListener('input', () => {
        activeSearch = (input.value || '').trim().toLowerCase();
        renderCerts();
      });
    }

function setupArchiveSearch() {
      const input = document.getElementById('archive-search');
      if (!input) return;
      input.addEventListener('input', () => {
        renderArchive(input.value || "");
        applyArchiveMeta();
      });
    }

function matchesSearch(cert) {
      if (!activeSearch) return true;
      const hay = `${cert.title} ${cert.issuer} ${cert.cat} ${cert.id}`.toLowerCase();
      return hay.includes(activeSearch);
    }

    function filteredCerts() {
      return certsData
        .filter(c => activeFilter === 'all' ? true : c.cat === activeFilter)
        .filter(matchesSearch);
    }

    function renderCerts() {
      const container = document.getElementById('certs-container');
      const empty = document.getElementById('cert-empty');
      const countEl = document.getElementById('cert-count');

      container.innerHTML = '';
      const list = filteredCerts();

      countEl.innerText = String(list.length);

      if (list.length === 0) {
        empty.classList.remove('hidden');
        return;
      }
      empty.classList.add('hidden');

      list.forEach((cert) => {
        const card = document.createElement('div');
        card.className = "premium-card p-6 md:p-10 group cursor-pointer flex flex-col justify-between";
        card.onclick = () => openViewer(certsData.indexOf(cert));
        card.innerHTML = `
          <div class="flex justify-between items-start mb-6">
            <div class="feature-icon-v2"><i class="${iconClass(cert.icon)}"></i></div>
          </div>
          <div class="text-start">
            <span class="text-[8px] font-black tracking-widest text-gray-500 uppercase block mb-1">${cert.issuer}</span>
            <h3 class="title-font text-xl md:text-2xl font-black italic group-hover:text-rose-500 transition-colors leading-tight">${cert.title}</h3>
          </div>
        `;
        container.appendChild(card);
      });
    }

    function filterCerts(cat) {
      activeFilter = cat;
      document.querySelectorAll('#cert-filters button').forEach(b => b.classList.toggle('active-filter', b.dataset.filter === cat));
      renderCerts();
      haptic();
    }

    function openViewer(index) {
      currentCertIdx = index;
      const cert = certsData[index];
      const modal = document.getElementById('viewer-modal');
      const lang = document.documentElement.lang || 'fr';
      const dict = getDict(lang);

      const viewerImg = document.getElementById('viewer-img');
      const linkWrap = document.getElementById('viewer-linkwrap');
      const linkEl = document.getElementById('viewer-link');

      if (cert.img && String(cert.img).trim().length > 0) {
        viewerImg.classList.remove('hidden');
        viewerImg.src = cert.img;
        if (linkWrap) linkWrap.classList.add('hidden');
      } else {
        viewerImg.classList.add('hidden');
        viewerImg.removeAttribute('src');
        if (linkWrap) linkWrap.classList.remove('hidden');
        if (linkEl) linkEl.href = cert.link ? cert.link : '#';
      }
      document.getElementById('viewer-title').innerText = cert.title;
      document.getElementById('viewer-issuer').innerText = cert.issuer;
      document.getElementById('viewer-description').innerText = (dict.certs_desc && dict.certs_desc[cert.id]) ? dict.certs_desc[cert.id] : "";
      document.getElementById('viewer-index').innerText = `${(index+1).toString().padStart(2, '0')} / ${certsData.length}`;

      modal.style.display = 'flex';
      gsap.to(modal, { opacity: 1, duration: 0.25 });

      document.body.style.overflow = 'hidden';
      gsap.from(".viewer-card", { scale: 0.9, opacity: 0, duration: 0.45, ease: "back.out(1.2)" });
      haptic();
    }

    function changeCert(dir, e) {
      e.stopPropagation();
      currentCertIdx = (currentCertIdx + dir + certsData.length) % certsData.length;

      const cert = certsData[currentCertIdx];
      const lang = document.documentElement.lang || 'fr';
      const dict = getDict(lang);

      gsap.to(".viewer-card", {
        opacity: 0, scale: 0.97, duration: 0.18,
        onComplete: () => {
          const viewerImg = document.getElementById('viewer-img');
      const linkWrap = document.getElementById('viewer-linkwrap');
      const linkEl = document.getElementById('viewer-link');

      if (cert.img && String(cert.img).trim().length > 0) {
        viewerImg.classList.remove('hidden');
        viewerImg.src = cert.img;
        if (linkWrap) linkWrap.classList.add('hidden');
      } else {
        viewerImg.classList.add('hidden');
        viewerImg.removeAttribute('src');
        if (linkWrap) linkWrap.classList.remove('hidden');
        if (linkEl) linkEl.href = cert.link ? cert.link : '#';
      }
          document.getElementById('viewer-title').innerText = cert.title;
          document.getElementById('viewer-issuer').innerText = cert.issuer;
          document.getElementById('viewer-description').innerText = (dict.certs_desc && dict.certs_desc[cert.id]) ? dict.certs_desc[cert.id] : "";
          document.getElementById('viewer-index').innerText = `${(currentCertIdx+1).toString().padStart(2, '0')} / ${certsData.length}`;
          gsap.to(".viewer-card", { opacity: 1, scale: 1, duration: 0.24 });
        }
      });
      haptic();
    }

    function closeViewer() {
      gsap.to("#viewer-modal", {
        opacity: 0, duration: 0.18,
        onComplete: () => {
          document.getElementById('viewer-modal').style.display = 'none';
          document.body.style.overflow = 'auto';
        }
      });
      haptic();
    }

    // --- Archive viewer ---
    function openArchive(url) {
      const modal = document.getElementById('archive-modal');
      const frame = document.getElementById('archive-frame');
      const urlEl = document.getElementById('archive-url');
      const openNew = document.getElementById('archive-open-new');

      urlEl.textContent = url;
      openNew.href = url;
      frame.src = url;

      modal.style.display = 'flex';
      gsap.to(modal, { opacity: 1, duration: 0.22 });
      document.body.style.overflow = 'hidden';
      haptic();
    }

    function openArchiveFromInput() {
      const input = document.getElementById('archive-input');
      const url = (input.value || '').trim();
      if (!url) return;
      openArchive(url);
    }

    function closeArchive() {
      const modal = document.getElementById('archive-modal');
      const frame = document.getElementById('archive-frame');
      gsap.to(modal, {
        opacity: 0, duration: 0.18,
        onComplete: () => {
          modal.style.display = 'none';
          frame.src = 'about:blank';
          document.body.style.overflow = 'auto';
        }
      });
      haptic();
    }

    // ESC close (cert viewer + menu + archive)
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        const viewer = document.getElementById('viewer-modal');
        if (viewer && viewer.style.display === 'flex') closeViewer();
        const menu = document.getElementById('mobile-menu');
        if (menu && menu.style.display === 'flex') toggleMenu();
        const arc = document.getElementById('archive-modal');
        if (arc && arc.style.display === 'flex') closeArchive();
      }
    });


    // --- Mobile menu ---
    function toggleMenu() {
      const menu = document.getElementById('mobile-menu');
      const btn = document.getElementById('menu-toggle');

      const isOpen = menu.style.display === 'flex';
      if (isOpen) {
        gsap.to(".mobile-menu-item", { opacity:0, y:10, duration:0.2, stagger:0.04 });
        gsap.to(".mobile-menu-footer", { opacity: 0, y: 10, duration: 0.2 });
        gsap.to(menu, { opacity: 0, duration: 0.25, onComplete: () => {
          menu.style.display = 'none';
          menu.setAttribute('aria-hidden', 'true');
          if (btn) btn.setAttribute('aria-expanded', 'false');
          document.body.style.overflow = 'auto';
        }});
      } else {
        menu.style.display = 'flex';
        menu.setAttribute('aria-hidden', 'false');
        if (btn) btn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';

        gsap.fromTo(menu, { opacity:0 }, { opacity:1, duration:0.3 });
        gsap.fromTo(".mobile-menu-item",
          { opacity:0, y:26 },
          { opacity:1, y:0, duration:0.55, stagger:0.08, delay:0.08, ease: "power4.out" }
        );
        gsap.fromTo(".mobile-menu-footer",
          { opacity: 0, y: 14 },
          { opacity: 1, y: 0, duration: 0.5, delay: 0.45 }
        );
      }
      haptic();
    }

    function setupNav() {
      window.addEventListener('scroll', () => {
        const nav = document.getElementById('navbar');
        if (window.scrollY > 50) nav.classList.add('scrolled');
        else nav.classList.remove('scrolled');
      });

      document.getElementById('menu-toggle').onclick = toggleMenu;
      document.getElementById('menu-close').onclick = toggleMenu;
}

    // --- Magnetic + spotlight hover ---
    function setupMagnetic() {
      const items = document.querySelectorAll('.social-icon, .btn-world, .btn-ghost, .feature-icon-v2, .premium-card');
      items.forEach(el => {
        el.addEventListener('mousemove', (e) => {
          const rect = el.getBoundingClientRect();
          const x = e.clientX - rect.left;
          const y = e.clientY - rect.top;
          el.style.setProperty('--mx', (x / rect.width * 100) + '%');
          el.style.setProperty('--my', (y / rect.height * 100) + '%');
        });
      });

      const magnets = document.querySelectorAll('.social-icon, .btn-world, .btn-ghost');
      magnets.forEach(el => {
        el.addEventListener('mousemove', (e) => {
          const rect = el.getBoundingClientRect();
          const x = e.clientX - rect.left - rect.width / 2;
          const y = e.clientY - rect.top - rect.height / 2;
          gsap.to(el, { x: x * 0.22, y: y * 0.22, duration: 0.28, ease: "power2.out" });
        });
        el.addEventListener('mouseleave', () => {
          gsap.to(el, { x: 0, y: 0, duration: 0.45, ease: "elastic.out(1, 0.35)" });
        });
      });
    }

    function handleImageError(img, text) {
      const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600"><rect width="100%" height="100%" fill="#080808"/><text x="50%" y="50%" fill="#e11d48" font-family='Cinzel' font-size="24" text-anchor="middle" dominant-baseline="middle">${text}</text></svg>`;
      img.src = `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;
    }

    // --- Background (Matrix) with pause + mobile FPS ---
    function createMatrixBackground() {
      const canvas = document.getElementById('canvas-neural');
      const ctx = canvas.getContext('2d');

      let width = canvas.width = window.innerWidth;
      let height = canvas.height = window.innerHeight;

      const chars = "010101<>[]{}/\\|!@#$%^&*()_+-=ResilienceCyberArchitectureSystems";
      const charArray = chars.split("");

      const isMobile = window.innerWidth < 768;
      const fontSize = isMobile ? 16 : 14;
      let columns = Math.floor(width / fontSize);

      let drops = [];
      for (let x = 0; x < columns; x++) drops[x] = Math.random() * -100;

      let paused = false;
      document.addEventListener('visibilitychange', () => { paused = document.hidden; });

      function draw() {
        ctx.fillStyle = "rgba(5, 5, 5, 0.10)";
        ctx.fillRect(0, 0, width, height);
        ctx.font = fontSize + "px 'Space Mono'";

        for (let i = 0; i < drops.length; i++) {
          const text = charArray[Math.floor(Math.random() * charArray.length)];
          const isBright = Math.random() > 0.988;
          ctx.fillStyle = isBright ? "#e11d48" : "rgba(225, 29, 72, 0.18)";
          ctx.fillText(text, i * fontSize, drops[i] * fontSize);

          if (drops[i] * fontSize > height && Math.random() > 0.975) drops[i] = 0;
          drops[i]++;
        }
      }

      window.addEventListener('resize', () => {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
        columns = Math.floor(width / fontSize);
        drops = [];
        for (let x = 0; x < columns; x++) drops[x] = Math.random() * -100;
      });

      let lastTime = 0;
      const fps = isMobile ? 18 : 30;
      const interval = 1000 / fps;

      function loop(timestamp) {
        if (!paused) {
          const delta = timestamp - lastTime;
          if (delta > interval) {
            draw();
            lastTime = timestamp;
          }
        }
        requestAnimationFrame(loop);
      }
      requestAnimationFrame(loop);
    }

    

    // Archive meta: domain + date from Wayback URL (NEW)
    function parseWayback(url) {
      try {
        const m = url.match(/web\.archive\.org\/web\/(\d{14})\//);
        const ts = m ? m[1] : null;
        const dm = url.match(/web\.archive\.org\/web\/\d{14}\/(https?:\/\/[^/]+)/);
        const target = dm ? dm[1] : null;
        let date = null;
        if (ts) {
          const y = parseInt(ts.slice(0,4),10);
          const mo = parseInt(ts.slice(4,6),10)-1;
          const d = parseInt(ts.slice(6,8),10);
          date = new Date(Date.UTC(y, mo, d));
        }
        return { date, target };
      } catch(e) { return { date: null, target: null }; }
    }
    function domainFromTarget(target) {
      try {
        const u = new URL(target);
        return u.hostname.replace(/^www\./,'');
      } catch(e) { return ""; }
    }
    function formatArchiveDate(date) {
      if (!date) return "";
      const lang = document.documentElement.lang || 'fr';
      const locale = (lang === 'ar') ? 'ar' : (lang === 'fr' ? 'fr-FR' : 'en-US');
      try {
        return new Intl.DateTimeFormat(locale, { year: 'numeric', month: 'short', day: '2-digit' }).format(date);
      } catch(e) {
        // fallback YYYY-MM-DD
        const y = date.getUTCFullYear();
        const m = String(date.getUTCMonth()+1).padStart(2,'0');
        const d = String(date.getUTCDate()).padStart(2,'0');
        return `${y}-${m}-${d}`;
      }
    }
    function applyArchiveMeta() {
      const cards = document.querySelectorAll('#archive [data-archive-date]');
      cards.forEach(badge => {
        const card = badge.closest('.premium-card');
        if (!card) return;
        const link = card.querySelector('a[href*="web.archive.org"]');
        if (!link) return;
        const { date, target } = parseWayback(link.href);
        const domain = domainFromTarget(target);
        const dateStr = formatArchiveDate(date);
        const dateEl = card.querySelector('[data-archive-date]');
        const domainEl = card.querySelector('[data-archive-domain]');
        if (dateEl) dateEl.textContent = dateStr || "—";
        if (domainEl) domainEl.textContent = domain || "";
      });
    }

    /* ============================================================
       NEW FEATURES JS — added after applyArchiveMeta (safe zone)
       ============================================================ */

    // 1 ── Reading Progress Bar
    (function () {
      var bar = document.getElementById('reading-progress');
      if (!bar) return;
      window.addEventListener('scroll', function () {
        var st = window.scrollY;
        var dh = document.documentElement.scrollHeight - window.innerHeight;
        bar.style.width = (dh > 0 ? Math.min(st / dh * 100, 100) : 0) + '%';
      }, { passive: true });
    }());

    // 2 ── Toast
    var _tt = null;
    window.showToast = function (msg, icon, dur) {
      icon = icon || '✓'; dur = dur || 3000;
      var t = document.getElementById('toast');
      var tm = document.getElementById('toast-msg');
      var ti = document.getElementById('toast-icon');
      if (!t) return;
      if (_tt) clearTimeout(_tt);
      if (tm) tm.textContent = msg;
      if (ti) ti.textContent = icon;
      t.style.opacity = '1';
      t.style.transform = 'translateX(-50%) translateY(0)';
      t.style.pointerEvents = 'auto';
      _tt = setTimeout(function () {
        t.style.opacity = '0';
        t.style.transform = 'translateX(-50%) translateY(20px)';
        t.style.pointerEvents = 'none';
      }, dur);
    };

    // 3 ── Copy Email
    window.copyEmail = function () {
      var email = _email || 'contact@qassem.io';
      if (navigator.clipboard) {
        navigator.clipboard.writeText(email).then(function () { window.showToast('Email copié !', '📋'); });
      } else {
        var el = document.createElement('textarea');
        el.value = email; document.body.appendChild(el); el.select();
        document.execCommand('copy'); document.body.removeChild(el);
        window.showToast('Email copié !', '📋');
      }
    };

    // 4 ── Floating Contact Button
    var _fo = false;
    window.toggleFloat = function () {
      _fo = !_fo;
      var m = document.getElementById('float-contact-menu');
      var ic = document.getElementById('float-icon');
      var btn = document.getElementById('float-btn');
      if (!m) return;
      if (_fo) {
        m.style.opacity = '1'; m.style.transform = 'translateY(0) scale(1)'; m.style.pointerEvents = 'auto';
        if (ic) ic.className = 'fas fa-times';
        if (btn) { btn.style.background = 'rgba(225,29,72,0.75)'; btn.style.transform = 'rotate(15deg)'; }
      } else {
        m.style.opacity = '0'; m.style.transform = 'translateY(10px) scale(0.95)'; m.style.pointerEvents = 'none';
        if (ic) ic.className = 'fas fa-comment-dots';
        if (btn) { btn.style.background = '#e11d48'; btn.style.transform = 'rotate(0)'; }
      }
    };
    (function () {
      var fc = document.getElementById('float-contact');
      var shown = false;
      if (!fc) return;
      window.addEventListener('scroll', function () {
        if (window.scrollY > 400 && !shown) {
          shown = true; fc.style.opacity = '1'; fc.style.transform = 'translateY(0)'; fc.style.pointerEvents = 'auto';
        } else if (window.scrollY <= 400 && shown) {
          shown = false; fc.style.opacity = '0'; fc.style.transform = 'translateY(20px)'; fc.style.pointerEvents = 'none';
        }
      }, { passive: true });
    }());

    // 5 ── Back to Top
    (function () {
      var btn = document.getElementById('back-to-top');
      if (!btn) return;
      window.addEventListener('scroll', function () {
        btn.classList.toggle('visible', window.scrollY > 600);
      }, { passive: true });
    }());

    // 6 ── Typing Animation (hero)
    (function () {
      var el = document.getElementById('hero-typed');
      if (!el) return;
      var words = (window._TYPING_WORDS && window._TYPING_WORDS.length) ? window._TYPING_WORDS : ['Résilience Numérique', 'Cloud Architect', 'Cyber Strategist', 'SI Governance', 'Digital Resilience'];
      var wi = 0, ci = 0, del = false, pause = 0;
      function type() {
        if (pause > 0) { pause--; setTimeout(type, 80); return; }
        var w = words[wi];
        if (!del) {
          el.textContent = w.slice(0, ++ci);
          if (ci === w.length) { del = true; pause = 28; }
          setTimeout(type, 80);
        } else {
          el.textContent = w.slice(0, --ci);
          if (ci === 0) { del = false; wi = (wi + 1) % words.length; pause = 10; }
          setTimeout(type, 44);
        }
      }
      setTimeout(type, 2500);
    }());

    // 7 ── Theme Switcher (Dark ➜ Ultra ➜ Light)
    (function () {
      var btn = document.getElementById('theme-toggle');
      if (!btn) return;
      var themes = ['dark', 'ultra', 'light'];
      var icons  = ['fa-circle-half-stroke', 'fa-moon', 'fa-sun'];
      var labels = ['Dark', 'Ultra', 'Light'];
      var idx = 0;
      // Restore saved
      try {
        var saved = localStorage.getItem('qassem-theme');
        if (saved && themes.indexOf(saved) > -1) {
          idx = themes.indexOf(saved);
          document.documentElement.setAttribute('data-theme', saved);
        }
      } catch (e) {}
      function updateBtn() {
        var ic = btn.querySelector('i');
        if (ic) ic.className = 'fas ' + icons[idx];
        btn.title = 'Theme: ' + labels[idx];
      }
      updateBtn();
      btn.addEventListener('click', function () {
        idx = (idx + 1) % themes.length;
        document.documentElement.setAttribute('data-theme', themes[idx]);
        try { localStorage.setItem('qassem-theme', themes[idx]); } catch (e) {}
        updateBtn();
        if (window.showToast) window.showToast(labels[idx] + ' mode', idx === 2 ? '☀️' : idx === 1 ? '🌑' : '🌗');
      });
    }());

document.addEventListener('DOMContentLoaded', function() { if(typeof init === 'function') { if(_CFG.enableAnimations===false){var c=document.querySelector('#canvas-neural');if(c)c.style.display='none';} init(); } });
  

/* ============ SECTION ============ */


    // Archive logos: graceful fallback if an asset is missing
    (function(){
      const fallback = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 96 96'%3E%3Crect width='96' height='96' rx='18' fill='%230b0b0b'/%3E%3Cpath d='M28 60h40M28 44h40' stroke='%23e11d48' stroke-width='4' stroke-linecap='round' opacity='.8'/%3E%3Ccircle cx='34' cy='32' r='4' fill='%23e11d48'/%3E%3C/svg%3E";
      document.querySelectorAll('img.archive-logo').forEach(img => {
        img.addEventListener('error', () => {
          if(!img.dataset.fallback){ img.dataset.fallback='1'; img.src=fallback; }
        }, { once: true });
      });
    })();
    

/* ============ SECTION ============ */


  (function(){
    function ready(fn){
      if(document.readyState !== 'loading') fn();
      else document.addEventListener('DOMContentLoaded', fn);
    }
    ready(function(){
      var cards = Array.prototype.slice.call(document.querySelectorAll('.pf-card'));
      if(!cards.length) return;

      // Make cards keyboard accessible (without changing markup structure)
      cards.forEach(function(card){
        if(!card.hasAttribute('tabindex')) card.setAttribute('tabindex','0');
        card.setAttribute('role','button');
        card.setAttribute('aria-pressed', card.classList.contains('is-color') ? 'true' : 'false');
      });

      function setActive(card){
        cards.forEach(function(c){
          if(c !== card){
            c.classList.remove('is-color');
            c.setAttribute('aria-pressed','false');
          }
        });
        var nowOn = !card.classList.contains('is-color');
        card.classList.toggle('is-color', nowOn);
        card.setAttribute('aria-pressed', nowOn ? 'true' : 'false');
      }

      function onActivate(e){
        var card = e.target.closest && e.target.closest('.pf-card');
        if(!card) return;
        // Don't block normal navigation if there's a link; toggle color first then allow default
        setActive(card);
      }

      document.addEventListener('click', onActivate, {passive:true});
      document.addEventListener('keydown', function(e){
        if(e.key !== 'Enter' && e.key !== ' ') return;
        var card = e.target.closest && e.target.closest('.pf-card');
        if(!card) return;
        e.preventDefault();
        setActive(card);
      });
    });
  })();


/* ============ SECTION ============ */


(function(){
  function setupMobileReveal(){
    if (window.matchMedia && window.matchMedia('(max-width: 768px)').matches){
      var items = document.querySelectorAll('#heritage .legacy-item');
      if (!items || !items.length) return;

      // Respect reduced motion
      var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (reduce){
        items.forEach(function(el){ el.classList.add('in-view'); });
        return;
      }

      var io = new IntersectionObserver(function(entries){
        entries.forEach(function(e){
          if (e.isIntersecting){
            e.target.classList.add('in-view');
            io.unobserve(e.target);
          }
        });
      }, { root: null, threshold: 0.22 });

      items.forEach(function(el){ io.observe(el); });
    }
  }
  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', setupMobileReveal);
  } else {
    setupMobileReveal();
  }
})();


/* ============ SECTION ============ */


(function(){
  function isMobile(){
    return window.matchMedia && window.matchMedia('(max-width: 768px)').matches;
  }

  function addImpactCounter(){
    var heritageCard = document.querySelector('#heritage .premium-card');
    if (!heritageCard) return;
    if (heritageCard.querySelector('.impact-counter')) return;

    // Insert after the section label (h3) for a "live" feel
    var title = heritageCard.querySelector('[data-i18n="legacy.timeline_title"]');
    if (!title) return;

    var wrap = document.createElement('div');
    wrap.className = 'impact-counter';
    wrap.innerHTML = [
      '<span class="impact-pill" data-pill="range">',
        '<strong id="impactYears">—</strong>',
        '<span id="impactRangeLabel">Années</span>',
      '</span>',
      '<span class="impact-pill" data-pill="items">',
        '<strong id="impactItems">—</strong>',
        '<span id="impactItemsLabel">Étapes</span>',
      '</span>'
    ].join('');

    title.insertAdjacentElement('afterend', wrap);
  }

  function extractYearRangeFromTimeline(){
    var items = document.querySelectorAll('#heritage .legacy-item');
    var years = [];
    items.forEach(function(it){
      var dateEl = it.querySelector('.heritage-date [data-i18n] + span[dir="ltr"], .heritage-date span[dir="ltr"]');
      // Fallback: any span in heritage-date
      if (!dateEl){
        dateEl = it.querySelector('.heritage-date span[dir="ltr"]') || it.querySelector('.heritage-date');
      }
      var txt = (dateEl && dateEl.textContent) ? dateEl.textContent : '';
      // Collect 4-digit years
      var m = txt.match(/\b(19|20)\d{2}\b/g);
      if (m){
        m.forEach(function(y){ years.push(parseInt(y,10)); });
      }
    });
    if (!years.length) return null;
    var minY = Math.min.apply(null, years);
    var maxY = Math.max.apply(null, years);
    return {min:minY, max:maxY};
  }

  function updateImpactCounterText(){
    var yEl = document.getElementById('impactYears');
    var iEl = document.getElementById('impactItems');
    if (!yEl || !iEl) return;

    var items = document.querySelectorAll('#heritage .legacy-item');
    iEl.textContent = items.length.toString();

    var r = extractYearRangeFromTimeline();
    if (!r){ yEl.textContent = '—'; return; }

    // Inclusive span (e.g., 2011..2016 = 6)
    var span = (r.max - r.min) + 1;
    yEl.textContent = span.toString();
  }

  function setupAccordion(){
    var items = document.querySelectorAll('#heritage .legacy-item');
    if (!items || !items.length) return;

    items.forEach(function(card){
      // Ensure a toggle icon exists
      if (!card.querySelector('.accordion-toggle')){
        var t = document.createElement('div');
        t.className = 'accordion-toggle';
        t.innerHTML = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        card.appendChild(t);
      }

      // Move description into accordion-content wrapper (if not already)
      var p = card.querySelector('p');
      if (!p) return;

      if (!p.closest('.accordion-content')){
        var wrap = document.createElement('div');
        wrap.className = 'accordion-content';
        p.parentNode.insertBefore(wrap, p);
        wrap.appendChild(p);
      }

      // Default: collapsed
      card.classList.remove('expanded');

      // Click to toggle (ignore if user selects text)
      card.addEventListener('click', function(e){
        // If user tapped a link (unlikely here), let it behave.
        var a = e.target.closest && e.target.closest('a');
        if (a) return;

        // Toggle
        var isOpen = card.classList.contains('expanded');
        // Close others for clean accordion feel
        items.forEach(function(other){
          if (other !== card) other.classList.remove('expanded');
        });
        card.classList.toggle('expanded', !isOpen);
      }, {passive:true});
    });
  }

  function localizeCounterLabels(){
    // Keep it lightweight: infer language from <html lang> if present
    var lang = (document.documentElement.getAttribute('lang') || '').toLowerCase();
    var rangeLabel = document.getElementById('impactRangeLabel');
    var itemsLabel = document.getElementById('impactItemsLabel');
    if (!rangeLabel || !itemsLabel) return;

    if (lang.startsWith('ar')){
      rangeLabel.textContent = 'سنوات';
      itemsLabel.textContent = 'محطات';
    } else if (lang.startsWith('en')){
      rangeLabel.textContent = 'Years';
      itemsLabel.textContent = 'Milestones';
    } else {
      rangeLabel.textContent = 'Années';
      itemsLabel.textContent = 'Étapes';
    }
  }

  function boot(){
    if (!isMobile()) return;

    addImpactCounter();
    // Wait a tick for i18n to populate dates, then compute
    setTimeout(function(){
      updateImpactCounterText();
      localizeCounterLabels();
    }, 450);

    setupAccordion();

    // Recompute if language switch updates DOM
    var heritage = document.getElementById('heritage');
    if (heritage && window.MutationObserver){
      var mo = new MutationObserver(function(){
        updateImpactCounterText();
        localizeCounterLabels();
      });
      mo.observe(heritage, {subtree:true, characterData:true, childList:true});
    }
  }

  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
