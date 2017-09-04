<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Version 2.0
 * Front Page Dialog for Avantlink
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-05-05
 ********************************************************************/
class AvantlinkDlg extends PBaseFeedDialog
{

    function __construct()
    {
        parent::__construct();
        $this->service_name = 'Avantlink';
        $this->service_name_long = 'Avantlink Products XML Export';
    }

    function convert_option($option)
    {
        return strtolower(str_replace(" ", "_", $option));
    }

}
