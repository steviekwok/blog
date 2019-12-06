<?php
/****分类添加修改处理****/

//连接数据库
require('../lib/init.php');

if(!acc()) {
    $log = "----------------------------------------- ".date('Y/m/d H:i:s').", hack att"  . "-----------------------------------------";
    error_log($log);
    http_response_code(404);
}
if($_POST['act'] == 'add'){
    $cattree = gettree();
    require(ROOT. '/view/admin/catDialogA.html');
}elseif($_POST['act'] == 'mod') {
    if(!($cid = intval($_POST['cid']))){
        die('error');
    }
    if(!(($cname = $_POST['cname']) && is_numeric($_POST['pid']))){
        die('error');
    }
    $pid = intval($_POST['pid']);
    $pdo = pConn();
    $cattree = gettreeArr(catList($cid));
    //var_dump($cattree);
    require(ROOT. '/view/admin/catDialogE.html');
}elseif(($catname = $_POST['catname'])  && is_numeric($_POST['parent_id'])){
    $pid = intval($_POST['parent_id']);
    if(isset($_POST['edit'])){
        if(is_numeric($_POST['cat_id'])) {
            $cid = intval($_POST['cat_id']);
            $sql = " update cat set catname = ?, parent_id = ? where cat_id = ?";
            $pdo = pConn();
            pStmtIdu($pdo, $sql, [$catname, $pid,$cid]);
        }
    }else {
        $sql = " insert into cat (catname, parent_id) values (?, ?)";
        $pdo = pConn();
        pStmtIdu($pdo, $sql, [$catname, $pid]);
    }
    header('Location: /admin/catlist/');
}else{
    die('error');
}
