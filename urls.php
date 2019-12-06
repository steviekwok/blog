<?php
$urls = array(
    'https://www.steviekwok.top',
    'https://www.steviekwok.top/about/',
    'https://www.steviekwok.top/art/13/',
    'https://www.steviekwok.top/art/16/'
);
$api = 'http://data.zz.baidu.com/urls?site=https://www.steviekwok.top&token=vNWOK3KGKrR00VG0';
$ch = curl_init();
$options =  array(
    CURLOPT_URL => $api,
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => implode("\n", $urls),
    CURLOPT_HTTPHEADER => array("Content-Type: text/plain"),
);
curl_setopt_array($ch, $options);
$result = curl_exec($ch);
echo $result;
