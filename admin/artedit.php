<?php 
/****文章编辑****/
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
$sql = "select art_id from art where art_id=$art_id";
if(!pQuery($pdo,$sql,1)) {
	error('文章不存在','javascript:history.go(-1)');
}

$sql="select tag from art right join tag on art.art_id=tag.art_id where art.art_id=$art_id";
if($tags=pQuery($pdo,$sql))
{
    foreach($tags as $t) {
        $tag.=$t['tag'].',';
    }
    $tag = rtrim($tag , ",");
}
$sql = "select title,content,cat_id,abstract from art where art_id={$art_id}";
$art = pQuery($pdo,$sql,1);

$art['title'] = str_replace("\"","&quot;",$art['title']);//转义标题的双引号，不然input value=""不能正常输出
if(empty($_POST)) {
//查询出所有的栏目
    $cattree = gettree();
	include(ROOT . '/view/admin/artedit.html');
}
else {
	//验证标题
	if(($str=trim($_POST['title'])) != $art['title']) {
		$str=strip_tags($str);
		if($str==''){
			error('标题不能为空','javascript:history.go(-1)');
		}
		$newart['title'] = $str;
	}

	//检测栏目是否合法
	$newart['cat_id'] = intval($_POST['cat_id']);

	//检测内容是否为空
	$newart['content'] = trim($_POST['content']);
	if($newart['content'] == '') {
		error('内容不能为空','javascript:history.go(-1)');
	}
	//文章中摘要的引号和<>化为字符（因为修改或发布时用if第一流程的$newart['content']入库，content因为ueditor编辑器会把引号<>化为实体）
    $art['abstract'] = htmlspecialchars_decode($art['abstract'],ENT_QUOTES);

    //处理摘要  !=、== > && > ||
	if(empty($_POST['abstract']) || $_POST['content']!=$art['content'] && $_POST['abstract'] == $art['abstract']) {
		$newart['abstract']=mb_substr(trim(strip_tags($newart['content'])),0,200);
	}
	else if($_POST['abstract']!=$art['abstract'] && !empty($_POST['abstract'])) {
            $newart['abstract'] = mb_substr(trim(strip_tags($_POST['abstract'])), 0, 200);
        }

    //收集tag
    $nowtag = trim($_POST['tag']);
	if($tag != $nowtag) {
		if($nowtag != '' && !preg_match("/^([\x{4e00}-\x{9fa5}A-Za-z0-9]{1,10},){0,4}[\x{4e00}-\x{9fa5}A-Za-z0-9]{1,10}$/u",$nowtag))
			error('标签输入错误','javascript:history.go(-1)');
	}

	if(!pStmtExec($pdo,'art' , $newart ,'update' , "art_id={$art_id}")) {
		error('文章修改失败','javascript:history.go(-1)');
	}
	else{
		//删除tag表的所有tag 再insert插入新的tag,
		//如果tag没改动，不用动          
		if($tag != $nowtag) {
			if($tag != '') {
				$sql="delete from tag where art_id = {$art_id}";
				if(!pExec($pdo,$sql))
					error('文章修改失败:删除标签失败','javascript:history.go(-1)');
			}
			//插入tag 到tag表
			if($nowtag != ''){
				$tags = explode(',', $nowtag);
				foreach($tags as $v) {
					$sql = "insert into tag (art_id,tag) values ({$art_id}, ? )";
					if(!pStmtIdu($pdo,$sql,$v))
						error('文章修改失败:修改文章时插入标签失败','javascript:history.go(-1)');
				}
			}
		}
		succ('文章修改成功');
	}
}
