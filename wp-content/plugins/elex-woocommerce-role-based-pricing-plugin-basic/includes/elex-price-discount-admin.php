<?php
// to check whether accessed directly
if (!defined('ABSPATH')) {
	exit;
}


class Elex_Price_Discount_Admin {

    public function __construct($execute = true) {

        $this->sales_method = 'regular';
        if ($execute == true) {
            $this->elex_rp_add_filter_for_get_price();

            
            add_action('woocommerce_single_product_summary', array($this, 'elex_rp_product_page_remove_add_to_cart_option')); //function to remove add to cart at product page
            
            add_filter('woocommerce_loop_add_to_cart_link', array($this, 'elex_rp_shop_remove_add_to_cart'), 100, 2); // function to remove add to cart from shop page
           // add_action('wp_head', array($this, 'custom_css_for_add_to_cart'));

            add_filter('woocommerce_is_purchasable', array(&$this, 'elex_rp_is_product_purchasable'), 10, 2); //to hide add to cart button when price is hidden
            add_filter('woocommerce_loop_add_to_cart_link', array($this, 'elex_rp_add_to_cart_text_url_replace'), 1, 2); //to replace add to cart with user defined url
            add_filter('woocommerce_product_single_add_to_cart_text', array($this, 'elex_rp_add_to_cart_text_content_replace'), 1, 1); //to replace add to cart with user defined placeholder text for product page
            add_filter('woocommerce_get_price_html', array($this, 'elex_rp_get_price_html'), 9, 2); //to modify display for various options of settings page
            
            //------------
            add_filter('woocommerce_product_is_on_sale', array($this, 'elex_rp_product_is_on_sale'), 99, 2);
            add_filter('woocommerce_product_add_to_cart_text', array($this, 'elex_rp_view_product_text'), 99, 2);
        }
        //----for price filter
        add_filter('woocommerce_price_filter_widget_min_amount', array($this, 'elex_rp_get_min_price'), 100, 1);
        add_filter('woocommerce_price_filter_widget_max_amount', array($this, 'elex_rp_get_max_price'), 100, 1);
        //----------
        add_action( 'wp', array( $this, 'elex_rp_hide_cart_checkout_pages' ) );
        $this->init_fields();
    }

    public function elex_rp_hide_cart_checkout_pages() {
        $hide = FALSE;
        if ('yes' == get_option('eh_pricing_discount_cart_catalog_mode_remove_cart_checkout')) {
            if(! (get_option('eh_pricing_discount_price_catalog_mode_exclude_admin') == 'yes' && $this->current_user_role =='administrator')) {
                $hide = TRUE;
            }
        }
        elseif (is_user_logged_in()) {
            $remove_settings_cart_roles = get_option('eh_pricing_discount_cart_user_role_remove_cart_checkout');
            if (is_array($remove_settings_cart_roles) && in_array($this->current_user_role, $remove_settings_cart_roles)) {
                $hide = TRUE;
            }
        }
        else {
            if ('yes' == get_option('eh_pricing_discount_cart_unregistered_user_remove_cart_checkout')) {
                $hide = TRUE;
            }
        }
            $cart     = is_page( wc_get_page_id( 'cart' ) );
            $checkout = is_page( wc_get_page_id( 'checkout' ) );

            wp_reset_query();
            if ( $hide && ($cart || $checkout) ) {

                    wp_redirect( home_url() );
                    exit;
            }
    }

    public function elex_rp_shop_remove_add_to_cart($args, $product) {
        if($this->elex_rp_get_product_type($product) == 'variable' || $this->elex_rp_get_product_type($product) == 'grouped'){
            return $args;
        }
        $product_id = $this->elex_rp_get_product_id($product);
        $add_to_cart_link = $args;
        
        if (is_user_logged_in()) {
            $remove_settings_cart_roles = get_option('eh_pricing_discount_cart_user_role');
            $remove_product_cart_roles = get_post_meta($product_id, 'eh_pricing_adjustment_product_addtocart_user_role', true);
            if (is_array($remove_product_cart_roles) && in_array($this->current_user_role, $remove_product_cart_roles)) {               
                $add_to_cart_link = '';
                $this->elex_rp_get_add_to_cart_product_placeholder_text($product_id);
            } elseif (is_array($remove_settings_cart_roles) && in_array($this->current_user_role, $remove_settings_cart_roles)) {
                $add_to_cart_link = '';
                $this->elex_rp_get_add_to_cart_placeholder_text();
            }
        } else {
            if ('yes' == (get_post_meta($product_id, 'product_adjustment_hide_addtocart_unregistered', true))) {
                $add_to_cart_link = '';
                $this->elex_rp_get_add_to_cart_product_placeholder_text($product_id);
            } elseif ('yes' == get_option('eh_pricing_discount_cart_unregistered_user')) {
                $add_to_cart_link = '';
                $this->elex_rp_get_add_to_cart_placeholder_text();
            }
        }
        
        return $add_to_cart_link;
    }



    public function elex_rp_get_min_price($price) {
        $user_roles = get_option('eh_pricing_discount_product_price_user_role');
        if (is_array($user_roles) && in_array($this->current_user_role, $user_roles)) {
            global $wpdb;
            $all_product_data = $wpdb->get_results("SELECT ID FROM `" . $wpdb->prefix . "posts` where post_type='product' and post_status = 'publish'");
            $min_prices = array();
            for ($i = 0; $i < count($all_product_data); $i++) {
                $p_id = $all_product_data[$i]->{'ID'};
                $product_data = wc_get_product($p_id);
                if ($product_data->is_type('simple')) {
                    $min_prices[$i] = $product_data->get_price();
                } elseif ($product_data->is_type('variable')) {
                    $prices = $product_data->get_variation_prices(true);
                    if (empty($prices['price'])) {
                        continue;
                    }
                    foreach ($prices['price'] as $pid => $old_price) {
                        $pobj = wc_get_product($pid);
                        $prices['price'][$pid] = wc_get_price_to_display($pobj);
                    }
                    $min_prices[$i] = min($prices['price']);
                }
            }
            $price = min($min_prices);
        }
        return $price;
    }

