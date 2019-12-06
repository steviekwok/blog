<?php
/* 前台留言ajax处理 */
require('./lib/init.php');

if($_POST['act'] == 'comm') {
    if (!is_numeric($_POST['art'])) {
        die('hack');
    }
    $art_id = $_POST['art'];
    $comm['nick'] = htmlspecialchars(trim($_POST['name']), ENT_QUOTES);
    $comm['email'] = $_POST['email'];
    if (!preg_match("/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/", $comm['email'])) {
        die('hack');
    }
    $comm['content'] = htmlspecialchars(trim($_POST['content']), ENT_QUOTES);
    $comm['pubtime'] = time();
    $comm['art_id'] = $art_id;
    $comm['ip'] = sprintf('%u', ip2long(getRealIp()));

    $pdo = pConn();
    $rs = pStmtExec($pdo, 'comment', $comm);
    if ($rs) {
        //评论发布成功 将art表的comm+1
        $sql = "update art set comm=comm+1 where art_id='{$art_id}'";
        if (pExec($pdo, $sql)) {
            die('done');
        } else {
            die('error:pExec');
        }
    } else {
        die('error:pStmtExec');
    }
}else{
    $log = "----------------------------------------- ".date('Y/m/d H:i:s').", hack att"  . "-----------------------------------------";
    error_log($log);
    http_response_code(404);
}
