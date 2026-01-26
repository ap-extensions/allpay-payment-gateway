<?php
/* Allpay Payment Gateway Class */

if ( ! defined( 'ABSPATH' ) ) exit;

class WC_Allpay extends WC_Payment_Gateway {

    public $enabled;
    public $title;
    public $description;

    public $api_login;
    public $api_key;

    public $installment_n;
    public $installment_min_order;

    public $display_tehudat_mode;

    public $currency_payment;

    public $debug;

	function __construct() {
		$this->id = "allpay-payment-gateway";
		$this->method_title = __( 'Credit card payment via Allpay', 'allpay-payment-gateway' );
		$this->method_description = __( 'Allpay payment gateway plug-in for WooCommerce', 'allpay-payment-gateway' );
		$this->title = __( 'Credit card payment via Allpay', 'allpay-payment-gateway' );
		$this->description = __( 'Allpay payment gateway plug-in for WooCommerce', 'allpay-payment-gateway' );
		$this->icon = null;
		$this->has_fields = true;

		$this->init_form_fields();
		$this->init_settings();
		
        $this->enabled               = $this->get_option( 'enabled' );
        $this->title                 = $this->get_option( 'title' );
        $this->description           = $this->get_option( 'description' );

        $this->api_login             = $this->get_option( 'api_login' );
        $this->api_key               = $this->get_option( 'api_key' );

        $this->installment_n         = (int) $this->get_option( 'installment_n', 0 );
        $this->installment_min_order = (float) $this->get_option( 'installment_min_order', 1000 );

        $this->display_tehudat_mode  = (int) $this->get_option( 'display_tehudat_mode', 2 );

        $this->currency_payment      = $this->get_option( 'currency_payment', 'ILS' );

		add_action( 'woocommerce_api_allpay' , array($this, 'webhook') );
		
		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}		
	} 

	public function webhook() {  

        $inputJSON = file_get_contents('php://input'); 
        $input = json_decode($inputJSON, true); 
        if(is_array($input)) { 
            foreach($input as $k => $v) { 
                $_REQUEST[$k] = $v; 
            } 
        }

		$chunks = [];
		$wh_params = ['name', 'items', 'amount', 'inst', 'order_id', 'currency', 'status', 'client_name', 
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
		} else {
			echo 'Notification error: ' . json_encode($_REQUEST) . ' / ' . $sign . ' / ' . $this->debug;
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
				'description' => __( 'Payment method name shown to customers at checkout', 'allpay-payment-gateway' ),
				'desc_tip'	=> __( 'Recommended: Credit card payment via Allpay', 'allpay-payment-gateway' ),
				'default'	=> __( 'Credit card payment via Allpay', 'allpay-payment-gateway' ),
			),
			'description' => array(
				'title'		=> __( 'Description', 'allpay-payment-gateway' ),
				'type'		=> 'textarea',
				'description' => __( 'Payment method description shown to customers at checkout', 'allpay-payment-gateway' ),
				'desc_tip'	=> __( 'Recommended: Pay securely with a credit/debit card, Apple Pay, or Bit', 'allpay-payment-gateway' ),
				'default'	=> __( 'Pay securely with a credit/debit card, Apple Pay, or Bit.', 'allpay-payment-gateway' ),
				'css'		=> 'max-width:350px;'
			),
			'api_login' => array(
				'title'		=> __( 'API login', 'allpay-payment-gateway' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Crate API Login in your Allpay account: Settings -> Integrations -> WordPress', 'allpay-payment-gateway' ),
			),
			'api_key' => array(
				'title'		=> __( 'API key', 'allpay-payment-gateway' ),
				'type'		=> 'password',
				'desc_tip'	=> __( 'Crate API key in your Allpay account: Settings -> Integrations -> WordPress', 'allpay-payment-gateway' ),
			),
			'installment_n' => array(
				'title' => __( 'Installment max payments', 'allpay-payment-gateway' ),
				'type' => 'number',
				'description' => __( 'Maximum number of installment payments. Up to 12, zero to disable', 'allpay-payment-gateway' ),
				'desc_tip'	=> __( 'Allows client to choose number of payments. Valid for credit cards only (no debit cards)', 'allpay-payment-gateway' ),
				'default' => 0
			),
			'installment_min_order' => array(
				'title' => __( 'Installment min order amount', 'allpay-payment-gateway' ),
				'type' => 'number',
				'description' => __( 'Minimum order amount for installments. Zero for orders of any amount', 'allpay-payment-gateway' ),
				'desc_tip'	=> __( 'Enables installment option when payment amount equals or above this value', 'allpay-payment-gateway' ),
				'default' => 1000
			),
            'currency_payment' => array(
                'title'       => __( 'Payment currency', 'allpay-payment-gateway' ),
                'type'        => 'select',
                'description' => __( 'Before selecting USD or EUR, make sure these currencies are enabled on your Allpay account', 'allpay-payment-gateway' ),
                'desc_tip'	=> __( 'The currency in which the payment will be processed. Any price shown in another currency will be converted to this currency', 'allpay-payment-gateway' ),
                'default'     => 'ILS',
                'options'     => array(
                    'ILS' => __( 'Israeli Shekel (ILS)', 'allpay-payment-gateway' ),
                    'USD' => __( 'US Dollar (USD)', 'allpay-payment-gateway' ),
                    'EUR' => __( 'Euro (EUR)', 'allpay-payment-gateway' ),
                ),
            ),
            'display_tehudat_mode' => array(
                'title'       => __( 'Teudat zeut field', 'allpay-payment-gateway' ),
                'type'        => 'select',
                'description' => __( 'Request Teudat Zehut (Israeli ID) on the payment page', 'allpay-payment-gateway' ),
                'desc_tip'	=> __( 'When customers should be asked to provide their Teudat Zehut number', 'allpay-payment-gateway' ),
                'default'     => 2,
                'options'     => array(
                    1 => __( 'Only for customers from Israel', 'allpay-payment-gateway' ),
                    2 => __( 'For all customers', 'allpay-payment-gateway' ),
                    0 => __( 'Do not request', 'allpay-payment-gateway' ),
                ),
            )
		);		
	}
	
	public function process_payment( $order_id ) {

		$customer_order = wc_get_order( $order_id );

		$environment_url = 'https://allpay.to/app/?show=getpayment&mode=api10';
		
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
			"currency"				=> ($this->currency_payment ?? 'ILS'),
            "currency_display"      => get_woocommerce_currency(),
			"lang"					=> $this->get_lang(),
			"order_id"        		=> str_replace( "#", "", $customer_order->get_order_number() ),
			"client_name"			=> $full_name,
			"client_phone"			=> $customer_order->get_billing_phone(),
			"client_email"			=> $customer_order->get_billing_email(),
			"notifications_url"		=> get_home_url() . '/?wc-api=allpay',
			"success_url"			=> $customer_order->get_checkout_order_received_url(),
			"backlink_url"			=> wc_get_checkout_url()
		);

		if($this->installment_n > 0 && ((int)$this->installment_min_order == 0 || $this->installment_min_order <= $customer_order->get_total())) {
			$request['inst'] = (int)$this->installment_n;
			if($this->installment_fixed == 'yes') {
				$request['inst_fixed'] = 1;				
			}
		}

        if($this->display_tehudat_mode == 0 || $this->display_tehudat_mode == 1 && $customer_order->get_billing_country() != 'IL') {
            $request['client_tehudat'] = '000000000';
        }

        $tax_included = wc_tax_enabled() && wc_prices_include_tax() && $customer_order->get_total_tax() > 0;

        // Items
        $items = [];

        foreach ($customer_order->get_items() as $item_id => $item) {
            $qty = (int) $item->get_quantity();

            $line_total     = (float) $item->get_total();     
            $line_total_tax = (float) $item->get_total_tax();   

            $unit_price_gross = ($qty > 0) ? (($line_total + $line_total_tax) / $qty) : 0;

            $items[] = [
                'name'  => $item->get_name(),
                'price' => $unit_price_gross,
                'qty'   => $qty,
                'vat'   => ($line_total_tax > 0 ? 1 : 0),
            ];
        }

        foreach ($customer_order->get_fees() as $fee_id => $fee) {
            $fee_total     = (float) $fee->get_total();       
            $fee_total_tax = (float) $fee->get_total_tax();   

            $items[] = [
                'name'  => $fee->get_name(),
                'price' => ($fee_total + $fee_total_tax),     
                'qty'   => 1,
                'vat'   => ($fee_total_tax > 0 ? 1 : 0),
            ]; 
        }

        $shipping_total     = (float) $customer_order->get_shipping_total(); 
        $shipping_total_tax = (float) $customer_order->get_shipping_tax();  

        if ($shipping_total > 0 || $shipping_total_tax > 0) {
            $items[] = [
                'name'  => $customer_order->get_shipping_method(),
                'price' => ($shipping_total + $shipping_total_tax),         
                'qty'   => 1,
                'vat'   => ($shipping_total_tax > 0 ? 1 : 0),
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
                            if ($val !== null && $val !== '') {
                                $chunks[] = $val; 
                            }	 
                        }
                    } elseif ($item !== null && $item !== '') {
                        $chunks[] = $item; 
                    }	   
                }
            } elseif ($v !== null && $v !== '') {
                $chunks[] = $v; 
            }	                
        }
        $this->debug = implode(':', $chunks);
        $signature = implode(':', $chunks) . ':' . $this->api_key;
        $signature = hash('sha256', $signature);
        return $signature;  
    }  

	public function get_lang() {
        $lang = substr( get_locale(), 0, 2 );
        return $lang;
    }
}