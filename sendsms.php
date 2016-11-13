<?php
//SERVER IP  eg localhost
$serverurl="http://localhost:5000/";



//HTTP REQUEST FUNCTION
function httpRequest($url){
    $pattern = "/http...([0-9a-zA-Z-.]*).([0-9]*).(.*)/";
    preg_match($pattern,$url,$args);
    $in = "";
    $fp = fsockopen("$args[1]", $args[2], $errno, $errstr, 30);
    if (!$fp) {
       return("$errstr ($errno)");
    } else {
        $out = "GET /$args[3] HTTP/1.1\r\n";
        $out .= "Host: $args[1]:$args[2]\r\n";
        $out .= "User-agent: Crowny PHP client\r\n";
        $out .= "Accept: */*\r\n";
        $out .= "Connection: Close\r\n\r\n";

        fwrite($fp, $out);
        while (!feof($fp)) {
           $in.=fgets($fp, 128);
        }
    }
    fclose($fp);
    return($in);
}


//SEND SMS FUNCTION
function SendSms($phone, $msg, $debug=false)
{
 	  global $serverurl;
	  
   	  $url=$serverurl;
	  $url.= 'sendsms?';
      $url.= 'Recipient='.encode_url($phone);
      $url.= '&Message='.encode_url($msg);

       if ($debug) { echo "Request URL: <br>$url<br><br>"; }

      //Open the URL to send the message
      $response = httpRequest($url);
      if ($debug) {
           echo "Response: <br><pre>".
           str_replace(array("<",">"),array("&lt;","&gt;"),$response).
           "</pre><br>"; }

      return($response);
}

function encode_url($url) {
$reserved = array(
":" => '!%3A!ui',
"/" => '!%2F!ui',
"?" => '!%3F!ui',
"#" => '!%23!ui',
"[" => '!%5B!ui',
"]" => '!%5D!ui',
"@" => '!%40!ui',
"!" => '!%21!ui',
"$" => '!%24!ui',
"&" => '!%26!ui',
"'" => '!%27!ui',
"(" => '!%28!ui',
")" => '!%29!ui',
"*" => '!%2A!ui',
"+" => '!%2B!ui',
"," => '!%2C!ui',
";" => '!%3B!ui',
"=" => '!%3D!ui',
"%" => '!%25!ui',
);

$url = rawurlencode($url);
$url = preg_replace(array_values($reserved), array_keys($reserved), $url);
return $url;
} 



?>
