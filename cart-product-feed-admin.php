<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/********************************************************************
 * Version 1.2
 * Modified: 2014-05-01 Now Product Categories can export to both XML and TXT ( CSV or Tabbed )
 * Copyright 2015 WRI HK LTD. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Keneto
 ********************************************************************/


/**
 * Required admin files
 *
 */
require_once 'cart-product-setup.php';

/**
 * Hooks for adding admin specific styles and scripts
 *
 */
function register_cart_product_styles_and_scripts( $hook ) {
	if ( ! strchr( $hook, 'cart-product-feed' ) ) {
		return;
	}

	wp_register_style( 'cart-product-style', plugins_url( 'css/cart-product.css', __FILE__ ), '', FEED_PLUGIN_VERSION );
	wp_enqueue_style( 'cart-product-style' );

	wp_register_style( 'cart-product-colorstyle', plugins_url( 'css/colorbox.css', __FILE__ ), '', FEED_PLUGIN_VERSION );
	wp_enqueue_style( 'cart-product-colorstyle' );

	wp_register_script( 'cart-product-script', plugins_url( 'js/cart-product.js', __FILE__ ), array( 'jquery' ), false );
	wp_enqueue_script( 'cart-product-script' );

	wp_register_script( 'cart-product-colorbox', plugins_url( 'js/jquery.colorbox-min.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script( 'cart-product-colorbox' );

    wp_localize_script( 'cart-product-script', 'cpf', [
        'cpf_nonce' 				=> wp_create_nonce('cpf_nonce'),
        'action'    				=> 'cpf_cart_product'
    ] );

	wp_register_script( 'cart-product-autocomplete', plugins_url( 'js/jquery-ui.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script( 'cart-product-autocomplete' );

}

/*
 * ajax handles
 * */
if(isset($_REQUEST['action']) && ($_REQUEST['action'] == 'cpf_cart_product')){
    add_action('wp_ajax_cpf_cart_product','cpf_all_ajax_handles');
}

add_action( 'admin_enqueue_scripts', 'register_cart_product_styles_and_scripts' );


/*
 * ajax handle function
 * */
function cpf_all_ajax_handles(){
    $nonce = sanitize_text_field($_REQUEST['security']);
    if (!wp_verify_nonce($nonce,'cpf_nonce')){
        die('Permission denied');
    } else {
        $feedpath = $_REQUEST['feedpath'];
        include_once plugin_dir_path(__FILE__).$feedpath;
    }
    die;
}

/**
 * Add menu items to the admin
 *
 */
function cart_product_admin_menu() {

	/* add new top level */
	add_menu_page(
		__( 'Product Feed', 'cart-product-strings' ),
		__( 'Product Feed', 'cart-product-strings' ),
		'manage_options',
		'cart-product-feed-admin',
		'cart_product_feed_admin_page',
		plugins_url( '/', __FILE__ ) . '/images/xml-icon.png'
	);

	/* add the submenus */
	add_submenu_page(
		'cart-product-feed-admin',
		__( 'Create New Feed', 'cart-product-strings' ),
		__( 'Create New Feed', 'cart-product-strings' ),
		'manage_options',
		'cart-product-feed-admin',
		'cart_product_feed_admin_page'
	);

	add_submenu_page(
		'cart-product-feed-admin',
		__( 'Manage Feeds', 'cart-product-strings' ),
		__( 'Manage Feeds', 'cart-product-strings' ),
		'manage_options',
		'cart-product-feed-manage-page',
		'cart_product_feed_manage_page'
	);

	add_submenu_page(
		'cart-product-feed-admin',
		__( 'Tutorials', 'cart-product-strings' ),
		__( 'Tutorials', 'cart-product-strings' ),
		'manage_options',
		'cart-product-feed-tutorials-page',
		'cart_product_feed_tutorials_page'
	);
}

add_action( 'admin_menu', 'cart_product_admin_menu' );
add_action( 'cpf_init_pageview', 'cart_product_feed_admin_page_action' );
add_action( 'wp_enqueue_scripts', 'wpb_adding_scripts' );

function wpd_adding_scripts() {

}

function cart_product_feed_admin_page() {

	require_once 'cart-product-wpincludes.php';
	include_once 'core/classes/dialogfeedpage.php';
	require_once 'core/feeds/basicfeed.php';

	global $pfcore;
	$pfcore->trigger( 'cpf_init_feeds' );

	do_action( 'cpf_init_pageview' );
}

//include_once('cart-product-version-check.php');
/**
 * Create news feed page
 */
function cart_product_feed_admin_page_action() {

	echo "<div class='wrap'>";
	//echo 	"<div class='cpf-header'>";
	echo '<h2>Create Product Feed';
	$url         = site_url() . '/wp-admin/admin.php?page=cart-product-feed-manage-page';
	echo '<input style="margin-top:12px;" type="button" class="add-new-h2" onclick="document.location=\'' . $url . '\';" value="' . __( 'Manage Feeds', 'cart-product-strings' ) . '" />
    </h2>';
	//echo    '</div>';
	//prints logo/links header info: also version number/check
	CPF_print_info();
	//prints navigation bar
	CPF_render_navigation($url);

	$action         = '';
	$source_feed_id = - 1;
	$feed_type      = - 1;

	$message2    = null;

	//check action
	if ( isset( $_POST['action'] ) ) {
		$action = $_POST['action'];
	}
	if ( isset( $_GET['action'] ) ) {
		$action = $_GET['action'];
	}

	switch ( $action ) {
		case 'reset_attributes':
			//I don't think this is used -K
			global $wpdb, $woocommerce;
			$attr_table = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
			$sql        = "SELECT attribute_name FROM " . $attr_table . " WHERE 1";
			$attributes = $wpdb->get_results( $sql );
			foreach ( $attributes as $attr ) {
				delete_option( $attr->attribute_name );
			}
			break;
		case 'edit':
			$action         = '';
			$source_feed_id = $_GET['id'];
			$feed_type      = isset( $_GET['feed_type'] ) ? $_GET['feed_type'] : '';
			break;
	}

	if ( isset( $action ) && ( strlen( $action ) > 0 ) ) {
		echo "<script> window.location.assign( '" . admin_url() . "admin.php?page=cart-product-feed-admin' );</script>";
	}

	if ( isset( $_GET['debug'] ) ) {
		$debug = $_GET['debug'];
		if ( $debug == 'phpinfo' ) {
			phpinfo( INFO_GENERAL + INFO_CONFIGURATION + INFO_MODULES );

			return;
		}
	}

	# Get Variables from storage ( retrieve from wherever it's stored - DB, file, etc... )

	global $wpdb;


	//Main content
	echo '
	<script type="text/javascript">
    jQuery( document ).ready( function( $ ) {
        ajaxhost = "' . plugins_url( '/', __FILE__ ) . '";
        jQuery( "#selectFeedType" ).val( "&nbsp;" );
        doFetchLocalCategories();
        doFetchLocalCategories_custom();
        feed_id = ' . $source_feed_id . ';
        window.feed_type = ' . $feed_type . ' ;
        if(feed_id > 0  && feed_type == 1){
            showSelectedProductTables(feed_id);
            saveTocustomTable(feed_id);
        }
    });
    </script>';

	//WordPress Header ( May contain a message )

	global $message;
	$installtion_date = get_option('cart-product-feed-installation-date');
	$add_days = 4;
	$fourth_date_of_installation =  date('Y-m-d', strtotime($installtion_date .' +'.$add_days.' days'));
	$now = date('Y-m-d');
	if($now == $fourth_date_of_installation)
		$message = 'Are you stuck on feed setup? We will create a complimentary feed according to your needs. Contact us for more details. <a target=\'_blank\' href = \'http://www.exportfeed.com/contact/\'>exportfeed.com</a>';

	if ( strlen( $message ) > 0 ) {
		$message .= '<br>';
	} //insert break after local message (if present)

	if ( strlen( $message ) > 0 ) {
		//echo '<div id="setting-error-settings_updated" class="error settings-error">'
		echo '<div id="setting-error-settings_updated" class="updated settings-error">
			  <p>' . $message . '</p>
			  </div>';
	}

	if ( $source_feed_id == - 1 ) {
		$wpdb->query( "TRUNCATE {$wpdb->prefix}cpf_custom_products" );
		//Page Header
		echo PFeedPageDialogs::pageHeader();
		//Page Body
		echo PFeedPageDialogs::pageBody();
	} else {
		require_once dirname( __FILE__ ) . '/core/classes/dialogeditfeed.php';
		echo PEditFeedDialog::pageBody( $source_feed_id, $feed_type );
	}

}

/**
 * Display the manage feed page
 *
 */

add_action( 'cpf_init_pageview_manage', 'cart_product_feed_manage_page_action' );
add_action( 'cpf_init_pageview_tutorails', 'cart_product_feed_tutorials_page_action' );

function cart_product_feed_manage_page() {

	require_once 'cart-product-wpincludes.php';
	include_once 'core/classes/dialogfeedpage.php';

	global $pfcore;
	$pfcore->trigger( 'cpf_init_feeds' );

	do_action( 'cpf_init_pageview_manage' );

}

function cart_product_feed_tutorials_page() {
	do_action( 'cpf_init_pageview_tutorails' );
}

function cart_product_feed_tutorials_page_action() {

	echo "<div class='wrap'>";
	//prints logo/links header info: also version number/check
	CPF_print_info();
	CPF_render_navigation();
	$_GET['tab'] = "tutorials";
	require_once 'cart-product-feed-tutorials-page.php';
}

function cart_product_feed_manage_page_action() {
	$_GET['tab'] = "managefeed";
	require_once 'cart-product-manage-feeds.php';
}
