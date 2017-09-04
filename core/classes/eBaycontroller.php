<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
 * ebayController class
 *
 * @package default
 * @author
 **/
class eBayController
{
    var $logger;

    var $apiurl;
    var $signin;
    var $devId;
    var $appId;
    var $certId;
    var $RuName;
    var $siteId;
    var $sandbox;
    var $compLevel;
    var $eBayItemListing;

    public $session; //ebay sessionm
    public $sp; //ebay service
    const OptionPrefix = 'cpf_';
    public $EC;
    public $message = false;
    public $error = false;
    public $lastResults = array();

    /**
     * contruct function
     * set up autoloader for eBay classes
     * @return void
     * @author
     **/
    public function __construct()
    {
        $this->config();
        self::loadEbayClasses();
    }

    public function config()
    {
        $incPath = CPF_PATH . '/includes/EbatNs';
        set_include_path(get_include_path() . ':' . $incPath);
    }

    /**
     * loadebayClassess function
     * make sure this only runs once
     * return if autoloader already loaded
     * @return void
     * @author
     **/
    static function loadEbayClasses()
    {

        //we want to be patient when connection to ebay
        if (!ini_get('safe_mode')) @set_time_limit(600);

        ini_set('mysql.connect_timeout', 600);
        ini_set('default_socket_timeout', 600);
        //add EbatNs folder to include path - required for sdk
        //require_once (CPF_PATH  .'/includes/EbatNs/EbatNs_ServiceProxy.php');
        require_once(CPF_PATH . '/includes/EbatNs/EbatNs_Logger.php');
        require_once(CPF_PATH . '/includes/EbatNs/EbatNs_ServiceProxy.php');
        $incPath = CPF_PATH . '/includes/EbatNs/EbatNs_Session.php';
        require_once($incPath);
        require_once(CPF_PATH . '/includes/EbatNs/EbatNs_Client.php');
        require_once(CPF_PATH . '/includes/EbatNs/GetSessionIDRequestType.php');
        require_once(CPF_PATH . '/includes/EbatNs/GetTokenStatusRequestType.php');
        require_once(CPF_PATH . '/includes/EbatNs/FetchTokenRequestType.php');
        require_once(CPF_PATH . '/includes/EbatNs/GetUserRequestType.php');
        require_once(CPF_PATH . '/includes/EbatNs/GetUserPreferencesRequestType.php');
        require_once(CPF_PATH . '/includes/EbatNs/ItemType.php');
        require_once(CPF_PATH . '/includes/EbatNs/AddFixedPriceItemRequestType.php');
        require_once(CPF_PATH . '/includes/EbatNs/AddItemRequestType.php');


    } // loadeBayClassess()


    /**
     * GEtEbaySignInUrl function
     *
     * @return $url
     * @author
     **/
    function GetEbaySignInUrl($RuName = null, $Params = null)
    {
        $s = $this->session;
        if ($s->getAppMode() == 0) {
            $url = 'https://signin.' . self::getDomainnameBySiteId($s->getSiteId()) . '/ws/eBayISAPI.dll?SignIn';
        } else {
            $url = 'https://signin.sandbox.' . self::getDomainnameBySiteId($s->getSiteId()) . '/ws/eBayISAPI.dll?SignIn';
        }
        if ($RuName != null) {
            $url .= '&runame=' . $RuName;
        }
        if ($params != null) {
            $url .= '&ruparams =' . $Params;
        }
        return $url;
    }// GetEbaySignInUrl()

    /**
     * getAuthUrl function
     *get sessionId and build AuthUrl
     * @return $url
     * @author
     **/
    public function getAuthUrl()
    {
        //fetch SessionId - valid for about 5 minutes
        $SessionID = $this->getSessionId($this->RuName);

        //save SessionID to DB
        update_option('cpf_ebay_sessionid', $SessionID);
        //CPlE()->logger->info('new SessionID:' . $SessionID);

        //build auth url
        $query = array('RuName' => $this->RuName, 'SessID' => $SessionID);
        $url = $this->GetEbaySignInUrl() . '&' . http_build_query($query, '', '&');
        //CPlE()->logger->info('AuthUrl: ' .$url);
        return $url;

    }// getAuthUrl()

