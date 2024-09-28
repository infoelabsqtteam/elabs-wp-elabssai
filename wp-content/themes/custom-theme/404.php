<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package custom-theme
 */

get_header();
?>

	<main id="primary" class="container site-main py-3">


	<div class="row mx-0">
		<div class="col-md-9 py-3">
			<section class="error-404 not-found">
				<header class="page-header">
					<h1 class="page-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'custom-theme' ); ?></h1>
				</header>

				<div class="page-content">
					<p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'custom-theme' ); ?></p>

						<?php
						// get_search_form();

						// the_widget( 'WP_Widget_Recent_Posts' );
						?>

						<!-- .widget -->

						<?php
						/* translators: %1$s: smiley */
						$custom_theme_archive_content = '<p>' . sprintf( esc_html__( 'Try looking in the monthly archives. %1$s', 'custom-theme' ), convert_smilies( ':)' ) ) . '</p>';
						the_widget( 'WP_Widget_Archives', 'dropdown=1', "after_title=</h2>$custom_theme_archive_content" );

						the_widget( 'WP_Widget_Tag_Cloud' );
						?>

				</div><!-- .page-content -->
			</section>
		</div>
		<div class="col-md-3 bg-light p-4">
			<?php echo get_sidebar() ?>

			<div class="widget widget_categories">
							<h4 class="widget-title"><?php esc_html_e( 'Most Used Categories', 'custom-theme' ); ?></h4>
							<ul>
								<?php
								wp_list_categories(
									array(
										'orderby'    => 'count',
										'order'      => 'DESC',
										'show_count' => 1,
										'title_li'   => '',
										'number'     => 10,
									)
								);
								?>
							</ul>
						</div>
		</div>
	</div>

	</main><!-- #main -->

<?php
get_footer();
