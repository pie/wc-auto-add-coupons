<?php
/*
Plugin Name: WooCommerce: Automatically apply coupons
Plugin URI:  #
Description: Allows the automatic application of coupon codes passed into the checkout URL
Version:     0.1
Author:      The team at PIE
Author URI:  #
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wc-auto-apply-coupons
*/

namespace PIE\WCAutoApplyCoupons;

/**
 * Set session variable on page load if the query string has coupon_code variable.
 *
 * @return void
 */
function add_custom_coupon_to_session() {
    if( isset( $_GET[ 'coupon_code' ] ) ) {
        // Ensure that customer session is started
        if( !WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie(true);
        }

        // Check and register coupon code in a custom session variable
        $coupon_code = WC()->session->get( 'coupon_code' );
        if( empty( $coupon_code ) ) {
            $coupon_code = esc_attr( $_GET[ 'coupon_code' ] );
            WC()->session->set( 'coupon_code', $coupon_code ); // Set the coupon code in session
        }
    }
}
add_action( 'woocommerce_init', __NAMESPACE__ . '\add_custom_coupon_to_session' );

/**
 * Apply Coupon code to the cart if the session has coupon_code variable.
 *
 * @return void
 */
function apply_discount_to_cart() {
    if ( is_admin() ) {
        return;
    }
    // Set coupon code
    $coupon_code = WC()->session->get( 'coupon_code' );
    if ( ! empty( $coupon_code ) && ! WC()->cart->has_discount( $coupon_code ) ){
        WC()->cart->add_discount( $coupon_code ); // apply the coupon discount
        WC()->session->__unset( 'coupon_code' ); // remove coupon code from session
    }
}

add_action( 'woocommerce_checkout_init', __NAMESPACE__ . '\apply_discount_to_cart' );
