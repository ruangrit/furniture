<?php
// to check whether accessed directly
if (!defined('ABSPATH')) {
	exit;
}

echo '<h3 style="text-align: center;"></h3>';
echo '<h3 style="text-align: center;">';
$user_roles = get_option('eh_pricing_discount_product_price_user_role');
if (is_array($user_roles) && !empty($user_roles)) {
    _e('Role Based Price', 'elex-catmode-rolebased-price');
    global $wp_roles;
    echo '</h3>';
    ?>
    <br/>
    <table class="product_role_based_price widefat" id="eh_pricing_discount_product_price_adjustment_data">
        <thead>
        <th class="sort">&nbsp;</th>
        <th><?php _e('User Role', 'elex-catmode-rolebased-price'); ?></th>
        <th><?php echo sprintf(__('Price (%s)', 'elex-catmode-rolebased-price'), get_woocommerce_currency_symbol()); ?></th>
    </thead>
    <tbody>

        <?php
        $this->price_table = array();
        $i = 0;
        global $post;
        $product_adjustment_price;
        $product_adjustment_price = get_post_meta($post->ID, 'product_role_based_price', false);
        foreach ($user_roles as $id => $value) {
            $this->price_table[$i]['id'] = $value;
            $this->price_table[$i]['name'] = $wp_roles->role_names[$value];
            if ((is_array($product_adjustment_price) && !empty($product_adjustment_price)) && is_array($product_adjustment_price[0]) && key_exists($value, $product_adjustment_price[0])) {
                $this->price_table[$i]['role_price'] = $product_adjustment_price[0][$value]['role_price'];
            }
            $i++;
        }
        foreach ($this->price_table as $key => $value) {
            ?>
            <tr>
                <td class="sort">
                    <input type="hidden" class="order" name="product_role_based_price[<?php echo $this->price_table[$key]['id'] ?>]" value="<?php echo $this->price_table[$key]['id']; ?>" />
                </td>
                <td>
                    <label name="product_role_based_price[<?php echo $this->price_table[$key]['id']; ?>][name]" style="margin-left:0px;"><?php echo isset($this->price_table[$key]['name']) ? $this->price_table[$key]['name'] : ''; ?></label>
                </td>
                <td>
                    <?php echo get_woocommerce_currency_symbol(); ?><input type="text" name="product_role_based_price[<?php echo $this->price_table[$key]['id']; ?>][role_price]" placeholder="N/A" value="<?php echo isset($this->price_table[$key]['role_price']) ? $this->price_table[$key]['role_price'] : ''; ?>" size="4" />
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
    </table>
<?php }
else {
    _e( 'Role Based Price ', 'elex-catmode-rolebased-price' ); 
    ?>
<table class="product_role_based_price widefat" id="eh_pricing_discount_product_price_adjustment_data">
<th><?php _e( 'For setting up user roles eligible for individual price adjustment, go to Role Based Pricing settings -> Add roles for the field "Individual Price Adjustment".', 'elex-catmode-rolebased-price' ); ?></th>
</table>
<?php
}
?>
<script>
    jQuery(document).ready(function () {
        jQuery('#product-type').change(function () {
            elex_rp_pricing_discount_product_role_based_price();
        });
        if (jQuery('#product-type').val() == 'variable') {
            elex_rp_pricing_discount_product_role_based_price();
        }
    });

    function elex_rp_pricing_discount_product_role_based_price() {
        product_type = jQuery('#product-type').val();
        switch (product_type) {
            case 'simple':
                jQuery('#general_role_based_price').show();
                break;

            case 'variable':
                jQuery('#general_role_based_price').hide();
                break;


        }
    }
</script>