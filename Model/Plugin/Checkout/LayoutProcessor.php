<?php
/**
* Magiccart 
* @category    Magiccart 
* @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
* @license     http://www.magiccart.net/license-agreement.html
* @Author: DOng NGuyen<nguyen@dvn.com>
* @@Create Date: 2018-05-16 16:15:05
* @@Modify Date: 2018-06-05 16:05:36
* @@Function:
*/

namespace Magiccart\Orderforward\Model\Plugin\Checkout;
 
class LayoutProcessor
{
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
 
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['custom_field'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress.shipping_remark',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'custom-field'
            ],
            'dataScope' => 'shippingAddress.shipping_remark.custom_field',
            'label' => 'Custom Field',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => [],
            'sortOrder' => 250,
            'id' => 'custom-field'
        ];
 
 
        return $jsLayout;
    }
}
