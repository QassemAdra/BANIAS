<?php /* Template Part: portfolio */ ?>
<section class="section" id="portfolio">
<div class="container mx-auto px-6">
<h2 class="title-font section-title font-black italic text-center md:text-start" data-i18n="portfolio.title">أعمال الجرافيك</h2>
<p class="text-gray-500 uppercase tracking-[0.3em] md:tracking-[0.6em] text-[8px] md:text-[10px] font-black text-center md:text-start" data-i18n="portfolio.lead">نماذج مختارة من الشعارات والهوية البصرية</p>
<div class="pf-grid mt-10">
<div class="pf-card" data-desc="شعار حديث مستوحى من فكرة البصيرة والوضوح. التكوين الدائري يرمز للإحاطة والكمال، مع رمز قوي يعمل ممتازًا للطباعة والمنصات الرقمية." data-id="almukhl" data-img="assets/portfolio/almukhl.png" data-pid="almukhl" data-sub="Logo Design • Islamic Brand • 2022" data-title="Almukhl – المُكحل">
<img alt="Almukhl Logo" src="assets/portfolio/almukhl.png"/>
<div class="pf-overlay"><div class="pf-meta">
<p class="pf-title" data-i18n="portfolio.items.almukhl.title"></p>
<p class="pf-sub" data-i18n="portfolio.items.almukhl.sub"></p>
<p class="pf-desc" data-i18n="portfolio.items.almukhl.desc"></p>
</div></div>
</div>
<div class="pf-card" data-desc="هوية ثنائية اللغة تجمع العربي والإنجليزي، مع رمز يعكس الزراعة والتجارة والثقة والانتشار." data-id="middle_east" data-img="assets/portfolio/middle-east.jpg" data-pid="middle_east" data-sub="Corporate Identity • Food Trading • 2021" data-title="Middle East Food Trading">
<img alt="Middle East Food Trading" src="assets/portfolio/middle-east.jpg"/>
<div class="pf-overlay"><div class="pf-meta">
<p class="pf-title" data-i18n="portfolio.items.middle_east.title"></p>
<p class="pf-sub" data-i18n="portfolio.items.middle_east.sub"></p>
<p class="pf-desc" data-i18n="portfolio.items.middle_east.desc"></p>
</div></div>
</div>
<div class="pf-card" data-desc="شعار رمزي يعبّر عن الدعم والنمو عبر حركة العناصر وتوازنها، بألوان تعزز فكرة التمكين والتقدم." data-id="sanad" data-img="assets/portfolio/sanad.jpg" data-pid="sanad" data-sub="Logo Design • Development Initiative • 2025" data-title="SANAD">
<img alt="SANAD" src="assets/portfolio/sanad.jpg"/>
<div class="pf-overlay"><div class="pf-meta">
<p class="pf-title" data-i18n="portfolio.items.sanad.title"></p>
<p class="pf-sub" data-i18n="portfolio.items.sanad.sub"></p>
<p class="pf-desc" data-i18n="portfolio.items.sanad.desc"></p>
</div></div>
</div>
<div class="pf-card" data-desc="عمل فني توضيحي بتكوين متناظر يعكس الازدواجية والتوازن، مع تفاصيل خطية وزخرفية عالية." data-id="eva_adam" data-img="assets/portfolio/eva-adam.jpg" data-pid="eva_adam" data-sub="Illustration • Artistic Concept • 2020" data-title="Eva &amp; Adam">
<img alt="Eva &amp; Adam Illustration" src="assets/portfolio/eva-adam.jpg"/>
<div class="pf-overlay"><div class="pf-meta">
<p class="pf-title" data-i18n="portfolio.items.eva_adam.title"></p>
<p class="pf-sub" data-i18n="portfolio.items.eva_adam.sub"></p>
<p class="pf-desc" data-i18n="portfolio.items.eva_adam.desc"></p>
</div></div>
</div>
<div class="pf-card" data-desc="شعار إعلامي معاصر يعتمد على خطوط انسيابية لتجسيد حركة الخبر وسرعة تدفق المعلومات، مناسب للسوشال ميديا." data-id="feda_news" data-img="assets/portfolio/feda-news.png" data-pid="feda_news" data-sub="Media Identity • Logo Design • 2016" data-title="FedaNews">
<img alt="FedaNews" src="assets/portfolio/feda-news.png"/>
<div class="pf-overlay"><div class="pf-meta">
<p class="pf-title" data-i18n="portfolio.items.feda_news.title"></p>
<p class="pf-sub" data-i18n="portfolio.items.feda_news.sub"></p>
<p class="pf-desc" data-i18n="portfolio.items.feda_news.desc"></p>
</div></div>
</div>
</div>
</div>
</section>
<div aria-hidden="true" class="lb" id="lightbox">
<button aria-label="Close" class="lb-close" type="button">✕</button>
<div class="lb-box">
<img alt="" class="lb-img" id="lbImg" src=""/>
<div class="lb-cap">
<h3 id="lbTitle"></h3>
<p id="lbSub"></p>
<p id="lbDesc" style="margin-top:8px;"></p>
</div>
</div>
</div>
<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script>
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
</script>
