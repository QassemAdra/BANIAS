<?php
if (!defined('ABSPATH')) exit;

/* Save panel settings */
add_action('wp_ajax_qassem_save_panel', function () {
    check_ajax_referer('qassem_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error(['msg'=>'Unauthorized']);

    $map = [
        'skin'       => ['qassem_active_skin',          'sanitize_text_field'],
        'theme'      => ['qassem_default_theme',         'sanitize_text_field'],
        'brand'      => ['qassem_skin_brand_override',   'sanitize_hex_color'],
        'gold'       => ['qassem_skin_gold_override',    'sanitize_hex_color'],
        'title_font' => ['qassem_title_font',            'sanitize_text_field'],
        'body_font'  => ['qassem_body_font',             'sanitize_text_field'],
        'lang'       => ['qassem_default_lang',          'sanitize_text_field'],
        'hero_title' => ['qassem_hero_title',            'sanitize_text_field'],
        'hero_desc'  => ['qassem_hero_description',      'sanitize_textarea_field'],
    ];
    foreach ($map as $k => [$mod, $fn]) {
        if (isset($_POST[$k])) set_theme_mod($mod, $fn($_POST[$k]));
    }

    if (!empty($_POST['sections'])) {
        $s = json_decode(stripslashes($_POST['sections']), true);
        if (is_array($s)) foreach ($s as $k => $v) set_theme_mod('qassem_show_'.sanitize_key($k), (bool)$v);
    }
    if (!empty($_POST['section_order'])) {
        $o = json_decode(stripslashes($_POST['section_order']), true);
        if (is_array($o)) set_theme_mod('qassem_section_order', array_map('sanitize_key', $o));
    }
    if (!empty($_POST['features'])) {
        $f = json_decode(stripslashes($_POST['features']), true);
        if (is_array($f)) {
            $fm = ['canvas'=>'qassem_show_canvas','loader'=>'qassem_show_loader','animations'=>'qassem_enable_animations','progressBar'=>'qassem_show_progress_bar','backToTop'=>'qassem_show_back_to_top','floatContact'=>'qassem_show_float_contact','typing'=>'qassem_show_typing','langSwitcher'=>'qassem_show_lang_switcher','themeToggle'=>'qassem_show_theme_toggle'];
            foreach ($fm as $k => $m) if (isset($f[$k])) set_theme_mod($m, (bool)$f[$k]);
        }
    }
    if (!empty($_POST['tabs'])) {
        $t = json_decode(stripslashes($_POST['tabs']), true);
        if (is_array($t)) foreach ($t as $i => $tab) {
            $n = $i+1;
            set_theme_mod("qassem_tab_{$n}_label",  sanitize_text_field($tab['label']??''));
            set_theme_mod("qassem_tab_{$n}_icon",   sanitize_text_field($tab['icon']??''));
            set_theme_mod("qassem_tab_{$n}_href",   sanitize_text_field($tab['href']??''));
            set_theme_mod("qassem_tab_{$n}_active", (bool)($tab['active']??true));
        }
    }
    wp_send_json_success(['msg' => 'Saved!']);
});
