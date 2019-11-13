<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 41.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wc_print_notices();

do_action( 'woocommerce_before_cart' ); ?>

<form action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">

<?php do_action( 'woocommerce_before_cart_table' ); ?>

<table class="shop_table cart" cellspacing="0">
	<thead>
		<tr>
			<th class="product-thumbnail"></th>
			<th class="product-name"><?php esc_html_e( 'Product', 'mobilia' ); ?></th>
			<th class="product-price"><?php esc_html_e( 'Price', 'mobilia' ); ?></th>
			<th class="product-quantity"><?php esc_html_e( 'Quantity', 'mobilia' ); ?></th>
			<th class="product-subtotal"><?php esc_html_e( 'Total', 'mobilia' ); ?></th>
			<th class="product-remove"></th>
		</tr>
	</thead>
	<tbody>
		<?php do_action( 'woocommerce_before_cart_contents' ); ?>

		<?php
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				?>
				<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>" id="lcitem-<?php echo esc_attr($cart_item_key); ?>">

					<td class="product-thumbnail">
						<?php
							$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

							if ( ! $_product->is_visible() )
								echo wp_kses($thumbnail, array(
									'a'=>array(
										'href'=>array(),
										'title'=>array(),
										'class'=>array(),
									),
									'img'=>array(
										'src'=>array(),
										'height'=>array(),
										'width'=>array(),
										'class'=>array(),
										'alt'=>array(),
									)
								));
							else
								printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
						?>
					</td>

					<td class="product-name">
						<?php
							if ( ! $_product->is_visible() )
								echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;';
							else
								echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s </a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key );

							// Meta data
							echo wc_get_formatted_cart_item_data( $cart_item );

							// Backorder notification
							if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) )
								echo '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'mobilia' ) . '</p>';
						?>
					</td>

					<td class="product-price">
						<?php
							echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
						?>
					</td>

					<td class="product-quantity">
						<?php
							if ( $_product->is_sold_individually() ) {
								$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
							} else {
								$product_quantity = woocommerce_quantity_input( array(
									'input_name'  => "cart[{$cart_item_key}][qty]",
									'input_value' => $cart_item['quantity'],
									'max_value'   => $_product->get_max_purchase_quantity(),
									'min_value'   => '0'
								), $_product, false );
							}

							echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key );
						?>
					</td>

					<td class="product-subtotal">
						<?php
							echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
						?>
					</td>
					
					<td class="product-remove">
						<?php echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" onclick="roadMiniCartRemove(\'%s\', \'%s\');return false;" class="remove" title="%s"><i class="fa fa-times-circle"></i></a>', esc_url( wc_get_cart_remove_url( $cart_item_key ) ), esc_url( wc_get_cart_remove_url( $cart_item_key ) ), $cart_item_key, esc_html__( 'Remove this item', 'mobilia' ) ), $cart_item_key ); ?>
					</td>
				</tr>
				<?php
			}
		}

		do_action( 'woocommerce_cart_contents' );
		?>
		<?php do_action( 'woocommerce_after_cart_contents' ); ?>
	</tbody>
</table>

<div class="row">
	<div class="col-xs-12">
		<div class="buttons-cart">
			<input type="submit" class="button" name="update_cart" value="<?php esc_html_e( 'Update Cart', 'mobilia' ); ?>" />
			<a class="continue button" href="<?php echo get_permalink( wc_get_page_id( 'shop' ) );?>"><?php esc_html_e('Continue Shopping', 'mobilia');?></a>
		</div>
	</div>
	<div class="col-xs-12 col-md-8">
		<?php if ( wc_coupons_enabled() ) { ?>
			<div class="coupon">
				<h3><?php esc_html_e( 'Coupon', 'mobilia' ); ?></h3>
				<p><?php esc_html_e('Enter your coupon code if you have one.', 'mobilia');?></p>
				<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_html_e( 'Coupon code', 'mobilia' ); ?>" />
				<input type="submit" class="button" name="apply_coupon" value="<?php esc_html_e( 'Apply Coupon', 'mobilia' ); ?>" />

				<?php do_action( 'woocommerce_cart_coupon' ); ?>

			</div>
		<?php } ?>
		<?php do_action( 'woocommerce_cart_collaterals' ); ?>
	</div>
	<div class="col-xs-12 col-md-4">
		<?php woocommerce_cart_totals(); ?>
	</div>
</div>

<?php do_action( 'woocommerce_cart_actions' ); ?>

<?php wp_nonce_field( 'woocommerce-cart' ); ?>

<?php do_action( 'woocommerce_after_cart_table' ); ?>
<div class="loading"></div>
</form>

<?php do_action( 'woocommerce_after_cart' ); ?>
