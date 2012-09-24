<?php

define('API_KEY',      "");
define('API_SECRET',   "");
define('CALLBACK_URL', "");

function createNonce()
{
	return md5(time() . mt_rand());
}

	// start session
session_start();

// Initialize cURL
$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);

// Show Login Screen
if(!isset($_SESSION['oauth_token']))
{
	// Request token and secret
	curl_setopt($curl, CURLOPT_URL,
		"https://www.readability.com/api/rest/v1/oauth/request_token" .
		"?oauth_signature=" . API_SECRET . "%26" .
		"&oauth_consumer_key=" . API_KEY .
		"&oauth_timestamp=" . time() .
		"&oauth_nonce=" . createNonce());
	parse_str(curl_exec($curl), $tokens);
	$_SESSION['oauth_token']        = $tokens['oauth_token'];
	$_SESSION['oauth_token_secret'] = $tokens['oauth_token_secret'];
	
	// Create link to login to Readability
	$loginLink = "https://www.readability.com/api/rest/v1/oauth/authorize" .
	"?oauth_callback=" . CALLBACK_URL .
	"&oauth_token=" . $tokens['oauth_token'];
	
	echo <<<HTMLOUTPUT
<!DOCTYPE html>
<html>
<head>
	<title>Apfelstudio Readability Backup</title>
</head>
<body>
	<a href="$loginLink">Login to Readability</a>
</body>
</html>
HTMLOUTPUT;
	
}

// Logout
elseif(isset($_GET['logout']))
{
	unset($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
}
// Show Bookmark List
else
{	
	header("Content-Type: text/plain");
	
	parse_str($_SERVER['QUERY_STRING'], $tokens);
	$_SESSION['oauth_verifier'] = $tokens['oauth_verifier'];
	
	curl_setopt($curl, CURLOPT_URL, 
		"https://www.readability.com/api/rest/v1/oauth/access_token" .
		"?oauth_consumer_key=" . API_KEY .
		"&oauth_timestamp=" . time() . 
		"&oauth_verifier=" . $_SESSION['oauth_verifier'] .
		"&oauth_token=" . $_SESSION['oauth_token'] .
		"&oauth_token_secret=" . $_SESSION['oauth_token_secret'] .
		"&oauth_nonce=" . createNonce() .
		"&oauth_signature=" . API_SECRET . "%26" . $_SESSION['oauth_token_secret'] .
		"&oauth_signature_method=PLAINTEXT");
	parse_str(curl_exec($curl), $tokens);
	
	curl_setopt($curl, CURLOPT_URL, 
		"https://www.readability.com/api/rest/v1/bookmarks" .
		"?oauth_consumer_key=" . API_KEY .
		"&oauth_timestamp=" . time() . 
		"&oauth_token=" . $tokens['oauth_token'] .
		"&oauth_nonce=" . createNonce() .
		"&oauth_signature=" . API_SECRET . "%26" . $tokens["oauth_token_secret"] .
		"&oauth_signature_method=PLAINTEXT" . 
		"&archive=1");
	$json = json_decode(curl_exec($curl));
	
	foreach($json->bookmarks as $b)
	{
		echo $b->article->id . PHP_EOL
		. $b->article->title . PHP_EOL
		. $b->article->url . PHP_EOL
		. PHP_EOL;
		
	}
}