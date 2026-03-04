/**
 * QASSEM — UX Control Panel (Front-End)
 * Controls: Skins, Colors, Fonts, Sections, Tabs, Images, Effects
 * Visible only to logged-in admins
 */
(function () {
'use strict';

/* ── Skin definitions ── */
var SKINS = {
  qassem:       {n:'QASSEM',       d:'Original',  c:'#e11d48', r:'225,29,72',  bg:'#09090c'},
  avada:        {n:'Avada',        d:'Corporate', c:'#0073aa', r:'0,115,170',  bg:'#0d1117'},
  divi:         {n:'Divi',         d:'Elegant',   c:'#7c3aed', r:'124,58,237', bg:'#0f0a1e'},
  astra:        {n:'Astra',        d:'Minimal',   c:'#e11d48', r:'225,29,72',  bg:'#0a0a0a'},
  newspaper:    {n:'Newspaper',    d:'Magazine',  c:'#e11d48', r:'225,29,72',  bg:'#080002'},
  flatsome:     {n:'Flatsome',     d:'Commerce',  c:'#00a8a8', r:'0,168,168',  bg:'#080f18'},
  jannah:       {n:'Jannah',       d:'العربي',    c:'#10b981', r:'16,185,129', bg:'#04100a'},
  generatepress:{n:'GeneratePress',d:'Speed',     c:'#1e40af', r:'30,64,175',  bg:'#06060e'},
};

var SECTIONS = [
  {k:'about',       label:'About / Profile',   icon:'fa-user'},
  {k:'heritage',    label:'Heritage',           icon:'fa-landmark'},
  {k:'archive',     label:'Archive',            icon:'fa-archive'},
  {k:'focus',       label:'Skills',             icon:'fa-bullseye'},
  {k:'credentials', label:'Certifications',     icon:'fa-award'},
  {k:'contact',     label:'Contact',            icon:'fa-envelope'},
  {k:'portfolio',   label:'Portfolio',          icon:'fa-images'},
];

var TITLE_FONTS = ['Cinzel','Montserrat','Playfair Display','Bebas Neue','Josefin Sans','Cormorant Garamond','Tajawal','Space Grotesk'];
var BODY_FONTS  = ['Plus Jakarta Sans','Inter','DM Sans','Open Sans','Roboto','Nunito','Poppins','Tajawal'];

/* ── Live state ── */
var S = {
  skin:  document.documentElement.getAttribute('data-skin')  || 'qassem',
  theme: document.documentElement.getAttribute('data-theme') || 'dark',
  brand: '#e11d48', gold: '#d4af37',
  titleFont: 'Cinzel', bodyFont: 'Plus Jakarta Sans',
  lang: 'fr',
  sections: {about:true,heritage:true,archive:true,focus:true,credentials:true,contact:true,portfolio:true},
  sectionOrder: SECTIONS.map(function(s){return s.k;}),
  features: {canvas:true,loader:true,animations:true,progressBar:true,backToTop:true,floatContact:true,typing:true,langSwitcher:true,themeToggle:true},
  tabs: [
    {label:'الرئيسية',icon:'fa-house',   href:'#home',        active:true},
    {label:'من أنا',  icon:'fa-user',    href:'#about',       active:true},
    {label:'الإرث',   icon:'fa-landmark',href:'#heritage',    active:true},
    {label:'الشهادات',icon:'fa-award',   href:'#credentials', active:true},
    {label:'تواصل',   icon:'fa-envelope',href:'#contact',     active:true},
  ],
  heroTitle: 'QASSEM',
  heroDesc: 'Une vision forgée par les défis.',
};

/* Load from config */
if (window.QASSEM_CONFIG) {
  var C = window.QASSEM_CONFIG;
  S.skin  = C.activeSkin  || S.skin;
  S.theme = C.defaultTheme || S.theme;
  S.lang  = C.lang || S.lang;
  S.heroTitle = C.heroTitle || S.heroTitle;
  if (C.showSections) Object.assign(S.sections, C.showSections);
  if (C.tabs && C.tabs.length) S.tabs = C.tabs;
}

/* Restore saved state */
try {
  var saved = localStorage.getItem('qassem_ux');
  if (saved) Object.assign(S, JSON.parse(saved));
} catch(e) {}

/* ── Build the panel HTML ── */
function buildPanel() {
  var el = document.createElement('div');
  el.id = 'qpanel-root';
  el.innerHTML =
    '<div id="qpanel-overlay"></div>' +
    '<button id="qpanel-trigger" aria-label="Open UX Panel">' +
      '<span class="trig-dot"></span>' +
      '<span class="trig-label">UX</span>' +
    '</button>' +
    '<div id="qpanel">' +
      head() + nav() +
      '<div id="qpanel-body">' +
        paneSkins() + paneColors() + paneSections() + paneFonts() + paneMobile() + paneEffects() +
      '</div>' +
      foot() +
    '</div>' +
    '<div id="qpanel-toast"></div>';
  document.body.appendChild(el);
}

function head() {
  return '<div id="qpanel-head">' +
    '<div class="qph-icon"><i class="fas fa-sliders"></i></div>' +
    '<div class="qph-title"><h3>UX Control Panel</h3><p>QASSEM — Admin Only</p></div>' +
    '<button class="qph-close" id="qpanel-close">×</button>' +
  '</div>';
}

function nav() {
  var tabs = [
    {id:'skins',   icon:'fa-palette',     label:'Skins'},
    {id:'colors',  icon:'fa-droplet',     label:'Colors'},
    {id:'sections',icon:'fa-layer-group', label:'Layout'},
    {id:'fonts',   icon:'fa-font',        label:'Fonts'},
    {id:'mobile',  icon:'fa-mobile-alt',  label:'Mobile'},
    {id:'effects', icon:'fa-wand-magic-sparkles', label:'Effects'},
  ];
  return '<div id="qpanel-nav">' + tabs.map(function(t,i){
    return '<button class="qnav-btn'+(i===0?' active':'') +'" data-pane="'+t.id+'">' +
      '<i class="fas '+t.icon+'"></i>'+t.label+'</button>';
  }).join('') + '</div>';
}

function paneSkins() {
  var cards = Object.keys(SKINS).map(function(k) {
    var s = SKINS[k], active = S.skin===k;
    return '<div class="skin-card'+(active?' active':'') +'" data-skin="'+k+'" style="--sc:'+s.c+';--sc-rgb:'+s.r+'">' +
      '<div class="skin-strip" style="background:'+s.bg+'">' +
        '<div class="skin-strip-bar" style="background:'+s.c+'"></div>' +
        '<div class="skin-strip-lines">' +
          '<div class="skin-strip-line a"></div>' +
          '<div class="skin-strip-line" style="width:80%"></div>' +
          '<div class="skin-strip-line" style="width:60%"></div>' +
        '</div>' +
      '</div>' +
      '<div class="skin-name">'+s.n+'</div>' +
      '<div class="skin-desc">'+s.d+'</div>' +
      '<div class="skin-check"><i class="fas fa-check"></i></div>' +
    '</div>';
  }).join('');
  return '<div class="qpane active" id="qpane-skins">' +
    '<div class="qgroup">Visual Identity</div>' +
    '<div class="skin-grid">'+cards+'</div>' +
  '</div>';
}

function paneColors() {
  return '<div class="qpane" id="qpane-colors">' +
    '<div class="qgroup">Brand Colors</div>' +
    colorRow('brand','Brand / Accent', S.brand) +
    colorRow('gold', 'Gold Accent',    S.gold) +
    '<div class="qgroup">Theme Mode</div>' +
    selRow('theme-mode','Mode',[
      {v:'dark', l:'🌑 Dark (default)'},
      {v:'ultra',l:'⚫ Ultra Black'},
      {v:'light',l:'☀️ Light'},
    ], S.theme) +
    '<div class="qgroup">Site Info</div>' +
    inputRow('hero-title','Main Name / Title', S.heroTitle) +
    inputRow('hero-desc', 'Hero Description',  S.heroDesc) +
  '</div>';
}

function colorRow(id, label, val) {
  return '<div class="color-row" id="crow-'+id+'">' +
    '<span class="color-row-label">'+label+'</span>' +
    '<div class="color-swatch" style="background:'+val+'" id="swatch-'+id+'">' +
      '<input type="color" id="color-'+id+'" value="'+val+'">' +
    '</div>' +
    '<span class="color-hex" id="hex-'+id+'">'+val+'</span>' +
  '</div>';
}

function selRow(id, label, opts, current) {
  return '<div class="qselect-wrap"><label>'+label+'</label>' +
    '<select class="qselect" id="sel-'+id+'">' +
      opts.map(function(o){ return '<option value="'+o.v+'"'+(o.v===current?' selected':'')+'>'+o.l+'</option>'; }).join('') +
    '</select></div>';
}

function inputRow(id, label, val) {
  return '<div class="qinput-wrap"><label>'+label+'</label>' +
    '<input class="qinput" id="inp-'+id+'" type="text" value="'+(val||'')+'">' +
  '</div>';
}

function toggleRow(key, icon, label, checked) {
  return '<div class="toggle-row">' +
    '<span class="toggle-label"><i class="fas '+icon+'"></i>'+label+'</span>' +
    '<label class="qt-switch">' +
      '<input type="checkbox" data-key="'+key+'"'+(checked?' checked':'')+'>' +
      '<div class="qt-track"></div><div class="qt-thumb"></div>' +
    '</label>' +
  '</div>';
}

function paneSections() {
  var items = S.sectionOrder.map(function(k,i) {
    var s = SECTIONS.find(function(x){return x.k===k;}) || {label:k,icon:'fa-circle'};
    return '<div class="section-item" draggable="true" data-section="'+k+'">' +
      '<i class="fas fa-grip-vertical si-grip"></i>' +
      '<div class="si-icon"><i class="fas '+s.icon+'"></i></div>' +
      '<span class="si-name">'+s.label+'</span>' +
      '<label class="qt-switch">' +
        '<input type="checkbox" data-section-toggle="'+k+'"'+(S.sections[k]?' checked':'')+'>'+
        '<div class="qt-track"></div><div class="qt-thumb"></div>' +
      '</label>' +
      '<span class="si-order">'+(i+1)+'</span>' +
    '</div>';
  }).join('');
  return '<div class="qpane" id="qpane-sections">' +
    '<div class="qgroup">Show / Hide — Drag to Reorder</div>' +
    '<div class="sections-list" id="sections-list">'+items+'</div>' +
  '</div>';
}

function paneFonts() {
  return '<div class="qpane" id="qpane-fonts">' +
    '<div class="qgroup">Typography</div>' +
    selRow('title-font','Title / Display Font', TITLE_FONTS.map(function(f){return{v:f,l:f};}), S.titleFont) +
    '<div id="font-preview" style="margin:8px 0 14px;padding:12px;background:rgba(255,255,255,.03);border-radius:9px;border:1px solid rgba(255,255,255,.06)">' +
      '<div id="fp-title" style="font-size:20px;font-weight:900;color:rgba(255,255,255,.85);margin-bottom:3px">QASSEM</div>' +
      '<div id="fp-body"  style="font-size:10px;color:rgba(255,255,255,.4)">Architecte SI • Résilience Numérique</div>' +
    '</div>' +
    selRow('body-font','Body Font', BODY_FONTS.map(function(f){return{v:f,l:f};}), S.bodyFont) +
    '<div class="qgroup">Language</div>' +
    selRow('lang','Default Language',[{v:'fr',l:'Français'},{v:'en',l:'English'},{v:'ar',l:'العربية'}], S.lang) +
  '</div>';
}

function paneMobile() {
  var rows = S.tabs.map(function(t,i){
    return '<div class="tab-editor-row">' +
      '<div class="tab-icon-prev" id="tip-'+i+'"><i class="fas '+(t.icon||'fa-circle')+'"></i></div>' +
      '<div class="tab-fields">' +
        '<input class="tab-field" data-ti="'+i+'" data-tf="label" value="'+(t.label||'')+'" placeholder="Label">' +
        '<input class="tab-field" data-ti="'+i+'" data-tf="icon"  value="'+(t.icon||'')+'"  placeholder="fa-house">' +
        '<input class="tab-field" data-ti="'+i+'" data-tf="href"  value="'+(t.href||'')+'"  placeholder="#section">' +
      '</div>' +
      '<label class="qt-switch">' +
        '<input type="checkbox" data-ti="'+i+'" data-tf="active"'+(t.active?' checked':'')+'>' +
        '<div class="qt-track"></div><div class="qt-thumb"></div>' +
      '</label>' +
    '</div>';
  }).join('');
  return '<div class="qpane" id="qpane-mobile">' +
    '<div class="qgroup">Mobile Tab Bar</div>' +
    '<div class="tab-editor-list">'+rows+'</div>' +
    '<div class="qgroup">Mobile Menu</div>' +
    selRow('menu-style','Menu Animation Style',[
      {v:'fullscreen',l:'Full Screen (default)'},
      {v:'sidebar',l:'Slide-in Sidebar'},
    ], 'fullscreen') +
  '</div>';
}

function paneEffects() {
  var feats = [
    {k:'canvas',      icon:'fa-network-wired',        l:'Neural Canvas Background'},
    {k:'loader',      icon:'fa-spinner',               l:'Page Preloader'},
    {k:'animations',  icon:'fa-wand-magic-sparkles',   l:'GSAP Scroll Animations'},
    {k:'progressBar', icon:'fa-minus',                 l:'Reading Progress Bar'},
    {k:'backToTop',   icon:'fa-arrow-up',              l:'Back to Top Button'},
    {k:'floatContact',icon:'fa-comment-dots',          l:'Floating Contact Button'},
    {k:'typing',      icon:'fa-keyboard',              l:'Hero Typing Animation'},
    {k:'langSwitcher',icon:'fa-language',              l:'Language Switcher'},
    {k:'themeToggle', icon:'fa-circle-half-stroke',    l:'Dark/Light Toggle Button'},
  ];
  return '<div class="qpane" id="qpane-effects">' +
    '<div class="qgroup">Toggle Features</div>' +
    feats.map(function(f){ return toggleRow(f.k, f.icon, f.l, S.features[f.k]!==false); }).join('') +
  '</div>';
}

function foot() {
  return '<div id="qpanel-foot">' +
    '<button id="qp-reset" title="Reset defaults"><i class="fas fa-rotate-left"></i></button>' +
    '<button id="qp-save"><i class="fas fa-floppy-disk"></i> Save Changes</button>' +
  '</div>';
}

/* ── Apply state visually ── */
function applyState() {
  var h = document.documentElement;
  h.setAttribute('data-skin',  S.skin);
  h.setAttribute('data-theme', S.theme);
  h.style.setProperty('--brand', S.brand);
  h.style.setProperty('--gold',  S.gold);
  // brand-rgb
  var rgb = hexToRgb(S.brand);
  if (rgb) h.style.setProperty('--brand-rgb', rgb);

  // Sections
  S.sectionOrder.forEach(function(k) {
    var el = document.getElementById(k);
    if (el) el.style.display = S.sections[k] ? '' : 'none';
  });

  // Fonts
  h.style.setProperty('--title-font', "'"+S.titleFont+"'");
  document.querySelectorAll('.title-font,.main-title,.section-title').forEach(function(e){
    e.style.fontFamily = "'"+S.titleFont+"',serif";
  });
  document.body.style.fontFamily = "'"+S.bodyFont+"',Tajawal,sans-serif";

  // Features
  var map = {
    canvas: 'canvas-neural', loader: 'loader',
    progressBar: 'reading-progress', backToTop: 'back-to-top',
    floatContact: 'float-contact',
  };
  Object.keys(map).forEach(function(k) {
    var el = document.getElementById(map[k]);
    if (el) el.style.display = S.features[k] !== false ? '' : 'none';
  });
  var lb = document.getElementById('lang-btns');
  if (lb) lb.style.display = S.features.langSwitcher !== false ? '' : 'none';
  var tt = document.getElementById('theme-toggle');
  if (tt) tt.style.display = S.features.themeToggle !== false ? '' : 'none';

  // Hero texts
  var ht = document.querySelectorAll('.main-title,[data-hero-title]');
  ht.forEach(function(e){ if (S.heroTitle) e.textContent = S.heroTitle; });

  // Save button brand color
  var sb = document.getElementById('qp-save');
  if (sb) sb.style.background = S.brand;

  // Update tabbar live
  refreshTabbar();
}

function refreshTabbar() {
  var tb = document.getElementById('mobile-tabbar');
  if (!tb) return;
  tb.innerHTML = '';
  S.tabs.forEach(function(t, i) {
    if (!t.active) return;
    var a = document.createElement('a');
    a.href = t.href || '#home';
    a.className = 'tab-item' + (i===0 ? ' active' : '');
    a.setAttribute('data-section', (t.href||'#home').replace('#',''));
    a.innerHTML = '<i class="fas '+(t.icon||'fa-house')+'"></i><span>'+(t.label||'')+'</span>';
    tb.appendChild(a);
  });
}

/* ── Event wiring ── */
function wire() {
  /* open/close */
  document.getElementById('qpanel-trigger').addEventListener('click', open);
  document.getElementById('qpanel-close').addEventListener('click', close);
  document.getElementById('qpanel-overlay').addEventListener('click', close);

  /* nav tabs */
  document.querySelectorAll('.qnav-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.qnav-btn').forEach(function(b){ b.classList.remove('active'); });
      document.querySelectorAll('.qpane').forEach(function(p){ p.classList.remove('active'); });
      btn.classList.add('active');
      var pane = document.getElementById('qpane-'+btn.dataset.pane);
      if (pane) pane.classList.add('active');
    });
  });

  /* skin cards */
  document.querySelectorAll('.skin-card').forEach(function(card) {
    card.addEventListener('click', function() {
      var k = card.dataset.skin, s = SKINS[k];
      S.skin = k;
      if (s) { S.brand = s.c; }
      document.querySelectorAll('.skin-card').forEach(function(c){ c.classList.remove('active'); });
      card.classList.add('active');
      syncColorInputs();
      applyState();
      toast('Skin: '+(s?s.n:k));
    });
  });

  /* colors */
  ['brand','gold'].forEach(function(id) {
    var inp = document.getElementById('color-'+id);
    var sw  = document.getElementById('swatch-'+id);
    var hx  = document.getElementById('hex-'+id);
    if (!inp) return;
    inp.addEventListener('input', function() {
      S[id] = inp.value;
      if (sw) sw.style.background = inp.value;
      if (hx) hx.textContent = inp.value;
      document.documentElement.style.setProperty('--'+id, inp.value);
      if (id==='brand') {
        var rgb = hexToRgb(inp.value);
        if (rgb) document.documentElement.style.setProperty('--brand-rgb', rgb);
        var sb = document.getElementById('qp-save');
        if (sb) sb.style.background = inp.value;
      }
    });
  });

  /* theme mode */
  var tm = document.getElementById('sel-theme-mode');
  if (tm) tm.addEventListener('change', function(){ S.theme = tm.value; document.documentElement.setAttribute('data-theme', tm.value); });

  /* hero title */
  var ht = document.getElementById('inp-hero-title');
  if (ht) ht.addEventListener('input', function(){
    S.heroTitle = ht.value;
    document.querySelectorAll('.main-title,[data-hero-title]').forEach(function(e){ e.textContent = ht.value; });
  });

  /* fonts */
  var tf = document.getElementById('sel-title-font');
  if (tf) tf.addEventListener('change', function(){
    S.titleFont = tf.value;
    document.getElementById('fp-title').style.fontFamily = "'"+tf.value+"',serif";
    document.querySelectorAll('.title-font,.main-title,.section-title').forEach(function(e){
      e.style.fontFamily = "'"+tf.value+"',serif";
    });
  });
  var bf = document.getElementById('sel-body-font');
  if (bf) bf.addEventListener('change', function(){
    S.bodyFont = bf.value;
    document.getElementById('fp-body').style.fontFamily = "'"+bf.value+"',sans-serif";
    document.body.style.fontFamily = "'"+bf.value+"',Tajawal,sans-serif";
  });

  /* lang */
  var lg = document.getElementById('sel-lang');
  if (lg) lg.addEventListener('change', function(){ S.lang = lg.value; });

  /* section toggles */
  document.querySelectorAll('[data-section-toggle]').forEach(function(inp) {
    inp.addEventListener('change', function() {
      var k = inp.dataset.sectionToggle;
      S.sections[k] = inp.checked;
      var sec = document.getElementById(k);
      if (sec) sec.style.display = inp.checked ? '' : 'none';
    });
  });

  /* drag & drop sections */
  initDrag();

  /* tab editor */
  document.querySelectorAll('[data-ti][data-tf]').forEach(function(inp) {
    inp.addEventListener('input', function() {
      var i = parseInt(inp.dataset.ti), f = inp.dataset.tf;
      if (!S.tabs[i]) return;
      S.tabs[i][f] = f==='active' ? inp.checked : inp.value;
      if (f==='icon') {
        var prev = document.getElementById('tip-'+i);
        if (prev) prev.innerHTML = '<i class="fas '+inp.value+'"></i>';
      }
      refreshTabbar();
    });
    inp.addEventListener('change', function() {
      if (inp.dataset.tf === 'active') {
        var i = parseInt(inp.dataset.ti);
        if (S.tabs[i]) S.tabs[i].active = inp.checked;
        refreshTabbar();
      }
    });
  });

  /* features */
  document.querySelectorAll('[data-key]').forEach(function(inp) {
    inp.addEventListener('change', function() {
      S.features[inp.dataset.key] = inp.checked;
      applyState();
    });
  });

  /* save & reset */
  document.getElementById('qp-save').addEventListener('click', save);
  document.getElementById('qp-reset').addEventListener('click', function(){
    if (!confirm('Reset all settings to defaults?')) return;
    S.skin='qassem'; S.brand='#e11d48'; S.gold='#d4af37';
    S.theme='dark'; S.titleFont='Cinzel'; S.bodyFont='Plus Jakarta Sans';
    applyState(); syncColorInputs();
    toast('Reset complete ✓','ok');
  });
}

