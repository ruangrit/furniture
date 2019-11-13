<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Mobilia_Theme
 * @since Huge Shop 1.0
 */

$mobilia_opt = get_option( 'mobilia_opt' );

get_header();

$bloglayout = 'nosidebar';
if(isset($mobilia_opt['blog_layout']) && $mobilia_opt['blog_layout']!=''){
	$bloglayout = $mobilia_opt['blog_layout'];
}
if(isset($_GET['layout']) && $_GET['layout']!=''){
	$bloglayout = $_GET['layout'];
}
$blogsidebar = 'right';
if(isset($mobilia_opt['sidebarblog_pos']) && $mobilia_opt['sidebarblog_pos']!=''){
	$blogsidebar = $mobilia_opt['sidebarblog_pos'];
}
if(isset($_GET['sidebar']) && $_GET['sidebar']!=''){
	$blogsidebar = $_GET['sidebar'];
}
switch($bloglayout) {
	case 'sidebar':
		$blogclass = 'blog-sidebar';
		$blogcolclass = 9;
		break;
	default:
		$blogclass = 'blog-nosidebar'; //for both fullwidth and no sidebar
		$blogcolclass = 12;
		$blogsidebar = 'none';
}
?>
<div class="main-container page-wrapper">
	<div class="blog-header-title">
		<div class="container">
			<div class="title-breadcrumb-inner">
				<header class="entry-header">
					<h1 class="entry-title"><?php if(isset($mobilia_opt)) { echo esc_html($mobilia_opt['blog_header_text']); } else { esc_html_e('Blog', 'mobilia');}  ?></h1>
				</header>
				<?php Mobilia_Class::mobilia_breadcrumb(); ?>
			</div>
		</div>
		
	</div>
	<div class="container">
		<div class="row">

			<?php
			$customsidebar = get_post_meta( $post->ID, '_mobilia_custom_sidebar', true );
			$customsidebar_pos = get_post_meta( $post->ID, '_mobilia_custom_sidebar_pos', true );

			if($customsidebar != ''){
				if($customsidebar_pos == 'left' && is_active_sidebar( $customsidebar ) ) {
					echo '<div id="secondary" class="col-xs-12 col-md-3">';
						dynamic_sidebar( $customsidebar );
					echo '</div>';
				} 
			} else {
				if($blogsidebar=='left') {
					get_sidebar();
				}
			} ?>
			
			<div class="col-xs-12 <?php echo 'col-md-'.$blogcolclass; ?>">
				<div class="page-content blog-page single <?php echo esc_attr($blogclass); if($blogsidebar=='left') {echo ' left-sidebar'; } if($blogsidebar=='right') {echo ' right-sidebar'; } ?>">
					<?php while ( have_posts() ) : the_post(); ?>

						<?php get_template_part( 'content', get_post_format() ); ?>

						<?php comments_template( '', true ); ?>
						
						<!--<nav class="nav-single">
							<h3 class="assistive-text"><?php esc_html_e( 'Post navigation', 'mobilia' ); ?></h3>
							<span class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'mobilia' ) . '</span> %title' ); ?></span>
							<span class="nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'mobilia' ) . '</span>' ); ?></span>
						</nav><!-- .nav-single -->
						
					<?php endwhile; // end of the loop. ?>
				</div>
			</div>
			<?php
			if($customsidebar != ''){
				if($customsidebar_pos == 'right' && is_active_sidebar( $customsidebar ) ) {
					echo '<div id="secondary" class="col-xs-12 col-md-3">';
						dynamic_sidebar( $customsidebar );
					echo '</div>';
				} 
			} else {
				if($blogsidebar=='right') {
					get_sidebar();
				}
			} ?>
		</div>
	</div> 
</div>

<?php get_footer(); ?>