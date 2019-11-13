<?php
/**
 * Template Name: Contact Template
 *
 * Description: A page template that provides a key component of WordPress as a CMS
 * by meeting the need for a carefully crafted introductory page. The front page template
 * in Huge Shop consists of a page content area for adding text, images, video --
 * anything you'd like -- followed by front-page-only widgets in one or two columns.
 *
 * @package WordPress
 * @subpackage Mobilia_Theme
 * @since Huge Shop 1.0
 */

$mobilia_opt = get_option( 'mobilia_opt' );

get_header();
?>
<div class="main-container contact-page">
	<div class="title-breadcrumb">
		<div class="container">
			<div class="title-breadcrumb-inner">
				<header class="entry-header">
					<h1 class="entry-title"><?php the_title(); ?></h1>
				</header>
				<?php Mobilia_Class::mobilia_breadcrumb(); ?>
			</div>
		</div>
	</div>
	<div class="page-content">
			<?php while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					
					<div class="entry-content">
						<?php the_content(); ?>
					</div><!-- .entry-content -->
				
				</article><!-- #post -->
			<?php endwhile; // end of the loop. ?>
	</div>
</div>
<?php
if(isset($mobilia_opt['enable_map']) && $mobilia_opt['enable_map']) :
	//Add google map API
	wp_enqueue_script( 'gmap-api-js', 'http://maps.google.com/maps/api/js?sensor=false' , array(), '3', false );
	// Add jquery.gmap.js file
	wp_enqueue_script( 'jquery.gmap-js', get_template_directory_uri() . '/js/jquery.gmap.js', array(), '2.1.5', false );

	$map_desc = str_replace(array("\r\n", "\r", "\n"), "<br />", $mobilia_opt['map_desc']);
	$map_desc = addslashes($map_desc);
?>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery('#map').gMap({
				scrollwheel: false,
				zoom: <?php echo esc_js($mobilia_opt['map_zoom']);?>,
				<?php if($mobilia_opt['address_by']=='address') : ?>
				address: "<?php echo  esc_js($mobilia_opt['map_address']);?>",
				<?php endif; ?>
				markers:[
					<?php if($mobilia_opt['address_by']=='coordinate') : ?>
					{
						latitude: <?php echo  esc_js($mobilia_opt['map_lat']);?>,
						longitude: <?php echo  esc_js($mobilia_opt['map_long']);?>,
						html: '<?php echo wp_kses($map_desc, array(
										'a' => array(
											'href' => array(),
											'title' => array()
										),
										'i' => array(
											'class' => array()
										),
										'img' => array(
											'src' => array(),
											'alt' => array()
										),
										'br' => array(),
										'em' => array(),
										'strong' => array(),
										'p' => array(),
									)); ?>',
						icon: {
							<?php if( isset($mobilia_opt['map_marker']['url']) && $mobilia_opt['map_marker']['url']!='') : ?>
							image: "<?php echo  esc_js($mobilia_opt['map_marker']['url']); ?>",
							<?php else : ?>
							image: "<?php echo get_template_directory_uri() . '/images/marker.png'; ?>",
							<?php endif; ?>
							iconsize: [40, 46],
							iconanchor: [40, 46]
						},
						popup: true
					}
					<?php else : ?>
					{
						address: "<?php echo  esc_js($mobilia_opt['map_address']);?>",
						html: '<?php echo wp_kses($map_desc, array(
										'a' => array(
											'href' => array(),
											'title' => array()
										),
										'i' => array(
											'class' => array()
										),
										'img' => array(
											'src' => array(),
											'alt' => array()
										),
										'br' => array(),
										'em' => array(),
										'strong' => array(),
										'p' => array(),
									)); ?>',
						icon: {
							<?php if( isset($mobilia_opt['map_marker']['url']) && $mobilia_opt['map_marker']['url']!='') : ?>
							image: "<?php echo  esc_js($mobilia_opt['map_marker']['url']); ?>",
							<?php else : ?>
							image: "<?php echo get_template_directory_uri() . '/images/marker.png'; ?>",
							<?php endif; ?>
							iconsize: [40, 46],
							iconanchor: [40, 46]
						},
						popup: true
					}
					<?php endif; ?>
				]
			});
		});
	</script>	
<?php endif; ?>
<?php get_footer('contact'); ?>