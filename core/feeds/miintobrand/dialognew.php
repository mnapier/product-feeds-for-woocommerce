<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Created by PhpStorm.
 * User: sushma
 * Date: 23/5/17
 * Time: 9:51 AM
 */

class MiintoBrandDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'MiintoBrand';
		$this->service_name_long = 'Miinto Brand TSV Export';
	}

	function convert_option($option) {
		return strtolower(str_replace(" ", "_", $option));
	}

}