<?php
/* Allpay Payment Gateway Class */

if ( ! defined( 'ABSPATH' ) ) exit;

class WC_Allpay extends WC_Payment_Gateway {

	function __construct() {
		$this->id = "allpay-payment-gateway";
		$this->method_title = __( 'Bank cards payments via Allpay', 'allpay-payment-gateway' );
		$this->method_description = __( 'Allpay Payment Gateway Plug-in for WooCommerce', 'allpay-payment-gateway' );
		$this->title = __( 'Bank cards payments via Allpay', 'allpay-payment-gateway' );
		$this->description = __( 'Allpay Payment Gateway Plug-in for WooCommerce', 'allpay-payment-gateway' );
		$this->icon = null;
		$this->has_fields = true;

		$this->init_form_fields();
		$this->init_settings();
		
		foreach ( $this->settings as $setting_key => $value ) {
			$this->$setting_key = $value;
		}

		add_action( 'woocommerce_api_allpay' , array($this, 'webhook') );
		
		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}		
	} 

	public function webhook() {  

		$chunks = [];
		$wh_params = ['name', 'items', 'amount', 'order_id', 'currency', 'status', 'client_name', 
            'client_email', 'client_tehudat', 'client_phone', 'card_mask', 'card_brand', 'foreign_card', 
            'add_field_1', 'add_field_2', 'receipt'];
		foreach($wh_params as $k) {
			if(isset($_REQUEST[$k])) {
				$chunks[$k] = $_REQUEST[$k];
			}
		} 
		$sign = $this->get_signature($chunks); 

		$order_id = $_REQUEST['order_id'];
		$status = (int)$_REQUEST['status'];
		if($order_id > 0 && $status == 1 && $sign == $_REQUEST['sign']) {
			$customer_order = wc_get_order($order_id);
			$transaction_id = (int)$_REQUEST['order_id'];
			$customer_order->payment_complete($transaction_id);
			wc_reduce_stock_levels($customer_order);	
		} else {
			echo 'Notification error: ' . json_encode($_REQUEST);
		}
		exit();
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'		=> __( 'Enable / Disable', 'allpay-payment-gateway' ),
				'label'		=> __( 'Enable this payment gateway', 'allpay-payment-gateway' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
			'title' => array(
				'title'		=> __( 'Title', 'allpay-payment-gateway' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Payment title the customer will see during the checkout process.', 'allpay-payment-gateway' ),
				'default'	=> __( 'Credit card', 'allpay-payment-gateway' ),
			),
			'description' => array(
				'title'		=> __( 'Description', 'allpay-payment-gateway' ),
				'type'		=> 'textarea',
				'desc_tip'	=> __( 'Payment description the customer will see during the checkout process.', 'allpay-payment-gateway' ),
				'default'	=> __( 'Pay securely using your credit card.', 'allpay-payment-gateway' ),
				'css'		=> 'max-width:350px;'
			),
			'api_login' => array(
				'title'		=> __( 'API login', 'allpay-payment-gateway' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Allpay API Login', 'allpay-payment-gateway' ),
			),
			'api_key' => array(
				'title'		=> __( 'API key', 'allpay-payment-gateway' ),
				'type'		=> 'password',
				'desc_tip'	=> __( 'Allpay API Key', 'allpay-payment-gateway' ),
			),
			'installment_n' => array(
				'title' => __( 'Installment max payments', 'allpay-payment-gateway' ),
				'type' => 'number',
				'description' => __( 'Maximum number of installment payments. Up to 12, zero to disable.', 'allpay-payment-gateway' ),
				'desc_tip'	=> __( 'Allows client to choose number of payments. Valid for credit cards only (no debit cards)', 'allpay-payment-gateway' ),
				'default' => 0
			),
			'installment_min_order' => array(
				'title' => __( 'Installment min order amount', 'allpay-payment-gateway' ),
				'type' => 'number',
				'description' => __( 'Minimum order amount for installments. Zero for orders of any amount.', 'allpay-payment-gateway' ),
				'desc_tip'	=> __( 'Enables installment option when payment amount equals or above this value', 'allpay-payment-gateway' ),
				'default' => 1000
			)
		);		
	}
	
	public function process_payment( $order_id ) {

		$customer_order = wc_get_order( $order_id );

		$environment_url = 'https://allpay.to/app/?show=getpayment&mode=api8';
		
		$user_id = get_current_user_id();

		$first_name = $customer_order->get_shipping_first_name();
		if (trim($first_name) == '') {
			$first_name = $customer_order->get_billing_first_name();
		}
		if (trim($first_name) == '' && $user_id) { 
			$first_name = get_user_meta($user_id, 'first_name', true);
		}
		
		$last_name = $customer_order->get_shipping_last_name();
		if (empty($last_name)) {
			$last_name = $customer_order->get_billing_last_name();
		}
		if (trim($last_name) == '' && $user_id) { 
			$last_name = get_user_meta($user_id, 'last_name', true);
		}
		
		$full_name = trim($first_name . ' ' . $last_name);

		$request = array(
			"login"           		=> $this->api_login,
			"amount"             	=> $customer_order->get_total(),
			"currency"				=> get_woocommerce_currency(),
			"lang"					=> $this->get_lang(),
			"order_id"        		=> str_replace( "#", "", $customer_order->get_order_number() ),
			"client_name"			=> $full_name,
			"client_phone"			=> $customer_order->get_billing_phone(),
			"client_email"			=> $customer_order->get_billing_email(),
			"notifications_url"		=> get_home_url() . '/?wc-api=allpay',
			"success_url"			=> $customer_order->get_checkout_order_received_url(),
			"backlink_url"			=> home_url()
		);

		if($this->installment_n > 0 && ((int)$this->installment_min_order == 0 || $this->installment_min_order <= $customer_order->get_total())) {
			$request['tash'] = (int)$this->installment_n;
			if($this->installment_first_payment > 0) {
				$request['tash_first_payment'] = (float)$this->installment_first_payment;
			}
			if($this->installment_fixed == 'yes') {
				$request['tash_fixed'] = 1;				
			}
		}

        $tax_included = wc_tax_enabled() && wc_prices_include_tax() && $customer_order->get_total_tax() > 0;

		// Items
		$items = [];
		foreach ($customer_order->get_items() as $item_id => $item) {
			$quantity = $item->get_quantity();
			$price = ($quantity > 0) ? ( $item->get_total() + $item->get_total_tax() ) / $quantity : 0;

			$items[] = [
				'name' => $item->get_name(),
				'price' => $price, 
				'qty' => $quantity,
                'vat' => ($tax_included ? 1 : 0)
			];
		}
		foreach ($customer_order->get_fees() as $fee_id => $fee) {
			$items[] = [
				'name' => $fee->get_name(),
				'price' => $fee->get_total(),
				'qty' => 1,
                'vat' => ($tax_included ? 1 : 0)
			];
		}
		if ($shipping_total = $customer_order->get_shipping_total()) {
			$items[] = [
				'name' => $customer_order->get_shipping_method(),
				'price' => $shipping_total,
				'qty' => 1,
                'vat' => ($tax_included ? 1 : 0)
			];
		}
		$request['items'] = $items;

		$request['sign'] = $this->get_signature($request);
	
		$response = wp_remote_post( $environment_url, array(
			'method'    => 'POST',
			'body'      => http_build_query( $request ),
			'timeout'   => 90,
			'sslverify' => false,
		) );
		if ( is_wp_error( $response ) ) {
			throw new Exception( __( 'We are currently experiencing problems trying to connect to this payment gateway. Sorry for the inconvenience.', 'allpay-payment-gateway' ) );
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset($response['error_msg'])) {
			throw new Exception( $response['error_msg']);
		}
		
		if ( isset($response['payment_url']) ) {
			return array(
				'result'   => 'success',
				'redirect' => $response['payment_url'],
			);
		} else {
			throw new Exception( json_encode($response));
		}
		
		throw new Exception( __( 'Unknown error', 'allpay-payment-gateway' ) );
	}

    public function get_signature($params) {    
        ksort($params);
        $chunks = [];

        foreach ($params as $k => $v) { 
            if (is_array($v)) {
                ksort($v);

                foreach ($v as $subkey => $item) {
                    if (is_array($item)) {
                        ksort($item);
                        foreach($item as $name => $val) {
                            if ($val !== '') {
                                $chunks[] = $val; 
                            }	 
                        }
                    } elseif ($item !== '') {
                        $chunks[] = $item; 
                    }	   
                }
            } elseif ($v !== '') {
                $chunks[] = $v; 
            }	                
        }
        
        $signature = implode(':', $chunks) . ':' . $this->api_key;
        $signature = hash('sha256', $signature);
        return $signature;  
    }  

	public function get_lang() {
		$lang = get_locale();
		if ( strpos($lang, 'en') === 0 ) {
			return 'en';
		} else if ( strpos($lang, 'de') === 0 ) {
			return 'de';
		} else if ( strpos($lang, 'es') === 0 ) {
			return 'es';
		} else if ( strpos($lang, 'fr') === 0 ) {
			return 'fr';
		} else if ( strpos($lang, 'ru') === 0 ) {
			return 'ru';
		} else {
			return 'pl';
		}
	}
}