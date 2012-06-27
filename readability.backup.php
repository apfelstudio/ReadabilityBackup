<?php

die();
header("Content-Type: text/plain");

include "mydata.php";

$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);


curl_setopt($curl, CURLOPT_URL, 
	"https://www.readability.com/api/rest/v1/oauth/access_token" .
	"?oauth_consumer_key=" . $data['consumerkey'] .
	"&oauth_timestamp=" . time() . 
	"&oauth_nonce=" . md5(time() . "salt").
	"&x_auth_username=" . $data['username'] . 
	"&x_auth_password=" . $data['userpwd'] . 
	"&x_auth_mode=client_auth" .
	"&oauth_signature=" . $data['oauth_signature'] .
	"&oauth_signature_method=PLAINTEXT");
$oauth_result = curl_exec($curl);
parse_str($oauth_result, $tokens);

curl_setopt($curl, CURLOPT_URL, 
	"https://www.readability.com/api/rest/v1/bookmarks" .
	"?oauth_token=" . $tokens["oauth_token"] .
	"&oauth_consumer_key=" . $data['consumerkey'] . 
	"&oauth_timestamp=" . time() . 
	"&oauth_nonce=" . md5(time() . "salt").
	"&oauth_signature=" . $data['oauth_signature'] . $tokens["oauth_token_secret"] .
	"&oauth_signature_method=PLAINTEXT" . 
	"&archive=1"
);
$json = json_decode(curl_exec($curl));

foreach($json->bookmarks as $b)
{
	$echo = $b->article->id . PHP_EOL
	   . $b->article->title . PHP_EOL
	   . $b->article->url . PHP_EOL
	   . PHP_EOL;
}

curl_setopt($curl, CURLOPT_URL, 
	"https://www.readability.com/api/rest/v1/articles/" . $json->bookmarks[3]->article->id .
	"?oauth_token=" . $tokens["oauth_token"] .
	"&oauth_consumer_key=" . $data['consumerkey'] . 
	"&oauth_timestamp=" . time() . 
	"&oauth_nonce=" . md5(time() . "salt").
	"&oauth_signature=" . $data['oauth_signature'] . $tokens["oauth_token_secret"] .
	"&oauth_signature_method=PLAINTEXT"
);
$json2 = json_decode(curl_exec($curl));

var_dump($json2);