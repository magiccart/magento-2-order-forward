<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2018-05-16 10:40:51
 * @@Modify Date: 2018-06-08 19:19:33
 * @@Function:
 */

namespace Magiccart\Orderforward\Controller\Adminhtml\Order;

use \Magento\Sales\Controller\Adminhtml\Order\Email as OrderEmail;
use \Magiccart\Orderforward\Block\CreditEmail;

class Email extends OrderEmail
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
        	/*
	            // print_r(get_class_methods($order));
	            $items = $order->getItemsCollection();
	            // print_r(get_class_methods($items));
	            foreach ($items as $key => $value) {
	                // print_r(get_class_methods($value));
	                var_dump($value->getData('sku'));
	                var_dump($value->getData('qty_ordered'));
	            }

	            // $addresse = $order->getAddressesCollection();
	            // // print_r(get_class_methods($addresse));
	            // foreach ($addresse as $key => $value) {
	            //     // print_r(get_class_methods($value));
	            //     // print_r($value->getData());
	            // }

	            $billingAddress = $order->getBillingAddress();
	            $shippingAddress = $order->getShippingAddress();
	            // var_dump($shippingAddress);
	            // print_r(get_class_methods($shippingAddress));
	                print_r($shippingAddress->getData());
	            die;
	            */
                // print_r($order->getData()); die;
            try {
                // $this->orderManagement->notify($order->getEntityId());
                $CreditEmail = $this->_view->getLayout()->createBlock('Magiccart\Orderforward\Block\CreditEmail');
                $partialallowed = $this->getRequest()->getParam('partialallowed');
                if($partialallowed) $order->setData('partialallowed', $partialallowed);
                $CreditEmail->sendEmailReport($order);
                $this->messageManager->addSuccess(__('You sent the forward order email.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t send the email forward order right now error: ' . $e->getMessage()));
                $this->logger->critical($e);
            }
            return $this->resultRedirectFactory->create()->setPath(
                'sales/order/view',
                [
                    'order_id' => $order->getEntityId()
                ]
            );
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
    }


}
