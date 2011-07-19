<?php
// Returns balance data from Bitcoins.lc as JSON data for Bitcoin-mining-proxy
//
// Written by Raetha based on bash script by echiu64
// 2011-07-19
//

// Load required files
require_once(dirname(__FILE__) . '/../common.inc.php');

// Intialize cookie jar
$cookiejar = trim(`mktemp`);

// Get login_token
$ch = curl_init();
curl_setopt_array($ch, array(CURLOPT_URL => 'http://www.bitcoins.lc/',
                             CURLOPT_HEADER => 0,
                             CURLOPT_RETURNTRANSFER => 1,
                             CURLOPT_COOKIEJAR => $cookiejar)
                 );
$html = curl_exec($ch);
curl_close($ch);
preg_match("/name=\"_csrf_token\" value=\"(.*)\"/siU", $html, $csrf_token);

// Prepare post fields
$post_fields = array('_csrf_token' => $csrf_token[1], 'action' => 'login', 'email' => $BALANCE_JSON['bitcoins-lc']['email'], 'login' => 'Proceed', 'password' => $BALANCE_JSON['bitcoins-lc']['password']);

// Get real data
$ch2 = curl_init();
curl_setopt_array($ch2, array(CURLOPT_URL => 'https://www.bitcoins.lc/',
                              CURLOPT_HEADER => 0,
                              CURLOPT_RETURNTRANSFER => 1,
                              CURLOPT_COOKIEFILE => $cookiejar,
                              CURLOPT_FOLLOWLOCATION => 1,
                              CURLOPT_REFERER => 'http://www.bitcoins.lc/',
                              CURLOPT_POST => 1,
                              CURLOPT_POSTFIELDS => $post_fields)
                 );
$html2 = curl_exec($ch2);
curl_close($ch2);

// Match data
preg_match("/Account balance<\/b><br>\s*(\d.*) BTC/siU", $html2, $balance);
preg_match("/Estimated earnings<\/b><br>\s*(\d.*) BTC/siU", $html2, $estimate);

// Return desired JSON values
echo "{\"balance\": \"$balance[1]\", \"unconfirmed\": \"$estimate[1]\"}";

//Cleanup
$ret = unlink($cookiejar);
?>
