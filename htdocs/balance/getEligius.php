<?php
// Returns balance data from Eligius as JSON data for Bitcoin-mining-proxy
//
// Written by Raetha based on bash script by echiu64
// 2011-07-19
//

// Load required files
require_once(dirname(__FILE__) . '/../common.inc.php');

// Get that website's content
$handle = fopen("http://eligius.st/~luke-jr/raw/5/balances.json", "r");

// If handle exists read in the data
if ($handle) {
    while (!feof($handle)) {
        $buffer = fgets($handle, 64);
        $data = $data . $buffer;
    }
    fclose($handle);

    // Decode JSON data
    $decoded_data = json_decode($data, TRUE);

    // Get my data values
    $json_balance = str_pad($decoded_data[$BALANCE_JSON['eligius']['userid']]['balance'],9,'0',STR_PAD_LEFT);

    // Format data correctly
    $pos = strlen($json_balance) - 8;
    $BALANCE = substr_replace($json_balance, ".", $pos) . substr($json_balance, $pos);;

    // Return JSON data to JSON handler
    echo "{\"balance\": \"$BALANCE\"}";
}
else {
    echo "Error";
}
?>
