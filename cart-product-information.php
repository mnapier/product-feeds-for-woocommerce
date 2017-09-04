<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
//Checks cart-product-feed version
function CPF_check_version()
{
    //taken from /include/update.php line 270
    $plugin_info = get_site_transient('update_plugins');

    //we want to always display 'up to date', therefore we don't need the below check
    if (!isset($plugin_info->response[CPF_PLUGIN_BASENAME]))
        return ' | You are up to date';

    $CPF_WP_version = $plugin_info->response[CPF_PLUGIN_BASENAME]->new_version; //wordpress repository version
    //version_compare:
    //returns -1 if the first version is lower than the second,
    //0 if they are equal,
    //1 if the second is lower.
    $doUpdate = version_compare($CPF_WP_version, FEED_PLUGIN_VERSION);
    //if current version is older than wordpress repo version
    if ($doUpdate == 1) return ' | <a href=\'plugins.php\'>Out of date - please update</a>';
    //else, up to date
    return ' | You are up to date';
}

function CPF_print_info()
{
    $iconurl = plugins_url('/', __FILE__) . '/images/exf-sm-logo.png';
    $gts_iconurl = plugins_url('/', __FILE__) . '/images/google-customer-review.png';
    echo
        '<div class="exf-logo-header">
		<div class="exf-logo-link">
	 		<a target="_blank" href="http://www.exportfeed.com"><img class="exf-logo-style" src=' . $iconurl . ' alt="shopping cart logo"></a>
	 	</div>
	 	<div class=\'version-style\'>
	 		<a target="_blank" href="http://www.exportfeed.com/woocommerce-product-feed/">Product Site</a> | 
	 		<a target="_blank" href="http://www.exportfeed.com/faq/">FAQ/Help</a> 
	 		'//| <a target="_blank" href="http://www.exportfeed.com/?s=">SEARCH</a> <br>
        . '<br>Version: ' . FEED_PLUGIN_VERSION . CPF_check_version() . '<br>
	 	</div>
	 	<div class="gts-link">
	 		<a target="_blank" href="http://www.exportfeed.com/google-trusted-store-woocommerce/">Get the Google Customer Reviews Plugin<br>Sell More - Be placed 1st!</a>
	 	</div>
	 	<div class="gts-logo-link" >
	 		<a target="_blank" href="http://www.exportfeed.com/google-trusted-store/"><img class="gts-logo-style" src=' . $gts_iconurl . ' alt="google trusted stores"></a>
	 	</div>
	 </div>
	 <div style="clear:both"></div>';
}

function CPF_render_navigation()
{
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'createfeed';
    $tutorials_url = site_url() . '/wp-admin/admin.php?page=cart-product-feed-tutorials-page';
    $url = site_url() . '/wp-admin/admin.php?page=cart-product-feed-manage-page';
    ?>
    <div class="nav-wrapper">
        <nav class="nav-tab-wrapper">
            <a href="<?php echo site_url(); ?>/wp-admin/admin.php?page=cart-product-feed-admin&tab=createfeed"
               class="nav-tab <?php echo $active_tab == 'createfeed' ? 'nav-tab-active ' : ''; ?>"><?php _e('Create Feed', 'cart-product-strings'); ?></a>
            <a href="<?php echo $url; ?>&tab=managefeed"
               class="nav-tab <?php echo $active_tab == 'managefeed' ? 'nav-tab-active ' : ''; ?>"><?php _e('Manage Feed', 'cart-product-strings'); ?></a>
            <a href="http://www.exportfeed.com/contact/"
               target="_blank"
               class="nav-tab <?php echo $active_tab == 'contactus' ? 'nav-tab-active ' : ''; ?>"><?php _e('Contact Us', 'cart-product-strings'); ?></a>
            <a href="<?php echo $tutorials_url; ?>&tab=tutorials"
               class="nav-tab <?php echo $active_tab == 'tutorials' ? 'nav-tab-active ' : ''; ?>"><?php _e('Tutorials', 'cart-product-strings'); ?></a>

            <a href="http://www.exportfeed.com/woocommerce-product-feed/" target="_blank"
               class="nav-tab"><?php _e('Go Pro', 'cart-product-strings'); ?></a>

            <ul class="subsubsub" style="float: right;">
                <li><a href="http://www.exportfeed.com/woocommerce-product-feed/" target="_blank">Go Premium</a> |</li>
                <li><a href="http://www.exportfeed.com/woocommerce-product-feed/" target="_blank">Product Site</a> |
                </li>
                <li><a href="http://www.exportfeed.com/faq/" target="_blank">FAQ/Help</a></li>
            </ul>
        </nav>
    </div>
    <div class="clear"></div>
    <?php
}
