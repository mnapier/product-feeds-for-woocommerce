<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 2.0
 * A GoDataFeed Feed
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-09
 ********************************************************************/

require_once dirname(__FILE__) . '/../basicfeed.php';

class PGPAnalysisFeed extends PXML2Feed
{

    function __construct()
    {

        parent::__construct();

        $this->providerName = 'GPAnalysis';
        $this->providerNameL = 'gpanalysis';
        $this->productLevelElement = 'auction';
        //Create some attributes (Mapping 3.0)
        //required
        $this->addAttributeMapping('title', 'title', true, true); //product name (15-70 chars)
        $this->addAttributeMapping('', 'issue', false, true);
        $this->addAttributeMapping('', 'grade', false, true);
        $this->addAttributeMapping('', 'certification', false, true);
        $this->addAttributeMapping('', 'date', false, true);
        $this->addAttributeMapping('', 'price', false, true);
        $this->addAttributeMapping('', 'type', false, true);
    }

    //not needed, as we are using the XML2Feed class
    //function formatProduct($product) {}

    function getFeedFooter($file_name, $file_path)
    {
        $output = '
</auctions>';
        return $output;
    }

    function getFeedHeader($file_name, $file_path)
    {
        $output = '<?xml version="1.0" encoding="UTF-8" ?>
<auctions>';
        return $output;
    }

}