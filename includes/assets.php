<?php
/*
* @Pakage product discount manager.
*/

if( !defined( 'ABSPATH' ) ){
    exit; // Exit if directly access.
}
 // Check if function already exist.
if( ! function_exists('wcpdm_css_js_assets') ){

    function wcpdm_css_js_assets() {

        wp_register_style( 'wcpd-bootsrap-css', WCPDM_DIR_URI .'assets/css/bootstrap.min.css');
        wp_enqueue_style( 'wcpd-choices-css', WCPDM_DIR_URI .'assets/css/choices.min.css');
        wp_enqueue_style('wcpd-style', WCPDM_DIR_URI .'assets/css/wcpd-style.css');

        // Date Picker UI
        wp_enqueue_style('wcpd-datatables-css', WCPDM_DIR_URI.'assets/css/datatables.min.css');
        wp_enqueue_style('wcpd-select2-css', WCPDM_DIR_URI. '/assets/css/select2.min.css');
        wp_enqueue_style('wcpd-datepicker-css', WCPDM_DIR_URI. 'assets/css/bootstrap-datepicker.css');
        wp_enqueue_style('wcpd-timepicker-css', WCPDM_DIR_URI. 'assets/css/jquery.timepicker.css');

        // Register the script
        wp_register_script( 'wcpd-bootsrap-js', WCPDM_DIR_URI .'assets/js/bootstrap.min.js', array('jquery'), 'v5.3.0', true );
        wp_register_script( 'wcpd-datatables-js', WCPDM_DIR_URI .'assets/js/datatables.min.js', array('jquery'), 'v1.13.5', true );

        wp_register_script( 'wcpd-select2-js', WCPDM_DIR_URI .'assets/js/select2.min.js', array(), '1.0.0', true );
        
        wp_enqueue_script( 'wcpd_bootstrap-datepicker_js', WCPDM_DIR_URI .'/assets/js/bootstrap-datepicker.js', array('jquery'), '1.0.0', true );
        wp_enqueue_script( 'wcpd-timepicker-js', WCPDM_DIR_URI .'assets/js/jquery.timepicker.js', array('jquery'), '1.0.0', true );
        wp_enqueue_script( 'wcpd-datepair-js', WCPDM_DIR_URI .'assets/js/datepair.min.js', array('jquery'), '1.0.0', true );
        wp_enqueue_script( 'wcpd-jqueryDatepairs-js', WCPDM_DIR_URI .'assets/js/jquery.datepair.min.js', array('jquery'), '1.0.0', true );


        wp_register_script( 'wcpd-custom-js',  WCPDM_DIR_URI .'assets/js/wcpd-script.js', array('jquery'), '1.0.0', true );
        wp_register_script( 'wcpd-security',  WCPDM_DIR_URI .'assets/js/security.js', array('jquery'), '1.0.0', true );
        
        $args = array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'current_userId' => get_current_user_id(),
            'wcpd_nonce' => wp_create_nonce( 'wcpd_woo_nonce' )
        );
        wp_localize_script( 'wcpd-custom-js', 'wcpdObj', $args );

        // License Validation
        $licenseArg = array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'current_userId' => get_current_user_id(),
            'license_nonce' => wp_create_nonce( 'wcpd_license_nonce' )
        );
        wp_localize_script( 'wcpd-custom-js', 'licenseObj', $licenseArg );

        // License Deactivation
        $licenseDeArg = array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'current_userId' => get_current_user_id(),
            'license_nonce' => wp_create_nonce( 'wcpd_licenseDe_nonce' )
        );
        wp_localize_script( 'wcpd-custom-js', 'licenseDeObj', $licenseDeArg );

        wp_enqueue_style('wcpd-bootsrap-css');
        wp_enqueue_style('wcpd-style');
        // Enqueue the script
        wp_enqueue_script( 'wcpd-datatables-js' );
        wp_enqueue_script( 'wcpd-bootsrap-js' );
        wp_enqueue_script( 'wcpd-select2-js' );
        wp_enqueue_script( 'wcpd-custom-js' );
        wp_enqueue_script( 'wcpd-security' );
    }


}

