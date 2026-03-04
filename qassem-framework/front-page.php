<?php get_header(); ?>
<?php get_template_part('template-parts/hero'); ?>
<main id="main-content" class="max-w-[1440px] mx-auto px-6 md:px-8 space-y-[15vh] md:space-y-[25vh] pb-32">
<?php if(get_q_bool('show_about',true))       get_template_part('template-parts/about'); ?>
<?php if(get_q_bool('show_heritage',true))    get_template_part('template-parts/heritage'); ?>
<?php if(get_q_bool('show_archive',true))     get_template_part('template-parts/archive'); ?>
<?php if(get_q_bool('show_focus',true))       get_template_part('template-parts/focus'); ?>
<?php if(get_q_bool('show_credentials',true)) get_template_part('template-parts/credentials'); ?>
<?php if(get_q_bool('show_contact',true))     get_template_part('template-parts/contact'); ?>
</main>
<?php if(get_q_bool('show_portfolio',true))   get_template_part('template-parts/portfolio'); ?>
<?php get_footer(); ?>
