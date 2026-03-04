<?php
/**
 * Footer Template
 */
$email     = get_option('qassem_email',     'contact@qassem.io');
$linkedin  = get_option('qassem_linkedin',  'https://linkedin.com/in/qassem-adra');
$twitter   = get_option('qassem_twitter',   'https://x.com/QassemAdra');
$github    = get_option('qassem_github',    'https://github.com/QassemAdra');
$facebook  = get_option('qassem_facebook',  'https://facebook.com/Qassem');
$instagram = get_option('qassem_instagram', 'https://instagram.com/QassemAdra');
$snapchat  = get_option('qassem_snapchat',  'https://snapchat.com/add/QasemAdra');
$cv_url    = get_option('qassem_cv_url',    get_template_directory_uri() . '/assets/QASSEM_CV.pdf');
$hero_title = get_option('qassem_hero_title', 'QASSEM');
?>

<footer class="relative pt-20 pb-12 bg-black/60 border-t border-white/5 overflow-hidden site-footer">
<div class="max-w-[1440px] mx-auto px-6 md:px-12 relative z-10">
<div class="grid lg:grid-cols-4 gap-12 md:gap-16 mb-16 items-start">

    <!-- Brand -->
    <div class="lg:col-span-2 space-y-8 order-1 lg:order-3 lg:text-end text-center">
        <div class="flex items-center gap-3 justify-center lg:justify-end">
            <h2 class="title-font text-3xl md:text-5xl font-black italic"><?php echo esc_html($hero_title); ?></h2>
            <span class="w-2 h-2 md:w-3 md:h-3 rounded-full bg-rose-600"></span>
        </div>
        <p class="text-gray-500 text-base md:text-xl leading-relaxed max-w-lg font-light italic mx-auto lg:mr-0 lg:ml-auto lg:text-end" data-i18n="footer.mission">
            <?php echo esc_html( get_bloginfo('description') ?: 'Architecting digital resilience and enterprise governance at global standards.' ); ?>
        </p>
        <!-- Social Icons -->
        <div class="flex flex-wrap gap-3.5 justify-center lg:justify-end pt-4 footer-socials">
            <?php if ($linkedin)  : ?><a class="social-icon li w-11 h-11" href="<?php echo esc_url($linkedin); ?>"  rel="noopener noreferrer" target="_blank"><i class="fab fa-linkedin-in text-xl"></i></a><?php endif; ?>
            <?php if ($twitter)   : ?><a class="social-icon tw w-11 h-11" href="<?php echo esc_url($twitter); ?>"   rel="noopener noreferrer" target="_blank"><i class="fab fa-x-twitter text-xl"></i></a><?php endif; ?>
            <?php if ($github)    : ?><a class="social-icon gh w-11 h-11" href="<?php echo esc_url($github); ?>"    rel="noopener noreferrer" target="_blank"><i class="fab fa-github text-xl"></i></a><?php endif; ?>
            <?php if ($facebook)  : ?><a class="social-icon fb w-11 h-11" href="<?php echo esc_url($facebook); ?>"  rel="noopener noreferrer" target="_blank"><i class="fab fa-facebook-f text-xl"></i></a><?php endif; ?>
            <?php if ($instagram) : ?><a class="social-icon ig w-11 h-11" href="<?php echo esc_url($instagram); ?>" rel="noopener noreferrer" target="_blank"><i class="fab fa-instagram text-xl"></i></a><?php endif; ?>
            <?php if ($snapchat)  : ?><a class="social-icon sc w-11 h-11" href="<?php echo esc_url($snapchat); ?>"  rel="noopener noreferrer" target="_blank"><i class="fab fa-snapchat text-xl"></i></a><?php endif; ?>
        </div>
    </div>

    <!-- Navigation -->
    <div class="space-y-6 hidden lg:block order-2 lg:text-start">
        <h4 class="text-[10px] font-black uppercase tracking-widest text-rose-600" data-i18n="footer.explore">
            <?php esc_html_e('EXPLORE', 'qassem'); ?>
        </h4>
        <ul class="flex flex-col gap-4 text-sm font-bold">
            <?php
            $nav_items = [
                '#about'       => __('Profil',         'qassem'),
                '#heritage'    => __('Héritage',       'qassem'),
                '#archive'     => __('Archive',        'qassem'),
                '#credentials' => __('Certifications', 'qassem'),
                '#contact'     => __('Contact',        'qassem'),
                '#portfolio'   => __('Galerie',        'qassem'),
            ];
            foreach ($nav_items as $href => $label) :
            ?>
            <li><a class="text-white/40 hover:text-rose-500 transition-colors" href="<?php echo esc_attr($href); ?>">
                <?php echo esc_html($label); ?>
            </a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Location -->
    <div class="space-y-6 order-3 lg:order-1 lg:text-start text-center">
        <h4 class="text-[10px] font-black uppercase tracking-widest text-rose-600" data-i18n="footer.location_title">
            <?php esc_html_e('LOCATION', 'qassem'); ?>
        </h4>
        <div class="space-y-6 text-gray-400 text-sm">
            <div class="flex items-center gap-4 justify-center lg:justify-start group">
                <i class="fas fa-location-dot text-rose-600"></i>
                <span class="font-bold group-hover:text-white transition-colors" data-i18n="footer.location_val">
                    <?php esc_html_e('Banias, Syrie / France', 'qassem'); ?>
                </span>
            </div>
            <div class="flex items-center gap-4 justify-center lg:justify-start group">
                <i class="fas fa-envelope text-rose-600"></i>
                <a href="mailto:<?php echo esc_attr($email); ?>" class="font-bold group-hover:text-rose-500 transition-colors">
                    <?php echo esc_html($email); ?>
                </a>
            </div>
            <div class="flex items-center gap-4 justify-center lg:justify-start group">
                <i class="fas fa-shield-halved text-rose-600"></i>
                <span class="font-bold group-hover:text-white transition-colors" data-i18n="footer.status">
                    <?php esc_html_e('Systems Online & Secure', 'qassem'); ?>
                </span>
            </div>
        </div>
    </div>