/* ── Drag & Drop sections ── */
function initDrag() {
  var list = document.getElementById('sections-list');
  if (!list) return;
  var dragging = null;
  list.querySelectorAll('.section-item').forEach(function(item) {
    item.addEventListener('dragstart', function(e){
      dragging = item; item.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move';
    });
    item.addEventListener('dragend', function(){
      dragging = null; item.classList.remove('dragging');
      list.querySelectorAll('.section-item').forEach(function(i){ i.classList.remove('drag-over'); });
      // Update order state
      S.sectionOrder = [];
      list.querySelectorAll('.section-item').forEach(function(i,idx){
        S.sectionOrder.push(i.dataset.section);
        var n = i.querySelector('.si-order'); if(n) n.textContent = idx+1;
      });
      // Reorder in DOM
      var main = document.getElementById('main-content');
      if (main) S.sectionOrder.forEach(function(k){
        var sec = document.getElementById(k);
        if (sec && sec.parentNode===main) main.appendChild(sec);
      });
    });
    item.addEventListener('dragover', function(e){
      e.preventDefault();
      if (dragging && dragging!==item) {
        item.classList.add('drag-over');
        var r = item.getBoundingClientRect();
        list.insertBefore(dragging, (e.clientY-r.top)>r.height/2 ? item.nextSibling : item);
      }
    });
    item.addEventListener('dragleave', function(){ item.classList.remove('drag-over'); });
  });
}

