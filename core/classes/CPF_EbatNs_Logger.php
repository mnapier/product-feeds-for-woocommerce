<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
class CPF_EbatNs_Logger{

	// debugging options
	protected $debugXmlBeautify = true;
	protected $debugLogDestination = 'db';
	protected $debugSecureLogging = true;
	protected $currentUserID = 0;
	protected $callname = '';
	protected $success = false;
	protected $id = 0;
	
	function __construct( $beautfyXml = false, $destination = 'db', $account_id = false, $site_id = false )
	{
		global $wpdb;

		$this->debugXmlBeautify    = $beautfyXml;
		$this->debugLogDestination = $destination;
		$this->debugSecureLogging  = get_option('wplister_log_include_authinfo') ? false : true;

		// get current user id
		$user = wp_get_current_user();
		$this->currentUserID = $user->ID;

		// insert row into db
		$data = array();
		$data['timestamp']  = date( 'Y-m-d H:i:s' );
		$data['user_id']    = ( defined('DOING_CRON') && DOING_CRON ) ? 'wp_cron' : $this->currentUserID;
		$data['site_id']    = $site_id;
		$data['account_id'] = $account_id;
		$wpdb->insert($wpdb->prefix.'ebay_log', $data);
		if ( $wpdb->last_error ) echo 'Error in WPL_EbatNs_Logger::__construct: '.$wpdb->last_error.'<br>'.$wpdb->last_query;
		$this->id = $wpdb->insert_id;

	}
	
	function log($msg, $subject = null)
	{
		global $wpdb;
		$data = array();

		// check if MySQL server has gone away and reconnect if required - WP 3.9+
		if ( method_exists( $wpdb, 'check_connection') ) $wpdb->check_connection();

		// extract Ack status from response
		if ( $subject == 'Response' ) {
			if ( preg_match("/<Ack>(.*)<\/Ack>/", $msg, $matches) ) {
				$this->success = $matches[1];
				$data['success'] = $this->success;
			} elseif ( preg_match("/<ErrorCode>(.*)<\/ErrorCode>/", $msg, $matches) ) {
				$this->success = 'Error '.$matches[1];
				$data['success'] = $this->success;
			} elseif ( preg_match("/<soapenv:Fault>/", $msg, $matches) ) {
				$this->success = 'SOAP Error';
				$data['success'] = $this->success;
			}
		}
		// extract ItemID from request
		if ( $subject == 'Request' ) {
			if ( preg_match("/<ItemID>(.*)<\/ItemID>/", $msg, $matches) ) {
				$this->ebay_id = $matches[1];
				$data['ebay_id'] = $this->ebay_id;
			}
		}
		// extract ItemID from response
		if ( $subject == 'Response' ) {
			if ( preg_match("/<ItemID>(.*)<\/ItemID>/", $msg, $matches) ) {
				$this->ebay_id = $matches[1];
				$data['ebay_id'] = $this->ebay_id;
			}
		}
		// extract call name from request url
		if ( $subject == 'RequestUrl' ) {
			if ( preg_match("/callname=(.*)&/U", $msg, $matches) ) {
				$this->callname = $matches[1];
				$data['callname'] = $this->callname;
			}
		}

		// handle curl_error
		if ( $subject == 'curl_error' ) {
			$this->success = 'Failure';
			$data['success']  = $this->success;
			$data['response'] = 'cURL error: '.$msg;
		}

		// assign msg
		if ( $subject == 'RequestUrl' ) {
			$data['request_url'] = $msg;			
		}
		if ( $subject == 'Request' ) {
			$data['request'] = $msg;			
		}
		if ( $subject == 'Response' ) {
			if ( strlen($msg) > 65000 ) {
				$limit = get_option( 'wplister_log_record_limit', 4096 );
				$msg   = substr($msg, 0, $limit ) . "\n\n-- result was bigger than 64k - truncated to $limit bytes";				
			}
			$data['response'] = $msg;			
		}


		if ($this->debugLogDestination) {
			if ($this->debugLogDestination == 'db') {
				
				// insert into db
				if ( isset($data['ebay_id']) ) $data['ebay_id'] = floatval( $data['ebay_id'] );
				$wpdb->update($wpdb->prefix.'ebay_log', $data, array( 'id' => $this->id ));
				if ( $wpdb->last_error ) echo 'Error in WPL_EbatNs_Logger::log() - subject '.$subject.' - '.$wpdb->last_error.'<br>'.$wpdb->last_query;

			}
		}

	}

    function updateLog( $data )
    {
        global $wpdb;

        if ($this->debugLogDestination == 'db') {
            
            // insert into db
            $wpdb->update($wpdb->prefix.'ebay_log', $data, array( 'id' => $this->id ));
            if ( $wpdb->last_error ) echo 'Error in WPL_EbatNs_Logger::updateLog() - '.$wpdb->last_error.'<br>'.$wpdb->last_query;

        }

    } // updateLog()
	
	function logXml($xmlMsg, $subject = null)
	{
		if ($this->debugSecureLogging) {
			$xmlMsg = preg_replace("/<eBayAuthToken>.*<\/eBayAuthToken>/", "<eBayAuthToken>...</eBayAuthToken>", $xmlMsg);
			$xmlMsg = preg_replace("/<AuthCert>.*<\/AuthCert>/", "<AuthCert>...</AuthCert>", $xmlMsg);
		}
				
		$this->log($xmlMsg, $subject);
	}
}

