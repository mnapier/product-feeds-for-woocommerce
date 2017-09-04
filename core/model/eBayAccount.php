<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * WPLE_eBayAccount class
 *
 */
class CPF_eBayAccount
{

    const TABLENAME = 'ebay_accounts';

    var $id;
    var $title;
    var $site_id;
    var $site_code;
    public $S_Page;
    public $EC;

    function __construct($id = null)
    {

        $this->init();

        if ($id) {
            $this->id = $id;

            $account = $this->getAccount($id);
            if (!$account)
                return false; // this doesn't actually return an empty object - why?
            // load data into object
            foreach ($account AS $key => $value) {
                $this->$key = $value;
            }

            return $this;
        }
    }

    function init()
    {
        // global $wpl_logger;
        // $this->logger = &$wpl_logger;

        $this->fieldnames = array(
            'title',
            'site_id',
            'site_code',
            'active',
            'sandbox_mode',
            'token',
            'user_name',
            'user_details',
            'valid_until',
            'ebay_motors',
            'oosc_mode',
            'seller_profiles',
            'shipping_profiles',
            'payment_profiles',
            'return_profiles',
            'shipping_discount_profiles',
            'categories_map_ebay',
            'categories_map_store',
            'default_ebay_category_id',
            'paypal_email',
            'sync_orders',
            'sync_products',
            'last_orders_sync',
            'default_account',
        );
    }

    // get single account
    static function getAccount($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLENAME;

        $item = $wpdb->get_row($wpdb->prepare("
			SELECT *
			FROM $table
			WHERE id = %d
		", $id
        ), OBJECT);

        // $item->allowed_sites = maybe_unserialize( $item->allowed_sites );
        return $item;
    }

    // get all accounts
    static function getAll($include_inactive = false, $sort_by_id = false)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLENAME;
	// return if DB has not been initialized yet
      

        $where_sql = $include_inactive ? '' : 'WHERE active = 1';
        $order_sql = $sort_by_id ? '' : 'ORDER BY title ASC';
        $items = $wpdb->get_results("
			SELECT *
			FROM $table
			$where_sql
			$order_sql
		", OBJECT_K);
	 return $items;
    }

    // get account title
    static function getAccountTitle($id)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLENAME;

        $account_title = $wpdb->get_var($wpdb->prepare("
			SELECT title
			FROM $table
			WHERE id = %d
		", $id));
        return $account_title;
    }

    // get this account's site
    static function getDefaultAccount()
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLENAME;

        $default_account = $wpdb->get_var("
			SELECT id
			FROM $table
            WHERE default_account = 1");
        return $default_account;
    }

    // save account

    function getSite()
    {
        // return WPLA_AmazonSite::getSite( $this->site_id );
    }

    // update feed

    function add()
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLENAME;

        $data = array();
        $data['user_details'] = ''; // fix rare "Field 'user_details' doesn't have a default value" error on some MySQL servers
        $data['shipping_profiles'] = '';
        $data['payment_profiles'] = '';
        $data['return_profiles'] = '';
        $data['shipping_discount_profiles'] = '';
        $data['categories_map_ebay'] = '';
        $data['categories_map_store'] = '';

        foreach ($this->fieldnames as $key) {
            if (isset($this->$key)) {
                $data[$key] = $this->$key;
            }
        }

        if (sizeof($data) > 0) {
            $result = $wpdb->insert($table, $data);

            if (!$wpdb->insert_id) {
                $this->error_message = 'There was a problem adding your account. MySQL said: ' . $wpdb->last_error;
            }

            $this->id = $wpdb->insert_id;
            return $wpdb->insert_id;
        }
    }

    function updateUserDetails()
    {
        if (!$this->id)
            return;
        global $EC;
        // update token expiration date
        $EC->initEC($this->id);
        //$this->EC->initLogger();
        $expdate = $EC->GetTokenStatus(true);
        $EC->closeEbay();
        if ($expdate) {
            $this->valid_until = $expdate;
            $this->update();
            update_option('cpf_ebay_token_is_invalid', false);
        }

        // update user details
        $EC->initEC($this->id);
        $user_details = $EC->GetUser(true);
        $EC->closeEbay();
        if ($user_details) {
            $this->user_name = $user_details->UserID;
            $this->user_details = maybe_serialize($user_details);
            if ($this->title == 'My Account') {
                $this->title = $user_details->UserID; // use UserID as default title for new accounts
            }
            $this->update();
        }

        // update seller profiles
        $EC->initEC($this->id);
        $result = $EC->GetUserPreferences(true);
        $EC->closeEbay();
        if ($result) {
            $this->oosc_mode = $result->OutOfStockControl ? 1 : 0;
            $this->seller_profiles = $result->SellerProfileOptedIn ? 1 : 0;
            $this->shipping_profiles = maybe_serialize($result->seller_shipping_profiles);
            $this->payment_profiles = maybe_serialize($result->seller_payment_profiles);
            $this->return_profiles = maybe_serialize($result->seller_return_profiles);
            $this->update();
        }
    }// updateUserDetails()

    function update()
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLENAME;
        if (!$this->id)
            return;

        $data = array();
        foreach ($this->fieldnames as $key) {
            if (isset($this->$key)) {
                $data[$key] = $this->$key;
            }
        }

        if (sizeof($data) > 0) {
            $result = $wpdb->update($table, $data, array('id' => $this->id));
            echo $wpdb->last_error;
            // echo "<pre>";print_r($wpdb->last_query);echo"</pre>";#die();
            // return $wpdb->insert_id;
        }
    }

    function delete()
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLENAME;
        if (!$this->id)
            return;

        $result = $wpdb->delete($table, array('id' => $this->id));
        echo $wpdb->last_error;
        return "Account deleted successfully!";
    }

    function makeAccountDefault(){
        $result = array();
        $status = false;
        global $wpdb;
        $table = $wpdb->prefix .self::TABLENAME;

        if(!$this->id)
            return false;
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
            'id' => $this->id
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
       // echo json_encode($result);
    }


// getPageItems()

    function getPageItems($current_page, $per_page)
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLENAME;

        $orderby = (!empty($_REQUEST['orderby'])) ? esc_sql($_REQUEST['orderby']) : 'title';
        $order = (!empty($_REQUEST['order'])) ? esc_sql($_REQUEST['order']) : 'asc';
        $offset = ($current_page - 1) * $per_page;
        $per_page = esc_sql($per_page);

        // get items
        $items = $wpdb->get_results("
			SELECT *
			FROM $table
            ORDER BY active desc, $orderby $order
            LIMIT $offset, $per_page
		", ARRAY_A);

        // get total items count - if needed
        if (($current_page == 1) && (count($items) < $per_page)) {
            $this->total_items = count($items);
        } else {
            $this->total_items = $wpdb->get_var("
				SELECT COUNT(*)
				FROM $table
				ORDER BY $orderby $order
			");
        }

        foreach ($items as &$account) {
            // $account['ReportTypeName'] = $this->getRecordTypeName( $account['ReportType'] );
        }

        return $items;
    }

}

// CPF_eBayAccount()
