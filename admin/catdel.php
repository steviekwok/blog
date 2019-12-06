<?php 
/****删除分类****/
require('../lib/init.php');

if(!acc()) {
    $log = "----------------------------------------- ".date('Y/m/d H:i:s').", hack att"  . "-----------------------------------------";
    error_log($log);
    http_response_code(404);
}

//验证分类id
if(!$cat_id = intval($_GET['cat_id'])){
	error('分类id不合法','javascript:history.go(-1)');
}

//检测 栏目是否存在
$sql = "select cat_id from cat where cat_id={$cat_id}";
$pdo=pConn();
if(!pQuery($pdo,$sql,1)) {
	error('分类不存在','javascript:history.go(-1)');
}

//检测完毕,删除栏目
$sql = "delete from cat where cat_id={$cat_id}";
if(!pExec($pdo,$sql)) {
	error('类别删除失败','javascript:history.go(-1)');
} else {
	//如有文章属于删除的类别，把类别修改成空0
	$sql = "select count(art.cat_id) from cat left join art on cat.cat_id=art.cat_id where cat.cat_id = {$cat_id} group by cat.cat_id";
	$rs=pQuery($pdo,$sql,1);
	if($rs[0]>0) {
		$sql="update art set cat_id = 0 where cat_id = {$cat_id}";
		pExec($pdo,$sql) or die("改类别失败");
	}
    header('Location: /admin/catlist/');
}
