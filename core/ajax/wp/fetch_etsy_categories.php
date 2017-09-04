<?php

/********************************************************************
 * Version 2.0
 * Go get the category
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto 2014-05-18
 ********************************************************************/
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);


require_once dirname(__FILE__) . '/../../data/feedcore.php';
require_once dirname(__FILE__) . '/../../classes/etsyclient.php';
// echo plugins_url('../classes/etsyclient.php',dirname(__FILE__));

$service_name = $_POST['service_name'];

$data = '';
if (class_exists('CPF_Taxonomy'))
    $data = CPF_Taxonomy::onLoadTaxonomy(strtolower($_POST['service_name']));

/*if (strlen($data) == 0)
    $data = file_get_contents(dirname(__FILE__) . '/../../feeds/' . strtolower($service_name) . '/categories.txt');

$data = explode("\n", $data);*/
$searchTerm = strtolower($_POST['partial_data']);
$count = 0;
$canDisplay = true;
$id = get_current_user_id();
if ($id == 0) {
    $id = $_POST['service_status'];
}

$etsy = new Etsy($id);
switch ($_POST['level']) {

    case '1':
        $etsy->fetchEtsyCategories();
        break;

    case '2':
        $etsy->shippingTemplate();
        break;

    case '3':
        $etsy->timeToUpload();
        break;

    case '4':
        $etsy->deleteShipping();
        break;

    case '5':
        $etsy->createShippingTemplate();
        break;

    case '6':
        $etsy->makeDefaultShipping();
        break;

    case '7':
        $etsy->makeDefaultShop();
        break;

    case '8':
        $etsy->shippingTemplate();
        break;

    case '9':
        $etsy->changeConfigurations();
        break;
    case '10':
        $etsy->deleteAccount();
        break;
    default:

        # code...
        break;
}
