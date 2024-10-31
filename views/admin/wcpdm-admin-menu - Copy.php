<?php
/*
* @Pakage product discount manager.
*/

if( !defined( 'ABSPATH' ) ){
    exit; // Exit if directly access.
}
// Check if class exist or not
function wcpdm_product_discount_page() {
    add_menu_page(
        __('Product Discount Settings', 'product-discount-manager'), 
        __('Product Discount Manager', 'product-discount-manager'), 
        'manage_options',
        'product-discount-manager', 
        'wcpdm_product_discount_callback',
         'dashicons-cart',
        25 
    );
}

// The callback function to render the menu page
function wcpdm_product_discount_callback() { ?>
    <div id="wpbody" role="main">
        <div id="wpbody-content">
            <div class="wrap">
                <div class="wcpd_custom_wrapper">
                    <!-- Start Custom Wrapper -->
                    <div class="row">
                        <div class="float-none wcpdm_title_area">
                            <h1><?php esc_html_e( 'Product Discount Manager', 'product-discount-manager' ); ?></h1>
                        </div>
                    </div>
                    <nav>
                        <div class="nav nav-tabs" id="wcpd-admin-tab" role="tablist">
                            <button class="nav-link active" id="nav-addNew-tab" data-bs-toggle="tab" data-bs-target="#nav_addNew"
                                type="button" role="tab" aria-controls="nav_addNew"
                                aria-selected="false"><?php esc_html_e( 'Discount Settings', 'product-discount-manager' ); ?>
                            </button>

                            <button class="nav-link" id="nav-discount-tab" data-bs-toggle="tab"
                                data-bs-target="#navDiscountSettings" type="button" role="tab"
                                aria-controls="navDiscountSettings"
                                aria-selected="true"><?php esc_html_e( 'Created Discount', 'product-discount-manager' ); ?>
                            </button>

                            <button class="nav-link" id="nav-price-tab" data-bs-toggle="tab" data-bs-target="#navPriceChange"
                                type="button" role="tab" aria-controls="navPriceChange"
                                aria-selected="false"><?php esc_html_e( 'Change Price', 'product-discount-manager' ); ?>
                            </button>
                        </div> <!-- End of Nav-tabs -->
                    </nav>
                    <div class="tab-content py-3" id="nav-tabContent">
                        <!-- Add New -->
                        <div class="tab-pane fade show active pb-2" id="nav_addNew" role="tabpanel"
                                                aria-labelledby="nav-addNew-tab" tabindex="0">
                            <div class="row">
                                <div class="float-none my-4">            
                                    <h5 class="discount_setting_header shadow-none p-3 mb-5 bg-light rounded"><?php esc_html_e( 'Add New Discount', 'product-discount-manager' ); ?></h5>
                                    <h4 class="successMsg text-success shadow-sm p-3 mb-5 bg-body rounded" style="display: none;"></h4>
                                </div>
                            </div>

                            <div class="col">
                                <table class="table settingsTable">
                                    <!-- Table Body -->
                                    <tbody>
                                        <tr>
                                            <td>
                                            <h6><?php esc_html_e( 'Discount Naming / Title:', 'product-discount-manager' ); ?></h6>
                                            </td>
                                            <td>
                                                <div class="input-group" style="width: 70%">
                                                    <input type="text" class="form-control discountTitle" placeholder="Ex: Discount 10% On T-shirt" required >
                                                </div>
                                                <p class="subtitle error_subtile">
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
                                                        name="discountBy" value="category" id="btnCategory"
                                                        autocomplete="off" checked>
                                                    <label class="btn btn-outline-secondary categoryLabel"
                                                        for="btnCategory"><?php esc_html_e( 'Category', 'product-discount-manager' ); ?>
                                                    </label>

                                                    <input type="radio" class="btn-check btn-sm btnGroup"
                                                        name="discountBy" value="group" id="btnGroup"
                                                        autocomplete="off">
                                                    <label class="btn btn-outline-secondary btnGrpLabel"
                                                        for="btnGroup"><?php esc_html_e( 'Group', 'product-discount-manager' ); ?>
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
                                                        name="productType" value="simple" id="btnSimple" autocomplete="off" checked>
                                                    <label class="btn btn-outline-secondary btnSimpleLabel"
                                                        for="btnSimple"><?php esc_html_e( 'Simple', 'product-discount-manager' ); ?></label>

                                                    <input type="radio" class="btn-check btn-sm btnVariable"
                                                        name="productType" id="btnVariable"
                                                        autocomplete="off" value="variable">
                                                    <label class="btn btn-outline-secondary btnVariableLabel"
                                                        for="btnVariable"><?php esc_html_e( 'Variable', 'product-discount-manager' ); ?></label>
                                                </div>
                                                <p class="subtitle">
                                                    <?php esc_html_e( 'Choose a Product Type for Discount', 'product-discount-manager' ); ?>
                                                </p>
                                            </td>
                                        </tr>
                                        <tr class="productCategory">
                                            <td>
                                                <h6>
                                                    <?php esc_html_e( 'Category :', 'product-discount-manager' ); ?>
                                                </h6>
                                            </td>
                                            <td>
                                                <?php
                                                    // Fetch category data from "wp_spark_product_discount" Table.
                                                    global $wpdb;
                                                    $table_name = "{$wpdb->prefix}spark_product_discount";
                                                    // Check if the table exists
                                                    $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

                                                    // Check if the group_id already exists in the database
                                                    $dataQuery = '';
                                                    if ($table_exists) {
                                                        $dataQuery = $wpdb->get_results("SELECT wcpd_product_category FROM {$wpdb->prefix}spark_product_discount WHERE wcpd_product_category IS NOT NULL");
                                                    }
                                                    
                                                    $dbProductCategories = '';
                                                    if ( is_array($dataQuery) && count($dataQuery) > 0 ) {
                                                        foreach ($dataQuery as $key => $data) {
                                                            $dbProductCategories = isset($data->wcpd_product_category) && $data->wcpd_product_category !== null ? json_decode($data->wcpd_product_category) : [];
                                                        }
                                                    }

                                                    // Get product categories
                                                    $wcpd_categories = get_terms( array(
                                                        'post_type' => 'product',
                                                        'taxonomy' => 'product_cat', 
                                                        'hide_empty' => false, 
                                                    ) );

                                                    // Check if there are any product categories
                                                    if ( ! empty( $wcpd_categories ) && ! is_wp_error( $wcpd_categories ) ) {

                                                        echo '<div id="wcpd_productCategory" class="wcpd_product_category">';
                                                        foreach ( $wcpd_categories as $category ) {

                                                            $isCategoryChecked = $dbProductCategories && in_array($category->term_id, $dbProductCategories);

                                                            echo '<div class="form-check">
                                                                    <input class="form-check-input wcpd_category_input"   dataType="category" name="productCate" type="checkbox"
                                                                        value="'.esc_attr($category->term_id).'" id="productCat'. esc_attr($category->term_id).'" '.($isCategoryChecked ? ' checked' : '').'>
                                                                    <label class="form-check-label"
                                                                        for="productCat'. esc_attr($category->term_id).'">
                                                                        '.esc_html($category->name).'
                                                                        <span class="err_msg">Error massage</span>
                                                                    </label>
                                                                </div>';
                                                            }
                                                            echo '</div>';
                    
                                                    } else {
                                                        esc_html_e( 'No product categories found.', 'product-discount-manager' );
                                                    }
                                                ?>
                                                <p class="subtitle">
                                                    <?php esc_html_e( 'Choose Product Category for Discount - ( 1 Category Limit in this Version )', 'product-discount-manager' ); ?>
                                                </p>
                                            </td>
                                        </tr>

                                        <tr class="productGroup">
                                            <td>
                                                <h6><?php esc_html_e( 'Product Group :', 'product-discount-manager' ); ?></h6>
                                            </td>
                                            <td>
                                            <div class="wcpd_sku_paid">
                                            <select id="wcpd_productGroup" dataType="group" class="form-select wcpd_product_group wcpd_checkData wcpd_sku_paid"
                                                    aria-label="Default select">
                                                    <?php 
                                                    
                                                        global $wpdb;
                                                        // Show Product Type and Created Group Name
                                                        $groupNameQuery = $wpdb->get_results("SELECT pTable.group_id, gName.id, gName.group_name FROM {$wpdb->prefix}spark_group_product_table AS pTable INNER JOIN {$wpdb->prefix}spark_group_name_table AS gName ON pTable.group_id = gName.id");

                                                        echo ' <option value="">'.esc_html__( 'Select Group', 'product-discount-manager' ).'</option>';
                                                        foreach ($groupNameQuery as $key => $group) { 
                                                            echo ' <option value="'.esc_attr($group->id).'">'.esc_html($group->group_name).'</option>';
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
                                                    <option selected><?php esc_html_e( 'Select Discount Type', 'product-discount-manager' ); ?></option>
                                                    <option value="percentage">
                                                        <?php esc_html_e( 'Percentage %', 'product-discount-manager' ); ?>
                                                    </option>
                                                    <option value="fixed">
                                                        <?php esc_html_e( 'Fixed', 'product-discount-manager' ); ?>
                                                    </option>
                                                </select>
                                                <p class="subtitle"><?php esc_html_e( 'Select an option for Discount', 'product-discount-manager' ); ?></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <h6><?php esc_html_e( 'Discount Amount :', 'product-discount-manager' ); ?></h6>
                                            </td>
                                            <td>
                                                <div class="input-group" style="width: 30%">
                                                    <input type="number" value="" class="form-control discountAmount" name="discountAmount" placeholder="Ex: 10"  required>
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
                                                        <input type="text" class="date start" placeholder="Starting Date" name="dateStart" />
                                                        <input type="text" class="time start" placeholder="Starting Time" name="timeStart" /> <?php esc_html_e( 'To', 'product-discount-manager'); ?>
                                                        <input type="text" class="time end" placeholder="End Time" name="timeEnd" />
                                                        <input type="text" class="date end" placeholder="End Date" name="dateEnd" />
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
                                                        value="sale" id="sale" name="apply_on" checked>
                                                    <label class="form-check-label"
                                                        for="sale">
                                                        <?php esc_html_e( 'Sale', 'product-discount-manager' ); ?>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>

                                    </tbody> <!-- ./Table Body -->
                                </table>
                                <button type="button" class="btn btn-success saveSettings"><?php esc_html_e('Save Settings', 'product-discount-manager'); ?></button>
                            </div>
                        </div>

                        <div class="tab-pane fade show pb-2" id="navDiscountSettings" role="tabpanel"
                            aria-labelledby="nav-discount-tab" tabindex="0">

                            <div class="col">
                                <h5 class="created_discount_title shadow-none p-3 mb-5 bg-light rounded"><?php esc_html_e( 'Created Discount Settings', 'product-discount-manager' ); ?></h5>
                                <h4 class="deleteMessage text-success shadow-sm p-3 mb-5 bg-body rounded" style="display: none;"></h4>
                                <!-- Show: Discount Settings Data -->
                                <div class="settings_wrapper">
                                    <form id="discountSettingsForm">
                                        <div class="accordion" id="discountSetting">
                                            <?php
                                                global $wpdb;
                                                // Show Product Type and Created Group Name
                                                $discountQuery = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}spark_product_discount"); 

                                                // Nonce 
                                                $wcpdm_edit_nonce = wp_create_nonce( 'wcpdm_nonce' );
                                                // Generate and include nonce in HTML markup
                                                echo '<input type="hidden" id="wcpdm_nonce" value="' . esc_attr($wcpdm_edit_nonce) . '">';

                                                foreach ($discountQuery as $key => $discountData) { 
                                                    // Assuming $discountData->id is numeric, intval() ensures it's treated as an integer
                                                    $discountId = isset($discountData->id) ? intval($discountData->id) : 1;
                                                    $discountTitle = isset($discountData->wcpd_discount_title) ? sanitize_text_field($discountData->wcpd_discount_title) : '';
                                                    
                                                    $discountBy = isset($discountData->wcpd_discount_by) ? sanitize_text_field($discountData->wcpd_discount_by) : '';
                                                    
                                                    $productType = isset($discountData->wcpd_product_type) ? sanitize_text_field($discountData->wcpd_product_type) : '';
                                                    
                                                    $productGroupId = isset($discountData->wcpd_productGroup_id) ? intval($discountData->wcpd_productGroup_id) : 0;
                                                    // Assuming $discountData->wcpd_productGroup_id is numeric, intval() ensures it's treated as an integer
                                                    
                                                    $simpleSkus = isset($discountData->wcpd_simple_skus) ? json_decode(wp_unslash($discountData->wcpd_simple_skus)) : null;
                                                    
                                                    $variableSkus = isset($discountData->wcpd_variable_skus) ? json_decode(wp_unslash($discountData->wcpd_variable_skus)) : null;
                                                    
                                                    $discountType = isset($discountData->wcpd_discount_type) ? sanitize_text_field($discountData->wcpd_discount_type) : '';
                                                    
                                                    $discountApply_on = isset($discountData->wcpd_discount_apply_on) ? sanitize_text_field($discountData->wcpd_discount_apply_on) : '';
                                                    
                                                    $discountAmount = isset($discountData->wcpd_discount_amount) ? floatval($discountData->wcpd_discount_amount) : 0;
                                                    $productCategories = isset($discountData->wcpd_product_category) ? json_decode(wp_unslash($discountData->wcpd_product_category)) : '';
                                                    
                                                    $timezoneDiscount = isset($discountData->wcpd_timezone_discount) ? sanitize_text_field($discountData->wcpd_timezone_discount) : '';
                                                    
                                                    $discountScheduleDate = isset($discountData->wcpd_discount_schedule_date) ? json_decode(wp_unslash($discountData->wcpd_discount_schedule_date)) : null;

                                                    // Update url
                                                    $discountUpdatePage = admin_url("admin.php?page=discount-settings-update&editId={$discountId}&wcpdmNonce={$wcpdm_edit_nonce}");

                                                ?>

                                                    <div class="accordion-item mb-2">
                                                        <h2 class="accordion-header wcpd_accordionBtn" id="heading_<?php echo esc_attr($discountId); ?>">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="#collapse<?php echo esc_attr($discountId); ?>"
                                                                aria-expanded="false" aria-controls="collapse<?php echo esc_attr($discountId); ?>">
                                                                <?php echo esc_html($discountTitle); ?>

                                                            </button>
                                                                <!-- Update & Delete Button -->
                                                                <div class="accordionItemAction">
                                                                    <a href="<?php echo esc_url($discountUpdatePage); ?>" target="_self" type="button" id="<?php echo esc_attr($discountId); ?>" class="btn btn-primary itemUpdate"><?php esc_html_e( 'Edit', 'product-discount-manager' ) ?></a>
                                                                    <span type="button" class="btn btn-danger itemDelete" id="<?php echo esc_attr($discountId); ?>"><?php esc_html_e( 'Delete', 'product-discount-manager' ) ?></span>
                                                                </div>
                                                        </h2>

                                                        <div id="collapse<?php echo esc_attr($discountId); ?>" class="accordion-collapse collapse"
                                                            aria-labelledby="heading_<?php echo esc_attr($discountId); ?>" data-bs-parent="#discountSetting">
                                                            <div class="accordion-body">

                                                                <table class="table settingsTable mb-0">
                                                                    <tbody>
                                                                        <!-- Table Body -->
                                                                        <tr>
                                                                            <td>
                                                                            <h6><?php esc_html_e( 'Discount Naming / Title:', 'product-discount-manager' ); ?></h6>
                                                                            </td>
                                                                            <td>
                                                                            <h6><?php printf( esc_html__( '%s', 'product-discount-manager' ), $discountTitle ); ?></h6>

                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <h6><?php esc_html_e( 'Discount By :', 'product-discount-manager' ); ?>
                                                                                </h6>
                                                                            </td>
                                                                            <td>
                                                                            <h5>
                                                                                <?php if($discountBy === "category"){
                                                                                        echo '<span class="badge bg-secondary">'.esc_html__('Category', 'product-discount-manager').'</span>';
                                                                                    }elseif ($discountBy === "group") {
                                                                                        echo '<span class="badge bg-secondary">'.esc_html__('Group', 'product-discount-manager').'</span>';
                                                                                    }else{
                                                                                        esc_html_e( 'No Discount By Selected', 'product-discount-manager' );
                                                                                    }
                                                                                ?>
                                                                            </h5>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <h6><?php esc_html_e( 'Product Type :', 'product-discount-manager' ); ?></h6>
                                                                            </td>
                                                                            <td>
                                                                                <h5>
                                                                                    <?php if($productType === "simple"){
                                                                                        echo '<span class="badge bg-secondary">'.esc_html__('Simple', 'product-discount-manager').'</span>';
                                                                                    }elseif ($productType === "variable") {
                                                                                        echo '<span class="badge bg-secondary">'.esc_html('Variable', 'product-discount-manager').'</span>';
                                                                                    }else{
                                                                                        esc_html_e( 'No Discount Type Selected', 'product-discount-manager' );
                                                                                    }
                                                                                    ?>
                                                                                </h5>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <h6><?php esc_html_e( 'Category :', 'product-discount-manager' ); ?>
                                                                                </h6>
                                                                            </td>
                                                                            <td>
                                                                                <h6>
                                                                                <?php
                                                                                    // Get product categories id from db
                                                                                    $category_ids = $productCategories;
                                                                                    // Retrieve the category names
                                                                                    $category_names = array();
                                                                                    if( $category_ids ){
                                                                                        foreach ( $category_ids as $category_id) {
                                                                                            $category = get_term_by('term_id', $category_id, 'product_cat');
                                                                                            if ($category && !is_wp_error($category)) {
                                                                                                $category_names[] = $category->name;
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                    // Display the category names
                                                                                    if (!empty($category_names)) {
                                                                                        echo esc_html(implode(', ', $category_names));
                                                                                    } else {
                                                                                        esc_html_e( 'No categories was selected', 'product-discount-manager' );
                                                                                    }
                                                                                ?>
                                                                                </h6>
                            
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <h6><?php esc_html_e( 'Product Group :', 'product-discount-manager' ); ?></h6>
                                                                            </td>
                                                                            <td>
                                                                            <h6>
                                                                                <?php 

                                                                                    if( !empty($productGroupId)){

                                                                                        $gNameTable = "{$wpdb->prefix}spark_group_name_table";

                                                                                        $groupQuery = $wpdb->get_results($wpdb->prepare("SELECT * FROM %s WHERE id = %d", $gNameTable, $productGroupId ));

                                                                                        foreach ($groupQuery as  $value) {
                                                                                            echo '<span class="">'.esc_html($value->group_name).'</span>';
                                                                                        }
                                                                                    }else {
                                                                                        esc_html_e( 'No Group Was Selected', 'product-discount-manager' );
                                                                                    }
                                                                                ?>
                                                                            </h6>
                                                                            </td>
                                                                        </tr>
                                                                        <?php if( is_array($simpleSkus) ): ?>
                                                                        <tr>
                                                                            <td>
                                                                                <h6><?php esc_html_e( 'Simple SKU :', 'product-discount-manager' ); ?></h6>
                                                                            </td>
                                                                            <td>
                                                                                <?php 
                                                                                    if($simpleSkus) {
                                                                                        echo implode(', ', $simpleSkus);
                                                                                    }
                                                                                ?>
                                                                            </td>
                                                                        </tr>
                                                                        <?php endif; ?>

                                                                        <?php if( is_array($variableSkus) ): ?>
                                                                        <tr>
                                                                            <td>
                                                                                <h6><?php esc_html_e( 'Variable SKU :', 'product-discount-manager' ); ?></h6>
                                                                            </td>
                                                                            <td>
                                                                                <?php 
                                                                                    if ($variableSkus) {
                                                                                        echo esc_html(implode(', ', $variableSkus));
                                                                                    }
                                                                                ?>
                                                                            </td>
                                                                        </tr>
                                                                        <?php endif; ?>
                                                                        <tr>
                                                                            <td>
                                                                                <h6><?php esc_html_e( 'Discount Type :', 'product-discount-manager' ); ?></h6>
                                                                            </td>
                                                                            <td>
                                                                                <h5>
                                                                                    <?php 
                                                                                        $dType = '';
                                                                                        if($discountType === "percentage"){
                                                                                            $dType = '%';
                                                                                            echo '<span class="badge bg-secondary">'.esc_html('Percentage', 'product-discount-manager').'</span>';
                                                                                        }elseif ($discountType === "fixed") {
                                                                                            $dType = '';
                                                                                            echo '<span class="badge bg-secondary">'.esc_html__('Fixed', 'product-discount-manager').'</span>';
                                                                                        }else{
                                                                                            esc_html_e("No Discount Type Selected", "wcpd-discount" );
                                                                                        }
                                                                                    ?>
                                                                                </h5>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <h6><?php esc_html_e( 'Discount Amount :', 'product-discount-manager' ); ?></h6>
                                                                            </td>
                                                                            <td>
                                                                                <h6>
                                                                                    <?php 
                                                                                        if( !empty($discountAmount)){
                                                                                            echo '<span">'.esc_html($discountAmount).''.esc_html($dType).'</span>';
                                                                                        }else {
                                                                                            esc_html_e( "No Discount", "wcpd-discount" );
                                                                                        }
                                                                                    ?>
                                                                                </h6>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <h6>
                                                                                    <?php esc_html_e( 'Discount Schedule :', 'product-discount-manager' ); ?>
                                                                                </h6>
                                                                            </td>
                                                                            <td>
                                                                                <h6>
                                                                                <?php 
                                                                                    if( !empty($discountScheduleDate)){
                                                                                        echo '<h6 class="fs-6">'.esc_html($discountScheduleDate->dateStart).' - '.esc_html($discountScheduleDate->dateEnd).', <span>'.esc_html($discountScheduleDate->timeStart).' - '.esc_html($discountScheduleDate->timeEnd).'</span></h6>';
                                                                                    }else {
                                                                                        esc_html_e("No Schedule", "wcpd-discount" );
                                                                                    }   
                                                                                ?>
                                                                                </h6>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <h6><?php esc_html_e( 'Apply On :', 'product-discount-manager' ); ?>
                                                                                </h6>
                                                                            </td>
                                                                            <td>
                                                                                <h6 class="text-capitalize">
                                                                                    <?php 
                                                                                        if( $discountApply_on == "regular" ){
                                                                                                echo '<span">'.esc_html_e( 'regular', 'product-discount-manager' ).'</span>';
                                                                                            }
                                                                                            elseif($discountApply_on == "sale") {
                                                                                                echo '<span">'.esc_html__( 'sale', 'product-discount-manager' ).'</span>';
                                                                                            }
                                                                                            else{
                                                                                            echo esc_html__("No Data", 'product-discount-manager');
                                                                                        }
                                                                                    ?>
                                                                                </h6>
                                                                            </td>
                                                                        </tr>
     
                                                                    </tbody> <!-- ./Table Body -->
                                                                </table>

                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php 
                                                }
                                            ?>
                                        </div>
                                    </form>
                                </div> 
                                <!-- End: Discount Settings Data -->
                            </div>
                        </div>

                        <!-- Product Price Change Settings -->
                        <div class="tab-pane fade show pb-2" id="navPriceChange" role="tabpanel" aria-labelledby="nav-price-tab" tabindex="0">
                            <div class="row">
                                <div class="float-none my-4">            
                                    <h5 class="shadow-none p-3 mb-5 bg-light rounded d-flex align-items-center position-relative "><?php esc_html_e( 'Change Product Price', 'product-discount-manager' ); ?>
                                        <span class="successMsg text-success mx-4" style="display: block"></span>

                                        <span class="priceChangesModal">
                                            <a class=" text-decoration-none text-white text-bg-success py-2 px-2"  data-bs-toggle="modal" href="#showPriceChanges" role="button"><?php esc_html_e( 'Show Price Changes', 'product-discount-manager' ) ?></a>
                                        </span>
                                    </h5>
                                </div>
                            </div>

                            <div class="col">
                                <table class="table settingsTable changePriceSettings">

                                <?php 
                                    // Fetch category data from "wp_spark_product_price_change" Table.
                                    global $wpdb;
                                    $table_name = "{$wpdb->prefix}spark_product_price_change";
                                    // Check if the table exists
                                    $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

                                    // Check if the group_id already exists in the database
                                    $dataQuery = '';
                                    if ($table_exists) {
                                        $dataQuery = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}spark_product_price_change");
                                    }
                                    
                                    // Initialize 
                                    $changePriceBy = '';
                                    $change_productType = '';
                                    $dbPriceCategories = '';
                                    $price_changeType = '';
                                    $price_changeMethod = '';
                                    $price_changeAmount = '';

                                    if (is_array($dataQuery) && count($dataQuery) > 0) {
                                        foreach ($dataQuery as $key => $data) {
                                            // Assign Value 
                                            $dbPriceCategories = isset($data->changePriceCats) && $data->changePriceCats !== null ? json_decode($data->changePriceCats) : [];
                                            
                                            $changePriceBy = isset($data->changePriceBy) ? sanitize_text_field($data->changePriceBy) : '';
                                            $change_productType = isset($data->productType) ? sanitize_text_field($data->productType) : '';
                                            $price_changeType = isset($data->changeType) ? sanitize_text_field($data->changeType) : '';
                                            $price_changeMethod = isset($data->changeMethod) ? sanitize_text_field($data->changeMethod) : '';
                                            $price_changeAmount = isset($data->changeAmount) ? floatval($data->changeAmount) : 0;
                                        }
                                    }
                                ?>
                                    <!-- Table Body -->
                                    <tbody>
                                        <tr>
                                            <td>
                                                <h6><?php esc_html_e( 'Change Price By :', 'product-discount-manager' ); ?>
                                                </h6>
                                            </td>
                                            <td>
                                            <div class="btn-group" role="group" aria-label="Basic radio toggle button group changePriceBy">
                                                <input type="radio" class="btn-check" name="changeBy" id="changeByCat" autocomplete="off" value="category" <?php echo $changePriceBy !== "productType" && $changePriceBy !== "category" ? 'checked' : ''; ?> <?php echo $changePriceBy === "category" ? 'checked' : ''; ?>>
                                                <label class="btn btn-outline-secondary" for="changeByCat"><?php esc_html_e( 'Category', 'product-discount-manager' ); ?></label>

                                                <input type="radio" class="btn-check" name="changeBy" id="changeByProduct" autocomplete="off" value="productType" <?php echo $changePriceBy === "productType" ? 'checked' : ''; ?>>
                                                <label class="btn btn-outline-secondary" for="changeByProduct"><?php esc_html_e( 'Product Type', 'product-discount-manager' ); ?></label>
                                            </div>
                                            </td>
                                        </tr>
                                        <tr class="productType">
                                         
                                            <td>
                                                <h6><?php esc_html_e( 'Product Type :', 'product-discount-manager' ); ?></h6>
                                            </td>
                                            <td>
                                                <div class="btn-group productTypeGrp byProductTypeGrp" role="group"
                                                    aria-label="Basic radio toggle button group">
                                                    <input type="radio" class="btn-check btn-sm"
                                                        name="byProductType" value="simple" id="priceSimple" autocomplete="off" <?php echo $change_productType !== "simple" && $change_productType !== "variable" ? 'checked' : ''; ?> <?php echo $change_productType === "simple" ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-secondary"
                                                        for="priceSimple"><?php esc_html_e( 'Simple', 'product-discount-manager' ); ?></label>

                                                    <input type="radio" class="btn-check btn-sm"
                                                        name="byProductType" id="priceVariable"
                                                        autocomplete="off" value="variable" <?php echo $change_productType === "variable" ? 'checked' : ''; ?>>
                                                    <label class="btn btn-outline-secondary"
                                                        for="priceVariable"><?php esc_html_e( 'Variable', 'product-discount-manager' ); ?></label>
                                                </div>
                                                <p class="subtitle">
                                                    <?php esc_html_e( 'Choose a Product Type for Price Change', 'product-discount-manager' ); ?>
                                                </p>
                                            </td>
                                        </tr>
                                        <tr class="productCategory priceChangeProductCategory">
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
                                                    if ( ! empty( $wcpd_categories ) && ! is_wp_error( $wcpd_categories ) ) {

                                                        echo '<div id="wcpd_productCategory changePriceCatList" class="wcpd_product_category">';
                                                        foreach ( $wcpd_categories as $category ) {

                                                            $isCategoryChecked = $dbPriceCategories && in_array($category->term_id, $dbPriceCategories);

                                                            echo '<div class="form-check">
                                                                <input class="changePriceCat" name="changePriceCat" type="checkbox"
                                                                    value="' . esc_attr($category->term_id) . '" id="changePriceCat' . esc_attr($category->term_id) . '"' .
                                                                    ($isCategoryChecked ? ' checked' : '') . '>
                                                                <label class="form-check-label"
                                                                    for="changePriceCat' . esc_attr($category->term_id) . '">
                                                                    ' . esc_html($category->name) . '
                                                                    <span class="err_msg"></span>
                                                                </label>
                                                            </div>';    
                                                        }
                                                        echo '</div>';
                    
                                                    } else {
                                                        echo esc_html__('No product categories found.', 'product-discount-manager');
                                                    }
                                                ?>
                                                <p class="subtitle">
                                                    <?php esc_html_e( 'Choose Product Category to apply price change - ( 1 Category Limit in this Version ) ', 'product-discount-manager' ); ?>
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 17%">
                                                <h6><?php esc_html_e( 'Change Type :', 'product-discount-manager' ); ?>
                                                </h6>
                                            </td>
                                            <td>
                                                <div class="changeWrapper">
                                                    <select class="form-select orm-select-lg changeType" aria-label=".form-select-lg" name="changeType">
                                                        <option <?php echo $price_changeType !== "increase" || $price_changeType !== "decrease" ? 'selected' : ''; ?>><?php esc_html_e( 'Select An Option', 'product-discount-manager' ); ?></option>

                                                        <option value="increase" <?php echo $price_changeType === "increase" ? 'selected' : ''; ?>>
                                                            <?php esc_html_e( 'Increase +', 'product-discount-manager' ); ?>
                                                        </option>
                                                        <option value="decrease" <?php echo $price_changeType === "decrease" ? 'selected' : ''; ?> disabled>
                                                            <?php esc_html_e( 'Decrease -', 'product-discount-manager' ); ?>
                                                            <div class="wcpd_paid"><?php echo esc_html('Coming Soon'); ?></div>
                                                        </option>
                                                    </select>
                                                    <select class="form-select orm-select-lg priceType" aria-label=".form-select-lg" name="priceType">
                                                        <option  <?php echo $price_changeMethod !== "percentage" || $price_changeMethod !== "fixed" ? 'selected' : ''; ?>><?php esc_html_e( 'Select An Option', 'product-discount-manager' ); ?></option>
                                                        <option value="percentage" <?php echo $price_changeMethod === "percentage" ? 'selected' : ''; ?>>
                                                            <?php esc_html_e( 'Percentage %', 'product-discount-manager' ); ?>
                                                        </option>
                                                        <option value="fixed" <?php echo $price_changeMethod === "fixed" ? 'selected' : ''; ?>>
                                                            <?php esc_html_e( 'Fixed', 'product-discount-manager' ); ?>
                                                        </option>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <h6><?php esc_html_e( 'Amount :', 'product-discount-manager' ); ?></h6>
                                            </td>
                                            <td>
                                                <div class="input-group" style="width: 29%">
                                                    <input type="number" value="<?php echo esc_attr($price_changeAmount); ?>" class="form-control changeAmount" name="changeAmount" placeholder="Ex: 10">
                                                </div>
                                                <p class="subtitle">
                                                    <?php esc_html_e( 'Enter Change Amount', 'product-discount-manager' ); ?>
                                                </p>
                                            </td>
                                        </tr>

                                    </tbody> <!-- ./Table Body -->
                                </table>
                                <button type="button" class="btn btn-success savePrice_Settings"><?php esc_html_e('Save Price', 'product-discount-manager'); ?></button>
                            </div>
                        </div>

                    </div> <!-- End of Tab content -->
                    <!-- Show Price Changes Modal -->
                    <?php 

                        global $wpdb;
                        // Fetch Data from database
                        $table_name = "{$wpdb->prefix}spark_product_price_change";
                        // Check if the table exists
                        $table_exists = $wpdb->get_var( $wpdb->prepare("SHOW TABLES LIKE %s", $table_name ));
                        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}spark_product_price_change");

                    ?>
                    <!-- Modal -->
                    <div class="modal fade" id="showPriceChanges" tabindex="-1" aria-labelledby="priceChangeLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="priceChangeLabel"> <?php echo esc_html__( 'Price Changes List', 'product-discount-manager' ); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="priceChange_list my-2">
                                        <!-- Accordion -->
                                        <div class="accordion accordion-flush" id="pricingChangeAccordion">
                                            <?php 
                                            if( !empty($results)) :
                                                foreach ($results as $key => $value) :  

                                                    $changesCategories = isset($value->changePriceCats) ? json_decode($value->changePriceCats) : '';

                                                    $changePriceBy = isset($value->changePriceBy) ? sanitize_text_field($value->changePriceBy) : '';
                                                    $changesProductType = isset($value->productType) ? sanitize_text_field($value->productType) : '';
                                                    $changeType = isset($value->changeType) ? sanitize_text_field($value->changeType) : '';
                                                    $changeMethod = isset($value->changeMethod) ? sanitize_text_field($value->changeMethod) : '';
                                                    $changeAmount = isset($value->changeAmount) ? floatval($value->changeAmount) : 0.0;
                                                    
                                                    ?>

                                                    <div class="accordion-item priceChange_accordion" priceChangeId="<?php echo esc_attr($value->id); ?>">
                                                        <h2 class="accordion-header" id="flush-heading<?php echo esc_attr($value->id); ?>">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse<?php echo esc_attr($value->id); ?>" aria-expanded="false" aria-controls="flush-collapse<?php echo esc_attr($value->id); ?>">
                                                            <?php esc_html_e('Changes By :', 'product-discount-manager'); ?> <?php echo esc_html($value->changePriceBy); ?>

                                                        <span type="button" class="priceCloseBtn">&times;</span>
                                                        </button>
                                                        </h2>
                                                        <div id="flush-collapse<?php echo esc_attr($value->id); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr($value->id); ?>" data-bs-parent="#pricingChangeAccordion">
                                                            <div class="accordion-body">
                                                                <table class="table">
                                                                    <tbody>
                                                                        <tr>
                                                                            <th scope="row"><?php esc_html_e( 'Change Price By :', 'product-discount-manager' ); ?> </th>
                                                                            <td><?php echo esc_html($changePriceBy); ?></td>
                                                                        </tr>

                                                                        <?php if($changePriceBy === "category"): ?>
                                                                        <tr>
                                                                            <th scope="row"> <?php esc_html_e('Category Name :', 'product-discount-manager'); ?></th>
                                                                            <td>
                                                                                <?php 
                                                                                    $taxonomy = 'product_cat';
                                                                                    $category_names = array();

                                                                                    if ($changesCategories) {
                                                                                        foreach ($changesCategories as $category_id) {
                                                                                            $category = get_term_by('id', $category_id, $taxonomy);

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
                                                                                        echo esc_html($comma_separated_names);
                                                                                    }
                                                                                ?>
                                                                            </td>
                                                                        </tr>
                                                                        <?php endif; ?>

                                                                        <?php if($changePriceBy === "productType"): ?>
                                                                            <tr>
                                                                                <th scope="row"><?php esc_html_e('Product Type : ', 'product-discount-manager'); ?></th>
                                                                                <td><?php echo esc_html($changesProductType); ?></td>
                                                                            </tr>
                                                                        <?php endif; ?>
                                                                        <tr>
                                                                            <th scope="row"><?php esc_html_e('Change Method : ', 'product-discount-manager'); ?></th>
                                                                            <td><?php echo esc_html($changeMethod); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row"><?php esc_html_e('Change Type :  ', 'product-discount-manager'); ?></th>
                                                                            <td><?php echo esc_html($changeType); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row"><?php esc_html_e('Change Amount : ', 'product-discount-manager'); ?></th>
                                                                            <td><?php echo esc_html($changeAmount); ?></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php else : ?>
                                                <h4 class="text-center text-secondary">
                                                    <?php esc_html_e(' No Price Changes !!', 'product-discount-manager'); ?> 
                                                </h4>
                                            <?php  endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!--End Modal -->

                    <!-- Custom Alert Box  -->
                    <div class="modal fade" id="wcpd_customAlert" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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
                    
                </div>
                <!--/ End Custom Wrapper -->
            </div>
            <!--/ End WP Wrap -->
            <div class="clear"></div>
        </div><!-- wpbody-content -->
        <div class="clear"></div>
    </div>
    <!--End Main -->
<?php

}





















