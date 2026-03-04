<?php /* Template Part: credentials */ ?>
<section class="space-y-10 md:space-y-18" id="credentials">
<div class="flex flex-col gap-6">
<div class="flex flex-col md:flex-row justify-between items-center md:items-end gap-6">
<div class="space-y-3 text-center md:text-start">
<h2 class="title-font section-title font-black italic" data-i18n="certs.title">Portfolio <span class="text-rose-600">Certifié</span></h2>
<p class="text-gray-500 uppercase tracking-[0.2em] md:tracking-[0.4em] text-[8px] md:text-[10px] font-black" data-i18n="certs.subtitle">Validé par 15 Piliers d'Excellence Mondiale</p>
</div>
<div class="flex flex-col sm:flex-row gap-3 items-center justify-center">
<span class="result-pill"><span data-i18n="ui.results">Results</span>: <b id="cert-count">15</b></span>
<input autocomplete="off" class="search-input" id="cert-search" placeholder="Search: ITIL, AWS, Microsoft..." spellcheck="false" type="search"/>
</div>
</div>
<div class="flex flex-wrap justify-center md:justify-start gap-2 md:gap-3" id="cert-filters">
<button class="px-5 md:px-8 py-2 md:py-3 rounded-full premium-card text-[7px] md:text-[9px] font-black active-filter" data-filter="all" data-i18n="certs.filters.all" onclick="filterCerts('all')" type="button">TOUT</button>
<button class="px-5 md:px-8 py-2 md:py-3 rounded-full premium-card text-[7px] md:text-[9px] font-black" data-filter="strategy" data-i18n="certs.filters.strategy" onclick="filterCerts('strategy')" type="button">STRATÉGIE</button>
<button class="px-5 md:px-8 py-2 md:py-3 rounded-full premium-card text-[7px] md:text-[9px] font-black" data-filter="tech" data-i18n="certs.filters.tech" onclick="filterCerts('tech')" type="button">TECH</button>
<button class="px-5 md:px-8 py-2 md:py-3 rounded-full premium-card text-[7px] md:text-[9px] font-black" data-filter="media" data-i18n="certs.filters.media" onclick="filterCerts('media')" type="button">MEDIA</button>
</div>
</div>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-8" id="certs-container">
<!-- Dynamic Content -->
</div>
<div class="premium-card p-10 text-center hidden" id="cert-empty">
<div class="text-rose-500 text-4xl mb-4"><i class="fas fa-magnifying-glass"></i></div>
<div class="title-font text-2xl font-black italic mb-2" data-i18n="certs.no_results_title">No results</div>
<div class="text-white/50 text-sm" data-i18n="certs.no_results_desc">Try another keyword (e.g., Microsoft, AWS, ITIL).</div>
</div>
</section>
<!-- Contact Section -->
