<?php
/****后台主控制页 ****/
require('../lib/init.php');

if(!acc()) {
    $log = "----------------------------------------- ".date('Y/m/d H:i:s').", hack att"  . "-----------------------------------------";
    error_log($log);
    http_response_code(404);
}

$sql = "SELECT count(*) FROM art";//获取总的文章数
if($_GET['cat_id']){
    $cat_id=$_GET['cat_id'];
	$sql.=" WHERE art.cat_id = ? ";
}
$pdo=pConn();
//总的文章数
if($cat_id)
    $num=pStmtS($pdo,$sql,$cat_id,true)[0];
else
    $num=pQuery($pdo,$sql,true)[0];


$cnt = 10;//每页多少条记录
$curr = isset($_GET['page']) && intval($_GET['page']) > 0 ? intval($_GET['page'])  : 1;//当前页
$pages=getpage($num,$curr,$cnt);
$startpos=($curr-1)*$cnt;
$sql="SELECT art.art_id,title,pubtime,comm,catname FROM art LEFT JOIN cat ON art.cat_id = cat.cat_id";
if($cat_id)
    $sql.=" WHERE art.cat_id = ? ";
$sql.=" ORDER BY art_id DESC LIMIT $startpos,$cnt";

if($cat_id)
    $arts=pStmtS($pdo,$sql,$cat_id);
else
    $arts=pQuery($pdo,$sql);

include(ROOT . '/view/admin/artlist.html');
