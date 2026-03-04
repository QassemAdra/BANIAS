<?php
$lang  = get_q('default_lang','fr');
$theme = get_q('default_theme','dark');
$skin  = get_theme_mod('qassem_active_skin','qassem');
$dir   = ($lang==='ar') ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html data-theme="<?php echo esc_attr($theme); ?>" data-skin="<?php echo esc_attr($skin); ?>" dir="<?php echo $dir; ?>" lang="<?php echo esc_attr($lang); ?>" class="scroll-smooth">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a href="#main-content" style="position:fixed;top:-100%;left:16px;z-index:99999;padding:10px 18px;background:var(--brand,#e11d48);color:white;font-weight:900;border-radius:0 0 12px 12px;text-decoration:none;font-size:13px;transition:top .2s" onfocus="this.style.top='0'" onblur="this.style.top='-100%'">Skip to content</a>
<?php if(get_q_bool('show_canvas',true) && !in_array($skin,['generatepress','astra'])): ?>
<canvas id="canvas-neural"></canvas>
<?php endif; ?>
<div id="stage-backdrop" aria-hidden="true">
  <div class="stage-spotlight"></div><div class="stage-aurora"></div>
  <div class="stage-line"></div><div class="stage-vignette"></div><div class="stage-texture"></div>
</div>
<div class="glow-blob top-[-10%] left-[-10%]"></div>
<div class="glow-blob gold bottom-[-10%] right-[-10%]"></div>
<?php if(get_q_bool('show_loader',true)): ?>
<div id="loader">
  <div class="title-font text-4xl md:text-7xl font-black text-white tracking-widest mb-10 text-center px-4" id="loader-logo">
    <span class="inline-block"><?php echo esc_html(get_q('hero_title','QASSEM')); ?></span><span style="color:var(--brand,#e11d48)">.</span>
  </div>
  <div class="w-48 md:w-64 h-[2px] bg-white/5 relative overflow-hidden rounded-full">
    <div class="h-full w-0" id="loader-bar" style="background:var(--brand,#e11d48);box-shadow:0 0 15px var(--brand,#e11d48)"></div>
  </div>
  <div class="mt-8 text-center px-6">
    <p class="text-[9px] uppercase tracking-[.5em] font-black animate-pulse" id="loader-status" style="color:var(--brand,#e11d48)">ESTABLISHING CORE</p>
    <p class="text-[7px] font-mono text-white/20 uppercase mt-2 tracking-widest" id="loader-subtext">Verifying security protocols...</p>
  </div>
  <div class="loader-num" id="loader-num">00</div>
</div>
<?php endif; ?>
<div id="site-content">
<?php get_template_part('template-parts/navbar'); ?>
