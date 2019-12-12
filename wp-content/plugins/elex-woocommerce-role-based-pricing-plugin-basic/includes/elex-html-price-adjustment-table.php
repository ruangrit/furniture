<?php
// to check whether accessed directly
if (!defined('ABSPATH')) {
	exit;
}
?>
<tr valign="top" >
    <td class="forminp" colspan="2" style="padding-left:0px">
            <?php
            global $wp_roles;           
            ?>
        <table class="price_adjustment widefat" id="eh_pricing_discount_price_adjustment_options">
            <thead>
            <th class="sort">&nbsp;</th>
            <th><?php _e('User Role', 'elex-catmode-rolebased-price'); ?></th>
            <th style="text-align:center;"><?php _e('Categories', 'elex-catmode-rolebased-price'); ?></th>
            <th style="text-align:center;"><?php echo sprintf(__('Price Adjustment (%s)', 'elex-catmode-rolebased-price'), get_woocommerce_currency_symbol()); ?></th>
            <th style="text-align:center;"><?php _e('Price Adjustment (%)', 'elex-catmode-rolebased-price'); ?></th>
            <th style="text-align:center;"><?php _e('Enable', 'elex-catmode-rolebased-price'); ?></th>
        </thead>
        <tbody>

            <?php
            $this->price_table = array();
            $i = 0;
            $decimal_steps = 1;
            $woo_decimal = wc_get_price_decimals();
            for ($temp=0;$temp<$woo_decimal;$temp++) {
                $decimal_steps = $decimal_steps/10;
            }
            $user_adjustment_price = get_option('eh_pricing_discount_price_adjustment_options');
            $wordpress_roles = $wp_roles->role_names;
            $wordpress_roles['unregistered_user'] = 'Unregistered User';
            if (empty($user_adjustment_price)) {
                foreach ($wordpress_roles as $id => $value) {
                    $this->price_table[$i]['id'] = $id;
                    $this->price_table[$i]['name'] = $value;
                    $this->price_table[$i]['category'] = '';
                    $this->price_table[$i]['adjustment_price'] = '';
                    $this->price_table[$i]['adjustment_percent'] = '';
                    $this->price_table[$i]['role_price'] = '';
                    $this->price_table[$i]['adj_price_dis'] = '';
                    $this->price_table[$i]['adj_percent_dis'] = '';
                    $i++;
                }
            } else {
                foreach ($user_adjustment_price as $id => $value) {
                    if (is_array($wordpress_roles) && key_exists($id, $wordpress_roles)) {
                        $this->price_table[$i]['id'] = $id;
                        $this->price_table[$i]['name'] = $wordpress_roles[$id];
                        $this->price_table[$i]['category'] = isset($this->user_adjustment_price[$id]['category']) ? $this->user_adjustment_price[$id]['category'] : '';
                        $this->price_table[$i]['adjustment_price'] = $this->user_adjustment_price[$id]['adjustment_price'];
                        $this->price_table[$i]['adjustment_percent'] = $this->user_adjustment_price[$id]['adjustment_percent'];
                        $this->price_table[$i]['adj_price_dis'] = isset($this->user_adjustment_price[$id]['adj_price_dis']) ? $this->user_adjustment_price[$id]['adj_price_dis']:'discount';
                        $this->price_table[$i]['adj_percent_dis'] = isset($this->user_adjustment_price[$id]['adj_percent_dis']) ? $this->user_adjustment_price[$id]['adj_percent_dis']:'discount';
                        if (key_exists('role_price', $this->user_adjustment_price[$id])) {
                            $this->price_table[$i]['role_price'] = $this->user_adjustment_price[$id]['role_price'];
                        } else {
                            $this->price_table[$i]['role_price'] = '';
                        }
                    }
                    $i++;
                    unset($wordpress_roles[$id]);
                }
                if (!empty($wordpress_roles)) {
                    foreach ($wordpress_roles as $id => $value) {
                        $this->price_table[$i]['id'] = $id;
                        $this->price_table[$i]['name'] = $value;
                        $this->price_table[$i]['category'] = '';
                        $this->price_table[$i]['adjustment_price'] = '';
                        $this->price_table[$i]['adjustment_percent'] = '';
                        $this->price_table[$i]['role_price'] = '';
                        $i++;
                    }
                }
            }
            $product_category = get_terms('product_cat', array('fields' => 'id=>name', 'hide_empty' => false, 'orderby' => 'title', 'order' => 'ASC',));
            foreach ($this->price_table as $key => $value) {
                ?>
                <tr>
                    <td class="sort">
                        <input type="hidden" class="order" name="eh_pricing_discount_price_adjustment_options[<?php echo $this->price_table[$key]['id'] ?>]" value="<?php echo $this->price_table[$key]['id']; ?>" />
                    </td>
                    <td style="width: 15%;">
                        <label name="eh_pricing_discount_price_adjustment_options[<?php echo $this->price_table[$key]['id']; ?>][name]" size="35" ><?php echo isset($this->price_table[$key]['name']) ? $this->price_table[$key]['name'] : ''; ?></label>
                    </td>
                    <td style="text-align:center;">
                        <select  data-placeholder="N/A" class="wc-enhanced-select" name="eh_pricing_discount_price_adjustment_options[<?php echo $this->price_table[$key]['id'] ?>][category][]"  multiple="multiple" style="width: 25%;float: left;">
                            <?php
                            foreach ($product_category as $id => $product_category_one) {
                                if (is_array($this->price_table[$key]['category']) && in_array($id, $this->price_table[$key]['category'])) {
                                    echo '<option value="' . $id . '" selected >' . $product_category_one . '</option>';
                                } else {
                                    echo '<option value="' . $id . '" >' . $product_category_one . '</option>';
                                }
                            }
                            ?>

                        </select>
                    </td>
                    <td style="text-align:center;">
                        <?php echo get_woocommerce_currency_symbol(); ?><input type="number" style="width:50% !important;" min="0" step="<?php echo $decimal_steps ?>" name="eh_pricing_discount_price_adjustment_options[<?php echo $this->price_table[$key]['id']; ?>][adjustment_price]" placeholder="N/A" value="<?php echo isset($this->price_table[$key]['adjustment_price']) ? $this->price_table[$key]['adjustment_price'] : ''; ?>" />
                        <?php
                        $select_price_dis = 'selected';
                        $select_price_mar = '';
                        if(isset($this->price_table[$key]['adj_percent_dis']) && $this->price_table[$key]['adj_price_dis'] == 'markup'){
                            $select_price_mar = 'selected';
                            $select_price_dis = '';
                        }
                        ?>
                        <select name="eh_pricing_discount_price_adjustment_options[<?php echo $this->price_table[$key]['id']; ?>][adj_price_dis]"><option value="discount" <?php echo $select_price_dis ?>>D</option><option value="markup" <?php echo $select_price_mar ?>>M</option></select>
                    </td>
                    <td style="text-align:center;">
                        <input type="number" style="width:50% !important;" min="0" step="<?php echo $decimal_steps ?>" name="eh_pricing_discount_price_adjustment_options[<?php echo $this->price_table[$key]['id']; ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset($this->price_table[$key]['adjustment_percent']) ? $this->price_table[$key]['adjustment_percent'] : ''; ?>"/>%
                        <?php
                        $select_percent_dis = 'selected';
                        $select_percent_mar = '';
                        if(isset($this->price_table[$key]['adj_percent_dis']) && $this->price_table[$key]['adj_percent_dis'] == 'markup'){
                            $select_percent_mar = 'selected';
                            $select_percent_dis = '';
                        }
                        ?>
                        <select name="eh_pricing_discount_price_adjustment_options[<?php echo $this->price_table[$key]['id']; ?>][adj_percent_dis]"><option value="discount" <?php echo $select_percent_dis ?>>D</option><option value="markup" <?php echo $select_percent_mar ?>>M</option></select>
                    </td>
                    <td style="text-align:center; width: 5%;">
                        <label>
                            <?php $checked = (!empty($this->price_table[$key]['role_price']) ) ? true : false; ?>
                            <input type="checkbox" name="eh_pricing_discount_price_adjustment_options[<?php echo $this->price_table[$key]['id']; ?>][role_price]" <?php checked($checked, true); ?> />
                        </label>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</td>
</tr>

<style type="text/css">
    .price_adjustment td {
        vertical-align: middle;
        padding: 4px 7px;
    }
    .price_adjustment th {
        padding: 9px 7px;
    }
    .price_adjustment td input {
        margin-right: 4px;
    }
    .price_adjustment .check-column {
        vertical-align: middle;
        text-align: left;
        padding: 0 7px;
    }
    .price_adjustment th.sort {
        width: 16px;
        padding: 0 16px;
    }
    .price_adjustment td.sort {
        cursor: move;
        width: 16px;
        padding: 0 16px;
        cursor: move;
        background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAYAAADED76LAAAAHUlEQVQYV2O8f//+fwY8gJGgAny6QXKETRgEVgAAXxAVsa5Xr3QAAAAASUVORK5CYII=) no-repeat center;					}
</style>