<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
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

	<main id="primary" class="container singlepost site-main py-3">
		<div class="row mx-0">
			<div class="col-md-9">
			<?php
				while ( have_posts() ) :
					the_post();

					get_template_part( 'template-parts/content', get_post_type() );

					the_post_navigation(
						array(
							'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'custom-theme' ) . '</span> <span class="nav-title">%title</span>',
							'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'custom-theme' ) . '</span> <span class="nav-title">%title</span>',
						)
					);

					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;

				endwhile; // End of the loop.
				?>
			</div>
			<div class="col-md-3 bg-light p-4"><?php echo get_sidebar() ?></div>
		</div>

	</main><!-- #main -->
<?php
get_footer();
