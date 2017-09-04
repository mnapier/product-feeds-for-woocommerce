<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
/**
* AMAZON API REQUEST HANDLER
*/
class Amazon_Request_Handler
{
	var $service;
	var $request;
	function __construct()
	{
		define ('DATE_FORMAT', 'Y-m-d\TH:i:s\Z');
	}

	function invokeListMarketplaceParticipations($service,$request){
		 try {
	        $response = $service->ListMarketplaceParticipations($request);
	        if ($response->isSetListMarketplaceParticipationsResult()) {
                $listMarketplaceParticipationsResult = $response->getListMarketplaceParticipationsResult();

                // process marketplaces
                $listMarketplaces = $listMarketplaceParticipationsResult->getListMarketplaces();
                $marketplaceList = $listMarketplaces->getMarketplace();
                foreach ($marketplaceList as $marketplace) {

                    $key = $marketplace->getMarketplaceId();

                    $allowed_markets[ $key ] = new stdClass();
                    $allowed_markets[ $key ]->MarketplaceId       = $marketplace->getMarketplaceId();
                    $allowed_markets[ $key ]->Name                = $marketplace->getName();
                    $allowed_markets[ $key ]->DefaultLanguageCode = $marketplace->getDefaultLanguageCode();
                    $allowed_markets[ $key ]->DefaultCountryCode  = $marketplace->getDefaultCountryCode();
                    $allowed_markets[ $key ]->DefaultCurrencyCode = $marketplace->getDefaultCurrencyCode();
                    $allowed_markets[ $key ]->DomainName          = $marketplace->getDomainName();
                }

                // process participations
                $listParticipations = $listMarketplaceParticipationsResult->getListParticipations();
                $participationList = $listParticipations->getParticipation();
                foreach ($participationList as $participation) {

                    $key = $marketplace->getMarketplaceId();
                    $allowed_markets[ $key ]->MarketplaceId              = $participation->getMarketplaceId();
                    $allowed_markets[ $key ]->SellerId                   = $participation->getSellerId();
                    $allowed_markets[ $key ]->HasSellerSuspendedListings = $participation->getHasSellerSuspendedListings();
                }
                // echo "<pre>allowed_markets: ";print_r($allowed_markets);echo"</pre>";#die();

                $result = new stdClass();
                $result->allowed_markets = $allowed_markets;
                $result->success = true;
                return $result;
            }

	     } catch ( MarketplaceWebServiceSellers_Exception $ex ) {
            $error = new stdClass();
            $error->ErrorMessage = $ex->getMessage();
            $error->ErrorCode    = $ex->getErrorCode();
            $error->StatusCode   = $ex->getStatusCode();
            return $error;
        }

        $result = new stdClass();
        $result->success = false;
        return $result;
 	}