    public function elex_rp_get_max_price($price) {
        $user_roles = get_option('eh_pricing_discount_product_price_user_role');

        if (is_array($user_roles) && in_array($this->current_user_role, $user_roles)) {
            global $wpdb;
            $all_product_data = $wpdb->get_results("SELECT ID FROM `" . $wpdb->prefix . "posts` where post_type='product' and post_status = 'publish'");
            $max_prices = array();
            for ($i = 0; $i < count($all_product_data); $i++) {
                $p_id = $all_product_data[$i]->{'ID'};
                $product_data = wc_get_product($p_id);
                if ($product_data->is_type('simple')) {
                    $max_prices[$i] = $product_data->get_price();
                } elseif ($product_data->is_type('variable')) {
                    $prices = $product_data->get_variation_prices(true);
                    if (empty($prices['price'])) {
                        continue;
                    }
                    foreach ($prices['price'] as $pid => $old_price) {
                        $pobj = wc_get_product($pid);
                        $prices['price'][$pid] = wc_get_price_to_display($pobj);
                    }
                    $max_prices[$i] = max($prices['price']);
                }
            }
            $price = max($max_prices);
        }
        return $price;
    }

    public function elex_rp_view_product_text($text, $product) {
        if ($this->elex_rp_is_hide_price($product) === true) {
            $text = 'Read more';
        }
        return $text;
    }

    public function elex_rp_product_is_on_sale($on_sale, $product) {
        if ($this->elex_rp_get_product_type($product) == 'grouped' || $this->elex_rp_get_product_type($product) == 'variable' || $this->elex_rp_get_product_type($product) == 'variation') {
            return $on_sale;
        }
        if ($this->elex_rp_is_hide_price($product) === true || $this->elex_rp_is_hide_regular_price($product)) {
            $on_sale = false;
        } else {
            if ($this->elex_rp_get_product_type($product) != 'grouped') {
                $regular_price = $product->get_regular_price();
                $sale_price = $product->get_price();
                if (empty($sale_price)) {
                    $on_sale = false;
                } else {
                    if ($sale_price < $regular_price) {
                        $on_sale = true;
                    }
                }
            }
        }
        return $on_sale;
    }


    // function to hide simple product from grouped product
    public function elex_rp_add_filter_for_get_price() {
        if (WC()->version < '2.7.0') {
            if ($this->sales_method === 'regular') {
                add_filter('woocommerce_get_regular_price', array($this, 'elex_rp_get_price'), 99, 2); //function to modify product sale price
            } else {
                add_filter('woocommerce_get_sale_price', array($this, 'elex_rp_get_price'), 99, 2); //function to modify product sale price
            }
            add_filter('woocommerce_get_price', array($this, 'elex_rp_get_price'), 99, 2); //function to modify product price at all level
        } else {
            if ($this->sales_method === 'regular') {
                add_filter('woocommerce_product_get_regular_price', array($this, 'elex_rp_get_price'), 99, 2);
                add_filter('woocommerce_product_variation_get_regular_price', array($this, 'elex_rp_get_price'), 99, 2);
                add_filter('woocommerce_get_variation_regular_price', array($this, 'elex_rp_get_price'), 99, 2);
            } else {
                add_filter('woocommerce_product_get_sale_price', array($this, 'elex_rp_get_price'), 99, 2);
            }
            add_filter('woocommerce_product_get_price', array($this, 'elex_rp_get_price'), 99, 2);
            add_filter('woocommerce_product_variation_get_price', array($this, 'elex_rp_get_price'), 99, 2);
        }
    }

    public function elex_rp_remove_filter_for_get_price() {
        if (WC()->version < '2.7.0') {
            if ($this->sales_method === 'regular') {
                remove_filter('woocommerce_get_regular_price', array($this, 'elex_rp_get_price'), 99, 2); //function to modify product sale price
            } else {
                remove_filter('woocommerce_get_sale_price', array($this, 'elex_rp_get_price'), 99, 2); //function to modify product sale price
            }
            remove_filter('woocommerce_get_price', array($this, 'elex_rp_get_price'), 99, 2); //function to modify product price at all level
        } else {
            if ($this->sales_method === 'regular') {
                remove_filter('woocommerce_product_get_regular_price', array($this, 'elex_rp_get_price'), 99, 2);
                remove_filter('woocommerce_product_variation_get_regular_price', array($this, 'elex_rp_get_price'), 99, 2);
                remove_filter('woocommerce_get_variation_regular_price', array($this, 'elex_rp_get_price'), 99, 2);
            } else {
                remove_filter('woocommerce_product_get_sale_price', array($this, 'elex_rp_get_price'), 99, 2);
            }
            remove_filter('woocommerce_product_get_price', array($this, 'elex_rp_get_price'), 99, 2);
            remove_filter('woocommerce_product_variation_get_price', array($this, 'elex_rp_get_price'), 99, 2);
        }
    }


    function elex_rp_get_add_to_cart_placeholder_text() {
        if (is_user_logged_in()) {
            $add_to_cart_text = get_option('eh_pricing_discount_cart_user_role_text');
        } else {
            $add_to_cart_text = get_option('eh_pricing_discount_cart_unregistered_user_text');
        }

        if (!empty($add_to_cart_text)) {
            echo $add_to_cart_text;
        }
    }
    function elex_rp_get_add_to_cart_product_placeholder_text($product_id) {
        if (is_user_logged_in()) {
            $add_to_cart_text = get_post_meta($product_id, 'product_adjustment_hide_addtocart_placeholder_role',true);
        } else {
            $add_to_cart_text = get_post_meta($product_id, 'product_adjustment_hide_addtocart_placeholder_unregistered',true);
        }

        if (!empty($add_to_cart_text)) {
            echo $add_to_cart_text;
        }
    }

