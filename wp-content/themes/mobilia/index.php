<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * For example, it puts together the home page when no home.php file exists.
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Mobilia_Theme
 * @since Huge Shop 1.0
 */

$mobilia_opt = get_option( 'mobilia_opt' );

get_header();

$bloglayout = 'sidebar';

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
		Mobilia_Class::mobilia_post_thumbnail_size('mobilia-category-thumb');
		break;
	case 'largeimage':
		$blogclass = 'blog-large';
		$blogcolclass = 9;
		Mobilia_Class::mobilia_post_thumbnail_size('mobilia-category-thumb');
		break;
	case 'grid':
		$blogclass = 'grid';
		$blogcolclass = 9;
		Mobilia_Class::mobilia_post_thumbnail_size('mobilia-category-thumb');
		break;
	default:
		$blogclass = 'blog-nosidebar';
		$blogcolclass = 12;
		$blogsidebar = 'none';
		Mobilia_Class::mobilia_post_thumbnail_size('mobilia-post-thumb');
}
?>

<div class="main-container"> 
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
	<?php if(isset($mobilia_opt['blog_slider_alias'])){
		if(is_home() && $mobilia_opt['blog_slider_alias']!=''){ ?>
			<div class="revo-container">
				<div class="container">
					<?php putRevSlider($mobilia_opt['blog_slider_alias']); ?>
				</div>  
			</div>
		<?php }
	}?> 
	<div class="container">
		
		<div class="row">
			<?php if($blogsidebar=='left') : ?>
				<?php get_sidebar(); ?>
			<?php endif; ?>
			
			<div class="col-xs-12 <?php echo 'col-md-'.$blogcolclass; ?>">
			
				<div class="page-content blog-page <?php echo esc_attr($blogclass); if($blogsidebar=='left') {echo ' left-sidebar'; } if($blogsidebar=='right') {echo ' right-sidebar'; } ?>">
					<?php if ( have_posts() ) : ?>

						<?php /* Start the Loop */ ?>
						<?php while ( have_posts() ) : the_post(); ?>
							
							<?php get_template_part( 'content', get_post_format() ); ?>
							
						<?php endwhile; ?>

						<div class="pagination">
							<?php Mobilia_Class::mobilia_pagination(); ?>
						</div>
						
					<?php else : ?>

						<article id="post-0" class="post no-results not-found">

						<?php if ( current_user_can( 'edit_posts' ) ) :
							// Show a different message to a logged-in user who can add posts.
						?>
							<header class="entry-header">
								<h1 class="entry-title"><?php esc_html_e( 'No posts to display', 'mobilia' ); ?></h1>
							</header>

							<div class="entry-content">
								<p><?php printf( wp_kses(__( 'Ready to publish your first post? <a href="%s">Get started here</a>.', 'mobilia' ), array('a'=>array('href'=>array()))), admin_url( 'post-new.php' ) ); ?></p>
							</div><!-- .entry-content -->

						<?php else :
							// Show the default message to everyone else.
						?>
							<header class="entry-header">
								<h1 class="entry-title"><?php esc_html_e( 'Nothing Found', 'mobilia' ); ?></h1>
							</header>

							<div class="entry-content">
								<p><?php esc_html_e( 'Apologies, but no results were found. Perhaps searching will help find a related post.', 'mobilia' ); ?></p>
								<?php get_search_form(); ?>
							</div><!-- .entry-content -->
						<?php endif; // end current_user_can() check ?>

						</article><!-- #post-0 -->

					<?php endif; // end have_posts() check ?>
				</div>
				
			</div>
			<?php if( $blogsidebar=='right') : ?>
				<?php get_sidebar(); ?>
			<?php endif; ?>
		</div>
	</div> 
</div>
<?php get_footer(); ?>