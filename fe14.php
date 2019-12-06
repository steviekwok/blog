<?php 
/****后台登陆****/
require('./lib/init.php');

if(empty($_POST)) {
	require(ROOT . '/view/front/login.html');
} else {
	$user['name'] = trim($_POST['name']);
    $user['password'] = trim($_POST['password']);
	if(empty($user['name'])) {
		error('用户名不能为空');
	}
    if(empty($user['password'])) {
        error('密码不能为空');
    }
	if (!preg_match("/^[a-zA-Z]{6}$/",$user['name']) || !preg_match("/^[a-zA-Z0-9!@$%^&*,.]{6,16}$/",$user['password'])) {
		error('用户名或密码错误');
	}
	//$sql = "select * from user where name='$user[name]' and password='$user[password]'";
	$sql = "SELECT * FROM `user` WHERE name = ? ";
	$pdo=pConn();
	$row = pStmtS($pdo,$sql,$user['name'],true);
	//print_r($row);exit();
	if(!$row) {
		error('用户名或密码错误');
	} else {
		if(md5($user['password'].$row['salt']) === $row['password']){
			setcookie('name' , substr_replace($user['name'],'',-1),time()+864000,'/admin/','',false,true);
			setcookie('ccode' , cCode($user['name']),time()+86400,'/admin/','',false,true);//cookie过期时间一天
			header('Location: /admin/artlist/');
		} else {
			error('用户名或密码错误');
		}
	}
}
