<?php
$hero_title = get_option('qassem_hero_title', 'QASSEM');
$cv_url     = get_option('qassem_cv_url', get_template_directory_uri() . '/assets/QASSEM_CV.pdf');
?>
<header class="min-h-screen flex flex-col justify-center items-center px-6 md:px-10 text-center relative pt-24 pb-12" id="home">
<div class="w-full" id="hero-master">
<p class="text-[7px] md:text-[9px] uppercase tracking-[1em] md:tracking-[1.5em] text-rose-500 font-black mb-6 md:mb-10" data-i18n="hero.subtitle">
  Architecte SI • <span id="hero-typed"></span><span id="hero-cursor" style="display:inline-block;width:2px;height:.85em;background:#e11d48;margin-left:1px;vertical-align:middle;animation:blink .7s step-end infinite;"></span>
</p>
<h1 class="title-font main-title leading-tight font-black text-white mb-7 md:mb-10 italic"><?php echo esc_html($hero_title); ?></h1>
<p class="max-w-3xl text-gray-400 leading-relaxed mx-auto mb-9 md:mb-14 font-light text-base md:text-xl" data-i18n="hero.description">
  Une vision forgée par les défis, transformée en gouvernance technologique.
</p>
<div class="flex flex-col sm:flex-row justify-center gap-3 md:gap-6">
<a class="btn-world justify-center" href="#credentials"><span data-i18n="hero.cta">Voir Certifications</span><i class="fas fa-chevron-right text-[10px]"></i></a>
<a class="btn-ghost justify-center" download href="<?php echo esc_url($cv_url); ?>"><i class="fas fa-file-arrow-down"></i><span data-i18n="ui.download_cv">Télécharger CV</span></a>
<a class="px-8 md:px-12 py-3 md:py-4 rounded-full border border-white/10 text-[8px] md:text-[9px] font-black uppercase tracking-widest text-white/50 hover:bg-white/5 transition-all" data-i18n="hero.contact" href="#contact">Contactez-moi</a>
</div>
<div class="mt-10 md:mt-12 flex items-center justify-center gap-3 text-[9px] uppercase tracking-[0.4em] text-white/25 font-black">
<span class="inline-flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span><span data-i18n="hero.signal">Systems Online</span></span>
<span>•</span>
<span data-i18n="hero.arch">Architecture SI</span>
</div>
</div>
</header>
