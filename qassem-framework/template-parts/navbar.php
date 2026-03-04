<?php
$title   = get_q('hero_title','QASSEM');
$li      = get_q('linkedin','https://linkedin.com/in/qassem-adra');
$tw      = get_q('twitter','https://x.com/QassemAdra');
$gh      = get_q('github','https://github.com/QassemAdra');
$cv      = get_q('cv_url','');
$lang    = get_q('default_lang','fr');
$logo_img = get_q('logo_image','');
$use_img  = get_q_bool('use_logo_image',false);
$logo_h   = get_q('logo_height',40);
$show_lang   = get_q_bool('show_lang_switcher',true);
$show_toggle = get_q_bool('show_theme_toggle',true);
$nav = [
    'about'       => get_q('nav_about','Profil'),
    'heritage'    => get_q('nav_heritage','Héritage'),
    'archive'     => get_q('nav_archive','Archive'),
    'focus'       => get_q('nav_focus','Maîtrise'),
    'credentials' => get_q('nav_credentials','Certifications'),
    'contact'     => get_q('nav_contact','Contact'),
    'portfolio'   => get_q('nav_portfolio','Galerie'),
];
?>
<nav class="fixed top-0 w-full z-[5000] py-4 md:py-8 px-6 md:px-12 flex justify-between items-center transition-all duration-700" id="navbar">

  <!-- LOGO -->
  <div class="flex items-center gap-3 md:gap-5">
    <span class="w-2 h-2 rounded-full bg-rose-600 relative"><span class="absolute inset-0 rounded-full bg-rose-600 animate-ping"></span></span>
    <a href="<?php echo esc_url(home_url('/')); ?>#home">
      <?php if($use_img && $logo_img): ?>
        <img src="<?php echo esc_url($logo_img); ?>" alt="<?php echo esc_attr($title); ?>" class="qassem-logo" style="height:<?php echo absint($logo_h); ?>px;width:auto;object-fit:contain;"/>
      <?php else: ?>
        <span class="title-font text-xl md:text-2xl font-black tracking-tighter text-white"><?php echo esc_html($title); ?></span>
      <?php endif; ?>
    </a>
  </div>

  <!-- DESKTOP NAV -->
  <div class="hidden lg:flex gap-8 xl:gap-10 text-[9px] font-black uppercase tracking-[0.32em] text-white/30">
    <?php foreach($nav as $id => $label): ?>
      <?php if(get_q_bool("show_{$id}",true)): ?>
      <a class="hover:text-white transition-all" href="#<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <!-- RIGHT CONTROLS -->
  <div class="flex items-center gap-3 md:gap-6">
    <div class="hidden xl:flex gap-2.5">
      <?php if($li): ?><a class="social-icon li w-8 h-8 text-[11px]" href="<?php echo esc_url($li); ?>" rel="noopener noreferrer" target="_blank"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
      <?php if($tw): ?><a class="social-icon tw w-8 h-8 text-[11px]" href="<?php echo esc_url($tw); ?>" rel="noopener noreferrer" target="_blank"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
      <?php if($gh): ?><a class="social-icon gh w-8 h-8 text-[11px]" href="<?php echo esc_url($gh); ?>" rel="noopener noreferrer" target="_blank"><i class="fab fa-github"></i></a><?php endif; ?>
    </div>
    <?php if($show_toggle): ?>
    <button id="theme-toggle" aria-label="Toggle theme" type="button"><i class="fas fa-circle-half-stroke"></i></button>
    <?php endif; ?>
    <?php if($show_lang): ?>
    <div class="flex bg-white/5 p-1 rounded-full border border-white/5 text-[8px] md:text-[9px]" id="lang-btns">
      <?php foreach(['fr'=>'FR','en'=>'EN','ar'=>'AR'] as $code=>$lbl): ?>
      <button class="px-2 md:px-4 py-1.5 rounded-full font-bold transition-all <?php echo $lang===$code?'text-white':'text-white/30'; ?>" data-lang="<?php echo $code; ?>" onclick="setLanguage('<?php echo $code; ?>')" type="button"><?php echo $lbl; ?></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <button aria-controls="mobile-menu" aria-expanded="false" aria-label="Open menu" class="lg:hidden text-2xl text-white/50" id="menu-toggle" type="button"><i class="fas fa-bars-staggered"></i></button>
  </div>
</nav>

<!-- MOBILE MENU -->
<div aria-hidden="true" id="mobile-menu" class="<?php echo esc_attr(get_q('mobile_menu_style','fullscreen')==='sidebar'?'sidebar-menu':''); ?>">
  <div class="menu-glow"></div>
  <div class="mobile-menu-header">
    <div class="title-font text-2xl font-black tracking-tighter"><?php echo esc_html($title); ?><span class="text-rose-600">.</span></div>
    <button aria-label="Close menu" class="text-4xl text-white/20 hover:text-rose-600" id="menu-close" type="button">×</button>
  </div>
  <div class="flex flex-col">
    <?php $i=1; foreach($nav as $id=>$label): if(get_q_bool("show_{$id}",true)): ?>
    <a class="mobile-menu-item" href="#<?php echo esc_attr($id); ?>" onclick="toggleMenu()">
      <span class="index"><?php printf('%02d',$i++); ?></span> <?php echo esc_html($label); ?>
    </a>
    <?php endif; endforeach; ?>
  </div>
  <div class="mobile-menu-footer space-y-4">
    <?php if($cv): ?>
    <a class="btn-ghost w-full justify-center" download href="<?php echo esc_url($cv); ?>"><i class="fas fa-file-arrow-down"></i> CV</a>
    <?php endif; ?>
    <p class="text-[8px] uppercase tracking-[0.4em] text-white/20 font-black">Digital Presence</p>
    <div class="flex flex-wrap gap-4">
      <?php if($li): ?><a class="social-icon li w-10 h-10" href="<?php echo esc_url($li); ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
      <?php if($tw): ?><a class="social-icon tw w-10 h-10" href="<?php echo esc_url($tw); ?>" target="_blank"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
      <?php if($gh): ?><a class="social-icon gh w-10 h-10" href="<?php echo esc_url($gh); ?>" target="_blank"><i class="fab fa-github"></i></a><?php endif; ?>
    </div>
  </div>
</div>