    public function elex_rp_product_page_remove_add_to_cart_option() {
        global $product;
        $product_id = $this->elex_rp_get_product_id($product);
        if ($this->elex_rp_get_product_type($product) == 'simple') {
        if (is_user_logged_in()) {
            $remove_settings_cart_roles = get_option('eh_pricing_discount_cart_user_role');
            $remove_product_cart_roles = get_post_meta($product_id, 'eh_pricing_adjustment_product_addtocart_user_role', true);
            $replace_product_cart_roles = get_post_meta($product_id, 'eh_pricing_adjustment_product_customize_addtocart_user_role', true);
            $replace_cart_user_role = get_option('eh_pricing_discount_replace_cart_user_role');
            if (is_array($remove_product_cart_roles) && in_array($this->current_user_role, $remove_product_cart_roles)) {
                $this->elex_rp_remove_add_to_cart_action_product_page($product);
                $this->elex_rp_get_add_to_cart_product_placeholder_text($product_id);
            }
            elseif (is_array($replace_product_cart_roles) && in_array($this->current_user_role, $replace_product_cart_roles)) {
                $url_product_page = get_post_meta($product_id, 'product_adjustment_customize_addtocart_btn_url_role', true);
                $button_text_product_page = get_post_meta($product_id, 'product_adjustment_customize_addtocart_prod_btn_text_role', true);
                    if($url_product_page !='' && $button_text_product_page !=''){
                        $this->elex_rp_remove_add_to_cart_action_product_page($product);
                        $this->elex_rp_redirect_addtocart_product_page($url_product_page,$button_text_product_page);
                    }
            }
            elseif (is_array($remove_settings_cart_roles) && in_array($this->current_user_role, $remove_settings_cart_roles)) {
                $this->elex_rp_remove_add_to_cart_action_product_page($product);
                $this->elex_rp_get_add_to_cart_placeholder_text();
            }
            elseif(is_array($replace_cart_user_role) && in_array($this->current_user_role, $replace_cart_user_role)){
                if($this->replace_add_to_cart_user_role_url_shop !='' && $this->replace_add_to_cart_user_role_button_text_product !=''){
                    $this->elex_rp_remove_add_to_cart_action_product_page($product);
                    $this->elex_rp_redirect_addtocart_product_page($this->replace_add_to_cart_user_role_url_shop,$this->replace_add_to_cart_user_role_button_text_product);
                }
            }
        } else {
            if ('yes' == (get_post_meta($product_id, 'product_adjustment_hide_addtocart_unregistered', true))) {
                $this->elex_rp_remove_add_to_cart_action_product_page($product);
                $this->elex_rp_get_add_to_cart_product_placeholder_text($product_id);
            }
            elseif('yes' == get_post_meta($product_id, 'product_adjustment_customize_addtocart_unregistered', true)){
                $url_product_page = get_post_meta($product_id, 'product_adjustment_customize_addtocart_btn_url_unregistered', true);
                $button_text_product_page = get_post_meta($product_id, 'product_adjustment_customize_addtocart_prod_btn_text_unregistered', true);
                if($url_product_page !='' && $button_text_product_page !=''){
                    $this->elex_rp_remove_add_to_cart_action_product_page($product);
                    $this->elex_rp_redirect_addtocart_product_page($url_product_page,$button_text_product_page);
                }
            }
            elseif ('yes' == get_option('eh_pricing_discount_cart_unregistered_user')) {
                $this->elex_rp_remove_add_to_cart_action_product_page($product);
                $this->elex_rp_get_add_to_cart_placeholder_text();
            }
            elseif('yes' == get_option('eh_pricing_discount_replace_cart_unregistered_user')){
                if($this->replace_add_to_cart_button_url_shop !='' && $this->replace_add_to_cart_button_text_product !=''){
                    $this->elex_rp_remove_add_to_cart_action_product_page($product);
                    $this->elex_rp_redirect_addtocart_product_page($this->replace_add_to_cart_button_url_shop, $this->replace_add_to_cart_button_text_product);
                }
            }
        }
    }
    }
    
    function elex_rp_remove_add_to_cart_action_product_page($product) {
        if( $this->elex_rp_get_product_type($product) == 'variable') {
        remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
        }
        else {
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
        }
    }
            
    function elex_rp_redirect_addtocart_product_page($url_product_page,$button_text_product_page){
        $secure = strpos('https://', $url_product_page);
        $url_product_page = str_replace('https://', '', $url_product_page);
        $url_product_page = str_replace('http://', '', $url_product_page);
        $suff = ($secure === false) ? 'http://' : 'https://';
        ?>
        <button class="btn btn-success" onclick=" window.open('<?php echo $suff.$url_product_page ?>','_self')"><?php echo $button_text_product_page ?></button>
        <?php
    }

    public function init_fields() {
        $this->role_price_adjustment = get_option('eh_pricing_discount_price_adjustment_options', array());
        $this->current_user_role = $this->elex_rp_get_priority_user_role(wp_get_current_user()->roles, $this->role_price_adjustment);
        $this->enable_role_tax = get_option('eh_pricing_discount_enable_tax_options') == 'yes' ? true : false;
        $this->role_tax_option = get_option('eh_pricing_discount_price_tax_options', array());
        $this->tax_user_role = $this->elex_rp_get_priority_user_role(wp_get_current_user()->roles, $this->role_tax_option);
        $this->price_suffix_option = get_option('eh_pricing_discount_enable_price_suffix', 'none');
        $this->general_price_suffix = get_option('eh_pricing_discount_price_general_price_suffix', '');
        $this->role_price_suffix = get_option('eh_pricing_discount_role_price_suffix', array());
        $this->suffix_user_role = $this->elex_rp_get_priority_user_role(wp_get_current_user()->roles, $this->role_price_suffix);
        $this->price_suffix_user_role = $this->suffix_user_role != '' ? $this->suffix_user_role : 'unregistered_user';
        $this->replace_add_to_cart = get_option('eh_pricing_discount_replace_cart_unregistered_user') == 'yes' ? true : false;
        $this->replace_add_to_cart_button_text_product = get_option('eh_pricing_discount_replace_cart_unregistered_user_text_product', '');
        $this->replace_add_to_cart_button_text_shop = get_option('eh_pricing_discount_replace_cart_unregistered_user_text_shop', '');
        $this->replace_add_to_cart_button_url_shop = get_option('eh_pricing_discount_replace_cart_unregistered_user_url_shop', '');
        $this->replace_add_to_cart_user_role = get_option('eh_pricing_discount_replace_cart_user_role', array());
        $this->replace_add_to_cart_user_role_button_text_product = get_option('eh_pricing_discount_replace_cart_user_role_text_product', '');
        $this->replace_add_to_cart_user_role_button_text_shop = get_option('eh_pricing_discount_replace_cart_user_role_text_shop', '');
        $this->replace_add_to_cart_user_role_url_shop = get_option('eh_pricing_discount_replace_cart_user_role_url_shop', '');
        $this->individual_product_adjustment_roles = get_option('eh_pricing_discount_product_price_user_role', array());
    }

    //function to determine the user role to use in case of multiple user roles for one user
    public function elex_rp_get_priority_user_role($user_roles, $role_priority_list) {
        if (is_user_logged_in()) {
            if (!empty($role_priority_list)) {
                foreach ($role_priority_list as $id => $value) {
                    if (in_array($id, $user_roles)) {
                        return $id;
                    }
                }
            } else {
                return $user_roles[0];
            }
        } else {
            return 'unregistered_user';
        }
    }

