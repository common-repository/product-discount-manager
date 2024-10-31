<?php
/*
* @Pakage product discount manager.
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check Class existence
if( ! class_exists( 'Wcpdm_Woocommerce' ) ){
    class Wcpdm_Woocommerce {

        function wcpd_product_price_filter($price, $product) {

            global $wpdb;
            $table_name = "{$wpdb->prefix}spark_product_price_change";
            // Check if the table exists
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
            if( ! $table_exists ){
                return $price;
            }

            $qryResults = $wpdb->get_results( "SELECT * FROM  {$wpdb->prefix}spark_product_price_change" );
            if (!$qryResults) {
                return $price; // No results, return the original price
            }
            // Get the product type outside of the loop
            $product__Type = $product->get_type();

            foreach ($qryResults as $qryResult) {

                $changePriceBy = isset($qryResult->changePriceBy) ? sanitize_text_field($qryResult->changePriceBy) : '';
                $product_type = isset($qryResult->productType) ? sanitize_text_field($qryResult->productType) : '';
                $changePriceCategories = isset($qryResult->changePriceCats) ? (array)json_decode($qryResult->changePriceCats) : [];
                $changeType = isset($qryResult->changeType) ? sanitize_text_field($qryResult->changeType) : '';
                $changeMethod = isset($qryResult->changeMethod) ? sanitize_text_field($qryResult->changeMethod) : '';
                $change_Amount = isset($qryResult->changeMethod) ? intval($qryResult->changeAmount) : 0;

                $checkAmount = isset($qryResult->changeAmount) ? $qryResult->changeAmount : '';
            
                $product_id = $product->get_id();
                $product_categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));

                if ($changePriceBy === 'productType') {
                                                
                    if ( $product_type === "simple" && $product__Type === "simple") {
                        $new_price = wcpdm_calculate_new_price($changeMethod, $changeType, $price, $change_Amount);
                        // Update the new price and clear transients
                        $adjusted_data = get_post_meta($product_id, '_adjusted', true );

                        if( !$adjusted_data ){
                            update_post_meta($product_id, '_sale_price', $new_price);
                            update_post_meta($product_id, '_adjusted', 'yes');
                        }
                        wc_delete_product_transients($product_id); // Clear transients
                        return $new_price; // Return the new price for this filter
                    } 
                    if ( $product_type === "variable" && $product__Type === 'variable' ) {

                        // Initialize $new_price to null to handle the case where there are no children
                        $new_price = null;
                        $variation_products = $product->get_available_variations();

                        foreach ($variation_products as $variation_values) {
                            $variation_id = $variation_values['variation_id'];

                            // Get the variation product object
                            $variation = wc_get_product($variation_id);
                            $variation_price = $variation->get_price();

                            $new_price = wcpdm_calculate_new_price($changeMethod, $changeType, $variation_price, $change_Amount);

                            $adjustment_key = "_adjusted{$checkAmount}";
                            $adjusted_data = get_post_meta($variation_id, $adjustment_key, true);

                            if ( !$adjusted_data ) {
                                update_post_meta($variation_id, '_price', $new_price);
                                update_post_meta($variation_id, '_sale_price', $new_price);
                                update_post_meta($variation_id, $adjustment_key, 'yes');
                            }
                            wc_delete_product_transients($variation_id); // Clear/refresh the variation cache
                        }
                        // Clear/refresh the variable product cache
                        wc_delete_product_transients( $product_id );
                        return $new_price; 
                    }
                }

                // Check if price change is based on category and product is in the target category
                if ($changePriceBy === 'category' && !empty(array_intersect($changePriceCategories, $product_categories))) {    
                    // For variable products
                    if ($product__Type=== "variable") {
                        $new_price = null;
                        $variation_products = $product->get_available_variations();
                        foreach ($variation_products as $variation_values) {

                            $variation_id = $variation_values['variation_id'];
                            // Get the variation product object
                            $variation = wc_get_product($variation_id);
                            $variation_price = $variation->get_price();

                            $new_price = wcpdm_calculate_new_price($changeMethod, $changeType, $variation_price, $change_Amount);

                            $adjustment_key = "_adjusted{$checkAmount}";
                            $adjusted_data = get_post_meta($checkAmount, $adjustment_key, true );

                            if( !$adjusted_data ){
                                update_post_meta( $variation_id, '_price', $new_price );
                                update_post_meta( $variation_id, '_sale_price', $new_price );
                                update_post_meta($checkAmount, $adjustment_key, 'yes');
                            }
                            wc_delete_product_transients( $variation_id ); // Clear/refresh the variation cache
                        }
                        // Clear/refresh the variable product cache
                        wc_delete_product_transients( $product_id );
                        return $new_price;
                    } 
                    if ($product->get_type() === "simple" && !$product->is_type('variation')) { 
                        
                        $new_price = wcpdm_calculate_new_price($changeMethod, $changeType, $price, $change_Amount);
                        $adjusted_data = get_post_meta($checkAmount, '_adjusted', true );
                        if( !$adjusted_data ){
                            update_post_meta($product_id, '_sale_price', $new_price);
                            update_post_meta($product_id, '_adjusted', 'yes');
                        }
                        wc_delete_product_transients($product_id); // Clear transients
                        return $new_price; 
                    }
                }
    
            } // End of Foreach
            return $price; // Return the original price if no conditions met
        }

    }
} // End of class

// callback Custom function
function wcpdm_calculate_new_price($changeMethod, $changeType, $current_price, $changeAmount) {
    $new_price = $current_price; // Initialize $new_price with the current price
    
    if ($changeMethod === 'fixed') {
        $changeAmount = (float) $changeAmount; // Convert $changeAmount to float if necessary
        $current_price = (float) $current_price; 
        $new_price = ($changeType === 'increase') ? $current_price + $changeAmount : $current_price - $changeAmount;
    } elseif ($changeMethod === 'percentage') {

        $changeAmount = (float) $changeAmount; // Convert $changeAmount to float if necessary
        $current_price = (float) $current_price; 

        $percentage_change = ($current_price * $changeAmount) / 100;
        $new_price = ($changeType === 'increase') ? $current_price + $percentage_change : $current_price - $percentage_change;
    }
    
    return $new_price;
}



