<?php
/****BLOG前台主页****/

require('./lib/init.php');

//列出文章
$sql = "select count(*) from art";//获取总的文章数

if(isset($_GET['cat'])) {
    $cat = $_GET['cat'];
    $sql = "select count(art.cat_id) from cat join art on cat.cat_id=art.cat_id where catname= ?";
	$str = $cat;
}
else if(isset($_GET['tag'])) {
    $tag = $_GET['tag'];
    $sql = "select count(tag) from tag where tag= ?";
	$str = $tag;
}
$pdo = pConn();
if(isset($str))
    $num=pStmtS($pdo,$sql,$str,1)[0];//$num总的文章数
else
    $num=pQuery($pdo,$sql,1)[0];

if($num){
	$cnt = 10;//每页多少条记录
    $curr = isset($_GET['page']) && intval($_GET['page']) > 0 ? intval($_GET['page'])  : 1;//当前页
	$pages = getpage($num,$curr,$cnt);
	//echo $pages;
	$startpos=($curr-1)*$cnt;
	$sql="select art.art_id,title,pubtime,rtime,abstract from art";
	/*if($skey)
		$sql.=" where title like ?";
	else */
	if($cat)
        $sql.=" join cat on art.cat_id=cat.cat_id where catname= ?";
	else if($tag)
        $sql.=" join tag on art.art_id=tag.art_id where tag.tag= ?";

	$sql.=" order by art_id desc limit $startpos,$cnt";

    if(isset($str)) {
        $arts = pStmtS($pdo, $sql, $str);
    } else {
        $arts = pQuery($pdo, $sql);
    }

	if($arts){
	    for($i = 0; $i < count($arts); $i++){
	        $tag = pQuery($pdo, "select tag from tag where art_id = ".$arts[$i]['art_id']);
            if($tag){
               foreach($tag as $t) {
                   $arts[$i]['tag'][] = $t['tag'];
               }
            }
        }
    }
}
elseif($num === '0'){
	$res="<p style='font-size:20px;color:red'>抱歉，暂时没有文章_(:з」∠)_</p><p style='font-size:20px;color:red;'>返回<a href='/'>主页</a></p>";
}else{
    $log = "----------------------------------------- ".date('Y/m/d H:i:s').", hack att"  . "-----------------------------------------";
    error_log($log);
    http_response_code(404);
}

//列出分类
$sql="select cat.cat_id,catname,parent_id,count(art.cat_id) as num from cat left join art on cat.cat_id=art.cat_id group by cat.cat_id order by num desc";
$cats = gettreeIndex(pQuery($pdo,$sql));

	
$sql="select tag,count(tag) as num from tag group by tag order by num desc";
$tags= pQuery($pdo,$sql);

require(ROOT . '/view/front/index.html');
