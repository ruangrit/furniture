<?php
// to check whether accessed directly
if (!defined('ABSPATH')) {
	exit;
}

require_once( WP_PLUGIN_DIR . '/woocommerce/includes/admin/settings/class-wc-settings-page.php' );

class Elex_Pricing_Discount_Settings extends WC_Settings_Page {

    public function __construct() {
        global $user_adjustment_price;
        $this->init();
        $this->id = 'eh_pricing_discount';
    }

    public function init() {
        include( 'elex-class-admin-notice.php' );

        $this->user_adjustment_price = get_option('eh_pricing_discount_price_adjustment_options', array());
        add_filter('woocommerce_settings_tabs_array', array($this, 'elex_rp_add_settings_tab'), 50);
        add_filter('eh_pricing_discount_manage_user_role_settings', array($this, 'elex_rp_add_manage_role_settings'), 30);

        add_action('woocommerce_admin_field_priceadjustmenttable', array($this, 'elex_rp_pricing_admin_field_priceadjustmenttable')); //to add price adjustment table to settings
        add_action('woocommerce_admin_field_pricing_discount_manage_user_role', array($this, 'elex_rp_pricing_admin_field_pricing_discount_manage_user_role'));
        add_action('woocommerce_update_options_eh_pricing_discount', array($this, 'elex_rp_update_settings'));
        add_filter('woocommerce_product_data_tabs', array($this, 'elex_rp_add_product_tab'));
        add_action('woocommerce_product_data_panels', array($this, 'elex_rp_add_price_adjustment_data_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'elex_rp_add_custom_general_fields_save'));
        add_action('woocommerce_product_options_general_product_data', array($this, 'elex_rp_add_price_extra_fields'));
        add_action('event-category_add_form_fields', array($this, 'elex_rp_pricing_category_adjustment_fields'), 10);
        add_filter('woocommerce_sections_eh_pricing_discount', array($this, 'output_sections'));
        add_filter('woocommerce_settings_eh_pricing_discount', array($this, 'elex_rp_output_settings'));
        add_action('admin_init', array($this, 'elex_rp_pricing_discount_remove_notices'));
        add_action('admin_enqueue_scripts', array($this, 'elex_rp_include_js'));
    }

    function elex_rp_include_js() {
        $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
        $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : '';
        if ($page == 'wc-settings' && $tab == "eh_pricing_discount" && ($section == "" || $section == "xa-unregistered-role" )) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('eh-pricing-discount', ELEX_PRICING_DISCOUNT_MAIN_URL_PATH . 'includes/elex-html-price-adjustment.js');
        }
    }

    public function get_sections() {
        
        $sections = array(
            '' => __('Role-based Settings', 'elex-catmode-rolebased-price'),
            'xa-unregistered-role' => __('Unregistered User', 'elex-catmode-rolebased-price'),
            'manage-user-role' => __('Manage User Role', 'elex-catmode-rolebased-price'),
             'to-go-premium' => __('<li><strong><font color="red">Go Premium!</font></strong></li>', 'eh-woocommerce-pricing-discount')
        );
        return apply_filters('woocommerce_get_sections_eh_pricing_discount', $sections);
    }

    public function elex_rp_pricing_discount_remove_notices() {
        global $current_section;
        if ($current_section == 'manage-user-role') {
            remove_all_actions('admin_notices');
            Elex_admin_notice::throw_notices();
        }
    }


    public static function elex_rp_add_settings_tab($settings_tabs) {
        $settings_tabs['eh_pricing_discount'] = __('Role-based Pricing', 'elex-catmode-rolebased-price');
        return $settings_tabs;
    }

    public function elex_rp_output_settings() {
        global $current_section;
         if($current_section == 'to-go-premium'){
              wp_enqueue_style('eh-pricing-discount-bootstrap', ELEX_PRICING_DISCOUNT_MAIN_URL_PATH . 'resources/css/bootstrap.css');
            include_once("market.php");
        }
        else if ($current_section == '') {
            $settings = $this->elex_rp_get_role_settings($current_section);
            WC_Admin_Settings::output_fields($settings);
        } else if ($current_section == 'xa-unregistered-role') {
            $settings = $this->elex_rp_get_unregistered_settings($current_section);
            WC_Admin_Settings::output_fields($settings);
        } else if ($current_section == 'manage-user-role') {
            $settings = $this->elex_rp_get_user_role_settings($current_section);
            WC_Admin_Settings::output_fields($settings);
            remove_all_actions('admin_notices');
            Elex_admin_notice::throw_notices();
        }
    }

    public function elex_rp_get_user_role_settings($current_section) {
        $settings = array(
            'section_title' => array(
                'name' => __('', 'elex-catmode-rolebased-price'),
                'type' => 'title',
                'desc' => '',
                'id' => 'eh_pricing_discount_add_user_role_section_title',
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id' => 'eh_pricing_discount_add_user_role_section_end'
            ),
        );
        return apply_filters('eh_pricing_discount_manage_user_role_settings', $settings);
    }

    //function to add 
    public function elex_rp_add_manage_role_settings($settings) {
        $settings['price_adjustment_options'] = array(
            'type' => 'pricing_discount_manage_user_role',
            'id' => 'eh_pricing_discount_manage_user_role',
        );
        return $settings;
    }

    //function to generate manage user role setting page
    public function elex_rp_pricing_admin_field_pricing_discount_manage_user_role($settings) {
        include( 'elex-html-price-adjustment-manage-user-role.php' );
    }

    public function elex_rp_update_settings($current_section) {
        global $current_section;
        if ($current_section == '') {
            $options = $this->elex_rp_get_role_settings();
            woocommerce_update_options($options);
            $this->user_adjustment_price = get_option('eh_pricing_discount_price_adjustment_options', array());
        }
        if ($current_section == 'xa-unregistered-role') {
            $options = $this->elex_rp_get_unregistered_settings();
            woocommerce_update_options($options);
        }
        
        if ($current_section == 'manage-user-role') {
            $user_role_action = $_POST['pricing_discount_manage_user_roles'];
            $manage_role_status = '';
            if ($user_role_action == 'add_user_role') {
                $manage_role_status = $this->elex_rp_pricing_discount_add_user_role(sanitize_text_field($_POST['eh_woocommerce_pricing_discount_user_role_name']));
            }
            if (($user_role_action == 'remove_user_role')) {
                if (isset($_POST['pricing_discount_remove_user_role'])) {
                    $this->elex_rp_pricing_discount_remove_user_role($_POST['pricing_discount_remove_user_role']);
                } else {
                    $status = __('Please select atleast one role to delete', 'elex-catmode-rolebased-price');
                    Elex_admin_notice::add_notice($status, 'error');
                }
            }
        }
    }

    //function to create User Role
    public function elex_rp_pricing_discount_add_user_role($user_role_name) {
        global $wp_roles;
        $user_roles = $wp_roles->role_names;
        $new_user_role = str_replace(' ', '_', $user_role_name);
        try {
            if (($new_user_role != '' && $user_role_name != '' ) && !( array_key_exists($new_user_role, $user_roles) )) {
                add_role($new_user_role, $user_role_name, array('read' => true));
                $status = __('User Role created successfully', 'elex-catmode-rolebased-price');
                Elex_admin_notice::add_notice($status, 'notice');
            } else {
                $status = __('User Role creation failed', 'elex-catmode-rolebased-price');
                Elex_admin_notice::add_notice($status, 'error');
            }
        } catch (Exception $e) {
            Elex_admin_notice::add_notice($e, 'error');
        }
    }

    //function to remove User Role
    public function elex_rp_pricing_discount_remove_user_role($remove_user_role) {
        foreach ($remove_user_role as $id => $status) {
            try {
                remove_role($id);
                $status = __('User Role deleted successfully', 'elex-catmode-rolebased-price');
            } catch (Exception $e) {
                Elex_admin_notice::add_notice($e, 'error');
                return;
            }
        }
        Elex_admin_notice::add_notice($status, 'notice');
    }


    public function elex_rp_get_unregistered_settings() {
        $settings = array(
            'eh_pricing_discount_unregistered_title' => array(
                'title' => __('Unregistered User Options:', 'elex-catmode-rolebased-price'),
                'type' => 'title',
                'description' => '',
                'id' => 'eh_pricing_discount_unregistered'
            ),
            'cart_unregistered_user' => array(
                'title' => __('Remove Add to Cart', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_cart_unregistered_user',
                'desc_tip' => __('Check to remove Add to Cart option.', 'elex-catmode-rolebased-price'),
            ),
            'cart_unregistered_user_text' => array(
                'title' => __('Placeholder Text', 'elex-catmode-rolebased-price'),
                'type' => 'textarea',
                'desc' => __("Enter a text or html content to display when Add to Cart button is removed. Leave it empty if you don't want to show any content.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_cart_unregistered_user_text',
                'desc_tip' => true
            ),
            'replace_cart_unregistered_user' => array(
                'title' => __('Customize Add to Cart', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_replace_cart_unregistered_user',
                'desc_tip' => __('Check to customize Add to Cart option.', 'elex-catmode-rolebased-price'),
            ),
            'replace_cart_unregistered_user_text_product' => array(
                'title' => __('Change Button Text (Product Page)', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a text to replace the existing Add to Cart button text on the product page.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_unregistered_user_text_product',
                'desc_tip' => true
            ),
            'replace_cart_unregistered_user_text_shop' => array(
                'title' => __('Change Button Text (Shop Page)', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a text to replace the existing Add to Cart button text on the shop page.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_unregistered_user_text_shop',
                'desc_tip' => true
            ),
            'replace_cart_unregistered_user_url_shop' => array(
                'title' => __('Change Button URL', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a url to redirect customers from Add to Cart button. Leave this field empty to not change the button functionality. Make sure to enter a text in the above fields to apply these changes.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_unregistered_user_url_shop',
                'desc_tip' => true
            ),
            'hide_regular_price' => array(
                'title' => __('Hide Regular Price', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_hide_regular_price_unregistered',
                'desc_tip' => __('Check to hide regular price when sale price is provided.', 'elex-catmode-rolebased-price'),
            ),
            'price_unregistered_user' => array(
                'title' => __('Hide Price', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_price_unregistered_user',
                'desc_tip' => __('Check to hide product price. This will also remove Add to Cart option.', 'elex-catmode-rolebased-price'),
            ),
            'price_unregistered_user_text' => array(
                'title' => __('Placeholder Text', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter the text you want to display when price is removed. Leave it empty if you don't want to show any placeholder text.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_price_unregistered_user_text',
                'desc_tip' => true
            ),
            'cart_unregistered_user_remove_cart_checkout' => array(
                'title' => __('Hide Cart and Checkout Page', 'elex-catmode-rolebased-price'),
                'type' => 'checkbox',
                'desc' => __('Enable', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_cart_unregistered_user_remove_cart_checkout',
                'default' =>'no',
                'desc_tip' => __('Check to disable access to Cart and Checkout pages.', 'elex-catmode-rolebased-price'),
            ),
            'eh_pricing_discount_unregistered_title_end' => array(
                'type' => 'sectionend',
                'id' => 'eh_pricing_discount_unregistered'
            ),
        );
        return apply_filters('eh_pricing_discount_unregistered_settings', $settings);
    }

    public function elex_rp_get_role_settings() {
        global $wp_roles;

        $user_roles = $wp_roles->role_names;
        $settings = array(
            'eh_pricing_discount_user_role_title' => array(
                'title' => __('User Role Specific Options:', 'elex-catmode-rolebased-price'),
                'type' => 'title',
                'id' => 'eh_pricing_discount_user_role'
            ),
            'cart_user_role' => array(
                'title' => __('Remove Add to Cart', 'elex-catmode-rolebased-price'),
                'type' => 'multiselect',
                'desc' => __('Select the user role(s) for which you want to hide Add to Cart option.', 'elex-catmode-rolebased-price'),
                'class' => 'chosen_select',
                'id' => 'eh_pricing_discount_cart_user_role',
                'options' => $user_roles,
                'desc_tip' => true
            ),
            'cart_user_role_text' => array(
                'title' => __('Placeholder Content', 'elex-catmode-rolebased-price'),
                'type' => 'textarea',
                'desc' => __("Enter a text or html content to display when Add to Cart button is removed. Leave it empty if you don't want to show any content.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_cart_user_role_text',
                'desc_tip' => true
            ),
            'replace_cart_user_role' => array(
                'title' => __('Customize Add to Cart', 'elex-catmode-rolebased-price'),
                'type' => 'multiselect',
                'desc' => __('Select the user role(s) for which you want to customize Add to Cart option.', 'elex-catmode-rolebased-price'),
                'class' => 'chosen_select',
                'id' => 'eh_pricing_discount_replace_cart_user_role',
                'options' => $user_roles,
                'desc_tip' => true
            ),
            'replace_cart_user_role_text_product' => array(
                'title' => __('Change Button Text (Product Page)', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a text to replace the existing Add to Cart button text on the product page.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_user_role_text_product',
                'desc_tip' => true
            ),
            'replace_cart_user_role_text_shop' => array(
                'title' => __('Change Button Text (Shop Page)', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a text to replace the existing Add to Cart button text on the shop page.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_user_role_text_shop',
                'desc_tip' => true
            ),
            'replace_cart_user_role_url_shop' => array(
                'title' => __('Change Button URL', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a url to redirect customers from Add to Cart button. Leave this field empty to not change the button functionality. Make sure to enter a text in the above fields to apply these changes.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_replace_cart_user_role_url_shop',
                'desc_tip' => true
            ),
            'regular_price_user_role' => array(
                'title' => __('Hide Regular Price', 'elex-catmode-rolebased-price'),
                'type' => 'multiselect',
                'desc' => __('Select the user role(s) for which you want to hide regular price of all the simple products which have sale prices.', 'elex-catmode-rolebased-price'),
                'class' => 'chosen_select',
                'id' => 'eh_pricing_discount_regular_price_user_role',
                'options' => $user_roles,
                'desc_tip' => true
            ),
            'price_user_role' => array(
                'title' => __('Hide Price', 'elex-catmode-rolebased-price'),
                'type' => 'multiselect',
                'desc' => __('Select the user role(s) for which you want to hide product price. This will also remove Add to Cart option.', 'elex-catmode-rolebased-price'),
                'class' => 'chosen_select',
                'id' => 'eh_pricing_discount_price_user_role',
                'options' => $user_roles,
                'desc_tip' => true
            ),
            'price_user_role_text' => array(
                'title' => __('Placeholder Text', 'elex-catmode-rolebased-price'),
                'type' => 'text',
                'desc' => __("Enter a text you want to display when price is removed. Leave it empty if you don't want to show any text.", 'elex-catmode-rolebased-price'),
                'css' => 'width:350px',
                'id' => 'eh_pricing_discount_price_user_role_text',
                'desc_tip' => true
            ),
            'cart_user_role_remove_cart_checkout' => array(
                'title' => __('Hide Cart and Checkout Page ', 'elex-catmode-rolebased-price'),
                'type' => 'multiselect',
                'desc' => __('Select the user role(s) for which you do not want to provide access to Cart and Checkout page', 'elex-catmode-rolebased-price'),
                'class' => 'chosen_select',
                'id' => 'eh_pricing_discount_cart_user_role_remove_cart_checkout',
                'options' => $user_roles,
                'desc_tip' => true,
                
            ),
            'product_price_user_role' => array(
                'title' => __('Individual Product Adjustment', 'elex-catmode-rolebased-price'),
                'type' => 'multiselect',
                'desc' => __('Select the user role(s) for product level price adjustments. The price adjustments can be made in the individual product edit page.', 'elex-catmode-rolebased-price'),
                'class' => 'chosen_select',
                'id' => 'eh_pricing_discount_product_price_user_role',
                'options' => $user_roles,
                'desc_tip' => true
            ),
            'eh_pricing_discount_user_role_title_end' => array(
                'type' => 'sectionend',
                'id' => 'eh_pricing_discount_user_role'
            ),
            'eh_pricing_discount_adjustment_title' => array(
                'title' => __('Price Adjustment: (Discount/Markup)', 'elex-catmode-rolebased-price'),
                'type' => 'title',
                'desc' => __("Drag and drop User Roles to set priority. If a single User has multiple User Roles assigned, the User Role with the highest priority will be chosen. Select a category to apply price adjustment to the products which belong to that category. If no particular category is selected, the price adjustment will be applied to all the products.<br><p><strong>Price Adjustment - Choose 'D' for DISCOUNT and 'M' for MARKUP.</strong></p>","elex-catmode-rolebased-price"),
                'id' => 'eh_pricing_discount_adjustment'
            ),
            'price_adjustment_options' => array(
                'type' => 'priceadjustmenttable',
                'id' => 'eh_pricing_discount_price_adjustment_options',
            ),
            'eh_pricing_discount_adjustment_title_end' => array(
                'type' => 'sectionend',
                'id' => 'eh_pricing_discount_adjustment'
            ),
        );
        return apply_filters('eh_pricing_discount_general_settings', $settings);
    }

    //function to generate price adjustment table
    public function elex_rp_pricing_admin_field_priceadjustmenttable($settings) {
        include( 'elex-html-price-adjustment-table.php' );
    }

    //function to add a prodcut tab in product page
    public function elex_rp_add_product_tab($product_data_tabs) {
        $product_data_tabs['product_price_adjustment'] = array(
            'label' => __('Role-based Settings', 'elex-catmode-rolebased-price'),
            'target' => 'product_price_adjustment_data',
            'class' => Array('show_if_simple'),
        );
        return $product_data_tabs;
    }

    public function elex_rp_add_price_adjustment_data_fields() {
        global $woocommerce, $post;
        $settings = array('hide_regular_price' => array(
                'title' => __('Hide Regular Price', 'elex-catmode-rolebased-price'),
                'type' => 'check',
                'desc' => __('Check to hide product regular price', 'elex-catmode-rolebased-price'),
                'css' => 'width:100%',
                'id' => 'eh_pricing_discount_hide_regular_price',
            )
        );
        ?>
        <!-- id below must match target registered in above add_my_custom_product_data_tab function -->
        <div id="product_price_adjustment_data" class="panel woocommerce_options_panel hidden">
            <?php include( 'elex-html-product-price-adjustment.php' ); ?>
        </div>
        <?php
    }

    function elex_rp_add_price_extra_fields() {
            echo '<div id="general_role_based_price" style="padding: 3%; >';
            include( 'elex-html-product-role-based-price.php' );
            echo '</div>';
    }

    public function elex_rp_add_custom_general_fields_save($post_id) {
        //to update product hide Add to Cart for unregistered users
        $woocommerce_adjustment_field = (isset($_POST['product_adjustment_hide_addtocart_unregistered']) && ($_POST['product_adjustment_hide_addtocart_unregistered'] == 'on')) ? 'yes' : 'no';
        if (!empty($woocommerce_adjustment_field)) {
            update_post_meta($post_id, 'product_adjustment_hide_addtocart_unregistered', $woocommerce_adjustment_field);
        }
        //to update add to cart placeholder for unregistered users
        if (isset($_POST['product_adjustment_hide_addtocart_placeholder_unregistered'])) {
            update_post_meta($post_id, 'product_adjustment_hide_addtocart_placeholder_unregistered', sanitize_text_field($_POST['product_adjustment_hide_addtocart_placeholder_unregistered']));
        }
        
        
        //to update product role based hide Add to Cart for user role
          $woocommerce_product_price_hide_field =  '';
      if(isset($_POST['eh_pricing_adjustment_product_addtocart_user_role'])) {
              $woocommerce_product_price_hide_field = array_map( 'sanitize_text_field', wp_unslash( $_POST['eh_pricing_adjustment_product_addtocart_user_role'] ));
        }
        update_post_meta($post_id, 'eh_pricing_adjustment_product_addtocart_user_role', $woocommerce_product_price_hide_field);
        
        //to update hide add  to cart placeholder for user role
        if (isset($_POST['product_adjustment_hide_addtocart_placeholder_role'])) {
            update_post_meta($post_id, 'product_adjustment_hide_addtocart_placeholder_role', sanitize_text_field($_POST['product_adjustment_hide_addtocart_placeholder_role']));
        }
        
        //to update hide price placeholder for user role
        if (isset($_POST['product_adjustment_hide_price_placeholder_role'])) {
            update_post_meta($post_id, 'product_adjustment_hide_price_placeholder_role', sanitize_text_field($_POST['product_adjustment_hide_price_placeholder_role']));
        }
        //to update product hide price for unregistered users
        $woocommerce_adjustment_field = (isset($_POST['product_adjustment_hide_price_unregistered']) && ($_POST['product_adjustment_hide_price_unregistered'] == 'on')) ? 'yes' : 'no';
        if (!empty($woocommerce_adjustment_field)) {
            update_post_meta($post_id, 'product_adjustment_hide_price_unregistered', $woocommerce_adjustment_field);
        }
        //to update hide price placeholder for unregistered users
        if (isset($_POST['product_adjustment_hide_price_placeholder_unregistered'])) {
            update_post_meta($post_id, 'product_adjustment_hide_price_placeholder_unregistered', sanitize_text_field($_POST['product_adjustment_hide_price_placeholder_unregistered']));
        }

        //to update product hide price for user role
          $woocommerce_product_price_field = '';
      if(isset($_POST['eh_pricing_adjustment_product_price_user_role'])){
            $woocommerce_product_price_field = array_map( 'sanitize_text_field', wp_unslash( $_POST['eh_pricing_adjustment_product_price_user_role'] ));
      }
          
        update_post_meta($post_id, 'eh_pricing_adjustment_product_price_user_role', $woocommerce_product_price_field);

        //to update product based price adjustment
        $woocommerce_adjustment_field = (isset($_POST['product_based_price_adjustment']) && ($_POST['product_based_price_adjustment'] == 'on')) ? 'yes' : 'no';
        if (!empty($woocommerce_adjustment_field)) {
            update_post_meta($post_id, 'product_based_price_adjustment', $woocommerce_adjustment_field);
        }
        

        //to update the product role based adjustment
          $woocommerce_adjustment_field = '';
      if((isset($_POST['product_price_adjustment']))) {
          $woocommerce_adjustment_field = array();
          foreach ($_POST['product_price_adjustment'] as $key=>$val){
              $woocommerce_adjustment_field [sanitize_text_field($key)] = array_map( 'sanitize_text_field', wp_unslash( $val )); 
          }
      }
        update_post_meta($post_id, 'product_price_adjustment', $woocommerce_adjustment_field);
        
        //to update the product role based price
        $woocommerce_price_field = '';
        if(isset($_POST['product_role_based_price'])) {
            $woocommerce_price_field = array();
            foreach ($_POST['product_role_based_price'] as $key=>$val){
              $woocommerce_price_field [sanitize_text_field($key)] = array_map( 'sanitize_text_field', wp_unslash( $val )); 
          }
        }
        update_post_meta($post_id, 'product_role_based_price', $woocommerce_price_field);
    }

    //function to generate price adjustment table
    public function elex_rp_pricing_category_adjustment_fields($tag) {
        $t_id = $tag->term_id;
        $cat_meta = get_option("category_$t_id");
        print_r($cat_meta);
        print_r($t_id);
        print_r($tag);
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="meta-color"><?php _e('Category Name Background Color'); ?></label></th>
            <td>
                <div id="colorpicker">
                    <input type="text" name="cat_meta[catBG]" class="colorpicker" size="3" style="width:20%;" value="<?php echo (isset($cat_meta['catBG'])) ? $cat_meta['catBG'] : '#fff'; ?>" />
                </div>
                <br />
                <span class="description"><?php _e(''); ?></span>
                <br />
            </td>
        </tr>
        <?php
    }
}

new Elex_Pricing_Discount_Settings();
