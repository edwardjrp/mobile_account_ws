<?php
/**
 * Created by BlackCube Technologies
 * Developer: Edward Rodriguez
 * Date: 9/10/2012
 * Time: 11:38 AM
 * 
 */

define("PATH",".");
define("RelativePath", PATH);
date_default_timezone_set("America/Santo_Domingo");
include_once("Common.php");
include_once("includes/ws/nusoap.php");

/*
 * Web service registration information
 *
 * */
$server = new nusoap_server;
//Web service methods definition
$server->configureWSDL("viva","urn:viva");
//$server->wsdl->schemaTargetNamespace = "urn:viva";
$server->soap_defencoding = "UTF-8";
$server->register("checkaccount",
	array("account" => "xsd:string","token" => "xsd:string","nonce" => "xsd:string"),
	array("return" => "xsd:string"),
	"urn:viva",
	"urn:viva#checkaccount"
);

$server->register("addaccount",
	array("account" => "xsd:string","token" => "xsd:string","nonce" => "xsd:string"),
	array("return" => "xsd:string"),
	"urn:viva",
	"urn:viva#addaccount"
);

$server->register("deleteaccount",
	array("account" => "xsd:string","token" => "xsd:string","nonce" => "xsd:string"),
	array("return" => "xsd:string"),
	"urn:viva",
	"urn:viva#deleteaccount"
);

$server->register("getlogs",
	array("fecha1" => "xsd:string","fecha2" => "xsd:string","token" => "xsd:string","nonce" => "xsd:string"),
	array("return" => "xsd:string"),
	"urn:viva",
	"urn:viva#getlogs"
);

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

function checkaccount($account,$token,$nonce) {
    $account = trim($account); $token = trim($token); $nonce = trim($nonce);	
    return json_encode(existaccount($account,$token,$nonce));

    //"resultcode" => "0","resultstring" => "OK"
    //"resultcode" => "1","resultstring" => "Non existing or invalid wtoken"
    //"resultcode" => "2","resultstring" => "Non existing or invalid account"
    //"resultcode" => "3","resultstring" => "Bad authentication token"
    //"resultcode" => "5","resultstring" => "Service unavailable"
}

function addaccount($account,$token,$nonce) {
    $account = trim($account); $token = trim($token); $nonce = trim($nonce);
    return json_encode(newaccount($account,$token,$nonce));

}

function deleteaccount($account,$token,$nonce) {
    $account = trim($account); $token = trim($token); $nonce = trim($nonce);
    return json_encode(removeaccount($account,$token,$nonce));

}

function getlogs($fecha1,$fecha2,$token,$nonce) {
    $fecha1 = trim($fecha1); $fecha2 = trim($fecha2); $token = trim($token); $nonce = trim($nonce);
    //return "fecha1: $fecha1 - fecha2: $fecha2 - token: $token - nonce: $nonce";
    return json_encode(getlogs_bydate($fecha1,$fecha2,$token,$nonce));

}


function existaccount($account,$token,$nonce) {
    $db = new clsDBdbConnection();
    
    $sql = " select id,username from ws_users where md5(concat(username,':','$nonce',':',password,':','$account')) = '$token'";
    $db->query($sql);
    $db->next_record();
    $id = $db->f("id");
    $username = $db->f("username");

    if ($id > 0) {
        //Authentication token is valid
        $accountvalid = CCDLookUp("1 as valid","openaccounts","phoneaccount = '$account'",$db);
        if ($accountvalid == "1") {
            $sql = "insert into ws_logs (username,nonce,authtoken,account,result) ";
            $sql .= " values('$username','$nonce','$token','$account',0)";
            $db->query($sql);
            $db->next_record();
            $db->close();

            $result = array();
            $result["resultcode"] = 0;
            $result["result"] = array("accountexist" => "1");
            return $result;

        } else {
            $sql = "insert into ws_logs (username,nonce,authtoken,account,result) ";
            $sql .= " values('$username','$nonce','$token','$account',2)";
            $db->query($sql);
            $db->next_record();
            $db->close();

            $result = array();
            $result["resultcode"] = 0;
            $result["result"] = array("accountexist" => "0");
            return $result;

        }

    } else {
        //Invalid authentication
        $sql = "insert into ws_logs (username,nonce,authtoken,account,result) ";
        $sql .= " values('<BAD-AUTH>','$nonce','$token','$account',3)";
        $db->query($sql);
        $db->next_record();
        $db->close();

        $result = array();
        $result["resultcode"] = 3;
        $result["result"] = "Bad authentication token";
        return $result;

    }


}


