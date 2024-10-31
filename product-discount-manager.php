<?php
/**
 * Plugin Name: Product Discount for Woocommerce
 * Requires Plugins: woocommerce
 * Description: Product Discount Manager Plugin is a Simple, Easy and Smart solution for WooCommerce Product Discount & Price Changing.
 * Version: 1.0.3
 * Author: Spark Coder
 * Author URI: https://sparkcoder.com
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Slug: product-discount-manager
 * Text Domain: product-discount-manager
 * Domain Path: /languages
 */

if( ! defined( 'ABSPATH' ) ){
    exit; // Exit if directly access.
}
/*=======================
Check WordPress Version
=========================*/
if(version_compare(get_bloginfo( 'version' ), '5.0', '<')){
    $msg = "Need WordPress Version 5.0 or higher";
    die($msg);
}
/*=======================
CONSTANT
=========================*/
define('WCPDM_DIR_PATH', plugin_dir_path( __FILE__ ));
define('WCPDM_DIR_URI', plugin_dir_url( __FILE__ ));

// Load Text-domain
function wcpdm_plugin_textdomain() {
    load_plugin_textdomain('product-discount-manager', false, WCPDM_DIR_URI . '/languages');
}
// Hook into the init action and load the text domain
add_action('init', 'wcpdm_plugin_textdomain');


/*==================================
Check if Woocommerce activate or not
====================================*/
add_action('admin_notices', 'wcpdm_check_woocommerce');
function wcpdm_check_woocommerce() {
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        ?>
        <div class="notice notice-error is-dismissible wcpdm_dashboard_notice">
            <h3><?php esc_html_e('WooCommerce is not installed or activated. Please install and activate WooCommerce to use "Product Discount Manager".', 'product-discount-manager'); ?></h3>
        </div>
        <?php
    }
}


if( in_array('woocommerce/woocommerce.php', get_option( 'active_plugins' ))){
    // Check class exist or not
    if( !class_exists('WCPDM_CORE')){
        class WCPDM_CORE{

            public function __construct(){
                /******* Includes Files *******/
                require_once( WCPDM_DIR_PATH.'includes/activation.php' );
                require_once ( WCPDM_DIR_PATH.'includes/deactivation.php' );
                require_once ( WCPDM_DIR_PATH.'includes/assets.php' );
                // Load Assets
                add_action( 'admin_enqueue_scripts', 'wcpdm_css_js_assets');

                // Dashboard Menu
                require_once ( WCPDM_DIR_PATH.'views/admin/wcpdm-admin-menu.php');
                require_once ( WCPDM_DIR_PATH.'views/admin/wcpdm-discount-update.php');

                add_action( 'admin_menu', 'wcpdm_product_discount_page' );
                add_action( 'admin_menu', 'wcpdm_discount_update_page' );
                
                /******* Includes Classes *******/
                require_once ( WCPDM_DIR_PATH.'/classes/Wcpdm_simple_product.php' );
                require_once ( WCPDM_DIR_PATH.'/classes/Wcpdm_variable_product.php' );     
                require_once ( WCPDM_DIR_PATH.'/classes/Wcpdm_ajax_handling.php' );       
                require_once ( WCPDM_DIR_PATH.'/classes/Wcpdm_dataValidation.php' );
                require_once ( WCPDM_DIR_PATH.'/classes/Wcpdm_woocommerce.php' );       

                /******* Hooks *******/
                $activation_hooks = [
                    'wcpdm_create_group_name',
                    'wcpdm_create_group_product',
                    'wcpdm_spark_product_discount',
                    'wcpdm_spark_changing_price',
                ];
                
                foreach ($activation_hooks as $hook) {
                    register_activation_hook(__FILE__, $hook);
                }
                register_uninstall_hook(__FILE__, 'wcpdm_product_discount_manager');
                
               /*=======  WooCommerce =====*/
               add_filter( 'woocommerce_product_get_price',  array( new Wcpdm_Simple_Product(), 'wcpd_simple_product_discount'), 10, 2);
               add_filter('woocommerce_product_variation_get_price',  array( new Wcpdm_Variable_Product(), 'wcpd_variable_product_discount'), 10, 2);
               add_filter('woocommerce_product_get_price',  array( new Wcpdm_Woocommerce(), 'wcpd_product_price_filter'), 10, 2);

                /*======= Ajax Request =====*/
                // Create Group
                add_action( 'wp_ajax_group_name_create', array( new Wcpdm_Ajax_Handle(), 'process_wcpd_group_form' ) );
                add_action( 'wp_ajax_del_groupName_action', array( new Wcpdm_Ajax_Handle(), 'delete_created_groupName' ) );
                // Create Selected Product Group
                add_action( 'wp_ajax_group_product_action', array( new Wcpdm_Ajax_Handle(), 'selected_product_group' ) );
                // Remove Item From Created ProductGroup
                add_action( 'wp_ajax_remove_groupItem_action', array( new Wcpdm_Ajax_Handle(), 'remove_createdGroupItem' ) );
                add_action( 'wp_ajax_delete_group_action', array( new Wcpdm_Ajax_Handle(), 'remove_createdGroup' ) );
                // Send Category IDs Data by ajax request
                add_action( 'wp_ajax_check_sku_byCategoryIds_action', array( new Wcpdm_Ajax_Handle(), 'check_sku_byCategoryIds' ) );
                // Send Discount Settings Data by ajax request
                add_action( 'wp_ajax_discount_settings_action', array( new Wcpdm_Ajax_Handle(), 'discount_settings_data' ) );
                add_action( 'wp_ajax_update_discount_settings_action', array( new Wcpdm_Ajax_Handle(), 'update_discountSettings_data') );
                add_action( 'wp_ajax_delete_discountSettings_action', array( new Wcpdm_Ajax_Handle(), 'delete_created_discountSettings') );
                add_action( 'wp_ajax_changePrice_settings_action', array( new Wcpdm_Ajax_Handle(), 'changeProduct_price') );
                // Check  Duplication Data 
                add_action( 'wp_ajax_check_duplicate_data', array( new Wcpdm_DuplicateData_Check(), 'wcpd_duplicateData_check' ) );
                add_action( 'wp_ajax_check_skuData_action', array( new Wcpdm_DuplicateData_Check(), 'wcpd_duplicateSku_check' ) );
                add_action( 'wp_ajax_changePrice_check_action', array( new Wcpdm_DuplicateData_Check(), 'wcpd_pricingSettings_check') );
                add_action( 'wp_ajax_delete_changePrice_action', array( new Wcpdm_Ajax_Handle(), 'delete_changeProduct_price_list') );

            }

            // Sanitize Function
            public static function wcpdmSanitize($data) {
                if (is_array($data)) {
                    // Sanitize each element of the array
                    return array_map([self::class, 'wcpdmSanitize'], $data);
                } elseif (is_object($data)) {
                    // Sanitize each property of the object
                    foreach ($data as $key => $value) {
                        $data->$key = self::wcpdmSanitize($value);
                    }
                    return $data;
                } elseif (is_string($data)) {
                    // Use appropriate string sanitization functions
                    return sanitize_text_field($data);
                } elseif (is_numeric($data)) {
                    // Use appropriate numeric sanitization functions
                    return intval($data);
                } else {
                    // For other types,
                    return $data;
                }
            }

        }

        $WCPDM_CORE = new WCPDM_CORE();
    }

}

// It's Release Version

