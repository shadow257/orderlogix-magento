<?php
class OLX_Checkout_Model_Observer
{
    /**
     * Exports an order after it is placed
     *
     * @param Varien_Event_Observer $observer observer object
     *
     * @return boolean
     */
    public function exportOrder(Varien_Event_Observer $observer)
    {
      $order = $observer->getEvent()->getOrder();
      $items = $order->getAllItems();
      $shipping = $order->getShippingAddress()->getData();
      $billing = $order->getBillingAddress()->getData();
      
      $data=Mage::app()->getFrontController()->getRequest()->getPost('payment', false);
      
      Mage::getModel('OLX_Checkout/export')->exportOrder($order,$items,$shipping,$billing,$data);
 
      return true;
 
    }
}
?>