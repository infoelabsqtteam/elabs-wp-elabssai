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
			<div class="col-md-9 categoryitemlist">
			<?php if ( have_posts() ) : ?>
				<header class="page-header mb-4">
					<?php
					 the_archive_title( '<h3 class="page-title">', '</h3>' );
					 the_archive_description( '<div class="archive-description">', '</div>' );
					?>
				</header><!-- .page-header -->

				<?php
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

				the_posts_navigation();

				else :

				get_template_part( 'template-parts/content', 'none' );

				endif;
				?>


			</div>
			<div class="col-md-3 bg-light p-4"><?php echo get_sidebar() ?></div>
		</div>
	</main><!-- #main -->
<?php
get_footer();