    /**
     * doFetechToken function
     * Fetch token and save to DB
     * @return $token
     * @author
     **/
    public function doFetchToken($account_id = false)
    {
        $SessionID = get_option('cpf_ebay_sessionid');
        $token = $this->FetchToken($SessionID);
        if ($token) {
            if ($account_id) {
                $account = new CPF_eBayAccount($account_id);
                $accoun->token = $token;
                $account->update();
            }
        }

        return $token;
    }// doFetchToken()

    /**
     * getTokenExpiratinoTime function
     * do get token expiration time save to db
     * @return $expdate
     * @author
     **/
    public function getTokenExpirationTime($site_id, $sandbox_enabled)
    {
        $token = get_option('cpf_ebay_token');
        $expdate = $this->fetchTokenExpirationTime($token);

        //update option
        update_option('cpf_ebay_token_expirationtime', $expdate);

        return $expdate;
    }// getTokenExpirationTime;

    /**
     * initEbay function
     * Establish connection to eBay API
     * @return void
     * @author
     **/
    public function initEbay($site_id, $sandbox_enabled, $token = false, $account_id = false)
    {
        //init autoloader from EbatNs classess
        //$this->loadEbayClasses();
        //CPF()->logger->info("init( $account_id )" );


        //hide inevitable cURL warnings from sdk
        // *** DISABLE FOR DEBUGGING ***
        $this->error_reporting_level = error_reporting();
        //CPF()->logger->debug('original error reporting level: '.$this->error_reporting_level);


        error_reporting(E_ERROR);
        //CPF()->logger->debug('new error reporting level: '.error_reporting() );

        $this->siteId = $site_id;
        $this->sandbox = $sandbox_enabled;

        if ($sandbox_enabled) {
            //sandbox keys
            $this->devId = 'd3c19912-1d12-4ce4-9d75-a5d426e9d8f9';
            $this->appId = 'subashgh-ebayplug-SBX-e2f871c91-482b5863';
            $this->certId = 'SBX-2f871c9123be-fd08-419e-9f46-a372';
            $this->RuName = 'subash_ghimire-subashgh-ebaypl-ifueqvfe';

            $this->apiurl = 'https://api.sandbox.ebay.com/ws/api.dll';
            $this->signin = 'https://signin.sandbox.ebay.com/ws/eBayISAPI.dll?SignIn&';
        } else {

            // production keys
            $this->devId = 'd3c19912-1d12-4ce4-9d75-a5d426e9d8f9';
            $this->appId = 'subashgh-ebayplug-PRD-62f871c7c-579015ad';
            $this->certId = 'PRD-2f871c7ce7fa-bad0-4a25-b5a4-cde5';
            $this->RuName = 'subash_ghimire-subashgh-ebaypl-xyoiixe';


            $this->apiurl = 'https://api.ebay.com/ws/api.dll';
            $this->signin = 'https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&';
        }

        //filter RuName
        if (defined('FEED_PLUGIN_VERSION'))
            $this->RuName = apply_filters('cpf_runame', $this->RuName, $sandbox_enabled);

        //init session
        //require_once(CPF_PATH .'/includes/EbatNs/EbatNs_Session.php');
        $session = new EbatNs_Session();

        //depends on the site working on (needs ID-Value !)
        $session->setSiteId($site_id);
        $session->cpf_account_id = $account_id;
        //regard WP proxy server
        if (defined('WP_USEPROXY') && WP_USEPROXY) {
            if (defined('WP_PROXY_HOST') && defined('WP_PROXY_PORT'))
                $session->setProxyServer(WP_PROXY_HOST . ':' . WP_PROXY_PORT);
        }

        //enviromen (0 = production, 1= sandbox)
        if ($sandbox_enabled == '1') {
            CPF()->logger->info('initEbay(): SANDBOX ENABLED');
            $session->setAppMode(1); //this must be set before settingds the keys (appId , devId, ...)
        } else {
            $session->setAppMode(0);
        }

        $session->setAppId($this->appId);
        $session->setDevId($this->devId);
        $session->setCertId($this->certId);

        if ($token) {

            //use token as credentials
            $session->setTokenmode(true);

            //do not use a token file!
            $session->setTokenUsePickupFile(false);

            //token of the user
            $session->setRequestToken($token);
        } else {
            $session->setTokenMode(false);
        }
        $sp = new EbatNs_ServiceProxy($session, 'EbatNs_DataConverterUtf8');

        // attach custom DB Logger for Tools page
        // if ( get_option('wplister_log_to_db') == '1' ) {
        if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'wplister-tools') {
            $sp->attachLogger(new CPF_EbatNs_Logger(false, 'db', $account_id, $site_id));
        }