/* ── Save via AJAX ── */
function save() {
  var btn = document.getElementById('qp-save');
  btn.classList.add('busy');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

  var cfg = window.QASSEM_CONFIG || {};
  var fd  = new FormData();
  fd.append('action','qassem_save_panel');
  fd.append('nonce', cfg.nonce||'');
  fd.append('skin',         S.skin);
  fd.append('theme',        S.theme);
  fd.append('brand',        S.brand);
  fd.append('gold',         S.gold);
  fd.append('title_font',   S.titleFont);
  fd.append('body_font',    S.bodyFont);
  fd.append('lang',         S.lang);
  fd.append('hero_title',   S.heroTitle);
  fd.append('sections',     JSON.stringify(S.sections));
  fd.append('section_order',JSON.stringify(S.sectionOrder));
  fd.append('features',     JSON.stringify(S.features));
  fd.append('tabs',         JSON.stringify(S.tabs));

  fetch(cfg.ajaxUrl || '/wp-admin/admin-ajax.php', {method:'POST',body:fd})
    .then(function(r){ return r.json(); })
    .then(function(res){
      if (res.success) {
        try { localStorage.setItem('qassem_ux', JSON.stringify(S)); } catch(e){}
        toast('Saved successfully ✓','ok');
      } else {
        toast('Error: '+(res.data&&res.data.msg||'failed'),'err');
      }
    })
    .catch(function(){
      try { localStorage.setItem('qassem_ux', JSON.stringify(S)); } catch(e){}
      toast('Saved locally ✓','ok');
    })
    .finally(function(){
      btn.classList.remove('busy');
      btn.innerHTML = '<i class="fas fa-floppy-disk"></i> Save Changes';
      btn.style.background = S.brand;
    });
}

