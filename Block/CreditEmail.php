<?php

/**

* Magiccart 

* @category    Magiccart 

* @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 

* @license     http://www.magiccart.net/license-agreement.html

* @Author: DOng NGuyen<nguyen@dvn.com>

* @@Create Date: 2018-05-16 16:15:05

* @@Modify Date: 2018-06-17 17:14:09

* @@Function:

*/



namespace Magiccart\Orderforward\Block;



 

use Magento\Framework\View\Element\Template;

use Magento\Framework\View\Element\Template\Context;

use Magiccart\Orderforward\Helper\Emailreport;

use Magiccart\Orderforward\Block\VetusStockXml;

use Magento\Framework\App\Filesystem\DirectoryList;

 



class CreditEmail extends Template

{

    /* A constant is declared with custom field in admin created using system.xml */

 

    const XML_PATH_CUSTOM_EMAIL_TEMPLATE= 'orderforward/general/email_template';   // section_id/group_id/field_id

 

    /**

     * @var Emailreport

     */

    private $emailReport;

    /**

     * @var Filesystem

     */

    protected $_filesystem;



    private $_countryFactory;

    private $_region;



    public $_scopeConfig;

 

    /**

     * @param Context $context

     * @param Emailreport $emailReport

     * @param Filesystem $fileSystem

     * @param array $data

     */

    public function __construct(

        Context $context,

        Emailreport $emailReport,

        \Magento\Directory\Model\CountryFactory $countryFactory,

        \Magento\Directory\Model\Region $region,

        array $data = []

    ){

        $this->emailReport  = $emailReport;

        $this->_filesystem   = $context->getFilesystem();

        $this->_countryFactory = $countryFactory;

        $this->_region = $region;

        $this->_scopeConfig = $context->getScopeConfig();

        parent::__construct($context, $data);

    }

 

    /**

     * @return $this

     */

    public function sendEmailReport($order)

    {

        /* Sender Detail */

        $senderInfo = [

            'name'  => 'sender',

            'email' => $this->_scopeConfig->getValue( 'orderforward/general/sender', \Magento\Store\Model\ScopeInterface::SCOPE_STORE )

        ];

 

        /* Receiver Detail */

        $receiverInfo = [

            'name'  => 'receiver',

            'email' => $this->_scopeConfig->getValue( 'orderforward/general/receiver', \Magento\Store\Model\ScopeInterface::SCOPE_STORE )

        ];

 

        /* Info order */

            $our_id             = $this->_scopeConfig->getValue( 'orderforward/general/company_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE );

            $order_id           = $order->getData('increment_id'); //$order->getEntityId();

            $allow_backorder    =  $order->getData('partialallowed') ? true: false;

            $shippingAddress    = $order->getShippingAddress();

            $firstname          = $shippingAddress->getData('firstname');

            $middlename         = $shippingAddress->getData('middlename');

            $lastname           = $shippingAddress->getData('lastname');

            $name               = $firstname;

            if($middlename) $name .= " $middlename";

            if($lastname) $name .= " $lastname";

            $company            = $shippingAddress->getData('company');

            $city               = $shippingAddress->getData('city');

            $postcode           = $shippingAddress->getData('postcode');

            $countryId          = $shippingAddress->getData('country_id');

            $regionCode         = $shippingAddress->getRegionCode();

            $region             = $this->_region->loadByCode($regionCode, $countryId);

            $county             = $region ? $region->getName() : '';

            // $country            = $this->getCountryname($countryId);

            $address            = $shippingAddress->getStreet();

            $add1               = isset($address[0]) ? $address[0] : '';

            $add2               = isset($address[1]) ? $address[1] : '';

            $email              = $shippingAddress->getData('email');

            $phoneno            = $shippingAddress->getData('telephone');



            $delivery_info_array    = array(    

                                                'name'          => $name ? $name : '',

                                                'name2'         => $company ? $company : '',

                                                'address'       => $add1 ? $add1 : '',

                                                'address2'      => $add2 ? $add2 : '',

                                                'postcode'      => $postcode ? $postcode : '',

                                                'city'          => $city ? $city : '',

                                                'county'        => $county ? $county : '',

                                                'countrycode'   => $countryId,

                                                'contact'       => $name,

                                                'email'         => $email,

                                                'phoneno'       => $phoneno ? $phoneno : '',

                                                'remark'        => 'remark'

                                            );

            $order_items_array  = array();

            // $items = $order->getItemsCollection();
            $items = [];
            foreach ($order->getItemsCollection() as $item) {
                if (!$item->getParentItem()) {
                    $items[] = $item;
                }
            }
            foreach ($items as $key => $value) {

                $order_items_array[] = array(

                    'itemno'    => $value->getData('sku'),

                    'quantity'  => (int) $value->getData('qty_ordered')

                    );

            }



            $stock_xml = new VetusStockXml();

            $stock_xml->setOwnId($our_id);

            $stock_xml->setOrderId($order_id);

            $stock_xml->setBackorderBool($allow_backorder);

            $stock_xml->addProductArray($order_items_array);

            $stock_xml->addDeliveryInfo($delivery_info_array);

            if ($stock_xml->isAllGood()) {

                $xml = $stock_xml;

                $doc =  new \DOMDocument();

                $doc->loadXML($xml);

                $doc->formatOutput = true;

                $filePath = 'order/' . $stock_xml->order_id . '.xml';

                try{

                    $dir = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA); // DirectoryList::VAR_DIR

                    $dir->writeFile($filePath, '$xml');

                    $backupFilePath = $dir->getAbsolutePath($filePath);

                    $doc->save($backupFilePath);

                    /* To assign the values to template variables */

                    $customerName = $name;

                    $customerEmail = $email;

             

                    $customerDetails = [];

                    $customerDetails['name'] = $customerName;

                    $customerDetails['email'] = $customerEmail;

             

                    $this->emailReport->sendEmailReport(

                        self::XML_PATH_CUSTOM_EMAIL_TEMPLATE,

                        $senderInfo,

                        $receiverInfo,

                        $customerDetails,

                        $backupFilePath

                    );



                } catch (\Exception $e) {

                        echo __('Can not export file "%1".<br/>"%2"', $filePath, $e->getMessage());

                        die;

                }

            } else {

                //no mailstuff because something is not right.

                echo $stock_xml->tellMeTheIssue();//this is not very inforamtive though. :| feel free to change class to be more informative. :P

                die;

            }

    }



    public function getCountryname($countryCode){    

        $country = $this->_countryFactory->create()->loadByCode($countryCode);

        return $country->getName();

    }



}

