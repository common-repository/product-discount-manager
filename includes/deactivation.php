<?php
/*
* @Pakage product discount manager.
*/
if( ! defined( 'ABSPATH' ) ){
    exit; // Exit if directly access.
}
// Callback function to be executed on plugin uninstallation
if (!function_exists('wcpdm_product_discount_manager')) {
    
    function wcpdm_product_discount_manager() {

        global $wpdb;
        // Check if the current user has the necessary permissions
        if (!current_user_can('activate_plugins')) {
            return;
        }
        // Define an array of table names
        $table_names = array(
            'spark_product_discount',
            'spark_group_name_table',
            'spark_group_product_table',
            'spark_product_license_status',
            'spark_product_price_change',
            // Add more table names as needed
        );

        // Delete plugin's database tables
        foreach ($table_names as $table) {
            $table_name = $wpdb->prefix . $table;
            $query = $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %s", $table_name));
        }
    }
}

