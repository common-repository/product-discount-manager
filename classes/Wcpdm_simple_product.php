<?php
/*
* @Pakage product discount manager.
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check class already exist or not
if( !class_exists('Wcpdm_Simple_Product')){

    class Wcpdm_Simple_Product{

        public function wcpd_simple_product_discount($price, $product) {

            global $wpdb;
            $table_name = "{$wpdb->prefix}spark_product_discount";
            // Check if the table exists
            $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

            if( ! $table_exists ){
                return $price;
            }

            $wcpd_discount_settings = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}spark_product_discount");

            if(!empty($wcpd_discount_settings) && is_array($wcpd_discount_settings )):

                foreach ($wcpd_discount_settings as $discount_settings ) :

                    $wcpd_productGroupId = isset($discount_settings->wcpd_productGroup_id) ? sanitize_text_field($discount_settings->wcpd_productGroup_id) : '';
                    $wcpd_discount_by   = isset($discount_settings->wcpd_discount_by) ? sanitize_text_field($discount_settings->wcpd_discount_by) : ''; 
                    $wcpd_product_type  = isset($discount_settings->wcpd_product_type) ? sanitize_text_field($discount_settings->wcpd_product_type) : ''; 
                    $wcpd_discount_type = isset($discount_settings->wcpd_discount_type) ? sanitize_text_field($discount_settings->wcpd_discount_type) : '';  
                    $wcpd_product_categories = isset($discount_settings->wcpd_product_category) ? (array)json_decode($discount_settings->wcpd_product_category) : [];
                    $wcpd_discount_amount = isset($discount_settings->wcpd_discount_amount) ? floatval($discount_settings->wcpd_discount_amount) : 0;       
                    $wcpd_discount_apply_on = isset($discount_settings->wcpd_discount_apply_on) ? sanitize_text_field($discount_settings->wcpd_discount_apply_on) : "";  

                    $wcpd_schedule_date    = isset($discount_settings->wcpd_discount_schedule_date) ? json_decode($discount_settings->wcpd_discount_schedule_date) : ''; 

                    // date_default_timezone_set('UTC');
                    $defaultSettingsTimeZone = get_option('timezone_string');
                    
                    $wcpd_timezone_discount = isset($discount_settings->wcpd_timezone_discount) && ($discount_settings->wcpd_timezone_discount !== "Select a timezone") && !empty($discount_settings->wcpd_timezone_discount) ? $discount_settings->wcpd_timezone_discount : $defaultSettingsTimeZone;

                    $wc_simple_skus = isset($discount_settings->wcpd_simple_skus) ? (array)json_decode($discount_settings->wcpd_simple_skus) : [];

                    // Category Data from #option table
                    $category_ids = $wcpd_product_categories; 

                    $product_type = $product->get_type();
                    $productId = $product->get_id();

                    // Starting Date & Time
                    $wcpd_DateStart = isset( $wcpd_schedule_date->dateStart ) ? $wcpd_schedule_date->dateStart : '';
                    $wcpd_TimeStart = isset( $wcpd_schedule_date->timeStart ) ? $wcpd_schedule_date->timeStart : '';

                    $dateStringStart = $wcpd_DateStart;
                    $timeStringStart = $wcpd_TimeStart;
                    $dateTime_start = $dateStringStart. ' ' .$timeStringStart;

                    if( !empty($wcpd_schedule_date->dateStart) ){
                        $dateTime_start = gmdate("Y-m-d H:i:s", strtotime($dateTime_start));
                    }
                    // Ending Date & Time
                    $wcpd_DateEnd = isset( $wcpd_schedule_date->dateEnd ) ? $wcpd_schedule_date->dateEnd : '';
                    $wcpd_TimeEnd = isset( $wcpd_schedule_date->timeEnd ) ? $wcpd_schedule_date->timeEnd : '';

                    $dateStringEnd = $wcpd_DateEnd;
                    $timeStringEnd = $wcpd_TimeEnd;

                    $dateTime_end = $dateStringEnd. ' ' .$timeStringEnd;
                    if( !empty($wcpd_schedule_date->dateEnd) ){
                        $dateTime_end = gmdate("Y-m-d H:i:s", strtotime($dateTime_end));
                    }
                    // Get Requler Price
                    $regular_price = $product->get_regular_price();
                    // Get Sell Price
                    $sale_price   = $product->get_sale_price();
                    // Set the desired timezone
                    $timezone = new DateTimeZone($wcpd_timezone_discount);

                    // Create a DateTime object with the current date and time in the desired timezone
                    $currentDateTime = new DateTime('now', $timezone);
                    // Set the discount start and end dates in the desired timezone
                    $discountStart = new DateTime($dateTime_start, $timezone);
                    $discountEnd = new DateTime( $dateTime_end, $timezone);

                    // Replace with your desired discount amount
                    $discount_amount_num   = intval($wcpd_discount_amount);
                    // Initial $discount_amount to store values
                    $discount_amount = 0;

                    // Category
                    if ($wcpd_discount_by === 'category' && is_array($category_ids) && !empty($category_ids) && empty($wc_simple_skus[0])) {

                        foreach ($category_ids as $category_id) {
                            if ( !empty( $wcpd_discount_amount ) && has_term( $category_id, 'product_cat', $product->get_id() ) && $product_type ==='simple' &&  $wcpd_product_type === 'simple') {
    
                                if ( ($currentDateTime >= $discountStart && $currentDateTime <= $discountEnd) || ($discountStart && empty($discountEnd)) ) {
    
                                    if ($wcpd_discount_type === 'percentage') {
                                        $discount_amount = ($wcpd_discount_apply_on === 'regular') ? (float) $regular_price - ( (float) $regular_price * ($discount_amount_num / 100)) : (float) $sale_price - ( (float) $sale_price * ($discount_amount_num / 100));
                                    } elseif ($wcpd_discount_type === 'fixed') {
                                        $discount_amount = ($wcpd_discount_apply_on === 'regular') ? (float) $regular_price - $discount_amount_num : (float) $sale_price - $discount_amount_num;
                                    }
                                    return $discount_amount;
                                }
                            }
                        }
                    }
                    // End of Category

                    // Simple Product Type
                    if ( $wcpd_product_type === 'simple' && $product_type ==='simple' && empty($category_ids) && $wcpd_discount_by !== 'group' ) {

                        if ( ($currentDateTime >= $discountStart && $currentDateTime <= $discountEnd) || ($discountStart && empty($discountEnd)) ) {
             
                            if ($wcpd_discount_type === 'percentage') {
                                $discount_amount = ($wcpd_discount_apply_on === 'regular') ? (float) $regular_price - ( (float) $regular_price * ($discount_amount_num / 100)) : (float) $sale_price - ( (float) $sale_price * ($discount_amount_num / 100));
                            } elseif ($wcpd_discount_type === 'fixed') {
                                $discount_amount = ($wcpd_discount_apply_on === 'regular') ? (float) $regular_price - $discount_amount_num : (float) $sale_price - $discount_amount_num;
                            }
                            return $discount_amount;
                        }
                    }

                endforeach; // End of Foreach
            endif;
            return $price;
        } // End of Function
    }

}

