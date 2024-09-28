<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
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
					<header class="page-header mb-5">
						<h3 class="page-title">
							<?php
							/* translators: %s: search query. */
							printf( esc_html__( 'Search Results for: %s', 'custom-theme' ), '<span>' . get_search_query() . '</span>' );
							?>
						</h3>
					</header><!-- .page-header -->

					<?php
					/* Start the Loop */
					while ( have_posts() ) :
						the_post();

						/**
						 * Run the loop for the search to output the results.
						 * If you want to overload this in a child theme then include a file
						 * called content-search.php and that will be used instead.
						 */
						get_template_part( 'template-parts/content', 'search' );

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
get_sidebar();
get_footer();
