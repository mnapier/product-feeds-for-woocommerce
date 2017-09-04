<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 2.0
 * Front Page Dialog for GoDataFeed
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-09
 ********************************************************************/
class BonanzaDlg extends PBaseFeedDialog
{

    function __construct()
    {
        parent::__construct();
        $this->service_name = 'Bonanza';
        $this->service_name_long = 'Bonanza Product CSV Feed';
        // $this->options = array(
        // 	'brand', 'keywords', 'UPC', 'mpn'
        // 	);
        $this->blockCategoryList = false;
    }

}
