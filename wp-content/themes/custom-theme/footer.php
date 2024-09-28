<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package custom-theme
 */

?>

  <footer id="colophon" class="page-footer">
		<div class="container">
			<div class="row">
				<div class="col-sm-6 col-md-3 widget1">
					<?php
						if ( ! is_active_sidebar( 'footer-1' ) ) {
							return;
						}
					?>
					<aside id="footer1" class="widget-area">
					<?php dynamic_sidebar( 'footer-1' ); ?>
				</aside>
				</div>
				<div class="col-sm-6 col-md-3 widget2">
					<?php
							if ( ! is_active_sidebar( 'footer-2' ) ) {
								return;
							}
						?>
						<aside id="footer2" class="widget-area">
						<?php dynamic_sidebar( 'footer-2' ); ?>
					</aside>
				</div>
				
				<div class="col-sm-6 col-md-3 widget3">
					<?php
							if ( ! is_active_sidebar( 'footer-3' ) ) {
								return;
							}
						?>
						<aside id="footer3" class="widget-area">
						<?php dynamic_sidebar( 'footer-3' ); ?>
					</aside>
				</div>
				<div class="col-sm-6 col-md-3 widget4">
					<?php
							if ( ! is_active_sidebar( 'footer-4' ) ) {
								return;
							}
						?>
						<aside id="footer2" class="widget-area">
						<?php dynamic_sidebar( 'footer-4' ); ?>
					</aside>


					
				</div>
			 </div>
		</div>
	</footer>

<section class="copyright">
	<div class="container">
		<div class="row">
			<div class="col-sm-6"><p class="m-0">Copyright © 2024 Quality & Testing Infosolutions Pvt. Ltd. </p></div>
			<div class="col-sm-6"><div class="policylink"><a href="https://www.e-labs.ai/privacy-policy">Privacy Policy</a></div></div>
		</div>
	</div>
</section>



</div>

<?php wp_footer(); ?>



<div class="modal fade" id="videoplay">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header p-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body p-0">
	  	<iframe id="iframeYoutube" width="100%" height="400"  src="https://www.youtube.com/embed/h3Gf0KNS-EU" frameborder="0" allowfullscreen></iframe>
      </div>
    </div>
  </div>
</div>

</body>
</html>
