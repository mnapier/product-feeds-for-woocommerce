<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(!is_admin()){
	die('Permission Denied!');
}
require_once dirname(__FILE__) . '/../../classes/amazon.php';


$amazon = new Amazon;
switch ($_POST['action']) {
	case 'cpf_add_account':
			$amazon->addAccount();
		break;

	case 'get_markets':
			$amazon->getMarkets();
		break;
	case 'allowed_markets':
		$allowed_markets = $amazon->getAccountByID($_POST['id'],$_POST['action']);
		$markets = maybe_unserialize($allowed_markets);
		 if ( $markets && is_array( $markets ) ) {
		 	foreach ($markets as $key => $value) {
		 		echo "<i>$value->Name</i><br>";
		 	}
		 	echo '</ol>';
		 }
		break;
	case 'fetch_category':
		echo $amazon->fetchCategories();
		break;
	case 'synchronize_list':


			if (true == $amazon->synchronizeFeedList()) {
				$amazon->feed_report(true);
			}
		break;

	case 'get_feed_result':
	// print_r(wp_upload_dir());
   		$amazon->getFeedSubmissionResult();
		break;
    case 'get_amazon_settings':

        $amazon->getAmazonSettings();

        break;
	case 'set_amazon_settings':
		$amazon->setAmazonSettings();
		break;

	case 'create_template':
		$amazon->createTemplate();
		break;

	case 'delete_user_template':
		$amazon->deleteUserTemplate();
		break;
	default:
		# code...
		break;
}

