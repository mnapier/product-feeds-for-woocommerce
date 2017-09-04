<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/********************************************************************
 * Version 2.0
 * A GoDataFeed Feed
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2015-12
 ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PWinesearcherFeed extends PXMLFeed
{

    function __construct()
    {

        parent::__construct();

        $this->providerName = 'Winesearcher';
        $this->providerNameL = 'winesearcher';
        $this->productLevelElement = 'wine';
        //Create some attributes (Mapping 3.0)
        //required
        $this->addAttributeMapping('', 'wine-name', true, true); //product name (15-70 chars)
        $this->addAttributeMapping('', 'price', false, true);
        $this->addAttributeMapping('', 'vintage', false, true);
        $this->addAttributeMapping('', 'bottle-size', false, true);
        $this->addAttributeMapping('', 'link', false, true);
        //$this->addAttributeMapping('', 'price', false,true);
        //$this->addAttributeMapping('', 'type', false,true);
    }

    //not needed, as we are using the XML2Feed class
    //function formatProduct($product) {}

    function getFeedFooter($file_name, $file_path)
    {
        $output = '
</wine-list>';
        return $output;
    }

    function getFeedHeader($file_name, $file_path)
    {
        $output = '<?xml version="1.0" encoding="UTF-8" ?>
<wine-list>';
        return $output;
    }

}