</div><!-- /.grid -->

<!-- Bottom Bar -->
<div class="pt-12 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-6 text-center md:text-start">
    <p class="text-[9px] md:text-[11px] font-black uppercase tracking-[0.5em] text-rose-600" data-i18n="footer.tagline">
        <?php esc_html_e('Résilience Numérique • ', 'qassem'); ?><?php echo esc_html(strtoupper($hero_title)); ?> ADRA
    </p>
    <div class="flex items-center gap-6 text-[8px] md:text-[10px] text-gray-700 font-bold uppercase tracking-widest">
        <span>&copy; <?php echo date('Y'); ?> <?php echo esc_html(strtoupper($hero_title)); ?> ADRA.</span>
        <button class="hover:text-rose-500 transition-colors flex items-center gap-2"
                onclick="window.scrollTo({top:0,behavior:'smooth'})" type="button">
            <?php esc_html_e('TOP', 'qassem'); ?>
            <i class="fas fa-chevron-up"></i>
        </button>
    </div>
</div>

</div><!-- /.container -->
</footer>

</div><!-- /#site-content -->

<?php get_template_part('template-parts/tabbar'); ?>

<!-- Reading Progress Bar -->
<div id="reading-progress" aria-hidden="true" style="position:fixed;top:0;left:0;width:0%;height:3px;background:linear-gradient(90deg,#e11d48,#d4af37);z-index:10000;transition:width .12s linear;border-radius:0 2px 2px 0;pointer-events:none;"></div>

<!-- Toast -->
<div id="toast" role="status" aria-live="polite" style="position:fixed;bottom:90px;left:50%;transform:translateX(-50%) translateY(20px);background:rgba(10,10,12,0.95);color:white;padding:12px 22px;border-radius:999px;font-size:12px;font-weight:700;border:1px solid rgba(255,255,255,0.12);backdrop-filter:blur(20px);z-index:99998;opacity:0;transition:opacity .3s,transform .3s;white-space:nowrap;pointer-events:none;">
    <span id="toast-icon" style="margin-right:8px;"></span><span id="toast-msg"></span>
</div>

<!-- Floating Contact -->
<div id="float-contact" style="position:fixed;bottom:100px;right:20px;z-index:4998;display:flex;flex-direction:column;align-items:flex-end;gap:10px;opacity:0;transform:translateY(20px);transition:opacity .35s,transform .35s;pointer-events:none;">
    <div id="float-contact-menu" style="display:flex;flex-direction:column;gap:8px;opacity:0;transform:translateY(10px) scale(0.95);transition:opacity .25s,transform .25s;pointer-events:none;">
        <a href="mailto:<?php echo esc_attr($email); ?>" style="display:flex;align-items:center;gap:10px;padding:10px 16px;background:rgba(10,10,12,0.92);border:1px solid rgba(255,255,255,0.12);border-radius:999px;color:white;text-decoration:none;font-size:11px;font-weight:700;backdrop-filter:blur(20px);white-space:nowrap;">
            <i class="fas fa-envelope" style="color:var(--brand,#e11d48);"></i> Email
        </a>
        <?php if ($linkedin) : ?>
        <a href="<?php echo esc_url($linkedin); ?>" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:center;gap:10px;padding:10px 16px;background:rgba(10,10,12,0.92);border:1px solid rgba(255,255,255,0.12);border-radius:999px;color:white;text-decoration:none;font-size:11px;font-weight:700;backdrop-filter:blur(20px);white-space:nowrap;">
            <i class="fab fa-linkedin-in" style="color:#0077b5;"></i> LinkedIn
        </a>
        <?php endif; ?>
        <button onclick="window.copyEmail()" style="display:flex;align-items:center;gap:10px;padding:10px 16px;background:rgba(10,10,12,0.92);border:1px solid rgba(255,255,255,0.12);border-radius:999px;color:white;font-size:11px;font-weight:700;backdrop-filter:blur(20px);cursor:pointer;white-space:nowrap;">
            <i class="fas fa-copy" style="color:#d4af37;"></i>
            <?php esc_html_e('Copy email', 'qassem'); ?>
        </button>
    </div>
    <button id="float-btn" onclick="window.toggleFloat()" aria-label="<?php esc_attr_e('Contact', 'qassem'); ?>"
            style="width:52px;height:52px;border-radius:50%;background:#e11d48;color:white;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:20px;box-shadow:0 8px 30px rgba(225,29,72,0.45);transition:.35s;">
        <i class="fas fa-comment-dots" id="float-icon"></i>
    </button>
</div>

<!-- Back to Top -->
<button id="back-to-top" aria-label="<?php esc_attr_e('Back to top', 'qassem'); ?>"
        onclick="window.scrollTo({top:0,behavior:'smooth'})"
        style="position:fixed;bottom:100px;right:76px;width:44px;height:44px;border-radius:50%;background:#e11d48;color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;z-index:4999;opacity:0;transform:translateY(20px);transition:opacity .3s,transform .3s;box-shadow:0 8px 25px rgba(225,29,72,0.35);pointer-events:none;">
    <i class="fas fa-chevron-up"></i>
</button>

<script>
// Pass email to JS for copy function
window.QASSEM_EMAIL = <?php echo json_encode( $email ); ?>;
</script>

<?php wp_footer(); ?>
</body>
</html>
