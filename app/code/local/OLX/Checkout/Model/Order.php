<?php


class OLX_Checkout_Model_Order {
  
  
	/********************************************************************
	**            ORDERLOGIX MAGENTO CHECKOUT EXTENSION                **
	**                                                                 **
	**                @Copyright 2013 OrderLogix                       **
	**                                                                 **
	** This custom Magento extension will hook onto the Magento        **
	** OnepageCheckout controller and will gather all of the details   ** 
	** of the order and post them in real time via a secure HTTPS      **
	** post to a specified URL of the OrderLogix OLCC order management **
	** system.  The details of the request to OLCC and the response    **
	** from OLCC will be put into the Magento system logfile.          **
	**                                                                 **
	** This extension was developed for Magento Community Edition      **
	** version 1.7.0.2.                                                **
	**                                                                 **
	** This Extension is intended to be installed in the "local"       **
	** code pool of your magento implementation.			   **
	**                                                                 **
	** Please edit the parameters below appropriately for your         **
	** specific installation.                                          **
	**                                                                 **
	** Please consult with OrderLogix support before making any        **
	** modifications to the code below.				   **
	**                                                                 **
	** DISCLAIMER:  Since Magento Community Edition is an Open Source  **
	** system allowing for customization, this code is provided as-is  **
	** and will require testing and certification by the developer     **
	** responsible for maintaining the magento system where this       **
	** extension is installed.  OrderLogix assumes no responsibility   **
	** or liability for the use of this code in conjunction with a     **
	** Magento Community Edition implementation.       	           **
	**                                                                 **
	********************************************************************/

	/********************************************************************
	**   Parameter: site_address                                       **
	**   Value: Provide a value for the site base url address to       **
	**   	    the OrderLogix System where the orders will be posted. **
	**          This value will be provided by OrderLogix.             **	
	********************************************************************/
	var $site_address ='';
	/********************************************************************
	**   Parameter: user                                               **
	**   Value: Provide a value for the site level authentication to   **
	**   	    the OrderLogix System where the orders will be posted. **
	**          This value will be provided by OrderLogix.             **	
	********************************************************************/
	var $user = '';
        
 	/********************************************************************
	**   Parameter: pwd                                                **
	**   Value: Provide a value for the site level authentication to   **
	**   	    the OrderLogix System where the orders will be posted. **
	**          This value will be provided by OrderLogix.             **		
	********************************************************************/
  var $pwd = '';

	/********************************************************************
	**   Parameter: token                                              **
	**   Value: Provide a value for the site level authorization to    **
	**   	    the OrderLogix System where the orders will be posted. **
	**          This value will be provided by OrderLogix.             **	
	********************************************************************/
  var $token = '';

 	/********************************************************************
	**   Parameter: olcc_user                                          **
	**   Value: Provide a value for the app level authentication       **
	**          thta will be used to access OLCC from the webbsite.    **
	**          This value must be setup in the OrderLogix system with **
	**          permissions to use the API to send orders.             ** 	
	********************************************************************/
  var $olcc_user = '';

	/********************************************************************
	**   Parameter: olcc_pwd                                           **
	**   Value: Provide a value for the app level authentication       **
	**          thta will be used to access OLCC from the webbsite.    **
	**          This value must be setup in the OrderLogix system with **
	**          permissions to use the API to send orders.             ** 	
	********************************************************************/
    var $olcc_pwd = '';
	/********************************************************************
	**   Parameter: dnis                                               **
	**   Value: Provide a value for the source code for every order.   **
	**          thta will be taken on the website.   This value must   **
	**          be setup in the OrderLogix system in order to send     **
	**          orders from the shopping cart to OLCC.                 **
	********************************************************************/
        var $dnis = '';

	/********************************************************************
	**   Parameter: user                                               **
	**   Value: Provide a value for the site level authentication      **
	********************************************************************/
        var $emp_number = '';
        
