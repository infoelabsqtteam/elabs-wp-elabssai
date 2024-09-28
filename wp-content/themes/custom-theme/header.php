<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package custom-theme
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#284a84" />
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="styleSheet">
	<?php wp_head(); ?>
   <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XT1D58RREP"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-XT1D58RREP');
</script>
</head>

<body <?php body_class(); ?>>
    <!-- Load Facebook SDK for JavaScript -->
<div id="fb-root"></div>
<script>
window.fbAsyncInit = function() {
  FB.init({
    xfbml            : true,
    version          : 'v17.0'
  });
};

(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<!-- Your Chat Plugin code -->
<div class="fb-customerchat"
  attribution="install_email"
  attribution_version="biz_inbox"
  page_id="106928411716133">
</div>

<?php wp_body_open(); ?>
<div id="page" class="site">
	<header id="masthead" class="topheader px-3 navbar navbar-expand-lg navbar-light bg-light py-0">
	  <div class="container">
	  	<div class="navbar-brand d-flex">
			<?php
			the_custom_logo();
			if ( is_front_page() && is_home() ) :
				?>
				<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				<?php
			else :
				?>
				<strong class="logoname ms-2 mt-1"><a style="color:#294a83;" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></strong>
				<?php
			endif;
			$custom_theme_description = get_bloginfo( 'description', 'display' );
			if ( $custom_theme_description || is_customize_preview() ) :
				?>
				<p class="site-description"><?php echo $custom_theme_description;?></p>
			<?php endif; ?>
		</div>
		<button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#site-navigation"><span class="navbar-toggler-icon"></span></button>
		<nav id="site-navigation" class="collapse navbar-collapse justify-content-end">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'menu-1',
					'menu_id'        => 'primary-menu',
				)
			);
			?>
		</nav>
	</div>
	</header>
