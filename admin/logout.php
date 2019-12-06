<?php
/***退出登陆***/
require('../lib/init.php');

if(!acc()) {
    $log = "----------------------------------------- ".date('Y/m/d H:i:s').", hack att"  . "-----------------------------------------";
    error_log($log);
    http_response_code(404);
}
setcookie('name',null,0,'/admin');
setcookie('ccode',null,0,'/admin');
header('Location: /fe14/');