 	/********************************************************************
	**   Parameter: order_prefix                                       **
	**   Value: Provide a value for the prefix assigned to orders to   **
	**          guarantee unique eternal order numbers in the OLCC     **
	**          system.						   **
	********************************************************************/
        var $order_prefix = '';
        
        /********************************************************************
	**   Parameter: admin_email                                        **
	**   Value: Provide a value for the email address to send error    **
	**          reports to in thge event of a failed API request to    **
	**          OLCC.                                                  **
	********************************************************************/
        var $admin_email = '';
        
        /********************************************************************
	**         PLEASE DO NOT MODIFY ANYTHING BELOW THIS LINE           **
	**         WITHOUT PRIOR APPROVAL FROM ORDERLOGIX SUPPORT          **
	********************************************************************/
	
	public function __construct() {  
	
        /********************************************************************
	**         Pull settingss from the Store configuration             **
	********************************************************************/
	
     $this->site_address=Mage::getStoreConfig('OLX/OLX_group/OLX_site_address',Mage::app()->getStore());
     $this->user=Mage::getStoreConfig('OLX/OLX_group/OLX_site_username',Mage::app()->getStore());
     $this->pwd=Mage::getStoreConfig('OLX/OLX_group/OLX_site_password',Mage::app()->getStore());
     $this->token=Mage::getStoreConfig('OLX/OLX_group/OLX_api_token',Mage::app()->getStore());
     $this->olcc_user=Mage::getStoreConfig('OLX/OLX_group/OLX_olcc_username',Mage::app()->getStore());
     $this->olcc_pwd=Mage::getStoreConfig('OLX/OLX_group/OLX_olcc_password',Mage::app()->getStore());
     $this->dnis=Mage::getStoreConfig('OLX/OLX_group/OLX_dnis',Mage::app()->getStore());
     $this->emp_number=Mage::getStoreConfig('OLX/OLX_group/OLX_employ_num',Mage::app()->getStore());
     $this->order_prefix=Mage::getStoreConfig('OLX/OLX_group/OLX_prefix',Mage::app()->getStore());
     $this->admin_email=Mage::getStoreConfig('OLX/OLX_group/OLX_email',Mage::app()->getStore());
	 
	 $this->payment_action=Mage::getStoreConfig('payment/authorizenet/payment_action',Mage::app()->getStore());
   }  
	
	
    public function saveOrderAction($order,$items,$shipping,$billing,$data) {
    	    
		
        $result = array();
        $result['success'] = false;
        $result['error'] = true;
        $result['error_messages'] = '';
       
         
         
        /* Map the Order's CC type to our CC type */
        if( isset($data['cc_type']) ) {
            switch($data['cc_type']) {
                case 'VI':
                    $cctype = 'VS'; break;
                case 'MC':
                    $cctype = 'MC'; break;
                case 'AE':
                    $cctype = 'A'; break;
                case 'DI':
                    $cctype = 'D'; break;
                default:
                    $cctype = ''; break;
            }
        }
	
	// Grab all of the necessary order data
        $inputs = array();
        
        /* Billing Address */
        //$inputs['customer_number'] = $this->order_prefix . '-' .$order->increment_id;
		$inputs['customer_number'] = $order->increment_id;
        $inputs['bill_first_name'] = $billing['firstname'];
        $inputs['bill_last_name'] = $billing['lastname'];
        $inputs['bill_address1'] = $billing['street'];
        $inputs['bill_city'] = $billing['city'];
        $inputs['bill_state'] = self::getShortState($billing['region']);
        $inputs['bill_zipcode'] = $billing['postcode'];
        $inputs['country'] = $billing['country_id'];  
        $inputs['bill_phone_number'] = $billing['telephone'];
        $inputs['email'] = $order->customer_email;
        
        /* Shipping Address */
        $inputs['ship_to_first_name'] = $shipping['firstname'];
        $inputs['ship_to_last_name'] = $shipping['lastname'];
        $inputs['ship_to_address1'] = $shipping['street'];
        $inputs['ship_to_city'] = $shipping['city'];
        $inputs['ship_to_state'] = self::getShortState($shipping['region']);
        $inputs['ship_to_zipcode'] = $shipping['postcode'];
        $inputs['scountry'] =  $shipping['country_id']; 
        $inputs['ship_to_phone'] = $shipping['telephone'];
        
        /* Order Info */
        setlocale(LC_ALL, '');
        $inputs['order_date'] = Mage::getModel('core/date')->date('m/d/Y');
        //$inputs['order_number'] = $this->order_prefix . '-' .$order->increment_id; Original
		$inputs['order_number'] = $order->increment_id; // Using Magento Invoice System
        
        //$inputs['dnis'] = $this->dnis; Original
		$inputs['dnis'] = substr($order->increment_id,0,5); // Magento Custom Store DNIS (First 5 Char)
        $inputs['emp_number'] = $this->emp_number;
        
		
		/* DNIS Campaign Overrides */
		switch($inputs['dnis']){
			case "MBSMK":
				$inputs['dnis'] = "MBSMK299";
				break;
				
			case "30000":
				$inputs['dnis'] = "MBBBF";
				break;
				
			case "MBBBF":
				$inputs['dnis'] = "MBBBF";
				break;
			
			default:
				// This will keep DNIS the same if nothing matches
				break;	
		}
		
        
        /* Pricing controls */
        $inputs['use_prices'] = 'N';
        $inputs['use_shipping'] = 'N';
        
        
        /* Check for tax */
        $tax_info = $order->getFullTaxInfo();
        
			if(count($tax_info)>0){
				$tax=current($tax_info);
				if(count($tax)>0 && isset($tax['amount'])){
					$inputs['order_state_sales_tax']=$tax['amount'];
         	}
        }
        else{
        	$inputs['order_state_sales_tax']="0.00";
        }
 
 
          
        // Get payment fields for CC orders
        if( !empty($data['cc_number'])) {
            $inputs['cc_type'] = $cctype;
            $inputs['cc_number'] = $data['cc_number'];
            $inputs['exp_date'] = str_pad($data['cc_exp_month'], 2, '0', STR_PAD_LEFT).'/'.substr($data['cc_exp_year'], 2);
            $inputs['cvv_code'] = ""; 
            $inputs['payment_method'] = 'CC'; 

            
            // Set Type, TransID and amount for Authorized orders
            if(strcmp($data['method'],'authorizenet')==0) {
                $orderData = $order->getPayment()->getData();
				$additional = $orderData['additional_information']['authorize_cards'];
				$keys = array_keys($additional);
				$transactionId = $additional[$keys[0]]['last_trans_id'];
				
				if($this->payment_action == "authorize_capture"){
                	$inputs['payment_type'] = 'SALE'; //Auth and Capture
				}else{
					$inputs['payment_type'] = 'AUTH'; // Auth Only
				}
				
                $inputs['merchant_transaction_id'] = $transactionId;
                $inputs['amount_already_paid'] = number_format($order->getPayment()->amount_authorized,2);
            }
        } else {
            //Non-Creditcard Order.  Set Payment Method to "INVOICE" and manage payment outside of OLCC system.
            $inputs['payment_method'] = 'IN';
        }

        $inputs['shipping_method']='REG';

        // Loop through and add all products ordered
        $n = 1;
        
        foreach($items as $productId => $product) {
            $nString = str_pad($n, 2, '0', STR_PAD_LEFT);
            $inputs['product'.$nString] = $product->getSku(); //$tmp[1];
			if($product->getQtyToInvoice() == 0){
				// Try to get just the regular product qty
				$inputs['quantity'.$nString] = $product->getQtyOrdered();
			}else{
				$inputs['quantity'.$nString] = $product->getQtyToInvoice();
			}            
            $inputs['price'.$nString] = number_format($product->getPrice(), 2);
            ++$n;
        }

        /* Note that for a default implementation, shipping needs to be matched in the OrderLogix System */
        $inputs['shipping']=number_format($order->getShippingAmount(),2);
        
        //Build the XML doc and also a "pci_safe version" while we are at it...
        $xml = '<?xml version="1.0" encoding="utf-8" ?><OrderImport><Order>';
        $pci_xml = '<?xml version="1.0" encoding="utf-8" ?><OrderImport><Order>';
        foreach($inputs as $key => $val) {
            $xml .= "<".strtoupper($key).">{$val}</".strtoupper($key).">";
            if ($key == 'cc_number' || $key == 'exp_date'  || $key == 'cvv_code' ) {
            	$pci_xml .= "<".strtoupper($key).">*** REDACTED ***</".strtoupper($key).">";
            }
            else {
            	$pci_xml .= "<".strtoupper($key).">{$val}</".strtoupper($key).">";
            	}
        }
        $xml .= '</Order></OrderImport>';
        $pci_xml .= '</Order></OrderImport>';
      
        // use the pci safe XML for all logging and emailing
        $logVar = print_r($pci_xml, true);
        
        // send the xml to the log for review
        Mage::log( $logVar,null,'OrderLogix_API.log' );

		// Build the url with basic auth parameters
        $url = 'https://' . $this->user . ':' . $this->pwd . '@' . $this->site_address;

    	// send the order to OLCC
    	try {
	
		// setup an http request object
		$client = new Varien_Http_Client($url,array("timeout"=>10000));
		$client->setMethod(Varien_Http_Client::POST);
		
		// Add the 4 post params
		$client->setParameterPost('user', $this->olcc_user);
		$client->setParameterPost('pwd', $this->olcc_pwd);
		$client->setParameterPost('token', $this->token);
		$client->setParameterPost('inXMLDoc', $xml);
		
		// send the actual request
		$response = $client->request();
		
		// check and parse response
		if ($response->isSuccessful()) {
		        $resp=$response->getBody();
		        Mage::log($resp,null,'OrderLogix_API.log');
	                $obj = simplexml_load_string($resp); 
	                $error="";
	                $customer_id="";
	                $order_id="";
	                
	                if(isset($obj->RESULT_CODE) && isset($obj->MESSAGE_LIST) && (strcmp($obj->RESULT_CODE,"FAILURE")==0)){
	                	$error=$obj->MESSAGE_LIST;
	                }
	                
	                if(isset($obj->DATA->ERROR_DETAIL)){
	                	$error.=current($obj->DATA->ERROR_DETAIL);
	                }
	                
	                if(isset($obj->DATA->ORDER_ID)){
	                	$order_id=current($obj->DATA->ORDER_ID);
	                }
	                
	                if(isset($obj->DATA->CUSTOMER_ID)){
	                	$customer_id=current($obj->DATA->CUSTOMER_ID);
	                }
	                
		        Mage::log('Val of CustomerID:(' . $customer_id .')',null,'OrderLogix_API.log');
		        Mage::log('Val of OrderID:(' . $order_id .')',null,'OrderLogix_API.log');
		        //check response to see if the XML doc contains 2 specific elements indicating an error
	        	if(is_numeric ($customer_id) && is_numeric($order_id)) {
	            		$result['success'] = true;
	            		$result['error'] = false;
	        		Mage::log('Did not trap an error during this response.',null,'OrderLogix_API.log');
		          }
		          else {
		          	$result['success'] = false;
		            	$result['error'] = true;
				// Send and email to administrator so they know about the issue
	            		$messagebody = 'Please review the following request and response which failed to successfully post the order to OLCC.\n';
	            		$messagebody .="Error: ".$error."\n\n";
	            		$messagebody .= 'REQUEST_SENT:\n' .  $logVar . '\n\n' . 'RESPONSE_RECEIVED:\n' . $resp;
	            		
	            		$this->sendEmail($this->admin_email
	            				,"sgiftos@orderlogix.com"
	            				,"Failed Magento Store Order submission from OrderLogix API"
	            				,$messagebody);

		          }
		} else	{
			// should probably log that this one failed
			 $resp=$response->getBody();
		        Mage::log($resp,null,'OrderLogix_API.log');
	  	}
	} catch (Exception $e) {
	    Mage::log( 'Caught exception: '. $e->getMessage(). "\n",null,'OrderLogix_API.log');
	}
 
        if($result['success']==true)
        	return true;
        else
        	return false;
        
        
       
    }
    
