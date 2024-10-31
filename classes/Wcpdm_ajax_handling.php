<?php
/*
* @Pakage product discount manager.
*/
if( !defined( 'ABSPATH' ) ){
    exit; // Exit if directly access.
}
global $wpdb;
// Check class already exist or not
if( !class_exists('Wcpdm_Ajax_Handle')){
    class Wcpdm_Ajax_Handle{
        
        // Discount Settings Data
        public function discount_settings_data(){
            global $wpdb;

            if( !check_ajax_referer( 'wcpd_woo_nonce', 'security', false )){
                wp_send_json_error( 'Nonce verification failed.' );
                die();
            } 

            // Edit nonce 
            $editNonceId = isset($_POST['editNonce']) ? sanitize_text_field( $_POST['editNonce'] ) : '';

            $table_name = "{$wpdb->prefix}spark_product_discount";
            // Check if the table exists
            $table_exists = $wpdb->get_var($wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

            // Access the submitted form data
            // usage:
            $formData = WCPDM_CORE::wcpdmSanitize($_POST['formData']);
            // Assign Data to Variable
            $discount_title = isset($formData['discountTitle']) ? $formData['discountTitle']: '';
            $discount_by    = isset($formData['discountBy']) ? $formData['discountBy']: '';
            $product_type   = isset($formData['productType']) ? $formData['productType']: '';


            $product_categories = isset($formData['categories']) ? wp_json_encode($formData['categories']): null;
            $productGroup = isset($formData['productGroup']) ? $formData['productGroup']: '';
            $simple_skus = isset($formData['simpleSkus']) ? $formData['simpleSkus']: null;

            $variable_skus = isset($formData['variableSkus']) ? wp_json_encode($formData['variableSkus']): null;
            $discount_type = isset($formData['discountType']) ? $formData['discountType']: '';
            $discount_amount = isset($formData['discountAmount']) ? $formData['discountAmount']: 0;
            $discount_schedule_date = isset($formData['discountSchedule']) ? wp_json_encode($formData['discountSchedule']): null;

            $discount_apply_on = isset( $formData['applyOn'] ) ? sanitize_text_field($formData['applyOn']) : 'sale';
            $discount_timeZone = isset($formData['timeZone']) ? $formData['timeZone'] : '';


            if ( $table_exists) {
                // Check Already Category saved in DB
                $qryData = $wpdb->get_results("SELECT wcpd_product_category FROM {$wpdb->prefix}spark_product_discount WHERE wcpd_product_category IS NOT NULL");

                $categoryData = !empty($qryData[0]->wcpd_product_category ) ? json_decode($qryData[0]->wcpd_product_category) : [];

                $discountCategories = [];
                foreach ($categoryData as $data) {
                   $discountCategories[] = $data;
                }
                $category_count = $discountCategories; 
                // Restriction for this version.
                $row_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}spark_product_discount");

                if( $row_count < 2 || ( $row_count < 2 && count($discountCategories) < 1 ) ){
                    $wpdb_result = $wpdb->insert($table_name, array(
                        'wcpd_discount_title' => $discount_title,
                        'wcpd_discount_by' => $discount_by,
                        'wcpd_product_type' => $product_type,
                        'wcpd_product_category' => count($discountCategories) < 1 ? $product_categories : null,
                        'wcpd_productGroup_id' => $productGroup,
                        'wcpd_simple_skus' => $simple_skus,
                        'wcpd_variable_skus' => $variable_skus,
                        'wcpd_discount_type' => $discount_type,
                        'wcpd_discount_amount' => $discount_amount,
                        'wcpd_discount_schedule_date' => $discount_schedule_date,
                        'wcpd_discount_apply_on' => $discount_apply_on,
                        'wcpd_timezone_discount' => $discount_timeZone
                    ));
                }else {
                    return false;
                }
            }
                        
            // Fetch the most recent record from the database
            $qryDiscountData = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}spark_product_discount ORDER BY id DESC LIMIT %d", 1));

            if ($qryDiscountData) {
                // Fetch Categories name using category id.
                $product_category_ids = isset($qryDiscountData->wcpd_product_category) ? json_decode($qryDiscountData->wcpd_product_category) : null;

                $category_names = array();
                $comma_separated_names = '';

                if ( is_array($product_category_ids) && !empty($product_category_ids) ) {
                    foreach ($product_category_ids as $category_id) {
                        $category = get_term_by('id', $category_id, 'product_cat');

                        // Check if $category is a valid object
                        if ($category && is_object($category)) {
                            // Check if the 'name' property exists before accessing it
                            if (property_exists($category, 'name')) {
                                $category_names[] = $category->name;
                            }
                        }
                    }
                }

                if (!empty($category_names)) {
                    $comma_separated_names = implode(', ', $category_names);
                 }

                $grpId = $qryDiscountData->wcpd_productGroup_id;
                $table_name = "{$wpdb->prefix}spark_group_name_table";
  
                $groupNameQry = $wpdb->get_results($wpdb->prepare("SELECT group_name FROM %s WHERE id = %s", $table_name,$grpId ));
                // $qryDiscountData contains the most recent record
                $discountData = $qryDiscountData;
            } else {
                // Handle the case when no records are found
                $discountData = null;
            }

            // Url for Edit discount
            $updatePageUrl = admin_url("admin.php?page=discount-settings-update&editId={$discountData->id}&wcpdmNonce={$editNonceId}");

            // Return a response
            wp_send_json_success( array(
                'success'      => 'Your Discount Created successfully!',
                'discountData' => $discountData,
                'groupQuery'    => $groupNameQry,
                'updatePageUrl' => $updatePageUrl,
                'category_name' => $comma_separated_names,
                'category_count' => $category_count
            ) );
            wp_die();
        }
        // Update Discount Settings Data
        public function update_discountSettings_data(){

            global $wpdb;
            // Access the submitted form data
            if( !check_ajax_referer( 'wcpd_woo_nonce', 'security', false )){
                wp_send_json_error( 'Nonce verification failed.' );
                die();
            } 

            // Access the submitted form data
            $formData = WCPDM_CORE::wcpdmSanitize($_POST['formData']);

            $updateId = isset($formData['updateId']) ? $formData['updateId']  : '';
            $checked_categories = isset($formData['categories']) ?  $formData['categories']  : '';



            $table_name = "{$wpdb->prefix}spark_product_discount";
            // Check if the table exists
            $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

            // Assign Data to Variable
            $discount_title = isset($formData['discountTitle']) ? $formData['discountTitle']: '';
            $discount_by = isset($formData['discountBy']) ? $formData['discountBy']: '';
            $product_type = isset($formData['productType']) ? $formData['productType']: '';
            $categories = isset($formData['categories']) ? wp_json_encode($formData['categories']): '';
            $productGroup = isset($formData['productGroup']) ? $formData['productGroup']: '';
            $simple_skus = isset($formData['simpleSkus']) ? wp_json_encode($formData['simpleSkus']): null;
            $variable_skus = isset($formData['variableSkus']) ? wp_json_encode($formData['variableSkus']): null;
            $discount_type = isset($formData['discountType']) ? $formData['discountType']: '';
            $discount_amount = isset($formData['discountAmount']) ? $formData['discountAmount']: '';
            $discount_schedule_date = isset($formData['discountSchedule']) ? wp_json_encode($formData['discountSchedule']): '';
            $discount_apply_on = isset( $formData['applyOn'] ) ? sanitize_text_field($formData['applyOn']) : '';
            $discount_timeZone = isset($formData['timeZone']) ? ($formData['timeZone']) : '';

            if ($table_exists) {
                // Check Already Category saved in DB
                $qryData = $wpdb->get_results("SELECT wcpd_product_category FROM {$wpdb->prefix}spark_product_discount WHERE wcpd_product_category IS NOT NULL");

                $categoryData = !empty($qryData[0]->wcpd_product_category ) ? json_decode($qryData[0]->wcpd_product_category) : '';

                $discountCategories = [];
                foreach ($categoryData as $data) {
                   $discountCategories[] = $data;
                }

                $category_count = $discountCategories; 

                if( count($category_count) < 2 ){
                    $wpdb_result = $wpdb->update($table_name, array(
                        'wcpd_discount_title' => $discount_title,
                        'wcpd_discount_by'    => $discount_by,
                        'wcpd_product_type'   => $product_type,
                        'wcpd_product_category' => $categories,
                        'wcpd_productGroup_id' => $productGroup,
                        'wcpd_simple_skus' => $simple_skus,
                        'wcpd_variable_skus' => $variable_skus,
                        'wcpd_discount_type' => $discount_type,
                        'wcpd_discount_amount' => $discount_amount,
                        'wcpd_discount_schedule_date' => $discount_schedule_date,
                        'wcpd_discount_apply_on' => $discount_apply_on,
                        'wcpd_timezone_discount' => $discount_timeZone,
                    ),  array('id' => $updateId) );

                        // Check for errors and display them
                    if ($wpdb_result === false) {
                        echo "Error: " . esc_html($wpdb->last_error);
                    }
                }
            }
            $admin_page_url = admin_url('admin.php?page=product-discount-manager#navDiscountSettings');
            // Send the URL as part of the response data
            wp_send_json_success(array(
                'success'       => 'Your Data Updated successfully!',
                'category_count'=> $category_count,
                'redirect_url'  => $admin_page_url
            ));
            wp_die(); 
        }
        // Delete Created Discount Settings
        public function delete_created_discountSettings(){
            global $wpdb;
            if( !check_ajax_referer( 'wcpd_woo_nonce', 'security', false )){
                wp_send_json_error( 'Nonce verification failed.' );
                die();
            } 

            $delId = isset($_POST['delId']) ? sanitize_text_field($_POST['delId']) : '';
            
            $table_name = "{$wpdb->prefix}spark_product_discount";
            // Check if the table exists
            $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

            // Check if the group_id already exists in the database
            if ( $table_exists ) {
                $result = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}spark_product_discount WHERE id = %d", $delId ));
            }
            $admin_page_url = admin_url('admin.php?page=product-discount-manager');
            // Return a response
            wp_send_json_success( array(
                'success'  => 'Your Discount Settings Deleted successfully!',
                'delId'    => $delId,
                'redirect_url'  => $admin_page_url
            ) );
            wp_die();
        }
        // Delete Created Group Name
        public function delete_created_groupName(){
            
            global $wpdb;
            if( !check_ajax_referer( 'wcpd_woo_nonce', 'security', false )){
                wp_send_json_error( 'Nonce verification failed.' );
                die();
            } 

            $groupId = isset($_POST['groupId']) ? sanitize_text_field($_POST['groupId']) : '';
            $table_name = "{$wpdb->prefix}spark_group_name_table";
            // Check if the table exists
            $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

            // Check if the group_id already exists in the database
            if ($table_exists) {
                $result = $wpdb->query($wpdb->prepare("DELETE FROM %s WHERE id = %d", $table_name, $groupId));
            }
            $admin_page_url = admin_url('admin.php?page=product-discount-manager#nav-group');

            // Fetch Data from database
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}spark_group_name_table" );

            // print_r($results);
            $groupName = [];
            if( !empty($results)){
                foreach ($results as $value) {
                    $groupName[] = $value;
                }
            }
            // Return a response
            wp_send_json_success( array(
                'success'  => 'Group Deleted Successfully!',
                'id'    => $groupId,
                'groupName'    => $groupName,
                'redirect_url'  => $admin_page_url
            ) );
            wp_die();
        }
        // Change Product Price
        public function changeProduct_price(){

            global $wpdb;
            if( !check_ajax_referer( 'wcpd_woo_nonce', 'security', false )){
                wp_send_json_error( 'Nonce verification failed.' );
                die();
            } 

            // Access the submitted form data

            // usage:
            $formData = WCPDM_CORE::wcpdmSanitize($_POST['formData']);

            $productType = isset($formData['byProductType']) ? $formData['byProductType'] : '';
            $changePriceCategory = isset($formData['changePriceCats']) ? wp_json_encode($formData['changePriceCats']) : '';
            $changePriceBy = isset($formData['changeBy']) ? $formData['changeBy'] : '';
            $changeType = isset($formData['changeType']) ? $formData['changeType']: '';
            $priceType = isset($formData['priceType']) ? $formData['priceType']: '';
            $changeAmount = isset($formData['changeAmount']) ? $formData['changeAmount']: 0;

            $table_name = "{$wpdb->prefix}spark_product_price_change";
            // Check if the table exists
            $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

            if ($table_exists) {
                // Count existing rows for the given conditions
                $countRows = $wpdb->get_var( "SELECT COUNT(*) FROM  {$wpdb->prefix}spark_product_price_change");

                // Allow insertion only if the count is less than 2
                if ($countRows >= 2) {
                    wp_send_json_error(array(
                        'error_msg' => 'You can only have change price up to 2 rows for the specified conditions in This version'
                    ));
                }
                // Fetch all rows 
                $qryData = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}spark_product_price_change");
                $catNumber = '';
                foreach ($qryData as $data) {
                    $changePriceCats = isset($data->changePriceCats) ? json_decode($data->changePriceCats) : [];
                    $catNumber = $changePriceCats;  
                }

                if (($changePriceBy === "category" && ( $catNumber < 1 || $catNumber === null)) ||
                ($changePriceBy === "productType" && $changeType === "increase")) {
                    $wpdb_result = $wpdb->insert($table_name, array(
                        'changePriceBy' => $changePriceBy,
                        'productType' => $productType,
                        'changePriceCats' => $changePriceCategory,
                        'changeType' => $changeType,
                        'changeMethod' => $priceType,
                        'changeAmount' => $changeAmount,
                        'changeOn' => $formData['changeApply_on'],
                    ));
                }
            }
            
            $redirect_url = admin_url('admin.php?page=product-discount-manager#navPriceChange');
            // Return a response
            wp_send_json_success( array(
                'msg'  => 'Price Saved Successfully!',
                'catNumber'    => $catNumber ? $catNumber : 0,
                'redirect_url' => $redirect_url,
            ) );
            wp_die();
        }
        // Delete Change Product Price
        public function delete_changeProduct_price_list(){
            global $wpdb;
            if( !check_ajax_referer( 'wcpd_woo_nonce', 'security', false )){
                wp_send_json_error( 'Nonce verification failed.' );
                die();
            } 
            $delId = isset($_POST['changesPriceId']) ? absint($_POST['changesPriceId']) : 0;

            $table_name = "{$wpdb->prefix}spark_product_price_change";
            // Check if the table exists
            $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

            // Check if the group_id already exists in the database
            if ($table_exists) {
                $result = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}spark_product_price_change WHERE id = %d", $delId));
            }
            // Return a response
            wp_send_json_success( array(
                'success'  => 'Changes Price Removed Successfully!',
                'id'    => $delId,
            ) );
            wp_die();
        }
    }
}



