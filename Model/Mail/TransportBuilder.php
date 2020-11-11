<?php
/**
* Magiccart 
* @category    Magiccart 
* @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
* @license     http://www.magiccart.net/license-agreement.html
* @Author: DOng NGuyen<nguyen@dvn.com>
* @@Create Date: 2018-05-16 16:15:05
* @@Modify Date: 2018-06-02 00:45:56
* @@Function:
*/

namespace Magiccart\Orderforward\Model\Mail;

use Magento\Framework\App\TemplateTypesInterface;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{

    protected $message;
    protected $_attachment;
    protected $_parts = [];

     // /**
     //  * @param string $pdfString
     //  * @param string $filename
     //  * @return mixed
     //  */
     // public function addAttachment($pdfString, $filename)
     // {
     //     if($filename == '' ) {
     //         $filename="order";
     //     }
     //     $this->message->createAttachment(
     //         $pdfString,
     //         'application/xml',
     //         \Zend_Mime::DISPOSITION_ATTACHMENT,
     //         \Zend_Mime::ENCODING_BASE64,
     //         $filename.'.xml'
     //     );
     //     return $this;
     // }

    /**
     * Add an attachment to the message.
     *
     * @param string $content
     * @param string $fileName
     * @param string $fileType
     * @return $this
     */
    public function getAttachment($content, $fileName, $fileType)
    {
        $attachment = new \Zend\Mime\Part($content);
        $attachment->type = $fileType;
        $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
        $attachment->filename = $fileName;
        return $attachment;
    }

    /**
     * Prepare message.
     *
     * @return $this
     * @throws LocalizedException if template type is unknown
     */
    protected function prepareMessage()
    {
        parent::prepareMessage();
        $this->setPartsToBody();
        return $this;
    }

    public function createAttachment($content, $fileName, $fileType) {
        if($fileType === null) $fileType = 'application/pdf';
        $disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
        $encoding = \Zend\Mime\Mime::ENCODING_BASE64;
        if($fileName === null) throw new \Exception("Param 'filename' can not be null");
        $attachmentPart = new \Zend\Mime\Part();
        $attachmentPart
            ->setContent($content)
            ->setType($fileType)
            ->setDisposition($disposition)
            ->setEncoding($encoding)
            ->setFileName($fileName)
        ;
        $this->_attachment = $attachmentPart;
        return $this;
    }

    public function setPartsToBody() {
        if($this->_attachment) $this->_parts[] = $this->_attachment;
        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->setParts($this->_parts);
        $this->message->setBody($mimeMessage);
        return $this;
    }

}
