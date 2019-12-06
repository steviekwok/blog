<?php
/****前台单个文章显示****/
require('./lib/init.php');

//判断地址栏传来的art_id 是否合法
if (!$art_id = intval($_GET['art'])) {
    header('Location: /');
}
//如果没有这篇文章 跳转到首页去
$sql = "select art_id from art where art_id='{$art_id}'";
$pdo = pConn();
if (!pQuery($pdo, $sql, 1)) {
    header('Location: /');
}
//阅读次数+1 一个cookie一天只加1
if (!isset($_COOKIE["ready_" . $art_id])) {
    $sql = "update art set rtime=rtime+1 where art_id='{$art_id}'";
    pExec($pdo, $sql);
}
setcookie('ready_' . $art_id, '1', time() + 86400);//设置一天访问量+1

//查询文章
$sql = "select title,content,pubtime,catname,comm,rtime from art left join cat on art.cat_id=cat.cat_id where art_id='{$art_id}'";
$art = pQuery($pdo, $sql, 1);
//查询此文章的标签
if($art){
    $tag = pQuery($pdo, "select tag from tag where art_id = ".$art_id);
    if($tag) {
        foreach ($tag as $t) {
            $art['tag'][] = $t['tag'];
        }
    }
}
//var_dump($art);
//查前一篇和后一篇文章
$sql = "select art_id, title from art where art_id > '{$art_id}' limit 1";
$art_next = pQuery($pdo, $sql,true);
$sql = "select art_id, title from art where art_id < '{$art_id}' order by art_id desc limit 1";
$art_prev = pQuery($pdo, $sql,true);
//列出分类
$sql = "select cat.cat_id,catname,parent_id,count(art.cat_id) as num from cat left join art on cat.cat_id=art.cat_id group by cat.cat_id order by num desc";
$cats = gettreeIndex(pQuery($pdo, $sql));

$sql = "select tag,count(tag) as num from tag group by tag order by num desc";
$tags = pQuery($pdo, $sql);

//查询所有的留言
$sql = "select * from comment where art_id='{$art_id}' and parent_id is null order by comment_id";
$comms = pQuery($pdo, $sql);
if($comms){
    for($i = 0; $i < count($comms); $i++){
        $adm_comm = pQuery($pdo, "select content from comment where art_id = '{$art_id}' and parent_id = '{$comms[$i]['comment_id']}'",true);
        if($adm_comm){
            $comms[$i]['adm_comm'] = $adm_comm[0];
        }
    }
}
require(ROOT . '/view/front/art.html');
