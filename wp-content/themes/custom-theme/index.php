<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package custom-theme
 */

get_header();
?>


<section class="categorylist">
	<div class="container">
		<ul>
			<?php 
				$args = array(
				'style'      => 'list',
				'hide_empty' => 1,
				);
				wp_list_categories($args); 
			?>
		</ul>
	</div>
</section>


	<main id="primary" class="container site-main py-3">

	<div class="row mx-0">
			<div class="col-lg-9 py-3">
				<div class="row">
					<div class="col-md-9 bloglist">
					<div class="mb-2 blogslider"><?php echo do_shortcode('[recent_post_slider]'); ?></div>
						<?php
							if ( have_posts() ) :

								if ( is_home() && ! is_front_page() ) :
									?>
									<header>
										<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
									</header>
									<?php
								endif;

								/* Start the Loop */
								while ( have_posts() ) :
									the_post();

									/*
									* Include the Post-Type-specific template for the content.
									* If you want to override this in a child theme, then include a file
									* called content-___.php (where ___ is the Post Type name) and that will be used instead.
									*/
									get_template_part( 'template-parts/content', get_post_type() );

								endwhile;

								// the_posts_navigation();

							else :

								get_template_part( 'template-parts/content', 'none' );

							endif;
							?>

					</div>
					<div class="col-md-3">
						

					<?php
						$the_query = new WP_Query('category_name=videos');?>
						<?php if ( $the_query->have_posts() ) : ?>
						<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
							<div class="videopost">
								<?php custom_theme_post_thumbnail(); ?>
								<div class="video_title">
									<?php
									if ( is_singular() ) :
										the_title( '<h3 class="entry-title">', '</h3>' );
									else :
										the_title( '<h6 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h6>' );
									endif;
									?>
								</div>
							</div>
						<?php endwhile; ?>
					<?php else : ?>
					<br>
						<h4><?php _e( 'Sorry, no videos found...' ); ?></h4>
					<?php endif; ?>
					
					</div>
				</div>
			</div>
			<div class="col-lg-3 bg-light p-4"><?php echo get_sidebar() ?></div>
		</div>
	</main><!-- #main -->

<?php
get_footer();