     function invokeSubmitFeed(MarketplaceWebService_Interface $service, $request) 
  {
      try {
              $response = $service->submitFeed($request);
              
                if ($response->isSetSubmitFeedResult()) { 
              
                    $submitFeedResult = $response->getSubmitFeedResult();
                    if ($submitFeedResult->isSetFeedSubmissionInfo()) { 

              
                        $feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();
                        if ($feedSubmissionInfo->isSetFeedSubmissionId()) 
                        {
                            $result['FeedSubmissionInfo']['FeedSubmissionId'] = $feedSubmissionInfo->getFeedSubmissionId(); 
              
                        }
                        if ($feedSubmissionInfo->isSetFeedType()) 
                        {
                            $result['FeedSubmissionInfo']['FeedType'] = $feedSubmissionInfo->getFeedType();
                        }
                        if ($feedSubmissionInfo->isSetSubmittedDate()) 
                        {
                            $result['FeedSubmissionInfo']['SubmittedDate'] = $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT);
                        }
                        if ($feedSubmissionInfo->isSetFeedProcessingStatus()) 
                        {
                            $result['FeedSubmissionInfo']['FeedProcessingStatus'] = $feedSubmissionInfo->getFeedProcessingStatus();
                        }
                        if ($feedSubmissionInfo->isSetStartedProcessingDate()) 
                        {
                            $result['FeedSubmissionInfo']['StartedProcessingDate'] = $feedSubmissionInfo->getStartedProcessingDate()->format(DATE_FORMAT);
                        }
                        if ($feedSubmissionInfo->isSetCompletedProcessingDate()) 
                        {
                            $result['FeedSubmissionInfo']['CompletedProcessingDate'] = $feedSubmissionInfo->getCompletedProcessingDate()->format(DATE_FORMAT);
                        }
                    } 
                } 
                if ($response->isSetResponseMetadata()) { 

                    // echo("            ResponseMetadata\n");
                    $responseMetadata = $response->getResponseMetadata();
                    if ($responseMetadata->isSetRequestId()) 
                    {
                        $result['ResponseMetadata']['RequestId'] = $responseMetadata->getRequestId();
                        // echo("                RequestId\n");
                        // echo("                    " . $responseMetadata->getRequestId() . "\n");
                    }
                } 
                    $result['ResponseMetadata']['ResponseHeaderMetadata'] = $response->getResponseHeaderMetadata();
                    $result['status'] = 1;
                // echo("            ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");
                   return $result;
     } catch (MarketplaceWebService_Exception $ex) {
         echo("Caught Exception: " . $ex->getMessage() . "\n");
         echo("Response Status Code: " . $ex->getStatusCode() . "\n");
         echo("Error Code: " . $ex->getErrorCode() . "\n");
         echo("Error Type: " . $ex->getErrorType() . "\n");
         echo("Request ID: " . $ex->getRequestId() . "\n");
         echo("XML: " . $ex->getXML() . "\n");
         echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
         $result['status'] = 0;
         return $result; 
     }
 }

 function invokeGetFeedSubmissionList(MarketplaceWebService_Interface $service, $request) 
  {
      try {
              $response = $service->getFeedSubmissionList($request);
              
                // echo ("Service Response\n");
                // echo ("=============================================================================\n");

                // echo("        GetFeedSubmissionListResponse\n");
                if ($response->isSetGetFeedSubmissionListResult()) { 
                    // echo("            GetFeedSubmissionListResult\n");
                    $getFeedSubmissionListResult = $response->getGetFeedSubmissionListResult();
                    if ($getFeedSubmissionListResult->isSetNextToken()) 
                    {
                        // echo("                NextToken\n");
                        // echo("                    " . $getFeedSubmissionListResult->getNextToken() . "\n");
                    }
                    if ($getFeedSubmissionListResult->isSetHasNext()) 
                    {
                        // echo("                HasNext\n");
                        // echo("                    " . $getFeedSubmissionListResult->getHasNext() . "\n");
                    }
                    $feedSubmissionInfoList = $getFeedSubmissionListResult->getFeedSubmissionInfoList();
                    $i = 0;

                    foreach ($feedSubmissionInfoList as $feedSubmissionInfo) {
                        // echo("                FeedSubmissionInfo\n");
                        if ($feedSubmissionInfo->isSetFeedSubmissionId()) 
                        {
                            $result['FeedSubmissionInfo'][$i]['FeedSubmissionId'] = $feedSubmissionInfo->getFeedSubmissionId();
                            // echo("                    FeedSubmissionId\n");
                            // echo("                        " . $feedSubmissionInfo->getFeedSubmissionId() . "\n");
                        }
                        if ($feedSubmissionInfo->isSetFeedType()) 
                        {
                            $result['FeedSubmissionInfo'][$i]['FeedType'] = $feedSubmissionInfo->getFeedType();
                            // echo("                    FeedType\n");
                            // echo("                        " . $feedSubmissionInfo->getFeedType() . "\n");
                        }
                        if ($feedSubmissionInfo->isSetSubmittedDate()) 
                        {
                            $result['FeedSubmissionInfo'][$i]['SubmittedDate'] = $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT);
                            // echo("                    SubmittedDate\n");
                            // echo("                        " . $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT) . "\n");
                        }
                        if ($feedSubmissionInfo->isSetFeedProcessingStatus()) 
                        {
                            $result['FeedSubmissionInfo'][$i]['FeedProcessingStatus'] = $feedSubmissionInfo->getFeedProcessingStatus();
                            // echo("                    FeedProcessingStatus\n");
                            // echo("                        " . $feedSubmissionInfo->getFeedProcessingStatus() . "\n");
                        }
                        if ($feedSubmissionInfo->isSetStartedProcessingDate()) 
                        {
                            $result['FeedSubmissionInfo'][$i]['StartedProcessingDate'] = $feedSubmissionInfo->getStartedProcessingDate()->format(DATE_FORMAT);
                            // echo("                    StartedProcessingDate\n");
                            // echo("                        " . $feedSubmissionInfo->getStartedProcessingDate()->format(DATE_FORMAT) . "\n");
                        }
                        if ($feedSubmissionInfo->isSetCompletedProcessingDate()) 
                        {
                            $result['FeedSubmissionInfo'][$i]['CompletedProcessingDate'] = $feedSubmissionInfo->getCompletedProcessingDate()->format(DATE_FORMAT);
                            // echo("                    CompletedProcessingDate\n");
                            // echo("                        " . $feedSubmissionInfo->getCompletedProcessingDate()->format(DATE_FORMAT) . "\n");
                        }
                        $i++;
                    }
                } 
                if ($response->isSetResponseMetadata()) { 
                    // echo("            ResponseMetadata\n");
                    $responseMetadata = $response->getResponseMetadata();
                    if ($responseMetadata->isSetRequestId()) 
                    {
                        $result['ResponseMetadata']['RequestId'] = $responseMetadata->getRequestId(); 
                        // echo("                RequestId\n");
                        // echo("                    " . $responseMetadata->getRequestId() . "\n");
                    }
                } 

                $result['ResponseMetadata']['ResponseHeaderMetadata']=$response->getResponseHeaderMetadata();
                // echo("            ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");
                $result['result'] = 1;
     } catch (MarketplaceWebService_Exception $ex) {
         // echo("Caught Exception: " . $ex->getMessage() . "\n");
         // echo("Response Status Code: " . $ex->getStatusCode() . "\n");
         // echo("Error Code: " . $ex->getErrorCode() . "\n");
         // echo("Error Type: " . $ex->getErrorType() . "\n");
         // echo("Request ID: " . $ex->getRequestId() . "\n");
         // echo("XML: " . $ex->getXML() . "\n");
         // echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
         $result['result'] = 0;
         $result['Exception'] = $ex->getMessage();
         $result['Response Status Code'] = $ex->getStatusCode();
         $result['Error_Code'] = $ex->getErrorCode();
         $result['Error_Type'] = $ex->getErrorType();
         $result['Request_ID'] = $ex->getRequestId();
         $result['XML'] = $ex->getXML();
         $result['ResponseHeaderMetadata'] = $ex->getResponseHeaderMetadata();

     }

     // echo "<pre>";
     // print_r($result);
     // echo "<pre>";
     return $result;
 }

 function invokeCancelFeedSubmissions(MarketplaceWebService_Interface $service, $request) 
  {
      try {
              $response = $service->cancelFeedSubmissions($request);
              echo "<pre>";
              
              print_r($service->toXML());
           
                // echo ("Service Response\n");
                // echo ("=============================================================================\n");

                // echo("        CancelFeedSubmissionsResponse\n");
                // if ($response->isSetCancelFeedSubmissionsResult()) { 
                //     echo("            CancelFeedSubmissionsResult\n");
                //     $cancelFeedSubmissionsResult = $response->getCancelFeedSubmissionsResult();
                //     if ($cancelFeedSubmissionsResult->isSetCount()) 
                //     {
                //         echo("                Count\n");
                //         echo("                    " . $cancelFeedSubmissionsResult->getCount() . "\n");
                //     }
                //     $feedSubmissionInfoList = $cancelFeedSubmissionsResult->getFeedSubmissionInfoList();
                //     foreach ($feedSubmissionInfoList as $feedSubmissionInfo) {
                //         echo("                FeedSubmissionInfo\n");
                //         if ($feedSubmissionInfo->isSetFeedSubmissionId()) 
                //         {
                //             echo("                    FeedSubmissionId\n");
                //             echo("                        " . $feedSubmissionInfo->getFeedSubmissionId() . "\n");
                //         }
                //         if ($feedSubmissionInfo->isSetFeedType()) 
                //         {
                //             echo("                    FeedType\n");
                //             echo("                        " . $feedSubmissionInfo->getFeedType() . "\n");
                //         }
                //         if ($feedSubmissionInfo->isSetSubmittedDate()) 
                //         {
                //             echo("                    SubmittedDate\n");
                //             echo("                        " . $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT) . "\n");
                //         }
                //         if ($feedSubmissionInfo->isSetFeedProcessingStatus()) 
                //         {
                //             echo("                    FeedProcessingStatus\n");
                //             echo("                        " . $feedSubmissionInfo->getFeedProcessingStatus() . "\n");
                //         }
                //         if ($feedSubmissionInfo->isSetStartedProcessingDate()) 
                //         {
                //             echo("                    StartedProcessingDate\n");
                //             echo("                        " . $feedSubmissionInfo->getStartedProcessingDate()->format(DATE_FORMAT) . "\n");
                //         }
                //         if ($feedSubmissionInfo->isSetCompletedProcessingDate()) 
                //         {
                //             echo("                    CompletedProcessingDate\n");
                //             echo("                        " . $feedSubmissionInfo->getCompletedProcessingDate()->format(DATE_FORMAT) . "\n");
                //         }
                //     }
                // } 
                // if ($response->isSetResponseMetadata()) { 
                //     echo("            ResponseMetadata\n");
                //     $responseMetadata = $response->getResponseMetadata();
                //     if ($responseMetadata->isSetRequestId()) 
                //     {
                //         echo("                RequestId\n");
                //         echo("                    " . $responseMetadata->getRequestId() . "\n");
                //     }
                // } 

                // echo("            ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");
     } catch (MarketplaceWebService_Exception $ex) {
         echo("Caught Exception: " . $ex->getMessage() . "\n");
         echo("Response Status Code: " . $ex->getStatusCode() . "\n");
         echo("Error Code: " . $ex->getErrorCode() . "\n");
         echo("Error Type: " . $ex->getErrorType() . "\n");
         echo("Request ID: " . $ex->getRequestId() . "\n");
         echo("XML: " . $ex->getXML() . "\n");
         echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
     }
 }

 function invokeGetFeedSubmissionResult(MarketplaceWebService_Interface $service, $request) 
  {
    // $handle = fopen(wp_upload_dir()['basedir']."/test.xml",'w+');
    // $handle = fopen(dirname(__FILE__).'/Amazon/MarketplaceWebService/Mock/GetFeedSubmissionResultResponse.xml', 'w+');
    // $request->setFeedSubmissionResult($handle);
      try {

             $response = $service->getFeedSubmissionResult($request);
             echo ("Service Response\n");
                echo ("=============================================================================\n");

                echo("        GetFeedSubmissionResultResponse\n");
                if ($response->isSetGetFeedSubmissionResultResult()) {
                  $getFeedSubmissionResultResult = $response->getGetFeedSubmissionResultResult(); 
                  echo ("            GetFeedSubmissionResult");
                  
                  if ($getFeedSubmissionResultResult->isSetContentMd5()) {
                    echo ("                ContentMd5");
                    echo ("                " . base64_encode(md5($getFeedSubmissionResultResult->getContentMd5(), true)) . "\n");
                  }
                }
                if ($response->isSetResponseMetadata()) { 
                    echo("            ResponseMetadata\n");
                    $responseMetadata = $response->getResponseMetadata();
                    if ($responseMetadata->isSetRequestId()) 
                    {
                        echo("                RequestId\n");
                        echo("                    " . $responseMetadata->getRequestId() . "\n");
                    }
                } 

                echo("            ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");
     } catch (MarketplaceWebService_Exception $ex) {
         echo("Caught Exception: " . $ex->getMessage() . "\n");
         echo("Response Status Code: " . $ex->getStatusCode() . "\n");
         echo("Error Code: " . $ex->getErrorCode() . "\n");
         echo("Error Type: " . $ex->getErrorType() . "\n");
         echo("Request ID: " . $ex->getRequestId() . "\n");
         echo("XML: " . $ex->getXML() . "\n");
         echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
     }
 }
}
?>