<?php
/**
* Magiccart 
* @category    Magiccart 
* @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
* @license     http://www.magiccart.net/license-agreement.html
* @Author: DOng NGuyen<nguyen@dvn.com>
* @@Create Date: 2018-05-16 16:15:05
* @@Modify Date: 2018-06-08 19:04:31
* @@Function:
*/

namespace Magiccart\Orderforward\Block;

 
class VetusStockXml
{
    //you can change associative key names if you dont want to rename keys before adding the arrays
    //eg changing 'itemno' in $product_info_tags to 'sku' would allow sku to be used and it will rename it to what is in 'rename' => 
    private $header = array(    
                                'custno'            => array(   
                                                                'rename'        => 'custno',
                                                                'allow_empty'   => 0
                                                            ),
                                'yourref'           => array(   
                                                                'rename'        => 'yourref',
                                                                'allow_empty'   => 0
                                                            ),
                                'partialallowed'    => array(   
                                                                'rename'        => 'partialallowed',
                                                                'allow_empty'   => 0
                                                            )
                            );
    private $product_info_tags = array( 
                                        'itemno'            => array(   
                                                                        'rename'        => 'itemno',
                                                                        'allow_empty'   => 0
                                                                    ), 
                                        'quantity'          => array(   
                                                                        'rename'        => 'quantity',
                                                                        'allow_empty'   => 0
                                                                    )
                                        );
    private $delivery_info_tags = array(
                                        'name'              => array(   
                                                                        'rename'        => 'name',
                                                                        'allow_empty'   => 0
                                                                    ), 
                                        'name2'             => array(   
                                                                        'rename'        => 'name2',
                                                                        'allow_empty'   => 1
                                                                    ), 
                                        'address'           => array(   
                                                                        'rename'        => 'address',
                                                                        'allow_empty'   => 0
                                                                    ), 
                                        'address2'          => array(   
                                                                        'rename'        => 'address2',
                                                                        'allow_empty'   => 1
                                                                    ), 
                                        'postcode'          => array(   
                                                                        'rename'        => 'postcode',
                                                                        'allow_empty'   => 0
                                                                    ), 
                                        'city'              => array(   
                                                                        'rename'        => 'city',
                                                                        'allow_empty'   => 0
                                                                    ), 
                                        'county'            => array(   
                                                                        'rename'        => 'county',
                                                                        'allow_empty'   => 1
                                                                    ), 
                                        'countrycode'       => array(   
                                                                        'rename'        => 'countrycode',
                                                                        'allow_empty'   => 0
                                                                    ), 
                                        'contact'           => array(   
                                                                        'rename'        => 'contact',
                                                                        'allow_empty'   => 0
                                                                    ), 
                                        'email'             => array(   
                                                                        'rename'        => 'email',
                                                                        'allow_empty'   => 0
                                                                    ), 
                                        'phoneno'           => array(   
                                                                        'rename'        => 'phoneno',
                                                                        'allow_empty'   => 0
                                                                    ), 
                                        'remark'            => array(   
                                                                        'rename'        => 'remark',
                                                                        'allow_empty'   => 1
                                                                    )
                                        );
    private $delivery_info;
    private $product_info   = array();
    private $backorder      = false;
    private $own_id;
    public $order_id;
    private $all_good       = true;
    private $issues         = '';
    private $encoding       = "iso-8859-1";
    private $xml_version    = "1.0";
    public function         __construct(){}
    private function        __clone(){}
    public function         __toString()
    {
        return $this->generateResult();
    }
    public function setOwnId($id)
    {
        $this->own_id           = $id;
    }
    public function setOrderId($id)
    {
        $this->order_id         = $id;
    }
    public function setBackorderBool($bool)
    {
        $this->backorder        = $bool;
    }
    public function addProductArray($array_of_assoc_arrays)
    {
        foreach ($array_of_assoc_arrays as $array_of_assoc_arrays_key => $array_of_assoc_arrays_value) 
        {
            if (count($array_of_assoc_arrays_value) == count($this->product_info_tags)) 
            {
                $pushy_array = array();
                foreach ($this->product_info_tags as $key => $value) 
                {
                    if (isset($array_of_assoc_arrays_value[(string)$key])) 
                    {
                        if ($value['allow_empty'] || trim((string)$array_of_assoc_arrays_value[$key]) != '') 
                        {
                            $pushy_array[(string)$key] = trim((string)$array_of_assoc_arrays_value[$key]);
                            unset($array_of_assoc_arrays_value[$key]);
                        }
                    }
                }
                if (count($array_of_assoc_arrays_value) == 0) 
                {
                    unset($array_of_assoc_arrays[$array_of_assoc_arrays_key]);
                    array_push($this->product_info, $pushy_array);
                }
            }
        }
        if (count($array_of_assoc_arrays) != 0) 
        {
            $this->all_good = false;
            $this->issues .= 'array mismatch in arrays in addProductArray </br>';
        }
        return $this->all_good;
    }
    public function addDeliveryInfo($assoc_array)
    {
        if (count($assoc_array) == count($this->delivery_info_tags)) 
        {
            foreach ($this->delivery_info_tags as $key => $value) 
            {
                if (isset($assoc_array[$key])) 
                {
                    if ($value['allow_empty'] || trim((string)$assoc_array[$key]) != '') 
                    {
                        $this->delivery_info[$key] = trim((string)$assoc_array[$key]);
                        unset($assoc_array[$key]);
                    }
                }
            }
        }
        if (count($assoc_array) != 0) 
        {
            $this->all_good = false;
            $this->issues .= 'array mismatch in arrays in addDeliveryInfo </br>';
            $this->issues .= var_dump($assoc_array);
        }
        return $this->all_good;
    }
    public function generateResult()
    {
        $string =   '<?xml version="'.$this->xml_version.'" encoding="'.$this->encoding.'"?>'
                    .'<salesorder>'
                        .'<header>'
                            .$this->doStuff($this->header, 'custno',            $this->own_id   )
                            .$this->doStuff($this->header, 'yourref',           $this->order_id )
                            .$this->doStuff($this->header, 'partialallowed',    $this->backorder)
                        .'</header>'
                        .'<shiptoaddr>'
                            .$this->handleAddress()
                        .'</shiptoaddr>'
                        .'<lines>'
                            .$this->handleItems()
                        .'</lines>'
                    .'</salesorder>';
        return iconv('UTF-8', strtolower($this->encoding), $string);
    }
    public function isAllGood()
    {
        return $this->all_good;
    }
    public function tellMeTheIssue()
    {
        return $this->issues;
    }
    private function handleAddress()
    {
        $string = '';
        foreach ($this->delivery_info as $key => $value) 
        {
            $string .= $this->doStuff($this->delivery_info_tags, $key, $value);
        }
        return $string;
    }
    private function handleItems()
    {
        $string     = '';
        foreach ($this->product_info as $product_info_key => $product_info_items) 
        {
            $string .= '<salesline>';
            foreach ($product_info_items as $key => $value) 
            {
                $string .= $this->doStuff($this->product_info_tags, $key, $value);
            }
            $string .= '</salesline>';
        }
        return $string;
    }
    private function doStuff($array, $name, $value)
    {
        if (isset($array[$name])) 
        {
            if ($array[$name]['allow_empty'] || trim((string)$value) != '') 
            {
                return '<'.$array[$name]['rename'].'>'.trim((string)$value).'</'.$array[$name]['rename'].'>';
            }
            else
            {
                $this->issues .= 'The Xml tag '.$name.'('.$array[$name]['rename'].') does not allow its value to be empty.</br>';
            }
        }
        else
        {
            $this->issues .= 'The Xml tag '.$name.' dont exists in the allowed list.</br>';
        }
        $this->all_good = false;
        return '';
    }
}
