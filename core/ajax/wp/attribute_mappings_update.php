<?php
/********************************************************************
 * Version 2.0
 * Save a change in attribute mappings
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-05-13
 * 2014-11 Note: This format is possibly to be phased out in favour of attribute_user_map
 ********************************************************************/
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
update_option($_POST['service_name'] . '_cp_' . $_POST['attribute'], $_POST['mapto']);
