<?php
class OLX_Checkout_Model_Export {
	 public function __construct() {  
     
   }  
	 /**
     * Generates an XML file from the order data and send it to OLX 
     *
     *
     * @param Mage_Sales_Model_Order $order order object
     *
     * @return boolean
     */
    public function exportOrder($order,$items,$shipping,$billing,$data)
    {
    
    Mage::getModel('OLX_Checkout/order')->saveOrderAction($order,$items,$shipping,$billing,$data);
 
    return true;
    }
}
?>