<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);
ob_start(null);

ob_clean();
global $wpdb;

//Fetch: id, title, tally-of-products
$sql = "
			SELECT taxo.term_id as id, term.name as title, taxo.count as tally ,taxo.* , term.*
			FROM $wpdb->term_taxonomy taxo
			LEFT JOIN $wpdb->terms term ON taxo.term_id = term.term_id
			WHERE taxo.taxonomy = 'product_cat'";
$source_categories = $wpdb->get_results($sql);

//convert to objects
$categories = array();
foreach ($source_categories as $a_source_category) {
    $this_category = new stdClass();
    $this_category->id = $a_source_category->id;
    $this_category->title = $a_source_category->title;
    $this_category->tally = $a_source_category->tally;
    $categories[] = $this_category;
}

echo '<pre>';
print_r($categories);
echo '</pre>';
