<?php
#   PLEASE DO NOT REMOVE OR CHANGE THIS COPYRIGHT BLOCK
#   ====================================================================
#
#    Quran Analysis (www.qurananalysis.com). Full Semantic Search and Intelligence System for the Quran.
#    Copyright (C) 2015  Karim Ouda
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#    You can use Quran Analysis code, framework or corpora in your website
#	 or application (commercial/non-commercial) provided that you link
#    back to www.qurananalysis.com and sufficient credits are given.
#
#  ====================================================================


#CODE SOURCE:https://msdn.microsoft.com/en-us/library/ff512421.aspx#phpexample

$accessToken = null;
$authHeader = null;

/**
 * Refreshes the OAuth access token for the Microsoft Translator API.
 *
 * NOTE: You must replace "YOUR_ID" and "YOUR_SECRET" with your actual
 * Microsoft Azure application credentials.
 *
 * @return string The authorization header string with the new access token.
 */
function refreshAccessToken()
{
	
	// Client ID of the application.
	$clientID = "YOUR_ID";
	// Client Secret key of the application.
	$clientSecret = "YOUR_SECRET";
	// OAuth Url.
	$authUrl = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
	// Application Scope Url
	$scopeUrl = "http://api.microsofttranslator.com";
	// Application grant type
	$grantType = "client_credentials";
	
	// Create the AccessTokenAuthentication object.
	$authObj = new AccessTokenAuthentication ();
	
	// Get the Access token.
	$accessToken = $authObj->getTokens ( $grantType, $scopeUrl, $clientID, $clientSecret, $authUrl );
	// Create the authorization Header string.
	$authHeader = "Authorization: Bearer " . $accessToken;
	
	return $authHeader;
}


/**
 * Handles the OAuth process to obtain an access token from Microsoft Azure.
 */
class AccessTokenAuthentication 
{
	/**
	 * Get the access token.
	 *
	 * @param string $grantType    Grant type.
	 * @param string $scopeUrl     Application Scope URL.
	 * @param string $clientID     Application client ID.
	 * @param string $clientSecret Application client secret.
	 * @param string $authUrl      OAuth URL.
	 * @return string The access token.
	 * @throws Exception If there is a cURL error or an error from the OAuth service.
	 */
	function getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl) {
		try {
			// Initialize the Curl Session.
			$ch = curl_init ();
			// Create the request Array.
			$paramArr = array (
					'grant_type' => $grantType,
					'scope' => $scopeUrl,
					'client_id' => $clientID,
					'client_secret' => $clientSecret 
			);
			// Create an Http Query.//
			$paramArr = http_build_query ( $paramArr );
			// Set the Curl URL.
			curl_setopt ( $ch, CURLOPT_URL, $authUrl );
			// Set HTTP POST Request.
			curl_setopt ( $ch, CURLOPT_POST, TRUE );
			// Set data to POST in HTTP "POST" Operation.
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $paramArr );
			// CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			// CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
			// Execute the cURL session.
			$strResponse = curl_exec ( $ch );
			// Get the Error Code returned by Curl.
			$curlErrno = curl_errno ( $ch );
			if ($curlErrno) {
				$curlError = curl_error ( $ch );
				throw new Exception ( $curlError );
			}
			// Close the Curl Session.
			curl_close ( $ch );
			// Decode the returned JSON string.
			$objResponse = json_decode ( $strResponse );
			if ($objResponse->error) {
				throw new Exception ( $objResponse->error_description );
			}
			return $objResponse->access_token;
		} catch ( Exception $e ) {
			echo "Exception-" . $e->getMessage ();
		}
	}
}
/**
 * Processes the HTTP requests to the Microsoft Translator API.
 */
class HTTPTranslator {
	/**
	 * Create and execute the HTTP cURL request.
	 *
	 * @param string $url        HTTP URL.
	 * @param string $authHeader Authorization Header string.
	 * @param string $postData   Data to post (optional).
	 * @return string The response from the cURL request.
	 * @throws Exception If there is a cURL error.
	 */
	function curlRequest($url, $authHeader, $postData = '') {
		// Initialize the Curl Session.
		$ch = curl_init ();
		// Set the Curl url.
		curl_setopt ( $ch, CURLOPT_URL, $url );
		// Set the HTTP HEADER Fields.
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
				$authHeader,
				"Content-Type: text/xml" 
		) );
		// CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		// CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, False );
		if ($postData) {
			// Set HTTP POST Request.
			curl_setopt ( $ch, CURLOPT_POST, TRUE );
			// Set data to POST in HTTP "POST" Operation.
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postData );
		}
		// Execute the cURL session.
		$curlResponse = curl_exec ( $ch );
		// Get the Error Code returned by Curl.
		$curlErrno = curl_errno ( $ch );
		if ($curlErrno) {
			$curlError = curl_error ( $ch );
			throw new Exception ( $curlError );
		}
		// Close a cURL session.
		curl_close ( $ch );
		return $curlResponse;
	}
	/**
	 * Create Request XML Format.
	 *
	 * @param string $languageCode Language code.
	 * @return string The request XML.
	 * @throws Exception If the language code is empty.
	 */
	function createReqXML($languageCode) {
		// Create the Request XML.
		$requestXml = '<ArrayOfstring xmlns="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">';
		if ($languageCode) {
			$requestXml .= "<string>$languageCode</string>";
		} else {
			throw new Exception ( 'Language Code is empty.' );
		}
		$requestXml .= '</ArrayOfstring>';
		return $requestXml;
	}
}

/**
 * Translates a given text using the Microsoft Translator API.
 *
 * @param string $text          The text to translate.
 * @param string $translateFrom The source language code (e.g., "en").
 * @param string $translateTo   The target language code (e.g., "ar").
 * @return string|false|null The translated text, false if the input text is empty, or null on API error.
 */
function translateText($text,$translateFrom="en",$translateTo="ar")
{
	global $authHeader;
	
	$text = trim($text);
	
	if ( empty($text)) return false;
	
	echoN("*** translating ... [$text]");


	//return $text;

	try {

		
		if ( $authHeader == null)
		{
			$authHeader = refreshAccessToken();
		}
			
		
		//echon($authHeader);
		/*
		 * REFERENCE:
		 * https://msdn.microsoft.com/en-us/library/ff512421.aspx
		 */
		
		// Create the Translator Object.
		$translatorObj = new HTTPTranslator ();
		
		 //Set the params.//
	    $fromLanguage = $translateFrom;
	    $toLanguage   = $translateTo;
	  
	    $contentType  = 'text/plain';
	    $category     = 'general';
	    
	    $params = "text=".urlencode($text)."&to=".$toLanguage."&from=".$fromLanguage;
    
		// HTTP Detect Method URL.
		 $translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/Translate?$params";
		// Call the curlRequest.
		$strResponse = $translatorObj->curlRequest ( $translateUrl, $authHeader );
		// Interprets a string of XML into an object.
		$xmlObj = simplexml_load_string ( $strResponse );
		
		//var_dump($xmlObj);
		
		if ( empty($xmlObj[0])) return null;
		

		$translation = trim((string)$xmlObj[0]);
		//echoN($translation);
		//(string) casting to avoid SimpleXMLElement serialization problem
		// trim for endlines
		return $translation;

	} catch ( Exception $e ) {
		echo "Exception: " . $e->getMessage () . PHP_EOL;
	}
	
}

?>