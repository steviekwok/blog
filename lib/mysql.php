<?php
/**
* mysql.php mysql系列操作函数
* @author nianbaibai
*/

/**
* 连接数据库
*
* @return pdo 连接成功,返回pdo对象
*/


function pConn() {
	$params = array (
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		//,PDO::ATTR_PERSISTENT => true
        );
	$cfg = require(ROOT . '/lib/config.php');
	try {
	    $pdo = new PDO("mysql:host=".$cfg['host'].";dbname=".$cfg['db'].";charset=".$cfg['charset'], $cfg['user'], $cfg['pwd'],$params);
	}	
	catch (PDOException $e) {
        die("Error!: " . $e->getMessage() . "<br/>");
	}
	return $pdo;
}

/**
* pdo预处理查询的函数
* @param str $sql 预处理sql语句字符串
* @param str/int $str 绑定的参数
* @param bool $one 是否只select一行
* @return 成功返回一维或二维数组$data，失败返回false
*/

function pStmtS($pdo,$sql,$str,$one=false) {
	try {
		$stmt=$pdo->prepare($sql);
		//$stmt->setFetchMode(PDO::FETCH_ASSOC);
		if(is_string($str) || is_numeric($str)){
			$stmt->bindValue(1,$str);
	    }
		else if(is_array($str)){
			foreach($str as $k=>$v)
				$stmt->bindValue($k+1,$v);
	    }
	    else
		    return false;

        if($stmt->execute());
		{
			if(!$one)
				$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
			else
				$data=$stmt->fetch();
			return $data;
		}			
	}
	catch(PDOException $e) {
		mLog($e->getMessage());	
	}
	return false;
}

/**
* 查询的函数
* @param str $str 要查询的sql语句字符串
* @param bool $one 是否只select一行
* @return 成功返回一维或二维数组$data，失败返回false
*/

function pQuery($pdo,$sql,$one=false) {
	try {	
	    if($query=$pdo->query($sql)){
		    if(!$one)
				$data=$query->fetchAll(PDO::FETCH_ASSOC);
			else
				$data=$query->fetch();
			//var_dump($data);
			return $data;
		}
		else
            return false;
	}
	catch(PDOException $e) {
		mLog($e->getMessage());
	}
}

/**
* log日志记录功能
* @param str $str 待记录的字符串
*/
function mLog($str) {
	$filename = ROOT . '/log/' . date('Ymd') . '.txt';
	$log = "----------------------------------------- \n".date('Y/m/d H:i:s') . "\n" . $str . "\n" . "----------------------------------------- \n\n";
	return file_put_contents($filename, $log , FILE_APPEND);
}

/**
*  增加删除修改函数
*  @param str $str sql语句字符串
*  @return int $affected受此语句影响的行数/bool=false
*/

function pExec($pdo,$sql) {
	try {
		$affected=$pdo->exec($sql);
	    if($affected===false)
            return false;
		else
			return $affected;
	}
	catch(PDOException $e) {
		mLog($e->getMessage());	
	}
}

/**
* pdo预处理查询的函数
* @param str $sql 预处理sql语句字符串
* @param str/int $str 绑定的参数
* @return bool 
*/

function pStmtIdu($pdo,$sql,$str) {
	//echo $sql;
	try {
		//echo "str:".$str."<br>";
		$stmt=$pdo->prepare($sql);
		if(is_string($str) || is_numeric($str)){
			$stmt->bindValue(1,$str);
	    }
		else if(is_array($str)){
			foreach($str as $k=>$v)
				$stmt->bindValue($k+1,$v);
	    }
	    else
		    return false;
	    
        if($stmt->execute());
		    return true;
	}
	catch(PDOException $e) {
		mLog($e->getMessage());	
	}
	return false;
}
/**
* 增加删除修改预处理函数
* @param str $table 要sql的表名
* @param mixed $data 绑定的参数
* @param str $act 动作：增删减之一
* @return bool 成功返回true 失败false
*/
function pStmtExec($pdo, $table, $data, $act='insert', $where=0 ) {
	if($act == 'insert') {
		$sql = "insert into $table (";
		$keys = array_keys($data);
		$sql .= implode(',' , $keys) . ") values (";
		$sql .= substr(str_repeat(' ? ,',count($keys)),0,-1).')';
		//echo $sql;
	} 
	else if ($act == 'update') {
        $sql = "update $table set ";
		foreach($data as $k=>$v) {
			$sql .= $k . "= ? ,";
		}
		$sql = rtrim($sql , ',') . " where ".$where;
	}
	try {
		$stmt=$pdo->prepare($sql);
		//$stmt->setFetchMode(PDO::FETCH_ASSOC);
		if(is_string($data) || is_numeric($data)){
			$stmt->bindValue(1,$data);
	    }
		else if(is_array($data)){
			$i=1;
			foreach(array_values($data) as $v){
				$stmt->bindValue($i++,$v);
			}
		}
	    else
		    return false;
	    
        if($stmt->execute())
			return true;			
	}
	catch(PDOException $e) {
		mLog($e->getMessage());	
	}
	return false;
}

/**
* 取得上一步insert 操作产生的主键id
*/
function getLastId($pdo) {
	$s=$pdo->lastInsertId();
	if($s)
		return $s;
}


/**
* 使用反斜线 转义字符串
* @param arr 待转义的数组
* @return arr 被转义后的数组
*/

function _addslashes($arr) {
	//echo $_SERVER["REQUEST_METHOD"].'{';
	foreach($arr as $k=>$v) {
		if(is_string($v)) {
			//echo $arr[$k].'  ';
			$arr[$k] = addslashes($v);
			//echo '[]:'.$k.' v:'.$arr[$k].'}<br>';
		}else if(is_array($v)) {
			$arr[$k] = _addslashes($v);
		}
	}

	return $arr;
}