        //save service proxy - and session
        $this->sp = $sp;
        $this->session = $session;
    }// initeBay()

    /**
     * initLogger function
     * Re-attach logger  - requires to log multiple request in the same session
     * @return void
     * @author
     **/
    public function initLogger()
    {
        /*require_once(CPF_PATH .'/core/classes/CPF_EbatNs_Logger.php');
        $this->sp->attachLogger(new CPF_EbatNs_Logger(false, 'db', $this->session->cpf_account_id , $this->siteId) );*/
    }// initLogger()


    /**
     * closeEbay function
     * Close connection to eBay API
     * @return void
     * @author
     **/
    public function closeEbay()
    {
        error_reporting($this->error_reporting_level);
    }// closeEbay()

    /**
     * GetSessionId function
     *
     * @return session_id
     * @author
     **/
    public function GetSessionID($RuName)
    {
        //prepare request
        $req = new GetSessionIDRequestType();
        $req->setRuName($RuName);

        //send request
        $res = $this->sp->GetSessionID($req);

        //handle errors like blocked ips
        if ($res->Ack != 'Success') {
            echo "<h1>Problem connecting to eBay</h1>";
            echo "<p>Cart product feeed can't seem to establish a connection to eBay's servers. This could be caused by a firewall blocking cURL from accessing unkown ip addresses.</p>";
            echo "<p>Only your hosting company can sort out the problems causing cURL not to connect properly. Your hosting company's server administrator should be able to resolve the permission problems preventing cURL from working. They've probably got overly limiting restrictions configured on the server, preventing it from being able to do the communication required for listing items on eBay.</p>";
            echo "<p>debug output:</p>";
            echo "<pre>";
            print_r($res);
            echo "</pre>";
            echo "<pre>";
            print_r($req);
            echo "</pre>";
            die();
        }

        //TODO:handle error
        return ($res->SessionID);
    }// GetSessionID()

    /**
     * FetchToken function
     *
     * @return eBayAuthToken
     * @author
     **/
    public function FetchToken($SessionID)
    {
        //prepare request
        $req = new FetchTokenRequestType();
        $req->setSessionID($SessionID);

        //send request
        $res = $this->sp->FetchToken($req);
        //TODO: handle error

        if (!$res->eBayAuthToken) {
            echo "<pre>Error in FetchToken():";
            print_r($res);
            echo "</pre>";
            return false;
        }

        return ($res->eBayAuthToken);
    }// FetchToken()


    /**
     * fetchTokenExpirationTime function
     *
     * @return ExpirationTime
     * @author
     **/
    public function fetchTokenExpirationTime($SessionID)
    {
        //prepare request
        $req = new GetTokenStatusRequestType();
        $req->setSessionID($sessionID);

        //send request
        $res = $this->sp->GetTokenStatus($req);

        //TODE: handle error
        return ($res->ExpirationTime);
    }// fetchTokenExpirationTime()


    /**
     * GetUser function
     *
     * @return $userId
     * @author
     **/
    public function GetUser($return_result = false)
    {
        //prepare request
        $req = new GetUserRequestType();
        //send request
        $res = $this->sp->GetUser($req);

        $user = new stdClass();
        $user->UserID = $res->User->UserID;
        $user->Email = $res->User->Email;
        $user->FeedbackScore = $res->User->FeedbackScore;
        $user->FeedbackRatingStar = $res->User->FeedbackRatingStar;
        $user->NewUser = $res->User->NewUser;
        $user->IDVerified = $res->User->IDVerified;
        $user->eBayGoodStanding = $res->User->eBayGoodStanding;
        $user->Status = $res->User->Status;
        $user->Site = $res->User->Site;
        $user->VATStatus = $res->User->VATStatus;
        $user->PayPalAccountLevel = $res->User->PayPalAccountType;
        $user->PayPalAccountType = $res->User->PayPalAccountType;
        $user->PayPalAccountStatus = $res->User->PayPalAccountStatus;
        $user->StoreOwner = $res->User->SellerInfo->StoreOwner;
        $user->StoreURL = $res->USer->SellerInfo->StoreURL;
        $user->SellerBusinessType = $res->User->SellerInfo->SellerBusinessType;
        $user->ExpressEligible = $res->User->SellerInfo->ExpressEligible;
        $user->StoreSite = $res->User->SellerInfo->StoreSite;

        if ($return_result) return $user;

        $UserID = $res->User->UserID;
        update_option('cpf_ebay_token_userid', $UserID);
        update_option('cpf_ebay_user', $user);

        return $UserID;

    }// GetUser()

    /**
     * GetTokenStatus function
     *
     * @return $espdate
     * @author
     **/
    public function GetTokenStatus($return_result = false)
    {
        //prepare request
        $req = new GetTokenStatusRequestType();
        $res = $this->sp->GetTokenStatus($req);
        $expdate = $res->TokenStatus->ExpirationTime;

        if ($expdate) {
            $expdate = str_replace('T', ' ', $expdate);
            $expdate = str_replace('.00Z', '', $expdate);

            update_option('cpf_ebay_token_expirationtime', $expdate);
            update_option('cpf_ebay_token_is_invalid', false);
        }

        //handle result
        return ($expdate);
    }// GetTokenStatus()

    /**
     * getEbayTime function
     *
     * @return void
     * @author
     **/
    public function getEbayTime()
    {
        //prepare request
        $req = new GeteBayOfficialTime();

        //send request
        $res = $this->sp->GeteBayOfficialTime();

        //process timestamp
        if ($res->Ack == 'Success') {
            $ts = $res->Timestamp;                // 2016-04-21T04:10:022Z
            $ts = str_replace('T', '', $ts);    // 2016-04-21 04:10:012Z
            $ts = substr($ts, 0, 19);            // 2016-04-21 04:10:17
            return $ts;
        }

        //return result on error
        return ($res);
    }// getEbayTime()


    /**
     * getEbaySiteCode function
     *
     * @return $site_id
     * @author
     **/
    public function getEbaySiteCode($site_id)
    {
        $sites = self::getEbaySites();
        if (!array_key_exists($site_id, $sites)) return false;
        return $sites[$site_id];
    }// getEbaySiteCode()

    /**
     * getEbaySites function
     *
     * @return $sites
     * @author
     **/
    static public function getEbaySites()
    {
        $sites = array(
            '0' => 'United States',
            '2' => 'Canada',
            '3' => 'United Kingdom',
            '15' => 'Australia',
            '16' => 'Austria',
            '23' => 'Belgium(French)',
            '71' => 'France',
            '77' => 'Germany',
            '100' => 'eBay Motors',
            '101' => 'Italy',
            '123' => 'Belgium(Dutch)',
            '146' => 'Netherlands',
            '186' => 'Spain',
            '193' => 'Switzerland',
            '201' => 'HongKong',
            '203' => 'India',
            '205' => 'Ireland',
            '207' => 'Malaysia',
            '210' => 'Canada(French)',
            '211' => 'Philippines',
            '212' => 'Poland',
            '216' => 'Singapore',
        );
        return $sites;
    }// getEbaySites()

    /**
     * getDomainnameBySiteId function
     *
     * @return domain of siteId
     * @author
     **/
    static function getDomainnameBySiteId($siteid = 0)
    {
        switch ($siteid) {
            case 0:
                return 'ebay.com';
            case 2:
                return 'ebay.ca';
            case 3:
                return 'ebay.co.uk';
            case 15:
                return 'ebay.com.au';
            case 16:
                return 'ebay.at';
            case 23:
                return 'ebay.be';
            case 71:
                return 'ebay.fr';
            case 77:
                return 'ebay.de';
            case 100:
                return 'ebaymotors.com';
            case 101:
                return 'ebay.it';
            case 123:
                return 'ebay.be';
            case 146:
                return 'ebay.nl';
            case 186:
                return 'ebay.es';
            case 193:
                return 'ebay.ch';
            case 196:
                return 'ebay.tw';
            case 201:
                return 'ebay.com.hk';
            case 203:
                return 'ebay.in';
            case 207:
                return 'ebay.com.my';
            case 211:
                return 'ebay.ph';
            case 212:
                return 'ebay.pl';
            case 216:
                return 'ebay.com.sg';
            case 218:
                return 'ebay.se';
            case 223:
                return 'ebay.cn';
        }
        return 'ebay.com';
    }// getDomainnameBySiteId()


    public function newAccount($ebay_token)
    {

        require_once(CPF_PATH . '/core/model/eBayAccount.php');
        require_once(CPF_PATH . '/core/model/eBaySite.php');
        // create new account
        $account = new CPF_eBayAccount();
        // $account->title     = stripslashes( $_POST['wplister_account_title'] );
        $account->title = 'My Account';
        $account->site_id = $_REQUEST['site_id'];
        $account->site_code = EbayController::getEbaySiteCode($_REQUEST['site_id']);
        $account->sandbox_mode = $_REQUEST['sandbox'];
        $account->token = $ebay_token;
        $account->active = 1;

        $account->add();

        // set enabled flag for site
        $site = CPF_eBaySite::getSiteObj($account->site_id);
        $site->enabled = 1;
        $site->update();

        // update user details
        $account->updateUserDetails();

        // set default account automatically
        if (!get_option('cpf_default_account_id')) {
            update_option('cpf_default_account_id', $account->id);
            //$this->makeDefaultAccount( $account->id );
        }

        //$this->check_wplister_setup('settings');

        $this->error_message = ('New account was added');
    }

    // init eBay connection
    public function initEC($account_id = null, $site_id = null)
    {
        global $EC;
        //use current default account by default (WPL1)
        $ebay_site_id = self::getOption('ebay_site_id');
        $sandbox_enabled = self::getOption('sandbox_enabled');
        $ebay_token = self::getOption('ebay_token');

        if (isset($_REQUEST['site_id']) && isset($_REQUEST['sandbox'])) {
            $ebay_site_id = $_REQUEST['site_id'];
            $sandbox_enabled = $_REQUEST['sandbox'];
            $ebay_token = '';
        }

        // use specific account if provided in request or parameter
        if (!$account_id && isset($_REQUEST['account_id'])) {
            $account_id = $_REQUEST['account_id'];
        }
        if ($account_id) {
            // $account = new WPLE_eBayAccount( $account_id ); // not suitable to check if an account exists
            $account = CPF_eBayAccount::getAccount($account_id);
            if ($account) {
                $ebay_site_id = $account->site_id;
                $sandbox_enabled = $account->sandbox_mode;
                $ebay_token = $account->token;
            } else {
                $msg = sprintf('<b>Warning: You are trying to use an account which does not exist in Cart Product Feed</b> (ID %s).', $account_id) . '<br>';
                $msg .= 'This can happen when you delete an account from Cart Product Feed without removing all listings, profiles and orders first.' . '<br><br>';
                $msg .= 'In order to solve this issue, please visit your account settings and follow the instructions to assign all listings, orders and profiles to your default account.';
                $this->error_message = $msg;
            }
        } else {
            $account_id = get_option('cpf_default_account_id');
        }

        if ($site_id) $ebay_site_id = $site_id;
        $this->initEbay($ebay_site_id, $sandbox_enabled, $ebay_token, $account_id);
    }

    /* prefixed option handlers */
    static public function getOption($insKey, $default = null)
    {
        return get_option(self::OptionPrefix . $insKey, $default);
    }

    // GetUserPreferences
    public function GetUserPreferences($return_result = false)
    {

        // prepare request
        $req = new GetUserPreferencesRequestType();
        $req->setShowSellerProfilePreferences(true);
        $req->setShowOutOfStockControlPreference(true);
        // $req->setShowSellerExcludeShipToLocationPreference( true );

        // send request
        $res = $this->sp->GetUserPreferences($req);
        // echo "<pre>";print_r($res);echo"</pre>";#die();

        // handle response error
        if ('EbatNs_ResponseError' == get_class($res))
            return false;

        $result = new stdClass();
        $result->success = true;
        $result->seller_shipping_profiles = array();
        $result->seller_payment_profiles = array();
        $result->seller_return_profiles = array();

        $result->SellerProfileOptedIn = $res->SellerProfilePreferences->SellerProfileOptedIn;
        $result->OutOfStockControl = $res->OutOfStockControlPreference;

        $profiles = $res->getSellerProfilePreferences()->getSupportedSellerProfiles()->getSupportedSellerProfile();
        // echo "<pre>";print_r($profiles);echo"</pre>";#die();

        // if ( $result->SellerProfileOptedIn ) {
        if (sizeof($res->SellerProfilePreferences->SupportedSellerProfiles->SupportedSellerProfile) > 0) {

            foreach ($res->SellerProfilePreferences->SupportedSellerProfiles->SupportedSellerProfile as $profile) {

                $seller_profile = new stdClass();
                $seller_profile->ProfileID = $profile->ProfileID;
                $seller_profile->ProfileName = $profile->ProfileName;
                $seller_profile->ProfileType = $profile->ProfileType;
                $seller_profile->ShortSummary = $profile->ShortSummary;

                switch ($profile->ProfileType) {
                    case 'SHIPPING':
                        $result->seller_shipping_profiles[] = $seller_profile;
                        break;

                    case 'PAYMENT':
                        $result->seller_payment_profiles[] = $seller_profile;
                        break;

                    case 'RETURN_POLICY':
                        $result->seller_return_profiles[] = $seller_profile;
                        break;
                }

            }
            if ($return_result) return $result;

            update_option('cpf_ebay_seller_shipping_profiles', $result->seller_shipping_profiles);
            update_option('cpf_ebay_seller_payment_profiles', $result->seller_payment_profiles);
            update_option('cpf_ebay_seller_return_profiles', $result->seller_return_profiles);

        } else {
            if ($return_result) return $result;
            delete_option('cpf_ebay_seller_shipping_profiles');
            delete_option('cpf_ebay_seller_payment_profiles');
            delete_option('cpf_ebay_seller_return_profiles');
        }

        if ($return_result) return $result;
        update_option('cpf_ebay_seller_profiles_enabled', $result->SellerProfileOptedIn ? 'yes' : 'no');
        delete_option('cpf_ebay_seller_profiles');

    }

    public function postListing(Array $listingData, $sellerConfig = array(), $token)
    {
        /* Sample XML Request Block for minimum AddItem request
        see ... for sample XML block given length*/
        // Create unique id for adding item to prevent duplicate adds
        foreach ($listingData as $key => $Data) {
            # code...
            $format = isset($sellerConfig['listingType']) ? $sellerConfig['listingType'] : $Data['Format'];
            $duration = isset($sellerConfig['listingDuration']) ? $sellerConfig['listingDuration'] : $Data['Duration'];
            $dispatchTime = $sellerConfig['dispatchTime'];
            $PostalCode = isset($sellerConfig['postalcode']) ? $sellerConfig['postalcode'] : '11023';
            $conditionType = isset($sellerConfig['conditionType']) ? $sellerConfig['conditionType'] : $Data['ConditionID'];
            $quantity = isset($sellerConfig['quantity']) ? $sellerConfig['quantity'] : $Data['Quantity'];
            $paypal_email = isset($sellerConfig['paypal_email']) ? $sellerConfig['paypal_email'] : $Data['PayPalEmailAddress'];

            if ($sellerConfig['site_abbr'] == 'US') {
                $site = $sellerConfig['site_abbr'];
            } else {
                $site = $sellerConfig['site_code'];
            }
            if (empty($PostalCode)) {
                $PostalCode = '11023';
            }
            $category = explode(':', $Data['Category']);
            $uuid = md5(uniqid());
            //$addPicture = "http://localhost/wp-content/plugins/purple-xmls-google-product-feed-for-woocommerce/core/images/exf-sm-logo.png";
            // create the XML request
            $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
            $xmlRequest .= "<AddItemRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">";
            $xmlRequest .= "<ErrorLanguage>en_US</ErrorLanguage>";
            $xmlRequest .= "<WarningLevel>High</WarningLevel>";
            $xmlRequest .= "<Item>";
            $xmlRequest .= "<Title>" . $Data['Title'] . "</Title>";
            $xmlRequest .= "<Description>" . $Data['Description'] . "</Description>";
            $xmlRequest .= "<PrimaryCategory>";
            $xmlRequest .= "<CategoryID>" . $category[1] . "</CategoryID>";
            $xmlRequest .= "</PrimaryCategory>";
            $xmlRequest .= "<ProductListingDetails>";
            $xmlRequest .= "<BrandMPN>";
            $xmlRequest .= "<Brand>" . $Data['C:Brand'] . "</Brand>";
            $xmlRequest .= "<MPN>" . $Data['C:MPN'] . "</MPN>";
            $xmlRequest .= "</BrandMPN>";
            $xmlRequest .= "<EAN>" . $Data['C:EAN'] . "</EAN>";
            $xmlRequest .= "<UPC>" . $Data['C:UPC'] . "</UPC>";
            $xmlRequest .= "</ProductListingDetails>";
            $xmlRequest .= "<StartPrice>" . $Data['StartPrice'] . "</StartPrice>";
            $xmlRequest .= "<ConditionID>" . $conditionType . "</ConditionID>";
            $xmlRequest .= "<CategoryMappingAllowed>true</CategoryMappingAllowed>";
            $xmlRequest .= "<Country>" . $sellerConfig['site_abbr'] . "</Country>";
            $xmlRequest .= "<Currency>" . $sellerConfig['currency_code'] . "</Currency>";
            $xmlRequest .= "<DispatchTimeMax>" . $dispatchTime . "</DispatchTimeMax>";
            $xmlRequest .= "<ListingDuration>" . $duration . "</ListingDuration>";
            $xmlRequest .= "<ListingType>" . $format . "</ListingType>";
            $xmlRequest .= "<PaymentMethods>PayPal</PaymentMethods>";
            $xmlRequest .= "<PayPalEmailAddress>" . $paypal_email . "</PayPalEmailAddress>";
            $xmlRequest .= "<PictureDetails>";
            $xmlRequest .= "<PictureURL>" . $Data['PicURL'] . "</PictureURL>";
            $xmlRequest .= "</PictureDetails>";
            $xmlRequest .= "<PostalCode>" . $PostalCode . "</PostalCode>";
            $xmlRequest .= "<Quantity>" . $quantity . "</Quantity>";
            $xmlRequest .= "<ReturnPolicy>";
            $xmlRequest .= "<ReturnsAcceptedOption>" . $Data['ReturnsAcceptedOption'] . "</ReturnsAcceptedOption>";
            $xmlRequest .= "<RefundOption>" . $sellerConfig['refundOption'] . "</RefundOption>";
            $xmlRequest .= "<ReturnsWithinOption>" . $sellerConfig['returnwithin'] . "</ReturnsWithinOption>";
            $xmlRequest .= "<Description>" . $sellerConfig['refundDesc'] . "</Description>";
            $xmlRequest .= "<ShippingCostPaidByOption>Buyer</ShippingCostPaidByOption>";
            $xmlRequest .= "</ReturnPolicy>";
            $xmlRequest .= "<ShippingDetails>";
            $xmlRequest .= "<ShippingType>" . ($sellerConfig['ebayShippingType']) . "</ShippingType>";
            $xmlRequest .= "<ShippingServiceOptions>";
            $xmlRequest .= "<ShippingServicePriority>1</ShippingServicePriority>";
            $xmlRequest .= "<ShippingService>" . $sellerConfig['shipping_service'] . "</ShippingService>";
            $xmlRequest .= " <ShippingServiceAdditionalCost>" . $sellerConfig['additionalshippingservice'] . "</ShippingServiceAdditionalCost>";
            $xmlRequest .= "<ShippingServiceCost>" . $sellerConfig['shippingfee'] . "</ShippingServiceCost>";
            $xmlRequest .= "</ShippingServiceOptions>";
            $xmlRequest .= "</ShippingDetails>";
            $xmlRequest .= "<Site>" . $site . "</Site>";
            $xmlRequest .= "<UUID>" . $uuid . "</UUID>";
            $xmlRequest .= "</Item>";
            $xmlRequest .= "<RequesterCredentials>";
            $xmlRequest .= "<eBayAuthToken>" . $token . "</eBayAuthToken>";
            $xmlRequest .= "</RequesterCredentials>";
            $xmlRequest .= "<WarningLevel>High</WarningLevel>";
            $xmlRequest .= "</AddItemRequest>";
            // define our header array for the Trading API call
            // notice different headers from shopping API and SITE_ID changes to SITEID
            $headers = array(
                'X-EBAY-API-SITEID:0',
                'X-EBAY-API-CALL-NAME:AddItem',
                //'X-EBAY-API-REQUEST-ENCODING:XML',
                'X-EBAY-API-COMPATIBILITY-LEVEL:885',
                'X-EBAY-API-DEV-NAME:' . $this->devId,
                'X-EBAY-API-APP-NAME:' . $this->appId,
                'X-EBAY-API-CERT-NAME:' . $this->certId,
                'Content-Type: text/xml;charset=utf-8'
            );

            // initialize our curl session
            $session = curl_init("https://api.ebay.com/ws/api.dll");

            // set our curl options with the XML request
            curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($session, CURLOPT_POST, true);
            curl_setopt($session, CURLOPT_POSTFIELDS, $xmlRequest);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

            // execute the curl request
            $responseXML = curl_exec($session);

            // close the curl session
            curl_close($session);
            //handle response from ebay
            $this->handleResponse($responseXML);
        }
    }

    public function handleResponse($response)
    {
        $xmlResponse = simplexml_load_string($response);
        $this->message = '';
        // Verify that the xml response object was created
        if ($xmlResponse) {
            // Check for call success
            if ($xmlResponse->Ack == "Success") {
                // Display the item id number added
                $this->message .= "<p><h3>Successfully added item as item #" . $xmlResponse->ItemID . "</h3><br/>";
                // Calculate fees for listing
                // loop through each Fee block in the Fees child node
                $totalFees = 0;
                $fees = $xmlResponse->Fees;
                foreach ($fees->Fee as $fee) {
                    $totalFees += $fee->Fee;
                }
                $this->message .= "Total Fees for this listing: " . $totalFees . ".</p>";

            } else {
                // Unsuccessful call, display error(s)
                $this->message .= "<p><h3>The AddItem called failed due to the following error(s):</h3>";
                foreach ($xmlResponse->Errors as $error) {
                    $errCode = $error->ErrorCode;
                    $errLongMsg = htmlentities($error->LongMessage);
                    $errSeverity = $error->SeverityCode;
                    $this->message .= $errSeverity . ": [" . $errCode . "] " . $errLongMsg . "<br/>";
                }
                $this->message .= "</p>";

            }

        }

    }

    private function setLogger($serviceProxy)
    {
        if ($this->debug) {
            $logger = new EbatNs_Logger(true);
            $logger->_debugXmlBeautify = true;
            $logger->_debugSecureLogging = false;

            $serviceProxy->attachLogger($logger);
        }
    }
} // END class

global $EC;
// init controller
$EC = New eBayController();

?>