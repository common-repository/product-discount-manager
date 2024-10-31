<?php
/*
* @Pakage product discount manager.
*/

if( !defined( 'ABSPATH' ) ){
    exit; // Exit if directly access.
}
// Check if function already exist or Not. // Group Name
if( !function_exists('wcpdm_create_group_name')){

    function wcpdm_create_group_name() {
        global $wpdb;
        $table_name = "{$wpdb->prefix}spark_group_name_table";
        // Check if the table exists
        $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

        // Create the table if it does not exist
        if (!$table_exists) {
            $sql = "CREATE TABLE $table_name (
                `id` int NOT NULL AUTO_INCREMENT,
                group_name VARCHAR(150) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}
// Check if function already exist or Not. // Group Products
if( !function_exists('wcpdm_create_group_product')){

    function wcpdm_create_group_product() {

        global $wpdb;
        $table_name = "{$wpdb->prefix}spark_group_product_table";
        // Check if the table exists
        $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

        // Create the table if it does not exist
        if ( ! $table_exists ) {
            $sql = "CREATE TABLE $table_name (
                `id` int NOT NULL AUTO_INCREMENT,
                `group_id` int(150) NOT NULL,
                `products` TEXT(400) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}
// Check if function already exist or Not. // product_discount
if( !function_exists('wcpdm_spark_product_discount')){

    function wcpdm_spark_product_discount() {
        global $wpdb;
        $table_name = "{$wpdb->prefix}spark_product_discount";
        // Check if the table exists
        $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
        
        // Create the table if it does not exist
        if ( ! $table_exists ) {
            $sql = "CREATE TABLE $table_name (
                `id` int NOT NULL AUTO_INCREMENT,
                `wcpd_discount_title` varchar(150) NOT NULL,
                `wcpd_discount_by` varchar(150) NOT NULL,
                `wcpd_product_type` varchar(150) NOT NULL,
                `wcpd_product_category` varchar(250) NULL,
                `wcpd_variable_skus` varchar(250)  NULL,
                `wcpd_simple_skus` varchar(250) NULL,
                `wcpd_productGroup_id` varchar(150) NULL,
                `wcpd_discount_type` varchar(150) NOT NULL,
                `wcpd_discount_amount` int NOT NULL,
                `wcpd_discount_schedule_date` varchar(150) NOT NULL,
                `wcpd_discount_apply_on` varchar(100) NOT NULL,
                `wcpd_timezone_discount` varchar(150) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}
// Check if function already exist or Not. // product_discount
if( !function_exists('wcpdm_spark_changing_price')){

    function wcpdm_spark_changing_price() {

        global $wpdb;
        $table_name = "{$wpdb->prefix}spark_product_price_change";
        // Check if the table exists
        $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

        // Create the table if it does not exist
        if ( !$table_exists ) {
            $sql = "CREATE TABLE $table_name (
                `id` int NOT NULL AUTO_INCREMENT,
                `changePriceBy` varchar(100) NOT NULL,
                `changePriceCats` varchar(100) NULL,
                `productType` varchar(100) NULL,
                `changeType` varchar(50) NOT NULL,
                `changeMethod` varchar(50) NOT NULL,
                `changeAmount` int(50)  NOT NULL,
                `changeOn` varchar(50) NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}
// Check if function already exist or Not. // product_discount
if( !function_exists('wcpdm_spark_license_status')){

    function wcpdm_spark_license_status() {
        global $wpdb;
        $table_name = "{$wpdb->prefix}spark_product_license_status";
        // Check if the table exists
        $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

        // Create the table if it does not exist
        if ( !$table_exists ) {
            $sql = "CREATE TABLE $table_name (
                `id` INT NOT NULL AUTO_INCREMENT,
                `license_key` VARCHAR(255) NOT NULL,
                `website_url` varchar(255),
                `activation_date` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                `valid_until` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                `is_active` varchar(50) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE(`license_key`)
            ) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
}