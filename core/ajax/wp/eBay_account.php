<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(!is_admin()){
    die('Permission Denied!');
}
define('XMLRPC_REQUEST', true);
//echo dirname(__FILE__) . '../';

define('CPF_URL', plugins_url() . '/' . basename(dirname(__FILE__)) . '/');
//require_once (CPF_URL . 'core/model/eBayAccount.php');
global $wpdb;
$account_id = $_POST['account_id'];
$result = array();
$status = false;

//$wpdb->update($table, $data, $where, $format = null, $where_format = null);

$tableName = 'ebay_accounts';
$table = $wpdb->prefix . $tableName;

//first find the default account
$default_account = $wpdb->get_var("
			SELECT id
			FROM $table
                        WHERE default_account = 1"
);

//Set data for new default account
$data = array(
    'default_account' => 1
);

$where = array(
    'id' => $account_id
);

//set data for previously default account
$data1 = array(
    'default_account' => 0
);
$where1 = array(
    'id' => $default_account
);


if (($wpdb->update($table, $data, $where))) {
    $wpdb->update($table, $data1, $where1);
    $status = true;
}

if ($status) {
    $result = array(
        'msg' => $wpdb->last_error,
        'status' => true
    );
} else {
    $result = array(
        'msg' => $wpdb->last_error,
        'status' => false,
    );
}

//echo $wpdb->last_error;
// echo "<pre>";print_r($wpdb->last_query);echo"</pre>";#die();
// return $wpdb->insert_id;
echo json_encode($result);


