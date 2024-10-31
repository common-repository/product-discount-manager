<?php
/*
* @Pakage wcpd product discount.
*/

if( !defined( 'ABSPATH' ) ){
    exit; // Exit if directly access.
}
// Check if class exist or not
function wcpdm_discount_update_page() {
    add_menu_page(
        __('Update Settings', 'product-discount-manager'), 
        __('Update Settings', 'product-discount-manager'), 
        'manage_options',
        'discount-settings-update', 
        'wcpdm_discount_update_callback',
        '',
        45 
    );
}
// The callback function to render the menu page
function wcpdm_discount_update_callback() { ?>
    <div id="wpbody" role="main">
        <div id="wpbody-content">
            <div class="wrap">
                <!-- Start Custom Wrapper -->
                <div class="wcpd_custom_wrapper">
                            <div class="row">
                                <div class="float-none my-4">
                                    <h1><?php esc_html_e( 'Edit Discount Settings', 'product-discount-manager' ); ?></h1>
                                    <h4 class="updateMessage text-success shadow-sm p-3 mb-5 bg-body rounded" style="display: none;"></h4>
                                </div>
                            </div>
                            <div class="col">
                                <!-- Update: Discount Settings Data -->
                                <div class="updateSettings_wrapper">

                                    <?php
                                        global $wpdb;
                                        // Access the submitted form data
   
                                        $nonce = isset($_GET['wcpdmNonce']) ? sanitize_text_field($_GET['wcpdmNonce']) : '';
                                        $editId = isset($_GET['editId']) ? sanitize_text_field($_GET['editId']) : '';

                                        if(  !wp_verify_nonce( $nonce, 'wcpdm_nonce' )){
                                            wp_send_json_error( 'Nonce verification failed.' );
                                            die();
                                        } 

                                        // Show Product Type and Created Group Name
                                        $discountUpdateQuery = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}spark_product_discount WHERE id = %d", $editId)); 
                                        
                                        foreach ($discountUpdateQuery as $key => $discountData) { 

                                            $discountId = isset($discountData->id) ? intval($discountData->id) : 1;
                                            $discountTitle = isset($discountData->wcpd_discount_title) ? sanitize_text_field($discountData->wcpd_discount_title) : '';
                                            
                                            $discountBy = isset($discountData->wcpd_discount_by) ? sanitize_text_field($discountData->wcpd_discount_by) : '';
                                            
                                            $productType = isset($discountData->wcpd_product_type) ? sanitize_text_field($discountData->wcpd_product_type) : '';
                                            
                                            $productGroup = isset($discountData->wcpd_productGroup_id) ? intval($discountData->wcpd_productGroup_id) : 0;
            
                                            $simpleSkus = isset($discountData->wcpd_simple_skus) ? json_decode(wp_unslash($discountData->wcpd_simple_skus)) : null;
                                            
                                            $variableSkus = isset($discountData->wcpd_variable_skus) ? json_decode(wp_unslash($discountData->wcpd_variable_skus)) : null;
                                            
                                            $discountType = isset($discountData->wcpd_discount_type) ? sanitize_text_field($discountData->wcpd_discount_type) : '';
                                            
                                            $discountAmount = isset($discountData->wcpd_discount_amount) ? floatval($discountData->wcpd_discount_amount) : 0;
                                                      
                                            $productCategories = isset($discountData->wcpd_product_category) ? json_decode(wp_unslash($discountData->wcpd_product_category)) : null;
                                            
                                            $discountSchedule = isset($discountData->wcpd_discount_schedule_date) ? json_decode(wp_unslash($discountData->wcpd_discount_schedule_date)) : null;
                                            
                                            $discountApply_on = isset($discountData->wcpd_discount_apply_on) ? sanitize_text_field($discountData->wcpd_discount_apply_on) : '';
                                            
                                            $timezoneDiscount = isset($discountData->wcpd_timezone_discount) ? sanitize_text_field($discountData->wcpd_timezone_discount) : '';
                                        ?>
                                        <table class="table settingsTable">
                                            <tbody>
                                                <!-- Table Body -->
                                                <tr>
                                                    <td>
                                                    <h6><?php esc_html_e( 'Discount Naming / Title:', 'product-discount-manager' ); ?></h6>
                                                    </td>
                                                    <td>
                                                        <div class="input-group" style="width: 70%">
                                                            <input type="text" class="form-control discountTitle" placeholder="Ex: Discount 10% On T-shirt " value="<?php echo esc_attr($discountTitle); ?>">
                                                        </div>
                                                        <p class="subtitle">
                                                            <?php esc_html_e( 'Enter your discount title or name ', 'product-discount-manager' ); ?>
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <h6><?php esc_html_e( 'Discount By :', 'product-discount-manager' ); ?>
                                                        </h6>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group discountByGroup" role="group"
                                                            aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check btn-sm btnCategory"
                                                                name="discountBy" value="category" id="btnCategory<?php echo esc_attr($key); ?>"
                                                                autocomplete="off" <?php echo ($discountBy === "category") ? "checked" : ''; ?>>
                                                            <label class="btn btn-outline-secondary categoryLabel"
                                                                for="btnCategory<?php echo esc_attr($key); ?>"><?php esc_html_e( 'Category', 'product-discount-manager' ); ?>
                                                            </label>
                                                            <input type="radio" class="btn-check btn-sm btnGroup"
                                                                name="discountBy" value="group" id="btnGroup<?php echo esc_attr($discountId); ?>"
                                                                autocomplete="off" <?php echo ($discountBy === "group") ? "checked" : ''; ?> >
                                                            <label class="btn btn-outline-secondary btnGrpLabel"
                                                                for="btnGroup<?php echo esc_attr($discountId); ?>"><?php esc_html_e( 'Group', 'product-discount-manager' ); ?>
                                                            </label>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="producType">
                                                    <td>
                                                        <h6><?php esc_html_e( 'Product Type :', 'product-discount-manager' ); ?></h6>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group productTypeGrp" role="group"
                                                            aria-label="Basic radio toggle button group">
                                                            <input type="radio" class="btn-check btn-sm btnSimple"
                                                                name="productType" value="simple" id="btnSimple<?php echo esc_attr($discountId); ?>" autocomplete="off"
                                                                <?php echo ($productType === "simple") ? "checked" : ''; ?>>
                                                            <label class="btn btn-outline-secondary btnSimpleLabel"
                                                                for="btnSimple<?php echo esc_attr($discountId); ?>"><?php esc_html_e( 'Simple', 'product-discount-manager' ); ?></label>
                                                            <input type="radio" class="btn-check btn-sm btnVariable"
                                                                name="productType" id="btnVariable<?php echo esc_attr($discountId); ?>"
                                                                autocomplete="off" value="variable" <?php echo ($productType === "variable") ? "checked" : ''; ?>>
                                                            <label class="btn btn-outline-secondary btnVariableLabel"
                                                                for="btnVariable<?php echo esc_attr($discountId); ?>"><?php esc_html_e( 'Variable', 'product-discount-manager' ); ?></label>
                                                        </div>
                                                        <p class="subtitle">
                                                            <?php esc_html_e( 'Choose a Product Type for Discount', 'product-discount-manager' ); ?>
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr class="productCategory">
                                                    <td>
                                                        <h6><?php esc_html_e( 'Category :', 'product-discount-manager' ); ?>
                                                        </h6>
                                                    </td>
                                                    <td>
                                                        <?php
                                                            // Get product categories
                                                            $wcpd_categories = get_terms( array(
                                                                'post_type' => 'product',
                                                                'taxonomy' => 'product_cat', 
                                                                'hide_empty' => false, 
                                                            ) );

                                                            // Check if there are any product categories
                                                            if ( !empty( $wcpd_categories ) && ! is_wp_error( $wcpd_categories ) && !empty($productCategories) ) {

                                                                echo '<div class="wcpd_updateProduct_category">';
                                                                foreach ( $wcpd_categories as $category ) {
                                                                    if( in_array($category->term_id, $productCategories )){
                                                                    echo'<div class="form-check">
                                                                            <input class="form-check-input wcpd_category_input" dataType="category" name="productCate" type="checkbox"
                                                                                value="'.esc_attr($category->term_id).'" id="productCat'. esc_attr($category->term_id).'" checked>
                                                                            <label class="form-check-label"
                                                                                for="productCat'. esc_attr($category->term_id).'">
                                                                                '.esc_html($category->name).'
                                                                                <span class="err_msg">Error massage</span>
                                                                            </label>
                                                                        </div>';
                                                                        
                                                                        }else{
                                                                        echo'<div class="form-check">
                                                                            <input class="form-check-input wcpd_category_input" dataType="category" name="productCate" type="checkbox"
                                                                                value="'.esc_attr($category->term_id).'" id="productCat'. esc_attr($category->term_id).'" >
                                                                            <label class="form-check-label"
                                                                                for="productCat'. esc_attr($category->term_id).'">
                                                                                '.esc_html($category->name).'
                                                                                <span class="err_msg">Error massage</span>
                                                                            </label>
                                                                        </div>';
                                                                    }
                                                                }
                                                                echo '</div>';
                            
                                                            } else {
                                                                foreach ( $wcpd_categories as $category ) {
                      
                                                                echo'<div class="form-check">
                                                                        <input class="form-check-input wcpd_category_input" dataType="category" name="productCate" type="checkbox"
                                                                            value="'.esc_attr($category->term_id).'" id="productCat'. esc_attr($category->term_id).'">
                                                                        <label class="form-check-label"
                                                                            for="productCat'. esc_attr($category->term_id).'">
                                                                            '.esc_html($category->name).'
                                                                            <span class="err_msg">Error massage</span>
                                                                        </label>
                                                                    </div>';
                                                                }
                                                            }
                                                        ?>
                                                        <p class="subtitle">
                                                            <?php esc_html_e( 'Choose Product Category for Discount', 'product-discount-manager' ); ?>
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr class="productGroup">
                                                    <td>
                                                        <h6><?php esc_html_e( 'Product Group :', 'product-discount-manager' ); ?></h6>
                                                    </td>
                                                    <td>
                                                        <div class="wcpd_sku_paid">
                                                        <select class="form-select wcpd_product_group wcpd_checkData wcpd_sku_paid"
                                                            aria-label="Default select"  dataType="group" style="width: 50%" name="groupId">
                                                            <?php 
                                                                global $wpdb;
                                                                // Show Product Type and Created Group Name
                                                                $groupNameQuery = $wpdb->get_results("SELECT pTable.group_id, gName.id, gName.group_name FROM {$wpdb->prefix}spark_group_product_table AS pTable INNER JOIN {$wpdb->prefix}spark_group_name_table AS gName ON pTable.group_id = gName.id   ");
                                                                echo ' <option value="" selected>'.esc_html__( 'Select Group', 'product-discount-manager' ).'</option>';
                                                                
                                                                foreach ($groupNameQuery as $key => $group) { 
                                                                    if( $productGroup == $group->id ){
                                                                        echo ' <option value="'.esc_attr($productGroup).'" selected >'.esc_html($group->group_name).'</option>';
                                                                    }else{
                                                                        echo ' <option value="'.esc_attr($group->id).'">'.esc_html($group->group_name).'</option>';
                                                                    }
                                                                }
                                                            ?>
                                                        </select>
                                                        </div>
                                                        <p class="subtitle">
                                                            <?php esc_html_e( 'Choose a Group You Created for Discount', 'product-discount-manager' ); ?>
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <h6><?php esc_html_e( 'Discount Type :', 'product-discount-manager' ); ?>
                                                        </h6>
                                                    </td>
                                                    <td>
                                                        <select class="form-select orm-select-lg discountType" aria-label=".form-select-lg example" name="discountType">
                                                            <?php if($discountType == "percentage"): ?>
                                                                <option value="percentage" selected>
                                                                    <?php esc_html_e( 'Percentage %', 'product-discount-manager' ); ?>
                                                                </option>
                                                                <option value="fixed">
                                                                    <?php esc_html_e( 'Fixed', 'product-discount-manager' ); ?>
                                                                </option>
                                                            <?php elseif($discountType == "fixed"): ?>
                                                                <option value="percentage">
                                                                    <?php esc_html_e( 'Percentage %', 'product-discount-manager' ); ?>
                                                                </option>
                                                                <option value="fixed" selected>
                                                                    <?php esc_html_e( 'Fixed', 'product-discount-manager' ); ?>
                                                                </option>
                                                            <?php else: ?>
                                                                <option selected><?php esc_html_e( 'Select Discount Type', 'product-discount-manager' ); ?></option>
                                                                <option value="percentage">
                                                                    <?php esc_html_e( 'Percentage %', 'product-discount-manager' ); ?>
                                                                </option>
                                                                <option value="fixed">
                                                                    <?php esc_html_e( 'Fixed', 'product-discount-manager' ); ?>
                                                                </option>
                                                            <?php endif; ?>
                                                        </select>
                                                        <p class="subtitle"><?php esc_html_e( 'Select an option for Discount', 'product-discount-manager' ); ?></p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <h6><?php esc_html_e( 'Discount Amount :', 'product-discount-manager' ); ?></h6>
                                                    </td>
                                                    <td>
                                                        <div class="input-group" style="width: 50%">
                                                            <input type="number" value="<?php echo esc_attr($discountAmount); ?>" class="form-control discountAmount" name="discountAmount">
                                                        </div>
                                                        <p class="subtitle">
                                                            <?php esc_html_e( 'Enter Discount Number', 'product-discount-manager' ); ?>
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <h6><?php esc_html_e( 'Discount Schedule :', 'product-discount-manager' ); ?>
                                                        </h6>
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <div class="schedulePicker" >
                                                                <?php 
                                                                    if($discountSchedule){

                                                                        printf(
                                                                            '<input type="text" class="date start" name="dateStart" value="%s" />' .
                                                                            '<input type="text" class="time start" name="timeStart" value="%s" /> To' .
                                                                            '<input type="text" class="time end" name="timeEnd" value="%s" />' .
                                                                            '<input type="text" class="date end" name="dateEnd" value="%s" />',
                                                                            esc_attr($discountSchedule->dateStart),
                                                                            esc_attr($discountSchedule->timeStart),
                                                                            esc_attr($discountSchedule->timeEnd),
                                                                            esc_attr($discountSchedule->dateEnd)
                                                                        );
                                                                        }else { ?>
                                                                            <input type="text" class="date start" name="dateStart" />
                                                                            <input type="text" class="time start" name="timeStart" /> To
                                                                            <input type="text" class="time end" name="timeEnd" />
                                                                            <input type="text" class="date end" name="dateEnd" />
                                                                        <?php
                                                                    }
                                                                ?>
                                                            </div>
                                                        </div>
                                                        <p class="subtitle">
                                                            <?php esc_html_e( 'Choose discount Schedule Date & Time ', 'product-discount-manager' ); ?>
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <h6><?php esc_html_e( 'Apply On :', 'product-discount-manager' ); ?>
                                                        </h6>
                                                    </td>
                                                    <td>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                value="sale" id="sale" name="apply_on"  <?php echo ($discountApply_on == "sale") ? 'checked' : ''; ?>>
                                                            <label class="form-check-label"
                                                                for="sale">
                                                                <?php esc_html_e( 'Sale', 'product-discount-manager' ); ?>
                                                            </label>
                                                        </div>
                                                    </td>
                                                </tr>

                                            </tbody> <!-- ./Table Body -->
                                        </table>

                                        <!-- Custom Alert Box  -->
                                        <div class="modal fade" id="wcpd_customAlert1" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                            <div class="modal-dialog wcpd-modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="btn-close py-1" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <span class="alertMsg"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php 
                                        }
                                    ?>
                                    <input type="hidden" name="updateId" value="<?php echo esc_attr($editId); ?>">
                                    <button type="submit" id="saveUpdateSettings" class="btn button-primary btn-sm"><?php esc_html_e( 'Update Settings', 'product-discount-manager' ); ?></button>
                                </div>

                                    <!-- Custom Alert Box  -->
                                    <div class="modal fade" id="wcpd_customUpdateAlert" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog wcpd-modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="btn-close py-1" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <span class="alertMsg"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <!-- End: Update Settings Data -->
                            </div> <!--/ End col -->
                    <!--/ End Custom Wrapper -->
                </div> 
                <!-- End Custom Wrapper -->
            </div>
            <!--/ End WP Wrap -->
            <div class="clear"></div>
        </div><!-- wpbody-content -->
        <div class="clear"></div>
    </div>
    <!--End Main -->
<?php

}
