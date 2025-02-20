<?php
/* Allpay method blocks integration */

if ( ! defined( 'ABSPATH' ) ) exit;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;

final class AllpayBlocksIntegration extends AbstractPaymentMethodType {

	protected $name = 'allpay-payment-gateway';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_allpay-payment-gateway_settings', [] );
	}

	public function is_active() {
		return filter_var( $this->get_setting( 'enabled', false ), FILTER_VALIDATE_BOOLEAN );
	}

	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'supports'    => $this->get_supported_features(),
		];
	}

	public function get_payment_method_script_handles() {
		$hamdle = 'allpay-block-js';
		wp_register_script(
			$hamdle,
			plugins_url( 'js/allpay-payment-block.js', plugin_dir_path( __FILE__ )),
			[ 'wc-blocks-registry', 'react', 'wp-i18n', 'wp-html-entities' ], 
			$this->get_ver(), 
			true 
		);
		return [ $hamdle ];
	}

	private function get_ver() {
		$plugin_data = get_file_data(dirname(__FILE__, 2) . '/allpay-gateway.php', [ 'Version' => 'Version' ] );
		return $plugin_data['Version'];
	}
}
