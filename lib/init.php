<?php 
/****设置页****/

define('ROOT' , dirname(__DIR__));

/*echo __FILE__ , '<br>';
echo ROOT;
//echo __LINE__;*/
require(ROOT . '/lib/mysql.php');
require(ROOT . '/lib/func.php');

if(!get_magic_quotes_gpc()) {
	$_GET = _addslashes($_GET);//防范足够，可以不用了
	//$_POST = _addslashes($_POST);
	$_COOKIE = _addslashes($_COOKIE);
}