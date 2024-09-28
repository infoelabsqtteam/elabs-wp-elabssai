<?php
/**
 * Template Name: User Documents
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


<section class="doctitle py-5">
	<div class="container">
		<h5><strong>Documents List</strong></h5>
		<p class="m-0">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
	</div>
</section>


<section class="bg-light doclist">
    <div class="container">
        <div class="row">
            <div class="moduletitle" id="post-<?php the_ID(); ?>" <?php post_class(); ?>><?php the_title( '<h4>', '</h4>' ); ?></div>
            <?php query_posts(array( 'post_type' => 'documents' )); ?>
            <?php while (have_posts()) : the_post(); ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <h6><a href="<?php the_permalink() ?>" class="cardtitle"><?php the_title(); ?></a></h6>
                    <div class="doctxt"><?php the_excerpt(); ?></div>
                </div>
            </div>
            <?php endwhile;
				?>
        </div>
    </div>
</section>





<section class="docinfo">
    <div class="infoicon"><i class="fa-solid fa-circle-info"></i></div>
    <div class="docdetails">
        <div class="infotitle position-relative">
            <h4>On this page</h4>
            <span class="closesidebar" onClick="hideInfo()"><i class="fa-solid fa-circle-xmark"></i></span>
        </div>
        <div class="infotext px-4">
            <?php
                    if ( ! is_active_sidebar( 'documents-1' ) ) {
                        return;
                    }
                ?>
                <aside id="docuemnts" class="doclinks widget-area">
                <?php dynamic_sidebar( 'documents-1' ); ?>
            </aside>
        </div>
    </div>
</section>

<?php
get_footer();