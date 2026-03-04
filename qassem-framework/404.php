<?php
/**
 * 404.php — Not found template
 */
get_header();
?>

<main class="min-h-screen flex flex-col items-center justify-center px-6 text-center" id="main-content">
    <p class="text-[9px] uppercase tracking-[1em] text-rose-500 font-black mb-6">404 Error</p>
    <h1 class="title-font text-[8rem] md:text-[14rem] font-black text-white/5 leading-none mb-0">404</h1>
    <h2 class="text-2xl md:text-4xl font-black italic text-white mb-6 -mt-4">
        <?php esc_html_e('Page Not Found', 'qassem'); ?>
    </h2>
    <p class="text-gray-400 max-w-md mb-10 text-base leading-relaxed">
        <?php esc_html_e('The page you are looking for might have been removed or temporarily unavailable.', 'qassem'); ?>
    </p>
    <a href="<?php echo esc_url( home_url('/') ); ?>" class="btn-world flex items-center gap-2">
        <i class="fas fa-house text-[11px]"></i>
        <?php esc_html_e('Return Home', 'qassem'); ?>
    </a>
</main>

<?php get_footer(); ?>
