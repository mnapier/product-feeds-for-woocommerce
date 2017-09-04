<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!is_admin()) {
    die('Permission Denied!');
}
$map_string = get_option('cpf_attribute_user_map_' . $_POST['service_name']);

if (strlen($map_string) == 0)
    $map = array();
else {
    $map = json_decode($map_string);
    $map = get_object_vars($map);
}

$attr = $_POST['attribute'];
$mapto = $_POST['mapto'];
$map[$mapto] = $attr;

if ($attr == '(Reset)') {
    $new_map = array();
    foreach ($map as $index => $item)
        if ($index != $mapto)
            $new_map[$index] = $item;
    $map = $new_map;
}

update_option('cpf_attribute_user_map_' . $_POST['service_name'], json_encode($map));
