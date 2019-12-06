<?php
/****BLOG前台主页****/

require('./lib/init.php');

$sql = "select count(*) from art";//获取总的文章数
$pdo = pConn();
$num = pQuery($pdo,$sql,1)[0];
if(is_numeric($num))//总的文章数
{
    if($num !== '0'){
	$sql = "select art_id,title,pubtime from art order by pubtime desc";
        $arts = pQuery($pdo,$sql);
    }
    $sql = "select cat.cat_id,catname,parent_id,count(art.cat_id) as num from cat left join art on cat.cat_id=art.cat_id group by cat.cat_id order by num desc";    
    $cats = gettreeIndex(pQuery($pdo,$sql));
    
    $sql="select tag,count(tag) as num from tag group by tag order by num desc";
    $tags= pQuery($pdo,$sql);
    require(ROOT . '/view/front/archives.html');
}else{
    $log = "----------------------------------------- ".date('Y/m/d H:i:s').", hack att "  . "-----------------------------------------";
    error_log($log);
    http_response_code(404);
}
