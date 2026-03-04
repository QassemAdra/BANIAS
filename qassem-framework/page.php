<?php
/**
 * page.php — Default single page template
 */
get_header();
?>

<main class="max-w-4xl mx-auto px-6 md:px-8 py-32" id="main-content">
    <?php while ( have_posts() ) : the_post(); ?>
    <article class="premium-card p-8 md:p-14 rounded-3xl">
        <h1 class="text-3xl md:text-5xl font-black italic mb-8 text-white"><?php the_title(); ?></h1>
        <div class="prose prose-invert prose-lg max-w-none text-gray-400 leading-relaxed">
            <?php the_content(); ?>
        </div>
    </article>
    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
