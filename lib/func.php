<?php 
/****函数****/

/**
* 成功的提示信息
*/

function succ($res, $url='/admin/artlist/') {
	$result = 'succ';
	require(ROOT . '/view/admin/info.html');
	exit();
}

/**
* 失败返回的报错信息
*/

function error($res, $url='/fe14/') {
	$result = 'fail';
	require(ROOT . '/view/admin/info.html');
	exit();
}

/**
* 获取来访者的真实IP
*
*/

function getRealIp() {
	static $realip = null;
	if($realip !== null) {
		return $realip;
	}

	if(getenv('REMOTE_ADDR')) {
		$realip = getenv('REMOTE_ADDR');
	} else if(getenv('HTTP_CLIENT_IP')) {
		$realip = getenv('HTTP_CLIENT_IP');
	} else if (getenv('HTTP_X_FROWARD_FOR')) {
		$realip = getenv('HTTP_X_FROWARD_FOR');
	}

	return $realip;	
}

/**
* 生成分页代码
* @param int $num 文章总数
* @param int $curr 当前显示的页码数      $curr-2 $curr-1 $curr $curr+1 $curr+2
* @param int $cnt 每页显示的条数
* @return str $page_str html格式的分页，直接在html使用就行
*/

function getPage($num,$curr,$cnt) {
    //取当前url不要后缀、参数
   // echo $_SERVER['QUERY_STRING'];
    //echo $_SERVER['PHP_SELF'];
    $url = substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],'.'));
    //如果是主页index.php，把/index也删除，因为有伪静态
    $pos = strpos($url,'index');
    if($pos !== false){
        $url = substr_replace($url, '', $pos-1, 6);//$pos-1因为要删前面的/
    }

    if($_SERVER['QUERY_STRING']){
        preg_match('/\b(?!\bpage\b)[a-z_]+=[a-zA-Z\d]+/i',$_SERVER['QUERY_STRING'],$m);//例cat_id=$1&page=$2，只匹配cat_id=1
        if(count($m)) {
            $url .= '/' . strtr($m[0], '=', '/');
        }
    }
	//最大的页码数
	$max = ceil($num/$cnt);//ceil进一法取整
	//最左侧页码
	$left = max(1 , $curr-2);

	//最右侧页码
	$right = min($left+4 , $max);

	$left = max(1 , $right-4);

/*	(1 [2] 3 4 5) 6 7 8 9
	1 2 (3 4 [5] 6 7) 8 9
	1 2 3 4 (5 6 7 [8] 9)*/
    $page_str = '';
	$page = array();

    if($curr != 1){
        if($curr == 2) {
            $page_str = "<a class='newer-posts' href=$url/>&larr; Newer Posts</a>";
        }else{
            $p = $curr - 1;
            $page_str = "<a class='newer-posts' href=$url/p/$p/>&larr; Newer Posts</a>";
        }
    }

    if($left == 2){
        $page_str .= "<a class='num' href=$url/>first</a>";
    }elseif($left > 2) {
        $page_str .= "<a class='num' href=$url/>first</a><span class='space'>...</span>";
    }
	for($i = $left; $i <= $right; $i++) {
		//$_GET['page'] = $i;
 		//$page[$i] = http_build_query($_GET);
 		if($curr != $i) {
 		    if($i == 1){
                $page_str .= "<a class='num' href=$url/>$i</a>";
                continue;
            }
            $page_str .= "<a class='num' href=$url/p/$i/>$i</a>";
        }else{
            $page_str .= "<a class='curr'>$i</a>";
        }
	}
    if($right == $max -1 ){
        $page_str .= "<a class='num' href=$url/p/$max/>End</a>";
    }elseif($right < $max -1) {
        $page_str .= "<span class='space'>...</span><a class='num' href=$url/p/$max/>End</a>";
    }
    if($curr != $max){
        $p = $curr + 1;
        $page_str .= "<a class='older-posts' href=$url/p/$p/>Older Posts &rarr;</a>";
    }
    $page_str .= "<span>Page $curr of $max</span>";
	return $page_str;
}
/*<a class="newer-posts" href="/">&larr; Newer Posts</a>
            <span class="page-number">Page 1 of 6</span>
            <a class="older-posts" href="/page/2/">Older Posts &rarr;</a>*/
