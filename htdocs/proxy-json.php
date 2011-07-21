<?php

require_once(dirname(__FILE__) . '/config.inc.php');

// Set your return content type
header('Content-type: application/json');

global $BALANCE_JSON;

// Set local variables
$url = $BALANCE_JSON[$_REQUEST['pool']]['url'];
$exec = $BALANCE_JSON[$_REQUEST['pool']]['exec'];
$confirmed = $BALANCE_JSON[$_REQUEST['pool']]['confirmed'];
$unconfirmed = $BALANCE_JSON[$_REQUEST['pool']]['unconfirmed'];

// Open URL if available
if ($url) {
    // Start curl session and retrieve contents
    $ch = curl_init();
    curl_setopt_array($ch, array(CURLOPT_URL => $url,
                                  CURLOPT_HEADER => 0,
                                  CURLOPT_RETURNTRANSFER => 1,
                                  CURLOPT_FOLLOWLOCATION => 1,
                                  CURLOPT_CONNECTTIMEOUT => 2,
                                  CURLOPT_TIMEOUT => 4)
                     );
    $html = curl_exec($ch);
    curl_close($ch);

    if ($html) {
        echo $html;
    } else {
        echo "{\"$confirmed\":\"0\",\"$unconfirmed\":\"0\"}";
    }
// Else open command if available
} elseif ($exec) {
    echo exec($exec);
// Else return nothing
} else {
    echo "{\"$confirmed\":\"0\",\"$unconfirmed\":\"0\"}";
}
?>
