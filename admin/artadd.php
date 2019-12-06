<?php 
/****后台文章添加****/

require('../lib/init.php');

if(!acc()) {
    $log = "----------------------------------------- ".date('Y/m/d H:i:s').", hack att"  . "-----------------------------------------";
    error_log($log);
    http_response_code(404);
}

if(empty($_POST)) {
    $cattree = gettree();
	include(ROOT . '/view/admin/artadd.html');
}else {
	//检测标题是否为空
	$art['title'] = strip_tags(trim($_POST['title']));
	if($art['title'] == '') {
		error('标题不能为空','javascript:history.go(-1)');
	}

	//检测栏目是否合法
	$art['cat_id'] = intval($_POST['cat_id']);

	//检测内容是否为空
	$art['content'] = trim($_POST['content']);
	if($art['content'] == '') {
		error('内容不能为空','javascript:history.go(-1)');
	}

	//处理摘要
    if($str=trim($_POST['abstract']))
		$art['abstract']=mb_substr(trim(strip_tags($str)),0,200);
	else	
		$art['abstract']=mb_substr(trim(strip_tags($art['content'])),0,200);

	//插入发布时间
	$art['pubtime'] = $art['lastup'] = date("Y-m-d H:i:s");
	//收集tag 
	$tag = trim($_POST['tag']);
    if($tag != '' && !preg_match("/^([\x{4e00}-\x{9fa5}A-Za-z0-9]{1,10},){0,4}[\x{4e00}-\x{9fa5}A-Za-z0-9]{1,10}$/u",$tag))
		error('标签输入错误','javascript:history.go(-1)');
    //var_dump($art);
	//插入内容到art表
    $pdo=pConn();
	if(!pStmtExec($pdo,'art' , $art)) {
		error('文章发布失败','javascript:history.go(-1)');
	} else {
		//判断是否有tag
		if($tag != '') {
			//获取上次 insert 操作产生的主键id
			$art_id = getLastId($pdo);
			//插入tag 到tag表
			$tags = explode(',', $tag);//索引数组	
			foreach($tags as $v) {
				$sql = "insert into tag (art_id,tag) values ({$art_id}, ? )";
				if(!pStmtIdu($pdo,$sql,$v)) {
					$sql = "delete from art where art_id=$art_id";
					if(pExec($pdo,$sql))
						error('文章添加失败','javascript:history.go(-1)');
					else
						error('sql错误','javascript:history.go(-1)');
				}
			}
		}
		succ('文章添加成功');
	}
}
