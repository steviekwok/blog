<?php 
/****修改密码****/
require('../lib/init.php');

if(!acc()) {
    $log = "----------------------------------------- ".date('Y/m/d H:i:s').", hack att"  . "-----------------------------------------";
    error_log($log);
    http_response_code(404);
}

if(!empty($_POST)) {
    $user['name'] = trim($_POST['name']);
    $user['password'] = trim($_POST['password']);
    $user['newpassword1'] = trim($_POST['newpassword1']);
    $user['newpassword2'] = trim($_POST['newpassword2']);
    if (empty($user['name'])) {
        $err = '用户名不能为空';
    } else if (empty($user['password'])) {
        $err = '原密码不能为空';
    } else if (empty($user['newpassword1']) || empty($user['newpassword2'])) {
        $err = '新密码不能为空';
    } else if ($user['newpassword1'] != $user['newpassword2']) {
        $err = '新密码两次输入不一致';
    } else if (!preg_match("/^[a-zA-Z]{6}$/", $user['name'])){
        $err = '用户名或密码错误';
    } else if (!preg_match("/^[a-zA-Z0-9!@#$%^&*,.]{6,16}$/",$user['password']) || !preg_match("/^[a-zA-Z0-9!@#$%^&*,.]{6,16}$/",$user['newpassword1'])){
        $err='用户名或密码错误';
	}else {
        $sql = "SELECT * FROM `user` WHERE name = ? ";
        $pdo = pConn();
        $row = pStmtS($pdo, $sql, $user['name'], true);
        if (!$row)
            $err = '用户名或密码错误';
        else {
            if (md5($user['password'] . $row['salt']) === $row['password']) {
                $str = 'UasdfnpO*ISDMVMVREOuUWER,C(#$%@^)';
                $salt = substr(str_shuffle($str), 0, 8);
                $sql = "UPDATE `user` SET password = ?,salt = ? WHERE name = '{$row['name']}'";
                if (pStmtIdu($pdo, $sql, array(md5($user['newpassword1'] . $salt), $salt))) {
                    $suc = '修改成功!请3秒后重新登陆';
                    echo "<meta http-equiv='refresh' content='3;url=/admin/logout/'>";
                } else
                    $err = '修改失败！sql错误';
            } else
                $err = '用户名或密码错误';
        }
    }
}
require(ROOT . '/view/admin/cp.html');
