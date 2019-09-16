<?php
/* ----------------------------------------------------------------------
   $Id: paypal.php,v 1.1 2007/06/07 17:30:51 r23 Exp $

   MyOOS [Shopsystem]
   https://www.oos-shop.de

   Copyright (c) 2003 - 2019 by the MyOOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   File: paypal.php,v 1.39 2003/01/29 19:57:15 hpdl 
   ----------------------------------------------------------------------
   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2003 osCommerce
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */


if (defined( 'IS_ADMIN_FLAG' ) ) {
	$composerAutoload = MYOOS_INCLUDE_PATH . '/vendor/autoload.php';
	require $composerAutoload;
}

use PayPal\Api\Amount; 
use PayPal\Api\Details; 
use PayPal\Api\Item; 
use PayPal\Api\ItemList; 
use PayPal\Api\Payer; 
use PayPal\Api\Payment; 
use PayPal\Api\PaymentExecution; 
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class paypal_api {
    var $code, $title, $description, $form_action_url, $enabled = FALSE;

// class constructor
    public function __construct() {
		global $oOrder, $aLang;

		$this->code = 'paypal_api';
		$this->title = $aLang['module_payment_paypal_api_text_title'];
		$this->description = $aLang['module_payment_paypal_api_text_description'];
		$this->enabled = (defined('MODULE_PAYMENT_PAYPAL_API_STATUS') && (MODULE_PAYMENT_PAYPAL_API_STATUS == 'TRUE') ? TRUE : FALSE);
		$this->sort_order = (defined('MODULE_PAYMENT_PAYPAL_API_SORT_ORDER') ? MODULE_PAYMENT_PAYPAL_API_SORT_ORDER : NULL);

		if ((defined('MODULE_PAYMENT_PAYPAL_API_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_PAYPAL_API_ORDER_STATUS_ID > 0)) {
			$this->order_status = MODULE_PAYMENT_PAYPAL_API_ORDER_STATUS_ID;
		}

		if ( $this->enabled === TRUE ) {
			if ( isset($oOrder) && is_object($oOrder) ) {
				$this->update_status();
			}
		}
		$this->form_action_url = '';

    }

// class methods
    function update_status() {
		global $oOrder;

		if ( ($this->enabled == TRUE) && ((int)MODULE_PAYMENT_PAYPAL_API_ZONE > 0) ) {
			$check_flag = FALSE;

			// Get database information
			$dbconn =& oosDBGetConn();
			$oostable =& oosDBGetTables();

			$zones_to_geo_zonestable = $oostable['zones_to_geo_zones'];
			$check_result = $dbconn->Execute("SELECT zone_id FROM $zones_to_geo_zonestable WHERE geo_zone_id = '" . MODULE_PAYMENT_PAYPAL_API_ZONE . "' AND zone_country_id = '" . $oOrder->billing['country']['id'] . "' ORDER BY zone_id");
			while ($check = $check_result->fields) {
				if ($check['zone_id'] < 1) {
					$check_flag = TRUE;
					break;
				} elseif ($check['zone_id'] == $oOrder->billing['zone_id']) {
					$check_flag = TRUE;
					break;
				}

				// Move that ADOdb pointer!
				$check_result->MoveNext();
			}

			if ($check_flag == FALSE) {
				$this->enabled = FALSE;
			}
		}
	}

    function javascript_validation() {
		return FALSE;
    }

    function selection() {
		return array('id' => $this->code,
                   'module' => $this->title);
    }

    function pre_confirmation_check() {
		return FALSE;
    }

    function confirmation() {
		return FALSE;
    }

    function process_button() {
		global $oOrder, $oCurrencies, $aUser;

		$my_currency = $_SESSION['currency'];

		$payer = new Payer();
		$payer->setPaymentMethod("paypal");

		$itemList = new ItemList();
		$nOrder = count($oOrder->products);
		for ($i=0, $n=$nOrder; $i<$n; $i++) {
			if ($aUser['price_with_tax'] == 1) {
				$final_price = number_format(oos_round(oos_add_tax($oOrder->products[$i]['final_price'], $oOrder->products[$i]['tax']),2), 2, '.', '');
			} else {
				$final_price = $oOrder->products[$i]['final_price'];
			}

			$item = new Item();
			$item->setName($oOrder->products[$i]['name'])
				->setCurrency($my_currency)
				->setQuantity($oOrder->products[$i]['qty'])
				->setPrice($final_price);				

			// add item to list
			$itemList->addItem($item);
		}

		$shipping = $oOrder->info['shipping_cost'];
		$shipping = number_format($shipping, 2, '.', '');

		if ($aUser['price_with_tax'] == 1) {
			$subtotal = $oOrder->info['total'] - $oOrder->info['shipping_cost'];
		} else {
			$subtotal = $oOrder->info['total'] - $oOrder->info['shipping_cost'] - $oOrder->info['tax'];
		}

		$subtotal = number_format($subtotal, 2, '.', '');
		$tax = number_format($oOrder->info['tax'], 2, '.', '');
		$total = number_format($oOrder->info['total'], 2, '.', '');

		// ### Additional payment details
		// Use this optional field to set additional
		// payment information such as tax, shipping
		// charges etc.
		$details = new Details();
		$details->setShipping($shipping)
				->setSubtotal($subtotal);

		if ($aUser['price_with_tax'] != 1) {	
			$details->setTax( $tax );
		}
		
		// ### Amount
		// Lets you specify a payment amount.
		// You can also specify additional details
		// such as shipping, tax.
		$amount = new Amount();
		$amount->setCurrency($my_currency)
			->setTotal($total)
			->setDetails($details);

		// ### Transaction
		// A transaction defines the contract of a
		// payment - what is the payment for and who
		// is fulfilling it. 
		$transaction = new Transaction();
		$transaction->setAmount($amount)
					->setItemList($itemList)
					->setDescription("Payment description")
					->setInvoiceNumber(uniqid());

		// ### Redirect urls
		// Set the urls that the buyer must be redirected to after 
		// payment approval/ cancellation.
		$aContents = oos_get_content();
		$sReturnUrl .= oos_href_link($aContents['checkout_process']);
		$sCancelUrl .= oos_href_link($aContents['checkout_payment']);

		// ### Payment
		// A Payment Resource; create one using
		// the above types and intent set to 'sale'
		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl($sReturnUrl)
					->setCancelUrl($sCancelUrl);

		$payment = new Payment();
		$payment->setIntent("sale")
				->setPayer($payer)
				->setRedirectUrls($redirectUrls)
				->setTransactions(array($transaction));

		// Replace these values by entering your own ClientId and Secret by visiting https://developer.paypal.com/developer/applications/
		$clientId =  MODULE_PAYMENT_PAYPAL_API_CLIENTID;
		$clientSecret = MODULE_PAYMENT_PAYPAL_API_SECURE;

		/**
		* All default curl options are stored in the array inside the PayPalHttpConfig class. To make changes to those settings
		* for your specific environments, feel free to add them using the code shown below
		* Uncomment below line to override any default curl options.
		*/
		// \PayPal\Core\PayPalHttpConfig::$defaultCurlOptions[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_2;


		/** @var \Paypal\Rest\ApiContext $apiContext */
		$apiContext = getApiContext($clientId, $clientSecret);


		try {
			$payment->create($apiContext);
			$this->form_action_url = $payment->getApprovalLink();
		} catch (Exception $ex) {

/*
		echo $ex->getCode(); 
		echo $ex->getData();
		die($ex);
*/		

			$_SESSION['error_message'] = MODULE_PAYMENT_PAYPAL_API_ERROR;
			oos_redirect(oos_href_link($aContents['checkout_payment']));
		}

		return;
		

    }

    function before_process() {
		
		$clientId =  MODULE_PAYMENT_PAYPAL_API_CLIENTID;
		$clientSecret = MODULE_PAYMENT_PAYPAL_API_SECURE;
		/**
		* All default curl options are stored in the array inside the PayPalHttpConfig class. To make changes to those settings
		* for your specific environments, feel free to add them using the code shown below
		* Uncomment below line to override any default curl options.
		*/
		// \PayPal\Core\PayPalHttpConfig::$defaultCurlOptions[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_2;


		/** @var \Paypal\Rest\ApiContext $apiContext */
		$apiContext = getApiContext($clientId, $clientSecret);		
		
		
		// ### Approval Status
		// Determine if the user approved the payment or not
		if (isset($_GET['paymentId'])) {

			// Get the payment Object by passing paymentId
			// payment id was previously stored in session in
			// CreatePaymentUsingPayPal.php
			#  $paymentId = $_GET['paymentId'];
			# $payment = Payment::get($paymentId, $apiContext);

			// ### Payment Execute
			// PaymentExecution object includes information necessary
			// to execute a PayPal account payment.
			// The payer_id is added to the request query parameters
			// when the user is redirected from paypal back to your site
			$execution = new PaymentExecution();
			$execution->setPayerId($_GET['PayerID']);

			// Add the above transaction object inside our Execution object.
			$result = $payment->execute($execution, $apiContext);
		}	
		return;
    }

    function after_process() {
		return FALSE;
    }

    function output_error() {
		return FALSE;
    }

    function check() {
      if (!isset($this->_check)) {
        $this->_check = defined('MODULE_PAYMENT_PAYPAL_API_STATUS');
      }

      return $this->_check;
    }

    function install() {

      // Get database information
		$dbconn =& oosDBGetConn();
		$oostable =& oosDBGetTables();

		$configurationtable = $oostable['configuration'];
		$dbconn->Execute("INSERT INTO $configurationtable (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_PAYPAL_API_STATUS', 'TRUE', '6', '3', 'oos_cfg_select_option(array(\'TRUE\', \'FALSE\'), ', now())");
		$dbconn->Execute("INSERT INTO $configurationtable (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYPAL_API_ID', 'you@yourbusiness.com', '6', '4', now())");
		$dbconn->Execute("INSERT INTO $configurationtable (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYPAL_API_CLIENTID', '', '6', '5', now())");
		$dbconn->Execute("INSERT INTO $configurationtable (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYPAL_API_SECURE', '', '6', '5', now())");
		$dbconn->Execute("INSERT INTO $configurationtable (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_PAYPAL_API_MODE', 'SANDBOX', '6', '3', 'oos_cfg_select_option(array(\'SANDBOX\', \'LIVE\'), ', now())");
		$dbconn->Execute("INSERT INTO $configurationtable (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYPAL_API_SORT_ORDER', '0', '6', '0', now())");
		$dbconn->Execute("INSERT INTO $configurationtable (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('MODULE_PAYMENT_PAYPAL_API_ZONE', '0', '6', '2', 'oos_cfg_get_zone_class_title', 'oos_cfg_pull_down_zone_classes(', now())");
		$dbconn->Execute("INSERT INTO $configurationtable (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_PAYMENT_PAYPAL_API_ORDER_STATUS_ID', '0', '6', '0', 'oos_cfg_pull_down_order_statuses(', 'oos_cfg_get_order_status_name', now())");
    }

    function remove() {

      // Get database information
      $dbconn =& oosDBGetConn();
      $oostable =& oosDBGetTables();

      $configurationtable = $oostable['configuration'];
      $dbconn->Execute("DELETE FROM $configurationtable WHERE configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
		return array('MODULE_PAYMENT_PAYPAL_API_STATUS', 'MODULE_PAYMENT_PAYPAL_API_ID', 'MODULE_PAYMENT_PAYPAL_API_CLIENTID', 'MODULE_PAYMENT_PAYPAL_API_SECURE', 'MODULE_PAYMENT_PAYPAL_API_MODE', 'MODULE_PAYMENT_PAYPAL_API_ZONE', 'MODULE_PAYMENT_PAYPAL_API_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYPAL_API_SORT_ORDER');
    }
}



/**
 * Helper method for getting an APIContext for all calls
 * @param string $clientId Client ID
 * @param string $clientSecret Client Secret
 * @return PayPal\Rest\ApiContext
 */
function getApiContext($clientId, $clientSecret)
{

    // #### SDK configuration
    // Register the sdk_config.ini file in current directory
    // as the configuration source.
    /*
    if(!defined("PP_CONFIG_PATH")) {
        define("PP_CONFIG_PATH", __DIR__);
    }
    */


    // ### Api context
    // Use an ApiContext object to authenticate
    // API calls. The clientId and clientSecret for the
    // OAuthTokenCredential class can be retrieved from
    // developer.paypal.com

    $apiContext = new ApiContext(
        new OAuthTokenCredential(
            $clientId,
            $clientSecret
        )
    );

    // Comment this line out and uncomment the PP_CONFIG_PATH
    // 'define' block if you want to use static file
    // based configuration

    $apiContext->setConfig(
        array(
            'mode' => MODULE_PAYMENT_PAYPAL_API_MODE,
            'log.LogEnabled' => true,
            'log.FileName' => OOS_TEMP_PATH . 'logs/PayPal.log',
            'log.LogLevel' => 'INFO', // DEBUG, PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
            'cache.enabled' => true,
            //'cache.FileName' => '/PaypalCache' // for determining paypal cache directory
            // 'http.CURLOPT_CONNECTTIMEOUT' => 30
            // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
            //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
        )
    );

    // Partner Attribution Id
    // Use this header if you are a PayPal partner. Specify a unique BN Code to receive revenue attribution.
    // To learn more or to request a BN Code, contact your Partner Manager or visit the PayPal Partner Portal
    // $apiContext->addRequestHeader('PayPal-Partner-Attribution-Id', '123123123');

    return $apiContext;
}