<?php
 
// The response object constructs the TwiML for our applet
$tResponse = new TwimlResponse;

//construct zendesk client object
define("ZDAPIKEY", AppletInstance::getValue('apitoken'));
define("ZDUSER", AppletInstance::getValue('email'));
define("ZDURL", "https://" . AppletInstance::getValue('subdomain') . ".zendesk.com/api/v2");
 
/* Note: do not put a trailing slash at the end of v2 */

function curlWrap($url, $json, $action)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );

    curl_setopt($ch, CURLOPT_USERPWD, ZDUSER."/token:".ZDAPIKEY);
    $url = ZDURL.$url;
    switch($action){
        case "POST":
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            break;
        case "GET":
            $url .= '?' . http_build_query($json, '', '&', PHP_QUERY_RFC3986);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            break;
        case "PUT":
            curl_setopt($ch, CURLOPT_URL, ZDURL.$url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            break;
        case "DELETE":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            break;
        default:
            break;
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = curl_exec($ch);
    curl_close($ch);
    $decoded = json_decode($output, TRUE);
    return $decoded;
}

 
$phone = normalize_phone_to_E164($_REQUEST['From']);
$phone = str_replace('+', '', $phone);

//zendesk api call

$response = curlWrap("/search.json", array("query" => "type:user phone:" . $phone), "GET");

if($response['count'] == 0){
  //create a new user
  $response = curlWrap("/users.json", json_encode(array("user" => array("name"=>"Unknown Phone#: " . $phone, "phone"=>$phone))), "POST"); 
  //create a new ticket in the new user's name
  $response = curlWrap("/tickets.json", json_encode(array("ticket"=> array("subject" => "New Phone Call From " . $phone, "comment" => array("body" => "call made at " . date('r')), "requester_id" => $response['user']['id']))), "POST");
}else if($response['count'] == 1){
  //create a new ticket in the name of the current user
  $response = curlWrap("/tickets.json", json_encode(array("ticket"=> array("subject" => "New Phone Call From " . $phone . " at " . date('r'), "comment" => array("body" => "Call came in at " . date('r')), "requester_id" => $response['results'][0]['id']))), "POST");
}


// openvbx code below
// $primary is getting the url created by what ever applet was put 
// into the primary dropzone
$primary = AppletInstance::getDropZoneUrl('primary');
 
// As long as the primary dropzone is not empty add the redirect 
// twiml to $response
if(!empty($primary)) {
    $tResponse->redirect($primary);
};
 
// This will create the twiml for hellomonkey
$tResponse->respond();