function newaccount($account,$token,$nonce) {
    $db = new clsDBdbConnection();

    $sql = " select id,username from ws_users where md5(concat(username,':','$nonce',':',password,':','$account')) = '$token'";
    $db->query($sql);
    $db->next_record();
    $id = $db->f("id");
    $username = $db->f("username");

    if ($id > 0) {
        //Authentication token is valid
        $sql = "insert into ws_logs (username,nonce,authtoken,account,result) ";
        $sql .= " values('$username','$nonce','$token','$account',0)";
        $db->query($sql);
        $db->next_record();

        $accountvalid = CCDLookUp("1 as valid","openaccounts","phoneaccount = '$account'",$db);
        if ($accountvalid == "1") {
            $db->close();

            $result = array();
            $result["resultcode"] = 0;
            $result["result"] = array("accountadded" => "0");
            return $result;

        } else {
            $sql = "insert into openaccounts (phoneaccount) values('$account') ";
            $db->query($sql);
            $db->next_record();
            $db->close();

            $result = array();
            $result["resultcode"] = 0;
            $result["result"] = array("accountadded" => "1");
            return $result;

        }


    } else {
        //Invalid authentication
        $sql = "insert into ws_logs (username,nonce,authtoken,account,result) ";
        $sql .= " values('<BAD-AUTH>','$nonce','$token','$account',3)";
        $db->query($sql);
        $db->next_record();
        $db->close();

        $result = array();
        $result["resultcode"] = 3;
        $result["result"] = "Bad authentication token";
        return $result;

    }


}


function removeaccount($account,$token,$nonce) {
    $db = new clsDBdbConnection();

    $sql = " select id,username from ws_users where md5(concat(username,':','$nonce',':',password,':','$account')) = '$token'";
    $db->query($sql);
    $db->next_record();
    $id = $db->f("id");
    $username = $db->f("username");

    if ($id > 0) {
        //Authentication token is valid
        $sql = "insert into ws_logs (username,nonce,authtoken,account,result) ";
        $sql .= " values('$username','$nonce','$token','$account',0)";
        $db->query($sql);
        $db->next_record();

        $accountvalid = (int)CCDLookUp("id","openaccounts","phoneaccount = '$account'",$db);
        if ($accountvalid > 0) {
            $sql = "delete from openaccounts where id = $accountvalid ";
            $db->query($sql);
            $db->next_record();
            $db->close();
            
            $result = array();
            $result["resultcode"] = 0;
            $result["result"] = array("accountdeleted" => "1");
            return $result;

        } else {
            $db->close();

            $result = array();
            $result["resultcode"] = 0;
            $result["result"] = array("accountdeleted" => "0");
            return $result;

        }
        

    } else {
        //Invalid authentication
        $sql = "insert into ws_logs (username,nonce,authtoken,account,result) ";
        $sql .= " values('<BAD-AUTH>','$nonce','$token','$account',3)";
        $db->query($sql);
        $db->next_record();
        $db->close();

        $result = array();
        $result["resultcode"] = 3;
        $result["result"] = "Bad authentication token";
        return $result;

    }


}


function getlogs_bydate($fecha1,$fecha2,$token,$nonce) {

    $db = new clsDBdbConnection();

    $sql = " select id,username from ws_users where md5(concat(username,':','$nonce',':',password)) = '$token'";
    $db->query($sql);
    $db->next_record();
    $id = $db->f("id");
    $username = $db->f("username");

    if ($id > 0) {
        //Authentication token is valid
        $sql = "insert into ws_logs (username,nonce,authtoken,account,result) ";
        $sql .= " values('$username','$nonce','$token','$account',0)";
        $db->query($sql);
        $db->next_record();

        $sql = "select a.clientip,a.account,a.recorddate,(select 1 from openaccounts b where b.phoneaccount = a.account) as openaccount
                from logredirect a where date(a.recorddate) between '$fecha1' and '$fecha2'";
        $db->query($sql);
        $logs = array();
        $cont = 0;
        while ($db->next_record()) {
            $logs[$cont]["clientip"] = $db->f("clientip");
            $logs[$cont]["account"] = $db->f("account");
            $logs[$cont]["recorddate"] = $db->f("recorddate");
            $db->f("openaccount") == 1 ? $logs[$cont]["premium"] = 1 : $logs[$cont]["premium"] = 0;
            $cont++;
        }

        $db->close();
        $result = array();
        $result["resultcode"] = 0;
        $result["result"] = $logs;
        return $result;

    } else {
        //Invalid authentication
        $sql = "insert into ws_logs (username,nonce,authtoken,result) ";
        $sql .= " values('<BAD-AUTH>','$nonce','$token',3)";
        $db->query($sql);
        $db->next_record();
        $db->close();

        $result = array();
        $result["resultcode"] = 3;
        $result["result"] = "Bad authentication token";
        return $result;

    }

}

?>