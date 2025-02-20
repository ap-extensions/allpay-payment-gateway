<?php
/**
 * Plugin Name: Allpay payment gateway
 * Plugin URI: https://www.allpay.co.il/integrations/wordpress
 * Description: Allpay Payment Gateway for WooCommerce to accepts Visa, Mastercard, Diners, AmEx and other brands.
 * Author: Allpay
 * Author URI: https://allpay.co.il
 * Version: 1.1.0
 * Text Domain: allpay-payment-gateway
 * Domain Path: /languages
 * Tested up to: 6.6.2
 * WC tested up to: 9.3.3
 * WC requires at least: 3.0
 *
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Include our Gateway Class and register Payment Gateway with WooCommerce

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'plugins_loaded', 'allpay_init', 0 ); 

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;

function allpay_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

	include_once( 'classes/class-allpay.php' );
	include_once( 'classes/class-allpay-blocks-integration.php' );
	
	// plugin translation
	load_plugin_textdomain( 'allpay-payment-gateway', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );  

	function add_allpay_gateway( $methods ) {
		$methods[] = 'WC_Allpay';
		return $methods;
	}
	add_filter( 'woocommerce_payment_gateways', 'add_allpay_gateway' );

	// blocks
	if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry' ) && class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		add_action( 'woocommerce_blocks_payment_method_type_registration', function ( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
			$payment_method_registry->register( new AllpayBlocksIntegration() );
		} );
	}		
}

// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'allpay_action_links' );
function allpay_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=allpay-payment-gateway' ) . '">' . __( 'Settings', 'allpay-payment-gateway' ) . '</a>',
	);

	// Merge our new link with the default ones
	return array_merge( $plugin_links, $links );	
}

// поддержка HPOS
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true ); //__FILE__ должен вести в основному файлу вашего плагина
	}
} );




