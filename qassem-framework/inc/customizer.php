<?php
/**
 * QASSEM Ultimate — Skin Switcher + Full Customizer
 */
if ( ! defined('ABSPATH') ) exit;

/* ================================================================
   SKIN DEFINITIONS — single source of truth
   ================================================================ */
function qassem_get_skins() {
    return [
        'qassem' => [
            'label'       => '🔴 QASSEM Original',
            'brand'       => '#e11d48',
            'gold'        => '#d4af37',
            'title_font'  => 'Cinzel',
            'body_font'   => 'Plus Jakarta Sans',
            'google_fonts'=> 'Cinzel:wght@700;900|Plus+Jakarta+Sans:wght@300;400;700;800',
            'preview_bg'  => '#09090c',
        ],
        'avada' => [
            'label'       => '🔵 Avada Corporate',
            'brand'       => '#0073aa',
            'gold'        => '#f0a500',
            'title_font'  => 'Montserrat',
            'body_font'   => 'Open Sans',
            'google_fonts'=> 'Montserrat:wght@400;700;900|Open+Sans:wght@300;400;600',
            'preview_bg'  => '#0d1117',
        ],
        'divi' => [
            'label'       => '🟣 Divi Elegant',
            'brand'       => '#7c3aed',
            'gold'        => '#f59e0b',
            'title_font'  => 'Playfair Display',
            'body_font'   => 'DM Sans',
            'google_fonts'=> 'Playfair+Display:ital,wght@0,700;0,900;1,700|DM+Sans:wght@300;400;500;700',
            'preview_bg'  => '#0f0a1e',
        ],
        'astra' => [
            'label'       => '⚪ Astra Minimal',
            'brand'       => '#e11d48',
            'gold'        => '#d4af37',
            'title_font'  => 'Inter',
            'body_font'   => 'Inter',
            'google_fonts'=> 'Inter:wght@300;400;500;700;900',
            'preview_bg'  => '#0a0a0a',
        ],
        'newspaper' => [
            'label'       => '📰 Newspaper Magazine',
            'brand'       => '#e11d48',
            'gold'        => '#ffd700',
            'title_font'  => 'Bebas Neue',
            'body_font'   => 'Roboto',
            'google_fonts'=> 'Bebas+Neue&family=Roboto:wght@300;400;700',
            'preview_bg'  => '#080002',
        ],
        'flatsome' => [
            'label'       => '🩵 Flatsome Commerce',
            'brand'       => '#00a8a8',
            'gold'        => '#f97316',
            'title_font'  => 'Josefin Sans',
            'body_font'   => 'Nunito',
            'google_fonts'=> 'Josefin+Sans:wght@300;400;600;700|Nunito:wght@300;400;700;800',
            'preview_bg'  => '#080f18',
        ],
        'jannah' => [
            'label'       => '🟢 Jannah العربي',
            'brand'       => '#10b981',
            'gold'        => '#d4af37',
            'title_font'  => 'Tajawal',
            'body_font'   => 'Tajawal',
            'google_fonts'=> 'Tajawal:wght@300;400;500;700;800;900',
            'preview_bg'  => '#04100a',
        ],
        'generatepress' => [
            'label'       => '⚡ GeneratePress Speed',
            'brand'       => '#1e40af',
            'gold'        => '#d4af37',
            'title_font'  => 'System',
            'body_font'   => 'System',
            'google_fonts'=> '', // no google fonts!
            'preview_bg'  => '#06060e',
        ],
    ];
}

/* ================================================================
   CUSTOMIZER REGISTRATION
   ================================================================ */
