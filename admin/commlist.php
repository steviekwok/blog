<?php
/****评论管理****/

require('../lib/init.php');
if(!acc()) {
    $log = "----------------------------------------- ".date('Y/m/d H:i:s').", hack att"  . "-----------------------------------------";
    error_log($log);
    http_response_code(404);
}
if(empty($_POST)) {
    //全文章评论－检查有没有文章，某文章评论－检查有没有此往篇文章
    $sql = "select count(*) from art";
    if($_GET['art_id']){
        if (!$art_id = intval($_GET['art_id'])) {
            header('Location: /admin/artlist/');
        }
        $str = " where art_id='{$art_id}'";
        $sql .= $str;
    }
    $pdo = pConn();
    if (pQuery($pdo, $sql, 1)[0]) {
        $sql = "select count(*) from comment";
        if($str){
            $sql .= $str;
        }
        if ($num = pQuery($pdo, $sql, 1)[0])//某文章或全文章下总评论数
        {
            $cnt = 10;//每页多少条记录
            $curr = isset($_GET['page']) && intval($_GET['page']) > 0 ? intval($_GET['page']) : 1;//当前页
            $pages = getpage($num, $curr, $cnt);
            $startpos = ($curr - 1) * $cnt;

            if($str){
                $sql = "select * from comment where art_id='{$art_id}' order by comment_id desc limit $startpos,$cnt";
            }else{
                $sql = "select * from comment order by comment_id desc limit $startpos,$cnt";
            }
            $comms = pQuery($pdo, $sql);
        }
    }
    require(ROOT . '/view/admin/commlist.html');
}else {
    if (!(is_numeric($_POST['parent_id']) && is_numeric($_POST['art_id']))) {
        error('hack?','javascript:history.go(-1)');
    }
    $comm['content'] = htmlspecialchars(trim($_POST['content']), ENT_QUOTES);
    $comm['parent_id'] = intval($_POST['parent_id']);
    $comm['art_id'] = intval($_POST['art_id']);
    $comm['nick'] = 'admin';
    $comm['pubtime'] = time();
    $comm['ip'] = sprintf('%u', ip2long(getRealIp()));

    $pdo = pConn();
    if (pStmtExec($pdo, 'comment', $comm)) {
        $url = substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],'.')).'/';
        if($_SERVER['QUERY_STRING']){
            preg_match('/\b(?!\bpage\b)[a-z_]+=[a-zA-Z\d]+/i',$_SERVER['QUERY_STRING'],$m);//例cat_id=$1&page=$2，只匹配cat_id=1
            if(count($m)) {
                $url .= strtr($m[0], '=', '/'). '/';
            }
        }
        succ('回复成功', $url);
    } else {
        error('回复失败','javascript:history.go(-1)');
    }
}
