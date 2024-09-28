<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package custom-theme
 */

get_header();
?>


	<main id="primary" class="container site-main py-3">

		
<?php $args = array( 'post_type' => 'documents', 'posts_per_page' => 20 );
            $loop = new WP_Query( $args );
            while ( $loop->have_posts() ) : $loop->the_post(); ?>
            <div class="services-items">
            <?php the_title(); 
        if ( has_post_thumbnail( $post->ID ) ) {
        echo '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( $post->post_title ) . '">';
        echo get_the_post_thumbnail( $post->ID, 'thumbnail' );
        echo '</a>'; }

?>
            </div>
    <?php endwhile; ?>




	<?php /* The loop */ ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <div class="main-post-div">
                <div class="single-page-post-heading">
                <h1><?php the_title(); ?></h1>
                </div>
                <div class="content-here">
                <?php  the_content();  ?>
                </div>
                <div class="comment-section-here"
                <?php //comments_template(); ?>
                </div>
                </div>

            <?php endwhile; ?>

	</main>
<?php
get_footer();