function qassem_ultimate_customizer( $wp_customize ) {

    $skins = qassem_get_skins();

    /* ── PANEL: SKIN SWITCHER ── */
    $wp_customize->add_panel('qassem_skin_panel', [
        'title'       => __('🎨 Skin — Visual Identity', 'qassem'),
        'description' => __('Choose and customize the complete visual identity of your portfolio. Each skin changes colors, typography, layout style and effects.', 'qassem'),
        'priority'    => 1,
    ]);

    // Active Skin
    $wp_customize->add_section('qassem_skin_section', [
        'title' => __('Active Skin', 'qassem'),
        'panel' => 'qassem_skin_panel',
    ]);
    $wp_customize->add_setting('qassem_active_skin', [
        'default'           => 'qassem',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ]);
    $wp_customize->add_control('qassem_active_skin', [
        'label'       => __('Select Skin', 'qassem'),
        'description' => __('Switch instantly between 7 complete visual identities', 'qassem'),
        'section'     => 'qassem_skin_section',
        'type'        => 'select',
        'choices'     => array_combine(
            array_keys($skins),
            array_column($skins, 'label')
        ),
    ]);

    // Per-skin override: brand color
    $wp_customize->add_setting('qassem_skin_brand_override', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'qassem_skin_brand_override', [
        'label'       => __('Override Brand Color (optional)', 'qassem'),
        'description' => __('Leave empty to use skin default', 'qassem'),
        'section'     => 'qassem_skin_section',
    ]));

    $wp_customize->add_setting('qassem_skin_gold_override', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'qassem_skin_gold_override', [
        'label'       => __('Override Gold Color (optional)', 'qassem'),
        'section'     => 'qassem_skin_section',
    ]));

    /* ── PANEL: IDENTITY ── */
    $wp_customize->add_panel('qassem_identity_panel', [
        'title'    => __('🪪 Identity & Logo', 'qassem'),
        'priority' => 10,
    ]);
    $wp_customize->add_section('qassem_logo_sec', ['title' => __('Logo & Name', 'qassem'), 'panel' => 'qassem_identity_panel']);

    foreach ([
        'qassem_hero_title'     => ['QASSEM',                           __('Main Name / Title', 'qassem'),        'text'],
        'qassem_tagline'        => ['Architecte SI',                    __('Tagline', 'qassem'),                   'text'],
        'qassem_hero_desc'      => ['Une vision forgée par les défis.', __('Hero Description', 'qassem'),          'textarea'],
        'qassem_use_logo_image' => [false,                              __('Use image logo instead of text', 'qassem'), 'checkbox'],
        'qassem_logo_image'     => ['',                                 __('Logo Image URL', 'qassem'),            'url'],
        'qassem_cv_url'         => ['',                                 __('CV File URL', 'qassem'),               'url'],
    ] as $id => [$default, $label, $type]) {
        $wp_customize->add_setting($id, [
            'default'           => $default,
            'sanitize_callback' => $type === 'checkbox' ? 'wp_validate_boolean' : ($type === 'url' ? 'esc_url_raw' : 'sanitize_text_field'),
            'transport'         => 'postMessage',
        ]);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'qassem_logo_sec', 'type' => $type]);
    }

    /* ── PANEL: HERO ── */
    $wp_customize->add_panel('qassem_hero_panel', ['title' => __('🦸 Hero Section', 'qassem'), 'priority' => 20]);
    $wp_customize->add_section('qassem_hero_sec', ['title' => __('Hero Content', 'qassem'), 'panel' => 'qassem_hero_panel']);

    $wp_customize->add_setting('qassem_typing_words', [
        'default'           => "Résilience Numérique\nCloud Architect\nCyber Strategist\nSI Governance\nDigital Resilience",
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);
    $wp_customize->add_control('qassem_typing_words', [
        'label'       => __('Typing Words (one per line)', 'qassem'),
        'section'     => 'qassem_hero_sec',
        'type'        => 'textarea',
    ]);

    foreach ([
        'qassem_cta1_text' => ['Voir Certifications', __('CTA Button Text', 'qassem')],
        'qassem_cta1_url'  => ['#credentials',        __('CTA Button URL', 'qassem')],
    ] as $id => [$def, $lbl]) {
        $wp_customize->add_setting($id, ['default' => $def, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
        $wp_customize->add_control($id, ['label' => $lbl, 'section' => 'qassem_hero_sec', 'type' => 'text']);
    }

    /* ── PANEL: SOCIAL ── */
    $wp_customize->add_panel('qassem_social_panel', ['title' => __('🌐 Social Links', 'qassem'), 'priority' => 30]);
    $wp_customize->add_section('qassem_social_sec', ['title' => __('Social Profiles', 'qassem'), 'panel' => 'qassem_social_panel']);

    $socials = [
        'qassem_email'     => ['contact@qassem.io',                   'Email',        'email'],
        'qassem_linkedin'  => ['https://linkedin.com/in/qassem-adra', 'LinkedIn',     'url'],
        'qassem_twitter'   => ['https://x.com/QassemAdra',           'Twitter / X',  'url'],
        'qassem_github'    => ['https://github.com/QassemAdra',       'GitHub',       'url'],
        'qassem_facebook'  => ['https://facebook.com/Qassem',         'Facebook',     'url'],
        'qassem_instagram' => ['https://instagram.com/QassemAdra',    'Instagram',    'url'],
        'qassem_snapchat'  => ['https://snapchat.com/add/QasemAdra',  'Snapchat',     'url'],
    ];
    foreach ($socials as $id => [$def, $label, $type]) {
        $wp_customize->add_setting($id, ['default' => $def, 'sanitize_callback' => $type === 'email' ? 'sanitize_email' : 'esc_url_raw']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'qassem_social_sec', 'type' => $type]);
    }

    /* ── PANEL: SECTIONS VISIBILITY ── */
    $wp_customize->add_panel('qassem_sections_panel', ['title' => __('📋 Sections Visibility', 'qassem'), 'priority' => 40]);
    $sections_list = ['about' => 'About/Profile', 'heritage' => 'Heritage/Timeline', 'archive' => 'Web Archive', 'focus' => 'Skills/Focus', 'credentials' => 'Certifications', 'contact' => 'Contact Form', 'portfolio' => 'Portfolio Gallery'];
    foreach ($sections_list as $key => $label) {
        $wp_customize->add_section("qassem_sec_{$key}", ['title' => __($label, 'qassem'), 'panel' => 'qassem_sections_panel']);
        $wp_customize->add_setting("qassem_show_{$key}", ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
        $wp_customize->add_control("qassem_show_{$key}", ['label' => sprintf(__('Show %s', 'qassem'), $label), 'section' => "qassem_sec_{$key}", 'type' => 'checkbox']);
        $wp_customize->add_setting("qassem_{$key}_title_override", ['default' => '', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
        $wp_customize->add_control("qassem_{$key}_title_override", ['label' => __('Custom Section Title', 'qassem'), 'section' => "qassem_sec_{$key}", 'type' => 'text']);
    }

    /* ── PANEL: MOBILE ── */
    $wp_customize->add_panel('qassem_mobile_panel', ['title' => __('📱 Mobile / Tabbar', 'qassem'), 'priority' => 50]);
    $wp_customize->add_section('qassem_tabbar_sec', ['title' => __('Tab Bar', 'qassem'), 'panel' => 'qassem_mobile_panel']);

    $wp_customize->add_setting('qassem_show_tabbar', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp_customize->add_control('qassem_show_tabbar', ['label' => __('Show mobile tabbar', 'qassem'), 'section' => 'qassem_tabbar_sec', 'type' => 'checkbox']);

    $default_tabs = [
        1 => ['الرئيسية','fa-house','#home'],
        2 => ['من أنا','fa-user','#about'],
        3 => ['الإرث','fa-landmark','#heritage'],
        4 => ['الاعتمادات','fa-award','#credentials'],
        5 => ['تواصل','fa-envelope','#contact'],
    ];
    for ($i = 1; $i <= 5; $i++) {
        $wp_customize->add_section("qassem_tab_{$i}_sec", ['title' => sprintf(__('Tab %d', 'qassem'), $i), 'panel' => 'qassem_mobile_panel']);
        foreach (['label' => $default_tabs[$i][0], 'icon' => $default_tabs[$i][1], 'href' => $default_tabs[$i][2]] as $f => $def) {
            $wp_customize->add_setting("qassem_tab_{$i}_{$f}", ['default' => $def, 'sanitize_callback' => 'sanitize_text_field']);
            $wp_customize->add_control("qassem_tab_{$i}_{$f}", ['label' => ucfirst($f), 'section' => "qassem_tab_{$i}_sec", 'type' => 'text']);
        }
        $wp_customize->add_setting("qassem_tab_{$i}_active", ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
        $wp_customize->add_control("qassem_tab_{$i}_active", ['label' => __('Show this tab', 'qassem'), 'section' => "qassem_tab_{$i}_sec", 'type' => 'checkbox']);
    }

    /* ── PANEL: FEATURES ── */
    $wp_customize->add_panel('qassem_features_panel', ['title' => __('⚙️ Features', 'qassem'), 'priority' => 60]);
    $wp_customize->add_section('qassem_features_sec', ['title' => __('Toggle Features', 'qassem'), 'panel' => 'qassem_features_panel']);

    $features = [
        'qassem_show_loader'         => [true,  __('Page Preloader', 'qassem')],
        'qassem_show_canvas'         => [true,  __('Neural Network Canvas', 'qassem')],
        'qassem_show_progress_bar'   => [true,  __('Reading Progress Bar', 'qassem')],
        'qassem_show_back_to_top'    => [true,  __('Back to Top Button', 'qassem')],
        'qassem_show_float_contact'  => [true,  __('Floating Contact Button', 'qassem')],
        'qassem_enable_animations'   => [true,  __('GSAP Animations', 'qassem')],
        'qassem_show_typing'         => [true,  __('Typing Animation', 'qassem')],
        'qassem_show_lang_switcher'  => [true,  __('Language Switcher', 'qassem')],
        'qassem_show_theme_toggle'   => [true,  __('Dark/Light Toggle', 'qassem')],
    ];
    foreach ($features as $id => [$def, $label]) {
        $wp_customize->add_setting($id, ['default' => $def, 'sanitize_callback' => 'wp_validate_boolean']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'qassem_features_sec', 'type' => 'checkbox']);
    }

    // Language
    $wp_customize->add_section('qassem_lang_sec', ['title' => __('Language', 'qassem'), 'panel' => 'qassem_features_panel']);
    $wp_customize->add_setting('qassem_default_lang', ['default' => 'fr', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('qassem_default_lang', ['label' => __('Default Language', 'qassem'), 'section' => 'qassem_lang_sec', 'type' => 'select', 'choices' => ['fr' => 'Français', 'en' => 'English', 'ar' => 'العربية']]);

    // Default theme mode
    $wp_customize->add_section('qassem_theme_mode_sec', ['title' => __('Theme Mode', 'qassem'), 'panel' => 'qassem_features_panel']);
    $wp_customize->add_setting('qassem_default_theme', ['default' => 'dark', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('qassem_default_theme', ['label' => __('Default Mode', 'qassem'), 'section' => 'qassem_theme_mode_sec', 'type' => 'select', 'choices' => ['dark' => '🌑 Dark', 'ultra' => '⚫ Ultra', 'light' => '☀️ Light']]);

    // Custom CSS
    $wp_customize->add_section('qassem_css_sec', ['title' => __('Custom CSS', 'qassem'), 'panel' => 'qassem_features_panel']);
    $wp_customize->add_setting('qassem_custom_css', ['default' => '', 'sanitize_callback' => 'wp_strip_all_tags']);
    $wp_customize->add_control('qassem_custom_css', ['label' => __('Additional CSS', 'qassem'), 'section' => 'qassem_css_sec', 'type' => 'textarea']);

    /* ── PANEL: FOOTER ── */
    $wp_customize->add_panel('qassem_footer_panel', ['title' => __('🦶 Footer', 'qassem'), 'priority' => 70]);
    $wp_customize->add_section('qassem_footer_sec', ['title' => __('Footer Content', 'qassem'), 'panel' => 'qassem_footer_panel']);
    foreach ([
        'qassem_footer_mission'  => ['Architecting digital resilience and enterprise governance at global standards.', __('Mission Text', 'qassem'), 'textarea'],
        'qassem_footer_location' => ['Banias, Syrie / France', __('Location', 'qassem'), 'text'],
        'qassem_footer_tagline'  => ['Résilience Numérique', __('Tagline', 'qassem'), 'text'],
    ] as $id => [$def, $label, $type]) {
        $wp_customize->add_setting($id, ['default' => $def, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'qassem_footer_sec', 'type' => $type]);
    }
}
add_action('customize_register', 'qassem_ultimate_customizer');


/* ================================================================
   OUTPUT: Dynamic CSS from active skin
   ================================================================ */
function qassem_ultimate_output_css() {
    $skins        = qassem_get_skins();
    $active       = get_theme_mod('qassem_active_skin', 'qassem');
    $skin         = $skins[$active] ?? $skins['qassem'];
    $brand        = get_theme_mod('qassem_skin_brand_override', '') ?: $skin['brand'];
    $gold         = get_theme_mod('qassem_skin_gold_override',  '') ?: $skin['gold'];
    $custom_css   = get_theme_mod('qassem_custom_css', '');
    $brand_rgb    = implode(',', sscanf(ltrim($brand,'#'), '%02x%02x%02x'));

    // Dynamic font enqueue (only for active skin)
    if (!empty($skin['google_fonts'])) {
        $font_url = 'https://fonts.googleapis.com/css2?family=' . $skin['google_fonts'] . '&display=swap';
        echo '<link rel="stylesheet" href="' . esc_url($font_url) . '"/>' . "\n";
    }
    ?>
    <style id="qassem-skin-vars">
    :root {
        --brand: <?php echo esc_attr($brand); ?>;
        --brand-rgb: <?php echo esc_attr($brand_rgb); ?>;
        --gold: <?php echo esc_attr($gold); ?>;
    }
    <?php if ($custom_css) echo wp_strip_all_tags($custom_css); ?>
    </style>
    <?php
}
add_action('wp_head', 'qassem_ultimate_output_css', 15);


/* ================================================================
   LIVE PREVIEW JS (postMessage)
   ================================================================ */
function qassem_ultimate_customizer_preview() {
    wp_enqueue_script('qassem-skin-preview',
        get_template_directory_uri() . '/assets/js/admin/skin-preview.js',
        ['customize-preview'], wp_get_theme()->get('Version'), true
    );
    wp_localize_script('qassem-skin-preview', 'QASSEM_SKINS', qassem_get_skins());
}
add_action('customize_preview_init', 'qassem_ultimate_customizer_preview');


/* ================================================================
   HELPERS
   ================================================================ */
function get_q($key, $default = '') {
    return get_theme_mod("qassem_{$key}", $default);
}
function get_q_bool($key, $default = true) {
    return (bool) get_theme_mod("qassem_{$key}", $default);
}
function qassem_active_skin() {
    $skins = qassem_get_skins();
    $active = get_theme_mod('qassem_active_skin', 'qassem');
    return $skins[$active] ?? $skins['qassem'];
}
function qassem_get_tabs_config() {
    $tabs = [];
    for ($i = 1; $i <= 5; $i++) {
        if (!get_q_bool("tab_{$i}_active", true)) continue;
        $tabs[] = [
            'label' => get_q("tab_{$i}_label", "Tab $i"),
            'icon'  => get_q("tab_{$i}_icon",  "fa-house"),
            'href'  => get_q("tab_{$i}_href",  "#home"),
        ];
    }
    return $tabs;
}
