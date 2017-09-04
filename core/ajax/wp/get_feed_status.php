<?php
/********************************************************************
 * Version 2.0
 * Get a feed's generation Status
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-07-02
 ********************************************************************/
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);
ob_start(null);

function safeGetPostData($index)
{
    if (isset($_POST[$index]))
        return $_POST[$index];
    else
        return '';
}

$feedIdentifier = safeGetPostData('feed_identifier');

ob_clean();
echo get_option('cp_feedActivity_' . $feedIdentifier);