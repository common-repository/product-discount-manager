<?php
/*
* @Pakage product discount manager.
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check Class Existance
if( ! class_exists( 'Wcpdm_DuplicateData_Check' ) ){

    class Wcpdm_DuplicateData_Check {

        // Data Validation 
        public function wcpd_duplicateData_check() {

            global $wpdb;
            // Verify the nonce
            if( !check_ajax_referer( 'wcpd_woo_nonce', 'security', false )){
                wp_send_json_error( 'Nonce verification failed.' );
                die();
            } 

            // 1st Release version
            $table_name = "{$wpdb->prefix}spark_product_discount";
            // Check if the table exists
            $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

            // Get the data type and data ID from the AJAX request
            $data_type = isset($_POST['data_type']) ? sanitize_text_field($_POST['data_type']) : '';
            $data_id = isset($_POST['data_id']) ? intval($_POST['data_id']) : 0;
            $timezone_val = isset($_POST['data_id']) ? sanitize_text_field($_POST['data_id']) : '';


            // Check for duplicate data based on the data type
            $is_duplicate = false;

            switch ( $data_type ) {
                case 'category':
                    $is_duplicate = wcpdm_check_if_category_exists($data_id);
                    break;
                case 'group':
                    $is_duplicate = wcpdm_check_if_group_exists($data_id);
                    break;
                case 'timezone':
                    $is_duplicate = wcpdm_check_if_timeZone_exists($timezone_val);
                    break;
                case 'product':
                    $is_duplicate = wcpdm_check_if_product_exists($data_id);
                    break;
                default:
                    wp_send_json_error('Invalid data type.');
                    break;
            }
            if ($is_duplicate) {
                wp_send_json_error(array(
                    'data_id' => $data_id,
                    'timezone' => $timezone_val,
                ));
            } else {
                wp_send_json_success('No duplicate data.');
            }
            wp_die();
        } 

        // SKU Validation
        public function wcpd_duplicateSku_check() {
            global $wpdb;
            // Verify the nonce
            if( !check_ajax_referer( 'wcpd_woo_nonce', 'security', false )){
                wp_send_json_error( 'Nonce verification failed.' );
                die();
            }
            // Get the data type and data ID from the AJAX request
            $data_type = isset($_POST['data_type']) ? sanitize_text_field($_POST['data_type']) : '';
            $sku_val = isset($_POST['data_id']) ? sanitize_text_field($_POST['data_id']) : '';

            $table_name = "{$wpdb->prefix}spark_product_discount";
            // Check if the table exists
            $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

            $skuQry = '';
            if ($table_exists) {
                $simpleSku = '';
                $variableSku = '';  
                $skuQry = $wpdb->get_results($wpdb->prepare(
                    "SELECT wcpd_simple_skus, wcpd_variable_skus FROM %s WHERE JSON_CONTAINS(wcpd_simple_skus, %s) || JSON_CONTAINS(wcpd_variable_skus, %s)",
                    $table_name,
                    wp_json_encode($sku_val),
                    wp_json_encode($sku_val)
                ));
                $simpleSku = wp_json_encode($skuQry[0]->wcpd_simple_skus, true);
                $variableSku = wp_json_encode($skuQry[0]->wcpd_variable_skus, true);
            }
            // Send Data
            wp_send_json_success(array(
                'simpleSku'    => $simpleSku,
                'variableSku'  => $variableSku
            ));
            wp_die();
        }  

        // Pricing Validation
        public function wcpd_pricingSettings_check() {
            global $wpdb;
            // Verify the nonce
            if( !check_ajax_referer( 'wcpd_woo_nonce', 'security', false )){
                wp_send_json_error( 'Nonce verification failed.' );
                die();
            } 
   
            $table_name = "{$wpdb->prefix}spark_product_price_change";
            // Check if the table exists
            $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

            $pricingData = array();

            if( $table_exists ){
                
                $pricingQry = $wpdb->get_results( "SELECT changePriceBy, changePriceCats, productType FROM {$wpdb->prefix}spark_product_price_change" );

                foreach ($pricingQry as $key => $qryData) {
                    $pricingData[$key] = array(
                        'changePriceBy' => $qryData->changePriceBy,
                        'changesCategory' => json_decode($qryData->changePriceCats),
                        'changesProductType' => $qryData->productType,
                    );
                }
            }
            // Send Data
            wp_send_json_success(array(
                'pricingData'    => $pricingData,
            ));
            wp_die();
        } 
    }
}

// Callback Functions
if( !function_exists('wcpdm_check_if_category_exists')){

    function wcpdm_check_if_category_exists($category_id) {

        global $wpdb;
        $table_name = "{$wpdb->prefix}spark_product_discount";
        // Check if the table exists
        $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

        if ($table_exists) {
            // Use $wpdb->prepare to safely handle user input
            $categoryQry = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}spark_product_discount WHERE wcpd_product_category LIKE %s",
                    '%' . $wpdb->esc_like($category_id) . '%'
                )
            );    
            if (!empty($categoryQry)) {
                return true;
            }
        }
        return false;
    }
}

if( !function_exists('wcpdm_check_if_group_exists')){

    function wcpdm_check_if_group_exists($group_id) {
        global $wpdb;
        $table_name = "{$wpdb->prefix}spark_product_discount";
        // Check if the table exists
        $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
    
        if ($table_exists) {
            // Use $wpdb->prepare to safely handle user input
            $grpQry = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT wcpd_productGroup_id FROM %s WHERE wcpd_productGroup_id = %d",
                    $table_name,
                    $group_id
                )
            );
            if (!empty($grpQry) && count($grpQry) > 0) {
                return true;
            }
        }
        return false;
    }
}

if( !function_exists('wcpdm_check_if_timeZone_exists')){
    function wcpdm_check_if_timeZone_exists($timezone_id) {

        global $wpdb;
        $table_name = "{$wpdb->prefix}spark_product_discount";
        // Check if the table exists
        $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
        
        if ($table_exists) {
            // Use $wpdb->prepare to safely handle user input
            $timeZoneQry = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT wcpd_timezone_discount FROM %s WHERE wcpd_timezone_discount = %s",
                    $table_name,
                    $timezone_id
                )
            );
            if (!empty($timeZoneQry)) {
                return true;
            }
        }
        return false;
    }
}

if( !function_exists('wcpdm_check_if_product_exists')){

    function wcpdm_check_if_product_exists($product_id) {

        global $wpdb;
        $table_name = "{$wpdb->prefix}spark_group_product_table";
        // Check if the table exists
        $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

        $productId = '%"id";s:' . strlen($product_id) . ':"' . $product_id . '"%';

        if ( $table_exists ) {

            $productQry = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT products FROM %s WHERE products LIKE %s",
                    $table_name,
                    $wpdb->esc_like($productId)
                )
            );
            if (!empty($productQry)) {
                return true;
            }
        }
        return false;
    }
}

