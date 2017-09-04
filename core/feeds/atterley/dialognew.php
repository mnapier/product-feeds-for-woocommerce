<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 1.0
 * Front Page Dialog for AtterleyFeed
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Sushma 2016-08-23
 ********************************************************************/
class AtterleyDlg extends PBaseFeedDialog
{

    function __construct()
    {
        parent::__construct();
        $this->service_name = 'Atterley';
        $this->service_name_long = 'Atterley Products XML Export';
    }

    function convert_option($option)
    {
        return strtolower(str_replace(" ", "_", $option));
    }

}

?>