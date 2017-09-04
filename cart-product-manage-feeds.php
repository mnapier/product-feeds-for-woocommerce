<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/********************************************************************
 * Manage Feed Xmls
 * Version 1.2
 * Moved Update/Refresh time here
 * By: Keneto 2014-05-07
 ********************************************************************/
global $cp_feed_order, $cp_feed_order_reverse;
require_once 'core/classes/dialogfeedsettings.php';
require_once 'core/data/savedfeed.php';

?>
    <div class="wrap">
        <!-- <?php $iconurl = plugins_url('/', __FILE__) . '/images/cp_feed32.png'; ?>
    <div id="icon-purple_feed" class="icon32" style="background: transparent url( <?php echo($iconurl); ?> ) no-repeat">
        <br />

    </div>
    -->

        <h2>
            <?php

            if ('eBay_settings_tabs' != $_GET['page']){
            _e('Manage Cart Product Feeds', 'cart-product-strings');
            $url = site_url() . '/wp-admin/admin.php?page=cart-product-feed-admin';
            echo '<input style="margin-top:12px;" type="button" class="add-new-h2" onclick="document.location=\'' . $url . '\';" value="' . __('Generate New Feed', 'cart-product-strings') . '" />';
            ?>
        </h2>
        <?php CPF_print_info(); ?>
        <?php CPF_render_navigation(); ?>
        <?php } ?>


        <?php
        $message = NULL;
        // check if wp-cron is enabled
        if (defined('DISABLE_WP_CRON') && (DISABLE_WP_CRON == true)){
            $message = '<span style="color:green;font-weight: bold">WordPress Cron is disabled. Set your Cron on server to update feeds.</span>';
            $message .= '<ol>';
            $message .= '<li>Log in to your hosting cpanel using your username and password.</li>';
            $message .= '<li>When you log into your cpanel you will see an option for cron jobs or scheduled tasks.</li>';
            $message .= '<li>Under the Common Settings, select <strong>Twice Per Hour</strong> to run cron every 30 minutes.</li>';
            $message .= '<li>Add Cron Command to Run as: <strong>wget -O /dev/null '.site_url('wp-cron.php').'</strong></li>';
            $message .= '<li>Click on <strong>Add New Cron Job</strong>, and then you are all ready.</li>';
            $message .= '</ol>';
            $message .= '<span style="color:green;font-weight: bold">If you have any confusion you can check our documentation. <a href="http://www.exportfeed.com/documentation/install-shoppingcartproductfeed-wordpress-plugin/" target="_blank">Click here</a> </span>';
        }

        // check for delete ID
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            if ($action == "delete") {
                if (isset($_GET['id'])) {
                    $delete_id = $_GET['id'];
                    $message = cart_product_feed_delete_feed($delete_id);
                }
            }
        }


        if ($message) {
            echo '<div id="setting-error-settings_updated" class="updated settings-error">
               <p>' . $message . '</p></div>';
        }
        //"New Feed" button
            $url = site_url() . '/wp-admin/admin.php?page=cart-product-feed-admin';
        ?>

        <br/>
        <?php
        echo '
        <script type="text/javascript">
        jQuery( document ).ready( function( $ ) {
           ajaxhost = "' . plugins_url('/', __FILE__) . '";
        } );
        </script>';
        if ($_GET['page'] != 'eBay_settings_tabs') {
            echo PFeedSettingsDialogs::refreshTimeOutDialog();
            echo PFeedSettingsDialogs::filterProductDialog();
        }
        // The table of existing feeds
        feeds_main_table();
        ?>
        <br/>
    </div>
<?php

