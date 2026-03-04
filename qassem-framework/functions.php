<?php
if (!defined('ABSPATH')) exit;
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/panel-ajax.php';

add_action('after_setup_theme', function () {
    load_theme_textdomain('qassem', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption']);
    register_nav_menus(['primary'=>__('Primary','qassem'),'footer'=>__('Footer','qassem')]);
});

add_action('wp_enqueue_scripts', function () {
    $ver = wp_get_theme()->get('Version');
    $uri = get_template_directory_uri();

    wp_enqueue_style('font-awesome','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',[],'6.5.1');
    wp_enqueue_script('tailwindcss','https://cdn.tailwindcss.com',[],null,false);

    if (get_q_bool('enable_animations',true)) {
        wp_enqueue_script('gsap','https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js',[],null,false);
        wp_enqueue_script('gsap-st','https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js',['gsap'],null,false);
    }

    wp_enqueue_style('qassem-main',    $uri.'/assets/css/main.css',             ['font-awesome'], $ver);
    wp_enqueue_style('qassem-skins',   $uri.'/assets/css/skins/skins.css',      ['qassem-main'],  $ver);
    wp_enqueue_style('qassem-typo',    $uri.'/assets/css/skins/typography.css', ['qassem-skins'], $ver);
    wp_enqueue_style('qassem-visuals', $uri.'/assets/css/visuals.css',          ['qassem-main'],  $ver);

    wp_enqueue_script('qassem-main', $uri.'/assets/js/main.js', ['gsap','gsap-st'], $ver, true);

    // UX Panel — admins only
    if (is_user_logged_in() && current_user_can('manage_options')) {
        wp_enqueue_style( 'qassem-panel', $uri.'/assets/css/admin/ux-panel.css', ['qassem-main'], $ver);
        wp_enqueue_script('qassem-panel', $uri.'/assets/js/panel/ux-panel.js', ['qassem-main'], $ver, true);
    }

    $skin = get_theme_mod('qassem_active_skin','qassem');
    wp_localize_script('qassem-main','QASSEM_CONFIG',[
        'ajaxUrl'          => admin_url('admin-ajax.php'),
        'nonce'            => wp_create_nonce('qassem_nonce'),
        'themeUri'         => $uri,
        'homeUrl'          => home_url('/'),
        'isAdmin'          => (is_user_logged_in() && current_user_can('manage_options')),
        'lang'             => get_q('default_lang','fr'),
        'defaultTheme'     => get_q('default_theme','dark'),
        'activeSkin'       => $skin,
        'heroTitle'        => get_q('hero_title','QASSEM'),
        'email'            => get_q('email','contact@qassem.io'),
        'cvUrl'            => get_q('cv_url',''),
        'linkedin'         => get_q('linkedin','https://linkedin.com/in/qassem-adra'),
        'twitter'          => get_q('twitter','https://x.com/QassemAdra'),
        'github'           => get_q('github','https://github.com/QassemAdra'),
        'facebook'         => get_q('facebook','https://facebook.com/Qassem'),
        'instagram'        => get_q('instagram','https://instagram.com/QassemAdra'),
        'typingWords'      => array_values(array_filter(array_map('trim',
            explode("\n", get_q('typing_words',"Résilience Numérique\nCloud Architect\nCyber Strategist\nSI Governance\nDigital Resilience"))))),
        'showTyping'       => get_q_bool('show_typing',true),
        'showCanvas'       => get_q_bool('show_canvas',true),
        'showLoader'       => get_q_bool('show_loader',true),
        'showProgressBar'  => get_q_bool('show_progress_bar',true),
        'showBackToTop'    => get_q_bool('show_back_to_top',true),
        'showFloatContact' => get_q_bool('show_float_contact',true),
        'enableAnimations' => get_q_bool('enable_animations',true),
        'showTabbar'       => get_q_bool('show_tabbar',true),
        'showSections'     => array_combine(
            ['about','heritage','archive','focus','credentials','contact','portfolio'],
            array_map(function($k){ return get_q_bool('show_'.$k,true); },
            ['about','heritage','archive','focus','credentials','contact','portfolio'])
        ),
        'tabs' => qassem_get_tabs_config(),
    ]);
});

add_action('wp_head','qassem_dns_prefetch',1);
function qassem_dns_prefetch(){
    echo '<link rel="dns-prefetch" href="https://fonts.googleapis.com"/>'."\n";
    echo '<link rel="dns-prefetch" href="https://images.unsplash.com"/>'."\n";
    echo '<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com"/>'."\n";
}

add_action('init', function () {
    $cpts = ['certification'=>['Certifications','awards','certifications'],'project'=>['Portfolio','portfolio','portfolio'],'archive_item'=>['Archive','archive','archives']];
    foreach ($cpts as $slug => [$plural,$icon,$rewrite]) {
        register_post_type($slug,['labels'=>['name'=>$plural],'public'=>true,'show_in_rest'=>true,'menu_icon'=>"dashicons-{$icon}",'supports'=>['title','editor','thumbnail','custom-fields'],'rewrite'=>['slug'=>$rewrite]]);
    }
});

add_action('wp_ajax_qassem_contact',        'qassem_handle_contact');
add_action('wp_ajax_nopriv_qassem_contact', 'qassem_handle_contact');
function qassem_handle_contact(){
    check_ajax_referer('qassem_nonce','nonce');
    $name=sanitize_text_field($_POST['name']??''); $email=sanitize_email($_POST['email']??''); $message=sanitize_textarea_field($_POST['message']??'');
    if(!$name||!is_email($email)||!$message) wp_send_json_error(['msg'=>'Fill all fields.']);
    $to=get_q('email',get_option('admin_email'));
    if(wp_mail($to,"Message from $name","Name: $name\nEmail: $email\n\n$message",['Content-Type: text/plain; charset=UTF-8',"Reply-To: $name <$email>"]))
        wp_send_json_success(['msg'=>'Sent!']);
    else wp_send_json_error(['msg'=>'Failed.']);
}

add_filter('body_class',function($c){
    $c[]='qassem-v5'; $c[]='skin-'.get_theme_mod('qassem_active_skin','qassem'); return $c;
});
add_action('widgets_init',function(){
    register_sidebar(['name'=>'Footer','id'=>'footer-1','before_widget'=>'<div class="widget %2$s">','after_widget'=>'</div>','before_title'=>'<h4>','after_title'=>'</h4>']);
});
remove_action('wp_head','print_emoji_detection_script',7);
remove_action('wp_print_styles','print_emoji_styles');
remove_action('wp_head','wp_generator');
