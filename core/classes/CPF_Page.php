<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * Created by PhpStorm.
 * User: subash
 * Date: 6/7/16
 * Time: 10:44 AM
 */
/**
 * CPF_Page
 *
 * This class provides methods for single admin pages
 *
 */

// helper class for custom screen options - will be removed in future versions
require_once dirname(__FILE__).'/CPF_Core.php';

class CPF_Page extends CPF_Core {

    public function __construct() {
        parent::__construct();

        self::$PLUGIN_URL = CPF_URL;
        self::$PLUGIN_DIR = CPF_PATH;

        $this->main_admin_menu_label = get_option( 'cpf_admin_menu_label', $this->app_name );
        $this->main_admin_menu_label = $this->main_admin_menu_label ? $this->main_admin_menu_label : $this->app_name;
        $this->main_admin_menu_slug  = sanitize_key( str_replace(' ', '-', $this->main_admin_menu_label ) );

        add_action( 'admin_menu', 			array( &$this, 'onWpAdminMenu' ), 20 );

        if ( is_admin() ) {
            add_action( 'plugins_loaded', 	array( &$this, 'handleSubmit' ) );
        }

    }

    // these methods can be overriden
    public function onWpAdminMenu() {
    }
    public function handleSubmit() {
    }


    // display view
    public function display( $insView, $inaData = array(), $echo = true ) {
        // $sFile = dirname(__FILE__).DS.self::ViewDir.DS.$insView.self::ViewExt;
        $sFile = WPLISTER_PATH . '/views/' . $insView . '.php';

        if ( !is_file( $sFile ) ) {
            $this->showMessage("View not found: ".$sFile,1,1);
            return false;
        }

        if ( count( $inaData ) > 0 ) {
            extract( $inaData, EXTR_PREFIX_ALL, 'wpl' );
        }

        ob_start();
        include( $sFile );
        $sContents = ob_get_contents();
        ob_end_clean();

        // change admin footer on wplister pages
        if ( apply_filters( 'wplister_enable_admin_footer', true ) ) {
            add_filter('admin_footer_text', array( &$this, 'change_admin_footer_text') );
            add_filter('update_footer', array( &$this, 'change_admin_footer_version') );
        }

        // MOVED to wp-lister.php
        // fix thickbox display problems caused by other plugins
        // like woocommerce-pip which enqueues media-upload on every admin page
        // if ( did_action( 'init' ) ) wp_dequeue_script( 'media-upload' );

        // filter content before output
        $sContents = apply_filters( 'wplister_admin_page_content', $sContents );

        if ($echo) {
            echo $sContents;
            return true;
        } else {
            return $sContents;
        }

    }




    // deprecated
    function get_plugin_version() {

        return WPLE_VERSION;

        // if ( ! function_exists( 'get_plugins' ) )
        // 	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        // // $plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
        // // $plugin_file = basename( ( __FILE__ ) );
        // $plugin_folder = get_plugins( '/' . plugin_basename( WPLISTER_PATH ) );
        // $plugin_file = 'wp-lister.php';

        // return $plugin_folder[$plugin_file]['Version'];
    }

    function get_i8n_html( $basename )	{
        if ( empty( $basename ) ) return false;
        if ( ! defined( 'WPLANG' ) ) define( 'WPLANG', 'en_US' );

        $lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : substr( WPLANG, 0, 2 ); // WPML COMPATIBILITY
        $lang_folder = trailingslashit(WPLISTER_PATH) . 'views/lang/';

        $default_file    = $lang_folder . $basename . '_en.html';
        $translated_file = $lang_folder . $basename . '_' . $lang . '.html';

        $file = file_exists( $translated_file ) ? $translated_file : $default_file;
        if ( is_readable( $file ) ) return file_get_contents( $file );

        $this->showMessage('file not found: '.$file,1,1);

        return false;
    }

    function check_wplister_setup( $page = false )	{
        $Setup = new WPL_Setup();
        $Setup->checkSetup( $page );
    }


}
