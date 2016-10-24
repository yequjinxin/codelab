<?php
/**
 * the lib of god
 * 上帝的实验室
 * 
 * @author liujun01
 *
 */
namespace main;

define('ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('SYSTEM', ROOT . '../system/');
require './init.php';

$controller = empty($_GET['c']) ? 'index' : $_GET['c'];
$action = empty($_GET['a']) ? 'index' : $_GET['a'];

$objStr =  "\\app\\" . $controller;
if (!class_exists($objStr)) {
    show_error("类{$objStr}不存在", '访问的页面不存在');
}

$obj = new $objStr;

if (method_exists($obj, $action)) {
    call_user_func_array(array($obj, $action), array());
} else {
    show_error("类{$controller}的方法{$action}不存在", '访问的页面不存在');
}
