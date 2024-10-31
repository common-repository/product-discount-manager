<?php
/*
* @Pakage product discount manager.
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check Class Existance
if( ! class_exists( 'Wcpdm_Variable_Product' ) ){

    class Wcpdm_Variable_Product {

        public function wcpd_variable_product_discount( $price, $variation) {

            global $wpdb;
            $table_name = "{$wpdb->prefix}spark_product_discount";
            // Check if the table exists
            $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

            if( ! $table_exists ){
                return $price;
            }

            $wcpd_discount_settings = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}spark_product_discount" );

            if( !empty( $wcpd_discount_settings ) && is_array( $wcpd_discount_settings ) ) :
                
                foreach ($wcpd_discount_settings as $discount_settings ) :
                    $wcpd_productGroupId = isset($discount_settings->wcpd_productGroup_id) ? sanitize_text_field($discount_settings->wcpd_productGroup_id) : '';

                    $wcpd_discount_by  = isset($discount_settings->wcpd_discount_by) ? sanitize_text_field($discount_settings->wcpd_discount_by) : ''; 
                    $wcpd_product_type = isset($discount_settings->wcpd_product_type) ? sanitize_text_field($discount_settings->wcpd_product_type) : ''; 
                    $wcpd_discount_type = isset($discount_settings->wcpd_discount_type) ? sanitize_text_field($discount_settings->wcpd_discount_type) : '';  

                    $wcpd_product_categories = isset($discount_settings->wcpd_product_category) ? 
                    (array)json_decode($discount_settings->wcpd_product_category) : [];  

                    $wcpd_discount_amount  = isset($discount_settings->wcpd_discount_amount) ? floatval($discount_settings->wcpd_discount_amount) : "";        
                    $wcpd_discount_apply_on   = isset($discount_settings->wcpd_discount_apply_on) ? sanitize_text_field($discount_settings->wcpd_discount_apply_on) : "";   
                    
                    $wcpd_schedule_date    = isset($discount_settings->wcpd_discount_schedule_date) ? json_decode($discount_settings->wcpd_discount_schedule_date) : ''; 

                    // Default Timezone
                    $defaultSettingsTimeZone = get_option('timezone_string');
                    $wcpd_timezone_discount = isset($discount_settings->wcpd_timezone_discount) && ($discount_settings->wcpd_timezone_discount !== "Select a timezone") && !empty($discount_settings->wcpd_timezone_discount) ? $discount_settings->wcpd_timezone_discount : $defaultSettingsTimeZone;

                    $wc_variable_skus = isset($discount_settings->wcpd_variable_skus ) ? (array)json_decode($discount_settings->wcpd_variable_skus) : [];

                    // Category Data from #option table
                    $category_ids = $wcpd_product_categories; // or $emptyArray = []; for PHP 5.4+

                    // Get the variation product ID from a variation obj
                    $variation_id = $variation->get_id(); 
                    $variations = wc_get_product($variation_id);
                    $product_type = $variations->get_type();

                    // Get the parent product ID from a variation
                    $product_id = $variation->get_parent_id();
                    $product = wc_get_product($product_id);
                    $productType = $product->get_type();

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

                    // Get the regular price and sale price of the variation
                    $regular_price = $variation->get_regular_price();
                    $sale_price = $variation->get_sale_price();
                    // Set the desired timezone
                    $timezone = new DateTimeZone($wcpd_timezone_discount);
                    // Create a DateTime object with the current date and time in the desired timezone
                    $currentDateTime = new DateTime('now', $timezone);
                    // Set the discount start and end dates in the desired timezone
                    $discountStart = new DateTime($dateTime_start, $timezone);
                    $discountEnd = new DateTime( $dateTime_end, $timezone);

                    // Replace with your desired discount amount
                    $discount_amount_num   = intval($wcpd_discount_amount);

                    // Category
                    if($wcpd_discount_by === 'category' && is_array($category_ids) && $wcpd_product_type  === 'variable' && $productType ==='variable' && !empty($category_ids[0]) ){

                        foreach ($category_ids as $category_id) {

                            if ( has_term( $category_id, 'product_cat', $product_id )) {

                                // Check for discount date validity
                                $validDiscountDate = ($currentDateTime >= $discountStart && $currentDateTime <= $discountEnd) || ($discountStart && empty($discountEnd));

                                if ($validDiscountDate) {
                                    // Percentage Price 
                                    if ($wcpd_discount_type === 'percentage') {
                                        $discounted_price = ($wcpd_discount_apply_on === 'regular') ? (float) $regular_price - ( (float) $regular_price * ($discount_amount_num / 100)) : (float) $sale_price - ( (float) $sale_price * ($discount_amount_num / 100));
                                        $price = $discounted_price;
        
                                    } elseif ($wcpd_discount_type === 'fixed') {
                                        $discounted_price = ($wcpd_discount_apply_on === 'regular') ? (float) $regular_price - $discount_amount_num : (float) $sale_price - $discount_amount_num;
                                        $price = $discounted_price;
                                    }
                                }
                                return $price;
                            }

                        }
                    } // End of Category

                    // Variable Product Type
                    if ($wcpd_product_type === 'variable' && ( $productType ==='variable' || $product_type === 'variation' ) && empty($category_ids[0]) && $wcpd_discount_by !== 'group') {
                        // Check for discount date validity
                        $validDiscountDate = ($currentDateTime >= $discountStart && $currentDateTime <= $discountEnd) || ($discountStart && empty($discountEnd));

                        if ($validDiscountDate) {
                            // Percentage Price 
                            if ($wcpd_discount_type === 'percentage') {
                                $discounted_price = ($wcpd_discount_apply_on === 'regular') ? (float) $regular_price - ( (float) $regular_price * ($discount_amount_num / 100)) : (float) $sale_price - ( (float) $sale_price * ($discount_amount_num / 100));
                                $price = $discounted_price;

                            } elseif ($wcpd_discount_type === 'fixed') {
                                $discounted_price = ($wcpd_discount_apply_on === 'regular') ? (float) $regular_price - $discount_amount_num : (float) $sale_price - $discount_amount_num;
                                $price = $discounted_price;
                            }
                        }
                        return $price;
                    }
                endforeach; // End of Foreach
            endif;
            return $price;

        } // End of function
    }
}
