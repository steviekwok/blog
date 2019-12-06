<?php
/****分类列表****/

//连接数据库
require('../lib/init.php');

if(!acc()) {
    $log = "----------------------------------------- ".date('Y/m/d H:i:s').", hack att"  . "-----------------------------------------";
    error_log($log);
    http_response_code(404);
}
$sql="select count(*) from cat";
$pdo = pConn();
if($num = pQuery($pdo,$sql,1)[0])//某文章下总评论数
{
    $cnt = 10;//每页多少条记录
    $curr = isset($_GET['page']) && intval($_GET['page']) > 0 ? intval($_GET['page']) : 1;//当前页
    $pages = getpage($num, $curr, $cnt);
//echo $pages;
    $startpos = ($curr - 1) * $cnt;
    $sql = "select cat.cat_id,catname,parent_id,count(art.cat_id) as num from cat left join art on cat.cat_id=art.cat_id group by cat.cat_id order by num desc,cat.cat_id limit $startpos,$cnt";

    $cats = pQuery($pdo, $sql);
}
//var_dump($cattree);
require(ROOT.'/view/admin/catlist.html');