    //function to replace add to cart with another url for user role and unregistered user 
    public function elex_rp_add_to_cart_text_url_replace($link, $product) {
        $temp_data = $this->elex_rp_get_product_type($product);
        $product_id = $this->elex_rp_get_product_id($product);
        $cart_text_content = $link;
        if ($temp_data === 'simple') {
           if ((is_user_logged_in())) {
                $role_shop_btn_text = get_post_meta($product_id, 'product_adjustment_customize_addtocart_shop_btn_text_role',true);
                $role_btn_url = get_post_meta($product_id, 'product_adjustment_customize_addtocart_btn_url_role',true);
                $replace_addtocart = get_post_meta($product_id, 'eh_pricing_adjustment_product_customize_addtocart_user_role',true);
                if (is_array($replace_addtocart) && in_array($this->current_user_role, $replace_addtocart) && $role_shop_btn_text != '') {
                    if (empty($role_btn_url)) {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop($cart_text_content,$role_shop_btn_text);
                    }
                    else {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop_with_url($cart_text_content,$role_shop_btn_text,$role_btn_url);
                    }
                }

                elseif (is_array($this->replace_add_to_cart_user_role) && in_array($this->current_user_role, $this->replace_add_to_cart_user_role) && $this->replace_add_to_cart_user_role_button_text_shop != '') {
                    if (empty($this->replace_add_to_cart_user_role_url_shop)) {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop($cart_text_content,$this->replace_add_to_cart_user_role_button_text_shop);
                    } else {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop_with_url($cart_text_content,$this->replace_add_to_cart_user_role_button_text_shop,$this->replace_add_to_cart_user_role_url_shop);
                    }
                }
            }
            elseif (!is_user_logged_in()) {
                $unregistered_shop_btn_text = get_post_meta($product_id, 'product_adjustment_customize_addtocart_shop_btn_text_unregistered',true);
                $unregistered_btn_url = get_post_meta($product_id, 'product_adjustment_customize_addtocart_btn_url_unregistered',true);
                if ('yes' == (get_post_meta($product_id, 'product_adjustment_customize_addtocart_unregistered', true)) && $unregistered_shop_btn_text != '') {
                    if (empty($unregistered_btn_url)) {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop($cart_text_content,$unregistered_shop_btn_text);
                    }
                    else {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop_with_url($cart_text_content,$unregistered_shop_btn_text,$unregistered_btn_url);
                    }
                }
                elseif ($this->replace_add_to_cart && $this->replace_add_to_cart_button_text_shop != '') {
                    if (empty($this->replace_add_to_cart_button_url_shop)) {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop($cart_text_content,$this->replace_add_to_cart_button_text_shop);
                    } else {
                        $cart_text_content = $this-> elex_rp_replace_add_cart_text_shop_with_url($cart_text_content,$this->replace_add_to_cart_button_text_shop,$this->replace_add_to_cart_button_url_shop);
                    }
                }
            }
        }
        return $cart_text_content;
    }
    function elex_rp_replace_add_cart_text_shop ($cart_text_content,$shop_addtocart_text) {
        $cart_text_content = str_replace('Add to cart', $shop_addtocart_text, $cart_text_content);
        $cart_text_content = str_replace('Select options', $shop_addtocart_text, $cart_text_content);
        $cart_text_content = str_replace('View products', $shop_addtocart_text, $cart_text_content);
        return $cart_text_content;
    }
    function elex_rp_replace_add_cart_text_shop_with_url ($cart_text_content,$shop_addtocart_text,$url) {
        $secure = strpos('https://', $url);
        $url = str_replace('https://', '', $url);
        $url = str_replace('http://', '', $url);
        $suff = ($secure === false) ? 'http://' : 'https://';
        $cart_text_content = '<a href="' . $suff . $url . '" class="button alt">' . $shop_addtocart_text . '</a>';
        return $cart_text_content;
    }



    //function to edit add to cart text of product page with placeholder text when replace add to cart button is selected

    public function elex_rp_add_to_cart_text_content_replace($text) {
        $cart_text_content = $text;
         global $product;
        if ($this->elex_rp_get_product_type($product) == 'variable' || $this->elex_rp_get_product_type($product) == 'grouped') {
            return $cart_text_content;
        }
       
        $product_id = $this->elex_rp_get_product_id($product);
       if ((is_user_logged_in())) {
            $individual_prod_btn_text = get_post_meta($product_id, 'product_adjustment_customize_addtocart_prod_btn_text_role',true);
            $replace_addtocart = get_post_meta($product_id, 'eh_pricing_adjustment_product_customize_addtocart_user_role',true);
            if (is_array($replace_addtocart) && in_array($this->current_user_role, $replace_addtocart) && $individual_prod_btn_text != '') {
                $cart_text_content = $individual_prod_btn_text;
            }
            elseif (is_array($this->replace_add_to_cart_user_role) && in_array($this->current_user_role, $this->replace_add_to_cart_user_role) && $this->replace_add_to_cart_user_role_button_text_product != '') {
                $cart_text_content = $this->replace_add_to_cart_user_role_button_text_product;
            }
        }
        elseif (!is_user_logged_in()) {
            $individual_prod_btn_text = get_post_meta($product_id, 'product_adjustment_customize_addtocart_prod_btn_text_unregistered',true);
            if ('yes' == (get_post_meta($product_id, 'product_adjustment_customize_addtocart_unregistered', true)) && $individual_prod_btn_text != '') {
                $cart_text_content = $individual_prod_btn_text;
            }
            elseif ($this->replace_add_to_cart && $this->replace_add_to_cart_button_text_product != '') {
                $cart_text_content = $this->replace_add_to_cart_button_text_product;
            }
        }
        return $cart_text_content;
    }

    //to get category ids for a product
    public function elex_rp_get_product_category_using_id($prod_id) {
        $terms = get_the_terms($prod_id, 'product_cat');
        if ($terms) {
            $cats_ids_array = array();
            foreach ($terms as $key => $term) {
                array_push($cats_ids_array, $term->term_id);
                $term2 = $term;

                if (!in_array($term2->parent, $cats_ids_array)) {
                    while ($term2->parent > 0) {
                        array_push($cats_ids_array, $term2->parent);
                        $term2 = get_term_by("id", $term2->parent, "product_cat");
                    }
                }
            }
            return $cats_ids_array;
        }
        return array();
    }

