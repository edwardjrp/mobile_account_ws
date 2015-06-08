<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EdwardData
 * Date: 9/10/2012
 * Time: 12:06 PM
 * To change this template use File | Settings | File Templates.
 */

ini_set("error_reporting","E_ERROR | E_PARSE");

include_once("includes/ws/nusoap.php");

//$tvs_url = "http://home.site/viva/accountsws/main.php?wsdl";
$tvs_url = "http://10.0.12.2/bct/accountsws/main.php?wsdl";
$tvs_username = "viva";
$tvs_password = "v1v4c3nt34cc3";

$account = "18093333312";
$nonce = md5($account.rand(10000,99000));
$fecha1 = "2012-11-01";
$fecha2 = "2012-11-15";

$authToken = md5($tvs_username.":".$nonce.":".md5($tvs_password).":".$account);
//$authToken = md5($tvs_username.":".$nonce.":".md5($tvs_password));

$client = new nusoap_client($tvs_url, true);
//echo $authToken;
//echo "<br>";
$result = $client->call('checkaccount',array('account'=>$account,'token'=>$authToken,'nonce'=>$nonce));
print_r($result)."<br>";

/*
echo "<h2>Request</h2>";
echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
echo"<hr>";
echo '<h2>Response</h2>';
echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
echo"<hr>";
//Display the debug messages
echo '<h2>Debug</h2>';
echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
echo"<hr>";
print_r($client->getError());
echo"<hr>";
print_r($client->fault);
echo"<hr>";
*/


//$result = $client->call('addaccount',array('account'=>$account,'token'=>$authToken,'nonce'=>$nonce));
//print_r($result)."<hr>";

//$result = $client->call('deleteaccount',array('account'=>$account,'token'=>$authToken,'nonce'=>$nonce));
//print_r($result)."<hr>";

//$result = $client->call('getlogs',array('fecha1'=>$fecha1,'fecha2' => $fecha2,'token'=>$authToken,'nonce'=>$nonce));
//print_r($result)."<br>";



?>