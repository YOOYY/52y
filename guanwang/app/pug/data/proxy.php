<?php
$payUrl = 'http://www.52y.com:3000/pay';
$res = posturl($payUrl,$_POST);
echo $res;

function posturl($url,$data){
    $data  = json_encode($data);    
    $headerArray =array("Content-type:application/json;charset=utf-8","Accept:application/json");
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl,CURLOPT_HTTPHEADER,$headerArray);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    // return json_decode($output，true);
    return $output;
}
?>