// The feeds table flat
function feeds_main_table()
{

    global $wpdb;

    $feed_table = $wpdb->prefix . 'cp_feeds';
    $providerList = new PProviderList();

    // Read the feeds
    $sql_feeds = ("SELECT f.*,description FROM $feed_table as f LEFT JOIN $wpdb->term_taxonomy on ( f.category=term_id and taxonomy='product_cat'  ) ORDER BY f.id");

    $list_of_feeds = $wpdb->get_results($sql_feeds, ARRAY_A);
    // Find the ordering method
    $reverse = false;
    if (isset($_GET['order_by']))
        $order = $_GET['order_by'];
    else
        $order = '';
    if ($order == '') {
        $order = get_option('cp_feed_order');
        $reverse = get_option('cp_feed_order_reverse');
    } else {
        $old_order = get_option('cp_feed_order');
        $reverse = get_option('cp_feed_order_reverse');
        if ($old_order == $order) {
            $reverse = !$reverse;
        } else {
            $reverse = FALSE;
        }
        update_option('cp_feed_order', $order);
        if ($reverse)
            update_option('cp_feed_order_reverse', TRUE);
        else
            update_option('cp_feed_order_reverse', FALSE);
    }

    if (!empty($list_of_feeds)) {

        // Setup the sequence array
        $seq = false;
        $num = false;
        foreach ($list_of_feeds as $this_feed) {
            $this_feed_ex = new PSavedFeed($this_feed['id']);
            switch ($order) {
                case 'name':
                    $seq[] = strtolower(stripslashes($this_feed['filename']));
                    break;
                case 'description':
                    $seq[] = strtolower(stripslashes($this_feed_ex->local_category));
                    break;
                case 'url':
                    $seq[] = strtolower($this_feed['url']);
                    break;
                case 'category':
                    $seq[] = $this_feed['category'];
                    $num = true;
                    break;
                case 'google_category':
                    $seq[] = $this_feed['remote_category'];
                    break;
                case 'type':
                    $seq[] = $this_feed['type'];
                    break;
                default:
                    $seq[] = $this_feed['id'];
                    $num = true;
                    break;
            }
        }

        // Sort the seq array
        if ($num)
            asort($seq, SORT_NUMERIC);
        else
            asort($seq, SORT_REGULAR);

        // Reverse ?
        if ($reverse) {
            $t = $seq;
            $c = count($t);
            $tmp = array_keys($t);
            $seq = false;
            for ($i = $c - 1; $i >= 0; $i--) {
                $seq[$tmp[$i]] = '0';
            }
        }

        $image['down_arrow'] = '<img src="' . CPF_URL . 'images/down.png" alt="down" style=" height:12px; position:relative; top:2px; " />';
        $image['up_arrow'] = '<img src="' . CPF_URL . 'images/down.png" alt="up" style=" height:12px; position:relative; top:2px; " />';
        ?>
        <!--	<div class="table_wrapper">	-->
        <input class="button-primary" type="submit" value="Update Now" onclick="doUpdateAllFeeds(this)">
        <div class="update-message">&nbsp;</div>
        <table class="widefat" style="margin-top:12px;" id="cpf_manage_table_originals">
            <thead>
            <tr>
                <?php $url = get_admin_url() . 'admin.php?page=cart-product-feed-manage-page&amp;order_by='; ?>
                <th scope="col" style="min-width: 40px;padding-left: 2px;"><input type="checkbox"
                                                                                  id="cpf_select_all_feed"
                                                                                  onclick="cpf_check_all_feeds(this);"/>
                </th>
                <th scope="col" style="min-width: 40px;">
                    <a href="<?php echo $url . "id" ?>">
                        <?php
                        _e('ID', 'cart-product-strings');
                        if ($order == 'id') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="min-width: 120px;">
                    <a href="<?php echo $url . "name" ?>">
                        <?php
                        _e('Name', 'cart-product-strings');
                        if ($order == 'name') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col">
                    <a href="<?php echo $url . "category" ?>">
                        <?php
                        _e('Local category', 'cart-product-strings');
                        if ($order == 'category') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="min-width: 100px;">
                    <a href="<?php echo $url . "google_category" ?>">
                        <?php
                        _e('Export category', 'cart-product-strings');
                        if ($order == 'google_category') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="min-width: 50px;">
                    <a href="<?php echo $url . "type" ?>">
                        <?php
                        _e('Type', 'cart-product-strings');
                        if ($order == 'type') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="width: 120px;">
                    <a href="<?php echo $url . "url" ?>">
                        <?php
                        _e('URL', 'cart-product-strings');
                        if ($order == 'url') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="width: 80px;"><?php _e('Last Updated', 'cart-product-strings'); ?></th>
                <!-- <th scope="col" width="50px"><?php //_e( 'View', 'cart-product-strings' ); ?></th> -->
                <!-- <th scope="col" width="50px"><?php _e('Options', 'cart-product-strings'); ?></th> -->
                <!-- <th scope="col" width="50px"><?php //_e( 'Delete', 'cart-product-strings' ); ?></th> -->
                <th scope="col"><?php _e('Products', 'cart-product-strings'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $alt = ' class="alternate" '; ?>

            <?php
            $idx = '0';
            foreach (array_keys($seq) as $s) {
                $this_feed = $list_of_feeds[$s];
                $this_feed_ex = new PSavedFeed($this_feed['id']);
                $pendcount = FALSE;
                ?>
                <tr <?php
                echo($alt);
                if ($pendcount)
                    echo 'style="background-color:#ffdddd"'
                ?>>
                    <td><input type="checkbox" class="cpf_select_feed"/></td>
                    <td><?php echo $this_feed['id']; ?></td>
                    <td><?php echo $this_feed['filename']; ?>
                        <input type="hidden" class="cpf_hidden_feed_id" value="<?php echo $this_feed['id']; ?>"/>
                        <div class="row-actions"><span class="id">ID: <?php echo $this_feed['id']; ?> | </span>
                            <span class="purple_xmlsedit"><a href="<?php echo $this_feed['url'] ?>" target="_blank"
                                                             title="View this Feed" rel="permalink">View</a>|</span>
                            <?php $url_edit = get_admin_url() . 'admin.php?page=cart-product-feed-admin&action=edit&id=' . $this_feed['id'] . '&feed_type=' . $this_feed['feed_type']; ?>
                            <span class="purple_xmlsedit"><a href="<?php echo($url_edit) ?>" target="_blank"
                                                             title="Edit this Feed" rel="permalink">Edit</a>|</span>
                            <?php $url = get_admin_url() . 'admin.php?page=cart-product-feed-manage-page&action=delete&id=' . $this_feed['id']; ?>
                            <span class="delete"><a href="<?php echo($url) ?>"
                                                    title="Delete this Feed">Delete</a>|</span>
                            <?php
                            if ($this_feed['type'] == "Etsy") {
                                $upload_url = get_admin_url() . 'admin.php?page=cart-product-feed-admin&action=etsy_upload&id=' . $this_feed['id'];
                            } elseif ($this_feed['type'] == "AmazonMWS") {
                                $upload_url = get_admin_url() . 'admin.php?page=cart-product-feed-admin&action=amazonmws_upload&id=' . $this_feed['id'];
                                ?>
                                <span class="etsy_upload">
                                    <a href="<?php echo $upload_url; ?>" title="Upload this to Etsy Store"
                                       rel="permalink"> Upload</a>
                                </span>
                            <?php } ?>
                        </div>
                    </td>
                    <td>
                        <small><?php echo esc_attr(stripslashes($this_feed_ex->local_category)) ?></small>
                    </td>
                    <td><?php $count_str = strlen(str_replace(".and.", " & ", str_replace(".in.", " > ", esc_attr(stripslashes($this_feed['remote_category'])))));
                        $concat = "";
                        if ($count_str > 200) {
                            $concat = "...";
                        }
                        echo substr(str_replace(".and.", " & ", str_replace(".in.", " > ", esc_attr(stripslashes($this_feed['remote_category'])))), 0, 200) . $concat; ?>
                    </td>
                    <td><?php echo $providerList->getPrettyNameByType($this_feed['type']) ?></td>
                    <td><?php echo $this_feed['url'] ?></td>
                    <?php //$url = get_admin_url() . 'admin.php?page=??? ( edit feed ) &amp;tab=edit&amp;edit_id=' . $this_feed['id']; ?>
                    <td><?php
                        $ext = '.' . $providerList->getExtensionByType($this_feed['type']);
                        $feed_file = PFeedFolder::uploadFolder() . $this_feed['type'] . '/' . $this_feed['filename'] . $ext;
                        if (file_exists($feed_file)) {
                            echo date("d-m-Y H:i:s", filemtime($feed_file));
                        } else echo 'DNE';
                        ?></td>

                    <!--  <td><a href="<?php echo $this_feed['url'] ?>" target="_blank" class="purple_xmlsedit"><?php _e('View', 'cart-product-strings'); ?></a></td>
						<?php $url_edit = get_admin_url() . 'admin.php?page=cart-product-feed-admin&action=edit&id=' . $this_feed['id']; ?>
						<td><a href="<?php echo($url_edit) ?>" class="purple_xmlsedit"><?php _e('Edit', 'cart-product-strings'); ?></a></td>

                        <?php // ST - start ?>
                        <?php if ($this_feed['type'] == 'Etsy') {
                        $url_upload = get_admin_url() . 'admin.php?page=cart-product-feed-admin&action=upload&id=' . $this_feed['id']; ?>
                        <td><a href="<?php echo($url_upload) ?>" class="purple_xmlsedit"><?php _e('Upload', 'cart-product-strings'); ?></a></td>
                        <?php } ?>
                        <?php // ST - end ?>

                        <?php $url = get_admin_url() . 'admin.php?page=cart-product-feed-manage-page&action=delete&id=' . $this_feed['id']; ?>
                        <td><a href="<?php echo($url) ?>" class="purple_xmlsedit"><?php _e('Delete', 'cart-product-strings'); ?></a></td>
                        <?php if ($this_feed['type'] == "eBaySeller") : ?>
                            <?php $upload_url = get_admin_url() . 'admin.php?page=cart-product-feed-admin&action=uploadFeed&id=' . $this_feed['id']; ?>
                            <td><a href="<?php echo($upload_url) ?>" class="purple_xmlsedit"><?php _e('Upload', 'cart-product-strings'); ?></a></td>
                        <?php endif; ?>     -->
                    <td><?php echo $this_feed['product_count'] ?></td>

                </tr>
                <?php
                if ($alt == '') {
                    $alt = ' class="alternate" ';
                } else {
                    $alt = '';
                }

                $idx++;
            }
            ?>
            </tbody>
            <tfoot>
            <tr>
                <?php
                $url = get_admin_url() . 'admin.php?page=cart-product-manage-page&amp;order_by=';
                $order = '';
                ?>
                <th scope="col" style="min-width: 40px;padding-left: 2px;"><input type="checkbox"
                                                                                  id="cpf_select_all_feed_1"
                                                                                  onclick="cpf_check_all_feeds_1(this);"/>
                </th>
                <th scope="col" style="min-width: 40px;">
                    <a href="<?php echo $url . "id" ?>">
                        <?php
                        _e('ID', 'cart-product-strings');
                        if ($order == 'id') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="min-width: 120px;">
                    <a href="<?php echo $url . "name" ?>">
                        <?php
                        _e('Name', 'cart-product-strings');
                        if ($order == 'name') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col">
                    <a href="<?php echo $url . "category" ?>">
                        <?php
                        _e('Local Category', 'cart-product-strings');
                        if ($order == 'category') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="min-width: 100px;">
                    <a href="<?php echo $url . "google_category" ?>">
                        <?php
                        _e('Export category', 'cart-product-strings');
                        if ($order == 'google_category') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="min-width: 50px;">
                    <a href="<?php echo $url . "type" ?>">
                        <?php
                        _e('Type', 'cart-product-strings');
                        if ($order == 'type') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="width: 120px;">
                    <a href="<?php echo $url . "url" ?>">
                        <?php
                        _e('URL', 'cart-product-strings');
                        if ($order == 'url') {
                            if ($reverse)
                                echo $image['up_arrow'];
                            else
                                echo $image['down_arrow'];
                        }
                        ?>
                    </a>
                </th>
                <th scope="col" style="width: 80px;"><?php _e('Last Updated', 'cart-product-strings'); ?></th>
                <!--  <th scope="col"><?php //_e( 'View', 'cart-product-strings' ); ?></th> -->
                <!-- <th scope="col"><?php _e('Options', 'cart-product-strings'); ?></th> -->
                <!-- <th scope="col"><?php //_e( 'Delete', 'cart-product-strings' ); ?></th> -->
                <th scope="col"><?php _e('Products', 'cart-product-strings'); ?></th>
            </tr>
            </tfoot>

        </table>

        <input class="button-primary" type="submit" value="Update Now" onclick="doUpdateAllFeeds(this)">
        <div class="update-message">&nbsp;</div>
        <!--	</div> -->
        <?php
    } else {
        ?>
        <p><?php _e('No feeds yet!', 'cart-product-strings'); ?></p>
        <?php
    }
}

function cart_product_feed_delete_feed($delete_id = NULL)
{
    // Delete a Feed
    global $wpdb;
    $feed_table = $wpdb->prefix . 'cp_feeds';
    $sql_feeds = ("SELECT * FROM $feed_table where id=$delete_id");
    $list_of_feeds = $wpdb->get_results($sql_feeds, ARRAY_A);

    if (isset($list_of_feeds[0])) {
        $this_feed = $list_of_feeds[0];
        $ext = '.xml';
        if (strpos(strtolower($this_feed['url']), '.csv') > 0) {
            $ext = '.csv';
        }
        $upload_dir = wp_upload_dir();
        $feed_file = $upload_dir['basedir'] . '/cart_product_feeds/' . $this_feed['type'] . '/' . $this_feed['filename'] . $ext;

        if (file_exists($feed_file)) {
            unlink($feed_file);
        }
        $wpdb->query("DELETE FROM $feed_table where id=$delete_id");
        return "Feed deleted successfully!";
    }
}