    public function elex_rp_get_price($price = '', $product = null) {
        if (empty($price)) {
            return $price;
        }
        if(doing_filter('woocommerce_get_cart_item_from_session')){
            return $price;
        }
        if ($this->elex_rp_is_hide_price($product)) {
            if( $this->elex_rp_get_product_type($product) == 'variation') {
                    remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
                    return false;
                }
            if ($this->elex_rp_is_price_hidden_in_product_meta($product)) {
                $price = '';
            } else {
                $price = '';
            }
            return $price;
        }
        
        //If decimal seperator is ',' and thousand seperator is '.'
        $dec_seperator = wc_get_price_decimal_separator();
        
        $pid = $this->elex_rp_get_product_id($product);
        $temp_data = $this->elex_rp_get_product_type($product);
        
        if( apply_filters('xa_pbu_skip_product',false,$pid) != false || apply_filters('xa_pbu_skip_product_on_sale',false,$pid) != false ) {
            //Role Based Price (individual product page price change)
            if ( $temp_data == 'variation' ) {
                $pid = $this->elex_rp_get_product_parent_id($product);
            }
            $enforce_button_check_for_product = get_post_meta($pid, 'product_based_price_adjustment', true);
            $product_price_adjustment = get_post_meta($pid, 'product_price_adjustment', true);
            if ( !($enforce_button_check_for_product == 'yes' && isset($product_price_adjustment[$this->current_user_role]) && isset($product_price_adjustment[$this->current_user_role]['role_price']) && $product_price_adjustment[$this->current_user_role]['role_price'] == 'on' )) {
                $product_user_price = get_post_meta($pid, 'product_role_based_price');
                if ( is_array($product_user_price) && isset($product_user_price[0]) && !empty($product_user_price[0]) ) {
                    $product_user_price = $product_user_price[0];
                }
                if ( !empty($product_user_price) && is_array($this->individual_product_adjustment_roles) && in_array($this->current_user_role, $this->individual_product_adjustment_roles) ) {
                    if ( isset($product_user_price[$this->current_user_role]) ) {
                        $product_user_price_value = $product_user_price[$this->current_user_role]['role_price'];
                        $product_user_price_value = $this->elex_rp_fix_price_decimal_format( $dec_seperator, $product_user_price_value); 
                        if (is_numeric($product_user_price_value)) {
                            $price = $product_user_price_value;
                        }
                    }
                }
                return $price;
            }
        }
        if ( $temp_data == 'variation' ) {
            $pid = $this->elex_rp_get_product_parent_id($product);
        }
           
        //----------------------analyse this for bugs
        //price adjustment display for discount when price adjustment on both regular and sale price
        if ($this->sales_method == 'regular' && (doing_filter('woocommerce_product_get_regular_price') || doing_filter('woocommerce_product_variation_get_regular_price') || doing_filter('woocommerce_get_variation_regular_price'))) {
            $adjustment_value = 0;
            if (is_array($this->individual_product_adjustment_roles) && in_array($this->current_user_role, $this->individual_product_adjustment_roles)) {
                //individual product page price adjustment (discount/markup from settings page))
                $enforce_button_check_for_product = get_post_meta($pid, 'product_based_price_adjustment', true);
                $product_price_adjustment = get_post_meta($pid, 'product_price_adjustment', true);

                if ($enforce_button_check_for_product == 'yes' && isset($product_price_adjustment[$this->current_user_role]) && isset($product_price_adjustment[$this->current_user_role]['role_price']) && $product_price_adjustment[$this->current_user_role]['role_price'] == 'on') {
                    $current_user_product_rule = $product_price_adjustment[$this->current_user_role];

                    $current_user_product_rule['adjustment_price'] = $this->elex_rp_fix_price_decimal_format( $dec_seperator, $current_user_product_rule['adjustment_price']); 
                    if (!empty($current_user_product_rule['adjustment_price']) && is_numeric($current_user_product_rule['adjustment_price'])) {
                       if(isset($current_user_product_rule['adj_prod_price_dis']) && $current_user_product_rule['adj_prod_price_dis'] == 'markup') {
                        $adjustment_value += (float) $current_user_product_rule['adjustment_price'];
                       }
                       else {
                            $adjustment_value -= (float) $current_user_product_rule['adjustment_price'];
                       }
                    }

                    $current_user_product_rule['adjustment_percent'] = $this->elex_rp_fix_price_decimal_format( $dec_seperator, $current_user_product_rule['adjustment_percent']); 
                    if (!empty($current_user_product_rule['adjustment_percent']) && is_numeric($current_user_product_rule['adjustment_percent'])) {
                       if(isset($current_user_product_rule['adj_prod_percent_dis']) && $current_user_product_rule['adj_prod_percent_dis'] == 'markup') {
                        $adjustment_value += $price * ((float) $current_user_product_rule['adjustment_percent']) / 100;
                       }
                       else {
                           $adjustment_value -= $price * ((float) $current_user_product_rule['adjustment_percent']) / 100;
                       }
                    }
                    //discount/markup ajustment to $price
                    $price += $adjustment_value;
                    $this->elex_rp_add_filter_for_get_price();
                    return $price;
                }

                //common page adjustment
                if ($temp_data === 'variation') {
                    $prdct_id = $this->elex_rp_get_product_category_using_id($this->elex_rp_get_product_parent_id($product));
                } else {
                    if (WC()->version < '2.7.0') {
                        $temp_post_id = $product->post->ID;
                    } else {
                        $temp_post_id = $product->get_id();
                    }
                    $prdct_id = $this->elex_rp_get_product_category_using_id($temp_post_id);
                }

                $common_price_adjustment_table = get_option('eh_pricing_discount_price_adjustment_options', array());
                if (isset($common_price_adjustment_table[$this->current_user_role]) && isset($common_price_adjustment_table[$this->current_user_role]['role_price']) && $common_price_adjustment_table[$this->current_user_role]['role_price'] == 'on') {
                    $current_user_product_rule = $common_price_adjustment_table[$this->current_user_role];

                    $current_user_product_rule['adjustment_price'] = $this->elex_rp_fix_price_decimal_format( $dec_seperator, $current_user_product_rule['adjustment_price']); 
                    if (!empty($current_user_product_rule['adjustment_price']) && is_numeric($current_user_product_rule['adjustment_price'])) {
                        $adjustment_value = $this->elex_rp_adjust_price_for_user_roles($prdct_id,$current_user_product_rule,$temp_data,$adjustment_value);
                    }

                    $current_user_product_rule['adjustment_percent'] = $this->elex_rp_fix_price_decimal_format( $dec_seperator, $current_user_product_rule['adjustment_percent']); 
                    if (!empty($current_user_product_rule['adjustment_percent']) && is_numeric($current_user_product_rule['adjustment_percent'])) {
                        $adjustment_value = $this->elex_rp_adjust_percent_for_user_roles($prdct_id,$current_user_product_rule,$price,$temp_data,$adjustment_value);
                    }
                    //discount/markup ajustment to $price
                    $price += $adjustment_value;
                }
                $this->elex_rp_add_filter_for_get_price();
                return $price;
            } else {
                $temp_data = $this->elex_rp_get_product_type($product);
                if ($temp_data === 'variation') {
                    $prdct_id = $this->elex_rp_get_product_category_using_id($this->elex_rp_get_product_parent_id($product));
                } else {
                    if (WC()->version < '2.7.0') {
                        $temp_post_id = $product->post->ID;
                    } else {
                        $temp_post_id = $product->get_id();
                    }
                    $prdct_id = $this->elex_rp_get_product_category_using_id($temp_post_id);
                }
                $adjustment_value = 0;
                $common_price_adjustment_table = get_option('eh_pricing_discount_price_adjustment_options', array());
                if (isset($common_price_adjustment_table[$this->current_user_role]) && isset($common_price_adjustment_table[$this->current_user_role]['role_price']) && $common_price_adjustment_table[$this->current_user_role]['role_price'] == 'on') {
                    $current_user_product_rule = $common_price_adjustment_table[$this->current_user_role];

                    $current_user_product_rule['adjustment_price'] = $this->elex_rp_fix_price_decimal_format( $dec_seperator, $current_user_product_rule['adjustment_price']); 
                    if (!empty($current_user_product_rule['adjustment_price']) && is_numeric($current_user_product_rule['adjustment_price'])) {
                        $adjustment_value = $this->elex_rp_adjust_price_for_user_roles($prdct_id,$current_user_product_rule,$temp_data,$adjustment_value);
                    }

                    $current_user_product_rule['adjustment_percent'] = $this->elex_rp_fix_price_decimal_format( $dec_seperator, $current_user_product_rule['adjustment_percent']); 
                    if (!empty($current_user_product_rule['adjustment_percent']) && is_numeric($current_user_product_rule['adjustment_percent'])) {
                        $adjustment_value = $this->elex_rp_adjust_percent_for_user_roles($prdct_id,$current_user_product_rule,$price,$temp_data,$adjustment_value);
                    }
                    //discount/markup ajustment to $price
                    $price += $adjustment_value;
                }
                $this->elex_rp_add_filter_for_get_price();
                return $price;
            }
        }
        //------------------------
        $this->elex_rp_remove_filter_for_get_price();
        $pid = $this->elex_rp_get_product_id($product);
        $temp_data = $this->elex_rp_get_product_type($product);

        $adjustment_value = 0;
        if (is_array($this->individual_product_adjustment_roles) && in_array($this->current_user_role, $this->individual_product_adjustment_roles)) {
            //Role Based Price (individual product page price change)
            $product_user_price = get_post_meta($pid, 'product_role_based_price');
            if (is_array($product_user_price) && isset($product_user_price[0]) && !empty($product_user_price[0])) {
                $product_user_price = $product_user_price[0];
            }
            if (!empty($product_user_price)) {
                if (isset($product_user_price[$this->current_user_role])) {
                    $product_user_price_value = $product_user_price[$this->current_user_role]['role_price'];

                    $product_user_price_value = $this->elex_rp_fix_price_decimal_format( $dec_seperator, $product_user_price_value); 
                    if (is_numeric($product_user_price_value)) {
                        $price = $product_user_price_value;
                    }
                }
            }
            if ($temp_data == 'variation') {
                $pid = $this->elex_rp_get_product_parent_id($product);
            }
            //individual product page price adjustment (discount/markup from settings page))
            $enforce_button_check_for_product = get_post_meta($pid, 'product_based_price_adjustment', true);
            $product_price_adjustment = get_post_meta($pid, 'product_price_adjustment', true);

            if ($enforce_button_check_for_product == 'yes' && isset($product_price_adjustment[$this->current_user_role]) && isset($product_price_adjustment[$this->current_user_role]['role_price']) && $product_price_adjustment[$this->current_user_role]['role_price'] == 'on') {
                $current_user_product_rule = $product_price_adjustment[$this->current_user_role];
                
                $current_user_product_rule['adjustment_price'] = $this->elex_rp_fix_price_decimal_format( $dec_seperator, $current_user_product_rule['adjustment_price']); 
                if (!empty($current_user_product_rule['adjustment_price']) && is_numeric($current_user_product_rule['adjustment_price'])) {
                    if(isset($current_user_product_rule['adj_prod_price_dis']) && $current_user_product_rule['adj_prod_price_dis'] == 'markup') {
                        $adjustment_value += (float) $current_user_product_rule['adjustment_price'];
                    }
                    else {
                        $adjustment_value -= (float) $current_user_product_rule['adjustment_price'];
                    }
                }
                $current_user_product_rule['adjustment_percent'] = $this->elex_rp_fix_price_decimal_format( $dec_seperator, $current_user_product_rule['adjustment_percent']); 
                if (!empty($current_user_product_rule['adjustment_percent']) && is_numeric($current_user_product_rule['adjustment_percent'])) {
                    if(isset($current_user_product_rule['adj_prod_percent_dis']) && $current_user_product_rule['adj_prod_percent_dis'] == 'markup') {
                        $adjustment_value += $price * ((float) $current_user_product_rule['adjustment_percent']) / 100;
                    }
                    else {
                        $adjustment_value -= $price * ((float) $current_user_product_rule['adjustment_percent']) / 100;
                    }
                }
                //discount/markup ajustment to $price
                $price += $adjustment_value;
                $this->elex_rp_add_filter_for_get_price();
                return $price;
            }
        }
        //common price adjustment 
        add_filter('woocommerce_available_variation', function ($value, $object = null, $variation = null) {
            if ($value['price_html'] == '') {
                $value['price_html'] = '<span class="price">' . $variation->get_price_html() . '</span>';
            }
            return $value;
        }, 10, 3);
        if ($temp_data === 'variation') {
            $prdct_id = $this->elex_rp_get_product_category_using_id($this->elex_rp_get_product_parent_id($product));
        } else {
            if (WC()->version < '2.7.0') {
                $temp_post_id = $product->post->ID;
            } else {
                $temp_post_id = $product->get_id();
            }
            $prdct_id = $this->elex_rp_get_product_category_using_id($temp_post_id);
        }

        $common_price_adjustment_table = get_option('eh_pricing_discount_price_adjustment_options', array());
        if (isset($common_price_adjustment_table[$this->current_user_role]) && isset($common_price_adjustment_table[$this->current_user_role]['role_price']) && $common_price_adjustment_table[$this->current_user_role]['role_price'] == 'on') {
            $current_user_product_rule = $common_price_adjustment_table[$this->current_user_role];
            
            $current_user_product_rule['adjustment_price'] = $this->elex_rp_fix_price_decimal_format( $dec_seperator, $current_user_product_rule['adjustment_price']); 
            if (!empty($current_user_product_rule['adjustment_price']) && is_numeric($current_user_product_rule['adjustment_price'])) {
                $adjustment_value = $this->elex_rp_adjust_price_for_user_roles($prdct_id,$current_user_product_rule,$temp_data,$adjustment_value);
            }
            $current_user_product_rule['adjustment_percent'] = $this->elex_rp_fix_price_decimal_format( $dec_seperator, $current_user_product_rule['adjustment_percent']); 
            if (!empty($current_user_product_rule['adjustment_percent']) && is_numeric($current_user_product_rule['adjustment_percent'])) {
                $adjustment_value = $this->elex_rp_adjust_percent_for_user_roles($prdct_id,$current_user_product_rule,$price,$temp_data,$adjustment_value);
            }
            //discount/markup ajustment to $price
            $price += $adjustment_value;
        }
        $this->elex_rp_add_filter_for_get_price();

        return $price;
    }
    
