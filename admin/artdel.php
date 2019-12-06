<?php 
/****删除文章****/

require('../lib/init.php');

if(!acc()) {
    $log = "----------------------------------------- ".date('Y/m/d H:i:s').", hack att"  . "-----------------------------------------";
    error_log($log);
    http_response_code(404);
}

//判断地址栏传来的art_id是否合法
if(!$art_id = intval($_GET['art_id'])){
	error('文章id不合法','javascript:history.go(-1)');
}
$pdo=pConn();
//是否有这篇文章
$sql = "select art_id from art where art_id={$art_id}";
if(!pQuery($pdo,$sql,1)) {
	error('文章不存在','javascript:history.go(-1)');
}

//删除文章
$sql = "delete from art where art_id={$art_id}";
if(!pExec($pdo,$sql)) {
	error('文章删除失败','javascript:history.go(-1)');
} else {
	//succ('文章删除成功');
	header('Location: /admin/artlist/');
}
