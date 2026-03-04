<?php
/**
 * single.php — Single post/CPT template
 */
get_header();
?>

<main class="max-w-4xl mx-auto px-6 md:px-8 py-32" id="main-content">
    <?php while ( have_posts() ) : the_post(); ?>
    <article class="space-y-8">
        <?php if ( has_post_thumbnail() ) : ?>
        <div class="rounded-2xl overflow-hidden border border-white/10 max-h-[400px]">
            <?php the_post_thumbnail('full', ['class' => 'w-full h-full object-cover']); ?>
        </div>
        <?php endif; ?>
        <div class="premium-card p-8 md:p-14 rounded-3xl">
            <div class="mb-6">
                <span class="text-[9px] font-black uppercase tracking-widest text-rose-500">
                    <?php echo get_post_type_object(get_post_type())->labels->singular_name; ?>
                </span>
            </div>
            <h1 class="text-3xl md:text-5xl font-black italic mb-8 text-white"><?php the_title(); ?></h1>
            <div class="text-gray-400 leading-relaxed text-base md:text-lg">
                <?php the_content(); ?>
            </div>
        </div>
        <div class="flex justify-between items-center">
            <a href="<?php echo esc_url( get_home_url() ); ?>" class="btn-ghost flex items-center gap-2">
                <i class="fas fa-arrow-left text-[11px]"></i>
                <?php esc_html_e('Back to Portfolio', 'qassem'); ?>
            </a>
        </div>
    </article>
    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