    function elex_rp_adjust_percent_for_user_roles ($prdct_id,$current_user_product_rule,$price,$temp_data,$adjustment_value) {
        if (isset($this->role_price_adjustment[$this->current_user_role]['category'])) {
            $cat_display = $this->role_price_adjustment[$this->current_user_role]['category'];
            if ($temp_data != 'grouped')
                $result_chk = array_intersect($prdct_id, $cat_display);
            if (empty($result_chk)) {
                $adjustment_value = 0;
            } else {
                if(isset($current_user_product_rule['adj_percent_dis']) && $current_user_product_rule['adj_percent_dis'] == 'markup') {
                    $adjustment_value += $price * ((float) $current_user_product_rule['adjustment_percent']) / 100;
                }
                else {
                    $adjustment_value -= $price * ((float) $current_user_product_rule['adjustment_percent']) / 100;
                }
            }
        } else {
            if(isset($current_user_product_rule['adj_percent_dis']) && $current_user_product_rule['adj_percent_dis'] == 'markup') {
                $adjustment_value += $price * ((float) $current_user_product_rule['adjustment_percent']) / 100;
            }
            else {
                $adjustment_value -= $price * ((float) $current_user_product_rule['adjustment_percent']) / 100;
            }
        }
        return $adjustment_value;
    }
    