function open() {
  document.getElementById('qpanel').classList.add('open');
  document.getElementById('qpanel-overlay').classList.add('open');
  document.getElementById('qpanel-trigger').style.cssText='opacity:0;pointer-events:none';
}
function close() {
  document.getElementById('qpanel').classList.remove('open');
  document.getElementById('qpanel-overlay').classList.remove('open');
  document.getElementById('qpanel-trigger').style.cssText='';
}
function toast(msg,type) {
  var el = document.getElementById('qpanel-toast');
  if (!el) return;
  el.textContent = msg; el.className = 'show '+(type||'');
  clearTimeout(el._t); el._t = setTimeout(function(){ el.className=''; }, 2800);
}
function syncColorInputs() {
  var s = SKINS[S.skin];
  if (!s) return;
  var inp = document.getElementById('color-brand');
  var sw  = document.getElementById('swatch-brand');
  var hx  = document.getElementById('hex-brand');
  if (inp) inp.value = S.brand;
  if (sw)  sw.style.background = S.brand;
  if (hx)  hx.textContent = S.brand;
}
function hexToRgb(hex) {
  var m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return m ? parseInt(m[1],16)+','+parseInt(m[2],16)+','+parseInt(m[3],16) : null;
}

/* ── Init ── */
function init() {
  buildPanel();
  wire();
  applyState();
}

document.readyState==='loading'
  ? document.addEventListener('DOMContentLoaded', init)
  : init();

})();
