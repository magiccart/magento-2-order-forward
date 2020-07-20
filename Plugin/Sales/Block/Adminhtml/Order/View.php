<?php
/**
* Magiccart 
* @category    Magiccart 
* @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
* @license     http://www.magiccart.net/license-agreement.html
* @Author: DOng NGuyen<nguyen@dvn.com>
* @@Create Date: 2018-05-16 16:15:05
* @@Modify Date: 2018-06-12 21:28:33
* @@Function:
*/

namespace Magiccart\Orderforward\Plugin\Sales\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class View
{
	/** @var \Magento\Framework\UrlInterface */
	protected $_urlBuilder;

	/** @var \Magento\Framework\AuthorizationInterface */
	protected $_authorization;

    protected $_filesystem;

	public function __construct(
		UrlInterface $url,
		AuthorizationInterface $authorization,
		Filesystem $fileSystem
	) {
		$this->_urlBuilder = $url;
		$this->_authorization = $authorization;
		$this->_filesystem   = $fileSystem;
	}

	public function beforeSetLayout(OrderView $order) {
		$url = $this->_urlBuilder->getUrl('orderforward/order/email', ['order_id' => $order->getOrderId()]);
		$url_partial = $this->_urlBuilder->getUrl('orderforward/order/email', ['order_id' => $order->getOrderId(), 'partialallowed' => 1]);
		$status = $this->statusForward($order);
		if(!$status){
			$order->addButton(
				'send_order_forward',
				[
				'label' 	=> __('Forward an order'),
				'class' 	=> __('send_order_forward'),
				'id' 		=> 'send_order_forward',
				'onclick' 	=> "confirmSetLocation('" . __('Are you sure you want to forward an order?') . "', '" . $url . "')"
				]
			);
			$order->addButton(
				'send_order_forward_partial',
				[
				'label' 	=> __('Forward order - partial allowed'),
				'class' 	=> __('send_order_forward_partial'),
				'id' 		=> 'send_order_forward_partial',
				'onclick' 	=> "confirmSetLocation('" . __('Are you sure you want to forward an order?') . "', '" . $url_partial . "')"
				]
			);			
		} else {
			$order->addButton(
				'resend_order_forward',
				[
				'label' 	=> __('Re-forward an order'),
				'class' 	=> __('resend_order_forward'),
				'id' 		=> 'resend_order_forward',
				'onclick' 	=> "confirmSetLocation('" . __('Are you sure you want to re-forward an order?') . "', '" . $url . "')"
				]
			);
			$order->addButton(
				'resend_order_forward_partial',
				[
				'label' 	=> __('Re-Forward order - partial allowed'),
				'class' 	=> __('send_order_forward_partial'),
				'id' 		=> 'send_order_forward_partial',
				'onclick' 	=> "confirmSetLocation('" . __('Are you sure you want to re-forward an order?') . "', '" . $url_partial . "')"
				]
			);			
		}

	}

	public function statusForward($order)
	{
        $order = $order->getOrder();
        $dir = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA); // DirectoryList::VAR_DIR
        $filePath = 'order/' . $order->getData('increment_id') . '.xml';
        if ($dir->isExist($filePath )) return true;
        return false;
	}
}
