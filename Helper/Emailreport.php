<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2018-05-16 20:26:27
 * @@Modify Date: 2018-06-06 15:06:34
 * @@Function:
 */

namespace Magiccart\Orderforward\Helper;
 
use Magento\Framework\App\Area;
 
/**
 * Class Emailreport
 * @package Dckap\CustomModule\Helper
 */
class Emailreport extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;
    /**
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    private $reader;
 
    /**
     * Emailreport constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Filesystem\Driver\File $reader
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magiccart\Orderforward\Model\Mail\TransportBuilder $transportBuilder,
        \Magento\Framework\Filesystem\Driver\File $reader
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->reader = $reader;
    }
 
    /**
     * @param $path
     * @param $storeId
     * @return mixed
     */
    public function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
 
    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }
 
    /**
     * @param $template
     * @param $senderInfo
     * @param $receiverInfo
     * @param array $templateParams
     * @param null $xmlFile
     * @return $this
     */
    public function sendEmailReport(
        $template,
        $senderInfo,
        $receiverInfo,
        $templateParams = [],
        $xmlFile = null
    ) {
        $this->inlineTranslation->suspend();
        $templateId = $this->getConfigValue($template, $this->getStore()->getStoreId());

        if ($xmlFile) {
            $filename = basename($xmlFile); //basename($xmlFile, ".xml");
            // die($filename);
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($templateParams)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo['email'], $receiverInfo['name'])
                ->createAttachment($this->reader->fileGetContents($xmlFile), $filename, 'application/xml')
                ->getTransport();

        } else {
            $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($templateParams)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo['email'], $receiverInfo['name'])
                ->getTransport();
        }
        $transport->sendMessage();
        $this->inlineTranslation->resume();
 
        return $this;
    }
}
