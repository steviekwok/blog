<?php
require('../lib/init.php');
if(!acc() || !$id = intval($_POST['id'])) {
    $log = "----------------------------------------- ".date('Y/m/d H:i:s').", hack att"  . "-----------------------------------------";
    error_log($log);
    make_json_response('',1,'getout'); 
}
$pdo=pConn();
$sql = "select art_id,nick from comment where comment_id='{$id}'";

$str = pQuery($pdo,$sql,true);
$nick = $str['nick'];
$art_id = $str['art_id'];

//如果删除的是我回复
if($nick == 'admin') {
    $sql="delete from comment where comment_id='{$id}'";
    if(pExec($pdo,$sql)){
        make_json_response('done');
    }else {
        make_json_response('',1,'adm');
    }
}elseif(!empty($nick)) {
    $sql="delete from comment where comment_id='{$id}'";
    if(!pExec($pdo,$sql)){
        make_json_response('',1,'nodel');
    }else {
        $sql = "update art set comm=comm-1 where art_id='{$art_id}'";
        if (!pExec($pdo, $sql)) {
            make_json_response('',1,'comm-1');
        }
        //如果删除的回复有博主回复，一并删除
        $sql = "select count(*) from comment where parent_id='{$id}'";
        $str = pQuery($pdo,$sql,true)[0];
        if(is_numeric($str)) {
            if($str) {
                $sql = "delete from comment where parent_id='{$id}'";
                if (pExec($pdo, $sql)) {
                    make_json_response($id);
                } else {
                    make_json_response('',1,'treeadm');
                }
            }else {
                make_json_response('done');
            }
        }else {
            make_json_response('',1,'sqlstreeadm');
        }
    }
}else{
    make_json_response('',1,'sqlnick');
}
