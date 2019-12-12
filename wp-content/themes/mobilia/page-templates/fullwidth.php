<?php
/**
 * Template Name: Full Width
 *
 * Description: Full Width page template
 *
 * @package WordPress
 * @subpackage Mobilia_Theme
 * @since Huge Shop 1.0
 */
$mobilia_opt = get_option( 'mobilia_opt' );

get_header();
?>
<div class="main-container full-width">
	
	<div class="page-content">
		<div class="container">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', 'page' ); ?>
			<?php endwhile; ?>
		</div>
		<div class="home-static3 other-page">
			<div class="container">
				<?php if(isset($mobilia_opt['static_block3'])) {
					echo wp_kses($mobilia_opt['static_block3'], array(
						'a' => array(
						'class' => array(),
						'href' => array(),
						'title' => array()
						),
						'img' => array(
							'src' => array(),
							'alt' => array()
						),
						'strong' => array(),
						'h2' => array(),
						'p' => array(),
						'i' => array(),
					)); 
				} ?>
			</div>	
		</div>
	</div>
</div>
<?php get_footer(); ?>