<?php
/**
 * Grouped product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/grouped.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce/Templates
 * @version     41.3.0
 */

defined( 'ABSPATH' ) || exit;

global $product, $post;

$parent_product_post = $post;

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="cart" method="post" enctype='multipart/form-data'>
	<div class="group_table">
		<?php
			foreach ( $grouped_products as $product_id ) :
				$product = wc_get_product( $product_id );
				$post    = $product->post;
				setup_postdata( $post );
				?>
					<div class="group-row">
						<div class="quantity-row">
							<?php if ( $product->is_sold_individually() || ! $product->is_purchasable() ) : ?>
								<?php woocommerce_template_loop_add_to_cart(); ?>
							<?php else : ?>
								<?php
									$quantites_required = true;
									woocommerce_quantity_input( array( 'input_name' => 'quantity[' . $product_id . ']', 'input_value' => '0' ) );
								?>
							<?php endif; ?>
						</div>

						<div class="label">
							<label>
								<?php
								if($product->is_visible()) {
									echo '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
								} else {
									echo get_the_title();
								}
								?>
							</label>
						</div>

						<?php do_action ( 'woocommerce_grouped_product_list_before_price', $product ); ?>

						<div class="price">
							<?php
								echo ''.$product->get_price_html();

								if ( $availability = $product->get_availability() ) {
									$availability_html = empty( $availability['availability'] ) ? '' : '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>';
									echo apply_filters( 'woocommerce_stock_html', $availability_html, $availability['availability'], $product );
								}
							?>
						</div>
					</div>
				<?php
			endforeach;

			// Reset to parent grouped product
			$post    = $parent_product_post;
			$product = wc_get_product( $parent_product_post->ID );
			setup_postdata( $parent_product_post );
		?>
	</div>
<?php if($product) {?>
	<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />

	<?php if ( $quantites_required ) : ?>

		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<button type="submit" class="single_add_to_cart_button button alt"><?php echo ''.$product->single_add_to_cart_text(); ?></button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<?php endif; ?>
<?php } else { ?>
	<?php 
		$productid = intval( $_POST['data'] );
		$product = wc_get_product( $productid );
	?>
	<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />

	<?php if ( $quantites_required ) : ?>

		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<button type="submit" class="single_add_to_cart_button button alt"><?php echo ''.$product->single_add_to_cart_text(); ?></button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<?php endif; ?>
<?php }?>
</form>

<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>