<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 *
 */
require_once("Amazon/.config.inc.php");
require_once 'amazon-request.php';

class Amazon
{
    var $default_account;
    var $service;
    var $request;

    function __construct()
    {
        $this->default_account = $this->init();
    }

    function init()
    {
        global $wpdb;
        $table = $this->_getTable();

        $item = $wpdb->get_row("
			SELECT *
			FROM $table
			WHERE active = 1", OBJECT);
        return $item;
    }

    function loadNavigation()
    {
        $this->view('navigation');
    }

    function account_setting_page()
    {

        // require_once dirname(__FILE__).'/uploadamazon.php';
        // echo PAmazonUpload::uploadFeed();


        global $wpdb;

        $table = $this->_getTable();
        $users = $wpdb->get_results("SELECT * FROM " . $table, OBJECT);
        $marketplaces = $wpdb->get_results("SELECT * FROM " . $this->_getTable('amazon_markets'), OBJECT);

        $this->view('account', [
            'users' => $users,
            'amazon_markets' => $marketplaces
        ]);
    }

    function getAccountByID($id, $column = "")
    {
        global $wpdb;

        $table = $this->_getTable();
        $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $table . " WHERE id = %s", $id), OBJECT);
        if (count($column) > 0) {
            return $user->$column;
        }
        return $user;
    }

    function _getTable($table = "amazon_accounts")
    {

        global $wpdb;

        return $wpdb->prefix . "cpf_" . $table;

    }

    function editAccount()
    {
        global $wpdb;

        $account = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $this->_getTable() . " WHERE id = %s", $_GET['amazon_account']), OBJECT);
        $this->view('account_edit', ['account' => $account]);
    }

    function saveAccount()
    {
        echo "<pre>";
        print_r($_POST);
        die;
    }

    function makeDefaultAccount()
    {

        global $wpdb;

        $set_all_inactive = $wpdb->update($this->_getTable(), ['active' => 0], ['active' => 1]);
        $wpdb->update($this->_getTable(), ['active' => 1], ['id' => $_GET['amazon_account']]);

    }

    function getMarkets()
    {

        global $wpdb;

        $market = $wpdb->get_row($wpdb->prepare("SELECT code,url FROM " . $this->_getTable('amazon_markets') . " WHERE id = %s", $_POST['market_id']), ARRAY_A);

        echo json_encode($market);

        die;

    }

    function getMarketByID($id, $column)
    {
        global $wpdb;

        $market = $wpdb->get_row($wpdb->prepare("SELECT url,code FROM " . $this->_getTable('amazon_markets') . " WHERE id = %s", $id));
        return $market->$column;
    }


    function addAccount()
    {
        global $wpdb;

        $data = new stdClass;
        $insert['title'] = $_POST['cpf_account_title'];
        $insert['market_id'] = $_POST['cpf_amazon_market_id'];
        $insert['marketplace_id'] = $_POST['cpf_marketplace_id'];
        $insert['merchant_id'] = $_POST['cpf_merchant_id'];
        $insert['access_key_id'] = $_POST['cpf_access_key_id'];
        $insert['secret_key'] = $_POST['cpf_secret_key'];
        $insert['active'] = 0;
        $insert['is_valid'] = 0;
        $insert['market_code'] = $_POST['cpf_amazon_market_code'];

        foreach ($insert as $key => $value) {
            $data->$key = $value;
        }

        // $data->title			= $_POST['cpf_account_title'];
        // $data->market_id		= $_POST['cpf_amazon_market_id'];
        // $data->marketplace_id	= $_POST['cpf_marketplace_id'];
        // $data->merchant_id	= $_POST['cpf_merchant_id'];
        // $data->access_key_id	= $_POST['cpf_access_key_id'];
        // $data->secret_key		= $_POST['cpf_secret_key'];
        // $data->active			= 0;
        // $data->is_valid		= 0;
        // $data->market_code	= $_POST['cpf_amazon_market_code'];

        $table = $this->_getTable('amazon_accounts');

        if ($wpdb->insert($table, $insert)) {

            $this->initAPI($data, 'seller');

            $amazon_request = new Amazon_Request_Handler;

            $account_id = $wpdb->insert_id;

            // object or array of parameters
            $request = $amazon_request->invokeListMarketplaceParticipations($this->service, $this->request);
            $allowed_markets = maybe_serialize($request->allowed_markets);

            if (count($allowed_markets) > 0) {
                $wpdb->update($table, ['allowed_markets' => $allowed_markets, 'is_valid' => 1], ['id' => $account_id]);
            }

        } else {

            echo $wpdb->print_error;

        }
        $redirect_url = get_admin_url() . "admin.php?page=amazon-configure";
        // echo $redirect_url;
        echo "<script> window.location.href = '" . $redirect_url . "';</script>";
    }

    function submitFeed()
    {
        if ($_GET['id'] > 0) {
            global $wpdb;

            $table = $wpdb->prefix . "amazon_submitted_feed";

            $sql = "SELECT url FROM " . $wpdb->prefix . "cp_feeds WHERE id = " . $_GET['id'];
            $f_path = $wpdb->get_row($sql);

            $feed = $f_path->url;
            $marketplaceIdArray = array("Id" => array($this->default_account->marketplace_id,));
            $request = $this->initAPI($this->default_account, 'submit_feed', $data = [
                'feed' => $feed,
                'marketplaceIdArray' => $marketplaceIdArray,
            ]);

            if ($request['status'] > 0) {
                echo 'Your Feed is submited. Please check Feed Result Page.';
                // echo "<pre>";
                // print_r($request);
                // die;
                /*$wpdb->insert($table,[
                    'feed_submission_id' => $request['FeedSubmissionInfo']['FeedSubmissionId'],
                    'submitted_date'	=> $request['FeedSubmissionInfo']['SubmittedDate'],
                    'feed_status'		=> $request['FeedSubmissionInfo']['FeedProcessingStatus'],
                    'feed_id'			=> $_GET['id'],
                    'request_id'		=> $request['ResponseMetadata']['RequestId']
                    ]);*/
            }
        }
    }

    function initAPI($insert, $type, $params = array())
    {


        require_once "Amazon/MarketplaceWebService/Client.php";

        $access_key = $insert->access_key_id;
        $secret_key = $insert->secret_key;
        $application_name = $insert->title;
        $application_version = "1.0.0";
        $merchant_id = $insert->merchant_id;
        $marketplace_id = $insert->market_id;


        switch ($type) {
            case 'seller':
                $url = $this->getCountryServiceURL($insert->market_code);
                $serviceUrl = $url . "/Sellers/2011-07-01";
                require_once "Amazon/MarketplaceWebServiceSellers/Client.php";
                require_once "Amazon/MarketplaceWebServiceSellers/Model/ListMarketplaceParticipationsRequest.php";

                $config = array(
                    'ServiceURL' => $serviceUrl,
                    'ProxyHost' => null,
                    'ProxyPort' => -1,
                    'ProxyUsername' => null,
                    'ProxyPassword' => null,
                    'MaxErrorRetry' => 3,
                );
                $service = new MarketplaceWebServiceSellers_Client(
                    $access_key,
                    $secret_key,
                    $application_name,
                    $application_version,
                    $config
                );

                $request = new MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsRequest();
                $request->setSellerId($merchant_id);
                break;

            case 'submit_feed':


                require_once "Amazon/MarketplaceWebService/Model/SubmitFeedRequest.php";

                $amazon_request = new Amazon_Request_Handler;

                $serviceUrl = $this->getCountryServiceURL($insert->market_code);

                $config = array(
                    'ServiceURL' => $serviceUrl,
                    'ProxyHost' => null,
                    'ProxyPort' => -1,
                    'MaxErrorRetry' => 3,
                );

                $service = new MarketplaceWebService_Client(
                    $access_key,
                    $secret_key,
                    $config,
                    $application_name,
                    $application_version
                );
                $feedHandle = @fopen('php://memory', 'rw+');
                fwrite($feedHandle, $feed);
                rewind($feedHandle);

                $request = new MarketplaceWebService_Model_SubmitFeedRequest();
                $request->setMerchant($merchant_id);
                $request->setMarketplaceIdList($params['marketplaceIdArray']);
                $request->setFeedType('_POST_FLAT_FILE_LISTINGS_DATA_');
                $request->setContentMd5(base64_encode(md5(stream_get_contents($feedHandle), true)));
                rewind($feedHandle);
                $request->setPurgeAndReplace(false);
                $request->setFeedContent($feedHandle);
                //$request->setMWSAuthToken('<MWS Auth Token>'); // Optional

                rewind($feedHandle);

                $result = $amazon_request->invokeSubmitFeed($service, $request);

                @fclose($feedHandle);
                return $result;
                break;

            case 'FeedList':


                require_once "Amazon/MarketplaceWebService/Model/GetFeedSubmissionListRequest.php";
                $serviceUrl = $this->getCountryServiceURL($insert->market_code);
                $config = array(
                    'ServiceURL' => $serviceUrl,
                    'ProxyHost' => null,
                    'ProxyPort' => -1,
                    'MaxErrorRetry' => 3,
                );
                $service = new MarketplaceWebService_Client(
                    $access_key,
                    $secret_key,
                    $config,
                    $application_name,
                    $application_version
                );
                $parameters = array(
                    'Merchant' => $insert->merchant_id,
                    'FeedProcessingStatusList' => array('Status' => array('_SUBMITTED_', '_CANCELLED_', '_IN_SAFETY_NET_', '_IN_PROGRESS_', '_UNCONFIRMED_', '_AWAITING_ASYNCHRONOUS_REPLY_', '_DONE_')),
                    // 'MWSAuthToken' => '<MWS Auth Token>', // Optional
                );

                $request = new MarketplaceWebService_Model_GetFeedSubmissionListRequest($parameters);

                break;

            case 'cancel_feed':

                require_once 'Amazon/MarketplaceWebService/Model/CancelFeedSubmissionsRequest.php';
                require_once 'Amazon/MarketplaceWebService/Model/IdList.php';

                $serviceUrl = $this->getCountryServiceURL($insert->market_code);

                $config = array(
                    'ServiceURL' => $serviceUrl,
                    'ProxyHost' => null,
                    'ProxyPort' => -1,
                    'MaxErrorRetry' => 3,
                    'FeedTypeList' => _POST_PRODUCT_DATA_
                );

                $service = new MarketplaceWebService_Client(
                    $access_key,
                    $secret_key,
                    $config,
                    $application_name,
                    $application_version);

                $request = new MarketplaceWebService_Model_CancelFeedSubmissionsRequest();
                $request->setMerchant($merchant_id);

                $idList = new MarketplaceWebService_Model_IdList();
                $request->setFeedSubmissionIdList($idList->withId($_GET['feed_id']));

                break;

            case 'feed_result':

                require_once 'Amazon/MarketplaceWebService/Model/GetFeedSubmissionResultRequest.php';

                $serviceUrl = $this->getCountryServiceURL($insert->market_code);

                $config = array(
                    'ServiceURL' => $serviceUrl,
                    'ProxyHost' => null,
                    'ProxyPort' => -1,
                    'MaxErrorRetry' => 3,
                );

                $service = new MarketplaceWebService_Client(
                    $access_key,
                    $secret_key,
                    $config,
                    $application_name,
                    $application_version);

                $request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest();
                $request->setMerchant($merchant_id);
                $request->setFeedSubmissionId($_POST['feed_submission_id']);
                $request->setFeedSubmissionResult(@fopen('php://memory', 'rw+'));
                $request->setContentMd5(base64_encode(md5(stream_get_contents($feedHandle), true)));
                // $request->setFeedSubmissionResult(fopen(wp_upload_dir()['basedir']."/test.xml",'rw'));
                break;

            default:
                # code...
                break;
        }


        $this->service = $service;
        $this->request = $request;
    }

    function getCountryServiceURL($code)
    {
        switch ($code) {
            // Cananda
            case 'CA':
                $serviceUrl = "https://mws.amazonservices.ca";
                break;
            // United Kingdon
            case 'UK':
                $serviceUrl = "https://mws.amazonservices.co.uk";
                break;

            // United States
            case 'US':
                $serviceUrl = "https://mws.amazonservices.com";
                break;

            // Germany
            case 'DE':
                $serviceUrl = "https://mws.amazonservices.de";
                break;

            //Japan
            case 'JP':
                $serviceUrl = "https://mws.amazonservices.co.jp";
                break;

            case 'FR':
                $serviceUrl = "https://mws.amazonservices.fr";
                break;

            case 'ES':
                $serviceUrl = "https://mws.amazonservices.es";
                break;

            case 'IT':
                $serviceUrl = "https://mws.amazonservices.it";
                break;

            case 'CN':
                $serviceUrl = "https://mws.amazonservices.cn";
                break;

            case 'IN':
                $serviceUrl = "https://mws.amazonservices.in";
                break;


            default:
                # code...
                break;

        }
        return $serviceUrl;
    }


    function feed_report($ajax = false)
    {

        global $wpdb;
        $table = $wpdb->prefix . "amazon_submitted_feed";
        $list = $wpdb->get_results("SELECT * FROM $table");

        $this->view('feed_report', [
            'feeds' => $list,
            'is_ajax' => $ajax
        ]);
    }

    function synchronizeFeedList()
    {
        global $wpdb;

        $table = $wpdb->prefix . "amazon_submitted_feed";
        $this->initAPI($this->default_account, 'FeedList');
        $amazon_request = new Amazon_Request_Handler;
        $request = $amazon_request->invokeGetFeedSubmissionList($this->service, $this->request);

        if ($request['result'] > 0) {

            $wpdb->query("TRUNCATE $table");

            foreach ($request['FeedSubmissionInfo'] as $res) {

                $insert['feed_submission_id'] = $res['FeedSubmissionId'];
                $insert['submitted_date'] = $res['SubmittedDate'];
                $insert['feed_status'] = $res['FeedProcessingStatus'];
                $insert['start_processing_date'] = $res['StartedProcessingDate'];
                $insert['complete_date'] = $res['CompletedProcessingDate'];
                // print_r($insert);
                $wpdb->insert($table, $insert);
                if ($wpdb->insert_id > 0) $status = true;
                else $status = false;

            }

        } else $status = false;

        return $status;

    }

    function cancelSubmitFeed()
    {
        $this->initAPI($this->default_account, 'cancel_feed');

        $amazon_request = new Amazon_Request_Handler;

        $amazon_request->invokeCancelFeedSubmissions($this->service, $this->request);
        die;
    }


    // get feed result by submission id

    function getFeedSubmissionResult()
    {
        $feed_submission_id = $_POST['feed_submission_id'];
        $this->initAPI($this->default_account, 'feed_result');

        $amazon_request = new Amazon_Request_Handler;

        $result = $amazon_request->invokeGetFeedSubmissionResult($this->service, $this->request);
        echo $result;
        die;
        die;
    }

    function view($insView, $inaData = array(), $echo = true)
    {

        // $sFile = dirname(__FILE__).DS.self::ViewDir.DS.$insView.self::ViewExt;
        $sFile = dirname(__FILE__) . '/../amazon-views/' . $insView . '.php';

        if (!is_file($sFile)) {
            $this->showMessage("View not found: " . $sFile, 1, 1);
            return false;
        }

        if (count($inaData) > 0) {
            extract($inaData, EXTR_PREFIX_ALL, 'cpf');
        }

        ob_start();
        include($sFile);
        $sContents = ob_get_contents();
        ob_end_clean();

        if ($echo) {
            echo $sContents;
            return true;
        } else {
            return $sContents;
        }
    }

    function general_settings()
    {
        $this->getSettingOptions();
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM ".$this->_getTable('amazon_settings')." WHERE template = %s AND specified_template = %s",['all','0']);
        $settings = $wpdb->get_results($sql,OBJECT);
        $this->view('setting_tab',[
            'settings'  =>  $settings
            ]);
    }
    function getAmazonSettings(){
        global $wpdb;
        $title = $_POST['title'];

        if ($title === '0') {
            $title = 'all';
            $like_term = "0";
            $like = "AND specified_template = %s";
        }
        else {
            $like = 'OR specified_template like %s';
            $like_term = 'include::%'.$title.'%';
        }
        $sql = $wpdb->prepare("SELECT * FROM ".$this->_getTable('amazon_settings')." WHERE template = %s ".$like,[$title,$like_term]);
        //echo $sql;
        $settings = $wpdb->get_results($sql,OBJECT);

        $view = $this->view('setting_tab',['settings'=>$settings,'is_ajax' => 1],FALSE);
        echo $view;

    }

    function getSettingOptions($setting = "" ){
        $values = $setting->setting_value;
        $data = explode(",",$values);
        $html = "";
        foreach($data as $key=>$opt ){
            $selected = '"'.$setting->set_value.'"' == $opt ? ' selected ' : '';
            $html .="<option value=".$opt." $selected>".str_replace('"',"",$opt)."</option>";
        }
        echo $html;
    }

    function getSettingInputs($values = ""){
        $html = "";
        if (null !== $values){
            switch ($values->setting_type){
                case "text":
                    $html .= "<textarea id = '".$values->setting_title."' name='".$values->setting_title."'>".$values->set_value."</textarea>";
                break;
                case "input":
                    $html .= "<input type='text' name='".$values->setting_title."' id='".$values->setting_title."' value = '".$values->set_value."' />";
                    break;
            }
        }
        echo $html;
    }

    function setAmazonSettings(){
        global $wpdb;
        $id = $_POST['id'];
        $setting_value = $_POST['setting_value'];

        if ($id > 0 and null != $setting_value ){
            $wpdb->update($this->_getTable('amazon_settings'),['set_value'=>$setting_value],['id'=>$id]);
            echo "Updated!";
        } else {
            echo 'cannot save! please provide sufficient value';
        }
    }

    function create_template(){
        global $wpdb;
        $sql = "SELECT template_name,version FROM ".$this->_getTable('user_template')." GROUP BY template_name";
        $templates = $wpdb->get_results($sql,OBJECT);
        $this->view('template_tab',['templates'=>$templates]);
    }

    function createTemplate(){
        ini_set('memory_limit',-1);
        set_time_limit(300);
//        set_include_path(dirname(__FILE__).'/phpexcel/Classes');
        include dirname(__FILE__).'/phpexcel/Classes/'.'PHPExcel/IOFactory.php';

        $file = $_FILES['user_template']['tmp_name'];
        $country = $_POST['template_country'];
        $obj = PHPExcel_IOFactory::load($file);
        $obj->setActiveSheetIndex(3);
        $sheetData = $obj->getActiveSheet()->toArray();

        $template = explode("=",$sheetData[0][0])[1];
        $version = explode("=",$sheetData[0][1])[1];
        $result = $this->makeFormatForTemplate($sheetData,$template,$version,$country);
            echo '<script type=\'text/javascript\'>window.location.href = "'.admin_url('admin.php?page=amazon-configure&tab=amazon_createtemplate&success='.$result).'"</script>';
    }

function makeFormatForTemplate($sheetData,$template,$version,$country)
{
    if ($sheetData === "")
        return;
    global $wpdb;
    $res = false;
    //check if tempalte already exisits
    $sql = $wpdb->prepare("SELECT id FROM " . $this->_getTable('user_template') . " WHERE template_name = %s AND version = %s", [$template, $version]);
    $check = count($wpdb->get_row($sql, OBJECT)) > 0 ? false : true;
    if ($check) {
        for ($i = 1; $i < count($sheetData[1]); $i++) {
            $html['template_name'] = $template;
            $html['version'] = $version;
            $html['keyword'] = $sheetData[1][$i];
            $html['title'] = $sheetData[2][$i];
            $html['country'] = $country;
            if($wpdb->insert($this->_getTable('user_template'), $html))
                $res = true;
        }
    }
    return $res;
}

function deleteUserTemplate(){
    global $wpdb;
    $delete = $wpdb->delete($this->_getTable('user_template'),['template_name'=>$_POST['template']]);
    echo $delete;
}
}

