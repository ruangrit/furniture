<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Mobilia_Theme
 * @since Huge Shop 1.0
 */
 
$mobilia_opt = get_option( 'mobilia_opt' );
?>
			<?php if(isset($mobilia_opt['footer_layout']) && $mobilia_opt['footer_layout']!=''){
				$footer_class = str_replace(' ', '-', strtolower($mobilia_opt['footer_layout']));
			} else {
				$footer_class = '';
			} ?>

			<div class="footer <?php echo esc_html($footer_class);?>">
				<?php
				if ( isset($mobilia_opt['footer_layout']) && $mobilia_opt['footer_layout']!="" ) {

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
							if($jscomposer_template->post_title == $mobilia_opt['footer_layout']){
								echo do_shortcode($jscomposer_template->post_content);
							}
						}
					}
				} else { ?>
					<div class="widget-copyright">
						<?php 
						if( isset($mobilia_opt['copyright']) && $mobilia_opt['copyright']!='' ) {
							echo wp_kses($mobilia_opt['copyright'], array(
								'a' => array(
									'href' => array(),
									'title' => array()
								),
								'br' => array(),
								'em' => array(),
								'strong' => array(),
							));
						} else {
							echo wp_kses('Copyright <a href="'.esc_url( home_url( '/' ) ).'">'.get_bloginfo('name').'</a> '.date('Y').'. All Rights Reserved', array(
								'a' => array(
									'href' => array(),
									'title' => array()
								),
							));
						}
						?>
					</div>
				<?php
				}
				?>
			</div>
		</div><!-- .page -->
	</div><!-- .wrapper -->
	<!--<div class="mobilia_loading"></div>-->
	<?php if ( isset($mobilia_opt['back_to_top']) && $mobilia_opt['back_to_top'] ) { ?>
	<div id="back-top" class="hidden-xs hidden-sm hidden-md"></div>
	<?php } ?>
	<?php wp_footer(); ?> 
</body>
</html>