//print_r(getPage(100,5,10));

/**
* 生成随机字符串
* @param int $num 生成的随机字符串的个数
* @return str 生成的随机字符串
*/
function randStr($num=6) {
	$str = str_shuffle('abcedfghjkmnpqrstuvwxyzABCEDFGHJKMNPQRSTUVWXYZ123456789');
	return substr($str, 0 , $num);
}

/**
* 检测用户是否登录
*/

function acc() {
	if(!isset($_COOKIE['name']) || !isset($_COOKIE['ccode'])){
		return false;
	}
	return $_COOKIE['ccode'] === cCode($_COOKIE['name'].'Q');
}



/**
* 加密用户名
* @param str $name 用户登陆时输入的用户名
* @return str md5(用户名+salt)=>md5码
*/

function cCode($name) {
	$salt = require(ROOT . '/lib/config.php');
	return md5($name . '|' . $salt['salt']);
}

/**
* 删除指定的html标签
* @param str $tags 需要删除的标签(数组格式)
* @param str $str 需要处理的字符串
* @return str
*/

function strip_html_tags($tags,$str){
	$html=array();
	foreach ($tags as $tag) {
		$html[]="/(<(?:\/".$tag."|".$tag.")[^>]*>)/i";
    }
	$str=preg_replace($html, '', $str); 
	return $str;
}

/*计算分类等级lv，顶级－二级－三级......排除在$arr里的节点，因为‘所选择的上级分类不能是当前分类或者当前分类的下级分类’
@$p parent_id
@$lv 第几级
@$arr 排除的节点
*/
function gettreeArr($arr, $p=0, $lv=0){
    $t = array();
    $pdo = pConn();
    $sql = "select * from cat where parent_id = '{$p}'";
    //$t = pQuery($pdo,$sql);
    foreach(pQuery($pdo,$sql) as $data){
        if(!in_array($data['cat_id'], $arr)){
            $data['lv'] = $lv;
            $t[] = $data;
            $t = array_merge($t,gettreeArr($arr, $data['cat_id'], $lv+1));
        }
    }
    return $t;
}
function gettree($p=0,$lv=0){
    $t = array();
    $pdo = pConn();
    $sql = "select * from cat where parent_id = '{$p}'";
    //$t = pQuery($pdo,$sql);
    foreach(pQuery($pdo,$sql) as $data){
        $data['lv'] = $lv;
        $t[] = $data;
        $t = array_merge($t,gettree($data['cat_id'],$lv+1));
    }
    return $t;
}
//商品分类编辑下，计算‘所选择的上级分类不能是当前分类或者当前分类的下级分类’时用
//取当前编辑的cat_id和下级分类的cat_id
function catList($p){
    //$t = array();
    $pdo = pConn();
    $t[] = $p;
    foreach (pQuery($pdo,"select cat_id, parent_id from cat where parent_id = '{$p}'")  as $data) {
        if ($data['parent_id'] == $p) {
            $t = array_merge($t,catList($data['cat_id']));
        }
    }
    return $t;
}
//博客主页用，把分类文章数量也包含进cats数组里，再计算级数
//@$cats arr 包含cat表所有字段 + 分类文章数'num'
//@return arr 包含原cats数组键值 + 级数lv的数组
function gettreeIndex(&$cats,$p=0,$lv=0){
    $t = array();
    if(is_array($cats) && !empty($cats)){
        foreach($cats as $k => $data){
            if($data['parent_id'] == $p) {
                $data['lv'] = $lv;
                $t[] = $data;
                unset($cats[$k]);
                $t = array_merge($t, gettreeIndex($cats, $data['cat_id'], $lv + 1));
            }
         }
    }
    return $t;
}
/**
 * 创建一个JSON格式的数据
 *
 * @access  public
 * @param   string      $content
 * @param   integer     $error
 * @param   string      $message
 * @param   array       $append
 * @return  void
 */
function make_json_response($content='', $error="0", $message='', $append=array())
{
    $res = array('error' => $error, 'message' => $message, 'content' => $content);

    if (!empty($append))
    {
        foreach ($append AS $key => $val)
        {
            $res[$key] = $val;
        }
    }

    $val = json_encode($res);

    exit($val);
}
