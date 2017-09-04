<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Created by PhpStorm.
 * User: subash
 * Date: 10/4/16
 * Time: 9:51 AM
 */
class MiintoDlg extends PBaseFeedDialog
{

    function __construct()
    {
        parent::__construct();
        $this->service_name = 'Miinto';
        $this->service_name_long = 'Miinto TSV Export';
    }

    function convert_option($option)
    {
        return strtolower(str_replace(" ", "_", $option));
    }

}