<?php
/**
 * The Header template for our theme
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Mobilia_Theme
 * @since Huge Shop 1.0
 */
?><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<?php $mobilia_opt = get_option( 'mobilia_opt' ); ?>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="yith-wcwl-popup-message"><div id="yith-wcwl-message"></div></div>
<div class="wrapper <?php if($mobilia_opt['page_layout']=='box'){echo 'box-layout';}?>">
	<div class="page-wrapper">
		<?php if(isset($mobilia_opt['header_layout']) && $mobilia_opt['header_layout']!=''){
			$header_class = str_replace(' ', '-', strtolower($mobilia_opt['header_layout']));
		} else {
			$header_class = '';
		} ?>
		<div class="header-container <?php echo esc_html($header_class);?>">
			<div class="header">
				<div class="header-content">
					<?php
					if ( isset($mobilia_opt['header_layout']) && $mobilia_opt['header_layout']!="") {
						$jscomposer_templates_args = array(
							'orderby'          => 'title',
							'order'            => 'ASC',
							'post_type'        => 'templatera',
							'post_status'      => 'publish',
							'posts_per_page'   => 30,
						);
						$jscomposer_templates = get_posts( $jscomposer_templates_args );

						if(count($jscomposer_templates) > 0) {
							foreach($jscomposer_templates as $jscomposer_template){
								if($jscomposer_template->post_title == $mobilia_opt['header_layout']){
									echo do_shortcode($jscomposer_template->post_content);
								}
							}
						}
					} else {
						?>
						<div class="row">
							<div class="col-xs-12 col-lg-3">
								<?php if( isset($mobilia_opt['logo_main']['url']) && $mobilia_opt['logo_main']['url']!=''){ ?>
									<div class="logo"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><img src="<?php echo esc_url($mobilia_opt['logo_main']['url']); ?>" alt="" /></a></div>
								<?php
								} else { ?>
									<h1 class="logo"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
									<?php
								} ?>
							</div>
							<div class="col-xs-12 col-lg-9">
								<div class="nav-container">
									<div class="container">
										<div class="horizontal-menu visible-large">
											<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container_class' => 'primary-menu-container', 'menu_class' => 'nav-menu' ) ); ?>
										</div>
									</div>
								</div> 
							</div>
						</div>
						<div class="visible-small mobile-menu"> 
							<div class="mbmenu-toggler"><?php echo esc_html($mobilia_opt['mobile_menu_label']);?><span class="mbmenu-icon"><i class="fa fa-bars"></i></span></div>
						</div> 
						<?php
					} 
					?>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>