    function elex_rp_adjust_price_for_user_roles($prdct_id,$current_user_product_rule,$temp_data,$adjustment_value) {
        if (isset($this->role_price_adjustment[$this->current_user_role]['category'])) {
            $cat_display = $this->role_price_adjustment[$this->current_user_role]['category'];
            if ($temp_data != 'grouped')
                $result_chk = array_intersect($prdct_id, $cat_display);
            if (empty($result_chk)) {
                $adjustment_value = 0;
            } else {
                if(isset($current_user_product_rule['adj_price_dis']) && $current_user_product_rule['adj_price_dis'] == 'markup') {
                    $adjustment_value += (float) $current_user_product_rule['adjustment_price'];
                }
                else {
                    $adjustment_value -= (float) $current_user_product_rule['adjustment_price'];
                }
            }
        } else {
                if(isset($current_user_product_rule['adj_price_dis']) && $current_user_product_rule['adj_price_dis'] == 'markup') {
                    $adjustment_value += (float) $current_user_product_rule['adjustment_price'];
                }
                else {
                    $adjustment_value -= (float) $current_user_product_rule['adjustment_price'];
                }
        }
        return $adjustment_value;
        
    }


    private function elex_rp_fix_price_decimal_format( $dec_seperator, $price_to_update ){
        if ( $dec_seperator == ',' ) {
            if ( strpos($price_to_update, ',') ) {
                $updated_price = str_replace(',', '.', $price_to_update);
            }
            else {
                $updated_price = str_replace('.', ',', $price_to_update);
            }
        }
        else {
            $updated_price = $price_to_update;
        }
        
        return $updated_price;
    }

    public function elex_rp_get_price_html($price = '', $product) {
        if ($this->elex_rp_get_product_type($product) == 'simple') {
            if ($product->is_on_sale() && $this->elex_rp_is_hide_regular_price($product) === false) {
                $price = wc_format_sale_price(wc_get_price_to_display($product, array('price' => $product->get_regular_price())), wc_get_price_to_display($product)) . $product->get_price_suffix();
            } else {
                $price = wc_price(wc_get_price_to_display($product)) . $product->get_price_suffix();
            }
            if ($this->elex_rp_is_hide_price($product)) {
                if ($this->elex_rp_is_price_hidden_in_product_meta($product)) {
                    $price = $this->elex_rp_get_placeholder_text_product_hide_price($product);
                } else {
                    $price = $this->elex_rp_get_placeholder_text($product, $price);
                }
            }
        } 
        
        return apply_filters('eh_pricing_adjustment_modfiy_price', $this->elex_rp_pricing_add_price_suffix($price, $product), $this->current_user_role);
    }

    function elex_rp_is_hide_regular_price($product) {
        $hide = false;
        if (!is_user_logged_in()) {
            $hide = get_option('eh_pricing_discount_hide_regular_price_unregistered', 'no') == 'yes';
        } else {
            $remove_settings_regular_price_roles = get_option('eh_pricing_discount_regular_price_user_role', array());
            if (is_array($remove_settings_regular_price_roles) && in_array($this->current_user_role, $remove_settings_regular_price_roles)) {
                $hide = true;
            }
        }
        return $hide;
    }