    private function sendEmail( $to, $from, $subject, $body) {
	
	try {
		$mail = new Zend_Mail(); //class for mail
		$mail->setBodyText($body); 
		$mail->setFrom($from, 'Magento Store');
		$mail->addTo($to, 'OrderLogix/Magento Support');
		$mail->setSubject($subject);
		$msg  ='';
		
	    	if($mail->send())
	    	{
	     		$msg = true;
	    	}
	     	Mage::log('Email Send Results: ' . $msg,null,'OrderLogix_API.log');
	      }catch(Exception $ex) {
	        $msg = false;
	        Mage::log('Error in Email Send Results: ' . $ex->getMessage(),null,'OrderLogix_API.log');
	      }
   }
   
   private static function getShortState($state) {

        // This function is used to collect a state abreviation for a given state that is passed into the order checkout
        $state = ucwords(strtolower(trim($state)));
        $abbreviation = $state;
        $states = array("AL"=>"Alabama","AK"=>"Alaska","AZ"=>"Arizona","AR"=>"Arkansas","CA"=>"California","CT"=>"Connecticut","DE"=>"Delaware","DC"=>"District Of Columbia","FL"=>"Florida","GA"=>"Georgia","HI"=>"Hawaii","ID"=>"Idaho","IL"=>"Illinois","IN"=>"Indiana","IA"=>"Iowa","KS"=>"Kansas","KY"=>"Kentucky","LA"=>"Louisiana","ME"=>"Maine","MD"=>"Maryland","MA"=>"Massachusetts","MI"=>"Michigan","MN"=>"Minnesota","MS"=>"Mississippi","MO"=>"Missouri","MT"=>"Montana","NE"=>"Nebraska","NV"=>"Nevada","NH"=>"New Hampshire","NM"=>"New Mexico","NJ"=>"New Jersey","NY"=>"New York","NC"=>"North Carolina","ND"=>"North Dakota","OH"=>"Ohio","OK"=>"Oklahoma","OR"=>"Oregon","PA"=>"Pennsylvania","RI"=>"Rhode Island","SC"=>"South Carolina","SD"=>"South Dakota","TN"=>"Tennessee","TX"=>"Texas","UT"=>"Utah","VT"=>"Vermont","VA"=>"Virginia","WA"=>"Washington","WV"=>"West Virginia","WI"=>"Wisconsin","WY"=>"Wyoming","AS"=>"American Samoa","FM"=>"Federated States Of Micronesia","GU"=>"Guam","MH"=>"Marshall Islands","MP"=>"Northern Mariana Islands","PW"=>"Palau","PR"=>"Puerto Rico","VI"=>"Virgin Islands","AE"=>"Armed Forces Africa","AA"=>"Armed Forces Americas","AE"=>"Armed Forces Canada","AE"=>"Armed Forces Europe","AE"=>"Armed Forces Middle East","AP"=>"Armed Forces Pacific");

        if (strlen($state) > 2) {
            foreach ($states as $key => $value) {
                if ($state == $value) {
                    $abbreviation = $key;
                }
            }
        }

        return $abbreviation;
    }
    
    private function object2array($object) {
    	$array=array();
    	if (is_object($object)==true) {
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }
    	}else {
        $array = $object;
      }
      return $array;
    }

}
?>
