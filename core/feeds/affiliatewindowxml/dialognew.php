<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 3.0
 * Export an AffiliateWindow XML data feed
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Calv 2014-11-12
 ********************************************************************/
class AffiliateWindowXMLDlg extends PBaseFeedDialog
{

    function __construct()
    {
        parent::__construct();
        $this->service_name = 'AffiliateWindowXML';
        $this->service_name_long = 'Affiliate Window XML Feed';
        $this->blockCategoryList = false;
        $this->options = array();
    }

}