    function elex_rp_is_hide_price($product) {
        $hide = false;
        $product_id = $this->elex_rp_get_product_id($product);
        $temp_data = $this->elex_rp_get_product_type($product);
        if ($temp_data == 'variation') {
            $product_id = $this->elex_rp_get_product_parent_id($product);
        }
        if (get_option('eh_pricing_discount_price_catalog_mode') == 'yes') {
            if(get_option('eh_pricing_discount_price_catalog_mode_exclude_admin') == 'yes' && $this->current_user_role =='administrator') {
            $hide = false;
            }
            else
                $hide = true;
        }
        
        elseif('yes' == get_post_meta($product_id, 'product_adjustment_hide_price_catalog', true)){
            if(! (get_post_meta($product_id, 'product_adjustment_exclude_admin_catalog', true) == 'yes' && $this->current_user_role =='administrator')) {
                   $hide = true;
            } else {
                $hide = false;
            }
        }
        
        elseif (is_user_logged_in()) {
            $remove_settings_price_roles = get_option('eh_pricing_discount_price_user_role', array());
            $remove_product_price_roles = get_post_meta($product_id, 'eh_pricing_adjustment_product_price_user_role', true);
            if (is_array($remove_settings_price_roles) && in_array($this->current_user_role, $remove_settings_price_roles)) {
                $hide = true;
            }
            if (is_array($remove_product_price_roles) && in_array($this->current_user_role, $remove_product_price_roles)) {
                $hide = true;
            }
        } else {
            $remove_product_price_roles = get_post_meta($product_id, 'product_adjustment_hide_price_unregistered', true);
            if (get_option('eh_pricing_discount_price_unregistered_user') == 'yes' || $remove_product_price_roles == 'yes') {
                $hide = true;
            }
        }
        return $hide;
    }


    public function elex_rp_is_product_purchasable($is_purchasable, $product) {
        if ($this->elex_rp_is_hide_price($product) === true ) {
            return false;
        } else {
            return true;
        }
    }

    function elex_rp_is_price_hidden_in_product_meta($product) {
        $product_id = $this->elex_rp_get_product_id($product);

        if ($this->elex_rp_get_product_type($product) == 'variation') {
            $product_id = $this->elex_rp_get_product_parent_id($product);
        }
        if (is_user_logged_in()) {
            $remove_product_price_roles = get_post_meta($product_id, 'eh_pricing_adjustment_product_price_user_role', true);
            if (is_array($remove_product_price_roles) && in_array($this->current_user_role, $remove_product_price_roles)) {
                return true;
            } else {
                return false;
            }
        } else {
            $remove_product_price_roles = get_post_meta($product_id, 'product_adjustment_hide_price_unregistered', true);
            if ($remove_product_price_roles == 'yes') {
                return true;
            } else {
                return false;
            }
        }
    }

    function elex_rp_get_placeholder_text($product, $price) {
        $placeholder = '';
        $product_id = $this->elex_rp_get_product_id($product);
        if ($this->elex_rp_is_hide_price($product) == true) {
            if (get_option('eh_pricing_discount_price_catalog_mode') == 'yes') {
                $placeholder = get_option('eh_pricing_discount_price_catalog_mode_text');
            }
            elseif('yes' == get_post_meta($product_id, 'product_adjustment_hide_price_catalog', true)){
                $placeholder = get_post_meta($product_id, 'product_adjustment_hide_price_placeholder_catalog', true);
            }
            elseif (is_user_logged_in()) {
                $placeholder = get_option('eh_pricing_discount_price_user_role_text');
            } else {
                $placeholder = get_option('eh_pricing_discount_price_unregistered_user_text');
            }
            return $placeholder;
        } else {
            return $price;
        }
    }
    function elex_rp_get_placeholder_text_product_hide_price($product) {
        $placeholder = '';
        $prod_id = $this->elex_rp_get_product_id($product);
            if (is_user_logged_in()) {
                $placeholder = get_post_meta($prod_id, 'product_adjustment_hide_price_placeholder_role',true);
            } else {
                $placeholder = get_post_meta($prod_id, 'product_adjustment_hide_price_placeholder_unregistered',true);
            }
            return $placeholder;
        
    }

    function elex_rp_get_product_type($product) {
        if (empty($product)) {
            return 'not a valid object';
        }
        if (WC()->version < '2.7.0') {
            $product_type = $product->product_type;
        } else {
            $product_type = $product->get_type();
        }
        return $product_type;
    }

    function elex_rp_get_product_id($product) {
        if (empty($product)) {
            return 'not a valid object';
        }
        if (WC()->version < '2.7.0') {
            $product_id = $product->post->id;
        } else {
            $product_id = $product->get_id();
        }
        return $product_id;
    }

    function elex_rp_get_product_parent_id($product) {
        if (empty($product)) {
            return 'not a valid object';
        }
        if (WC()->version < '2.7.0') {
            $product_parent_id = $product->parent->id;
        } else {
            $product_parent_id = $product->get_parent_id();
        }
        return $product_parent_id;
    }

    //function to add price suffix
    public function elex_rp_pricing_add_price_suffix($price, $product) {
        $price_suffix;
        if ($this->price_suffix_option == 'general') {
            $price_suffix = ' <small class="woocommerce-price-suffix">' . $this->general_price_suffix . '</small>';
        } else if ($this->price_suffix_option == 'role_specific') {
            $user_role;
            if (is_user_logged_in()) {
                $user_role = $this->price_suffix_user_role;
            } else {
                $user_role = 'unregistered_user';
            }
            if (is_array($this->role_price_suffix) && key_exists($user_role, $this->role_price_suffix) && isset($this->role_price_suffix[$user_role]['price_suffix']) && $this->role_price_suffix[$user_role]['price_suffix'] != '') {
                $price_suffix = ' <small class="woocommerce-price-suffix">' . $this->role_price_suffix[$user_role]['price_suffix'] . '</small>';
            }
        }
        if (!empty($price_suffix) && $this->elex_rp_is_hide_price($product) === false) {

            $find = array(
                '{price_including_tax}',
                '{price_excluding_tax}'
            );
            $replace = array(
                wc_price((WC()->version < '2.7.0') ? $product->get_price_including_tax() : wc_get_price_including_tax($product)),
                wc_price((WC()->version < '2.7.0') ? $product->get_price_excluding_tax() : wc_get_price_excluding_tax($product))
            );
            $price_suffix = str_replace($find, $replace, $price_suffix);
            $price .= $price_suffix;
        }
        return $price;
    }

}

new Elex_Price_Discount_Admin();
